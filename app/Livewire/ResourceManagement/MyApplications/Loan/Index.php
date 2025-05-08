<?php

namespace App\Livewire\ResourceManagement\MyApplications\Loan;

use Livewire\Component;
use Livewire\WithPagination; // For pagination
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // For authorization

// Consider using models like LoanApplication, LoanApplicationItem, User
// use App\Models\LoanApplication;
// use App\Models\LoanApplicationItem;
// use App\Models\User;

class Index extends Component
{
  use WithPagination, AuthorizesRequests; // Use pagination and authorization

  // --- State Properties ---
  // public $search = '';

  // --- Computed Properties ---
  // public function getApplicationsProperty()
  // {
  //     return LoanApplication::where('user_id', auth()->id()) // Fetch applications for the logged-in user
  //         ->when($this->search, fn ($query) => $query->where('purpose', 'like', '%' . $this->search . '%')) // Example search
  //         ->latest()
  //         ->paginate(10); // Paginate the results
  // }

  public function render()
  {
    // Authorization check for viewing the list
    // $this->authorize('viewAny', LoanApplication::class); // Assuming you have a policy

    return view('livewire.resource-management.my-applications.loan.index', [
      // 'applications' => $this->applications, // Pass computed property to view
    ]);
  }

  // --- Actions ---
  // public function viewApplication($applicationId) { ... }
  // public function cancelApplication($applicationId) { ... }
}
