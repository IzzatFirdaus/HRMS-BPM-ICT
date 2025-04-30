<?php

namespace App\Http\Controllers; // Ensure the namespace is correct for your project. Consider App\Http\Controllers\Admin if application/transaction management is an admin function.

use App\Models\LoanApplication; // Import LoanApplication model
use App\Models\Equipment; // Needed for issuance logic
use App\Models\LoanTransaction; // Needed for return logic (route model binding)
use Illuminate\Http\Request; // Standard Request object
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Support\Facades\Gate; // Import Gate (less needed with Policies)
// Assuming a LoanApplicationService handles application lifecycle and orchestrates transactions
use App\Services\LoanApplicationService; // Import LoanApplicationService
// Assuming a dedicated service handles Loan Transaction creation/updates (e.g., LoanTransactionService)
// use App\Services\LoanTransactionService; // You might inject this service if LoanApplicationService doesn't fully encapsulate transaction logic


// Import the Rule class for validation
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Exception; // Import Exception for general errors
use Illuminate\Database\Eloquent\ModelNotFoundException; // Import ModelNotFoundException
use Illuminate\Validation\ValidationException; // Import ValidationException


// Import Form Requests if you plan to use standard forms for store/update OR dedicated BPM actions
// use App\Http\Requests\StoreLoanApplicationRequest; // For standard application creation form
// use App\Http\Requests\UpdateLoanApplicationRequest; // For standard application update form
use App\Http\Requests\IssueEquipmentRequest; // Create this Form Request for issuance validation
use App\Http\Requests\ProcessReturnRequest; // Create this Form Request for return validation


class LoanApplicationController extends Controller
{
  protected $loanApplicationService;
  // protected $loanTransactionService; // Inject if transactions have dedicated service


  /**
   * Inject services and apply authentication/authorization middleware.
   *
   * @param \App\Services\LoanApplicationService $loanApplicationService The application service instance.
   * // @param \App\Services\LoanTransactionService $loanTransactionService The transaction service instance (if used separately).
   */
  // Removed ApprovalService injection as approval decisions are handled in ApprovalController
  public function __construct(LoanApplicationService $loanApplicationService /*, LoanTransactionService $loanTransactionService */)
  {
    // Apply authentication middleware to all methods in this controller
    $this->middleware('auth');

    // Apply authorization policy checks automatically for resource methods (index, show, destroy)
    // Note: create, store, edit, update are handled by Livewire and don't need authorizeResource here.
    // Custom methods (issue, return) will have manual authorization checks or route middleware.
    // Assumes a LoanApplicationPolicy exists and is registered.
    // Policy methods: viewAny, view, delete, issue, processReturn
    $this->authorizeResource(LoanApplication::class, 'loan_application', [
      'only' => ['index', 'show', 'destroy'] // Apply authorizeResource only to these methods
    ]);


    $this->loanApplicationService = $loanApplicationService;
    // $this->loanTransactionService = $loanTransactionService; // Assign if used
  }

  /**
   * Display a listing of the loan applications.
   * Fetches applications the current user is authorized to view.
   *
   * @return \Illuminate\View\View
   */
  public function index(): \Illuminate\View\View // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('viewAny').
    // The policy's 'viewAny' method should ideally handle filtering based on roles/permissions (using scopes).

    // Log viewing loan applications index
    Log::info('Viewing loan applications index.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Fetch loan applications the current user is authorized to view.
    // Eager load related data for the index list.
    $applications = LoanApplication::query()
      ->with(['user', 'responsibleOfficer', 'items']) // Eager load applicant, responsible officer, and requested items
      ->latest(); // Order by latest application

    // Apply the 'viewAny' policy scope if it exists in your policy to handle filtering.
    // If not using policy scopes, manual filtering is necessary here:
    // Example manual filtering (less recommended than policy scopes):
    // if (!Auth::user()->can('viewAllLoanApplications')) { // Assuming 'viewAllLoanApplications' permission for admin/approvers/BPM
    //     $applications->where('user_id', Auth::id()); // Users only see their own applications
    // }


    $applications = $applications->paginate(10); // Paginate the results

    // Return the view with the list of applications
    // Ensure your view file name matches: resources/views/loan-applications/index.blade.php
    return view('loan-applications.index', compact('applications'));
  }

