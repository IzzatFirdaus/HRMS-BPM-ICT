<?php

namespace App\Livewire;

// --- Standard Namespace/Class Use Statements (must be before the class) ---
use Livewire\Component;
use App\Models\Grade; // Import Grade model if filtering by grade
use App\Models\Department; // Import Department model if filtering by department
use App\Models\User; // To get current user details and select supporting officer
use App\Models\EmailApplication; // To create application records
use App\Services\EmailApplicationService; // Use the service for logic
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Validation\Rule; // Import Rule for validation
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Trait for policy checks
use Illuminate\Database\Eloquent\ModelNotFoundException; // Import ModelNotFoundException
use Illuminate\View\View; // For render return type hint
use Illuminate\Http\RedirectResponse; // For redirect return type hint
use Illuminate\Validation\ValidationException; // Import ValidationException for catch blocks
use Illuminate\Support\Facades\Log; // Import Log facade for error logging
use Illuminate\Support\Facades\DB; // Ensure DB facade is imported for transactions
use Carbon\Carbon; // For timestamp
use Illuminate\Support\Collection; // Import Collection
use Illuminate\Support\Facades\Gate; // Import Gate facade if using Gates for authorization

// The 'use' statements for traits must be inside the class body.

class EmailApplicationForm extends Component
{
  // --- Trait Use Statements (must be inside the class body) ---
  use AuthorizesRequests;

  // Public properties that correspond to form fields and sync with the view
  public string $service_status = ''; // Bind to service_status select
  public string $purpose = ''; // Corresponds to 'Tujuan/Catatan'
  public string $proposed_email = ''; // Corresponds to 'Cadangan E-mel/ID'
  public string $group_email = '';
  public string $group_admin_name = '';
  public string $group_admin_email = '';
  public bool $certification = false; // Bind to certification checkbox ('Perakuan Pemohon') - Must be accepted for submission

  // New public property for the Supporting Officer selection
  public string $supporting_officer_id = ''; // Bind to supporting officer dropdown


  // Properties to hold dropdown data (fetched in mount)
  public ?Collection $supportingOfficers = null; // List of users eligible to be supporting officers


  // Optional: If updating an existing draft application
  public ?int $applicationId = null; // Set to null initially
  protected ?EmailApplication $emailApplication = null; // Type hint and initialize as nullable, protected as it's internal


  // Applicant's details displayed from User model (not form fields, fetched in mount)
  public ?string $applicantName = null;
  public ?string $applicantIC = null;
  public ?string $applicantGradeName = null; // Name of the applicant's grade
  public ?string $applicantPositionName = null; // Name of the applicant's position
  public ?string $applicantDepartmentName = null; // Name of the applicant's department/unit
  public ?string $applicantMobileNumber = null;
  public ?string $applicantPersonalEmail = null;


  /**
   * Real-time validation.
   *
   * @param string $propertyName The name of the property being updated.
   * @return void
   */
  public function updated(string $propertyName): void
  {
    // Define validation rules for real-time checks.
    // These can be a subset of the full submission rules.
    $realtimeRules = [
      'service_status' => ['required', Rule::in(['Kakitangan Tetap', 'Lantikan Kontrak', 'Personel MySTEP', 'Pelajar Latihan Industri', 'E-mel Sandaran MOTAC'])], // Using the string values from the form
      'purpose' => 'nullable|string|max:500',
      'proposed_email' => 'nullable|email:rfc,dns|max:255',
      'group_email' => 'nullable|email:rfc,dns|max:255',
      'group_admin_name' => 'nullable|string|max:255',
      'group_admin_email' => 'nullable|email:rfc,dns|max:255',
      'supporting_officer_id' => 'nullable|exists:users,id',
      'certification' => 'boolean', // Just check boolean type in real-time
    ];

    // Optional: Apply conditional validation rules for real-time updates if needed
    if ($propertyName === 'service_status') {
      // Example: If service_status changes, re-validate potentially affected fields
      $this->validateOnly('group_email', $realtimeRules);
      $this->validateOnly('group_admin_name', $realtimeRules);
      $this->validateOnly('group_admin_email', $realtimeRules);
    }
    if ($propertyName === 'group_email' || $propertyName === 'group_admin_name' || $propertyName === 'group_admin_email') {
      // Example: If group fields change, re-validate proposed_email if needed
      $this->validateOnly('proposed_email', $realtimeRules);
    }


    try {
      // Validate only the property that changed
      $this->validateOnly($propertyName, $realtimeRules);
    } catch (ValidationException $e) {
      // Keep existing errors for other properties and merge new ones
      $this->setErrorBag($this->getErrorBag()->merge($e->errors()));
      // Do not re-throw to prevent halting real-time updates
      Log::debug('Real-time validation failed', ['property' => $propertyName, 'errors' => $e->errors()]);
    }
  }


