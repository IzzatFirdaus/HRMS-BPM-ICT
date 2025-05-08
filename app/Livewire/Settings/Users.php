<?php

namespace App\Livewire\Settings;

use App\Models\User; // Import the User model
use Livewire\Component;
use Livewire\WithPagination; // Import the WithPagination trait
// use Illuminate\Support\Facades\Auth; // Import Auth facade if needed for policies/scopes

class Users extends Component
{
  use WithPagination; // Use the pagination trait

  // Optional: Define properties for filtering/searching if needed later
  // public $search = '';
  // public $status = '';

  // Optional: Listeners for events (e.g., 'userCreated' to refresh the list)
  // protected $listeners = ['userCreated' => '$refresh'];

  /**
   * Render the component's view.
   * Fetches users with pagination and passes them to the view.
   *
   * @return \Illuminate\Contracts\View\View
   */
  public function render(): \Illuminate\Contracts\View\View
  {
    // Fetch users, eager load roles if needed for the table display
    // Apply any necessary scopes or filters (e.g., based on roles, status)
    // Use pagination
    $users = User::with('roles') // Eager load roles relationship (assuming Spatie roles)
      // ->when($this->search, function ($query) { // Example search filter
      //     $query->where('name', 'like', '%' . $this->search . '%')
      //           ->orWhere('email', 'like', '%' . $this->search . '%');
      // })
      // ->when($this->status, function ($query) { // Example status filter
      //      $query->where('status', $this->status);
      // })
      ->paginate(10); // Paginate the results (e.g., 10 users per page)


    // Pass the fetched users data to the view
    return view('livewire.settings.users', [
      'users' => $users, // Pass the paginated users collection to the view
      // Pass other necessary data to the view if needed (e.g., available roles for filters)
    ]);
  }

  // Optional: Add a method to delete a user if handled by this component
  // public function deleteUser($userId)
  // {
  //     // Find the user
  //     $user = User::find($userId);

  //     // Check if the user exists and if the current user is authorized to delete
  //     // Ensure Auth facade is imported if needed for authorization checks
  //     // if ($user && Auth::user()->can('delete', $user)) { // Assuming a policy exists
  //         $user->delete();
  //         session()->flash('message', 'User deleted successfully.');
  //     // } else {
  //     //      session()->flash('error', 'Unable to delete user or not authorized.');
  //     // }

  //      // No explicit redirect needed; Livewire will re-render the component.
  //     // The pagination might automatically adjust. If needed, you could reset pagination.
  //     // $this->resetPage();
  // }

  // Optional: Add methods to handle pagination page changes if needed manually
  // public function paginationView()
  // {
  //     // If you need a custom pagination view (optional)
  //     // return 'livewire.custom-pagination';
  // }
}
