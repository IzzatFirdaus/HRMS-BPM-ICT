<?php

namespace App\Http\Controllers;

use App\Models\LoanApplication;
use App\Models\Equipment; // Still needed for potential forms/data loading if relevant to application view
use App\Models\LoanTransaction; // Still needed for relationships loading in show/index
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Services\LoanApplicationService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
// No longer need IssueEquipmentRequest or ProcessReturnRequest here as methods are moved
// use App\Http\Requests\IssueEquipmentRequest;
// use App\Http\Requests\ProcessReturnRequest;

class LoanApplicationController extends Controller
{
  protected $loanApplicationService;

  public function __construct(LoanApplicationService $loanApplicationService)
  {
    $this->middleware('auth');
    // Authorize standard resource actions
    $this->authorizeResource(LoanApplication::class, 'loan_application', [
      'only' => ['index', 'show', 'destroy'] // Keeping index, show, destroy
    ]);

    $this->loanApplicationService = $loanApplicationService;
  }

  /**
   * Display a listing of the loan applications.
   *
   * @return \Illuminate\View\View
   */
  public function index(): \Illuminate\View\View
  {
    Log::info('Viewing loan applications index.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Fetch applications with necessary relationships for the index view
    $applications = LoanApplication::query()
      ->with(['user', 'responsibleOfficer', 'items'])
      ->latest()
      ->paginate(10);

    return view('loan-applications.index', compact('applications'));
  }

  /**
   * Display the specified loan application.
   *
   * @param  \App\Models\LoanApplication  $loanApplication The loan application to display.
   * @return \Illuminate\View\View
   */
  public function show(LoanApplication $loanApplication): \Illuminate\View\View
  {
    Log::info('Viewing loan application details.', [
      'application_id' => $loanApplication->id,
      'user_id' => Auth::id(),
    ]);

    // Load relationships needed for the show view
    $loanApplication->load([
      'user.department',
      'user.position',
      'user.grade',
      'responsibleOfficer',
      'items.equipment', // Assuming items can link to specific equipment if approved/issued
      'transactions.equipment', // Load equipment related to transactions
      'transactions.issuingOfficer',
      'transactions.receivingOfficer',
      'transactions.returningOfficer',
      'transactions.returnAcceptingOfficer',
      'approvals.officer'
    ]);

    return view('loan-applications.show', compact('loanApplication'));
  }

  /**
   * Remove the specified loan application from storage.
   * Only allowed if the application is in 'draft' or 'rejected' status.
   *
   * @param  \App\Models\LoanApplication  $loanApplication The loan application to delete.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(LoanApplication $loanApplication): \Illuminate\Http\RedirectResponse
  {
    // Policy check is done by authorizeResource middleware
    Log::info('Attempting to delete loan application.', [
      'application_id' => $loanApplication->id,
      'user_id' => Auth::id(),
      'current_status' => $loanApplication->status,
      'ip_address' => request()->ip(),
    ]);

    // Additional manual check on status for safety
    $deletableStatuses = [LoanApplication::STATUS_DRAFT, LoanApplication::STATUS_REJECTED]; // Use constants
    if (!in_array($loanApplication->status, $deletableStatuses)) {
      Log::warning('Attempted to delete loan application in non-deletable status.', [
        'application_id' => $loanApplication->id,
        'user_id' => Auth::id(),
        'current_status' => $loanApplication->status,
      ]);
      return redirect()->back()->with('error', 'Permohonan pinjaman tidak dapat dibuang kerana statusnya bukan "draf" atau "ditolak".');
    }

    try {
      $loanApplicationId = $loanApplication->id;
      // Assuming the service handles deletion logic if needed, otherwise direct delete is fine after checks
      $loanApplication->delete();

      Log::info('Loan application deleted successfully.', [
        'application_id' => $loanApplicationId,
        'user_id' => Auth::id(),
      ]);

      return redirect()->route('loan-applications.index')
        ->with('success', 'Permohonan pinjaman berjaya dibuang.');
    } catch (Exception $e) {
      Log::error('Error deleting loan application.', [
        'application_id' => $loanApplication->id ?? 'unknown',
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => request()->ip(),
      ]);
      return redirect()->back()->with('error', 'Gagal membuang permohonan pinjaman disebabkan ralat: ' . $e->getMessage());
    }
  }

  // Removed issueEquipmentForm and issueEquipment methods - they belong in LoanTransactionController
  // Removed returnEquipmentForm and processReturn methods - they belong in LoanTransactionController

  // Add other methods relevant to the LoanApplication lifecycle (create, store, edit, update if needed)

  // Example stubs for create/store if you manage creation via this controller
  // public function create(): \Illuminate\View\View { ... }
  // public function store(StoreLoanApplicationRequest $request): \Illuminate\Http\RedirectResponse { ... }
  // public function edit(LoanApplication $loanApplication): \Illuminate\View\View { ... }
  // public function update(UpdateLoanApplicationRequest $request, LoanApplication $loanApplication): \Illuminate\Http\RedirectResponse { ... }


  // Note: Approval/rejection actions are handled by ApprovalController.
}
