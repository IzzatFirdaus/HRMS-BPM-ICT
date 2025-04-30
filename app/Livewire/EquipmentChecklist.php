<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\LoanTransaction; // Used for recording issue/return
use App\Models\Equipment; // To select specific equipment assets
use App\Models\LoanApplication; // To link to the application
use App\Models\User; // To select officers involved
use App\Services\LoanApplicationService; // Use the service for issue/return logic
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Support\Facades\DB; // For database transactions (can be used directly or in service)
use Illuminate\Support\Facades\Log; // For logging
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Trait for policy checks
use Illuminate\Database\Eloquent\ModelNotFoundException; // Import ModelNotFoundException
use Illuminate\Validation\Rule; // Import Rule for validation
use Illuminate\Validation\ValidationException; // Import ValidationException
use Illuminate\Support\Str; // Import Str facade
use Illuminate\View\View; // For render return type hint
use Illuminate\Http\RedirectResponse; // For redirect return type hint
use Illuminate\Support\Collection; // Import Collection

class EquipmentChecklist extends Component
{
  use AuthorizesRequests; // Use the AuthorizesRequests trait

  // --- Component State Properties ---
  public ?int $loanApplicationId = null; // ID of the loan application this transaction is for
  public string $transactionType = ''; // 'issue' or 'return'

  // Properties for selecting the specific equipment asset
  public ?int $selectedEquipmentId = null; // ID of the specific Equipment asset being issued/returned

  // Properties for the accessory checklist and notes
  public array $accessories = []; // Array of accessories selected from the master list
  public string $notes = ''; // General notes for the transaction (issue notes or return notes)

  // Properties for officers involved in the transaction
  // The BPM staff member currently logged in is the Issuing Officer (on issue) or Return Accepting Officer (on return)
  public ?int $receivingOfficerId = null; // Who is physically receiving the equipment on issue (usually applicant or responsible officer)
  public ?int $returningOfficerId = null; // Who is physically returning the equipment (usually applicant or responsible officer)

  // Properties specific to return transaction details
  public string $returnStatus = 'returned'; // Status of the return ('returned', 'damaged', 'lost') - aligns with LoanTransaction status
  public string $equipmentStatusOnReturn = 'available'; // Status of the equipment asset after return inspection ('available', 'under_maintenance', 'disposed') - aligns with Equipment status


  // Properties to hold dropdown data for selecting officers and equipment
  public Collection $receivingOfficers; // List of users selectable as receiving officer
  public Collection $returningOfficers; // List of users selectable as returning officer
  public Collection $availableEquipmentList; // List of equipment assets available for ISSUE for this application
  public Collection $onLoanEquipmentList; // List of equipment assets ON LOAN for this application (for RETURN)
  public array $allAccessoriesList; // Master list of all possible accessories


  // Optional: If editing an existing transaction
  public ?int $loanTransactionId = null;
  protected ?LoanTransaction $loanTransaction = null; // The transaction being created or updated
  protected ?LoanApplication $loanApplication = null; // The associated loan application


  // --- Listeners (Optional) ---
  // protected $listeners = ['loadTransactionData']; // Example if triggering from another component


