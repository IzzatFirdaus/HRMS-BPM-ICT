<?php

namespace App\Http\Controllers; // Ensure the namespace is correct for your project. Consider App\Http\Controllers\Admin if transaction management is an admin function.

use App\Models\LoanTransaction; // Import LoanTransaction model
use App\Models\LoanApplication; // Needed for issuance logic (route model binding)
use App\Models\Equipment; // Needed for issuance logic
use App\Models\User; // Needed for return logic (returning officer)
use Illuminate\Http\Request; // Standard Request object
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Support\Facades\Gate; // Import Gate (less needed with Policies)
// Assuming a LoanApplicationService handles transaction creation/updates or is orchestrated via it
use App\Services\LoanApplicationService; // Import LoanApplicationService
// Assuming dedicated Form Requests for validation of BPM actions
use App\Http\Requests\IssueEquipmentRequest; // Import IssueEquipmentRequest Form Request
use App\Http\Requests\ProcessReturnRequest; // Import ProcessReturnRequest Form Request


use Illuminate\Validation\Rule; // Import Rule for validation rules
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Exception; // Import Exception for general errors
use Illuminate\Database\Eloquent\ModelNotFoundException; // Import ModelNotFoundException
use Illuminate\Validation\ValidationException; // Import ValidationException
use Illuminate\Http\RedirectResponse; // Import RedirectResponse for type hinting
use Illuminate\View\View; // Import View for type hinting


// This controller manages Loan Transaction records and handles specific BPM staff workflow actions
// related to equipment issuance and return.
class LoanTransactionController extends Controller
{
  protected $loanApplicationService; // Service for orchestrating loan application/transaction logic

  /**
   * Inject services and apply authentication/authorization middleware.
   *
   * @param \App\Services\LoanApplicationService $loanApplicationService The application service instance.
   */
  public function __construct(LoanApplicationService $loanApplicationService)
  {
    // Apply authentication middleware to all methods in this controller
    $this->middleware('auth');

    // Apply authorization policy checks automatically for standard resource methods (index, show)
    // Custom methods (issue, return) will have manual authorization checks or route middleware.
    // Assumes a LoanTransactionPolicy exists and is registered.
    // Policy methods: viewAny, view, issue, processReturn
    $this->authorizeResource(LoanTransaction::class, 'loan_transaction', [
      'only' => ['index', 'show'] // Apply authorizeResource only to index and show
    ]);


    $this->loanApplicationService = $loanApplicationService;
  }

  /**
   * Display a listing of the loan transactions.
   * Useful for BPM staff or admins to see all transactions.
   *
   * @return \Illuminate\View\View
   */
  public function index(): View // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('viewAny' on LoanTransaction).
    // The policy's 'viewAny' method should ideally handle filtering based on roles/permissions (e.g., BPM staff see all, others see none).

    // Log viewing loan transactions index
    Log::info('Viewing loan transactions index.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Fetch loan transactions with relationships, ordered by latest.
    $transactions = LoanTransaction::query()
      ->with(['loanApplication.user', 'equipment', 'issuingOfficer', 'receivingOfficer', 'returningOfficer', 'returnAcceptingOfficer']) // Eager load related data
      ->latest(); // Order by latest transaction


    // Apply the 'viewAny' policy scope if it exists in your policy to handle filtering.
    // If not using policy scopes, manual filtering might be necessary here based on user roles/permissions.
    // Example manual filtering (less recommended than policy scopes):
    // if (!Auth::user()->can('viewAllLoanTransactions')) { // Assuming a permission check for BPM staff/admins
    //      // Maybe redirect or abort if user doesn't have permission
    //      abort(403, 'Unauthorized');
    // }


    $transactions = $transactions->paginate(10); // Paginate for better performance


    // Return the view with the list of transactions
    // Ensure your view file name matches: resources/views/loan-transactions/index.blade.php
    return view('loan-transactions.index', compact('transactions'));
  }


