<?php

namespace App\Http\Controllers;

use App\Models\LoanApplication; // Needed for issueEquipmentForm, issueEquipment
use App\Models\LoanApplicationItem; // Needed for issueEquipment (specific item) - though not passed to service
use App\Models\Equipment; // Needed for issueEquipmentForm, issueEquipment (specific equipment), and constants
use App\Models\LoanTransaction; // Needed for index, show, returnEquipmentForm, processReturn, and constants
use App\Models\User; // Needed for issueEquipment (receiving officer) and return (returning/accepting)
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // Needed for authorize (though Policy is preferred)
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;
use App\Services\LoanApplicationService; // Orchestrates via this service
// You might inject a dedicated LoanTransactionService if your transaction logic is complex
// use App\Services\LoanTransactionService;
use Illuminate\Validation\Rule; // Needed for validation potentially
use Illuminate\Support\Collection; // Needed for returningOfficers collection (if fetching multiple)
use App\Http\Requests\IssueEquipmentRequest; // Assuming this Form Request exists
use App\Http\Requests\ProcessReturnRequest; // Assuming this Form Request exists


class LoanTransactionController extends Controller
{
  // Inject the service responsible for loan transaction logic
  protected LoanApplicationService $loanApplicationService; // Use LoanApplicationService

  /**
   * Inject the LoanApplicationService and apply middleware.
   *
   * @param \App\Services\LoanApplicationService $loanApplicationService
   */
  public function __construct(LoanApplicationService $loanApplicationService)
  {
    $this->middleware('auth');
    // Apply authorization policy checks.
    // Assumes a LoanTransactionPolicy exists and is registered.
    // Policy methods: viewAny, view, create, issue, return, etc.
    $this->authorizeResource(LoanTransaction::class, 'loan_transaction'); // Use 'loan_transaction' parameter name

    $this->loanApplicationService = $loanApplicationService; // Assign injected service
  }

  /**
   * Display a listing of loan transactions.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\View\View
   */
  public function index(Request $request): View // Add return type hint
  {
    // Authorization handled by authorizeResource ('viewAny')
    // Policy viewAny method should scope results based on user roles/permissions.

    $transactions = LoanTransaction::query()
      ->with(['loanApplication.user', 'equipment', 'issuingOfficer', 'receivingOfficer', 'returningOfficer', 'returnAcceptingOfficer']) // Eager load relationships
      ->latest() // Order by latest transaction
      ->paginate(10); // Paginate results

    return view('loan-transactions.index', compact('transactions'));
  }

  /**
   * Display the specified loan transaction.
   *
   * @param  \App\Models\LoanTransaction  $loanTransaction
   * @return \Illuminate\View\View
   */
  public function show(LoanTransaction $loanTransaction): View // Add return type hint
  {
    // Authorization handled by authorizeResource ('view' on $loanTransaction)
    // Policy view method should check if user is involved or is admin.

    // Eager load relationships needed for the show view
    $loanTransaction->load(['loanApplication.user', 'equipment', 'issuingOfficer', 'receivingOfficer', 'returningOfficer', 'returnAcceptingOfficer']);

    return view('loan-transactions.show', compact('loanTransaction'));
  }


