<?php

namespace App\Http\Controllers; // Ensure the namespace is correct for your project

use App\Models\LoanApplication; // Import LoanApplication model
use App\Models\Equipment; // Needed for BPM actions potentially
use App\Models\LoanTransaction; // Needed for BPM actions potentially
use Illuminate\Http\Request; // Standard Request object
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Support\Facades\Gate; // Import Gate if needed (Policies are preferred with $this->authorize)
use App\Services\LoanApplicationService; // Import LoanApplicationService
use App\Services\ApprovalService; // Import ApprovalService if controller handles approval actions

// Import the Rule class for validation
use Illuminate\Validation\Rule;

// Import Form Requests if you plan to use standard forms for store/update
// use App\Http\Requests\StoreLoanApplicationRequest;
// use App\Http\Requests\UpdateLoanApplicationRequest;


class LoanApplicationController extends Controller
{
  protected $loanApplicationService;
  protected $approvalService; // Inject ApprovalService if this controller handles approvals

  // Inject services into the constructor
  // Added nullable type hint '?' for $approvalService parameter
  public function __construct(LoanApplicationService $loanApplicationService, ?ApprovalService $approvalService = null) // Corrected signature
  {
    // Apply authentication middleware to all methods in this controller
    $this->middleware('auth');

    $this->loanApplicationService = $loanApplicationService;
    $this->approvalService = $approvalService; // Assign injected service
  }

  /**
   * Display a listing of the loan applications.
   */
  public function index()
  {
    // Authorize if the user can view any loan applications (using a Policy)
    // This policy will filter the list based on user roles/permissions (e.g., user sees own, admin sees all).
    $this->authorize('viewAny', LoanApplication::class); // Assuming a LoanApplicationPolicy exists

    // Fetch loan applications the current user is authorized to view.
    // The policy's 'viewAny' method should ideally handle filtering based on roles/permissions.
    $applications = LoanApplication::query()
      ->with(['user', 'responsibleOfficer', 'items']) // Eager load related data for the index list
      ->latest(); // Order by latest

    // Apply the 'viewAny' policy scope if it exists in your policy.
    // If not using policy scopes, manually filter here based on user roles/permissions:
    // Example manual filtering:
    if (!Auth::user()->can('viewAllLoanApplications')) { // Assuming 'viewAllLoanApplications' permission for admin/approvers
      $applications->where('user_id', Auth::id()); // Users only see their own applications
    }


    $applications = $applications->paginate(10); // Paginate the results

    // Return the view with the list of applications
    // Ensure your view file name matches: resources/views/loan-applications/index.blade.php
    return view('loan-applications.index', compact('applications'));
  }

  /**
   * Show the form for creating a new resource.
   * This method is redundant as the route is handled by a Livewire component.
   */
  public function create()
  {
    // Redirect to the Livewire form page
    return redirect()->route('request-loan'); // Assuming 'request-loan' is the named route for the Livewire form
  }

  /**
   * Store a newly created resource in storage.
   * This method is redundant as form submission is likely handled by the Livewire component.
   */
  public function store(Request $request) // Use StoreLoanApplicationRequest if using standard form
  {
    // Form submission is likely handled within the Livewire component itself
    // If you have a non-Livewire form posting to this route, implement storing logic here:
    // $this->authorize('create', LoanApplication::class); // Check policy
    // $validatedData = $request->validated(); // Use $request->validated() if using a Form Request
    // $itemsData = $request->input('items', []); // Get items data from the form

    // // Use the LoanApplicationService to create the application
    // // Ensure createApplication method exists in service and accepts these arguments
    // $application = $this->loanApplicationService->createApplication(Auth::user(), $validatedData, $itemsData);

    // // Redirect to the 'show' route for the newly created application
    // return redirect()->route('loan-applications.show', $application)->with('success', 'Loan request created successfully!');

    // If this method is never used, you can remove its body or return an error.
    abort(405, 'Method Not Allowed - Use Livewire component for creation'); // Or similar error/redirect
  }

  /**
   * Display the specified resource.
   */
  public function show(LoanApplication $loanApplication) // Route model binding
  {
    // Authorize if the user can view this specific application (using a Policy)
    $this->authorize('view', $loanApplication); // Assumes the policy handles ownership/permissions

    // Eager load relationships needed for the show view (user, responsible officer, items, transactions, etc.)
    $loanApplication->load([
      'user.department',
      'user.position', // Assuming 'position' is the relationship name in User model
      'user.grade',
      'responsibleOfficer', // Assuming responsibleOfficer relationship exists
      'items',
      'transactions.equipment', // Eager load equipment on transactions
      'transactions.issuingOfficer', // Eager load issuing officer on transactions
      'transactions.receivingOfficer', // Eager load receiving officer on transactions
      'transactions.returningOfficer', // Eager load returning officer on transactions
      'transactions.returnAcceptingOfficer', // Eager load return accepting officer
      'approvals.officer' // Eager load approvals and the approving officer
    ]);

    // Return the view to show application details
    // Ensure your view file name matches: resources/views/loan-applications/show.blade.php
    return view('loan-applications.show', compact('loanApplication'));
  }

