<?php

namespace App\Http\Controllers; // Ensure the namespace is correct for your project. Consider App\Http\Controllers\Admin if approvals are an admin function.

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Models\Approval; // Import the Approval model
use App\Models\EmailApplication; // Import if needed for type hinting or checks (approvable)
use App\Models\LoanApplication; // Import if needed for type hinting or checks (approvable)
use App\Services\ApprovalService; // Import the ApprovalService
use Illuminate\Http\Request; // Import the Request object
use Illuminate\Support\Facades\Auth; // Import Auth facade for the authenticated user
use Illuminate\Support\Facades\Gate; // Import Gate if needed (Policies are preferred)

// Import the Rule class for validation rules
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Exception; // Import Exception for general errors
use Illuminate\Database\Eloquent\ModelNotFoundException; // Import ModelNotFoundException (less likely with route model binding but good to keep)
// Fix: Import RedirectResponse from the correct namespace
use Illuminate\Http\RedirectResponse;


class ApprovalController extends Controller
{
  protected $approvalService;

  /**
   * Inject the ApprovalService into the constructor and apply middleware/authorization.
   *
   * @param \App\Services\ApprovalService $approvalService The approval service instance.
   */
  public function __construct(ApprovalService $approvalService)
  {
    // Apply authentication middleware to all methods in this controller
    $this->middleware('auth');

    // Apply authorization policy checks automatically for resource methods (index, show, update)
    // Assumes an ApprovalPolicy exists and is registered.
    $this->authorizeResource(Approval::class, 'approval');

    $this->approvalService = $approvalService;
  }

  /**
   * Display a listing of the pending approvals for the authenticated user.
   * This method serves as the backend data source or the main list view for pending approvals.
   *
   * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
   */
  public function index()
  {
    // Authorization handled by authorizeResource in the constructor ('viewAny')

    // Fetch pending approvals specifically assigned to the authenticated officer (Auth::id())
    // and where the status is 'pending'.
    // Eager load the related 'approvable' model (polymorphic) and its user relationship for displaying details.
    $pendingApprovals = Approval::with('approvable', 'approvable.user') // Eager load approvable and its user
      ->where('officer_id', Auth::id()) // Filter by the current authenticated officer
      ->where('status', 'pending') // Filter by pending status
      ->latest() // Order by latest approval record
      ->paginate(10); // Paginate the results for larger datasets


    // Return the view with the list of pending approvals
    // Ensure your view file name matches: resources/views/approvals/index.blade.php
    // This view should display the list of $pendingApprovals
    return view('approvals.index', compact('pendingApprovals'));

    // If this method is primarily for API data, you could return JSON:
    // return response()->json($pendingApprovals);
  }

