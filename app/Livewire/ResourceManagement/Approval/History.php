<?php

namespace App\Livewire\ResourceManagement\Approval;

use Livewire\Component;
use Livewire\WithPagination; // For pagination
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // For authorization

// Consider using models like Approval, EmailApplication, LoanApplication, User
// use App\Models\Approval;
// use App\Models\EmailApplication;
// use App\Models\LoanApplication;
// use App\Models\User;

class History extends Component
{
  use WithPagination, AuthorizesRequests; // Use pagination and authorization

  // --- State Properties ---
  // public $search = '';
  // public $filterType = ''; // e.g., 'email', 'loan'
  // public $filterStatus = ''; // e.g., 'approved', 'rejected'

  // --- Computed Properties ---
  // public function getApprovalHistoryProperty()
  // {
  //     // Fetch approval records based on filters, accessible to Admin/Approver/BPM
  //     $query = Approval::with(['approvable', 'officer']); // Eager load relationships

  //     // Filter by application type if specified
  //     // if ($this->filterType === 'email') {
  //     //     $query->whereHasMorph('approvable', [EmailApplication::class]);
  //     // } elseif ($this->filterType === 'loan') {
  //     //     $query->whereHasMorph('approvable', [LoanApplication::class]);
  //     // }

  //     // Filter by status if specified
  //     // if ($this->filterStatus) {
  //     //     $query->where('status', $this->filterStatus);
  //     // }

  //     // Add search logic here if needed
  //     // ...

  //     return $query->latest('approval_timestamp')->paginate(10); // Paginate results
  // }

  public function render()
  {
    // Authorization check for the page itself
    // $this->authorize('viewHistory', Approval::class); // Assuming a policy method for viewing history

    return view('livewire.resource-management.approval.history', [
      // 'approvalHistory' => $this->approvalHistory, // Pass computed property
    ]);
  }

  // --- Actions ---
  // public function viewApplicationDetails($approvableType, $approvableId) { ... }
}
