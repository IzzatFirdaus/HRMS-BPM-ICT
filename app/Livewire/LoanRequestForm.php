<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User; // To get current user details or select responsible officer
use App\Models\LoanApplication; // To create application records
use App\Models\LoanApplicationItem; // To create related items
use App\Services\LoanApplicationService; // Use the service for logic
use Illuminate\Support\Facades\Auth;


class LoanRequestForm extends Component
{
  // Properties for Loan Details (Part 2)
  public $purpose = '';
  public $location = '';
  public $loan_start_date;
  public $loan_end_date;

  // Properties for Responsible Officer (Part 2 - Optional)
  public $is_applicant_responsible = true;
  public $responsible_officer_id;
  public $responsibleOfficers = []; // For dropdown

  // Properties for Equipment Items (Part 3)
  public $items = [
    ['equipment_type' => '', 'quantity_requested' => 1, 'notes' => ''],
  ]; // Array to hold multiple item requests

  // Properties for Confirmation (Part 4)
  public $applicant_confirmation = false;

  // Optional: If updating an existing draft application
  public $applicationId;
  protected $loanApplication;


  // Validation Rules - adjust based on your form and requirements
  protected $rules = [
    'purpose' => 'required|string|max:500',
    'location' => 'required|string|max:255',
    'loan_start_date' => 'required|date|after_or_equal:today',
    'loan_end_date' => 'required|date|after_or_equal:loan_start_date',
    'is_applicant_responsible' => 'boolean',
    'responsible_officer_id' => 'nullable|exists:users,id', // Conditional based on is_applicant_responsible
    'items' => 'required|array|min:1',
    'items.*.equipment_type' => 'required|string|max:255',
    'items.*.quantity_requested' => 'required|integer|min:1',
    'items.*.notes' => 'nullable|string|max:500',
    'applicant_confirmation' => 'accepted', // Requires the checkbox to be ticked and value is '1'
  ];

  // Validation rule for responsible_officer_id is conditional
  public function getRules() // Changed to public
  {
    $rules = $this->rules;
    if (!$this->is_applicant_responsible) {
      $rules['responsible_officer_id'] = 'required|exists:users,id';
    }
    return $rules;
  }

  // Real-time validation
  public function updated($propertyName)
  {
    $this->validateOnly($propertyName);
  }

  public function mount(?LoanApplication $loanApplication = null)
  {
    $this->loanApplication = $loanApplication;

    // Load responsible officers (e.g., all users or users with specific roles/grades)
    $this->responsibleOfficers = User::all(); // Simplified: all users are potential responsible officers

    // If editing an existing application, populate form fields
    if ($loanApplication) {
      // $this->authorize('update', $loanApplication); // Check policy

      $this->applicationId = $loanApplication->id;
      $this->purpose = $loanApplication->purpose;
      $this->location = $loanApplication->location;
      $this->loan_start_date = $loanApplication->loan_start_date->format('Y-m-d');
      $this->loan_end_date = $loanApplication->loan_end_date->format('Y-m-d');
      $this->responsible_officer_id = $loanApplication->responsible_officer_id;
      $this->is_applicant_responsible = is_null($loanApplication->responsible_officer_id); // Determine checkbox state

      // Load existing items
      $this->items = $loanApplication->items->toArray(); // Convert collection to array for Livewire

      $this->applicant_confirmation = $loanApplication->applicant_confirmation_timestamp !== null; // Determine checkbox state

      // Prevent editing if not in 'draft' status
      if ($loanApplication->status !== 'draft') {
        // Redirect or disable form fields
        // $this->redirect(route('loan-applications.show', $loanApplication));
      }
    }
  }

  public function render()
  {
    // Pass data to the view
    return view('livewire.loan-request-form', [
      'responsibleOfficers' => $this->responsibleOfficers,
      'loanApplication' => $this->loanApplication, // Pass for conditional rendering in view
    ]);
  }

  // Method to add a new equipment item row
  public function addItem()
  {
    $this->items[] = ['equipment_type' => '', 'quantity_requested' => 1, 'notes' => ''];
  }

  // Method to remove an equipment item row
  public function removeItem($index)
  {
    unset($this->items[$index]);
    $this->items = array_values($this->items); // Re-index the array
  }


  public function submit(LoanApplicationService $loanApplicationService)
  {
    // 1. Validate the form data using dynamic rules
    $validatedData = $this->validate($this->getRules());

    // Ensure confirmation is required for submission
    if (!$this->applicant_confirmation) {
      $this->addError('applicant_confirmation', 'You must confirm to submit.');
      return; // Stop submission if not confirmed
    }

    // Determine responsible officer ID
    $responsibleOfficerId = $this->is_applicant_responsible ? null : $validatedData['responsible_officer_id'];


    // 2. Process the form submission (Create or Update)
    $user = Auth::user();

    $applicationData = [
      'user_id' => $user->id,
      'responsible_officer_id' => $responsibleOfficerId,
      'purpose' => $validatedData['purpose'],
      'location' => $validatedData['location'],
      'loan_start_date' => $validatedData['loan_start_date'],
      'loan_end_date' => $validatedData['loan_end_date'],
      // Status will be set by the service
      'applicant_confirmation_timestamp' => now(), // Timestamp on submission
    ];

    $itemsData = $validatedData['items']; // Items data

    if ($this->applicationId) {
      // Update existing application (e.g., from draft to pending_support)
      $application = LoanApplication::findOrFail($this->applicationId);
      // $this->authorize('update', $application); // Policy check

      // Use service to handle update and status transition
      $loanApplicationService->updateApplication($application, $applicationData, $itemsData);


      session()->flash('message', 'Loan request updated and submitted successfully!');
    } else {
      // Create a new application
      // $this->authorize('create', LoanApplication::class); // Policy check

      // Use service to create the application and items
      $application = $loanApplicationService->createApplication($user, $applicationData, $itemsData);

      session()->flash('message', 'Loan request draft saved successfully!');
      // If you want to redirect to the form in edit mode after saving draft:
      // return $this->redirect(route('loan-applications.edit', $application));

    }

    // Optional: Redirect to a status page or dashboard after submission
    // $this->redirect(route('loan-applications.show', $application));


    // Reset form fields after successful submission/save
    $this->resetExcept(['responsibleOfficers']); // Keep dropdown data
    $this->items = [['equipment_type' => '', 'quantity_requested' => 1, 'notes' => '']]; // Reset items to one empty row


  }

  // Add other methods as needed (e.g., saveAsDraft)
}
