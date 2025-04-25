<?php

namespace App\Http\Controllers; // Ensure the namespace is correct for your project

use App\Models\Approval; // Import the Approval model
use App\Models\EmailApplication; // Import if needed for type hinting or checks (approvable)
use App\Models\LoanApplication; // Import if needed for type hinting or checks (approvable)
use App\Services\ApprovalService; // Import the ApprovalService
use Illuminate\Http\Request; // Import the Request object
use Illuminate\Support\Facades\Auth; // Import Auth facade for the authenticated user
use Illuminate\Support\Facades\Gate; // Import Gate if needed (Policies are preferred with $this->authorize)

// Import the Rule class for validation rules
use Illuminate\Validation\Rule;


class ApprovalController extends Controller
{
  protected $approvalService;

  // Inject the ApprovalService into the constructor
  public function __construct(ApprovalService $approvalService)
  {
    // Apply authentication middleware to all methods in this controller
    $this->middleware('auth');

    $this->approvalService = $approvalService;
  }

  /**
   * Display a listing of the pending approvals for the authenticated user.
   * Note: The main display is likely handled by the ApprovalDashboard Livewire component.
   * This method could serve as an API endpoint for the dashboard data or an alternative list view.
   *
   * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
   */
  public function index()
  {
    // Authorize if the user can view *any* approvals (this applies to the list itself)
    // The policy's 'viewAny' method should handle filtering based on roles/permissions.
    $this->authorize('viewAny', Approval::class); // Assuming an ApprovalPolicy exists

    // Fetch pending approvals specifically assigned to the authenticated officer
    // and where the status is 'pending'. Eager load the related 'approvable' model
    // and its user relationship for displaying details.
    $pendingApprovals = Approval::with('approvable', 'approvable.user') // Eager load approvable and its user
      ->where('officer_id', Auth::id()) // Filter by the current officer
      ->where('status', 'pending') // Filter by pending status
      ->latest() // Order by latest
      ->get(); // Get the results

    // You might paginate this list for larger datasets:
    // $pendingApprovals = Approval::with('approvable', 'approvable.user')
    //     ->where('officer_id', Auth::id())
    //     ->where('status', 'pending')
    //     ->latest()
    //     ->paginate(10);


    // Return the view with the list of pending approvals
    // Ensure your view file name matches: resources/views/approvals/index.blade.php
    // This view should display the list of $pendingApprovals
    return view('approvals.index', compact('pendingApprovals'));

    // If this method is primarily for API data:
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
    // Authorize if the user can view this specific approval record
    // The policy's 'view' method should handle if the user is the assigned officer, admin, etc.
    $this->authorize('view', $approval); // Assuming an ApprovalPolicy exists

    // Eager load related data needed for the show view (e.g., the approvable model and its user)
    $approval->load(['approvable', 'approvable.user', 'officer']); // Load approvable, its user, and the assigned officer

    // Return the view to show approval details
    // Ensure your view file name matches: resources/views/approvals/show.blade.php
    return view('approvals.show', compact('approval'));
  }

  /**
   * Update the specified approval record (to approve or reject it).
   *
   * @param  \Illuminate\Http\Request  $request  The incoming request containing the decision and comments.
   * @param  \App\Models\Approval  $approval  The approval instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(Request $request, Approval $approval) // Use route model binding
  {
    // Authorize if the user can update (approve/reject) this specific approval record.
    // The policy's 'update' method should check if the user is the assigned officer AND if the status is 'pending'.
    $this->authorize('update', $approval); // Assuming an ApprovalPolicy exists

    // 1. Validate the incoming request data
    $request->validate([
      'action' => ['required', Rule::in(['approve', 'reject'])], // Validate the decision action
      'comments' => 'nullable|string|max:500', // Validate optional comments
    ]);

    // 2. Get the related 'approvable' model instance (EmailApplication or LoanApplication)
    // This variable isn't strictly needed for the service calls now, but might be useful for redirect logic
    $approvable = $approval->approvable;

    // 3. Call the appropriate method on the ApprovalService based on the decision
    // Use the new method names: recordApprovalDecision and recordRejectionDecision
    if ($request->action === 'approve') {
      // Pass the approval record, the deciding officer (Auth::user()), and comments to the service
      // Corrected method call
      $this->approvalService->recordApprovalDecision($approval, Auth::user(), $request->comments);
    } else { // action is 'reject'
      // Pass the approval record, the deciding officer (Auth::user()), and comments to the service
      // Corrected method call
      $this->approvalService->recordRejectionDecision($approval, Auth::user(), $request->comments);
    }

    // 4. Redirect to the approval index page or the approvable item's show page with a success message
    // Redirecting back to the approval index (which is the Livewire dashboard) is common.
    return redirect()->route('approvals')->with('success', 'Decision recorded successfully.'); // Redirect to the Approval Dashboard route

    // Alternatively, redirect to the specific approvable item's show page:
    // if ($approvable instanceof EmailApplication) {
    //     return redirect()->route('email-applications.show', $approvable)->with('success', 'Decision recorded.');
    // } elseif ($approvable instanceof LoanApplication) {
    //     return redirect()->route('loan-applications.show', $approvable)->with('success', 'Decision recorded.');
    // }
  }

  /**
   * Show the form for creating a new resource. (Not typically used for approvals)
   */
  // public function create() { /* ... */ }

  /**
   * Store a newly created resource in storage. (Not typically used, approvals are created via workflow)
   */
  // public function store(Request $request) { /* ... */ }

  /**
   * Show the form for editing the specified resource. (Not typically used for approvals after creation)
   */
  // public function edit(Approval $approval) { /* ... */ }

  /**
   * Remove the specified resource from storage. (Deletion of approvals might be restricted)
   */
  // public function destroy(Approval $approval) { /* ... */ }
}
