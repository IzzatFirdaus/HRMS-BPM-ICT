<?php

namespace App\Livewire\ResourceManagement\MyApplications\Email;

use Livewire\Component;
use Livewire\WithPagination; // For pagination
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // For authorization

// Consider using models like EmailApplication, User
// use App\Models\EmailApplication;
// use App\Models\User;

class Index extends Component
{
  use WithPagination, AuthorizesRequests; // Use pagination and authorization

  // --- State Properties ---
  // Define properties for search filters, sorting, etc.
  // public $search = '';

  // --- Computed Properties ---
  // Define computed properties to fetch data
  // public function getApplicationsProperty()
  // {
  //     return EmailApplication::where('user_id', auth()->id()) // Fetch applications for the logged-in user
  //         ->when($this->search, fn ($query) => $query->where('purpose', 'like', '%' . $this->search . '%')) // Example search
  //         ->latest()
  //         ->paginate(10); // Paginate the results
  // }

  public function render()
  {
    // Authorization check for viewing the list
    // $this->authorize('viewAny', EmailApplication::class); // Assuming you have a policy

    return view('livewire.resource-management.my-applications.email.index', [
      // 'applications' => $this->applications, // Pass computed property to view
    ]);
  }

  // --- Actions ---
  // Define actions like view details, cancel application, etc.
  // public function viewApplication($applicationId) { ... }
}