  /**
   * Display the specified approval record.
   *
   * @param  \App\Models\Approval  $approval  The approval instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function show(Approval $approval) // Use route model binding
  {
    // Authorization handled by authorizeResource in the constructor ('view' on the specific $approval)

    // Eager load related data needed for the show view:
    // - The approvable model (EmailApplication or LoanApplication)
    // - The user associated with the approvable model (the applicant)
    // - The officer assigned to this specific approval record
    $approval->load(['approvable', 'approvable.user', 'officer']);

    // Return the view to show approval details
    // Ensure your view file name matches: resources/views/approvals/show.blade.php
    return view('approvals.show', compact('approval'));
  }

  /**
   * Update the specified approval record (to approve or reject it).
   * This method handles the POST/PUT request from the approval form.
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request containing the decision ('approve' or 'reject') and optional comments.
   * @param  \App\Models\Approval  $approval  The approval instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(Request $request, Approval $approval): RedirectResponse // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('update' on the specific $approval).
    // The policy's 'update' method should verify:
    // 1. The authenticated user is the assigned officer for this approval ($approval->officer_id == Auth::id()).
    // 2. The approval status is still 'pending' ($approval->status == 'pending').

    // Log the approval decision attempt
    Log::info('Attempting to record approval decision.', [
      'approval_id' => $approval->id,
      'officer_id' => Auth::id(),
      'decision_attempt' => $request->action,
      'approvable_type' => $approval->approvable_type,
      'approvable_id' => $approval->approvable_id,
      'current_status' => $approval->status,
      'ip_address' => $request->ip(),
    ]);

    // 1. Validate the incoming request data
    $request->validate([
      'action' => ['required', Rule::in(['approve', 'reject'])], // Must be 'approve' or 'reject'
      'comments' => 'nullable|string|max:500', // Optional comments with max length
    ]);

    // Prevent decision if the approval is no longer pending (e.g., processed by another officer concurrently)
    if ($approval->status !== 'pending') {
      Log::warning('Attempted to record decision on non-pending approval.', [
        'approval_id' => $approval->id,
        'officer_id' => Auth::id(),
        'current_status' => $approval->status,
        'ip_address' => $request->ip(),
      ]);
      // Changed message to Malay and redirect to index
      return redirect()->route('approvals')->with('error', 'Keputusan tidak dapat direkodkan kerana kelulusan ini tidak lagi berstatus menunggu.'); // Malay error message
    }

    // 2. Call the appropriate method on the ApprovalService based on the decision
    // The service methods should handle:
    // - Updating the Approval record (status, comments, timestamp).
    // - Updating the status of the related approvable model (EmailApplication or LoanApplication).
    // - Potentially triggering subsequent actions (e.g., notifying applicant, triggering IT provisioning, updating loan stock).
    // - Handling potential database transactions or errors during the process.
    try {
      $officer = Auth::user(); // Get the authenticated officer user model

      if ($request->action === 'approve') {
        // Pass the approval record, the deciding officer user, and comments to the service
        $this->approvalService->recordApprovalDecision($approval, $officer, $request->comments);
        // Log successful approval decision
        Log::info('Approval decision recorded as APPROVED.', [
          'approval_id' => $approval->id,
          'officer_id' => $officer->id,
          'approvable_type' => $approval->approvable_type,
          'approvable_id' => $approval->approvable_id,
        ]);
      } else { // action is 'reject'
        // Pass the approval record, the deciding officer user, and comments to the service
        $this->approvalService->recordRejectionDecision($approval, $officer, $request->comments);
        // Log successful rejection decision
        Log::info('Approval decision recorded as REJECTED.', [
          'approval_id' => $approval->id,
          'officer_id' => $officer->id,
          'approvable_type' => $approval->approvable_type,
          'approvable_id' => $approval->approvable_id,
          'comments' => $request->comments, // Log comments for rejection
        ]);
      }

      // 3. Redirect to the approval index page (likely the dashboard) with a success message
      // Changed message to Malay
      return redirect()->route('approvals')->with('success', 'Keputusan kelulusan berjaya direkodkan.'); // Malay success message

    } catch (Exception $e) {
      // Log any exceptions thrown by the service or during the process
      Log::error('An error occurred while recording approval decision.', [
        'approval_id' => $approval->id,
        'officer_id' => Auth::id(),
        'decision_attempt' => $request->action,
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
      ]);

      // Redirect back to the approval show page with an error message
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal merekodkan keputusan kelulusan disebabkan ralat: ' . $e->getMessage()); // Malay error message
      // Alternatively, redirect to the index page:
      // return redirect()->route('approvals')->with('error', 'Gagal merekodkan keputusan kelulusan.');

    }

    // Example alternative redirect to the specific approvable item's show page:
    // After the try-catch block (or inside the try block after successful service call)
    // $approvable = $approval->fresh()->approvable; // Re-fetch approvable to get updated status
    // if ($approvable instanceof EmailApplication) {
    //     return redirect()->route('email-applications.show', $approvable)->with('success', 'Keputusan kelulusan berjaya direkodkan.');
    // } elseif ($approvable instanceof LoanApplication) {
    //     return redirect()->route('loan-applications.show', $approvable)->with('success', 'Keputusan kelulusan berjaya direkodkan.');
    // } else {
    //     // Fallback if approvable type is unexpected
    //     return redirect()->route('approvals')->with('success', 'Keputusan kelulusan berjaya direkodkan.');
    // }
  }

  // The create, store, edit, and destroy methods are typically not needed for this controller
  // as approvals are usually created/managed by the application workflow itself.
  // The commented-out methods from the original code are omitted here for brevity.
}
