<?php

namespace App\Livewire\Sections\Menu;

use App\Models\User; // Import User model
use Illuminate\Support\Collection; // Import Collection
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Livewire\Component;
use Spatie\Permission\Traits\HasRoles; // Assuming Spatie, import the trait
use Illuminate\Support\Facades\Route; // Import Route facade (if needed, currently not used in this component)
use Illuminate\Support\Facades\Log; // Import Log facade for debugging


class VerticalMenu extends Component
{
  // Property to hold the authenticated user's roles as a Collection of strings (role names)
  public Collection $userRoles;

  /**
   * Mount the component.
   * Fetches the authenticated user's roles.
   */
  public function mount()
  {
    // Get the authenticated user
    $authenticatedUser = Auth::user();

    // Fetch role names using Spatie's getRoleNames() method.
    // If there's no authenticated user (should not happen in a protected route group)
    // or the user has no roles, default to an empty collection.
    $this->userRoles = $authenticatedUser
      ? $authenticatedUser->getRoleNames() // Returns a Collection of role name strings
      : collect([]); // Returns an empty Collection

    // Optional: Add logging here during debugging to confirm roles are loaded
    // Log::info('VerticalMenu mounted. User ID: ' . optional($authenticatedUser)->id . '. Roles: ' . $this->userRoles->implode(', '));
  }

  /**
   * Render the component's view.
   *
   * @return \Illuminate\View\View
   */
  public function render()
  {
    // This renders the vertical-menu.blade.php view file.
    // The $userRoles property is automatically available in the view.
    // Other data like $configData or menu structure might be passed via a layout or view composer.
    return view('livewire.sections.menu.vertical-menu');
  }

  /**
   * Helper function to check if the authenticated user has any of the required roles for a menu item.
   * This method is called from the vertical-menu.blade.php view.
   *
   * @param array|null $menuItemRoles An array of role names required for the menu item (can be null or empty array).
   * @return bool Returns true if the user should see the menu item, false otherwise.
   */
  public function hasRequiredRole($menuItemRoles): bool
  {
    // Rule 1: Admin role (Super Admin) sees everything.
    // Check if the user's roles collection contains the 'Admin' role.
    if ($this->userRoles->contains('Admin')) {
      // Log::debug("hasRequiredRole check: User is Admin. Access granted.");
      return true;
    }

    // Ensure $menuItemRoles is treated as a collection. Handle null input gracefully.
    // collect($menuItemRoles ?? []) converts null into an empty collection.
    $requiredRolesCollection = collect($menuItemRoles ?? []);

    // Rule 2: If the menu item has NO specific role restriction defined (empty array or null),
    // assume it's visible to all authenticated users.
    // Since this component is used within authenticated routes, Auth::check() should generally be true here.
    if ($requiredRolesCollection->isEmpty()) {
      // Log::debug("hasRequiredRole check: No specific roles required. User is authenticated: " . (Auth::check() ? 'true' : 'false') . ". Access granted based on authentication.");
      return Auth::check(); // Only show to authenticated users if no roles are specified for the item
    }

    // Rule 3: If specific roles ARE defined, check if the authenticated user has at least one of them.
    // Find the intersection of the user's roles and the required roles.
    $intersectingRoles = $this->userRoles->intersect($requiredRolesCollection);

    // If the intersection is not empty, the user has at least one required role.
    $canSee = $intersectingRoles->isNotEmpty();
    // Log::debug("hasRequiredRole check: Specific roles required. User Roles: " . $this->userRoles->implode(', ') . ", Required Roles: " . $requiredRolesCollection->implode(', ') . ", Intersection empty: " . ($intersectingRoles->isEmpty() ? 'true' : 'false') . ". Access granted: " . ($canSee ? 'true' : 'false'));
    return $canSee;
  }

  /**
   * Although active state logic is typically in the Blade view, you *could* move
   * complex active state determination logic into a helper method here if needed.
   * Keeping it in Blade for now as it's common practice and works with request()->routeIs().
   */
}
