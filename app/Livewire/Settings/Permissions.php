<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Spatie\Permission\Models\Permission; // Import Spatie Permission model
use Illuminate\Support\Facades\Gate; // Import Gate facade for authorization
// If listing permissions with pagination, import the trait:
// use Livewire\WithPagination;

class Permissions extends Component
{
  // Optional: Use pagination if listing permissions
  // use WithPagination;

  // Optional: Define properties for form/filtering if needed later
  // public $newPermissionName = '';
  // public $search = '';

  public function mount()
  {
    // Optional: Enforce authorization check when the component is mounted
    // Assuming you have a policy or gate like 'viewAnyPermissions'\n\
    // if (!Gate::allows('viewAnyPermissions')) {
    //     abort(403); // Or redirect to an unauthorized page
    // }
  }

  /**
   * Render the component's view.
   * Fetches data needed for the view (e.g., list of permissions).
   *
   * @return \Illuminate\Contracts\View\View|\Closure|string
   */
  public function render()
  {
    // Fetch permissions (you can add filtering/pagination later)
    // Example: $permissions = Permission::paginate(10);
    $permissions = Permission::all(); // Fetch all permissions for now

    // Update view path to match the new file name resources/views/livewire/settings/permissions.blade.php
    return view('livewire.settings.permissions', [
      'permissions' => $permissions, // Pass permissions to the view
      // Pass other necessary data (e.g., roles for assigning permissions)
    ]);
  }

  // You will add methods here later for adding, editing, deleting permissions
  // public function savePermission() { ... }
  // public function deletePermission($permissionId) { ... }
}