  /**
   * Display the specified loan transaction record.
   * Shows details of a specific equipment issuance or return transaction.
   *
   * @param  \App\Models\LoanTransaction  $loanTransaction  The transaction instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function show(LoanTransaction $loanTransaction): View // Use 'loanTransaction' as parameter name, add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('view' on the specific $loanTransaction).
    // The policy's 'view' method should verify if the user has permission to see this transaction
    // (e.g., is the applicant, is BPM staff, is admin, is any of the involved officers).

    // Log viewing loan transaction details
    Log::info('Viewing loan transaction details.', [
      'transaction_id' => $loanTransaction->id,
      'loan_application_id' => $loanTransaction->loan_application_id,
      'equipment_id' => $loanTransaction->equipment_id,
      'user_id' => Auth::id(), // Log the user viewing the transaction
    ]);

    // Eager load related data needed for the show view:
    // - Loan Application and its applicant user
    // - Equipment involved
    // - Involved officers
    $loanTransaction->load([
      'loanApplication.user', // Load application and applicant
      'equipment',
      'issuingOfficer',
      'receivingOfficer',
      'returningOfficer',
      'returnAcceptingOfficer'
    ]);


    // Return the view to show transaction details
    // Ensure your view file name matches: resources/views/loan-transactions/show.blade.php
    return view('loan-transactions.show', compact('loanTransaction')); // Pass as 'loanTransaction'
  }


  // --- BPM Staff Workflow Actions (Issuance and Return) ---
  // These methods are transferred from the LoanApplicationController.

  /**
   * Show the form to issue equipment for an approved application.
   * This method is accessed by BPM staff and binds a LoanApplication.
   *
   * @param LoanApplication $loanApplication The approved application instance.
   * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
   */
  public function issueEquipmentForm(LoanApplication $loanApplication): View|RedirectResponse // Add return type hint
  {
    // Authorization check: Ensure user is BPM staff and the application is in a state ready for issuance (e.g., 'approved', 'partially_issued').
    // This check is crucial and should be implemented in the Policy's 'issue' method on LoanApplicationPolicy.
    // This uses the LoanApplicationPolicy because the action is tied to the application's workflow state.
    $this->authorize('issue', $loanApplication); // Assumes a policy action 'issue' on LoanApplicationPolicy


    // Optional: Add logging for accessing issuance form
    Log::info('User accessing equipment issuance form.', [
      'application_id' => $loanApplication->id,
      'user_id' => Auth::id(), // BPM Staff user accessing form
    ]);

    // Ensure the application status allows issuance before showing the form
    $issuableStatuses = ['approved', 'partially_issued']; // Define statuses where issuance is possible
    if (!in_array($loanApplication->status, $issuableStatuses)) {
      Log::warning('Attempted to access issuance form for application not in issuable status.', [
        'application_id' => $loanApplication->id,
        'user_id' => Auth::id(),
        'current_status' => $loanApplication->status,
      ]);
      // Changed message to Malay
      return redirect()->route('loan-applications.show', $loanApplication)->with('error', 'Permohonan pinjaman tidak berstatus bersedia untuk pengeluaran peralatan.'); // Malay error message
    }

    // Eager load necessary relationships for the form view:
    // - Applicant User details
    // - Requested Items and their equipment details
    // - Existing transactions if partially issued
    $loanApplication->load(['user.department', 'user.position', 'items.equipment', 'transactions.equipment']);

    // Pass the application data to the issuance form view
    // Ensure your view file name matches: resources/views/loan-transactions/issue.blade.php
    return view('loan-transactions.issue', compact('loanApplication')); // Pass the application
  }


