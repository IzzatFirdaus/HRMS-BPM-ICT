<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User; // To get current user details or select responsible officer
use App\Models\LoanApplication; // To create application records
use App\Models\LoanApplicationItem; // To create related items
use App\Services\LoanApplicationService; // Use the service for logic
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Validation\Rule; // Import Rule for validation
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Trait for policy checks
use Illuminate\Database\Eloquent\ModelNotFoundException; // Import ModelNotFoundException
use Illuminate\Support\Str; // Import Str facade - FIX: Added this import

// Import models for applicant details display
use App\Models\Position; // Assuming Position model exists for user relationship
use App\Models\Department; // Assuming Department model exists for user relationship
use App\Models\Grade; // Assuming Grade model exists for user relationship


class LoanRequestForm extends Component
{
  use AuthorizesRequests; // Use the AuthorizesRequests trait

  // Properties for Loan Details (Bahagian 1 & 2)
  public $purpose = ''; // Tujuan Permohonan
  public $location = ''; // Lokasi
  public $loan_start_date; // Tarikh Pinjaman
  public $loan_end_date; // Tarikh Dijangka Pulang

  // Properties for Responsible Officer (Bahagian 2)
  public $is_applicant_responsible = true; // Sila tandakan jika Pemohon adalah Pegawai Bertanggungjawab
  public $responsible_officer_id; // Pegawai Bertanggungjawab (if different from applicant)
  public $responsibleOfficers = []; // For dropdown (list of potential responsible officers)

  // Properties for Equipment Items (Bahagian 3)
  public $items = [
    ['equipment_type' => '', 'quantity_requested' => 1, 'notes' => ''],
  ]; // Array to hold multiple item requests (Jenis Peralatan, Kuantiti, Catatan)

  // Properties for Confirmation (Bahagian 4)
  public $applicant_confirmation = false; // Pengesahan Pemohon checkbox

  // Optional: If updating an existing draft application
  public $applicationId;
  protected ?LoanApplication $loanApplication = null; // Type hint and initialize as nullable

  // Applicant's details displayed from User model (Bahagian 1 - Not form fields)
  public $applicantName; // Nama Penuh
  public $applicantJobTitleGrade; // Jawatan & Gred (Combined for display)
  public $applicantDivisionUnit; // Bahagian/Unit
  public $applicantPhone; // No. Telefon


  // No static $rules property or getRules() method, we will use validate() calls in methods


  /**
   * Real-time validation.
   *
   * @param string $propertyName
   */
  public function updated($propertyName)
  {
    // Define validation rules for real-time checks.
    // These can be a subset of the full submission rules.
    // Ensure fields required for draft are validated in real-time.
    $realtimeRules = [
      'purpose' => 'nullable|string|max:500', // Can be null for draft
      'location' => 'nullable|string|max:255', // Can be null for draft
      'loan_start_date' => 'nullable|date|after_or_equal:today',
      'loan_end_date' => 'nullable|date|after_or_equal:loan_start_date',
      'is_applicant_responsible' => 'boolean',
      'responsible_officer_id' => Rule::requiredIf(!$this->is_applicant_responsible) . '|nullable|exists:users,id', // Conditional required based on checkbox
      'items' => 'nullable|array', // Items array can be empty for draft
      'items.*.equipment_type' => 'nullable|string|max:255', // Can be null for draft item
      'items.*.quantity_requested' => 'nullable|integer|min:1', // Can be null/0 for draft item
      'items.*.notes' => 'nullable|string|max:500', // Can be null for draft item
      // applicant_confirmation is typically validated on submit, not real-time
    ];

    // Adjust rules for nested items if the item row is empty or partially filled
    if (Str::startsWith($propertyName, 'items.')) { // Uses Str facade
      $index = (int) explode('.', $propertyName)[1];
      // If equipment_type is null, quantity and notes are not strictly required for draft
      if (empty($this->items[$index]['equipment_type'])) {
        $realtimeRules[$propertyName] = 'nullable|string|max:255'; // Equipment type can be null in draft
        $realtimeRules['items.' . $index . '.quantity_requested'] = 'nullable|integer|min:1';
        $realtimeRules['items.' . $index . '.notes'] = 'nullable|string|max:500';
      } else {
        // If equipment_type is filled, quantity becomes required
        $realtimeRules['items.' . $index . '.quantity_requested'] = 'required|integer|min:1';
      }
    }


    try {
      $this->validateOnly($propertyName, $realtimeRules);
    } catch (\Illuminate\Validation\ValidationException $e) {
      // Keep existing errors for other properties
      $this->setErrorBag($this->getErrorBag()->merge($e->errors()));
      // Do not re-throw to prevent halting real-time updates
    }
  }

