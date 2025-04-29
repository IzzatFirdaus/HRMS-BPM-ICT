<?php

namespace App\Livewire\Sections\Menu;

use App\Models\User; // Assumed to exist and potentially use a role package
use Illuminate\Support\Facades\Auth; // For accessing the authenticated user
use Livewire\Attributes\Computed; // For Livewire v3+ computed properties
use Livewire\Component; // Base Livewire component


// This component renders the vertical menu section, potentially adjusting based on user roles.

class VerticalMenu extends Component
{
  // No state properties needed as the role is now a computed property

  // Removed public $role = null;

  // 👉 Computed Property for User Role

  /**
   * Get the authenticated user's primary role name.
   * Assumes the User model uses a package like spatie/laravel-permission
   * providing the getRoleNames() method.
   *
   * @return string|null
   */
  #[Computed] // Define this as a computed property
  public function role(): ?string // Type hint as nullable string
  {
    // Get the authenticated user directly
    $user = Auth::user();

    // Safely get the first role name if the user exists and has roles
    // Assumes getRoleNames() returns a Collection
    return $user?->getRoleNames()?->first();
  }


  // 👉 Render method

  /**
   * Render the component's view.
   * The computed property $this->role is automatically available to the view.
   *
   * @return \Illuminate\Contracts\View\View
   */
  public function render(): \Illuminate\Contracts\View\View // Added return type hint
  {
    // Return the Blade view for the vertical menu section.
    // The view can access $this->role to conditionally display menu items.
    return view('livewire.sections.menu.vertical-menu');
  }

  // Removed the mount() method as its logic is now in the computed property
  // public function mount() { ... }
}
