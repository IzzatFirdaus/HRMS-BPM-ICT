<?php

namespace App\Livewire\ResourceManagement\Admin\Reports;

use Livewire\Component;
use Livewire\WithPagination; // For pagination
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // For authorization

// Consider using models like User, EmailApplication, LoanApplication, Approval, and potentially Controllers/Services for reporting logic
// use App\Models\User;
// use App\Models\EmailApplication;
// use App\Models\LoanApplication;
// use App\Models\Approval;
// use App\Http\Controllers\ReportController; // If you use the controller for logic

class UserActivityReport extends Component
{
  use WithPagination, AuthorizesRequests; // Use pagination and authorization

  // --- State Properties ---
  // public $search = ''; // Search by user name/email
  // public $sortBy = 'name'; // Default sort column
  // public $sortDirection = 'asc'; // Default sort direction

  // --- Computed Properties ---
  // public function getReportDataProperty()
  // {
  //     // Fetch users with counts of their related activities (applications, approvals)
  //     // This might mirror logic in ReportController::userActivity()
  //     $query = User::withCount(['emailApplications', 'loanApplications', 'approvals']);

  //     // Apply search
  //     // $query->when($this->search, fn ($query) => $query->where('full_name', 'like', '%' . $this->search . '%')
  //     //     ->orWhere('email', 'like', '%' . $this->search . '%'));

  //     // Apply sorting
  //     // $query->orderBy($this->sortBy, $this->sortDirection);

  //     return $query->paginate(15); // Paginate results
  // }

  public function render()
  {
    // Authorization check for the page itself
    // $this->authorize('viewUserActivityReport'); // Assuming a permission or policy for reports

    return view('livewire.resource-management.admin.reports.user-activity-report', [
      // 'reportData' => $this->reportData, // Pass computed property
    ]);
  }

  // --- Actions ---
  // public function applyFilters() { ... }
  // public function sortBy($field) { ... }
}