  /**
   * Mount the component.
   *
   * @param LoanApplication|null $loanApplication Optional existing application for editing.
   */
  public function mount(?LoanApplication $loanApplication = null)
  {
    // Ensure user is authenticated to access the form
    if (!Auth::check()) {
      return $this->redirect(route('login')); // Redirect to login if not authenticated
    }

    $user = Auth::user(); // The authenticated user is the applicant

    $this->loanApplication = $loanApplication;

    // Load users eligible to be responsible officers (e.g., all users or users with specific roles/grades)
    // Keep as User::all() for simplicity, though filtering might be needed based on requirements.
    $this->responsibleOfficers = User::orderBy('name')->get(); // Order by name for display


    // Populate applicant's details from the authenticated user model for display
    $this->applicantName = $user->full_name ?? $user->name; // Adjust based on your User model attributes
    $this->applicantJobTitleGrade = ($user->position->name ?? 'N/A') . ' & ' . ($user->grade->name ?? 'N/A'); // Assuming position and grade relationships
    $this->applicantDivisionUnit = $user->department->name ?? 'N/A'; // Assuming department relationship
    $this->applicantPhone = $user->mobile_number; // Assuming mobile_number attribute


    // If editing an existing application, populate form fields
    if ($loanApplication) {
      // Authorize if the user can view/update this specific application (should be a draft owned by the user)
      $this->authorize('view', $loanApplication); // Check policy for viewing
      $this->authorize('update', $loanApplication); // Check policy for updating (should only be allowed for 'draft')


      // Prevent editing if the application is not in 'draft' status
      if ($loanApplication->status !== 'draft') {
        // If it's not a draft, redirect to the show page or an error page
        // You cannot edit an application that is already submitted or processed.
        session()->flash('error', 'Cannot edit application as it is not in draft status.');
        return $this->redirect(route('loan-applications.show', $loanApplication));
      }

      // Populate properties from the existing application
      $this->applicationId = $loanApplication->id;
      $this->purpose = $loanApplication->purpose;
      $this->location = $loanApplication->location;
      // Ensure dates are formatted correctly (YYYY-MM-DD) for HTML date input
      $this->loan_start_date = $loanApplication->loan_start_date ? $loanApplication->loan_start_date->format('Y-m-d') : null;
      $this->loan_end_date = $loanApplication->loan_end_date ? $loanApplication->loan_end_date->format('Y-m-d') : null;

      $this->responsible_officer_id = $loanApplication->responsible_officer_id;
      // Determine is_applicant_responsible checkbox state based on responsible_officer_id
      $this->is_applicant_responsible = is_null($loanApplication->responsible_officer_id);

      // Load existing items and convert collection to array for Livewire binding
      // Ensure item data structure matches the initial $items array structure
      $this->items = $loanApplication->items->map(function ($item) {
        return [
          'id' => $item->id ?? null, // Include item ID if exists (for potential update/delete)
          'equipment_type' => $item->equipment_type,
          'quantity_requested' => $item->quantity_requested,
          'notes' => $item->notes,
        ];
      })->toArray();

      // If no items are loaded (e.g., new draft), add a default empty row
      if (empty($this->items)) {
        $this->items[] = ['equipment_type' => '', 'quantity_requested' => 1, 'notes' => ''];
      }


      // Determine applicant_confirmation checkbox state based on timestamp
      $this->applicant_confirmation = $loanApplication->applicant_confirmation_timestamp !== null;
    } else {
      // For new applications, set default values if any
      $this->loan_start_date = now()->format('Y-m-d'); // Default start date to today
      $this->loan_end_date = now()->addWeek()->format('Y-m-d'); // Default end date to one week from today

      // Default responsible officer to applicant
      $this->is_applicant_responsible = true;
      $this->responsible_officer_id = null; // Applicant is responsible, so ID is null
    }
  }

