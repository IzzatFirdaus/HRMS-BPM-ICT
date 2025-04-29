<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Approval;
use App\Models\EmailApplication;
use App\Models\LoanApplication;
use App\Services\ApprovalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\Routing\Exception\RouteNotFoundException; // ✅ Correct import based on user's note and common usage


class ApprovalDashboard extends Component
{
  use WithPagination, AuthorizesRequests;

  // Set pagination theme (e.g., 'bootstrap', 'tailwind')
  protected string $paginationTheme = 'bootstrap';

  // Filters
  public string $filterStatus = 'pending';
  public string $filterType = 'all';

  // Modal/sidebar state and data
  public bool $showApprovalModal = false;
  public ?Approval $currentApproval = null;
  public mixed $currentApprovable = null; // Mixed type for EmailApplication or LoanApplication
  public string $approvalComments = '';
  public string $approvalDecision = ''; // 'approved' or 'rejected'

  /**
   * Component boot lifecycle hook.
   * Authorizes the user to view the approval dashboard.
   *
   * @return void
   */
  public function boot(): void
  {
    // Authorize viewAny permission for Approval model
    $this->authorize('viewAny', Approval::class);
  }

  /**
   * Render the component view.
   * Fetches Approval records assigned to the authenticated officer based on filters.
   *
   * @return \Illuminate\View\View
   */
  public function render(): View
  {
    $user = Auth::user();

    $query = Approval::query()
      ->where('officer_id', $user->id)
      ->with('approvable', 'approvable.user')
      ->latest();

    // Apply status filter
    if ($this->filterStatus !== 'all') {
      $query->where('status', $this->filterStatus);
    }

    // Apply type filter
    if ($this->filterType !== 'all') {
      $approvableTypeClass = null;
      if ($this->filterType === 'email') {
        $approvableTypeClass = EmailApplication::class;
      } elseif ($this->filterType === 'loan') {
        $approvableTypeClass = LoanApplication::class;
      }

      if ($approvableTypeClass) {
        $query->whereHasMorph('approvable', [$approvableTypeClass]);
      }
    }

    $approvals = $query->paginate(10);

    return view('livewire.approval-dashboard', [
      'approvals' => $approvals,
    ]);
  }

  /**
   * Open modal for approval/rejection of a specific pending Approval record.
   *
   * @param int $approvalId The ID of the pending Approval record.
   * @param string $decision 'approved' or 'rejected'.
   * @return void
   */
  public function openApprovalModal(int $approvalId, string $decision): void
  {
    try {
      $approval = Approval::where('id', $approvalId)
        ->where('officer_id', Auth::id())
        ->where('status', 'pending')
        ->firstOrFail();
    } catch (ModelNotFoundException $e) {
      Log::warning('ApprovalDashboard: Attempted to open modal for non-pending or unassigned approval task.', [
        'approval_id' => $approvalId,
        'user_id' => Auth::id(),
        'decision' => $decision
      ]);
      session()->flash('error', __('Approval task not found or already processed.'));
      return;
    }

    $this->authorize('update', $approval);

    $this->currentApproval = $approval;
    $this->currentApprovable = $approval->approvable;
    $this->approvalDecision = $decision;
    $this->approvalComments = '';

    $this->showApprovalModal = true;
  }

  /**
   * Close the approval/rejection modal.
   *
   * @return void
   */
  public function closeApprovalModal(): void
  {
    $this->showApprovalModal = false;
    $this->currentApproval = null;
    $this->currentApprovable = null;
    $this->approvalComments = '';
    $this->approvalDecision = '';
    $this->resetValidation();
  }