  /**
   * Process the issuance of equipment for an application.
   * Called by BPM staff when the issuance form is submitted.
   * Creates a LoanTransaction record.
   *
   * @param LoanApplication $loanApplication The application instance.
   * @param IssueEquipmentRequest $request The validated issuance request.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function issueEquipment(LoanApplication $loanApplication, IssueEquipmentRequest $request): RedirectResponse // Use Form Request, add return type hint
  {
    // Authorization check: Ensure user is BPM staff and application state allows issuance.
    // Handled by the Policy's 'issue' method on LoanApplicationPolicy.
    $this->authorize('issue', $loanApplication); // Assumes a policy action 'issue' on LoanApplicationPolicy

    // Validation handled automatically by IssueEquipmentRequest.
    // IssueEquipmentRequest should validate: equipment_id, accessories, notes, receiving_user_id, etc.

    // Log issuance attempt
    Log::info('Attempting to issue equipment for loan application.', [
      'application_id' => $loanApplication->id,
      'user_id' => Auth::id(), // BPM Staff user performing issuance
      'ip_address' => $request->ip(),
      'validated_data_keys' => array_keys($request->validated()), // Log keys
    ]);


    // Ensure the application status allows issuance before processing the request.
    $issuableStatuses = ['approved', 'partially_issued']; // Define statuses where issuance is possible
    if (!in_array($loanApplication->status, $issuableStatuses)) {
      Log::warning('Attempted to issue equipment for application not in issuable status.', [
        'application_id' => $loanApplication->id,
        'user_id' => Auth::id(),
        'current_status' => $loanApplication->status,
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Permohonan pinjaman tidak berstatus bersedia untuk pengeluaran peralatan.'); // Malay error message
    }


    try {
      $validatedData = $request->validated();
      $issuingOfficer = Auth::user(); // The BPM staff performing the issuance

      // Delegate the issuance logic to the LoanApplicationService (or a dedicated LoanTransactionService).
      // The service should:
      // - Validate equipment availability and linkage to requested items.
      // - Create a new LoanTransaction record.
      // - Link the transaction to the LoanApplication and the specific Equipment.
      // - Set issuance details (issuing officer, receiving officer, accessories, notes, timestamp).
      // - Update the Equipment status to 'on_loan'.
      // - Update the LoanApplication status to 'partially_issued' or 'issued'/'completed' if all items are issued.
      // - Potentially trigger notifications.
      $transaction = $this->loanApplicationService->issueEquipment( // Assumes service method exists
        $loanApplication,
        $validatedData['equipment_id'], // Pass equipment ID
        $validatedData['receiving_user_id'], // Pass receiving officer ID
        $issuingOfficer, // Pass issuing officer user model
        $validatedData['accessories'] ?? null, // Pass optional accessories
        $validatedData['notes'] ?? null // Pass optional notes
      );

      // Log successful issuance
      Log::info('Equipment issued successfully for loan application.', [
        'application_id' => $loanApplication->id,
        'transaction_id' => $transaction->id,
        'equipment_id' => $transaction->equipment_id,
        'issued_by' => $issuingOfficer->id,
        'received_by' => $transaction->receiving_user_id,
      ]);

      // Redirect to the newly created Loan Transaction show page with a success message
      // Changed message to Malay
      return redirect()->route('loan-transactions.show', $transaction)
        ->with('success', 'Peralatan berjaya dikeluarkan.'); // Malay success message

    } catch (ValidationException $e) {
      // Catch validation errors from the service (if service re-validates or throws)
      Log::warning('Equipment issuance validation failed in controller or service.', [
        'application_id' => $loanApplication->id,
        'user_id' => Auth::id(),
        'errors' => $e->errors(),
      ]);
      // Re-throw for Laravel's default validation error handling
      throw $e;
    } catch (Exception $e) {
      // Log any exceptions thrown by the service or during the process
      Log::error('Error processing equipment issuance for loan application.', [
        'application_id' => $loanApplication->id,
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal memproses pengeluaran peralatan disebabkan ralat: ' . $e->getMessage()); // Malay error message
    }
  }

  /**
   * Show the form to process the return of equipment.
   * This method is accessed by BPM staff and binds a specific LoanTransaction.
   *
   * @param LoanTransaction $loanTransaction The transaction instance being returned.
   * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
   */
  public function returnEquipmentForm(LoanTransaction $loanTransaction): View|RedirectResponse // Use 'loanTransaction' as parameter name, add return type hint
  {
    // Authorization check: Ensure user is BPM staff and the transaction status allows return processing (e.g., 'on_loan').
    // This check is crucial and should be implemented in the Policy's 'processReturn' method on LoanTransactionPolicy.
    $this->authorize('processReturn', $loanTransaction); // Assumes a policy action 'processReturn' on LoanTransactionPolicy


    // Optional: Add logging for accessing return form
    Log::info('User accessing equipment return form.', [
      'transaction_id' => $loanTransaction->id,
      'loan_application_id' => $loanTransaction->loan_application_id,
      'equipment_id' => $loanTransaction->equipment_id,
      'user_id' => Auth::id(), // BPM Staff user accessing form
    ]);

    // Ensure the transaction status allows processing return
    $returnableStatuses = ['on_loan']; // Define statuses where return processing is possible
    if (!in_array($loanTransaction->status, $returnableStatuses)) {
      Log::warning('Attempted to access return form for transaction not in returnable status.', [
        'transaction_id' => $loanTransaction->id,
        'user_id' => Auth::id(),
        'current_status' => $loanTransaction->status,
      ]);
      // Changed message to Malay
      return redirect()->route('loan-applications.show', $loanTransaction->loanApplication)->with('error', 'Transaksi pinjaman tidak berstatus "sedang dipinjam" dan tidak bersedia untuk pemulangan peralatan.'); // Malay error message
    }

    // Eager load necessary relationships for the form view
    $loanTransaction->load(['loanApplication.user', 'equipment', 'issuingOfficer', 'receivingOfficer', 'returningOfficer']); // Load relevant relationships

    // Load data for the form (e.g., list of users for "Pegawai Yang Memulangkan" dropdown)
    $returningOfficers = User::all(); // Or filter based on department/role if needed


    // Pass the transaction data and returning officers list to the return form view
    // Ensure your view file matches: resources/views/loan-transactions/return.blade.php
    return view('loan-transactions.return', compact('loanTransaction', 'returningOfficers')); // Pass the transaction and list
  }