  /**
   * Render the component view.
   *
   * @return \Illuminate\View\View
   */
  public function render()
  {
    // Pass data to the view
    return view('livewire.loan-request-form', [
      'responsibleOfficers' => $this->responsibleOfficers, // List for responsible officer dropdown
      'loanApplication' => $this->loanApplication, // Pass the application instance if editing
      // Pass applicant display details to the view
      'applicantName' => $this->applicantName,
      'applicantJobTitleGrade' => $this->applicantJobTitleGrade,
      'applicantDivisionUnit' => $this->applicantDivisionUnit,
      'applicantPhone' => $this->applicantPhone,
    ]);
  }

  /**
   * Method to add a new equipment item row to the items array.
   *
   * @return void
   */
  public function addItem()
  {
    // Ensure there's at least one item before adding a new empty one if deleting empties is allowed
    // if (count($this->items) > 0 && empty(array_filter($this->items[count($this->items) - 1], fn($value) => $value !== '' && $value !== null))) {
    //     // Do not add if the last row is completely empty
    //     return;
    // }
    $this->items[] = ['equipment_type' => '', 'quantity_requested' => 1, 'notes' => ''];

    // Optional: Re-validate the items list in real-time after adding
    // $this->validate(['items' => $this->getValidationRules()['items']]);
  }

  /**
   * Method to remove an equipment item row from the items array.
   *
   * @param int $index The index of the item to remove.
   * @return void
   */
  public function removeItem($index)
  {
    unset($this->items[$index]);
    $this->items = array_values($this->items); // Re-index the array

    // Ensure there's always at least one item row, even if empty, for UI
    if (empty($this->items)) {
      $this->items[] = ['equipment_type' => '', 'quantity_requested' => 1, 'notes' => ''];
    }

    // Optional: Re-validate the items list in real-time after removing
    // $this->validate(['items' => $this->getValidationRules()['items']]);
  }

