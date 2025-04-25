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
use Illuminate\Support\Str; // Import Str facade


class EquipmentChecklist extends Component
{
  use AuthorizesRequests; // Use the AuthorizesRequests trait

  // --- Component State Properties ---
  public $loanApplicationId; // ID of the loan application this transaction is for
  public $transactionType; // 'issue' or 'return'

  // Properties for selecting the specific equipment asset
  public $selectedEquipmentId; // ID of the specific Equipment asset being issued/returned

  // Properties for the accessory checklist and notes
  public $accessories = []; // Array of accessories selected from the master list
  public $notes = ''; // General notes for the transaction (issue notes or return notes)

  // Properties for officers involved in the transaction
  // The BPM staff member currently logged in is the Issuing Officer (on issue) or Return Accepting Officer (on return)
  public $receivingOfficerId; // Who is physically receiving the equipment on issue (usually applicant or responsible officer)
  public $returningOfficerId; // Who is physically returning the equipment (usually applicant or responsible officer)

  // Properties specific to return transaction details
  public $returnStatus = 'returned'; // Status of the return ('returned', 'damaged', 'lost') - aligns with LoanTransaction status
  public $equipmentStatusOnReturn = 'available'; // Status of the equipment asset after return inspection ('available', 'under_maintenance', 'disposed') - aligns with Equipment status


  // Properties to hold dropdown data for selecting officers
  public $receivingOfficers = []; // List of users selectable as receiving officer
  public $returningOfficers = []; // List of users selectable as returning officer
  public $availableEquipmentList = []; // List of equipment assets available for ISSUE for this application
  public $onLoanEquipmentList = []; // List of equipment assets ON LOAN for this application (for RETURN)
  public $allAccessoriesList = []; // Master list of all possible accessories


  // Optional: If editing an existing transaction
  public $loanTransactionId;
  protected ?LoanTransaction $loanTransaction = null; // The transaction being created or updated
  protected ?LoanApplication $loanApplication = null; // The associated loan application - FIX: Added property declaration here


  // --- Listeners (Optional) ---
  // protected $listeners = ['loadTransactionData']; // Example if triggering from another component