  /**
   * Mount the component.
   * Fetches initial data and populates the form if editing an existing draft.
   *
   * @param EmailApplication|null $emailApplication Optional existing application for editing.
   * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|void // Correct Livewire mount return type hint
   */
  public function mount(?EmailApplication $emailApplication = null)
  {
    // Ensure user is authenticated to access the form
    if (!Auth::check()) {
      Log::warning('EmailApplicationForm mounted for unauthenticated user.');
      // *** Corrected redirect syntax for Livewire mount ***
      return redirect()->route('login'); // Redirect to login if not authenticated
    }

    $user = Auth::user(); // The authenticated user is the applicant

    // Log whether mounting for new application or editing draft
    if ($emailApplication) {
      Log::info('EmailApplicationForm mounted for editing draft.', ['user_id' => $user->id, 'application_id' => $emailApplication->id]);
    } else {
      Log::info('EmailApplicationForm mounted for new application.', ['user_id' => $user->id]);
    }


    $this->emailApplication = $emailApplication;

    // Load users eligible to be supporting officers (e.g., Grade 9 and above)
    // You might refine this query based on active users, specific roles, etc.
    try {
      // Use a dedicated service method for getting approvers if logic is complex
      // $this->supportingOfficers = app(UserService::class)->getUsersByMinGradeLevel(config('motac.approval.min_supporting_officer_grade_level', 9));

      // Direct query example if service method not used or simpler lookup
      $minGradeLevel = config('motac.approval.min_supporting_officer_grade_level', 9); // Get min grade level from config
      Log::debug("Fetching supporting officers with min grade level {$minGradeLevel}");

      $this->supportingOfficers = User::with('grade') // Eager load grade relationship
        ->whereHas('grade', fn($query) => $query->where('level', '>=', $minGradeLevel)) // Filter by min grade level
        ->where('status', 'active') // Assuming only active users can be supporting officers
        ->orderBy('name') // Order by name for display
        ->get();

      Log::debug('Supporting officers fetched.', ['count' => $this->supportingOfficers->count()]);
    } catch (\Exception $e) {
      Log::error('EmailApplicationForm: Error fetching supporting officers.', ['user_id' => $user->id, 'exception' => $e]);
      $this->supportingOfficers = collect(); // Set to empty collection on error
      session()->flash('error', __('Could not load supporting officers. Please try again later.'));
    }


    // Populate applicant's details from the authenticated user model for display
    // Use null-safe operator and nullish coalescing for robust access
    $this->applicantName = $user->full_name ?? $user->name ?? __('N/A'); // Use full_name first if it exists
    $this->applicantIC = $user->ic_number ?? $user->identification_number ?? __('N/A'); // Assuming ic_number or identification_number field
    $this->applicantGradeName = $user->grade?->name ?? __('N/A'); // Use null-safe for grade relationship
    $this->applicantPositionName = $user->position?->name ?? __('N/A'); // Assuming position relationship
    $this->applicantDepartmentName = $user->department?->name ?? __('N/A'); // Use null-safe for department relationship
    $this->applicantMobileNumber = $user->mobile_number ?? __('N/A');
    $this->applicantPersonalEmail = $user->personal_email ?? $user->email ?? __('N/A'); // Use personal_email if it exists


    // If editing an existing application, populate form fields
    if ($emailApplication) {
      // Ensure the user owns this draft application
      if ($emailApplication->user_id !== $user->id) {
        Log::warning('EmailApplicationForm: User attempted to edit application not owned by them.', ['user_id' => $user->id, 'application_id' => $emailApplication->id]);
        session()->flash('error', __('You are not authorized to edit this application.'));
        // *** Corrected redirect syntax for Livewire mount ***
        return redirect()->route('dashboard'); // Redirect to a safe page if not authorized
      }


      // Prevent editing if the application is not in 'draft' status
      if ($emailApplication->status !== 'draft') {
        Log::warning('EmailApplicationForm: Attempted to edit non-draft application.', ['user_id' => $user->id, 'application_id' => $emailApplication->id, 'status' => $emailApplication->status]);
        session()->flash('error', __('Cannot edit application as it is not in draft status.'));
        // *** Corrected redirect syntax for Livewire mount ***
        return redirect()->route('email-applications.show', $emailApplication); // Redirect if not a draft
      }

      // Authorize if the user can update this specific application (policy should handle ownership/status)
      // This check is redundant if the manual checks above are used, but policy is the preferred method.
      // try {
      //      $this->authorize('update', $emailApplication); // Policy 'update' should handle draft editing authorization
      // } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      //      Log::warning('EmailApplicationForm: Policy denied user access to edit draft.', ['user_id' => $user->id, 'application_id' => $emailApplication->id]);
      //      session()->flash('error', __('You are not authorized to edit this application via policy.'));
      //      return redirect()->route('dashboard'); // Redirect if not authorized by policy
      // }


      // Populate properties from the existing application
      $this->applicationId = $emailApplication->id;
      // Use nullish coalescing for robustness when populating
      $this->service_status = $emailApplication->service_status ?? '';
      $this->purpose = $emailApplication->purpose ?? '';
      $this->proposed_email = $emailApplication->proposed_email ?? '';
      $this->group_email = $emailApplication->group_email ?? '';
      $this->group_admin_name = $emailApplication->group_admin_name ?? '';
      $this->group_admin_email = $emailApplication->group_admin_email ?? '';
      // Certification might be stored as boolean or tinyint; cast to boolean
      $this->certification = (bool) ($emailApplication->certification_accepted ?? false);

      // Populate supporting officer ID if stored on the application
      $this->supporting_officer_id = $emailApplication->supporting_officer_id ?? '';
    } else {
      // For new applications, pre-fill fields based on the authenticated user if available
      $this->service_status = $user->service_status ?? ''; // Pre-fill service status if available on user model
      // You might suggest an initial proposed email based on user data (example)
      // $this->proposed_email = app(EmailApplicationService::class)->suggestEmailAddress($user) ?? '';

      // Set default for supporting officer if needed (e.g., the user's supervisor_id)
      $this->supporting_officer_id = $user->supervisor_id ?? ''; // Assuming user model has supervisor_id
    }

    // If we reached this point and didn't redirect, return null implicitly (void)
    return;
  }