  /**
   * Show the form for issuing equipment for a specific approved application item.
   *
   * @param  \App\Models\LoanApplicationItem  $loanApplicationItem  The specific item from the application to issue.
   * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
   */
  // Changed route model binding to LoanApplicationItem as per form context
  public function issueEquipmentForm(LoanApplicationItem $loanApplicationItem): View | RedirectResponse // Add return types
  {
    // Authorization check: Can the user (likely IT Admin) issue this item?
    // Assumes a 'issue' policy method on LoanApplicationItem or LoanApplication.
    // Policy should check user role and application/item status.
    // Gate::authorize('issue', $loanApplicationItem); // Using Gate or Policy

    // Using authorizeResource setup: Need a specific permission check or policy method not covered by standard resource methods.
    // A custom gate/policy check here is more explicit.
    // Example using Gate:
    // if (! Gate::allows('issue loan application item', $loanApplicationItem)) { // Assuming a specific gate
    //     abort(403);
    // }
    // Example using Policy (define a 'issue' method in LoanTransactionPolicy or LoanApplicationPolicy):
    $this->authorize('issue', $loanApplicationItem); // Assuming LoanTransactionPolicy has an 'issue' method for LoanApplicationItem

    // Ensure the application item is approved and not yet fully issued.
    if (!$loanApplicationItem->isApproved() || $loanApplicationItem->quantity_approved <= $loanApplicationItem->quantity_issued) { // Assuming isApproved() and quantity_issued properties exist
      return redirect()->back()->with('error', 'Item permohonan ini tidak diluluskan atau telah dikeluarkan sepenuhnya.'); // Malay message
    }

    // Fetch potential receiving officers (e.g., from the same department as the applicant, or specific roles)
    // For simplicity, fetching all active users with a certain role or criteria
    $receivingOfficers = User::active()->get(); // Assumes User model has 'active' scope

    // Fetch available equipment of the requested type
    // Assuming equipment relationship exists on LoanApplicationItem to get equipment type
    // Assumes AVAILABILITY_AVAILABLE constant exists on Equipment model
    $availableEquipment = Equipment::where('equipment_type_id', $loanApplicationItem->equipment_type_id) // Assumes equipment_type_id on item/equipment
      ->where('availability_status', Equipment::AVAILABILITY_AVAILABLE)
      ->get();

    // Check if any equipment is available
    if ($availableEquipment->isEmpty()) {
      return redirect()->back()->with('error', 'Tiada peralatan jenis ini yang tersedia untuk dikeluarkan.'); // Malay message
    }

    // Return the view for the issuance form
    // Ensure view path is correct: resources/views/loan-transactions/issue.blade.php
    return view('loan-transactions.issue', compact('loanApplicationItem', 'receivingOfficers', 'availableEquipment'));
  }