  /**
   * Mount the component. Initializes properties based on transaction type and loan application.
   *
   * @param LoanTransaction|null $loanTransaction Optional existing transaction for editing/viewing.
   * @param int|null $loanApplicationId ID of the loan application if creating a new transaction.
   * @param string|null $transactionType Type of transaction ('issue' or 'return') if creating a new one.
   */
  public function mount(?LoanTransaction $loanTransaction = null, $loanApplicationId = null, $transactionType = null)
  {
    // Ensure user is authenticated (BPM staff should be logged in)
    if (!Auth::check()) {
      session()->flash('error', 'You must be logged in to manage equipment transactions.');
      return $this->redirect(route('login')); // Redirect to login if not authenticated
    }

    $this->loanTransaction = $loanTransaction; // Store the transaction if editing
    $this->loanTransactionId = $loanTransaction?->id; // Store transaction ID

    // Set loan application ID and transaction type based on existing transaction or passed parameters
    $this->loanApplicationId = $loanApplicationId ?? $loanTransaction?->loan_application_id;
    // Determine transaction type: 'issue' if no transaction, 'return' if transaction exists and has return timestamp
    $this->transactionType = $transactionType ?? ($loanTransaction ? ($loanTransaction->return_timestamp ? 'return_completed' : 'issue') : 'issue'); // Use 'issue' or 'return', add 'return_completed' state


    // Ensure loan application ID is set
    if (!$this->loanApplicationId) {
      // Handle error: Cannot load component without a loan application ID
      session()->flash('error', 'Loan Application ID is required.');
      Log::error("EquipmentChecklist mounted without loanApplicationId.");
      return; // Stop mount execution
    }

    // Load the associated loan application
    try {
      $loanApplication = LoanApplication::where('id', $this->loanApplicationId)->with('user', 'responsibleOfficer')->firstOrFail(); // Eager load applicant and responsible officer
      $this->loanApplication = $loanApplication; // Store application for easy access in render/save
    } catch (ModelNotFoundException $e) {
      session()->flash('error', 'Loan application not found.');
      Log::error("Loan application ID " . $this->loanApplicationId . " not found for EquipmentChecklist.");
      return; // Stop mount execution
    }


    // --- Authorization Check (Can user perform this action on this application?) ---
    // Assuming policies for 'issue' and 'processReturn' on LoanApplication or LoanTransaction
    if ($this->transactionType === 'issue') {
      $this->authorize('issue', $this->loanApplication); // BPM staff can issue for this application
    } elseif ($this->transactionType === 'return') {
      $this->authorize('processReturn', $this->loanApplication); // BPM staff can process return for this application
    }
    // Note: Authorizing view/update on an existing transaction is handled below if editing.


    // Load lists for dropdowns
    $this->receivingOfficers = User::orderBy('name')->get(); // List of all users for receiving officer dropdown
    $this->returningOfficers = User::orderBy('name')->get(); // List of all users for returning officer dropdown
    // Master list of accessories (should come from config or DB)
    $this->allAccessoriesList = config('motac.equipment.accessories_list', ['Power Cable', 'HDMI Cable', 'VGA Cable', 'Mouse', 'Keyboard', 'Carry Bag', 'Remote Control']); // Get from config or default


    // --- Populate properties based on existing transaction (if editing/viewing) ---
    if ($this->loanTransaction) {
      // Authorize viewing/updating this specific transaction
      $this->authorize('view', $this->loanTransaction); // Can view this transaction
      // If it's an 'issue' transaction that hasn't been returned yet, BPM might be able to edit some details?
      // $this->authorize('update', $this->loanTransaction); // Can update this transaction


      $this->selectedEquipmentId = $this->loanTransaction->equipment_id;

      if ($this->transactionType === 'issue') {
        // Load issue details for an existing issue transaction
        $this->accessories = $this->loanTransaction->accessories_checklist_on_issue ?? []; // Load issue checklist
        $this->notes = $this->loanTransaction->notes ?? ''; // Load issue notes
        $this->receivingOfficerId = $this->loanTransaction->receiving_officer_id; // Load receiving officer
        // Issuing officer is the user who created the transaction ($this->loanTransaction->issuing_officer_id), not a form field

      } elseif ($this->transactionType === 'return') {
        // Load return details for an existing return transaction (or start returning an issued one)
        if ($this->loanTransaction->return_timestamp) { // If already fully returned
          $this->accessories = $this->loanTransaction->accessories_checklist_on_return ?? $this->loanTransaction->accessories_checklist_on_issue ?? []; // Load return checklist if available, fallback to issue checklist
          $this->notes = $this->loanTransaction->return_notes ?? ''; // Load return notes
          $this->returningOfficerId = $this->loanTransaction->returning_officer_id; // Load returning officer
          $this->returnStatus = $this->loanTransaction->status; // Load transaction status ('returned', 'damaged', 'lost')
          // Need to load equipment status from the Equipment model linked to the transaction
          $this->equipmentStatusOnReturn = $this->loanTransaction->equipment->status ?? 'available'; // Load equipment status after return (assuming stored or derived)
          // Return Accepting Officer is the user who processed the return ($this->loanTransaction->return_accepting_officer_id), not a form field
        } else {
          // If it's an issued transaction being returned now, load issue details and prepare for return input
          $this->transactionType = 'return'; // Set state to 'return' for processing input
          $this->accessories = $this->loanTransaction->accessories_checklist_on_issue ?? []; // Start with issue checklist for comparison
          $this->notes = ''; // Start with empty return notes
          // returningOfficerId and returnStatus/equipmentStatusOnReturn will be selected by the user
        }
      }
    } else {
      // --- Initialize properties for a NEW transaction (Issue only from this component's logic) ---
      // If new issue, default receiving officer to applicant or responsible officer
      if ($this->transactionType === 'issue') {
        // Ensure loanApplication is loaded before accessing its properties
        $this->receivingOfficerId = $this->loanApplication->responsible_officer_id ?? $this->loanApplication->user_id;
        $this->accessories = $this->allAccessoriesList; // Default to all accessories selected for issue checklist
      }
      // If new return, this case should not happen if returning an existing issued item.
      // Returns are processed by updating an existing 'issued' transaction.
      // You would typically navigate to the list of issued items for an application and click 'Return' on one of them.
      // This component would then be mounted with the existing LoanTransaction model for that issued item.

      // If you need to support creating a 'return' transaction from scratch (e.g., for lost/damaged items not previously issued via system),
      // this logic would need refinement. For standard workflow, mount with existing transaction for return.
      if ($this->transactionType === 'return') {
        session()->flash('error', 'To process a return, please select an issued item from the application details page.');
        // Consider redirecting or emitting an event to close the modal/component
        return; // Prevent mounting in this state
      }
    }
  }

