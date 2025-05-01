<?php

namespace App\Livewire\Sections\Menu;

use App\Models\User; // Import User model
use Illuminate\Support\Collection; // Import Collection
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Livewire\Component;
use Spatie\Permission\Traits\HasRoles; // Assuming Spatie, import the trait

class VerticalMenu extends Component
{
  // Change to hold an array/collection of roles
  public Collection $userRoles; // Added Collection type hint

  public function mount()
  {
    // Fetch all roles for the authenticated user
    // Ensure your User model uses the HasRoles trait from Spatie
    $authenticatedUser = Auth::user();
    // Fetch role names or default to an empty collection
    $this->userRoles = $authenticatedUser
      ? $authenticatedUser->getRoleNames() // Get collection of role names
      : collect([]); // Default to empty collection if no user
  }

  public function render()
  {
    // The menu data is typically loaded in the layout's view composer
    // or passed down from the parent layout.
    // This component primarily needs the user's roles to filter the menu.
    // The menuData variable should be available in the view's scope.
    return view('livewire.sections.menu.vertical-menu');
  }

  // Helper function to check if the user has any of the required roles for a menu item
  public function hasRequiredRole($menuItemRoles): bool // Added bool return type hint
  {
    // Admin role has access to all configured menu items
    if ($this->userRoles->contains('Admin')) {
      return true;
    }

    // Ensure $menuItemRoles is treated as a collection for easier intersection
    $requiredRolesCollection = collect($menuItemRoles);


    // If the menu item has no specific role restriction, show it to everyone (except possibly unauthenticated users, handled by middleware)
    // Decide on default visibility for items with no role specified. True means visible to anyone not Admin if no role is specified.
    if ($requiredRolesCollection->isEmpty()) {
      return true; // Or false, depending on default visibility
    }

    // Check if the user has at least one of the roles required for the menu item
    return $this->userRoles->intersect($requiredRolesCollection)->isNotEmpty();
  }
}
