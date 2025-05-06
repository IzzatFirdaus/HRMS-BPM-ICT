<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Spatie\Permission\Models\Permission; // Import Spatie Permission model
use Illuminate\Support\Facades\Gate; // Import Gate facade for authorization


class Permissions extends Component
{
  // You can add properties here later for searching, pagination, etc.
  // public $search = '';

  public function mount()
  {
    // Optional: Enforce authorization check when the component is mounted
    // Assuming you have a policy or gate like 'viewAnyPermissions'
    // if (!Gate::allows('viewAnyPermissions')) {
    //     abort(403); // Or redirect to an unauthorized page
    // }
  }

  public function render()
  {
    // Fetch permissions (you can add filtering/pagination later)
    $permissions = Permission::all(); // Fetch all permissions for now

    return view('livewire.settings.permissions.index', [
      'permissions' => $permissions,
    ]);
  }

  // You will add methods here later for adding, editing, deleting permissions
  // public function savePermission() { ... }
  // public function deletePermission($permissionId) { ... }
}