  /**
   * Render the component view.
   * Fetches equipment lists based on transaction type and loan application.
   *
   * @return \Illuminate\View\View
   */
  public function render()
  {
    // Ensure loan application is loaded
    if (!$this->loanApplication) {
      return view('livewire.equipment-checklist'); // Render empty or error view (create this view)
    }

    // Fetch equipment assets based on transaction type and loan application
    $this->availableEquipmentList = []; // Equipment available for ISSUE for this application
    $this->onLoanEquipmentList = []; // Equipment currently ON LOAN for this application (for RETURN)


    if ($this->transactionType === 'issue') {
      // For ISSUE: Fetch specific Equipment assets that are 'available'
      // AND match the equipment types and quantities requested in the APPROVED LoanApplicationItems.
      // This requires joining or querying based on the application's items.
      // Simplified: Find 'available' equipment assets whose type matches any type requested in the application items.
      // Ensure the loan application has loaded its items relationship if needed here
      // $this->loanApplication->load('items');
      $requestedEquipmentTypes = $this->loanApplication->items->pluck('equipment_type')->unique()->toArray();

      $this->availableEquipmentList = Equipment::where('status', 'available')
        ->whereIn('equipment_type', $requestedEquipmentTypes) // Filter by requested types
        ->orderBy('equipment_type')
        ->get();
    } elseif ($this->transactionType === 'return') {
      // For RETURN: Fetch specific Equipment assets that are 'on_loan'
      // AND are linked to this application via a previous 'issued' LoanTransaction.
      // This requires querying the LoanTransaction model.
      $this->onLoanEquipmentList = Equipment::whereHas(
        'loanTransactions',
        fn($query) =>
        $query->where('loan_application_id', $this->loanApplicationId)
          ->where('status', 'issued') // Only consider transactions still marked as 'issued'
          ->whereNull('return_timestamp') // And not yet returned
      )
        ->orderBy('equipment_type')
        ->get();
    }


    return view('livewire.equipment-checklist', [
      'loanApplication' => $this->loanApplication, // Pass the loan application
      'availableEquipmentList' => $this->availableEquipmentList, // List for equipment selection on issue
      'onLoanEquipmentList' => $this->onLoanEquipmentList, // List for equipment selection on return
      'allAccessoriesList' => $this->allAccessoriesList, // Master list for checkboxes
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
  public function saveTransaction(LoanApplicationService $loanApplicationService)
  {
    // Ensure user is authenticated
    if (!Auth::check()) {
      session()->flash('error', 'You must be logged in to save transactions.');
      return;
    }
    $loggedInOfficer = Auth::user();


    // 1. Define validation rules based on transaction type
    $rules = [
      'selectedEquipmentId' => 'required|exists:equipment,id', // Must select one equipment asset
      'accessories' => 'nullable|array', // Accessories is an optional array
      'notes' => 'nullable|string|max:500', // Notes are optional

      // Issue-specific validation
      'receivingOfficerId' => Rule::requiredIf($this->transactionType === 'issue') . '|nullable|exists:users,id', // Receiving officer required on issue

      // Return-specific validation
      'returningOfficerId' => Rule::requiredIf($this->transactionType === 'return') . '|nullable|exists:users,id', // Returning officer required on return
      'returnStatus' => Rule::requiredIf($this->transactionType === 'return') . '|nullable|in:returned,damaged,lost', // Return status required on return
      'equipmentStatusOnReturn' => Rule::requiredIf($this->transactionType === 'return') . '|nullable|in:available,under_maintenance,disposed', // Equipment status required on return
    ];

    // 2. Validate the form data
    try {
      $validatedData = $this->validate($rules);
    } catch (\Illuminate\Validation\ValidationException $e) {
      $this->setErrorBag($e->errors());
      session()->flash('error', 'Please fix the errors in the form.');
      return;
    }


    // 3. Call the appropriate service method based on transaction type
    try {
      if ($this->transactionType === 'issue') {
        // Find the selected equipment asset - FIX: Use where()->firstOrFail() for explicit single model retrieval
        $equipment = Equipment::where('id', $this->selectedEquipmentId)->firstOrFail();


        // Authorize the issue action on the equipment asset
        $this->authorize('issue', [$equipment, $this->loanApplication]); // Assuming policy check can be on equipment or [equipment, application]


        // Prepare transaction data for issue
        $transactionData = [
          'loan_application_id' => $this->loanApplicationId,
          'equipment_id' => $this->selectedEquipmentId,
          'issuing_officer_id' => $loggedInOfficer->id, // Logged-in user is issuing
          'receiving_officer_id' => $validatedData['receivingOfficerId'], // Selected receiving officer
          'accessories' => $validatedData['accessories'] ?? [], // Save selected accessories
          'notes' => $validatedData['notes'] ?? null, // Save notes
          'status' => 'issued', // Initial status is 'issued'
          // issue_timestamp and other fields are set in the service
        ];

        // Call the service method to issue the equipment - FIX: Pass the single Equipment model
        $this->loanTransaction = $loanApplicationService->issueEquipment(
          $this->loanApplication, // Pass the loan application
          $equipment, // Pass the specific equipment asset (single model)
          $transactionData, // Pass the transaction details
          $loggedInOfficer // Pass the BPM staff issuing
        );

        session()->flash('message', 'Equipment issued successfully.');
      } elseif ($this->transactionType === 'return') {
        // Ensure we are editing an existing issued transaction to process return
        if (!$this->loanTransaction || $this->loanTransaction->status !== 'issued') {
          session()->flash('error', 'Invalid transaction selected for return processing.');
          return;
        }

        // Authorize the return action on the application or transaction
        $this->authorize('processReturn', $this->loanApplication); // Authorize on the application
        // Or authorize on the transaction: $this->authorize('processReturn', $this->loanTransaction);


        // Prepare return details data
        $returnDetails = [
          'returning_officer_id' => $validatedData['returningOfficerId'], // Selected returning officer
          'accessories' => $validatedData['accessories'] ?? [], // Save accessories checklist on return
          'notes' => $validatedData['notes'] ?? null, // Save return notes
          'status' => $validatedData['returnStatus'], // Save return transaction status ('returned', 'damaged', 'lost')
          'equipment_status_on_return' => $validatedData['equipmentStatusOnReturn'], // Save equipment status after return
        ];

        // Call the service method to process the equipment return
        $this->loanTransaction = $loanApplicationService->processReturn(
          $this->loanTransaction, // Pass the existing issued transaction
          $returnDetails, // Pass the return details
          $loggedInOfficer // Pass the BPM staff accepting the return
        );

        session()->flash('message', 'Equipment return processed successfully.');
      } else {
        // Handle unsupported transaction type
        session()->flash('error', 'Unsupported transaction type.');
        Log::error("Unsupported transaction type in EquipmentChecklist: " . $this->transactionType);
        return;
      }
    } catch (ModelNotFoundException $e) {
      session()->flash('error', 'Selected equipment or related record not found.');
      Log::error("Error in saveTransaction: " . $e->getMessage());
      return;
    } catch (\Exception $e) {
      // Catch exceptions from the service (including authorization exceptions if not handled by Livewire trait)
      session()->flash('error', 'An error occurred while saving the transaction: ' . $e->getMessage());
      Log::error("Failed to save equipment transaction. Type: " . $this->transactionType . ". Error: " . $e->getMessage());
      return; // Stop execution on error
    }


    // 4. Reset form fields and redirect/emit event
    $this->reset([
      'selectedEquipmentId',
      'accessories',
      'notes',
      'receivingOfficerId',
      'returningOfficerId',
      'returnStatus',
      'equipmentStatusOnReturn',
    ]); // Reset all transaction-specific fields

    // Optional: Emit event to refresh a parent component or redirect
    // $this->dispatch('transactionSaved'); // Example event
    // return $this->redirect(route('loan-applications.show', $this->loanApplicationId)); // Example redirect to application show page

  }


  // --- Helper methods (Optional) ---

  // Method to update accessories array from checkboxes
  public function updatedAccessories($value)
  {
    // Livewire handles checkbox arrays automatically, but you can add logic here if needed
  }

  // Method to set default receiving/returning officer based on checkbox (if applicant is officer)
  // This might be handled in the view or mount method based on is_applicant_responsible on the Loan Application
  // public function setOfficerDefaults() { ... }

}