  /**
   * Save the application form data as a draft.
   *
   * @param LoanApplicationService $loanApplicationService
   * @return void
   */
  public function saveAsDraft(LoanApplicationService $loanApplicationService)
  {
    // Ensure user is authenticated
    if (!Auth::check()) {
      session()->flash('error', 'You must be logged in to save a draft.');
      return;
    }
    $user = Auth::user();

    // 1. Define validation rules for saving a draft.
    // These are typically less strict than submission rules.
    $draftRules = [
      'purpose' => 'nullable|string|max:500', // Purpose optional for draft
      'location' => 'nullable|string|max:255', // Location optional for draft
      'loan_start_date' => 'nullable|date|after_or_equal:today',
      'loan_end_date' => 'nullable|date|after_or_equal:loan_start_date',
      'is_applicant_responsible' => 'boolean',
      'responsible_officer_id' => Rule::requiredIf(!$this->is_applicant_responsible) . '|nullable|exists:users,id',
      'items' => 'nullable|array', // Items array optional for draft
      'items.*.equipment_type' => 'nullable|string|max:255',
      'items.*.quantity_requested' => 'nullable|integer|min:1', // Min 1 if type is present
      'items.*.notes' => 'nullable|string|max:500',
      'applicant_confirmation' => 'boolean', // Confirmation optional for draft
    ];

    // 2. Validate the form data against draft rules
    try {
      // Conditional validation check for item quantities if type is filled
      $validatedData = $this->validate($draftRules);
      foreach ($this->items as $index => $item) {
        if (!empty($item['equipment_type'])) {
          $this->validateOnly('items.' . $index . '.quantity_requested', ['items.' . $index . '.quantity_requested' => 'required|integer|min:1']);
        }
      }
      // Re-validate all after specific checks
      $validatedData = $this->validate($draftRules);
    } catch (\Illuminate\Validation\ValidationException $e) {
      $this->setErrorBag($e->errors());
      session()->flash('error', 'Please fix the errors in the form.');
      return;
    }


    // Determine responsible officer ID
    $responsibleOfficerId = $this->is_applicant_responsible ? null : ($validatedData['responsible_officer_id'] ?? null); // Use validated data


    $applicationData = [
      'user_id' => $user->id,
      'responsible_officer_id' => $responsibleOfficerId, // Use determined ID
      'purpose' => $validatedData['purpose'] ?? null,
      'location' => $validatedData['location'] ?? null,
      'loan_start_date' => $validatedData['loan_start_date'] ?? null,
      'loan_end_date' => $validatedData['loan_end_date'] ?? null,
      'status' => 'draft', // Set initial status to draft
      'applicant_confirmation_timestamp' => $this->applicant_confirmation ? now() : null, // Save confirmation state if checked
    ];

    // Filter out empty item rows if needed for saving draft
    $itemsData = collect($validatedData['items'] ?? [])
      ->filter(fn($item) => !empty($item['equipment_type']) || !empty($item['notes']) || ($item['quantity_requested'] ?? 0) > 0)
      ->values()
      ->toArray();


    if ($this->applicationId) {
      // If applicationId exists, update the existing application (which must be a draft)
      try {
        $application = LoanApplication::where('id', $this->applicationId)->firstOrFail();
      } catch (ModelNotFoundException $e) {
        session()->flash('error', 'Draft application not found.');
        return;
      }


      // Authorize if the user can update this application (must be a draft owned by the user)
      $this->authorize('update', $application);

      // Check if the application is indeed in 'draft' status before updating
      if ($application->status !== 'draft') {
        session()->flash('error', 'Cannot update application as it is not in draft status.');
        // Redirect to the show page as it's already processed
        return $this->redirect(route('loan-applications.show', $application));
      }

      // Use service method to update the draft application and its items
      // Ensure updateApplication method exists in LoanApplicationService
      $application = $loanApplicationService->updateApplication($application, $applicationData, $itemsData);

      session()->flash('message', 'Loan request draft updated successfully!');
    } else {
      // If no applicationId, create a new application record with status 'draft'
      // Authorize if the user can create an application
      $this->authorize('create', LoanApplication::class);

      // Use service method to create the application and its items
      // Ensure createApplication method exists in LoanApplicationService and accepts these arguments
      $application = $loanApplicationService->createApplication($user, $applicationData, $itemsData);

      // Store the new application ID to allow future updates/submission
      $this->applicationId = $application->id;
      $this->loanApplication = $application; // Update the internal property as well

      session()->flash('message', 'Loan request draft saved successfully!');
    }

    // Keep the user on the form and display the flash message
  }