  /**
   * Mount the component. Initializes properties based on transaction type and loan application.
   *
   * @param LoanTransaction|null $loanTransaction Optional existing transaction for editing/viewing.
   * @param int|null $loanApplicationId ID of the loan application if creating a new transaction.
   * @param string|null $transactionType Type of transaction ('issue' or 'return') if creating a new one.
   * @return \Illuminate\Http\RedirectResponse|null // Return type can be RedirectResponse or null
   */
  public function mount(?LoanTransaction $loanTransaction = null, ?int $loanApplicationId = null, ?string $transactionType = null): RedirectResponse|null // Corrected return type hint
  {
    // Ensure user is authenticated (BPM staff should be logged in)
    if (!Auth::check()) {
      Log::warning('EquipmentChecklist mounted for unauthenticated user.');
      session()->flash('error', __('You must be logged in to manage equipment transactions.')); // Translated message
      return $this->redirect(route('login')); // Redirect to login if not authenticated
    }

    // Initialize collections to empty
    $this->receivingOfficers = collect();
    $this->returningOfficers = collect();
    $this->availableEquipmentList = collect();
    $this->onLoanEquipmentList = collect();
    $this->accessories = []; // Ensure accessories array is initialized
    $this->allAccessoriesList = config('motac.equipment.accessories_list', []); // Initialize from config or empty

    $this->loanTransaction = $loanTransaction; // Store the transaction if editing
    $this->loanTransactionId = $loanTransaction?->id; // Store transaction ID

    // Set loan application ID and transaction type based on existing transaction or passed parameters
    $this->loanApplicationId = $loanApplicationId ?? $loanTransaction?->loan_application_id;
    // Determine transaction type: 'issue' if no transaction, 'return' if transaction exists and has no return timestamp, 'return_completed' if transaction exists and is returned
    $this->transactionType = $transactionType ?? ($loanTransaction ? ($loanTransaction->return_timestamp ? 'return_completed' : 'return') : 'issue');


    // Ensure loan application ID is set
    if (!$this->loanApplicationId) {
      // Handle error: Cannot load component without a loan application ID
      session()->flash('error', __('Loan Application ID is required.')); // Translated message
      Log::error("EquipmentChecklist mounted without loanApplicationId.");
      return null; // Stop mount execution (explicit null return)
    }

    // Load the associated loan application
    try {
      $loanApplication = LoanApplication::where('id', $this->loanApplicationId)->with('user', 'responsibleOfficer', 'items')->firstOrFail(); // Eager load applicant, responsible officer, and items
      $this->loanApplication = $loanApplication; // Store application for easy access in render/save
    } catch (ModelNotFoundException $e) {
      session()->flash('error', __('Loan application not found.')); // Translated message
      Log::error("Loan application ID " . $this->loanApplicationId . " not found for EquipmentChecklist.");
      return null; // Stop mount execution (explicit null return)
    }


    // --- Authorization Check (Can user perform this action on this application?) ---
    // Assuming policies for 'issue' and 'processReturn' on LoanApplication or LoanTransaction
    // This is BPM staff performing action on behalf of applicant for a specific application
    try {
      if ($this->transactionType === 'issue') {
        $this->authorize('issue', $this->loanApplication); // BPM staff can issue for this application
      } elseif ($this->transactionType === 'return') {
        $this->authorize('processReturn', $this->loanApplication); // BPM staff can process return for this application
      } elseif ($this->transactionType === 'return_completed' && $this->loanTransaction) {
        $this->authorize('view', $this->loanTransaction); // Can view a completed return transaction
      }
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      Log::warning('EquipmentChecklist: User not authorized for transaction type.', ['user_id' => Auth::id(), 'application_id' => $this->loanApplicationId, 'type' => $this->transactionType]);
      session()->flash('error', __('You are not authorized to perform this action on this loan application.')); // Translated message
      return null; // Stop mount execution (explicit null return)
    }


    // Load lists for dropdowns
    try {
      $this->receivingOfficers = User::orderBy('name')->get(); // List of all users for receiving officer dropdown
      $this->returningOfficers = User::orderBy('name')->get(); // List of all users for returning officer dropdown
      // Master list of accessories (should come from config or DB)
      $this->allAccessoriesList = config('motac.equipment.accessories_list', []); // Get from config or default empty

    } catch (\Exception $e) {
      Log::error('EquipmentChecklist: Error fetching dropdown data.', ['user_id' => Auth::id(), 'exception' => $e]);
      session()->flash('error', __('Could not load required data. Please try again later.')); // Translated message
      // Still continue mount, but dropdowns will be empty
    }


    // --- Populate properties based on existing transaction (if editing/viewing) ---
    if ($this->loanTransaction) {
      // Authorize viewing/updating this specific transaction
      try {
        $this->authorize('view', $this->loanTransaction); // Can view this transaction
      } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        Log::warning('EquipmentChecklist: User not authorized to view transaction.', ['user_id' => Auth::id(), 'transaction_id' => $this->loanTransaction->id]);
        session()->flash('error', __('You are not authorized to view this transaction.')); // Translated message
        return null; // Stop mount execution (explicit null return)
      }


      $this->selectedEquipmentId = $this->loanTransaction->equipment_id;

      if ($this->transactionType === 'issue') {
        // Load issue details for an existing issue transaction
        $this->accessories = $this->loanTransaction->accessories_checklist_on_issue ?? [];
        $this->notes = $this->loanTransaction->notes ?? '';
        $this->receivingOfficerId = $this->loanTransaction->receiving_officer_id;
      } elseif ($this->transactionType === 'return' || $this->transactionType === 'return_completed') {
        // Load details for return processing or completed return view
        if ($this->loanTransaction->return_timestamp) { // If already fully returned ('return_completed' state)
          $this->transactionType = 'return_completed'; // Ensure state is correctly set for viewing completed returns
          $this->accessories = $this->loanTransaction->accessories_checklist_on_return ?? $this->loanTransaction->accessories_checklist_on_issue ?? [];
          $this->notes = $this->loanTransaction->return_notes ?? '';
          $this->returningOfficerId = $this->loanTransaction->returning_officer_id;
          $this->returnStatus = $this->loanTransaction->status;
          $this->equipmentStatusOnReturn = $this->loanTransaction->equipment->status ?? 'available';
        } else {
          // If it's an issued transaction being returned now ('return' state)
          $this->transactionType = 'return'; // Ensure state is correctly set for processing return
          $this->accessories = $this->loanTransaction->accessories_checklist_on_issue ?? []; // Start with issue checklist for comparison
          $this->notes = ''; // Start with empty return notes
          // returningOfficerId and returnStatus/equipmentStatusOnReturn will be selected by the user - keep defaults
        }
      }
    } else {
      // --- Initialize properties for a NEW transaction (Issue only from this component's logic) ---
      if ($this->transactionType === 'issue') {
        $this->receivingOfficerId = $this->loanApplication->responsible_officer_id ?? $this->loanApplication->user_id;
        // Default accessories selection based on all possible accessories
        $this->accessories = $this->allAccessoriesList;
      } else if ($this->transactionType === 'return') {
        // This state implies mounting WITHOUT an existing transaction but with type 'return'
        Log::warning('EquipmentChecklist mounted for new return transaction without existing transaction.', ['user_id' => Auth::id(), 'application_id' => $this->loanApplicationId]);
        session()->flash('error', __('To process a return, please select an issued item from the application details page.')); // Translated message
        return null; // Prevent mounting in this state (explicit null return)
      }
      // If transactionType was null or something else not handled, it would fall through and potentially cause issues.
      // The initial type determination `$transactionType ?? (...)` should handle this, defaulting to 'issue'.
    }
    return null; // Explicitly return null at the end of mount for paths that don't redirect
  }

  /**
   * Render the component view.
   * Fetches equipment lists based on transaction type and loan application.
   *
   * @return \Illuminate\View\View
   */
  public function render(): View
  {
    // Ensure loan application is loaded
    if (!$this->loanApplication) {
      // Render an empty state or error view if application is not loaded (should not happen if mount is successful)
      Log::error('EquipmentChecklist render called without loanApplication loaded.', ['user_id' => Auth::id(), 'application_id' => $this->loanApplicationId]);
      return view('livewire.equipment-checklist', [
        'loanApplication' => null,
        'availableEquipmentList' => collect(),
        'onLoanEquipmentList' => collect(),
        'allAccessoriesList' => $this->allAccessoriesList ?? config('motac.equipment.accessories_list', []), // Provide default empty array
        'receivingOfficers' => $this->receivingOfficers ?? collect(), // Ensure default collections on error
        'returningOfficers' => $this->returningOfficers ?? collect(), // Ensure default collections on error
        'transactionType' => $this->transactionType,
        'selectedEquipmentId' => $this->selectedEquipmentId,
        'returnStatus' => $this->returnStatus,
        'equipmentStatusOnReturn' => $this->equipmentStatusOnReturn,
        'equipmentReturnStatuses' => ['returned', 'damaged', 'lost'],
        'equipmentAssetStatuses' => ['available', 'under_maintenance', 'disposed'],
      ]); // Render with empty data
    }

    // Fetch equipment assets based on transaction type and loan application
    $this->availableEquipmentList = collect(); // Ensure initialized as collection
    $this->onLoanEquipmentList = collect(); // Ensure initialized as collection

    // Only fetch lists if the component is not in 'return_completed' view state
    if ($this->transactionType === 'issue') {
      // Ensure the loan application has loaded its items relationship
      if (!$this->loanApplication->relationLoaded('items')) {
        $this->loanApplication->load('items');
      }
      $requestedEquipmentTypes = $this->loanApplication->items->pluck('equipment_type')->unique()->toArray();

      try {
        $this->availableEquipmentList = Equipment::where('status', 'available')
          ->whereIn('equipment_type', $requestedEquipmentTypes) // Filter by requested types
          ->orderBy('equipment_type')
          ->get();
      } catch (\Exception $e) {
        Log::error('EquipmentChecklist: Error fetching available equipment.', ['user_id' => Auth::id(), 'application_id' => $this->loanApplicationId, 'exception' => $e]);
        session()->flash('error', __('Could not load available equipment list.')); // Translated message
        $this->availableEquipmentList = collect(); // Ensure it's still a collection on error
      }
    } elseif ($this->transactionType === 'return') {
      // For RETURN: Fetch specific Equipment assets that are 'on_loan'
      // AND are linked to this application via a previous 'issued' LoanTransaction.
      // This requires querying the LoanTransaction model.
      try {
        $this->onLoanEquipmentList = Equipment::whereHas(
          'loanTransactions',
          fn($query) =>
          $query->where('loan_application_id', $this->loanApplicationId)
            ->where('status', 'issued') // Only consider transactions still marked as 'issued'
            ->whereNull('return_timestamp') // And not yet returned
        )
          ->orderBy('equipment_type')
          ->get();
      } catch (\Exception $e) {
        Log::error('EquipmentChecklist: Error fetching on-loan equipment.', ['user_id' => Auth::id(), 'application_id' => $this->loanApplicationId, 'exception' => $e]);
        session()->flash('error', __('Could not load on-loan equipment list.')); // Translated message
        $this->onLoanEquipmentList = collect(); // Ensure it's still a collection on error
      }
    }


    return view('livewire.equipment-checklist', [
      'loanApplication' => $this->loanApplication, // Pass the loan application
      'availableEquipmentList' => $this->availableEquipmentList, // List for equipment selection on issue
      'onLoanEquipmentList' => $this->onLoanEquipmentList, // List for equipment selection on return
      'allAccessoriesList' => $this->allAccessoriesList ?? config('motac.equipment.accessories_list', []), // Master list for checkboxes, provide default empty array
      'receivingOfficers' => $this->receivingOfficers, // List for receiving officer dropdown
      'returningOfficers' => $this->returningOfficers, // List for returning officer dropdown
      'transactionType' => $this->transactionType, // Pass transaction type to view for conditional rendering
      'selectedEquipmentId' => $this->selectedEquipmentId, // Pass selected ID for pre-selection in dropdown

      // Pass return specific properties
      'returnStatus' => $this->returnStatus,
      'equipmentStatusOnReturn' => $this->equipmentStatusOnReturn,
      'equipmentReturnStatuses' => ['returned', 'damaged', 'lost'], // Options for return status
      'equipmentAssetStatuses' => ['available', 'under_maintenance', 'disposed'], // Options for equipment status on return
    ]);
  }

  /**
   * Saves the equipment transaction (Issue or Return).
   * Validates input and calls the appropriate service method.
   *
   * @param LoanApplicationService $loanApplicationService The LoanApplicationService instance.
   * @return void
   */
  public function saveTransaction(LoanApplicationService $loanApplicationService): void
  {
    // Ensure user is authenticated
    if (!Auth::check()) {
      Log::warning('EquipmentChecklist: Attempted to save transaction for unauthenticated user.');
      session()->flash('error', __('You must be logged in to save transactions.')); // Translated message
      return;
    }
    $loggedInOfficer = Auth::user();

    // Ensure loan application is loaded
    if (!$this->loanApplication) {
      Log::error('EquipmentChecklist saveTransaction called without loanApplication loaded.', ['user_id' => $loggedInOfficer->id, 'application_id' => $this->loanApplicationId]);
      session()->flash('error', __('Cannot save transaction. Loan application not loaded.')); // Translated message
      return;
    }


    // 1. Define validation rules based on transaction type
    $rules = [
      'selectedEquipmentId' => ['required', 'exists:equipment,id'], // Must select one equipment asset
      'accessories' => ['nullable', 'array'], // Accessories is an optional array
      // Validate each accessory exists in the master list (optional but good practice)
      'accessories.*' => [Rule::in($this->allAccessoriesList)],
      'notes' => ['nullable', 'string', 'max:500'], // Notes are optional

      // Issue-specific validation
      'receivingOfficerId' => [Rule::requiredIf($this->transactionType === 'issue'), 'nullable', 'exists:users,id'], // Receiving officer required on issue

      // Return-specific validation
      'returningOfficerId' => [Rule::requiredIf($this->transactionType === 'return'), 'nullable', 'exists:users,id'], // Returning officer required on return
      'returnStatus' => [Rule::requiredIf($this->transactionType === 'return'), 'nullable', Rule::in(['returned', 'damaged', 'lost'])], // Return status required on return
      'equipmentStatusOnReturn' => [Rule::requiredIf($this->transactionType === 'return'), 'nullable', Rule::in(['available', 'under_maintenance', 'disposed'])], // Equipment status required on return
    ];

    // 2. Validate the form data
    try {
      $validatedData = $this->validate($rules);
    } catch (ValidationException $e) {
      $this->setErrorBag($e->errors());
      Log::info('EquipmentChecklist: Validation failed.', ['user_id' => $loggedInOfficer->id, 'type' => $this->transactionType, 'errors' => $e->errors()]);
      session()->flash('error', __('Please fix the errors in the form.')); // Translated message
      return;
    }


    // Use a transaction for atomicity
    DB::beginTransaction();

    try {
      if ($this->transactionType === 'issue') {
        // Ensure we are not trying to issue if mounting for return
        if ($this->loanTransaction) {
          throw new \Exception("Cannot create new issue transaction when editing an existing one."); // Logic error guard
        }

        // Find the selected equipment asset - Use findOrFail
        $equipment = Equipment::findOrFail($this->selectedEquipmentId);

        // Authorize the issue action on the equipment asset in context of the application
        // Assuming policy check can be on [equipment, application]
        try {
          $this->authorize('issue', [$equipment, $this->loanApplication]);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
          DB::rollBack();
          Log::warning('EquipmentChecklist: User not authorized to issue equipment.', ['user_id' => $loggedInOfficer->id, 'equipment_id' => $equipment->id, 'application_id' => $this->loanApplicationId]);
          session()->flash('error', __('You are not authorized to issue this equipment for this application.')); // Translated message
          return; // Stop execution
        }


        // Prepare transaction data for issue
        $transactionData = [
          'loan_application_id' => $this->loanApplicationId,
          'equipment_id' => $this->selectedEquipmentId,
          'issuing_officer_id' => $loggedInOfficer->id, // Logged-in user is issuing
          'receiving_officer_id' => $validatedData['receivingOfficerId'], // Selected receiving officer
          'accessories_checklist_on_issue' => $validatedData['accessories'] ?? [], // Save selected accessories with a specific column name
          'notes' => $validatedData['notes'] ?? null, // Save notes with a specific column name
          'status' => 'issued', // Initial status is 'issued'
          // issue_timestamp is set in the service
        ];

        // Call the service method to issue the equipment - Pass the single Equipment model AND the issuing officer
        $this->loanTransaction = $loanApplicationService->issueEquipment(
          $this->loanApplication, // Pass the loan application
          $equipment, // Pass the specific equipment asset (single model)
          $transactionData, // Pass the transaction details
          $loggedInOfficer // Pass the BPM staff issuing (Fixes "Expected 4 arguments. Found 3." and "Missing argument $issuingOfficer")
        );

        DB::commit(); // Commit transaction on success

        session()->flash('success', __('Equipment issued successfully.')); // Use success flash

      } elseif ($this->transactionType === 'return') {
        // Ensure we are processing return on an existing issued transaction
        if (!$this->loanTransaction || $this->loanTransaction->status !== 'issued' || $this->loanTransaction->return_timestamp) {
          Log::warning('EquipmentChecklist: Invalid transaction selected for return processing.', ['user_id' => $loggedInOfficer->id, 'transaction_id' => $this->loanTransaction?->id, 'status' => $this->loanTransaction?->status]);
          session()->flash('error', __('Invalid transaction selected for return processing.')); // Translated message
          DB::rollBack(); // Rollback any potential unintended changes (shouldn't be any if no transaction loaded)
          return;
        }

        // Authorize the return action on the application or transaction
        try {
          $this->authorize('processReturn', $this->loanApplication); // Authorize on the application
          // Or authorize on the transaction if policy is defined there: $this->authorize('processReturn', $this->loanTransaction);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
          DB::rollBack();
          Log::warning('EquipmentChecklist: User not authorized to process return.', ['user_id' => $loggedInOfficer->id, 'application_id' => $this->loanApplicationId, 'transaction_id' => $this->loanTransaction->id]);
          session()->flash('error', __('You are not authorized to process the return for this application.')); // Translated message
          return; // Stop execution
        }

        // Prepare return details data
        $returnDetails = [
          'returning_officer_id' => $validatedData['returningOfficerId'], // Selected returning officer
          'accessories_checklist_on_return' => $validatedData['accessories'] ?? [], // Save accessories checklist on return
          'return_notes' => $validatedData['notes'] ?? null, // Save return notes
          'status' => $validatedData['returnStatus'], // Save return transaction status ('returned', 'damaged', 'lost')
          'equipment_status_on_return' => $validatedData['equipmentStatusOnReturn'], // Save equipment status after return
          // return_timestamp is set in the service
        ];

        // Call the service method to process the equipment return - Pass the transaction AND the accepting officer
        $this->loanTransaction = $loanApplicationService->processReturn(
          $this->loanTransaction, // Pass the existing issued transaction
          $returnDetails, // Pass the return details
          $loggedInOfficer // Pass the BPM staff accepting the return (Fixes "Expected 3 arguments. Found 2." and "Missing argument $acceptingOfficer")
        );

        DB::commit(); // Commit transaction on success

        session()->flash('success', __('Equipment return processed successfully.')); // Use success flash

        // After successful return, update transactionType to 'return_completed' to show read-only view
        $this->transactionType = 'return_completed';
      } else {
        // Handle unsupported transaction type - should be caught earlier in mount, but safeguard
        Log::error("Unsupported transaction type in saveTransaction: " . $this->transactionType, ['user_id' => $loggedInOfficer->id]);
        session()->flash('error', __('Unsupported transaction type.')); // Translated message
        DB::rollBack();
        return;
      }
    } catch (ModelNotFoundException $e) {
      DB::rollBack(); // Ensure rollback on ModelNotFound
      Log::error("Error in saveTransaction: Selected equipment or related record not found.", ['user_id' => $loggedInOfficer->id, 'selected_equipment_id' => $this->selectedEquipmentId, 'transaction_type' => $this->transactionType, 'exception' => $e]);
      session()->flash('error', __('Selected equipment or related record not found.')); // Translated message
      return;
    } catch (ValidationException $e) {
      // This catch block is technically redundant as validate() throws the exception, but kept as example
      DB::rollBack(); // Rollback on validation errors if using manual validation flow
      Log::info('EquipmentChecklist: Validation failed caught in generic catch.', ['user_id' => $loggedInOfficer->id, 'type' => $this->transactionType, 'errors' => $e->errors()]);
      session()->flash('error', __('Please fix the errors in the form.'));
      return;
    } catch (\Exception $e) {
      // Catch any other exceptions from the service or logic
      DB::rollBack(); // Ensure rollback on any other exception
      Log::error("Failed to save equipment transaction. Type: " . $this->transactionType . ". Error: " . $e->getMessage(), ['user_id' => $loggedInOfficer->id, 'application_id' => $this->loanApplicationId, 'exception' => $e]);
      session()->flash('error', __('An error occurred while saving the transaction: ') . $e->getMessage()); // Translated message
      return; // Stop execution on error
    }


    // 4. Reset form fields if a new issue was created and we are staying on the form
    // If you redirect or emit an event to close a modal, resetting here might not be necessary.
    // This reset is primarily if the component stays visible after a successful *issue*.
    if ($this->transactionType === 'issue') {
      $this->reset([
        'selectedEquipmentId',
        'accessories',
        'notes',
        'receivingOfficerId',
        // return specific fields are not applicable to reset after issue
      ]);
      // You might want to refresh the available equipment list here if staying on the form
      // $this->render();
    }


    // Optional: Emit event to refresh a parent component or redirect
    // $this->dispatch('transactionSaved', ['transactionId' => $this->loanTransaction->id, 'type' => $this->transactionType]); // Example event
    // return $this->redirect(route('loan-applications.show', $this->loanApplicationId)); // Example redirect to application show page
    // Returning void/null means stay on the current page/component
  }


  // --- Helper methods (Optional) ---

  /**
   * Listen for equipment selected event (e.g., from a modal or parent component).
   * Populates selected equipment ID and updates accessories checklist based on equipment type accessories.
   *
   * @param int $equipmentId
   * @return void
   */
  public function equipmentSelected(int $equipmentId): void
  {
    $this->selectedEquipmentId = $equipmentId;

    // Optional: Fetch the selected equipment model to get its default accessories
    try {
      $equipment = Equipment::findOrFail($equipmentId);
      // Assuming Equipment model has an accessories_list attribute (e.g., JSON column)
      // Ensure equipment accessories_list is an array when merging
      $equipmentAccessories = is_array($equipment->accessories_list) ? $equipment->accessories_list : [];
      $this->accessories = array_unique(array_merge($equipmentAccessories, $this->accessories)); // Add equipment default accessories to current list

    } catch (ModelNotFoundException $e) {
      Log::warning('EquipmentChecklist: equipmentSelected called with invalid ID.', ['user_id' => Auth::id(), 'equipment_id' => $equipmentId]);
      session()->flash('error', __('Selected equipment not found.')); // Translated message
      $this->selectedEquipmentId = null; // Reset selected ID
      $this->accessories = $this->allAccessoriesList ?? []; // Reset accessories to default list
    }
  }

  // Method to update accessories array from checkboxes - handled by Livewire automatically if bound
  // public function updatedAccessories($value) { } // Usually not needed unless extra logic is required


  // Method to set default receiving/returning officer based on checkbox (if applicant is officer)
  // This might be handled in the view or mount method based on is_applicant_responsible on the Loan Application
  // public function setOfficerDefaults() { ... }

}
