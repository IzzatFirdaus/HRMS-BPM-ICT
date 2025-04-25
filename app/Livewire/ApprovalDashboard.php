<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Approval; // Import the Approval model
use App\Models\EmailApplication; // Import for type hinting/checking
use App\Models\LoanApplication; // Import for type hinting/checking
use App\Services\ApprovalService; // Use the service for approval logic
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Support\Facades\Gate; // For Gate::allows (optional, prefer $this->authorize)
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Trait for policy checks
use Livewire\WithPagination; // Use WithPagination trait
use Illuminate\Database\Eloquent\ModelNotFoundException; // FIX: Import ModelNotFoundException
use Illuminate\Support\Facades\Log; // FIX: Import Log facade


class ApprovalDashboard extends Component
{
  use WithPagination; // Use the WithPagination trait
  use AuthorizesRequests; // Use the AuthorizesRequests trait

  // Set pagination theme (e.g., 'bootstrap', 'tailwind') - adjust based on your frontend framework
  protected $paginationTheme = 'bootstrap';


  public $filterStatus = 'pending'; // Filter by status ('pending' is default for dashboard)
  public $filterType = 'all'; // Filter by approvable type ('all', 'email', 'loan')

  // Properties for modal/sidebar to show details or capture comments
  public $showApprovalModal = false;
  public $currentApproval = null; // The specific Approval record being processed
  public $currentApprovable = null; // The related application (Email or Loan) for display
  public $approvalComments = '';
  public $approvalDecision = ''; // 'approved' or 'rejected'


  /**
   * Render the component view.
   * Fetches pending Approval records assigned to the authenticated officer.
   *
   * @return \Illuminate\View\View
   */
  public function render()
  {
    $user = Auth::user();

    // Authorize if the user can view the approval dashboard (viewAny on Approval model)
    $this->authorize('viewAny', Approval::class); // Assuming an ApprovalPolicy exists

    // Start building the query for Approval records
    $query = Approval::query()
      ->where('officer_id', $user->id) // Filter approvals assigned to the current user
      ->where('status', 'pending') // Only fetch pending approval tasks
      ->with('approvable', 'approvable.user') // Eager load the related application and the applicant user
      ->latest(); // Order by latest pending approval task


    // Apply type filter if selected
    if ($this->filterType !== 'all') {
      $approvableTypeClass = null;
      if ($this->filterType === 'email') {
        $approvableTypeClass = EmailApplication::class;
      } elseif ($this->filterType === 'loan') {
        $approvableTypeClass = LoanApplication::class;
      }

      if ($approvableTypeClass) {
        // Filter by the type of the related approvable model using whereHasMorph
        $query->whereHasMorph('approvable', [$approvableTypeClass]);
      }
    }

    // Apply pagination to the query results
    $pendingApprovals = $query->paginate(10); // Adjust items per page as needed


    // Note: Authorization for processing individual approvals (approve/reject)
    // is handled in openApprovalModal and processApproval methods using policies.
    // The view should ensure that action buttons are only shown if the user
    // can update the specific Approval record ($user->can('update', $approval)).


    return view('livewire.approval-dashboard', [
      'pendingApprovals' => $pendingApprovals, // Pass the paginated collection of Approval models
    ]);
  }

  /**
   * Method to open modal for approval/rejection of a specific pending Approval record.
   *
   * @param int $approvalId The ID of the pending Approval record.
   * @param string $decision 'approved' or 'rejected'.
   * @return void
   */
  public function openApprovalModal($approvalId, $decision)
  {
    // Find the specific Approval record
    try {
      $approval = Approval::findOrFail($approvalId);
    } catch (ModelNotFoundException $e) { // Catch ModelNotFoundException
      session()->flash('error', 'Approval task not found.');
      return; // Stop execution
    }


    // Authorize if the user can update this specific Approval record (policy should check officer_id and status='pending')
    $this->authorize('update', $approval); // Assuming an ApprovalPolicy exists with an 'update' method


    // Store the Approval model instance and its related approvable
    $this->currentApproval = $approval;
    $this->currentApprovable = $approval->approvable; // Get the related application (Email or Loan)
    $this->approvalDecision = $decision;
    $this->approvalComments = ''; // Reset comments field

    $this->showApprovalModal = true; // Show the modal
  }

  /**
   * Method to close the approval/rejection modal.
   *
   * @return void
   */
  public function closeApprovalModal()
  {
    $this->showApprovalModal = false;
    $this->currentApproval = null; // Clear the stored Approval record
    $this->currentApprovable = null; // Clear the stored approvable
    $this->approvalComments = '';
    $this->approvalDecision = '';
  }