  /**
   * Show the form for creating a new resource.
   * This method is redundant as the route is handled by a Livewire component ('request-loan').
   * Redirects the user to the Livewire form page.
   *
   * @return \Illuminate\Http\RedirectResponse
   */
  // Removed the method body, as it just redirects. You can remove the method entirely if unused.
  // public function create(): \Illuminate\Http\RedirectResponse
  // {
  //      Log::info('Redirecting user to loan application creation form (Livewire).', ['user_id' => Auth::id()]);
  //      return redirect()->route('request-loan'); // Assuming 'request-loan' is the named route for the Livewire form
  // }


  /**
   * Store a newly created resource in storage.
   * This method is redundant as form submission is likely handled by the Livewire component.
   * Kept as a commented-out reference for standard form handling.
   *
   * @param  \App\Http\Requests\StoreLoanApplicationRequest  $request  The validated incoming registration request.
   * @return \Illuminate\Http\RedirectResponse
   */
  // If you have a non-Livewire form posting to this route, uncomment and implement:
  // public function store(StoreLoanApplicationRequest $request): \Illuminate\Http\RedirectResponse
  // {
  //     // Policy authorization (redundant if handled by route middleware or Livewire component policy)
  //     // $this->authorize('create', LoanApplication::class);
  //     // Validation handled automatically by StoreLoanApplicationRequest
  //
  //     Log::info('Attempting to store new loan application via standard controller.', [
  //          'user_id' => Auth::id(),
  //          'ip_address' => $request->ip(),
  //      ]);
  //
  //     try {
  //          // Get validated data and items data
  //         $validatedData = $request->validated();
  //         $itemsData = $request->input('items', []); // Assuming items data is passed separately
  //
  //          // Use the LoanApplicationService to create the application
  //          // Ensure createApplication method exists in service and handles user association, status, items, etc.
  //          $application = $this->loanApplicationService->createApplication(Auth::user(), $validatedData, $itemsData);
  //
  //          Log::info('Loan application stored successfully via standard controller.', [
  //              'application_id' => $application->id,
  //              'user_id' => $application->user_id,
  //              'status' => $application->status,
  //          ]);
  //
  //          // Redirect to the 'show' route for the newly created application with a success message
  //          return redirect()->route('loan-applications.show', $application)
  //              ->with('success', 'Permohonan pinjaman berjaya dihantar.'); // Malay success message
  //
  //      } catch (Exception $e) {
  //           Log::error('Error storing loan application via standard controller.', [
  //               'user_id' => Auth::id(),
  //               'error' => $e->getMessage(),
  //               'ip_address' => $request->ip(),
  //               'validated_data' => $request->validated(), // Log validated data on error
  //           ]);
  //          return redirect()->back()->withInput()->with('error', 'Gagal menghantar permohonan pinjaman disebabkan ralat: ' . $e->getMessage()); // Malay error message
  //      }
  // }

  /**
   * Display the specified resource.
   * Shows details of a specific loan application.
   *
   * @param  \App\Models\LoanApplication  $loanApplication  The application instance resolved by route model binding.
   * @return \Illuminate\View\View
   */
  public function show(LoanApplication $loanApplication): \Illuminate\View\View // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('view' on the specific $loanApplication).
    // The policy's 'view' method should verify if the user is the applicant, assigned approver, BPM staff, or admin.

    // Log viewing loan application details
    Log::info('Viewing loan application details.', [
      'application_id' => $loanApplication->id,
      'user_id' => Auth::id(), // Log the user viewing the application
    ]);

    // Eager load relationships needed for the show view:
    // - User (applicant) and their details (department, position, grade)
    // - Responsible Officer
    // - Requested Items
    // - Related Transactions and details on transactions (equipment, officers)
    // - Related Approvals and the officers who made decisions
    $loanApplication->load([
      'user.department',
      'user.position',
      'user.grade',
      'responsibleOfficer',
      'items.equipment', // Load equipment details for requested items
      'transactions.equipment', // Load equipment details for transaction items
      'transactions.issuingOfficer',
      'transactions.receivingOfficer',
      'transactions.returningOfficer',
      'transactions.returnAcceptingOfficer',
      'approvals.officer'
    ]);