  /**
   * Process the equipment issuance for an approved application item.
   * Receives input from the issue form, delegates to the service.
   *
   * @param  \App\Http\Requests\IssueEquipmentRequest  $request  The validated incoming request for issuance.
   * @param  \App\Models\LoanApplicationItem  $loanApplicationItem The specific item from the application to issue.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function issueEquipment(IssueEquipmentRequest $request, LoanApplicationItem $loanApplicationItem): RedirectResponse // Use Form Request, add return type hint
  {
    // Authorization handled by the policy/gate check within this method or via authorizeResource if applicable
    $this->authorize('issue', $loanApplicationItem); // Ensure authorization is re-checked

    // Ensure the item is still issuable
    if (!$loanApplicationItem->isApproved() || $loanApplicationItem->quantity_approved <= $loanApplicationItem->quantity_issued) {
      return redirect()->back()->withInput()->with('error', 'Item permohonan ini tidak boleh dikeluarkan pada masa ini.'); // Malay message
    }

    $validatedData = $request->validated();

    Log::info('Attempting to issue equipment for application item.', [
      'item_id' => $loanApplicationItem->id,
      'application_id' => $loanApplicationItem->loan_application_id,
      'user_id' => Auth::id(),
      'validated_data_keys' => array_keys($validatedData),
    ]);

    // Fetch necessary models based on validated data
    try {
      // *** FIX 1: Change get() to firstOrFail() to get single models ***
      $equipment = Equipment::where('asset_tag', $validatedData['asset_tag'])->firstOrFail(); // Find the specific equipment by asset tag
      $receivingOfficer = User::where('staff_id', $validatedData['receiving_officer_staff_id'])->firstOrFail(); // Find the receiving officer by staff ID

      // The issuing officer is the currently authenticated user
      $issuingOfficer = Auth::user();

      // Prepare issue details array
      $issueDetails = $request->only(['accessories_checklist_on_issue', 'notes']); // Assuming notes field in form


      // Delegate the issuance logic to the LoanApplicationService
      // The service should handle:
      // - Creating the LoanTransaction record.
      // - Updating the equipment's status to 'on loan'.
      // - Updating the LoanApplicationItem's quantity_issued.
      // - Updating the parent LoanApplication's status (partially issued/fully issued).
      // - Potentially triggering notifications.
      // - Ensuring the transaction is atomic (using DB transactions).
      // *** FIX 2: Correct the arguments passed to issueEquipment ***
      $transaction = $this->loanApplicationService->issueEquipment(
        $loanApplicationItem->loanApplication, // Pass the parent application model
        $equipment,                             // Pass the single Equipment model
        $receivingOfficer,                      // Pass the single Receiving User model
        $issuingOfficer,                        // Pass the single Issuing User (Auth::user())
        $issueDetails                           // Pass the issue details array
        // REMOVED: $loanApplicationItem - This was the extra argument
      );

      // Log successful issuance
      Log::info('Equipment issued successfully.', [
        'transaction_id' => $transaction->id,
        'item_id' => $loanApplicationItem->id,
        'equipment_id' => $equipment->id,
        'issued_by_user_id' => Auth::id(),
      ]);

      // Redirect to the transaction's show page or application show page
      // Changed message to Malay
      return redirect()->route('loan-transactions.show', $transaction)
        ->with('success', 'Peralatan berjaya dikeluarkan.'); // Malay success message

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      // Log failure if equipment or user is not found
      Log::warning('Equipment or Receiving Officer not found during issuance.', [
        'item_id' => $loanApplicationItem->id,
        'user_id' => Auth::id(),
        'message' => $e->getMessage(),
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Peralatan atau Pegawai Penerima tidak ditemui.'); // Malay error message
    } catch (ValidationException $e) {
      // Catch validation errors from the Form Request or service (if service re-validates or throws)
      // *** FIX 3: Corrected syntax for the array literal ***
      Log::warning('Equipment issuance validation failed.', [
        'item_id' => $loanApplicationItem->id,
        'user_id' => Auth::id(),
        'errors' => $e->errors(),
      ]);
      // Re-throw for Laravel's default validation error handling
      throw $e;
    } catch (Exception $e) {
      // Log any exceptions thrown by the service or during the process
      Log::error('Error processing equipment issuance.', [
        'item_id' => $loanApplicationItem->id,
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal memproses pengeluaran peralatan disebabkan ralat: ' . $e->getMessage()); // Malay error message
    }
  }


  /**
   * Show the form for returning a specific issued equipment item.
   *
   * @param  \App\Models\LoanTransaction  $loanTransaction  The transaction representing the issued item.
   * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
   */
  public function returnEquipmentForm(LoanTransaction $loanTransaction): View | RedirectResponse // Add return types
  {
    // Authorization check: Can the user (likely IT Admin) process the return for this transaction?
    // Assumes a 'return' policy method on LoanTransaction.
    // Policy should check user role and transaction status.
    $this->authorize('return', $loanTransaction); // Assuming LoanTransactionPolicy has a 'return' method

    // Ensure the transaction is in a status that can be returned (e.g., 'issued', 'under_maintenance_on_loan')
    // Assumes isIssued() and isUnderMaintenanceOnLoan() helper methods or constants exist on LoanTransaction model
    if (!$loanTransaction->isIssued() && !$loanTransaction->isUnderMaintenanceOnLoan()) { // Use helper methods/constants
      return redirect()->back()->with('error', 'Peralatan ini tidak dalam status yang boleh dipulangkan.'); // Malay message
    }

    // Fetch potential returning officers (e.g., the original applicant, or other users)
    // If returningOfficer_id is nullable in the form, this list might include many users.
    // If the form requires specifying the returning officer, you might filter this list.
    $returningOfficers = User::active()->get(); // Assumes User model has 'active' scope

    // Fetch potential return accepting officers (e.g., IT Admin users)
    // Assuming a role or permission to identify who can accept returns
    $returnAcceptingOfficers = User::role('IT Admin')->get(); // Assumes Spatie Role Trait and 'IT Admin' role

    // Ensure necessary relationships are loaded
    $loanTransaction->load(['equipment']); // Load equipment for details in the form

    // Return the view for the return form
    // Ensure view path is correct: resources/views/loan-transactions/return.blade.php
    return view('loan-transactions.return', compact('loanTransaction', 'returningOfficers', 'returnAcceptingOfficers'));
  }