  /**
   * Show the form for editing the specified resource.
   * This method is likely redundant if editing is handled via a Livewire component.
   * (Placeholder - implement if needed)
   */
  public function edit(LoanApplication $loanApplication)
  {
    // Authorize if the user can update this application
    $this->authorize('update', $loanApplication); // Assumes the policy handles permissions

    // If editing happens via the LoanRequestForm Livewire component mounted with an application ID,
    // you might redirect to that component's route:
    return redirect()->route('request-loan', ['loanApplication' => $loanApplication->id]); // Example redirect to Livewire mount

    // If you have a non-Livewire edit form, load necessary data and return the view:
    // Load data needed for the form (e.g., potential responsible officers)
    // $responsibleOfficers = User::whereHasRole('responsible_officer')->get(); // Example fetching users with a specific role
    // $availableEquipment = Equipment::where('status', 'available')->get(); // Example fetching available equipment
    // return view('loan-applications.edit', compact('loanApplication', 'responsibleOfficers', 'availableEquipment'));
  }

  /**
   * Update the specified resource in storage.
   * This method is likely redundant if updating is handled via a Livewire component.
   * (Placeholder - implement if needed)
   */
  public function update(Request $request, LoanApplication $loanApplication) // Use UpdateLoanApplicationRequest if using standard form
  {
    // Authorize if the user can update this application
    $this->authorize('update', $loanApplication); // Assumes the policy handles permissions

    // If updating happens via Livewire, this method might not be used.
    // If using a non-Livewire form:
    // $validatedData = $request->validated(); // Use $request->validated() if using a Form Request
    // $itemsData = $request->input('items', []); // Get items data

    // // Use the LoanApplicationService to update the application
    // // Ensure updateApplication method exists in service and accepts these arguments
    // $updated = $this->loanApplicationService->updateApplication($loanApplication, $validatedData, $itemsData);

    // // Redirect to the 'show' page with a success message
    // if ($updated) {
    //     return redirect()->route('loan-applications.show', $loanApplication)->with('success', 'Loan request updated successfully!');
    // } else {
    //      return redirect()->back()->with('error', 'Failed to update loan request.');
    // }

    // If this method is never used, you can remove its body or return an error.
    abort(405, 'Method Not Allowed - Use Livewire component for updates'); // Or similar error/redirect
  }

  /**
   * Remove the specified resource from storage.
   * (Placeholder - implement if needed)
   */
  public function destroy(LoanApplication $loanApplication)
  {
    // Authorize if the user can delete this application
    $this->authorize('delete', $loanApplication); // Assumes the policy handles permissions

    // Use the LoanApplicationService to handle deletion if needed
    // $deleted = $this->loanApplicationService->deleteApplication($loanApplication); // Ensure deleteApplication exists in service

    // Or delete directly:
    $loanApplication->delete();

    // Redirect to the index page with a success message
    return redirect()->route('loan-applications.index')->with('success', 'Loan request deleted successfully!');
  }


  // Methods for specific workflow actions handled by BPM staff

  /**
   * Show the form to issue equipment for an approved application.
   * This might be a standard Blade view or redirect to a Livewire component.
   */
  public function issueEquipmentForm(LoanApplication $loanApplication)
  {
    // Authorize if the user can access the issuance form for this application
    // This policy check might be redundant if done via route middleware ('can:issue,loanApplication')
    // but good practice to have it in the controller method as well.
    $this->authorize('issue', $loanApplication); // Assuming a policy action 'issue'

    // Eager load necessary relationships for the form
    $loanApplication->load(['user', 'items.equipment']);

    // Load available equipment if needed for selection (optional, might be determined earlier)
    // $availableEquipment = Equipment::where('status', 'available')->get();

    // Return the view for the issuance form
    // Ensure your view file name matches: resources/views/loan-applications/issue.blade.php
    return view('loan-applications.issue', compact('loanApplication')); // Pass application and potential equipment data
  }


  /**
   * Process the issuance of equipment for an approved application.
   * Called when the BPM staff submits the issuance form.
   */
  public function issueEquipment(LoanApplication $loanApplication, Request $request)
  {
    // Authorize if the user can perform the issuance action
    // This policy check might be redundant if done via route middleware, but good practice here.
    $this->authorize('issue', $loanApplication); // Assuming a policy action 'issue'

    // 1. Validate input (e.g., selected equipment ID, accessories, notes, receiving officer)
    // You might create a dedicated Form Request for this (e.g., IssueEquipmentRequest)
    $validatedData = $request->validate([
      'equipment_id' => 'required|exists:equipment,id', // Validate selected equipment
      'accessories' => 'nullable|string|max:500', // Accessories checklist/notes
      'notes' => 'nullable|string', // Any additional notes for issuance
      'receiving_user_id' => 'required|exists:users,id', // The user receiving the equipment (corrected field name)
      // Add validation for any other fields collected in the form
    ]);

    // Find the selected equipment
    $equipment = Equipment::findOrFail($validatedData['equipment_id']);

    // Ensure the selected equipment is one of the items requested in the application
    // and that it is currently 'available'.
    if (!$loanApplication->items->contains('equipment_id', $equipment->id) || $equipment->status !== 'available') {
      // Abort or redirect with error if invalid equipment is selected
      return redirect()->back()->with('error', 'Invalid equipment selected for issuance.');
    }

    // Use the LoanApplicationService to record the issuance transaction and update statuses
    // Ensure issueEquipment method exists in service and accepts these arguments
    $transaction = $this->loanApplicationService->issueEquipment($loanApplication, $equipment, $validatedData['accessories'], $validatedData['notes'], $validatedData['receiving_user_id'], Auth::user()); // Pass validated data and issuing officer (Auth::user())


    // Redirect to the loan application show page or the transaction show page
    return redirect()->route('loan-applications.show', $loanApplication)->with('success', 'Equipment issued successfully!');
    // Or redirect to transaction show: return redirect()->route('loan-transactions.show', $transaction)->with('success', 'Equipment issued successfully!');
  }

