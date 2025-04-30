<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\LoanApplication;
use App\Models\LoanApplicationItem;
use App\Services\LoanApplicationService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Position;
use App\Models\Department;
use App\Models\Grade;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class LoanRequestForm extends Component
{
  use AuthorizesRequests;

  // Properties for Loan Details (Bahagian 1 & 2)
  public string $purpose = ''; // Tujuan Permohonan
  public string $location = ''; // Lokasi
  public ?string $loan_start_date = null; // Tarikh Pinjaman
  public ?string $loan_end_date = null; // Tarikh Dijangka Pulang

  // Properties for Responsible Officer (Bahagian 2)
  public bool $is_applicant_responsible = true; // Sila tandakan jika Pemohon adalah Pegawai Bertanggungjawab
  public ?int $responsible_officer_id = null; // Pegawai Bertanggungjawab (if different from applicant)
  public Collection $responsibleOfficers; // For dropdown (list of potential responsible officers)

  // Properties for Equipment Items (Bahagian 3)
  public array $items = [
    ['equipment_type' => '', 'quantity_requested' => 1, 'notes' => ''],
  ]; // Array to hold multiple item requests (Jenis Peralatan, Kuantiti, Catatan)

  // Properties for Confirmation (Bahagian 4)
  public bool $applicant_confirmation = false; // Pengesahan Pemohon checkbox

  // Optional: If updating an existing draft application
  public ?int $applicationId = null; // Property for application ID
  protected ?LoanApplication $loanApplication = null; // Type hint and initialize as nullable

  // Applicant's details displayed from User model (Bahagian 1 - Not form fields)
  public ?string $applicantName = null; // Nama Penuh
  public ?string $applicantJobTitleGrade = null; // Jawatan & Gred (Combined for display)
  public ?string $applicantDivisionUnit = null; // Bahagian/Unit
  public ?string $applicantPhone = null; // No. Telefon

  /**
   * Real-time validation rules.
   * Only validates properties touched by the user or necessary for basic UI state.
   * Full validation happens on save/submit.
   *
   * @return array
   */
  protected function realtimeRules(): array
  {
    return [
      'purpose' => 'nullable|string|max:500',
      'location' => 'nullable|string|max:255',
      'loan_start_date' => 'nullable|date|after_or_equal:today',
      'loan_end_date' => 'nullable|date|after_or_equal:loan_start_date',
      'is_applicant_responsible' => 'boolean',
      // Conditional required based on checkbox for real-time validation
      'responsible_officer_id' => [Rule::requiredIf(!$this->is_applicant_responsible && $this->responsible_officer_id !== null), 'nullable', 'exists:users,id'], // Added check for not null responsible_officer_id when applicant is not responsible
      'items' => 'nullable|array', // Validate the array structure itself
      'items.*.equipment_type' => 'nullable|string|max:255',
      // Quantity only needs basic real-time validation; min:1 check is more critical on submit
      'items.*.quantity_requested' => 'nullable|integer|min:1', // Still check minimum if value is entered
      'items.*.notes' => 'nullable|string|max:500',
      // Confirmation is typically validated on submit, not real-time
    ];
  }

  /**
   * Get validation rules for saving a draft.
   * Fields are generally nullable, allowing partial completion.
   *
   * @return array
   */
  protected function getDraftRules(): array
  {
    return [
      'purpose' => 'nullable|string|max:500',
      'location' => 'nullable|string|max:255',
      'loan_start_date' => 'nullable|date|after_or_equal:today',
      'loan_end_date' => 'nullable|date|after_or_equal:loan_start_date',
      'is_applicant_responsible' => 'boolean',
      // Conditional required based on checkbox for draft save
      'responsible_officer_id' => [Rule::requiredIf(!$this->is_applicant_responsible && $this->responsible_officer_id !== null), 'nullable', 'exists:users,id'], // Conditional required if checkbox is false AND a value has been selected
      'items' => 'nullable|array',
      'items.*.equipment_type' => 'nullable|string|max:255',
      // For draft, quantity can be nullable if type is empty, but if a number is entered, it must be >= 1
      'items.*.quantity_requested' => 'nullable|integer|min:1',
      'items.*.notes' => 'nullable|string|max:500',
      'applicant_confirmation' => 'boolean', // Optional for draft
    ];
  }

  /**
   * Get validation rules for final submission.
   * Fields required for processing are mandatory.
   *
   * @return array
   */
  protected function getSubmitRules(): array
  {
    // Define base rules for submitted items
    $itemRules = [
      'equipment_type' => 'required|string|max:255', // Equipment type required on submit
      'quantity_requested' => 'required|integer|min:1', // Quantity required and min 1 on submit
      'notes' => 'nullable|string|max:500',
    ];

    return [
      'purpose' => 'required|string|max:500', // Purpose required for submission
      'location' => 'required|string|max:255', // Location required for submission
      'loan_start_date' => 'required|date|after_or_equal:today',
      'loan_end_date' => 'required|date|after_or_equal:loan_start_date',
      'is_applicant_responsible' => 'boolean',
      // Conditional required based on checkbox for submission
      'responsible_officer_id' => [Rule::requiredIf(!$this->is_applicant_responsible), 'nullable', 'exists:users,id'], // Required if checkbox is false
      'items' => 'required|array|min:1', // At least one item row required
      // Apply the base item rules to each item in the array
      'items.*.equipment_type' => $itemRules['equipment_type'],
      'items.*.quantity_requested' => $itemRules['quantity_requested'],
      'items.*.notes' => $itemRules['notes'],
      'applicant_confirmation' => 'accepted', // Confirmation checkbox must be ticked
    ];
  }

  /**
   * Real-time validation.
   *
   * @param string $propertyName
   * @return void
   */
  public function updated(string $propertyName): void
  {
    // Use the defined realtime rules
    try {
      $this->validateOnly($propertyName, $this->realtimeRules());
    } catch (ValidationException $e) {
      // Keep existing errors for other properties
      $this->setErrorBag($this->getErrorBag()->merge($e->errors()));
      // Do not re-throw to prevent halting real-time updates
      Log::debug('LoanRequestForm: Real-time validation failed', ['property' => $propertyName, 'errors' => $e->errors()]);
    }
  }

  /**
   * Mount the component.
   *
   * @param LoanApplication|null $loanApplication Optional existing application for editing.
   * @return \Illuminate\Http\RedirectResponse|null
   */
  public function mount(?LoanApplication $loanApplication = null): RedirectResponse|null
  {
    if (!Auth::check()) {
      Log::warning('LoanRequestForm mounted for unauthenticated user.');
      session()->flash('error', __('You must be logged in to access the loan request form.'));
      return $this->redirect(route('login'));
    }

    $user = Auth::user();

    // Initialize properties
    $this->responsibleOfficers = collect();
    $this->items = [['equipment_type' => '', 'quantity_requested' => 1, 'notes' => '']];
    $this->applicant_confirmation = false;
    $this->applicationId = null;
    $this->loanApplication = null; // Explicitly nullify for new form
    $this->purpose = '';
    $this->location = '';
    $this->loan_start_date = now()->format('Y-m-d'); // Default start date to today
    $this->loan_end_date = now()->addWeek()->format('Y-m-d'); // Default end date to one week from today
    $this->is_applicant_responsible = true; // Default responsible officer to applicant
    $this->responsible_officer_id = null; // Applicant is responsible, so ID is null

    // Load users eligible to be responsible officers
    try {
      // Consider scoping this query if not all users should be selectable
      $this->responsibleOfficers = User::orderBy('name')->get();
    } catch (\Exception $e) {
      Log::error('LoanRequestForm: Error fetching responsible officers.', ['user_id' => $user->id, 'exception' => $e]);
      session()->flash('error', __('Could not load responsible officers. Please try again later.'));
      $this->responsibleOfficers = collect(); // Set to empty collection on error
    }

    // Populate applicant's details for display
    $this->applicantName = $user->full_name ?? $user->name ?? __('N/A');
    // Use null-safe operator '?->' and nullish coalescing '??' for robustness
    $this->applicantJobTitleGrade = ($user->position?->name ?? __('N/A')) . ' & ' . ($user->grade?->name ?? __('N/A'));
    $this->applicantDivisionUnit = $user->department?->name ?? __('N/A');
    $this->applicantPhone = $user->mobile_number ?? __('N/A');

    // If editing an existing application, populate form fields
    if ($loanApplication) {
      // Ensure the application exists and is a draft owned by the user
      if ($loanApplication->user_id !== $user->id || $loanApplication->status !== 'draft') {
        Log::warning('LoanRequestForm: Attempted to mount non-owned or non-draft application for editing.', ['user_id' => $user->id, 'application_id' => $loanApplication->id, 'status' => $loanApplication->status]);
        // You might choose a different message depending on desired behavior
        session()->flash('error', __('Cannot edit this application.'));
        // Redirect away if not allowed to edit
        return $this->redirect(route('loan-applications.show', $loanApplication));
      }

      // Authorize viewing/updating (policy check is still good practice)
      try {
        $this->authorize('view', $loanApplication);
        $this->authorize('update', $loanApplication); // Policy should enforce draft status and ownership
      } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        Log::warning('LoanRequestForm: User not authorized to edit application via policy.', ['user_id' => $user->id, 'application_id' => $loanApplication->id]);
        session()->flash('error', __('You are not authorized to edit this application.'));
        return $this->redirect(route('loan-applications.show', $loanApplication));
      }

      // Populate properties from the existing application
      $this->applicationId = $loanApplication->id;
      $this->loanApplication = $loanApplication; // Store the model instance
      $this->purpose = $loanApplication->purpose ?? '';
      $this->location = $loanApplication->location ?? '';
      $this->loan_start_date = $loanApplication->loan_start_date ? $loanApplication->loan_start_date->format('Y-m-d') : null;
      $this->loan_end_date = $loanApplication->loan_end_date ? $loanApplication->loan_end_date->format('Y-m-d') : null;

      $this->responsible_officer_id = $loanApplication->responsible_officer_id;
      // Determine is_applicant_responsible checkbox state
      $this->is_applicant_responsible = is_null($loanApplication->responsible_officer_id);

      // Load existing items
      $this->items = $loanApplication->items->map(function ($item) {
        return [
          'id' => $item->id, // Include item ID for updates
          'equipment_type' => $item->equipment_type ?? '',
          'quantity_requested' => $item->quantity_requested ?? 1,
          'notes' => $item->notes ?? '',
        ];
      })->toArray();

      // If no items were loaded (e.g., empty draft), add a default empty row
      if (empty($this->items)) {
        $this->items[] = ['equipment_type' => '', 'quantity_requested' => 1, 'notes' => ''];
      }

      // Confirmation state is not loaded from timestamp for drafting, user needs to confirm again on submit
      // $this->applicant_confirmation = $loanApplication->applicant_confirmation_timestamp !== null; // Removed this line as confirmation is for submission
    }

    return null; // Explicitly return null if no redirect happens
  }

  /**
   * Render the component view.
   *
   * @return \Illuminate\View\View
   */
  public function render(): View
  {
    return view('livewire.loan-request-form', [
      'responsibleOfficers' => $this->responsibleOfficers,
      'loanApplication' => $this->loanApplication,
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
  public function addItem(): void
  {
    // Add a new default item row
    $this->items[] = ['equipment_type' => '', 'quantity_requested' => 1, 'notes' => ''];
  }

  /**
   * Method to remove an equipment item row from the items array.
   *
   * @param int $index The index of the item to remove.
   * @return void
   */
  public function removeItem(int $index): void
  {
    if (isset($this->items[$index])) {
      unset($this->items[$index]);
      $this->items = array_values($this->items); // Re-index the array

      // Ensure there's always at least one item row for UI
      if (empty($this->items)) {
        $this->items[] = ['equipment_type' => '', 'quantity_requested' => 1, 'notes' => ''];
      }
    }
  }

  /**
   * Save the application form data as a draft.
   *
   * @param LoanApplicationService $loanApplicationService
   * @return void
   */
  public function saveAsDraft(LoanApplicationService $loanApplicationService): void
  {
    if (!Auth::check()) {
      Log::warning('LoanRequestForm: Attempted to save draft for unauthenticated user.');
      session()->flash('error', __('You must be logged in to save a draft.'));
      return;
    }
    $user = Auth::user();

    // 1. Validate the form data against draft rules
    try {
      // Use the dedicated draft rules method
      $validatedData = $this->validate($this->getDraftRules());
    } catch (ValidationException $e) {
      $this->setErrorBag($e->errors());
      Log::info('LoanRequestForm: Draft validation failed.', ['user_id' => $user->id, 'errors' => $e->errors()]);
      session()->flash('error', __('Please fix the errors in the form.'));
      return;
    }

    // Determine responsible officer ID based on checkbox state
    $responsibleOfficerId = $validatedData['is_applicant_responsible'] ? null : ($validatedData['responsible_officer_id'] ?? null);

    $applicationData = [
      'user_id' => $user->id,
      'responsible_officer_id' => $responsibleOfficerId,
      'purpose' => $validatedData['purpose'] ?? null,
      'location' => $validatedData['location'] ?? null,
      'loan_start_date' => $validatedData['loan_start_date'] ?? null,
      'loan_end_date' => $validatedData['loan_end_date'] ?? null,
      'status' => 'draft', // Ensure status is always draft for saveAsDraft
      // applicant_confirmation_timestamp is NOT set when saving draft
    ];

    // Filter out item rows that are completely empty for draft
    // You might adjust this filter based on whether you want to save rows with only notes/quantity
    $itemsData = collect($validatedData['items'] ?? [])
      ->filter(
        fn($item) =>
        !empty($item['equipment_type']) ||
          !empty($item['notes']) ||
          ($item['quantity_requested'] ?? 0) > 0
      )
      ->values() // Re-index the array
      ->toArray();

    try {
      if ($this->applicationId) {
        // If applicationId exists, update the existing draft application
        // Find the application instance (already checked in mount, but good to be safe)
        $application = $this->loanApplication ?? LoanApplication::where('id', $this->applicationId)->firstOrFail();

        // Policy check should ensure it's the user's draft
        $this->authorize('update', $application);

        // Double-check draft status
        if ($application->status !== 'draft') {
          Log::warning('LoanRequestForm: Attempted to update non-draft application via saveAsDraft.', ['user_id' => $user->id, 'application_id' => $application->id, 'status' => $application->status]);
          session()->flash('error', __('Cannot save draft as the application status is no longer draft.'));
          return; // Or redirect
        }

        // Use service method to update the draft application and its items
        $application = $loanApplicationService->updateApplication($application, $applicationData, $itemsData);
        session()->flash('success', __('Loan request draft updated successfully!'));
      } else {
        // If no applicationId, create a new draft application
        $this->authorize('create', LoanApplication::class); // Authorize creation

        // Use service method to create the application and its items
        $application = $loanApplicationService->createApplication($user, $applicationData, $itemsData);

        // Store the new application ID and instance
        $this->applicationId = $application->id;
        $this->loanApplication = $application;
        session()->flash('success', __('Loan request draft saved successfully!'));
      }

      // Optional: Emit an event if needed (e.g., to notify other parts of the UI)
      // $this->dispatch('draftSaved', $application->id);

      // Explicit return for void function
      return;
    } catch (ModelNotFoundException $e) {
      Log::error('LoanRequestForm: Draft application not found during save.', ['user_id' => $user->id, 'application_id' => $this->applicationId, 'exception' => $e]);
      session()->flash('error', __('Draft application not found.'));
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      Log::warning('LoanRequestForm: User not authorized during saveAsDraft.', ['user_id' => $user->id, 'application_id' => $this->applicationId ?? 'N/A', 'exception' => $e]);
      session()->flash('error', __('You are not authorized to perform this action.'));
    } catch (\Exception $e) {
      Log::error('LoanRequestForm: Failed to save draft application.', ['user_id' => $user->id, 'application_id' => $this->applicationId ?? 'N/A', 'exception' => $e]);
      session()->flash('error', __('An error occurred while saving the draft.'));
    }

    // Keep the user on the form
    return; // Explicit return for void function even in catch blocks
  }

  /**
   * Submit the application form for approval.
   * This transitions a draft application to 'pending_support' status.
   *
   * @param LoanApplicationService $loanApplicationService
   * @return \Illuminate\Http\RedirectResponse|null
   */
  public function submitApplication(LoanApplicationService $loanApplicationService): RedirectResponse|null
  {
    if (!Auth::check()) {
      Log::warning('LoanRequestForm: Attempted to submit application for unauthenticated user.');
      session()->flash('error', __('You must be logged in to submit an application.'));
      return null;
    }
    $user = Auth::user();

    // 1. Validate the form data against submission rules
    try {
      // Use the dedicated submit rules method
      $validatedData = $this->validate($this->getSubmitRules());
    } catch (ValidationException $e) {
      $this->setErrorBag($e->errors());
      Log::info('LoanRequestForm: Submission validation failed.', ['user_id' => $user->id, 'errors' => $e->errors()]);
      session()->flash('error', __('Please fix the errors in the form before submitting.'));
      return null;
    }

    // Ensure we have an application ID (submission requires a saved draft)
    if (!$this->applicationId) {
      Log::error('LoanRequestForm: Attempted to submit application without existing draft ID.', ['user_id' => $user->id]);
      session()->flash('error', __('Cannot submit. Please save as draft first.'));
      return null;
    }

    // Find the existing draft application
    try {
      $application = $this->loanApplication ?? LoanApplication::where('id', $this->applicationId)->firstOrFail();
    } catch (ModelNotFoundException $e) {
      Log::error('LoanRequestForm: Draft application not found for submission.', ['user_id' => $user->id, 'application_id' => $this->applicationId, 'exception' => $e]);
      session()->flash('error', __('Draft application not found.'));
      return null;
    }

    // Authorize submission (should check policy for updating/submitting)
    try {
      // Policy should enforce draft status and ownership for submission
      $this->authorize('update', $application);
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      Log::warning('LoanRequestForm: User not authorized to submit application.', ['user_id' => $user->id, 'application_id' => $application->id]);
      session()->flash('error', __('You are not authorized to submit this application.'));
      return $this->redirect(route('loan-applications.show', $application)); // Redirect if not authorized
    }

    // Double-check that the application is indeed in 'draft' status before submitting
    if ($application->status !== 'draft') {
      Log::warning('LoanRequestForm: Attempted to submit non-draft application.', ['user_id' => $user->id, 'application_id' => $application->id, 'status' => $application->status]);
      session()->flash('error', __('Application is not in draft status and cannot be submitted.'));
      return $this->redirect(route('loan-applications.show', $application)); // Redirect to the show page
    }

    // Determine responsible officer ID
    $responsibleOfficerId = $validatedData['is_applicant_responsible'] ? null : $validatedData['responsible_officer_id'];

    // Prepare application data for updating and submitting
    $applicationData = [
      'responsible_officer_id' => $responsibleOfficerId,
      'purpose' => $validatedData['purpose'],
      'location' => $validatedData['location'],
      'loan_start_date' => $validatedData['loan_start_date'],
      'loan_end_date' => $validatedData['loan_end_date'],
      // applicant_confirmation_timestamp is set by the service method initiateApprovalWorkflow
      // 'status' is also set by initiateApprovalWorkflow
    ];

    // Prepare items data for saving/submitting
    // Filter out items without equipment type as they are required for submission
    $itemsData = collect($validatedData['items'])
      ->filter(fn($item) => !empty($item['equipment_type']))
      ->values()
      ->toArray();

    // Ensure items data is not empty after filtering
    if (empty($itemsData)) {
      Log::warning('LoanRequestForm: Attempted to submit application with no items after filtering.', ['user_id' => $user->id, 'application_id' => $application->id]);
      session()->flash('error', __('Please add at least one equipment item to the application.'));
      $this->addError('items', __('At least one equipment item is required.'));
      return null;
    }

    // Use service methods to update the application data and initiate the workflow
    try {
      // Update the application with the submitted data
      $application = $loanApplicationService->updateApplication($application, $applicationData, $itemsData);

      // Initiate the approval workflow (sets status to pending_support, sets timestamp)
      $application = $loanApplicationService->initiateApprovalWorkflow($application);
    } catch (\Exception $e) {
      Log::error('LoanRequestForm: Failed to submit application or initiate workflow.', ['user_id' => $user->id, 'application_id' => $application->id, 'exception' => $e]);
      session()->flash('error', __('An error occurred while submitting the application.'));
      return null; // Do not redirect on error
    }

    // Redirect to the application's show page with a success message
    session()->flash('success', __('Loan request submitted successfully!'));
    return $this->redirect(route('loan-applications.show', $application));
  }
}