  /**
   * Process the return of a specific issued equipment item.
   * Receives input from the return form, delegates to the service.
   *
   * @param  \App\Http\Requests\ProcessReturnRequest  $request  The validated incoming request for return processing.
   * @param  \App\Models\LoanTransaction  $loanTransaction  The transaction representing the issued item.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function processReturn(ProcessReturnRequest $request, LoanTransaction $loanTransaction): RedirectResponse // Use Form Request, add return type hint
  {
    // Authorization handled by the policy/gate check within this method or via authorizeResource if applicable
    $this->authorize('return', $loanTransaction); // Ensure authorization is re-checked

    // Ensure the transaction is still in a status that can be returned
    if (!$loanTransaction->isIssued() && !$loanTransaction->isUnderMaintenanceOnLoan()) { // Use helper methods/constants
      return redirect()->back()->withInput()->with('error', 'Peralatan ini tidak boleh dipulangkan pada masa ini.'); // Malay message
    }

    $validatedData = $request->validated();

    Log::info('Attempting to process equipment return for transaction.', [
      'transaction_id' => $loanTransaction->id,
      'user_id' => Auth::id(),
      'validated_data_keys' => array_keys($validatedData),
    ]);

    // Fetch necessary models based on validated data if they are not already relationships
    // For instance, if returning_officer_id and return_accepting_officer_id are validated
    try {
      // The return accepting officer is the currently authenticated user
      $returnAcceptingOfficer = Auth::user();

      // Prepare return details array
      // Make sure the keys here match what the service's handleReturn method expects in its $returnDetails array
      $returnDetails = $request->only([
        'returning_officer_id', // If the form collects who returned it
        'accessories_checklist_on_return',
        'equipment_condition_on_return',
        'return_notes',
        'return_timestamp' // Might be collected from form or set to now() in service
      ]);

      // Delegate the return processing logic to the LoanApplicationService
      // The service should handle:
      // - Updating the LoanTransaction record with return details.
      // - Updating the equipment's status (e.g., back to 'available', 'damaged', 'under maintenance').
      // - Updating the LoanApplicationItem's quantity_returned.
      // - Updating the parent LoanApplication's status (fully returned, partially returned).
      // - Potentially triggering notifications.
      // - Ensuring the transaction is atomic.
      // *** FIX 3: Change method name to handleReturn and pass Auth::user() as the third argument ***
      $updatedTransaction = $this->loanApplicationService->handleReturn(
        $loanTransaction, // Pass the transaction model
        $returnDetails,   // Pass the return details array
        $returnAcceptingOfficer // Pass the user accepting the return (Auth::user())
      );

      // Log successful return processing
      Log::info('Equipment return processed successfully.', [
        'transaction_id' => $updatedTransaction->id,
        'status' => $updatedTransaction->status,
        'processed_by_user_id' => Auth::id(),
      ]);

      // Redirect to the transaction's show page
      // Changed message to Malay
      return redirect()->route('loan-transactions.show', $updatedTransaction)->with('success', 'Pemulangan peralatan berjaya diproses.'); // Malay success message

    } catch (ValidationException $e) {
      // Catch validation errors from the Form Request or service (if service re-validates or throws)
      Log::warning('Equipment return validation failed.', [
        'transaction_id' => $loanTransaction->id,
        'user_id' => Auth::id(),
        'errors' => $e->errors(),
      ]);
      // Re-throw for Laravel's default validation error handling
      throw $e;
    } catch (Exception $e) {
      // Log any exceptions thrown by the service or during the process
      Log::error('Error processing equipment return.', [
        'transaction_id' => $loanTransaction->id,
        'loan_application_id' => $loanTransaction->loan_application_id,
        'equipment_id' => $loanTransaction->equipment_id, // Can be null
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal memproses pemulangan peralatan disebabkan ralat: ' . $e->getMessage()); // Malay error message
    }
  }

  // TODO: Add methods for handling other transaction states like 'Cancelled', 'Overdue', etc.\
  // These could update transaction status, equipment status, and potentially trigger notifications.\
  // Example: public function cancelIssuance(LoanTransaction $transaction): RedirectResponse { ... }\

}
