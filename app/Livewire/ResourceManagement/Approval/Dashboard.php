<?php

namespace App\Livewire\ResourceManagement\Approval;

use Livewire\Component;
use Livewire\WithPagination; // For pagination
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // For authorization

// Consider using models like Approval, EmailApplication, LoanApplication, User, and services like ApprovalService
// use App\Models\Approval;
// use App\Models\EmailApplication;
// use App\Models\LoanApplication;
// use App\Models\User;
// use App\Services\ApprovalService;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Support\Facades\Config; // Import Config facade

class Dashboard extends Component
{
  use WithPagination, AuthorizesRequests; // Use pagination and authorization

  // --- State Properties ---
  // public $filterStatus = 'pending'; // Filter approvals by status
  // public $search = '';

  // --- Computed Properties ---
  // public function getApprovalsProperty()
  // {
  //     // Fetch approvals assigned to the current user where status matches filter
  //     $minApprovalGradeLevel = Config::get('motac.approval.min_approver_grade_level');
  //     $user = Auth::user();

  //     // Ensure user has the minimum grade and necessary roles to be an approver
  //     if (!$user->grade || $user->grade->level < $minApprovalGradeLevel || !$user->hasAnyRole(['Approver', 'Admin'])) {
  //         return \Illuminate\Support\Collection::make()->paginate(10); // Return empty paginated collection if not an approver
  //     }

  //     return Approval::with(['approvable', 'officer']) // Eager load relationships
  //         ->where('officer_id', $user->id) // Approvals assigned to this officer
  //         ->when($this->filterStatus, fn ($query) => $query->where('status', $this->filterStatus)) // Filter by status
  //         // Add search logic here if needed, potentially searching related models (approvable)
  //         ->latest()
  //         ->paginate(10); // Paginate results
  // }

  public function render()
  {
    // Authorization check for the page itself
    // This check is also done in the route middleware, but good practice to have here too.
    // $this->authorize('viewAny', Approval::class); // Assuming a generic Approval policy or a specific one for the dashboard

    return view('livewire.resource-management.approval.dashboard', [
      // 'approvals' => $this->approvals, // Pass computed property
    ]);
  }

  // --- Actions ---
  // public function approve($approvalId) { ... }
  // public function reject($approvalId) { ... }
}
