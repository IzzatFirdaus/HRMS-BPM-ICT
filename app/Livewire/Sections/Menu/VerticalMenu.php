<?php

namespace App\Livewire\Sections\Menu;

// Removed misplaced trait import: use App\Traits\CreatedUpdatedDeletedBy;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Models\User; // Ensure User model is imported
use Illuminate\Support\Facades\Log;
// Removed unused Eloquent relation import: use Illuminate\Database\Eloquent\Relations\BelongsTo;


// This component renders the vertical menu section, adjusting based on user roles.

class VerticalMenu extends Component
{
  // 👉 Public Property for User Role - This will be available in the view
  // Declare the public property with a type hint. Initialize as null.
  public ?string $role = null;

  // 👉 Public Property for Menu Data - This will be available in the view
  // Declare the public property with a type hint. Initialize as null or empty array.
  // Assuming your config returns an array structure.
  public ?array $menuData = null;


  // 👉 Lifecycle Hook: mount() - Called when the component is initialized
  // Menu data and role are fetched here.
  public function mount() // Removed $menuData parameter as we'll load internally
  {
    $user = Auth::user();

    // Fetch the user's role if they are logged in
    // Assuming you are using a package like Spatie/laravel-permission
    // Safely get the role name using optional chaining
    $this->role = $user?->getRoleNames()?->first();

    // 👉 Load the menu data from a configuration file or service
    // You NEED to ensure this config file exists and is correctly structured
    // or replace this line with your actual logic for loading menu data.
    // Safely load config and cast to array if necessary, handle potential null
    $configData = config('menuConfig.vertical');

    // Optional: Add a check to ensure menuData was loaded successfully
    if (is_null($configData)) {
      Log::error('VerticalMenu: Failed to load menu data from config("menuConfig.vertical"). Please check your configuration.');
      // Optionally, set menuData to an empty array to prevent view errors
      $this->menuData = ['menu' => []]; // Set to expected empty structure
    } else {
      // Assuming the config returns an array or can be cast to one
      $this->menuData = (array) $configData;
    }
  }


  // 👉 Render method

  /**
   * Render the component's view.
   * Public properties ($this->role, $this->menuData) are automatically available to the view.
   *
   * @return \Illuminate\Contracts\View\View
   */
  public function render(): \Illuminate\Contracts\View\View
  {
    // Return the Blade view for the vertical menu section.
    // The view can now access $role and $menuData via the public properties.
    return view('livewire.sections.menu.vertical-menu');
  }

  // You would define your menu structure in a config file, e.g., config/menuConfig.php
  // Example:
  /*
     // config/menuConfig.php
     return [
         'vertical' => [
             'menu' => [
                 [
                     'menuHeader' => 'Home',
                     'role' => ['Admin', 'User'], // Roles that can see this header
                 ],
                 [
                     'name' => 'Dashboard',
                     'url' => '/dashboard',
                     'icon' => 'ti ti-home',
                     'slug' => 'dashboard',
                     'role' => ['Admin', 'User'], // Roles that can see this item
                 ],
                 // ... other menu items ...
             ],
         ],
     ];
     */
}