  /**
   * Perform the approval/rejection action via the ApprovalService.
   *
   * @param ApprovalService $approvalService The ApprovalService instance.
   * @return void
   */
  public function processApproval(ApprovalService $approvalService): void
  {
    $this->validate([
      'approvalComments' => 'nullable|string|max:500',
    ]);

    if (!$this->currentApproval) {
      session()->flash('error', __('No approval task selected for processing.'));
      $this->closeApprovalModal();
      return;
    }

    try {
      $this->authorize('update', $this->currentApproval);
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      Log::warning('ApprovalDashboard: Authorization failed during processApproval.', [
        'approval_id' => $this->currentApproval->id,
        'user_id' => Auth::id(),
        'decision' => $this->approvalDecision
      ]);
      session()->flash('error', __('You are not authorized to process this approval task or it is no longer pending.'));
      $this->closeApprovalModal();
      return;
    }

    try {
      if ($this->approvalDecision === 'approved') {
        $approvalService->recordApprovalDecision(
          $this->currentApproval,
          Auth::user(),
          $this->approvalComments
        );
        session()->flash('success', __('Approved successfully.'));
      } elseif ($this->approvalDecision === 'rejected') {
        $approvalService->recordRejectionDecision(
          $this->currentApproval,
          Auth::user(),
          $this->approvalComments
        );
        session()->flash('success', __('Rejected successfully.'));
      } else {
        session()->flash('error', __('Invalid approval decision.'));
        Log::error("ApprovalDashboard: Invalid approval decision state for Approval ID: " . $this->currentApproval->id . ". Decision: " . $this->approvalDecision, ['user_id' => Auth::id()]);
      }

      $this->dispatch('toastr', type: 'success', message: session()->get('success') ?? __('Processing Complete!'));
    } catch (\Exception $e) {
      Log::error("ApprovalDashboard: Approval processing failed for Approval ID: " . $this->currentApproval->id . ". Error: " . $e->getMessage(), ['user_id' => Auth::id()]);
      session()->flash('error', __('An unexpected error occurred while processing the approval.'));
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
    }

    $this->closeApprovalModal();
    $this->dispatch('$refresh');
  }

  /**
   * Method to view details of the related application in a dedicated page.
   *
   * @param int $approvalId The ID of the pending Approval record.
   * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
   */
  public function viewDetails(int $approvalId): RedirectResponse
  {
    try {
      $approval = Approval::where('id', $approvalId)
        ->where('officer_id', Auth::id())
        ->with('approvable')
        ->firstOrFail();
    } catch (ModelNotFoundException $e) {
      Log::warning('ApprovalDashboard: Attempted to view details for unassigned or non-existent approval task.', [
        'approval_id' => $approvalId,
        'user_id' => Auth::id()
      ]);
      session()->flash('error', __('Approval task not found or not assigned to you.'));
      return redirect()->route('approvals');
    }

    try {
      $this->authorize('view', $approval);
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      Log::warning('ApprovalDashboard: Authorization failed during viewDetails.', [
        'approval_id' => $approval->id,
        'user_id' => Auth::id()
      ]);
      session()->flash('error', __('You are not authorized to view details for this approval task.'));
      return redirect()->route('approvals');
    }

    $approvable = $approval->approvable;
    $routeName = null;
    $routeParameters = [];

    if ($approvable instanceof EmailApplication) {
      $routeName = 'email-applications.show';
      $routeParameters = ['emailApplication' => $approvable->id];
    } elseif ($approvable instanceof LoanApplication) {
      $routeName = 'loan-applications.show';
      $routeParameters = ['loanApplication' => $approvable->id];
    } else {
      // Handle cases where approvable type is unknown or route logic fails BEFORE redirecting
      Log::error("ApprovalDashboard: Could not determine details page route or approvable is missing for Approval ID: " . $approvalId, ['approvable_type' => get_class($approvable), 'user_id' => Auth::id()]);
      session()->flash('error', __('Could not determine details page for this application type.'));
      return redirect()->route('approvals'); // Redirect on unknown type
    }

    try {
      // Use the determined route name and parameters
      return redirect()->route($routeName, $routeParameters);
    } catch (RouteNotFoundException $e) { // Using Symfony's RouteNotFoundException
      // Catch error if route name is not defined
      Log::error("ApprovalDashboard: Route not found for approvable type: " . get_class($approvable) . " for Approval ID: " . $approvalId, ['route_name' => $routeName, 'exception' => $e, 'user_id' => Auth::id()]);
      session()->flash('error', __('Details page route not defined for this application type.'));
      return redirect()->route('approvals'); // Redirect on route not found
    }
  }

  // Method to update filters and reset pagination
  public function updatedFilterType(): void
  {
    $this->resetPage();
    // Livewire re-fetches data automatically on state change
  }

  public function updatedFilterStatus(): void
  {
    $this->resetPage();
    // Livewire re-fetches data automatically on state change
  }

  // ... other methods ...
}
