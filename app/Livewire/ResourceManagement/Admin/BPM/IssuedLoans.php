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

class IssuedLoans extends Component
{
  use WithPagination, AuthorizesRequests; // Use pagination and authorization

  // --- State Properties ---
  // public $search = '';
  // public $filterStatus = 'issued'; // e.g., 'issued', 'overdue'

  // --- Computed Properties ---
  // public function getIssuedApplicationsProperty()
  // {
  //     // Fetch loan applications currently issued or overdue, relevant to BPM returns
  //     $query = LoanApplication::with(['user.department', 'loanItems']) // Eager load relevant relationships
  //         ->whereIn('status', [
  //             LoanApplication::STATUS_ISSUED,
  //             LoanApplication::STATUS_OVERDUE,
  //             LoanApplication::STATUS_PARTIALLY_RETURNED,
  //             // Include other statuses BPM might see for returns
  //         ]);

  //     // Add filters/search
  //     // ...

  //     return $query->latest()->paginate(10);
  // }

  public function render()
  {
    // Authorization check for the page itself
    // $this->authorize('viewIssuedLoans', LoanApplication::class); // Assuming a policy method for BPM

    return view('livewire.resource-management.admin.bpm.issued-loans', [
      // 'applications' => $this->issuedApplications, // Pass computed property
    ]);
  }

  // --- Actions ---
  // public function viewApplicationDetails($applicationId) { ... }
  // public function processReturn($applicationId) { ... } // Navigate to return processing form/modal
}