  /**
   * Render the component view.
   *
   * @return \Illuminate\View\View
   */
  public function render(): View
  {
    // Pass dropdown data and the application instance to the view
    return view('livewire.email-application-form', [
      'supportingOfficers' => $this->supportingOfficers ?? collect(), // Pass supporting officers list, default to empty collection
      'emailApplication' => $this->emailApplication, // Pass the application instance if editing
      'user' => Auth::user(), // Ensure the current user is available in the view for applicant details
    ]);
  }


  /**
   * Save the application form data as a draft.
   * This method saves or updates an application record with status 'draft'.
   * It does NOT submit the application for approval.
   *
   * @param EmailApplicationService $emailApplicationService The service to handle application logic.
   * @return void // This method does not return a RedirectResponse
   */
  public function saveAsDraft(EmailApplicationService $emailApplicationService): void
  {
    // Ensure user is authenticated
    if (!Auth::check()) {
      Log::warning('EmailApplicationForm: Attempted to save draft for unauthenticated user.');
      session()->flash('error', __('You must be logged in to save a draft.'));
      return; // Return null implicitly (void) - No redirect from a void method
    }
    $user = Auth::user();


    // 1. Define validation rules for saving a draft.
    // These are typically less strict than submission rules.
    $draftRules = [
      'service_status' => ['required', Rule::in(['Kakitangan Tetap', 'Lantikan Kontrak', 'Personel MySTEP', 'Pelajar Latihan Industri', 'E-mel Sandaran MOTAC'])], // Using the string values from the form
      'purpose' => 'nullable|string|max:500',
      'proposed_email' => 'nullable|email:rfc,dns|max:255',
      'group_email' => 'nullable|email:rfc,dns|max:255',
      'group_admin_name' => 'nullable|string|max:255',
      'group_admin_email' => 'nullable|email:rfc,dns|max:255',
      'supporting_officer_id' => 'nullable|exists:users,id',
      'certification' => 'boolean', // Just check boolean type for draft
    ];

    // 2. Validate the form data against draft rules
    try {
      $validatedData = $this->validate($draftRules);
    } catch (ValidationException $e) {
      $this->setErrorBag($e->errors());
      Log::info('EmailApplicationForm: Draft validation failed.', ['user_id' => $user->id, 'errors' => $e->errors()]);
      session()->flash('error', __('Please fix the errors in the form before saving the draft.')); // Malay success message
      return; // Stop execution if validation fails (return null implicitly)
    }


    // Use a transaction for atomicity in saving/updating
    try {
      DB::beginTransaction();

      if ($this->applicationId) {
        // --- Updating an existing draft ---
        Log::info('EmailApplicationForm: Attempting to update draft application.', ['user_id' => $user->id, 'application_id' => $this->applicationId]);

        // Find the existing application
        try {
          $application = EmailApplication::findOrFail($this->applicationId);
        } catch (ModelNotFoundException $e) {
          DB::rollBack();
          Log::error('EmailApplicationForm: Draft application not found for update.', ['user_id' => $user->id, 'application_id' => $this->applicationId, 'exception' => $e]);
          session()->flash('error', __('Draft application not found.')); // Malay error message
          return; // Return null implicitly (void) - No redirect from a void method
        }

        // Authorize if the user can update this application (must be a draft owned by the user)
        // Policy 'update' should handle draft editing authorization (user owns draft, status is 'draft')
        try {
          $this->authorize('update', $application);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
          DB::rollBack();
          Log::warning('EmailApplicationForm: User not authorized to update draft.', ['user_id' => $user->id, 'application_id' => $application->id, 'exception' => $e]);
          session()->flash('error', __('You are not authorized to update this draft.')); // Malay error message
          // *** Corrected to return null instead of redirecting from a void method ***
          return; // Return null implicitly (void)
        }


        // Check if the application is indeed in 'draft' status before updating (redundant if policy checks, but safe)
        if ($application->status !== 'draft') {
          DB::rollBack();
          Log::warning('EmailApplicationForm: Attempted to update non-draft application.', ['user_id' => $user->id, 'application_id' => $application->id, 'status' => $application->status]);
          session()->flash('error', __('Cannot update application as it is not in draft status.')); // Malay error message
          // *** Corrected to return null instead of redirecting from a void method ***
          return; // Return null implicitly (void)
        }


        // Call service method to update the draft application
        // Pass validated data and relevant fields that might be set via form
        $updated = $emailApplicationService->updateApplication($application, array_merge($validatedData, [
          'certification_accepted' => $this->certification,
          // supporting_officer_id might be updated here as well
          'supporting_officer_id' => $this->supporting_officer_id ?: null, // Save supporting officer ID or null
        ]));

        if ($updated) {
          Log::info('Email application draft updated successfully via service.', ['user_id' => $user->id, 'application_id' => $application->id]);
          session()->flash('success', __('Email application draft updated successfully!')); // Malay success message
        } else {
          Log::warning('Email application draft update via service resulted in no changes.', ['user_id' => $user->id, 'application_id' => $application->id]);
          session()->flash('info', __('No changes detected to the draft application.')); // Malay message
        }
      } else {
        // --- Creating a new draft ---
        Log::info('EmailApplicationForm: Attempting to create new draft application.', ['user_id' => $user->id]);

        // Authorize if the user can create an application
        try {
          $this->authorize('create', EmailApplication::class);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
          DB::rollBack();
          Log::warning('EmailApplicationForm: User not authorized to create application.', ['user_id' => $user->id, 'exception' => $e]);
          session()->flash('error', __('You are not authorized to create an application.')); // Malay error message
          return; // Return null implicitly (void) - No redirect from a void method
        }


        // Prepare data for creation, including user_id and initial status
        // Use nullish coalescing for nullable fields to ensure they save as NULL in DB if empty string
        $dataToCreate = array_merge($validatedData, [
          'user_id' => $user->id, // Set the applicant's user ID
          'status' => 'draft', // Set initial status to 'draft'
          'certification_accepted' => $this->certification, // Save certification status
          'supporting_officer_id' => $this->supporting_officer_id ?: null, // Save supporting officer ID or null
          // Timestamps like created_at, updated_at will be set by Eloquent
          // Other fields like submission_timestamp, certification_timestamp etc. will be set later upon submission/workflow progression
        ]);

        // Call service method to create the application
        $application = $emailApplicationService->createApplication($user, $dataToCreate); // Pass user as applicant

        // Store the new application ID to allow future updates/submission
        $this->applicationId = $application->id;
        $this->emailApplication = $application; // Store the created model instance


        Log::info('Email application draft created successfully via service.', ['user_id' => $user->id, 'application_id' => $application->id]);
        session()->flash('success', __('Email application draft saved successfully!')); // Malay success message
      }

      DB::commit();
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('EmailApplicationForm: Failed to save/update draft.', ['user_id' => $user->id, 'application_id' => $this->applicationId, 'exception' => $e]);
      session()->flash('error', __('An error occurred while saving the draft.')); // Malay error message
      return; // Return null implicitly (void) - No redirect from a void method
    }

    // Explicitly return for clarity in a void method
    return;
  }