  /**
   * Process the return of equipment for a specific transaction.
   * Called by BPM staff when the return form is submitted.
   * Updates the LoanTransaction record and equipment/application statuses.
   *
   * @param LoanTransaction $loanTransaction The transaction instance being returned.
   * @param ProcessReturnRequest $request The validated return request.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function processReturn(LoanTransaction $loanTransaction, ProcessReturnRequest $request): RedirectResponse // Use Form Request, add return type hint
  {
    // Authorization check: Ensure user is BPM staff and transaction state allows return processing.
    // Handled by the Policy's 'processReturn' method on LoanTransactionPolicy.
    $this->authorize('processReturn', $loanTransaction); // Assumes a policy action 'processReturn' on LoanTransactionPolicy

    // Validation handled automatically by ProcessReturnRequest.
    // ProcessReturnRequest should validate: returning_user_id, accessories_on_return, return_notes, equipment_condition, etc.

    // Log return processing attempt
    Log::info('Attempting to process equipment return for transaction.', [
      'transaction_id' => $loanTransaction->id,
      'loan_application_id' => $loanTransaction->loan_application_id,
      'equipment_id' => $loanTransaction->equipment_id,
      'user_id' => Auth::id(), // BPM Staff user processing return
      'ip_address' => $request->ip(),
      'validated_data_keys' => array_keys($request->validated()), // Log keys
    ]);

    // Ensure the transaction status allows processing return before proceeding.
    $returnableStatuses = ['on_loan']; // Define statuses where return processing is possible
    if (!in_array($loanTransaction->status, $returnableStatuses)) {
      Log::warning('Attempted to process return for transaction not in returnable status.', [
        'transaction_id' => $loanTransaction->id,
        'user_id' => Auth::id(),
        'current_status' => $loanTransaction->status,
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Transaksi pinjaman tidak berstatus "sedang dipinjam" dan tidak bersedia untuk pemulangan peralatan.'); // Malay error message
    }


    try {
      $validatedData = $request->validated();
      $acceptingOfficer = Auth::user(); // The BPM staff accepting the return

      // Delegate the return processing logic to the LoanApplicationService (or a dedicated LoanTransactionService).
      // The service should handle:
      // - Updating the LoanTransaction record with return details (notes, condition, timestamp, accepting officer).
      // - Updating the Transaction status to 'returned'.
      // - Updating the associated Equipment status back to 'available' or 'under_maintenance' based on condition.
      // - Updating the LoanApplication status if all items are returned.
      // - Potentially triggering notifications.
      $updatedTransaction = $this->loanApplicationService->processReturn( // Assumes service method exists
        $loanTransaction,
        $validatedData['returning_user_id'], // Pass returning officer ID
        $acceptingOfficer, // Pass return accepting officer user model
        $validatedData['accessories_on_return'] ?? null, // Pass optional accessories
        $validatedData['return_notes'] ?? null, // Pass optional notes
        $validatedData['equipment_condition'] // Pass equipment condition
      );

      // Log successful return processing
      Log::info('Equipment return processed successfully for transaction.', [
        'transaction_id' => $loanTransaction->id,
        'loan_application_id' => $loanTransaction->loan_application_id,
        'equipment_id' => $loanTransaction->equipment_id,
        'returning_user_id' => $updatedTransaction->returning_user_id,
        'accepted_by' => $acceptingOfficer->id,
      ]);

      // Redirect to the updated Loan Transaction show page with a success message
      // Changed message to Malay
      return redirect()->route('loan-transactions.show', $loanTransaction)->with('success', 'Pemulangan peralatan berjaya diproses.'); // Malay success message

    } catch (ValidationException $e) {
      // Catch validation errors from the service (if service re-validates or throws)
      Log::warning('Equipment return validation failed in controller or service.', [
        'transaction_id' => $loanTransaction->id,
        'user_id' => Auth::id(),
        'errors' => $e->errors(),
      ]);
      // Re-throw for Laravel's default validation error handling
      throw $e;
    } catch (Exception $e) {
      // Log any exceptions thrown by the service or during the process
      Log::error('Error processing equipment return for transaction.', [
        'transaction_id' => $loanTransaction->id,
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal memproses pemulangan peralatan disebabkan ralat: ' . $e->getMessage()); // Malay error message
    }
  }

  // Note: Standard resource methods like create, store, edit, update, destroy are typically
  // not used for LoanTransaction records as they are created/managed as part of the
  // loan application workflow (issuance, return). The issue and processReturn methods
  // serve the purpose of 'creating' and 'updating' transaction records via workflow actions.
}