  /**
   * Method to perform the approval/rejection action via the ApprovalService.
   *
   * @param ApprovalService $approvalService The ApprovalService instance.
   * @return void
   */
  public function processApproval(ApprovalService $approvalService)
  {
    // Validate comments if necessary
    $this->validate([
      'approvalComments' => 'nullable|string|max:500', // Validate comments
    ]);

    // Ensure a pending Approval record is selected
    if (!$this->currentApproval) {
      session()->flash('error', 'No approval task selected for processing.');
      $this->closeApprovalModal();
      return;
    }

    // Re-authorize the action before processing to prevent race conditions
    // The policy's 'update' method should ensure status is still 'pending' and user is the assigned officer
    $this->authorize('update', $this->currentApproval);


    try {
      // Use the ApprovalService to record the decision on the specific Approval record
      // Call the correct service methods based on the decision
      if ($this->approvalDecision === 'approved') {
        // Ensure recordApprovalDecision exists in ApprovalService
        $approvalService->recordApprovalDecision(
          $this->currentApproval, // Pass the specific Approval model
          Auth::user(), // The current user is the officer making the decision
          $this->approvalComments // Pass comments
        );
        session()->flash('message', 'Approved successfully.');
      } elseif ($this->approvalDecision === 'rejected') {
        // Ensure recordRejectionDecision exists in ApprovalService
        $approvalService->recordRejectionDecision(
          $this->currentApproval, // Pass the specific Approval model
          Auth::user(), // The current user is the officer making the decision
          $this->approvalComments // Pass comments
        );
        session()->flash('message', 'Rejected successfully.');
      } else {
        // Handle invalid decision state
        session()->flash('error', 'Invalid approval decision.');
        Log::error("Invalid approval decision state in ApprovalDashboard for Approval ID: " . $this->currentApproval->id . ". Decision: " . $this->approvalDecision); // Use Log facade
      }
    } catch (\Exception $e) {
      // Catch exceptions thrown by the service (e.g., database errors, policy failures within service)
      session()->flash('error', 'An error occurred while processing the approval: ' . $e->getMessage());
      Log::error("Approval processing failed for Approval ID: " . $this->currentApproval->id . ". Error: " . $e->getMessage()); // Use Log facade
    }


    // Close the modal
    $this->closeApprovalModal();

    // Refresh the Livewire component to update the list of pending approvals
    $this->dispatch('$refresh');
  }


  /**
   * Method to view details of the related application in a dedicated page.
   *
   * @param int $approvalId The ID of the pending Approval record.
   * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
   */
  public function viewDetails($approvalId)
  {
    // Find the specific Approval record
    try {
      $approval = Approval::findOrFail($approvalId);
    } catch (ModelNotFoundException $e) { // Catch ModelNotFoundException
      session()->flash('error', 'Approval task not found.');
      return redirect()->route('approvals'); // FIX: Return a redirect on this path
    }


    // Authorize if the user can view this specific Approval record (policy should check officer_id, etc.)
    $this->authorize('view', $approval); // Assuming an ApprovalPolicy exists with a 'view' method


    // Get the related approvable model (EmailApplication or LoanApplication)
    $approvable = $approval->approvable;

    // Determine the route name based on the approvable type
    $routeName = null;
    if ($approvable instanceof EmailApplication) {
      $routeName = 'email-applications.show'; // Assuming you have named routes like 'email-applications.show'
    } elseif ($approvable instanceof LoanApplication) {
      $routeName = 'loan-applications.show'; // Assuming you have named routes like 'loan-applications.show'
    }

    // Redirect to the show page for the specific application type, passing the approvable model ID
    if ($routeName && $approvable) {
      return redirect()->route($routeName, $approvable->id); // Use route model binding or just pass ID
    } else {
      // Handle cases where approvable type is unknown or route is not defined
      session()->flash('error', 'Could not determine details page for this application type.');
      Log::error("Could not determine details page for approvable type: " . get_class($approvable) . " for Approval ID: " . $approvalId); // Use Log facade
      return redirect()->route('approvals'); // FIX: Return a redirect on this path
    }
  }

  // Method to update filters and reset pagination
  public function updatedFilterType()
  {
    $this->resetPage(); // Reset to the first page when filter changes
  }
  public function updatedFilterStatus()
  {
    $this->resetPage(); // Reset to the first page when filter changes
    // Note: Current render method hardcodes status to 'pending', so this filter might not be fully utilized yet.
    // You would modify the render query to filter by $this->filterStatus if you want to show approved/rejected/all.
  }


  // Add other methods as needed (e.g., sorting, bulk actions)
}
