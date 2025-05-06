<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Spatie\Permission\Models\Role; // Import Spatie Role model
use Illuminate\Support\Facades\Gate; // Import Gate facade for authorization

class Roles extends Component
{
  // You can add properties here later for searching, pagination, etc.
  // public $search = '';

  public function mount()
  {
    // Optional: Enforce authorization check when the component is mounted
    // Assuming you have a policy or gate like 'viewAnyRoles'
    // if (!Gate::allows('viewAnyRoles')) {
    //     abort(403); // Or redirect to an unauthorized page
    // }
  }


  public function render()
  {
    // Fetch roles (you can add filtering/pagination later)
    $roles = Role::all(); // Fetch all roles for now

    return view('livewire.settings.roles.index', [
      'roles' => $roles,
    ]);
  }

  // You will add methods here later for adding, editing, deleting roles
  // public function saveRole() { ... }
  // public function deleteRole($roleId) { ... }
}
