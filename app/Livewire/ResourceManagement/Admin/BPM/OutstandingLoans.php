<?php

namespace App\Livewire\ResourceManagement\Admin\BPM;

use Livewire\Component;
use Livewire\WithPagination; // For pagination
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // For authorization

// Consider using models like LoanApplication, LoanApplicationItem, User, Equipment, LoanTransaction, and services like LoanApplicationService
// use App\Models\LoanApplication;
// use App\Models\LoanApplicationItem;
// use App\Models\User;
// use App\Models\Equipment;
// use App\Models\LoanTransaction;
// use App\Services\LoanApplicationService;

class OutstandingLoans extends Component
{
  use WithPagination, AuthorizesRequests; // Use pagination and authorization

  // --- State Properties ---
  // public $search = '';
  // public $filterDepartment = '';

  // --- Computed Properties ---
  // public function getOutstandingApplicationsProperty()
  // {
  //     // Fetch loan applications ready for BPM action (e.g., status 'approved', 'partially_issued')
  //     $query = LoanApplication::with(['user.department', 'loanItems']) // Eager load relevant relationships
  //         ->whereIn('status', [
  //             LoanApplication::STATUS_APPROVED,
  //             LoanApplication::STATUS_PARTIALLY_ISSUED,
  //             // Include other statuses BPM might see before issuance if needed
  //         ]);

  //     // Add filters/search
  //     // ...

  //     return $query->latest()->paginate(10);
  // }

  public function render()
  {
    // Authorization check for the page itself
    // $this->authorize('viewOutstandingLoans', LoanApplication::class); // Assuming a policy method for BPM

    return view('livewire.resource-management.admin.bpm.outstanding-loans', [
      // 'applications' => $this->outstandingApplications, // Pass computed property
    ]);
  }

  // --- Actions ---
  // public function viewApplicationDetails($applicationId) { ... }
  // public function prepareForIssuance($applicationId) { ... } // Navigate to issuance form/modal
}
