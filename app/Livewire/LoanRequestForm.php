<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\LoanApplication;
use App\Models\LoanApplicationItem; // Assuming this model exists for loan application items
use App\Services\LoanApplicationService; // Use the service for logic
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Trait for policy checks
use Illuminate\Database\Eloquent\ModelNotFoundException; // Import ModelNotFoundException
use Illuminate\Support\Str; // Not explicitly used in this version, but might be useful
use Illuminate\Validation\ValidationException; // Import ValidationException for catch blocks
use App\Models\Position; // Used in mount for applicant details
use App\Models\Department; // Used in mount for applicant details
use App\Models\Grade; // Used in mount for applicant details and for filtering responsible officers
use Illuminate\View\View; // For render return type hint
use Illuminate\Http\RedirectResponse; // For redirect return type hint
use Illuminate\Support\Collection; // Import Collection
use Illuminate\Support\Facades\Log; // Import Log facade for error logging
use Illuminate\Support\Facades\DB; // Ensure DB facade is imported for transactions
use Carbon\Carbon; // For date handling


class LoanRequestForm extends Component
{
  use AuthorizesRequests; // Trait for policy checks

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
  // Each item in the array will be an associative array with keys like 'equipment_type', 'quantity_requested', 'notes', 'id' (for existing items)
  public array $items = [
    ['equipment_type' => '', 'quantity_requested' => 1, 'notes' => ''],
  ]; // Array to hold multiple item requests (Jenis Peralatan, Kuantiti, Catatan)

  // Properties for Confirmation (Bahagian 4)
  public bool $applicant_confirmation = false; // Pengesahan Pemohon checkbox

  // Optional: If updating an existing draft application
  public ?int $applicationId = null; // Property for application ID
  protected ?LoanApplication $loanApplication = null; // Type hint and initialize as nullable, protected as it's internal


  // Applicant's details displayed from User model (Bahagian 1 - Not form fields, fetched in mount)
  public ?string $applicantName = null; // Nama Penuh
  public ?string $applicantJobTitleGrade = null; // Jawatan & Gred (Combined for display)
  public ?string $applicantDivisionUnit = null; // Bahagian/Unit
  public ?string $applicantPhone = null; // No. Telefon


  /**
   * Real-time validation rules.
   * Only validates properties touched by the user or necessary for basic UI state.
   * Full validation happens on save/submit.
   *
   * @return array<string, array<mixed>|string|Rule>
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
      // Only validate exists if a value is present and applicant is not responsible
      'responsible_officer_id' => [Rule::requiredIf(!$this->is_applicant_responsible && !is_null($this->responsible_officer_id)), 'nullable', 'exists:users,id'],
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
   * @return array<string, array<mixed>|string|Rule>
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
      // Only validate exists if checkbox is false AND a value has been selected
      'responsible_officer_id' => [Rule::requiredIf(!$this->is_applicant_responsible && !is_null($this->responsible_officer_id)), 'nullable', 'exists:users,id'],
      'items' => 'nullable|array',
      'items.*.id' => 'nullable|integer|exists:loan_application_items,id', // Include validation for existing item IDs
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
   * @return array<string, array<mixed>|string|Rule>
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
      'items.*.id' => 'nullable|integer|exists:loan_application_items,id', // Include validation for existing item IDs
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
   * @param string $propertyName The name of the property being updated.
   * @return void
   */
  public function updated(string $propertyName): void
  {
    // Use the defined realtime rules
    try {
      $this->validateOnly($propertyName, $this->realtimeRules());
    } catch (ValidationException $e) {
      // Keep existing errors for other properties and merge new ones
      $this->setErrorBag($this->getErrorBag()->merge($e->errors()));
      // Do not re-throw to prevent halting real-time updates
      Log::debug('LoanRequestForm: Real-time validation failed', ['property' => $propertyName, 'errors' => $e->errors()]);
    }
  }