    // Return the view to show application details
    // Ensure your view file name matches: resources/views/loan-applications/show.blade.php
    return view('loan-applications.show', compact('loanApplication'));
  }

  /**
   * Show the form for editing the specified resource.
   * This method is redundant as editing is handled by a Livewire component ('request-loan' mounted with ID).
   * Redirects the user to the Livewire form page with the application ID.
   *
   * @param  \App\Models\LoanApplication  $loanApplication  The application instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  // Removed the method body, as it just redirects. You can remove the method entirely if unused.
  // public function edit(LoanApplication $loanApplication): \Illuminate\Http\RedirectResponse
  // {
  //     // Policy authorization (redundant if handled by route middleware or Livewire component policy)
  //     // $this->authorize('update', $loanApplication);
  //
  //      Log::info('Redirecting user to loan application edit form (Livewire).', [
  //          'application_id' => $loanApplication->id,
  //          'user_id' => Auth::id(),
  //      ]);
  //     // Redirect to the Livewire component route, passing the application ID for mounting
  //     return redirect()->route('request-loan', ['loanApplication' => $loanApplication->id]); // Assuming 'request-loan' route accepts {loanApplication}
  // }


  /**
   * Update the specified resource in storage.
   * This method is redundant as updating is handled by the Livewire component.
   * Kept as a commented-out reference for standard form handling.
   *
   * @param  \App\Http\Requests\UpdateLoanApplicationRequest  $request  The validated incoming request.
   * @param  \App\Models\LoanApplication  $loanApplication  The application instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  // If you have a non-Livewire form posting to this route, uncomment and implement:
  // public function update(UpdateLoanApplicationRequest $request, LoanApplication $loanApplication): \Illuminate\Http\RedirectResponse
  // {
  //      // Policy authorization (redundant if handled by route middleware or Livewire component policy)
  //     // $this->authorize('update', $loanApplication);
  //      // Validation handled automatically by UpdateLoanApplicationRequest
  //
  //      Log::info('Attempting to update loan application via standard controller.', [
  //          'application_id' => $loanApplication->id,
  //          'user_id' => Auth::id(),
  //          'ip_address' => $request->ip(),
  //      ]);
  //
  //     try {
  //         $validatedData = $request->validated();
  //         $itemsData = $request->input('items', []); // Assuming items data is passed separately
  //
  //          // Use the LoanApplicationService to update the application
  //          // Ensure updateApplication method exists in service and handles status checks (e.g., only allow update if status is 'draft')
  //          $updated = $this->loanApplicationService->updateApplication($loanApplication, $validatedData, $itemsData);
  //
  //          if ($updated) {
  //              Log::info('Loan application updated successfully via standard controller.', [
  //                  'application_id' => $loanApplication->id,
  //                  'user_id' => Auth::id(),
  //              ]);
  //               return redirect()->route('loan-applications.show', $loanApplication)
  //                   ->with('success', 'Permohonan pinjaman berjaya dikemaskini.'); // Malay success message
  //           } else {
  //               Log::warning('Loan application update failed via service (no changes or service rule).', [
  //                    'application_id' => $loanApplication->id,
  //                    'user_id' => Auth::id(),
  //                ]);
  //              return redirect()->back()->withInput()->with('error', 'Gagal mengemaskini permohonan pinjaman.'); // Malay error message
  //           }
  //
  //      } catch (Exception $e) {
  //           Log::error('Error updating loan application via standard controller.', [
  //               'application_id' => $loanApplication->id,
  //               'user_id' => Auth::id(),
  //               'error' => $e->getMessage(),
  //               'ip_address' => $request->ip(),
  //               'validated_data' => $request->validated(), // Log validated data on error
  //           ]);
  //          return redirect()->back()->withInput()->with('error', 'Gagal mengemaskini permohonan pinjaman disebabkan ralat: ' . $e->getMessage()); // Malay error message
  //      }
  // }


  /**
   * Remove the specified resource from storage.
   * Typically only allowed if the application is in a specific status (e.g., 'draft' or 'rejected').
   * Delegates deletion logic to the LoanApplicationService or handles directly after check.
   *
   * @param  \App\Models\LoanApplication  $loanApplication  The application instance resolved by route model binding.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(LoanApplication $loanApplication): \Illuminate\Http\RedirectResponse // Add return type hint
  {
    // Authorization handled by authorizeResource in the constructor ('delete' on the specific $loanApplication).
    // The policy's 'delete' method should verify:
    // 1. The authenticated user is the applicant (or admin).
    // 2. The application status allows deletion (e.g., 'draft' or 'rejected').

    // Log deletion attempt
    Log::info('Attempting to delete loan application.', [
      'application_id' => $loanApplication->id,
      'user_id' => Auth::id(),
      'current_status' => $loanApplication->status,
      'ip_address' => request()->ip(),
    ]);

    // Prevent deletion if the application status does not allow it.
    // Based on workflow, only 'draft' or 'rejected' might be deletable.
    $deletableStatuses = ['draft', 'rejected']; // Define allowed statuses for deletion
    if (!in_array($loanApplication->status, $deletableStatuses)) {
      Log::warning('Attempted to delete loan application in non-deletable status.', [
        'application_id' => $loanApplication->id,
        'user_id' => Auth::id(),
        'current_status' => $loanApplication->status,
      ]);
      // Changed message to Malay
      return redirect()->back()->with('error', 'Permohonan pinjaman tidak dapat dibuang kerana statusnya bukan "draf" atau "ditolak".'); // Malay error message
    }

    // Consider using Soft Deletes for LoanApplication model if retaining historical data is needed.
    // If Soft Deletes are used, $loanApplication->delete() will perform a soft delete.

    try {
      // Delegate deletion logic to the service if cleanup/related actions are needed
      // $deleted = $this->loanApplicationService->deleteApplication($loanApplication); // Assumes deleteApplication method exists in service

      // Or delete directly after the status check:
      $loanApplicationId = $loanApplication->id; // Store ID before deletion
      $loanApplication->delete(); // Performs soft delete if SoftDeletes trait is used

      // Log successful deletion (soft or permanent)
      Log::info('Loan application deleted successfully.', [
        'application_id' => $loanApplicationId, // Use stored ID
        'user_id' => Auth::id(),
      ]);

      // Redirect to the index page with a success message
      // Changed message to Malay
      return redirect()->route('loan-applications.index')
        ->with('success', 'Permohonan pinjaman berjaya dibuang.'); // Malay success message

    } catch (Exception $e) {
      // Log any exceptions during deletion
      Log::error('Error deleting loan application.', [
        'application_id' => $loanApplication->id ?? 'unknown', // Use ID if available
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => request()->ip(),
      ]);
      // Changed message to Malay
      return redirect()->back()->with('error', 'Gagal membuang permohonan pinjaman disebabkan ralat: ' . $e->getMessage()); // Malay error message
    }
  }


  // --- BPM Staff Workflow Actions (Issuance and Return) ---

  /**
   * Show the form to issue equipment for an approved application.
   * This method is accessed by BPM staff.
   *
   * @param LoanApplication $loanApplication The approved application instance.
   * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
   */
  public function issueEquipmentForm(LoanApplication $loanApplication): \Illuminate\View\View|\Illuminate\Http\RedirectResponse // Add return type hint
  {
    // Authorization check: Ensure user is BPM staff and the application is in a state ready for issuance (e.g., 'approved', 'partially_issued').
    // This check is crucial and should be implemented in the Policy's 'issue' method.
    $this->authorize('issue', $loanApplication); // Assuming a policy action 'issue' which checks user role/permission and application status

    // Optional: Add logging for accessing issuance form
    Log::info('User accessing equipment issuance form.', [
      'application_id' => $loanApplication->id,
      'user_id' => Auth::id(),
    ]);

    // Eager load necessary relationships for the form view:
    // - Applicant User details
    // - Requested Items and their equipment details
    // - Existing transactions if partially issued
    $loanApplication->load(['user.department', 'user.position', 'items.equipment', 'transactions.equipment']);

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


    // Pass the application data to the issuance form view
    // Ensure your view file matches: resources/views/loan-applications/issue.blade.php
    return view('loan-applications.issue', compact('loanApplication'));
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
  public function issueEquipment(LoanApplication $loanApplication, IssueEquipmentRequest $request): \Illuminate\Http\RedirectResponse // Use Form Request, add return type hint
  {
    // Authorization check: Ensure user is BPM staff and application state allows issuance.
    // Handled by the Policy's 'issue' method.
    $this->authorize('issue', $loanApplication); // Assuming a policy action 'issue'

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

      // Delegate the issuance logic to the LoanApplicationService.
      // The service should:
      // - Create a new LoanTransaction record.
      // - Link the transaction to the LoanApplication and the specific Equipment.
      // - Set issuance details (issuing officer, receiving officer, accessories, notes, timestamp).
      // - Update the Equipment status to 'on_loan'.
      // - Update the LoanApplication status to 'partially_issued' or 'issued'/'completed' if all items are issued.
      // - Potentially trigger notifications.
      $transaction = $this->loanApplicationService->issueEquipment(
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

      // Redirect to the newly created Loan Transaction show page or the application show page
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
   * @param LoanTransaction $transaction The transaction instance being returned.
   * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
   */
  public function returnEquipmentForm(LoanTransaction $transaction): \Illuminate\View\View|\Illuminate\Http\RedirectResponse // Add return type hint
  {
    // Authorization check: Ensure user is BPM staff and the transaction status allows return processing (e.g., 'on_loan').
    // This check is crucial and should be implemented in the Policy's 'processReturn' method.
    $this->authorize('processReturn', $transaction); // Assuming a policy action 'processReturn' which checks user role/permission and transaction status

    // Optional: Add logging for accessing return form
    Log::info('User accessing equipment return form.', [
      'transaction_id' => $transaction->id,
      'loan_application_id' => $transaction->loan_application_id,
      'equipment_id' => $transaction->equipment_id,
      'user_id' => Auth::id(), // BPM Staff user accessing form
    ]);

    // Ensure the transaction status allows processing return
    $returnableStatuses = ['on_loan']; // Define statuses where return processing is possible
    if (!in_array($transaction->status, $returnableStatuses)) {
      Log::warning('Attempted to access return form for transaction not in returnable status.', [
        'transaction_id' => $transaction->id,
        'user_id' => Auth::id(),
        'current_status' => $transaction->status,
      ]);
      // Changed message to Malay
      return redirect()->route('loan-applications.show', $transaction->loanApplication)->with('error', 'Transaksi pinjaman tidak berstatus "sedang dipinjam" dan tidak bersedia untuk pemulangan peralatan.'); // Malay error message
    }

    // Eager load necessary relationships for the form view
    $transaction->load(['loanApplication.user', 'equipment', 'issuingOfficer', 'receivingOfficer', 'returningOfficer']); // Load relevant relationships


    // Pass the transaction data to the return form view
    // Ensure your view file matches: resources/views/loan-applications/return.blade.php
    return view('loan-applications.return', compact('transaction'));
  }


  /**
   * Process the return of equipment for a specific transaction.
   * Called by BPM staff when the return form is submitted.
   * Updates the LoanTransaction record and equipment/application statuses.
   *
   * @param LoanTransaction $transaction The transaction instance being returned.
   * @param ProcessReturnRequest $request The validated return request.
   * @return \Illuminate\Http\RedirectResponse
   */
  public function processReturn(LoanTransaction $transaction, ProcessReturnRequest $request): \Illuminate\Http\RedirectResponse // Use Form Request, add return type hint
  {
    // Authorization check: Ensure user is BPM staff and transaction state allows return processing.
    // Handled by the Policy's 'processReturn' method.
    $this->authorize('processReturn', $transaction); // Assuming a policy action 'processReturn'

    // Validation handled automatically by ProcessReturnRequest.
    // ProcessReturnRequest should validate: accessories_on_return, return_notes, equipment_condition, returning_user_id, etc.

    // Log return processing attempt
    Log::info('Attempting to process equipment return for transaction.', [
      'transaction_id' => $transaction->id,
      'loan_application_id' => $transaction->loan_application_id,
      'equipment_id' => $transaction->equipment_id,
      'user_id' => Auth::id(), // BPM Staff user processing return
      'ip_address' => $request->ip(),
      'validated_data_keys' => array_keys($request->validated()), // Log keys
    ]);

    // Ensure the transaction status allows processing return before proceeding.
    $returnableStatuses = ['on_loan']; // Define statuses where return processing is possible
    if (!in_array($transaction->status, $returnableStatuses)) {
      Log::warning('Attempted to process return for transaction not in returnable status.', [
        'transaction_id' => $transaction->id,
        'user_id' => Auth::id(),
        'current_status' => $transaction->status,
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Transaksi pinjaman tidak berstatus "sedang dipinjam" dan tidak bersedia untuk pemulangan peralatan.'); // Malay error message
    }


    try {
      $validatedData = $request->validated();
      $acceptingOfficer = Auth::user(); // The BPM staff accepting the return

      // Delegate the return processing logic to the LoanApplicationService (or LoanTransactionService).
      // The service should handle:
      // - Updating the LoanTransaction record with return details (notes, condition, timestamp, accepting officer).
      // - Updating the Transaction status to 'returned'.
      // - Updating the associated Equipment status back to 'available' or 'under_maintenance' based on condition.
      // - Updating the LoanApplication status if all items are returned.
      // - Potentially triggering notifications.
      $updatedTransaction = $this->loanApplicationService->processReturn( // Assuming service method exists
        $transaction,
        $validatedData['returning_user_id'], // Pass returning officer ID
        $acceptingOfficer, // Pass return accepting officer user model
        $validatedData['accessories_on_return'] ?? null, // Pass optional accessories
        $validatedData['return_notes'] ?? null, // Pass optional notes
        $validatedData['equipment_condition'] // Pass equipment condition
      );

      // Log successful return processing
      Log::info('Equipment return processed successfully for transaction.', [
        'transaction_id' => $transaction->id,
        'loan_application_id' => $transaction->loan_application_id,
        'equipment_id' => $transaction->equipment_id,
        'returning_user_id' => $updatedTransaction->returning_user_id,
        'accepted_by' => $acceptingOfficer->id,
      ]);

      // Redirect to the updated Loan Transaction show page with a success message
      // Changed message to Malay
      return redirect()->route('loan-transactions.show', $transaction)->with('success', 'Pemulangan peralatan berjaya diproses.'); // Malay success message

    } catch (ValidationException $e) {
      // Catch validation errors from the service (if service re-validates or throws)
      Log::warning('Equipment return validation failed in controller or service.', [
        'transaction_id' => $transaction->id,
        'user_id' => Auth::id(),
        'errors' => $e->errors(),
      ]);
      // Re-throw for Laravel's default validation error handling
      throw $e;
    } catch (Exception $e) {
      // Log any exceptions thrown by the service or during the process
      Log::error('Error processing equipment return for transaction.', [
        'transaction_id' => $transaction->id,
        'user_id' => Auth::id(),
        'error' => $e->getMessage(),
        'ip_address' => $request->ip(),
      ]);
      // Changed message to Malay
      return redirect()->back()->withInput()->with('error', 'Gagal memproses pemulangan peralatan disebabkan ralat: ' . $e->getMessage()); // Malay error message
    }
  }

  // Removed commented-out placeholder methods (approve, reject)
  // as approval decisions are handled in the ApprovalController.

  // Note: Methods for specific BPM actions like 'Cancel Issuance', 'Record Damage', etc.,
  // might also be needed and could be added to this controller or a dedicated
  // LoanTransactionController if you manage transactions as a separate resource.

}