  /**
   * Show the form to process the return of equipment.
   * This route should typically bind the LoanTransaction model.
   */
  public function returnEquipmentForm(LoanTransaction $transaction) // Correct method signature to bind Transaction
  {
    // Authorize if the user can access the return form for this transaction
    $this->authorize('processReturn', $transaction); // Assuming a policy action 'processReturn'

    // Eager load necessary relationships for the form
    $transaction->load(['loanApplication.user', 'equipment', 'issuingOfficer', 'receivingOfficer']); // Load relevant relationships

    // Return the view for the return form
    // Ensure your view file name matches: resources/views/loan-applications/return.blade.php
    return view('loan-applications.return', compact('transaction')); // Pass the transaction data
  }


  /**
   * Process the return of equipment.
   * Called when the BPM staff submits the return form.
   * This route should typically bind the LoanTransaction model.
   */
  public function processReturn(LoanTransaction $transaction, Request $request) // Correct method signature to bind Transaction
  {
    // Authorize if the user can perform the return action
    $this->authorize('processReturn', $transaction); // Assuming a policy action 'processReturn'

    // 1. Validate input for return (e.g., accessories checklist, return notes, final status)
    // You might create a dedicated Form Request for this (e.g., ProcessReturnRequest)
    $validatedData = $request->validate([
      'accessories_on_return' => 'nullable|string|max:500', // Accessories checklist/notes on return
      'return_notes' => 'nullable|string', // Any additional notes for return
      'equipment_condition' => ['required', Rule::in(['good', 'damaged', 'needs_repair'])], // Condition of equipment on return
      // You might have a field for the Return Accepting Officer if different from the current user
      // 'return_accepting_user_id' => 'required|exists:users,id', // The user accepting the return (using user_id naming)
    ]);

    // Add the current authenticated user as the return accepting officer
    $validatedData['return_accepting_user_id'] = Auth::id(); // Assuming column name is return_accepting_user_id

    // Use the LoanApplicationService to process the return transaction and update statuses
    // Ensure processReturn method exists in service and accepts these arguments
    $returnedTransaction = $this->loanApplicationService->processReturn($transaction, $validatedData['accessories_on_return'], $validatedData['return_notes'], $validatedData['equipment_condition'], $validatedData['return_accepting_user_id'], Auth::user()); // Pass transaction, validated data, and returning officer (Auth::user())


    // Redirect to the transaction show page or the loan application show page
    return redirect()->route('loan-transactions.show', $transaction)->with('success', 'Equipment return processed successfully!');
    // Or redirect to application show: return redirect()->route('loan-applications.show', $transaction->loanApplication)->with('success', 'Equipment return processed successfully!');
  }


  // Placeholder methods for approval/rejection actions if handled by this controller (alternative to Livewire Approval Dashboard)
  /**
   * Approve a loan application.
   */
  // public function approve(LoanApplication $loanApplication, ApprovalService $approvalService)
  // {
  //     // Authorize if the user can approve this application (using Policy check on grade/role)
  //     $this->authorize('approve', $loanApplication);

  //     // Use the ApprovalService to record the approval
  //     // Ensure recordApproval method exists in ApprovalService and updates application status
  //     $approval = $this->approvalService->recordApproval($loanApplication, Auth::user());

  //     // Trigger notification, next step in workflow (e.g., making available for issuance)
  //     // ...

  //     return redirect()->route('loan-applications.show', $loanApplication)->with('success', 'Loan application approved.');
  // }

  /**
   * Reject a loan application.
   */
  // public function reject(LoanApplication $loanApplication, Request $request, ApprovalService $approvalService)
  // {
  //     // Authorize if the user can reject this application (using Policy check on grade/role)
  //     $this->authorize('reject', $loanApplication);

  //     // Validate rejection reason
  //     $validatedData = $request->validate(['rejection_reason' => 'required|string|max:500']);

  //     // Use the ApprovalService to record the rejection
  //     // Ensure recordRejection method exists in ApprovalService and updates application status
  //     $approval = $this->approvalService->recordRejection($loanApplication, Auth::user(), $validatedData['rejection_reason']);

  //     // Trigger notification
  //     // ...

  //     return redirect()->route('loan-applications.show', $loanApplication)->with('success', 'Loan application rejected.');
  // }
}