  /**
   * Mount the component.
   * Fetches initial data and populates the form if editing an existing draft.
   *
   * @param LoanApplication|null $loanApplication Optional existing application for editing.
   * @return \Illuminate\Http\RedirectResponse|null
   */
  public function mount(?LoanApplication $loanApplication = null): RedirectResponse|null
  {
    // Ensure user is authenticated to access the form
    if (!Auth::check()) {
      Log::warning('LoanRequestForm mounted for unauthenticated user.');
      session()->flash('error', __('You must be logged in to access the loan request form.'));
      // *** Changed to global redirect() helper ***
      return redirect()->route('login'); // Redirect to login if not authenticated
    }

    $user = Auth::user(); // The authenticated user is the applicant

    // Log whether mounting for new application or editing draft
    if ($loanApplication) {
      Log::info('LoanRequestForm mounted for editing draft.', ['user_id' => $user->id, 'application_id' => $loanApplication->id]);
    } else {
      Log::info('LoanRequestForm mounted for new application.', ['user_id' => $user->id]);
    }


    // Initialize properties for a new form
    $this->responsibleOfficers = collect(); // Default empty collection
    $this->items = [['equipment_type' => '', 'quantity_requested' => 1, 'notes' => '']]; // Default one item row
    $this->applicant_confirmation = false; // Default checkbox unchecked
    $this->applicationId = null; // Default no application ID
    $this->loanApplication = null; // Default no application model instance
    $this->purpose = ''; // Default empty purpose
    $this->location = ''; // Default empty location
    $this->loan_start_date = now()->format('Y-m-d'); // Default start date to today
    $this->loan_end_date = now()->addWeek()->format('Y-m-d'); // Default end date to one week from today
    $this->is_applicant_responsible = true; // Default responsible officer to applicant
    $this->responsible_officer_id = null; // Applicant is responsible, so ID is null

    // Load users eligible to be responsible officers
    try {
      // Consider scoping this query if not all users should be selectable (e.g., by grade, role)
      // Example: Only users Grade 41 and above can be responsible officers
      // $this->responsibleOfficers = User::whereHas('grade', fn($query) => $query->where('level', '>=', 41))->orderBy('name')->get();
      $this->responsibleOfficers = User::orderBy('name')->get(); // Fetch all users, ordered by name
    } catch (\Exception $e) {
      Log::error('LoanRequestForm: Error fetching responsible officers.', ['user_id' => $user->id, 'exception' => $e]);
      session()->flash('error', __('Could not load responsible officers. Please try again later.')); // Malay error message
      $this->responsibleOfficers = collect(); // Set to empty collection on error
    }


    // Populate applicant's details from the authenticated user model for display (Bahagian 1)
    // Use null-safe operator '?->' and nullish coalescing '??' for robustness
    $this->applicantName = $user->full_name ?? $user->name ?? __('N/A');
    // Assuming User model has position and grade relationships
    $this->applicantJobTitleGrade = ($user->position?->name ?? __('N/A')) . ' & ' . ($user->grade?->name ?? __('N/A'));
    // Assuming User model has a department relationship
    $this->applicantDivisionUnit = $user->department?->name ?? __('N/A');
    // Assuming User model has a mobile_number field
    $this->applicantPhone = $user->mobile_number ?? __('N/A');


    // If editing an existing application, populate form fields
    if ($loanApplication) {
      // Ensure the application exists and is a draft owned by the user before populating
      if ($loanApplication->user_id !== $user->id || $loanApplication->status !== 'draft') {
        Log::warning('LoanRequestForm: Attempted to mount non-owned or non-draft application for editing.', [
          'user_id' => $user->id,
          'application_id' => $loanApplication->id,
          'status' => $loanApplication->status,
        ]);
        // You might choose a different message depending on desired behavior
        session()->flash('error', __('Anda tidak dibenarkan untuk mengedit permohonan ini.')); // Malay error message
        // Redirect away if not allowed to edit
        // *** Changed to global redirect() helper ***
        return redirect()->route('loan-applications.show', $loanApplication);
      }

      // Authorize viewing/updating (policy check is still good practice even after manual check)
      try {
        // 'view' policy checks if user can see the application (applicant, approver, BPM, admin)
        $this->authorize('view', $loanApplication);
        // 'update' policy should specifically enforce draft status and ownership for editing the form
        $this->authorize('update', $loanApplication);
      } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        Log::warning('LoanRequestForm: User not authorized to edit application via policy.', ['user_id' => $user->id, 'application_id' => $loanApplication->id, 'exception' => $e]);
        session()->flash('error', __('Anda tidak mempunyai kebenaran untuk mengedit permohonan ini.')); // Malay error message
        // *** Changed to global redirect() helper ***
        return redirect()->route('loan-applications.show', $loanApplication);
      }

      // Populate properties from the existing application
      $this->applicationId = $loanApplication->id;
      $this->loanApplication = $loanApplication; // Store the model instance
      $this->purpose = $loanApplication->purpose ?? '';
      $this->location = $loanApplication->location ?? '';
      // Format dates for form fields (assuming Y-m-d format is expected by input type="date")
      $this->loan_start_date = $loanApplication->loan_start_date ? Carbon::parse($loanApplication->loan_start_date)->format('Y-m-d') : null;
      $this->loan_end_date = $loanApplication->loan_end_date ? Carbon::parse($loanApplication->loan_end_date)->format('Y-m-d') : null;

      $this->responsible_officer_id = $loanApplication->responsible_officer_id;
      // Determine is_applicant_responsible checkbox state based on responsible_officer_id
      $this->is_applicant_responsible = is_null($loanApplication->responsible_officer_id);


      // Load existing items from the application's items relationship
      $this->items = $loanApplication->items->map(function ($item) {
        return [
          'id' => $item->id, // Include item ID for updates/deletions
          'equipment_type' => $item->equipment_type ?? '',
          'quantity_requested' => $item->quantity_requested ?? 1,
          'notes' => $item->notes ?? '',
        ];
      })->toArray();

      // If no items were loaded from the existing draft (e.g., saved with zero items), add a default empty row
      if (empty($this->items)) {
        $this->items[] = ['equipment_type' => '', 'quantity_requested' => 1, 'notes' => ''];
      }

      // Confirmation state is typically NOT loaded from timestamp for drafting, user needs to confirm again on submit
      // The checkbox state from a draft is not usually saved, only the timestamp upon submission.
      // $this->applicant_confirmation = $loanApplication->applicant_confirmation_timestamp !== null; // Removed this line as confirmation is for submission
    }