  /**
   * Submit the application form for approval.
   * This transitions a draft application to 'pending_support' status.
   *
   * @param EmailApplicationService $emailApplicationService The service to handle application logic.
   * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|null // Correct Livewire action return type hint
   */
  public function submitApplication(EmailApplicationService $emailApplicationService)
  {
    // Ensure user is authenticated
    if (!Auth::check()) {
      Log::warning('EmailApplicationForm: Attempted to submit application for unauthenticated user.');
      session()->flash('error', __('You must be logged in to submit an application.'));
      return null; // Stop execution (return null)
    }
    $user = Auth::user();

    Log::info('EmailApplicationForm: Attempting to submit application.', ['user_id' => $user->id, 'application_id' => $this->applicationId]);


    // 1. Define validation rules for final submission.
    // These rules are typically more strict and include required fields like purpose, supporting officer, and certification.
    // Referencing PDF: service status, purpose, proposed email OR group email/admin fields, supporting officer, certification are key fields.
    $submitRules = [
      'service_status' => ['required', Rule::in(['Kakitangan Tetap', 'Lantikan Kontrak', 'Personel MySTEP', 'Pelajar Latihan Industri', 'E-mel Sandaran MOTAC'])], // Must be one of these values
      'purpose' => 'required|string|max:500', // Purpose is required for submission
      'proposed_email' => 'nullable|email:rfc,dns|max:255', // Proposed email is nullable but validated if filled
      'group_email' => 'nullable|email:rfc,dns|max:255', // Group email is nullable but validated if filled
      'group_admin_name' => 'nullable|string|max:255', // Group admin name is nullable but validated if filled
      'group_admin_email' => 'nullable|email:rfc,dns|max:255', // Group admin email is nullable but validated if filled
      'supporting_officer_id' => ['required', 'exists:users,id'], // Supporting officer is required for submission and must exist
      'certification' => 'accepted', // Certification must be accepted (checkbox checked)

      // Note: Conditional validation for Proposed Email OR Group Email/Admin
      // is handled by the custom check after validation below for clarity.
    ];

    // 2. Validate the form data against submission rules
    try {
      $validatedData = $this->validate($submitRules);

      // --- Custom Check: Ensure Proposed Email OR Complete Group Email details are provided ---
      // This check is crucial as per the application types in the PDF.
      $isIndividualRequest = !empty($validatedData['proposed_email']);
      $isGroupRequest = !empty($validatedData['group_email']);
      $hasCompleteGroupDetails = $isGroupRequest && !empty($validatedData['group_admin_name']) && !empty($validatedData['group_admin_email']);


      if (!$isIndividualRequest && !$hasCompleteGroupDetails) {
        // Neither individual email is provided NOR complete group details are provided
        // Throw a validation exception assigned to multiple fields for clarity
        throw ValidationException::withMessages([
          'proposed_email' => __('Either Proposed Email/ID or complete Group Email/Admin details must be provided.'), // Malay message
          'group_email' => __('Either Proposed Email/ID or complete Group Email/Admin details must be provided.'), // Assign to multiple fields
          'group_admin_name' => __('Either Proposed Email/ID or complete Group Email/Admin details must be provided.'),
          'group_admin_email' => __('Either Proposed Email/ID or complete Group Email/Admin details must be provided.'),
        ]);
      }

      // Further check: If group email IS provided, group admin name and email are required.
      // This handles the case where proposed_email IS provided, but incomplete group details are also provided.
      if ($isGroupRequest && (!$hasCompleteGroupDetails)) {
        throw ValidationException::withMessages([
          'group_admin_name' => __('If Group Email is provided, Group Admin Name and Email are required.'), // Malay message
          'group_admin_email' => __('If Group Email is provided, Group Admin Name and Email are required.'), // Assign to multiple fields
        ]);
      }
      // --- End Custom Check ---


    } catch (ValidationException $e) {
      $this->setErrorBag($e->errors());
      Log::info('EmailApplicationForm: Submission validation failed.', ['user_id' => $user->id, 'errors' => $e->errors()]);
      session()->flash('error', __('Please fix the errors in the form before submitting.')); // Malay error message
      return null; // Stop execution if validation fails (return null)
    }


    // Ensure we have an application ID (submission typically requires a saved draft)
    if (!$this->applicationId) {
      Log::error('EmailApplicationForm: Attempted to submit application without existing draft ID.', ['user_id' => $user->id]);
      session()->flash('error', __('Cannot submit. Please save as draft first.')); // Malay error message
      return null; // Stop submission (return null)
    }

    // Find the existing draft application
    try {
      $application = EmailApplication::findOrFail($this->applicationId);
    } catch (ModelNotFoundException $e) {
      Log::error('EmailApplicationForm: Draft application not found for submission.', ['user_id' => $user->id, 'application_id' => $this->applicationId, 'exception' => $e]);
      session()->flash('error', __('Draft application not found.')); // Malay error message
      return null; // Stop execution (return null)
    }


    // Authorize if the user can update/submit this application (must be a draft owned by the user)
    // Policy 'update' should handle submission authorization (user owns draft, status is 'draft')
    try {
      // A dedicated 'submit' policy action might be cleaner if submission has different rules than general update
      // $this->authorize('submit', $application);

      // If using 'update' policy, ensure the policy method checks that status is 'draft'
      $this->authorize('update', $application);
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      Log::warning('EmailApplicationForm: User not authorized to submit application.', ['user_id' => $user->id, 'application_id' => $application->id, 'exception' => $e]);
      session()->flash('error', __('You are not authorized to submit this application.')); // Malay error message
      // *** Corrected redirect syntax for Livewire action ***
      return redirect()->route('dashboard'); // Redirect to a safe page if not authorized
    }

    // Double-check that the application is indeed in 'draft' status before submitting (redundant if policy checks, but safe)
    if ($application->status !== 'draft') {
      Log::warning('EmailApplicationForm: Attempted to submit non-draft application.', ['user_id' => $user->id, 'application_id' => $application->id, 'status' => $application->status]);
      session()->flash('error', __('Application is not in draft status and cannot be submitted.')); // Malay error message
      // *** Corrected redirect syntax for Livewire action ***
      return redirect()->route('email-applications.show', $application); // Redirect to show page if not a draft
    }


    // Use a transaction for atomicity in submitting and initiating workflow
    try {
      DB::beginTransaction();

      // 3. Use service methods to update the application data and initiate the workflow
      // Ensure updateApplication exists in EmailApplicationService and updates data
      $application = $emailApplicationService->updateApplication($application, array_merge($validatedData, [
        'certification_accepted' => true, // Explicitly set to true upon submission
        'certification_timestamp' => now(), // Record submission timestamp
        'supporting_officer_id' => $this->supporting_officer_id ?: null, // Save supporting officer ID
        'submission_timestamp' => now(), // Record the actual submission timestamp
        'status' => 'pending_support', // Set status to pending support upon submission
      ]));


      // Initiate the approval workflow (this might create the first Approval record)
      // Assuming initiateApprovalWorkflow method exists in your EmailApplicationService.
      // This method should handle the status transition to 'pending_support' and possibly notifying the supporting officer.
      // Note: We already set status to 'pending_support' above in updateApplication.
      // The initiateApprovalWorkflow method might focus on creating the initial Approval record.
      $emailApplicationService->initiateEmailApplicationApprovalWorkflow($application); // Renamed method for clarity

      DB::commit();

      // 4. Redirect to the application's show page or a confirmation page with a success message
      session()->flash('success', __('Email application submitted successfully!')); // Malay success message

      // *** Corrected redirect syntax for Livewire action ***
      return redirect()->route('email-applications.show', $application); // Redirect to the show page

    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('EmailApplicationForm: Failed to submit application or initiate workflow.', ['user_id' => $user->id, 'application_id' => $this->applicationId, 'exception' => $e]);
      session()->flash('error', __('An error occurred while submitting the application.')); // Malay error message
      return null; // Do not redirect on error, return null
    }
  }