  /**
   * Submit the application form for approval.
   * This transitions a draft application to 'pending_support' status.
   *
   * @param LoanApplicationService $loanApplicationService
   * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|void
   */
  public function submitApplication(LoanApplicationService $loanApplicationService)
  {
    // Ensure user is authenticated
    if (!Auth::check()) {
      session()->flash('error', 'You must be logged in to submit an application.');
      return;
    }
    $user = Auth::user();

    // 1. Define validation rules for final submission.
    // These rules are typically more strict and include required fields like purpose, responsible officer, and confirmation.
    $submitRules = [
      'purpose' => 'required|string|max:500', // Purpose is required for submission
      'location' => 'required|string|max:255', // Location is required for submission
      'loan_start_date' => 'required|date|after_or_equal:today',
      'loan_end_date' => 'required|date|after_or_equal:loan_start_date',
      'is_applicant_responsible' => 'boolean',
      'responsible_officer_id' => Rule::requiredIf(!$this->is_applicant_responsible) . '|nullable|exists:users,id', // Conditional required
      'items' => 'required|array|min:1', // At least one item is required
      'items.*.equipment_type' => 'required|string|max:255', // Equipment type required for submitted items
      'items.*.quantity_requested' => 'required|integer|min:1', // Quantity required and min 1 for submitted items
      'items.*.notes' => 'nullable|string|max:500',
      'applicant_confirmation' => 'accepted', // Confirmation checkbox must be ticked
    ];

    // 2. Validate the form data against submission rules
    try {
      $validatedData = $this->validate($submitRules);
    } catch (\Illuminate\Validation\ValidationException $e) {
      $this->setErrorBag($e->errors());
      session()->flash('error', 'Please fix the errors in the form before submitting.');
      return;
    }


    // Ensure we have an application ID (submission requires a saved draft)
    if (!$this->applicationId) {
      // This should not happen if the form logic/UI is correct (submit button only visible if draft exists)
      session()->flash('error', 'Cannot submit. Please save as draft first.');
      return; // Stop submission
    }

    // Find the existing draft application
    try {
      $application = LoanApplication::where('id', $this->applicationId)->firstOrFail();
    } catch (ModelNotFoundException $e) {
      session()->flash('error', 'Draft application not found.');
      return;
    }


    // Authorize if the user can update/submit this application (must be a draft owned by the user)
    $this->authorize('update', $application); // Check policy for updating/submitting

    // Double-check that the application is indeed in 'draft' status before submitting
    if ($application->status !== 'draft') {
      session()->flash('error', 'Application is not in draft status and cannot be submitted.');
      // Redirect to the show page as it's already processed
      return $this->redirect(route('loan-applications.show', $application));
    }

    // Determine responsible officer ID for saving
    $responsibleOfficerId = $this->is_applicant_responsible ? null : $validatedData['responsible_officer_id'];


    // Prepare application data for saving/submitting
    $applicationData = [
      'responsible_officer_id' => $responsibleOfficerId, // Use determined ID
      'purpose' => $validatedData['purpose'],
      'location' => $validatedData['location'],
      'loan_start_date' => $validatedData['loan_start_date'],
      'loan_end_date' => $validatedData['loan_end_date'],
      'applicant_confirmation_timestamp' => now(), // Set submission timestamp
    ];

    // Prepare items data for saving/submitting
    // Filter out any completely empty rows if add/remove logic allows them
    $itemsData = collect($validatedData['items'])
      ->filter(fn($item) => !empty($item['equipment_type'])) // Require equipment type for submission
      ->values()
      ->toArray();

    // Ensure items data is not empty after filtering
    if (empty($itemsData)) {
      session()->flash('error', 'Please add at least one equipment item to the application.');
      $this->addError('items', 'At least one equipment item is required.');
      return;
    }


    // 3. Use service methods to update the application data and initiate the workflow
    // Ensure updateApplication exists in LoanApplicationService and updates application data and items
    $application = $loanApplicationService->updateApplication($application, $applicationData, $itemsData);


    // Initiate the approval workflow (changes status from 'draft' to 'pending_support', creates first approval)
    // Ensure initiateApprovalWorkflow method exists in LoanApplicationService
    // The service should find the correct approver(s) (e.g., Gred 41+ based on department/location)
    $application = $loanApplicationService->initiateApprovalWorkflow($application); // This method needs to be implemented in LoanApplicationService.php


    // 4. Redirect to the application's show page or a confirmation page with a success message
    session()->flash('message', 'Loan request submitted successfully!');
    return $this->redirect(route('loan-applications.show', $application)); // Redirect to the show page

    // Alternative redirect: Redirect to the dashboard
    // return $this->redirect(route('dashboard'));
  }


  // Add other methods as needed (e.g., saveAsDraft)
}
