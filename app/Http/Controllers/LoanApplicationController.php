<?php

namespace App\Http\Controllers;

use App\Models\LoanApplication;
use App\Models\Equipment; // Needed for BPM actions potentially
use App\Models\LoanTransaction; // Needed for BPM actions potentially
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // For using policies manually if needed
use App\Services\LoanApplicationService; // Inject service
use App\Services\ApprovalService; // Inject service if controller handles approval actions


class LoanApplicationController extends Controller
{
  protected $loanApplicationService;
  protected $approvalService;

  public function __construct(LoanApplicationService $loanApplicationService, ApprovalService $approvalService)
  {
    $this->middleware('auth'); // Ensure user is authenticated for these routes
    $this->loanApplicationService = $loanApplicationService;
    $this->approvalService = $approvalService;
  }

  /**
   * Display a listing of the resource.
   */
  public function index()
  {
    // Example: Fetch applications the user can view
    // $applications = LoanApplication::where('user_id', Auth::id())->get();
    // if (Auth::user()->can('viewAny', LoanApplication::class)) { // Check policy
    //     $applications = LoanApplication::all(); // Or paginate
    // } else {
    //      $applications = LoanApplication::where('user_id', Auth::id())->get(); // Or paginate
    // }
    // return view('loan-applications.index', compact('applications'));

    // As per the route definition, Livewire components might handle viewing lists (e.g., ApprovalDashboard for approvers).
    // This controller method could redirect to Livewire or serve as API endpoint.
    return "Loan Applications Index Page (Under Development - likely handled by Livewire/specific dashboards)";
  }

  /**
   * Show the form for creating a new resource.
   */
  public function create()
  {
    // The route is handled by a Livewire component LoanRequestForm::class
    // Redirect to the Livewire form page
    return redirect()->route('request-loan'); // Assuming 'request-loan' is the named route for the Livewire form
  }

  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    // Form submission is likely handled within the Livewire component itself
    // If you have a non-Livewire form posting to this route, implement storing logic here
    // $this->authorize('create', LoanApplication::class); // Check policy
    // $validatedData = $request->validate([...]); // Validate main data
    // $itemsData = $request->input('items', []); // Get items data
    // $application = $this->loanApplicationService->createApplication(Auth::user(), $validatedData, $itemsData); // Use the service
    // return redirect()->route('loan-applications.show', $application)->with('success', 'Loan request created successfully!');
  }

  /**
   * Display the specified resource.
   */
  public function show(LoanApplication $loanApplication)
  {
    // $this->authorize('view', $loanApplication); // Check policy

    // Eager load relationships needed for the show view
    $loanApplication->load(['user.department', 'user.position', 'user.grade', 'responsibleOfficer', 'items', 'transactions.equipment', 'transactions.issuingOfficer', 'transactions.returnAcceptingOfficer']);

    // Return the view to show application details
    return view('loan-applications.show', compact('loanApplication'));
  }

  /**
   * Show the form for editing the specified resource.
   */
  public function edit(LoanApplication $loanApplication)
  {
    // $this->authorize('update', $loanApplication); // Check policy

    // If editing happens via the Livewire component mounted with an application ID,
    // this method might redirect.
    // return redirect()->route('request-loan', ['loanApplication' => $loanApplication]); // Example redirect to Livewire mount

    // If you have a non-Livewire edit form:
    // Load data needed for the form (e.g., potential responsible officers)
    // $responsibleOfficers = \App\Models\User::all(); // Simplified
    // return view('loan-applications.edit', compact('loanApplication', 'responsibleOfficers'));
  }

  /**
   * Update the specified resource in storage.
   */
  public function update(Request $request, LoanApplication $loanApplication)
  {
    // $this->authorize('update', $loanApplication); // Check policy

    // If updating happens via Livewire, this method might not be used.
    // If using a non-Livewire form:
    // $validatedData = $request->validate([...]);
    // $itemsData = $request->input('items', []);
    // $this->loanApplicationService->updateApplication($loanApplication, $validatedData, $itemsData); // Use the service
    // return redirect()->route('loan-applications.show', $loanApplication)->with('success', 'Loan request updated successfully!');
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(LoanApplication $loanApplication)
  {
    // $this->authorize('delete', $loanApplication); // Check policy

    // $loanApplication->delete();
    // return redirect()->route('loan-applications.index')->with('success', 'Loan request deleted successfully!');
  }


  // Add controller methods for specific workflow actions handled by BPM staff if not purely Livewire
  // Example: A method for BPM staff to issue equipment for an approved application
  public function issueEquipment(LoanApplication $loanApplication, Request $request)
  {
    // $this->authorize('issue', $loanApplication); // Check policy for issuing

    // Validate input (e.g., selected equipment ID, accessories, notes)
    // $request->validate([...]);
    // $equipmentId = $request->input('equipment_id');
    // $equipment = Equipment::findOrFail($equipmentId);
    // $transactionData = $request->only(['accessories', 'notes', 'receiving_officer_id']);

    // Use the LoanApplicationService to record the issuance transaction and update statuses
    // $transaction = $this->loanApplicationService->issueEquipment($loanApplication, $equipment, $transactionData, Auth::user());

    // Redirect or return response
    // return redirect()->route('loan-applications.show', $loanApplication)->with('success', 'Equipment issued successfully!');
  }

  // Example: A method for BPM staff to process the return of equipment
  public function processReturn(LoanApplication $loanApplication, LoanTransaction $transaction, Request $request)
  {
    // $this->authorize('processReturn', $loanApplication); // Check policy for processing return

    // Validate input for return (e.g., accessories checklist, return notes, final status)
    // $request->validate([...]);
    // $returnDetails = $request->only(['accessories', 'notes', 'returning_officer_id', 'status', 'equipment_status_on_return']);

    // Use the LoanApplicationService to process the return
    // $returnedTransaction = $this->loanApplicationService->processReturn($transaction, $returnDetails, Auth::user());

    // Redirect or return response
    // return redirect()->route('loan-applications.show', $loanApplication)->with('success', 'Equipment return processed successfully!');
  }


  // If controller handles approval/rejection actions (alternative to Livewire)
  // public function approve(LoanApplication $loanApplication) { $this->authorize('approve', $loanApplication); // Use ApprovalService }
  // public function reject(LoanApplication $loanApplication) { $this->authorize('reject', $loanApplication); // Use ApprovalService }
}
