<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\EmailApplication;
use App\Models\LoanApplication;
use App\Services\ApprovalService; // Use the service for approval logic
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // For policy checks
use Livewire\WithPagination; // If you want pagination


class ApprovalDashboard extends Component
{
  // use WithPagination; // Uncomment if using pagination

  public $filterStatus = 'pending'; // 'pending', 'approved', 'rejected', 'all'
  public $filterType = 'all'; // 'all', 'email', 'loan'

  // Properties for modal/sidebar to show details or capture comments
  public $showApprovalModal = false;
  public $currentApprovable = null; // The application being approved/rejected
  public $approvalComments = '';
  public $approvalDecision = ''; // 'approved' or 'rejected'


  public function render()
  {
    $user = Auth::user();
    $pendingApprovals = collect(); // Collection to hold pending applications

    // Fetch pending Email Applications the user can approve
    $emailApplications = EmailApplication::where('status', 'pending_support') // Assuming 'pending_support' needs Grade 41+ approval
      ->where(function ($query) use ($user) {
        // Add logic to filter based on department, role, or specific assignment
        // For now, assuming any Grade 41+ officer can approve any pending email application
      })
      ->get(); // Or paginate()

    // Filter email applications based on policy
    $emailApplications = $emailApplications->filter(fn($app) => Gate::allows('approve', $app));


    // Fetch pending Loan Applications the user can approve
    $loanApplications = LoanApplication::where('status', 'pending_support') // Assuming 'pending_support' needs Grade 41+ approval
      ->where(function ($query) use ($user) {
        // Add logic to filter based on department, role, or specific assignment
      })
      ->get(); // Or paginate()


    // Filter loan applications based on policy
    $loanApplications = $loanApplications->filter(fn($app) => Gate::allows('approve', $app));


    // Combine pending applications - ensure you add a 'type' identifier for display
    foreach ($emailApplications as $app) {
      $app->type = 'email';
      $pendingApprovals->push($app);
    }
    foreach ($loanApplications as $app) {
      $app->type = 'loan';
      $pendingApprovals->push($app);
    }


    // Apply filters if implemented
    if ($this->filterType !== 'all') {
      $pendingApprovals = $pendingApprovals->filter(fn($app) => $app->type === $this->filterType);
    }
    // Status filter is applied in the initial database query


    // Sort the results, e.g., by creation date
    $pendingApprovals = $pendingApprovals->sortByDesc('created_at');

    // If using pagination, apply it here

    return view('livewire.approval-dashboard', [
      'pendingApprovals' => $pendingApprovals, // Pass the filtered and sorted applications
    ]);
  }

  // Method to open modal for approval/rejection
  public function openApprovalModal($approvableId, $approvableType, $decision)
  {
    // Determine the model class from the type string
    $modelClass = 'App\\Models\\' . ucfirst($approvableType) . 'Application'; // e.g., App\Models\EmailApplication

    // Find the approvable model instance
    $approvable = $modelClass::findOrFail($approvableId);

    // Check policy to ensure the user can perform this action
    if ($decision === 'approved' && !Gate::allows('approve', $approvable)) {
      abort(403, 'Unauthorized action.'); // Or show error message
    }
    if ($decision === 'rejected' && !Gate::allows('reject', $approvable)) {
      abort(403, 'Unauthorized action.'); // Or show error message
    }

    $this->currentApprovable = $approvable;
    $this->approvalDecision = $decision;
    $this->approvalComments = ''; // Reset comments field
    $this->showApprovalModal = true;
  }

  // Method to close modal
  public function closeApprovalModal()
  {
    $this->showApprovalModal = false;
    $this->currentApprovable = null;
    $this->approvalComments = '';
    $this->approvalDecision = '';
  }


  // Method to perform approval/rejection
  public function processApproval(ApprovalService $approvalService)
  {
    $this->validate([
      'approvalComments' => 'nullable|string|max:500', // Validate comments if needed
    ]);

    if (!$this->currentApprovable) {
      session()->flash('error', 'No application selected for approval.');
      $this->closeApprovalModal();
      return;
    }

    // Check policy again before processing
    if ($this->approvalDecision === 'approved' && !Gate::allows('approve', $this->currentApprovable)) {
      abort(403, 'Unauthorized action.');
    }
    if ($this->approvalDecision === 'rejected' && !Gate::allows('reject', $this->currentApprovable)) {
      abort(403, 'Unauthorized action.');
    }


    try {
      // Use the ApprovalService to record the decision and update application status
      $approvalService->recordApproval(
        $this->currentApprovable,
        Auth::user(), // The current user is the approver
        $this->approvalDecision,
        $this->approvalComments
      );

      session()->flash('message', ucfirst($this->approvalDecision) . ' successfully.');
    } catch (\Exception $e) {
      session()->flash('error', 'An error occurred while processing the approval: ' . $e->getMessage());
      \Log::error("Approval processing failed for approvable ID: " . $this->currentApprovable->id . ", Type: " . get_class($this->currentApprovable) . ". Error: " . $e->getMessage());
    }


    // Close the modal and refresh the list
    $this->closeApprovalModal();
    // $this->render(); // Re-render the component to update the list
    // Or emit an event: $this->emitSelf('$refresh');

  }


  // Example: Method to view details in a sidebar or dedicated page
  public function viewDetails($approvableId, $approvableType)
  {
    // Redirect to the show page for the specific application type
    if ($approvableType === 'email') {
      return redirect()->route('email-applications.show', $approvableId);
    } elseif ($approvableType === 'loan') {
      return redirect()->route('loan-applications.show', $approvableId);
    }
  }

  // Add methods for filtering/sorting the list if using pagination or more complex queries

}
