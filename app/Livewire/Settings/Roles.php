<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Spatie\Permission\Models\Role; // Import Spatie Role model
use Illuminate\Support\Facades\Gate; // Import Gate facade for authorization
// If listing roles with pagination, import the trait:
// use Livewire\WithPagination;

class Roles extends Component
{
  // Optional: Use pagination if listing roles
  // use WithPagination;

  // Optional: Define properties for form/filtering if needed later
  // public $newRoleName = '';
  // public $search = '';

  public function mount()
  {
    // Optional: Enforce authorization check when the component is mounted
    // Assuming you have a policy or gate like 'viewAnyRoles'
    // if (!Gate::allows('viewAnyRoles')) {
    //     abort(403); // Or redirect to an unauthorized page
    // }
  }

  /**
   * Render the component's view.
   * Fetches data needed for the view (e.g., list of roles).
   *
   * @return \Illuminate\Contracts\View\View|\Closure|string
   */
  public function render()
  {
    // Fetch roles (you can add filtering/pagination later)
    // Example: $roles = Role::paginate(10);
    $roles = Role::all(); // Fetch all roles for now

    // Update view path to match the new file name resources/views/livewire/settings/roles.blade.php
    return view('livewire.settings.roles', [
      'roles' => $roles, // Pass roles to the view
      // Pass other necessary data (e.g., permissions for assigning)
    ]);
  }

  // You will add methods here later for adding, editing, deleting roles
  // public function saveRole() { ... }
  // public function deleteRole($roleId) { ... }
}