  // Add other methods as needed (e.g., confirm cancel)
  // Optional: Method to delete a draft application (if allowed)
  public function deleteDraft(EmailApplicationService $emailApplicationService): RedirectResponse|\Illuminate\Routing\Redirector|null
  {
    // Ensure user is authenticated
    if (!Auth::check()) {
      Log::warning('EmailApplicationForm: Attempted to delete draft for unauthenticated user.');
      session()->flash('error', __('You must be logged in to delete a draft.')); // Malay error message
      return null; // Return null
    }
    $user = Auth::user();

    Log::info('EmailApplicationForm: Attempting to delete draft application.', ['user_id' => $user->id, 'application_id' => $this->applicationId]);

    // Ensure we have a draft ID
    if (!$this->applicationId) {
      Log::warning('EmailApplicationForm: No draft ID provided for deletion.', ['user_id' => $user->id]);
      session()->flash('error', __('No draft to delete.')); // Malay error message
      return null; // Return null
    }

    try {
      $application = EmailApplication::findOrFail($this->applicationId);
    } catch (ModelNotFoundException $e) {
      Log::error('EmailApplicationForm: Draft application not found for deletion.', ['user_id' => $user->id, 'application_id' => $this->applicationId, 'exception' => $e]);
      session()->flash('error', __('Draft application not found.')); // Malay error message
      return null; // Return null
    }

    // Authorize if the user can delete this application (must be a draft owned by the user)
    // Policy 'delete' should handle draft deletion authorization (user owns draft, status is 'draft')
    try {
      $this->authorize('delete', $application);
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      Log::warning('EmailApplicationForm: User not authorized to delete draft.', ['user_id' => $user->id, 'application_id' => $application->id, 'exception' => $e]);
      session()->flash('error', __('You are not authorized to delete this draft.')); // Malay error message
      // *** Corrected redirect syntax for Livewire action ***
      return redirect()->route('dashboard'); // Redirect to a safe page if not authorized
    }

    // Check if the application is indeed in 'draft' status before deleting (redundant if policy checks, but safe)
    if ($application->status !== 'draft') {
      Log::warning('EmailApplicationForm: Attempted to delete non-draft application.', ['user_id' => $user->id, 'application_id' => $application->id, 'status' => $application->status]);
      session()->flash('error', __('Application is not in draft status and cannot be deleted.')); // Malay error message
      // *** Corrected redirect syntax for Livewire action ***
      return redirect()->route('email-applications.show', $application); // Redirect to show page if not a draft
    }


    // Use the service to delete the application
    try {
      $deleted = $emailApplicationService->deleteApplication($application); // Assumes deleteApplication method exists in service

      if ($deleted) {
        Log::info('Email application draft deleted successfully via service.', ['user_id' => $user->id, 'application_id' => $this->applicationId]);
        session()->flash('success', __('Email application draft deleted successfully!')); // Malay success message
        // Redirect to the application index page after successful deletion
        // *** Corrected redirect syntax for Livewire action ***
        return redirect()->route('email-applications.index'); // Assuming an index route exists
      } else {
        Log::error('EmailApplicationForm: Service failed to delete email application draft.', ['user_id' => $user->id, 'application_id' => $this->applicationId]);
        session()->flash('error', __('Failed to delete email application draft.')); // Malay error message
        return null; // Return null on failure to delete
      }
    } catch (\Exception $e) {
      Log::error('EmailApplicationForm: Exception occurred during draft deletion.', ['user_id' => $user->id, 'application_id' => $this->applicationId, 'exception' => $e]);
      session()->flash('error', __('An error occurred while deleting the draft.')); // Malay error message
      return null; // Return null on exception
    }
  }

  // Add other methods as needed (e.g., confirm before delete)
}