    // If we reached this point and didn't redirect, return null
    return null; // Explicitly return null if no redirect happens
  }

  /**
   * Render the component view.
   *
   * @return \Illuminate\View\View
   */
  public function render(): View
  {
    // Pass dropdown data and the application instance (if editing) to the view
    return view('livewire.loan-request-form', [
      'responsibleOfficers' => $this->responsibleOfficers,
      'loanApplication' => $this->loanApplication, // Pass the application instance if editing
      // Pass applicant details for display in the view
      'applicantName' => $this->applicantName,
      'applicantJobTitleGrade' => $this->applicantJobTitleGrade,
      'applicantDivisionUnit' => $this->applicantDivisionUnit,
      'applicantPhone' => $this->applicantPhone,
    ]);
  }


  /**
   * Method to add a new equipment item row to the items array.
   * Called when the "Tambah Peralatan Lain" button is clicked.
   *
   * @return void
   */
  public function addItem(): void
  {
    // Add a new default item row to the items array
    $this->items[] = ['equipment_type' => '', 'quantity_requested' => 1, 'notes' => ''];
  }

  /**
   * Method to remove an equipment item row from the items array.
   * Called when the "Buang" button next to an item row is clicked.
   *
   * @param int $index The index of the item row to remove.
   * @return void
   */
  public function removeItem(int $index): void
  {
    // Check if the item exists at the given index
    if (isset($this->items[$index])) {
      // If the item has an ID, it means it's an existing item from a saved draft.
      // Mark it for deletion from the database when the draft is saved/updated.
      // A better approach would be to manage item deletions explicitly in the service.
      // For this version, we'll just remove it from the Livewire array. The service
      // update method will need to handle syncing items (deleting items in DB that
      // are not in the updated $itemsData array).

      unset($this->items[$index]); // Remove the item from the array
      $this->items = array_values($this->items); // Re-index the array

      // Ensure there's always at least one item row visible in the UI
      if (empty($this->items)) {
        $this->items[] = ['equipment_type' => '', 'quantity_requested' => 1, 'notes' => ''];
      }

      // Clear validation errors specific to the removed item if any
      $this->resetErrorBag('items.' . $index . '.equipment_type');
      $this->resetErrorBag('items.' . $index . '.quantity_requested');
      $this->resetErrorBag('items.' . $index . '.notes');
    }
  }


  /**
   * Save the application form data as a draft.
   * This method saves or updates an application record with status 'draft'.
   * It does NOT submit the application for approval.
   *
   * @param LoanApplicationService $loanApplicationService The service to handle application logic.
   * @return void // This method does not return a RedirectResponse
   */
  public function saveAsDraft(LoanApplicationService $loanApplicationService): void
  {
    // Ensure user is authenticated
    if (!Auth::check()) {
      Log::warning('LoanRequestForm: Attempted to save draft for unauthenticated user.');
      session()->flash('error', __('You must be logged in to save a draft.')); // Malay error message
      return; // Return null implicitly (void) - No redirect from a void method
    }
    $user = Auth::user();


    // 1. Validate the form data against draft rules
    try {
      // Use the dedicated draft rules method
      $validatedData = $this->validate($this->getDraftRules());
    } catch (ValidationException $e) {
      $this->setErrorBag($e->errors());
      Log::info('LoanRequestForm: Draft validation failed.', ['user_id' => $user->id, 'errors' => $e->errors()]);
      session()->flash('error', __('Sila betulkan ralat pada borang sebelum menyimpan draf.')); // Malay error message
      return; // Stop execution if validation fails (return null implicitly)
    }

    // Determine responsible officer ID based on checkbox state
    // If is_applicant_responsible is true, responsible_officer_id is null
    // Otherwise, use the selected responsible_officer_id (which is validated as required if checkbox is false)
    $responsibleOfficerId = $validatedData['is_applicant_responsible'] ? null : ($validatedData['responsible_officer_id'] ?? null);


    // Prepare application data for saving
    $applicationData = [
      'user_id' => $user->id, // Set the applicant's user ID
      'responsible_officer_id' => $responsibleOfficerId, // Save responsible officer ID (null if applicant is responsible)
      'purpose' => $validatedData['purpose'] ?? null, // Save purpose (nullable for draft)
      'location' => $validatedData['location'] ?? null, // Save location (nullable for draft)
      // Save dates, ensure they are null if not provided
      'loan_start_date' => $validatedData['loan_start_date'] ?? null,
      'loan_end_date' => $validatedData['loan_end_date'] ?? null,
      'status' => 'draft', // Ensure status is always 'draft' for saveAsDraft
      // applicant_confirmation_timestamp is NOT set when saving draft
    ];

    // Prepare items data for saving (include 'id' for existing items)
    // Filter out item rows that are completely empty for draft
    // You might adjust this filter based on whether you want to save rows with only notes/quantity
    $itemsData = collect($validatedData['items'] ?? [])
      ->filter(
        // *** Corrected array access syntax ***
        fn($item) =>
        !empty($item['equipment_type']) || // Keep if equipment type is filled
          !empty($item['notes']) || // Keep if notes are filled
          ($item['quantity_requested'] ?? 0) > 0 || // Keep if quantity > 0
          isset($item['id']) // Keep if it's an existing item (has an ID)
      )
      ->values() // Re-index the array after filtering
      ->toArray();


    // Use a transaction for atomicity in saving/updating the application and its items
    try {
      DB::beginTransaction();

      if ($this->applicationId) {
        // --- Updating an existing draft ---
        Log::info('LoanRequestForm: Attempting to update draft application.', ['user_id' => $user->id, 'application_id' => $this->applicationId]);

        // Find the application instance (already checked in mount, but good to be safe)
        // Use findOrFail to throw exception if not found
        $application = $this->loanApplication ?? LoanApplication::where('id', $this->applicationId)->firstOrFail();

        // Policy check should ensure it's the user's draft they are authorized to update
        $this->authorize('update', $application);

        // Double-check draft status before updating (redundant if policy checks, but safe)
        if ($application->status !== 'draft') {
          DB::rollBack();
          Log::warning('LoanRequestForm: Attempted to update non-draft application via saveAsDraft.', ['user_id' => $user->id, 'application_id' => $application->id, 'status' => $application->status]);
          session()->flash('error', __('Tidak dapat menyimpan draf kerana status permohonan bukan draf.')); // Malay error
          // No redirect from a void method
          return;
        }

        // Use service method to update the draft application and its items
        // The service's update method should handle syncing items (creating new, updating existing, deleting removed)
        $application = $loanApplicationService->updateApplication($application, $applicationData, $itemsData);

        Log::info('Loan request draft updated successfully via service.', ['user_id' => $user->id, 'application_id' => $application->id]);
        session()->flash('success', __('Draf permohonan pinjaman berjaya dikemaskini!')); // Malay success message


      } else {
        // --- Creating a new draft ---
        Log::info('LoanRequestForm: Attempting to create new draft application.', ['user_id' => $user->id]);

        // Authorize if the user can create a loan application
        $this->authorize('create', LoanApplication::class);


        // Use service method to create the application and its items
        // The service's create method should set the initial status to 'draft'
        $application = $loanApplicationService->createApplication($user, $applicationData, $itemsData);

        // Store the new application ID and instance to allow future updates/submission
        $this->applicationId = $application->id;
        $this->loanApplication = $application; // Store the created model instance

        Log::info('Loan request draft created successfully via service.', ['user_id' => $user->id, 'application_id' => $application->id]);
        session()->flash('success', __('Draf permohonan pinjaman berjaya disimpan!')); // Malay success message
      }

      DB::commit(); // Commit the transaction

    } catch (ModelNotFoundException $e) {
      DB::rollBack(); // Rollback the transaction on error
      Log::error('LoanRequestForm: Draft application not found during save/update.', ['user_id' => $user->id, 'application_id' => $this->applicationId ?? 'N/A', 'exception' => $e]);
      session()->flash('error', __('Draf permohonan tidak ditemui.')); // Malay error message
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      DB::rollBack(); // Rollback the transaction on error
      Log::warning('LoanRequestForm: User not authorized during saveAsDraft.', ['user_id' => $user->id, 'application_id' => $this->applicationId ?? 'N/A', 'exception' => $e]);
      session()->flash('error', __('Anda tidak mempunyai kebenaran untuk melakukan tindakan ini.')); // Malay error message
    } catch (ValidationException $e) {
      // This catch block is primarily for exceptions thrown by the service's validation
      // Livewire's $this->validate() is caught earlier.
      DB::rollBack(); // Rollback the transaction on error
      Log::error('LoanRequestForm: Validation error during saveAsDraft service call.', ['user_id' => $user->id, 'application_id' => $this->applicationId ?? 'N/A', 'errors' => $e->errors(), 'exception' => $e]);
      // Re-throw the exception so Livewire can display validation errors if needed
      // However, since we already validate with $this->validate() at the start,
      // catching ValidationException here might indicate validation logic in the service,
      // or it could be safely removed if service doesn't throw ValidationException.
      // For now, we re-throw, but consider removing this specific catch if validation is only done via $this->validate().
      throw $e; // Re-throw the exception
    } catch (\Exception $e) {
      DB::rollBack(); // Rollback the transaction on error
      Log::error('LoanRequestForm: An unexpected error occurred while saving the draft.', ['user_id' => $user->id, 'application_id' => $this->applicationId ?? 'N/A', 'exception' => $e]);
      session()->flash('error', __('Ralat berlaku semasa menyimpan draf. Sila cuba sebentar lagi.')); // Malay error message
    }

    // Explicitly return for clarity in a void method
    return;
  }


  /**
   * Submit the application form for approval.
   * This transitions a draft application to 'pending_support' status.
   *
   * @param LoanApplicationService $loanApplicationService The service to handle application logic.
   * @return \Illuminate\Http\RedirectResponse|null
   */
  public function submitApplication(LoanApplicationService $loanApplicationService): RedirectResponse|null
  {
    // Ensure user is authenticated
    if (!Auth::check()) {
      Log::warning('LoanRequestForm: Attempted to submit application for unauthenticated user.');
      session()->flash('error', __('You must be logged in to submit an application.')); // Malay error message
      return null; // Stop execution (return null) - No redirect from here
    }
    $user = Auth::user();

    Log::info('LoanRequestForm: Attempting to submit application.', ['user_id' => $user->id, 'application_id' => $this->applicationId]);


    // 1. Validate the form data against submission rules
    try {
      // Use the dedicated submit rules method
      $validatedData = $this->validate($this->getSubmitRules());
    } catch (ValidationException $e) {
      $this->setErrorBag($e->errors());
      Log::info('LoanRequestForm: Submission validation failed.', ['user_id' => $user->id, 'errors' => $e->errors()]);
      session()->flash('error', __('Sila betulkan ralat pada borang sebelum menghantar permohonan.')); // Malay error message
      return null; // Stop execution if validation fails (return null)
    }


    // Ensure we have an application ID (submission requires a saved draft)
    if (!$this->applicationId) {
      Log::error('LoanRequestForm: Attempted to submit application without existing draft ID.', ['user_id' => $user->id]);
      session()->flash('error', __('Tidak dapat menghantar. Sila simpan draf terlebih dahulu.')); // Malay error message
      return null; // Stop submission (return null)
    }

    // Find the existing draft application
    try {
      // Use findOrFail to throw exception if not found
      $application = $this->loanApplication ?? LoanApplication::where('id', $this->applicationId)->firstOrFail();
    } catch (ModelNotFoundException $e) {
      Log::error('LoanRequestForm: Draft application not found for submission.', ['user_id' => $user->id, 'application_id' => $this->applicationId, 'exception' => $e]);
      session()->flash('error', __('Draf permohonan tidak ditemui.')); // Malay error message
      return null; // Stop execution (return null)
    }


    // Authorize submission (should check policy for updating/submitting)
    try {
      // 'update' policy should enforce draft status and ownership for submission
      $this->authorize('update', $application);
      // You might also need a separate policy action like 'submit' that checks status and possibly role (if only certain users can initiate)
      // $this->authorize('submit', $application); // Assuming a 'submit' policy action on LoanApplicationPolicy
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      Log::warning('LoanRequestForm: User not authorized to submit application.', ['user_id' => $user->id, 'application_id' => $application->id, 'exception' => $e]);
      session()->flash('error', __('Anda tidak mempunyai kebenaran untuk menghantar permohonan ini.')); // Malay error message
      // *** Changed to global redirect() helper ***
      return redirect()->route('loan-applications.show', $application); // Redirect if not authorized
    }

    // Double-check that the application is indeed in 'draft' status before submitting
    if ($application->status !== 'draft') {
      Log::warning('LoanRequestForm: Attempted to submit non-draft application.', ['user_id' => $user->id, 'application_id' => $application->id, 'status' => $application->status]);
      session()->flash('error', __('Permohonan bukan dalam status draf dan tidak dapat dihantar.')); // Malay error message
      // *** Changed to global redirect() helper ***
      return redirect()->route('loan-applications.show', $application); // Redirect to the show page
    }

    // Determine responsible officer ID based on checkbox state
    $responsibleOfficerId = $validatedData['is_applicant_responsible'] ? null : $validatedData['responsible_officer_id'];

    // Prepare application data for updating and submitting
    $applicationData = [
      'responsible_officer_id' => $responsibleOfficerId,
      'purpose' => $validatedData['purpose'],
      'location' => $validatedData['location'],
      'loan_start_date' => $validatedData['loan_start_date'],
      'loan_end_date' => $validatedData['loan_end_date'],
      // applicant_confirmation_timestamp and 'status' will be set by the service method initiateApprovalWorkflow
    ];

    // Prepare items data for saving/submitting (include 'id' for existing items)
    // Filter out items without equipment type as they are required for submission based on getSubmitRules()
    $itemsData = collect($validatedData['items'])
      // *** Corrected array access syntax ***
      ->filter(fn($item) => !empty($item['equipment_type']))
      ->values() // Re-index the array
      ->toArray();

    // Ensure items data is not empty after filtering (already validated by 'items.min:1' and 'items.*.equipment_type' required, but defensive check)
    if (empty($itemsData)) {
      Log::warning('LoanRequestForm: Attempted to submit application with no items after filtering by equipment_type.', ['user_id' => $user->id, 'application_id' => $application->id]);
      session()->flash('error', __('Sila tambah sekurang-kurangnya satu item peralatan pada permohonan.')); // Malay error message
      $this->addError('items', __('Sekurang-kurangnya satu item peralatan diperlukan.')); // Add a general error to the items property
      return null;
    }


    // Use service methods to update the application data and initiate the workflow
    try {
      DB::beginTransaction(); // Start transaction for atomicity

      // Update the application with the submitted data (including setting responsible officer, dates, etc.)
      // The service's update method should handle syncing items (creating new, updating existing, deleting removed)
      $application = $loanApplicationService->updateApplication($application, $applicationData, $itemsData);

      // Initiate the approval workflow (sets status to pending_support, sets confirmation timestamp)
      // Assumes initiateApprovalWorkflow method exists in your LoanApplicationService.
      $application = $loanApplicationService->initiateApprovalWorkflow($application, $user); // Pass the submitting user


      DB::commit(); // Commit the transaction

      Log::info('Loan request submitted successfully via service and workflow initiated.', ['user_id' => $user->id, 'application_id' => $application->id, 'new_status' => $application->status]);

      // Redirect to the application's show page with a success message
      session()->flash('success', __('Permohonan pinjaman berjaya dihantar!')); // Malay success message

      // *** Changed to global redirect() helper ***
      return redirect()->route('loan-applications.show', $application); // Redirect to the show page

    } catch (ModelNotFoundException $e) {
      DB::rollBack(); // Rollback transaction on error
      Log::error('LoanRequestForm: Model not found during submission process.', ['user_id' => $user->id, 'application_id' => $this->applicationId ?? 'N/A', 'exception' => $e]);
      session()->flash('error', __('Ralat: Permohonan tidak ditemui semasa proses penghantaran.')); // Malay error message
      return null;
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      DB::rollBack(); // Rollback transaction on error
      Log::warning('LoanRequestForm: User not authorized during submission process.', ['user_id' => $user->id, 'application_id' => $this->applicationId ?? 'N/A', 'exception' => $e]);
      session()->flash('error', __('Anda tidak mempunyai kebenaran untuk menghantar permohonan ini.')); // Malay error message
      // *** Changed to global redirect() helper ***
      return redirect()->route('loan-applications.show', $application); // Redirect if not authorized
    } catch (ValidationException $e) {
      // This catch block is primarily for exceptions thrown by the service's validation
      // Livewire's $this->validate() is caught earlier.
      DB::rollBack(); // Rollback transaction on error
      Log::error('LoanRequestForm: Validation error during submitApplication service call.', ['user_id' => $user->id, 'application_id' => $this->applicationId ?? 'N/A', 'errors' => $e->errors(), 'exception' => $e]);
      // Re-throw the exception so Livewire can display validation errors if needed
      // However, since we already validate with $this->validate() at the start,
      // catching ValidationException here might indicate validation logic in the service,
      // or it could be safely removed if validation is only done via $this->validate().
      // For now, we re-throw, but consider removing this specific catch if validation is only done via $this->validate().
      throw $e; // Re-throw the exception
    } catch (\Exception $e) {
      DB::rollBack(); // Rollback transaction on error
      Log::error('LoanRequestForm: An unexpected error occurred while submitting the application.', ['user_id' => $user->id, 'application_id' => $this->applicationId ?? 'N/A', 'exception' => $e]);
      session()->flash('error', __('Ralat berlaku semasa menghantar permohonan. Sila cuba sebentar lagi.')); // Malay error message
      return null; // Do not redirect on error
    }
  }

  // Optional: Method to delete a draft application (if allowed) - Keep commented out as example
  // public function deleteDraft(LoanApplicationService $loanApplicationService): RedirectResponse|null
  // {
  //     if (!Auth::check()) {
  //         session()->flash('error', __('You must be logged in to delete a draft.'));
  //         return null;
  //     }
  //     $user = Auth::user();
  //
  //     if (!$this->applicationId) {
  //         session()->flash('error', __('No draft to delete.'));
  //         return null;
  //     }
  //
  //     try {
  //         $application = LoanApplication::findOrFail($this->applicationId);
  //     } catch (ModelNotFoundException $e) {
  //         session()->flash('error', __('Draft application not found.'));
  //         return null;
  //     }
  //
  //     try {
  //         $this->authorize('delete', $application); // Policy 'delete' should handle draft deletion authorization
  //     } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
  //         session()->flash('error', __('You are not authorized to delete this draft.'));
  //         return redirect()->route('loan-applications.show', $application); // Changed to global redirect()
  //     }
  //
  //      if ($application->status !== 'draft') {
  //           session()->flash('error', __('Application is not in draft status and cannot be deleted.'));
  //           return redirect()->route('loan-applications.show', $application); // Changed to global redirect()
  //      }
  //
  //     try {
  //         $deleted = $loanApplicationService->deleteApplication($application); // Assumes deleteApplication method exists in service
  //
  //         if ($deleted) {
  //             session()->flash('success', __('Loan application draft deleted successfully!'));
  //             return redirect()->route('loan-applications.index'); // Changed to global redirect()
  //         } else {
  //             session()->flash('error', __('Failed to delete loan application draft.'));
  //             return null;
  //         }
  //
  //     } catch (\Exception $e) {
  //         session()->flash('error', __('An error occurred while deleting the draft: ' . $e->getMessage()));
  //         return null;
  //     }
  // }
}
