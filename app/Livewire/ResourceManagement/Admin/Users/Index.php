<?php

namespace App\Livewire\ResourceManagement\Admin\Users;

use Livewire\Component;
use Livewire\WithPagination; // For pagination
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // For authorization

// Consider using models like User, and Jetstream/Fortify Actions
// use App\Models\User;
// use App\Actions\Fortify\CreateNewUser;
// use App\Actions\Jetstream\DeleteUser;

class Index extends Component
{
  use WithPagination, AuthorizesRequests; // Use pagination and authorization

  // --- State Properties ---
  // public $search = '';
  // public $filterRole = ''; // Filter users by role
  // public $showingCreateModal = false; // State for create modal
  // public $showingEditModal = false; // State for edit modal
  // public $userToDeleteId = null; // User ID for delete confirmation

  // --- Computed Properties ---
  // public function getUsersProperty()
  // {
  //     $query = User::query();

  //     // Filter by search term
  //     // $query->when($this->search, fn ($query) => $query->where('full_name', 'like', '%' . $this->search . '%')
  //     //     ->orWhere('email', 'like', '%' . $this->search . '%'));

  //     // Filter by role
  //     // if ($this->filterRole) {
  //     //     $query->role($this->filterRole); // Assuming Spatie role scope
  //     // }

  //     return $query->latest()->paginate(10);
  // }

  public function render()
  {
    // Authorization check for the page itself
    // $this->authorize('viewAny', User::class); // Assuming a User policy

    return view('livewire.resource-management.admin.users.index', [
      // 'users' => $this->users, // Pass computed property
    ]);
  }

  // --- Actions ---
  // public function confirmUserDeletion($userId) { ... }
  // public function deleteUser() { ... } // Use DeleteUser action
  // public function createUser() { ... } // Use CreateNewUser action
  // public function editUser($userId) { ... } // Redirect or show edit modal
}
