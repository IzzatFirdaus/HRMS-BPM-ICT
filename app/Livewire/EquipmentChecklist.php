<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\LoanTransaction; // Assuming it's used for recording issue/return
use App\Models\Equipment; // To select specific equipment assets
use App\Models\LoanApplication; // To link to the application

class EquipmentChecklist extends Component
{
  public $loanApplicationId; // ID of the loan application
  public $transactionType; // 'issue' or 'return'
  public $selectedEquipment = []; // Array of equipment IDs being issued/returned
  public $accessories = []; // Array of accessories for the checklist
  public $notes = ''; // Notes for the transaction
  public $issuingOfficerId; // Or returning/accepting officer ID
  public $loanTransaction; // The transaction being created/updated

  // Listeners for events, e.g., to load data when transactionType/loanApplicationId are set
  protected $listeners = ['loadTransactionData'];

  public function mount(?LoanTransaction $loanTransaction = null, $loanApplicationId = null, $transactionType = null)
  {
    $this->loanTransaction = $loanTransaction;
    $this->loanApplicationId = $loanApplicationId ?? $loanTransaction?->loan_application_id;
    $this->transactionType = $transactionType ?? ($loanTransaction ? ($loanTransaction->return_timestamp ? 'returned' : 'issued') : null);


    if ($this->loanTransaction) {
      // Load existing transaction data for editing/viewing
      $this->selectedEquipment = [$this->loanTransaction->equipment_id]; // Assuming one equipment per transaction for simplicity
      $this->accessories = $this->loanTransaction->accessories_checklist_on_issue ?? []; // Load issue checklist
      if ($this->transactionType === 'returned') {
        $this->accessories = $this->loanTransaction->accessories_checklist_on_return ?? $this->accessories; // Load return checklist if available
        $this->notes = $this->loanTransaction->return_notes;
      }
      $this->notes = $this->loanTransaction->notes ?? ''; // General notes field if you add one
      // Load officer ID etc.
    } else {
      // Initialize for a new transaction
      $this->accessories = ['Power Cable', 'Carry Bag', 'Mouse', 'Keyboard']; // Default accessories checklist items
    }
  }


  public function render()
  {
    $loanApplication = LoanApplication::find($this->loanApplicationId);
    // You might fetch available equipment based on the requested items in the application
    $availableEquipment = []; // Fetch available equipment for issuance
    $onLoanEquipment = []; // Fetch equipment currently on loan for return

    if ($this->transactionType === 'issue') {
      // Fetch equipment that is 'available' and matches types requested in the application
      // This logic needs to be implemented based on the LoanApplicationItem
      $availableEquipment = Equipment::where('status', 'available')->get(); // Simplified: all available equipment
    } elseif ($this->transactionType === 'return') {
      // Fetch equipment that is 'on_loan' and linked to this application or the user
      // This logic needs to be implemented
      $onLoanEquipment = Equipment::where('status', 'on_loan')
        // ->whereHas('currentTransaction', fn($q) => $q->where('loan_application_id', $this->loanApplicationId)) // Need relation
        ->get(); // Simplified: all on loan equipment
    }


    return view('livewire.equipment-checklist', [
      'loanApplication' => $loanApplication,
      'availableEquipment' => $availableEquipment,
      'onLoanEquipment' => $onLoanEquipment,
      'allAccessoriesList' => ['Power Cable', 'HDMI Cable', 'VGA Cable', 'Mouse', 'Keyboard', 'Carry Bag', 'Remote Control'], // Master list for checkboxes
    ]);
  }

  public function saveTransaction()
  {
    // Validation based on transaction type
    $rules = [
      'selectedEquipment' => 'required|array|min:1',
      'selectedEquipment.*' => 'exists:equipment,id',
      'accessories' => 'nullable|array',
      'notes' => 'nullable|string',
      // Add validation for officer ID if necessary
    ];

    if ($this->transactionType === 'issue') {
      // Add issue-specific rules
    } elseif ($this->transactionType === 'return') {
      // Add return-specific rules
    }

    $this->validate($rules);

    DB::transaction(function () {
      if (!$this->loanTransaction) {
        // Create a new transaction
        $this->loanTransaction = new LoanTransaction([
          'loan_application_id' => $this->loanApplicationId,
          'equipment_id' => $this->selectedEquipment[0] ?? null, // Assuming one equipment per transaction for now
          'issuing_officer_id' => Auth::id(), // Assign current user as officer
          'issue_timestamp' => now(),
          'accessories_checklist_on_issue' => $this->accessories,
          'status' => 'issued',
          'notes' => $this->notes, // Or dedicated notes field
        ]);
        $this->loanTransaction->save();

        // Update equipment status
        $equipment = Equipment::find($this->selectedEquipment[0]);
        if ($equipment) {
          $equipment->status = 'on_loan';
          $equipment->save();
        }

        // Update loan application status (e.g., to 'issued' or 'partially_issued')
        $loanApplication = LoanApplication::find($this->loanApplicationId);
        if ($loanApplication) {
          $loanApplication->status = 'issued'; // Simplify status update
          $loanApplication->save();
          // Notify applicant? Notify next approver?
        }
      } elseif ($this->transactionType === 'return') {
        // Update the existing transaction for return
        $this->loanTransaction->fill([
          'returning_officer_id' => Auth::id(), // Assuming current user is returning officer
          'return_accepting_officer_id' => Auth::id(), // Assuming current user is BPM accepting return
          'return_timestamp' => now(),
          'accessories_checklist_on_return' => $this->accessories,
          'return_notes' => $this->notes,
          'status' => 'returned',
        ]);
        $this->loanTransaction->save();

        // Update equipment status
        $equipment = $this->loanTransaction->equipment;
        if ($equipment) {
          $equipment->status = 'available'; // Or 'under_maintenance' based on return notes/inspection
          $equipment->save();
        }

        // Update loan application status (e.g., to 'returned')
        $loanApplication = $this->loanTransaction->loanApplication;
        if ($loanApplication) {
          // Check if all items for the application are returned before setting application status to 'returned'
          $loanApplication->status = 'returned'; // Simplified status update
          $loanApplication->save();
          // Notify applicant?
        }
      }
    });

    session()->flash('message', 'Transaction saved successfully.');
    $this->reset(['selectedEquipment', 'accessories', 'notes']); // Reset form fields
    // Redirect or emit event to refresh a list
    // $this->redirect(...);
  }


  // Add other methods like removeEquipment, updateAccessoryQuantity etc.
}
