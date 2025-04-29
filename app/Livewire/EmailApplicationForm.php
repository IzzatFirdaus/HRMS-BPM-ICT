<?php

namespace App\Livewire;

// --- Standard Namespace/Class Use Statements (must be before the class) ---
use Livewire\Component;
use App\Models\Grade; // Assuming you need grades for dropdown - removed fetching if not used
use App\Models\Department; // Assuming you need departments for dropdown - removed fetching if not used
use App\Models\User; // To get current user details and select supporting officer
use App\Models\EmailApplication; // To create application records
use App\Services\EmailApplicationService; // Use the service for logic - IDE may report undefined method here
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
  // public $grades; // For displaying applicant's grade (likely from User model) - Removed fetching if not used for dropdown
  // public $departments; // For displaying applicant's department (likely from User model) - Removed fetching if not used for dropdown
  public $supportingOfficers; // List of users eligible to be supporting officers


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
      'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      'purpose' => 'nullable|string|max:500',
      'proposed_email' => 'nullable|email:rfc,dns|max:255',
      'group_email' => 'nullable|email:rfc,dns|max:255',
      'group_admin_name' => 'nullable|string|max:255',
      'group_admin_email' => 'nullable|email:rfc,dns|max:255',
      'supporting_officer_id' => 'nullable|exists:users,id',
      'certification' => 'boolean', // Just check boolean type in real-time
    ];

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
   * @return \Illuminate\Http\RedirectResponse|null // Return type can be RedirectResponse or null
   */
  public function mount(?EmailApplication $emailApplication = null): RedirectResponse|null
  {
    // Ensure user is authenticated to access the form
    if (!Auth::check()) {
      Log::warning('EmailApplicationForm mounted for unauthenticated user.');
      return $this->redirect(route('login')); // Redirect to login if not authenticated
    }

    $user = Auth::user(); // The authenticated user is the applicant

    $this->emailApplication = $emailApplication;

    // Load data needed for dropdowns
    // $this->grades = Grade::all(); // Removed fetching if only for display
    // $this->departments = Department::all(); // Removed fetching if only for display

    // Load users eligible to be supporting officers (e.g., Grade 9 and above)
    try {
      $this->supportingOfficers = User::with('grade') // Eager load grade relationship
        ->whereHas('grade', fn($query) => $query->where('level', '>=', config('motac.approval.min_supporting_officer_grade_level', 9))) // Filter by min grade level from config or default
        ->orderBy('name') // Order by name for display
        ->get();
    } catch (\Exception $e) {
      Log::error('EmailApplicationForm: Error fetching supporting officers.', ['user_id' => $user->id, 'exception' => $e]);
      $this->supportingOfficers = collect(); // Set to empty collection on error
      session()->flash('error', __('Could not load supporting officers. Please try again later.'));
    }


    // Populate applicant's details from the authenticated user model for display
    // Use null-safe operator and nullish coalescing for robust access
    $this->applicantName = $user->full_name ?? $user->name ?? __('N/A');
    $this->applicantIC = $user->ic_number ?? __('N/A');
    $this->applicantGradeName = $user->grade?->name ?? __('N/A'); // Use null-safe for grade relationship
    $this->applicantPositionName = $user->position?->name ?? __('N/A'); // Assuming position relationship
    $this->applicantDepartmentName = $user->department?->name ?? __('N/A'); // Use null-safe for department relationship
    $this->applicantMobileNumber = $user->mobile_number ?? __('N/A');
    $this->applicantPersonalEmail = $user->email ?? __('N/A');


    // If editing an existing application, populate form fields
    if ($emailApplication) {
      // Authorize if the user can view/update this specific application (should be a draft owned by the user)
      // Policy check should implicitly verify ownership and status.
      try {
        $this->authorize('view', $emailApplication);
        $this->authorize('update', $emailApplication); // Policy 'update' should handle draft editing authorization
      } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
        Log::warning('EmailApplicationForm: User not authorized to edit application.', ['user_id' => $user->id, 'application_id' => $emailApplication->id]);
        session()->flash('error', __('You are not authorized to edit this application.'));
        return $this->redirect(route('email-applications.show', $emailApplication)); // Redirect if not authorized
      }

      // Prevent editing if the application is not in 'draft' status
      // This check is redundant if policy's 'update' method correctly checks status, but good as a safeguard.
      if ($emailApplication->status !== 'draft') {
        Log::warning('EmailApplicationForm: Attempted to edit non-draft application.', ['user_id' => $user->id, 'application_id' => $emailApplication->id, 'status' => $emailApplication->status]);
        session()->flash('error', __('Cannot edit application as it is not in draft status.'));
        return $this->redirect(route('email-applications.show', $emailApplication)); // Redirect if not a draft
      }

      // Populate properties from the existing application
      $this->applicationId = $emailApplication->id;
      $this->service_status = $emailApplication->service_status ?? '';
      $this->purpose = $emailApplication->purpose ?? '';
      $this->proposed_email = $emailApplication->proposed_email ?? '';
      $this->group_email = $emailApplication->group_email ?? '';
      $this->group_admin_name = $emailApplication->group_admin_name ?? '';
      $this->group_admin_email = $emailApplication->group_admin_email ?? '';
      $this->certification = (bool) ($emailApplication->certification_accepted ?? false);

      // Populate supporting officer ID if stored on the application
      $this->supporting_officer_id = $emailApplication->supporting_officer_id ?? '';
    } else {
      // For new applications, pre-fill fields based on the authenticated user if available
      $this->service_status = $user->service_status ?? '';
      // You might suggest an initial proposed email based on user data (example)
      // $this->proposed_email = app(EmailApplicationService::class)->suggestEmailAddress($user) ?? '';

      // Set default for supporting officer if needed (e.g., the user's supervisor)
      // $this->supporting_officer_id = $user->supervisor_id ?? '';
    }

    // If we reached this point and didn't redirect, return null
    return null;
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
      // 'grades' => $this->grades, // Removed passing if not used in view
      // 'departments' => $this->departments, // Removed passing if not used in view
      'supportingOfficers' => $this->supportingOfficers ?? collect(), // Pass supporting officers list, default to empty collection
      'emailApplication' => $this->emailApplication, // Pass the application instance if editing
    ]);
  }


  /**
   * Save the application form data as a draft.
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
      return; // Return null implicitly (void)
    }
    $user = Auth::user();

    // 1. Define validation rules for saving a draft.
    // These are typically less strict than submission rules.
    $draftRules = [
      'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      'purpose' => 'nullable|string|max:500',
      'proposed_email' => 'nullable|email:rfc,dns|max:255',
      'group_email' => 'nullable|email:rfc,dns|max:255',
      'group_admin_name' => 'nullable|string|max:255',
      'group_admin_email' => 'nullable|email:rfc,dns|max:255',
      'supporting_officer_id' => 'nullable|exists:users,id',
      'certification' => 'boolean',
    ];

    // 2. Validate the form data against draft rules
    try {
      $validatedData = $this->validate($draftRules);
    } catch (ValidationException $e) {
      $this->setErrorBag($e->errors());
      Log::info('EmailApplicationForm: Draft validation failed.', ['user_id' => $user->id, 'errors' => $e->errors()]);
      session()->flash('error', __('Please fix the errors in the form.'));
      return; // Stop execution if validation fails (return null implicitly)
    }


    // Use a transaction for atomicity in saving/updating
    try {
      DB::beginTransaction();

      if ($this->applicationId) {
        // If applicationId exists, update the existing application (which must be a draft)
        try {
          $application = EmailApplication::findOrFail($this->applicationId);
        } catch (ModelNotFoundException $e) {
          DB::rollBack();
          Log::error('EmailApplicationForm: Draft application not found for update.', ['user_id' => $user->id, 'application_id' => $this->applicationId, 'exception' => $e]);
          session()->flash('error', __('Draft application not found.'));
          return; // Return null implicitly (void)
        }

        // Authorize if the user can update this application (must be a draft owned by the user)
        try {
          $this->authorize('update', $application); // Policy 'update' should handle draft editing authorization
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
          DB::rollBack();
          Log::warning('EmailApplicationForm: User not authorized to update draft.', ['user_id' => $user->id, 'application_id' => $application->id]);
          session()->flash('error', __('You are not authorized to update this draft.'));
          // The commented-out redirect below is causing the IDE error about void/redirect conflict.
          // If you uncomment this, the method must return RedirectResponse|null
          // return $this->redirect(route('email-applications.show', $application));
          return; // Return null implicitly (void)
        }


        // Check if the application is indeed in 'draft' status before updating (redundant if policy checks, but safe)
        if ($application->status !== 'draft') {
          DB::rollBack();
          Log::warning('EmailApplicationForm: Attempted to update non-draft application.', ['user_id' => $user->id, 'application_id' => $application->id, 'status' => $application->status]);
          session()->flash('error', __('Cannot update application as it is not in draft status.'));
          // The commented-out redirect below is causing the IDE error about void/redirect conflict.
          // return $this->redirect(route('email-applications.show', $application));
          return; // Return null implicitly (void)
        }


        // Call service method to update the draft
        $application = $emailApplicationService->updateApplication($application, $validatedData);

        session()->flash('success', __('Email application draft updated successfully!'));
      } else {
        // If no applicationId, create a new application record with status 'draft'
        // Authorize if the user can create an application
        try {
          $this->authorize('create', EmailApplication::class);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
          DB::rollBack();
          Log::warning('EmailApplicationForm: User not authorized to create application.', ['user_id' => $user->id]);
          session()->flash('error', __('You are not authorized to create an application.'));
          return; // Return null implicitly (void)
        }


        // Prepare data for creation, including user_id and initial status
        // Use nullish coalescing for nullable fields to ensure they save as NULL in DB if empty string
        $dataToCreate = array_merge($validatedData, [
          'user_id' => $user->id,
          'status' => 'draft',
          'certification_accepted' => $this->certification,
          'supporting_officer_id' => $this->supporting_officer_id ?: null,
        ]);

        // Call service method to create the application
        $application = $emailApplicationService->createApplication($user, $dataToCreate);

        // Store the new application ID to allow future updates/submission
        $this->applicationId = $application->id;
        $this->emailApplication = $application;

        session()->flash('success', __('Email application draft saved successfully!'));
      }

      DB::commit();
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('EmailApplicationForm: Failed to save/update draft.', ['user_id' => $user->id, 'application_id' => $this->applicationId, 'exception' => $e]);
      session()->flash('error', __('An error occurred while saving the draft.'));
      return; // Return null implicitly (void)
    }

    // Explicitly return for clarity in a void method
    return;
  }


  /**
   * Submit the application form for approval.
   * This transitions a draft application to 'pending_support' status.
   *
   * @param EmailApplicationService $emailApplicationService The service to handle application logic.
   * @return \Illuminate\Http\RedirectResponse|null // Return type can be RedirectResponse or null
   */
  public function submitApplication(EmailApplicationService $emailApplicationService): RedirectResponse|null
  {
    // Ensure user is authenticated
    if (!Auth::check()) {
      Log::warning('EmailApplicationForm: Attempted to submit application for unauthenticated user.');
      session()->flash('error', __('You must be logged in to submit an application.'));
      return null; // Return null (not redirect)
    }
    $user = Auth::user();


    // 1. Define validation rules for final submission.
    // These rules are typically more strict and include required fields like purpose, supporting officer, and certification.
    // Referencing PDF: purpose, proposed email (or group email related fields), supporting officer, certification are key fields.
    $submitRules = [
      'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      'purpose' => 'required|string|max:500',
      'proposed_email' => 'nullable|email:rfc,dns|max:255',
      'group_email' => 'nullable|email:rfc,dns|max:255',
      'group_admin_name' => 'nullable|string|max:255',
      'group_admin_email' => 'nullable|email:rfc,dns|max:255',
      'supporting_officer_id' => ['required', 'exists:users,id'],
      'certification' => 'accepted', // Certification must be true ('accepted' rule checks for '1' or true)
      // Add conditional logic if EITHER proposed_email OR group_email/admin fields are required, but not necessarily both sets.
      // Example: Rule::requiredIf(empty($this->group_email) && empty($this->group_admin_name) && empty($this->group_admin_email), 'proposed_email'),
      // You might need custom validation logic or rules for "Proposed Email / ID" OR "Group Email / Admin".
      // For now, assuming proposed_email/group_email/admin fields are nullable but validated if filled.
    ];

    // 2. Validate the form data against submission rules
    try {
      $validatedData = $this->validate($submitRules);
    } catch (ValidationException $e) {
      $this->setErrorBag($e->errors());
      Log::info('EmailApplicationForm: Submission validation failed.', ['user_id' => $user->id, 'errors' => $e->errors()]);
      session()->flash('error', __('Please fix the errors in the form before submitting.'));
      return null; // Stop execution if validation fails (return null)
    }


    // Ensure we have an application ID (submission typically requires a saved draft)
    if (!$this->applicationId) {
      Log::error('EmailApplicationForm: Attempted to submit application without existing draft ID.', ['user_id' => $user->id]);
      session()->flash('error', __('Cannot submit. Please save as draft first.'));
      return null; // Stop submission (return null)
    }

    // Find the existing draft application
    try {
      $application = EmailApplication::findOrFail($this->applicationId);
    } catch (ModelNotFoundException $e) {
      Log::error('EmailApplicationForm: Draft application not found for submission.', ['user_id' => $user->id, 'application_id' => $this->applicationId, 'exception' => $e]);
      session()->flash('error', __('Draft application not found.'));
      return null; // Stop execution (return null)
    }


    // Authorize if the user can update/submit this application (must be a draft owned by the user)
    try {
      $this->authorize('update', $application); // Policy 'update' should handle submission authorization
    } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
      Log::warning('EmailApplicationForm: User not authorized to submit application.', ['user_id' => $user->id, 'application_id' => $application->id]);
      session()->flash('error', __('You are not authorized to submit this application.'));
      return $this->redirect(route('email-applications.show', $application)); // Redirect if not authorized
    }

    // Double-check that the application is indeed in 'draft' status before submitting (redundant if policy checks, but safe)
    if ($application->status !== 'draft') {
      Log::warning('EmailApplicationForm: Attempted to submit non-draft application.', ['user_id' => $user->id, 'application_id' => $application->id, 'status' => $application->status]);
      session()->flash('error', __('Application is not in draft status and cannot be submitted.'));
      return $this->redirect(route('email-applications.show', $application)); // Redirect if not a draft
    }


    // Use a transaction for atomicity in submitting and initiating workflow
    try {
      DB::beginTransaction();

      // 3. Use service methods to update the application data and initiate the workflow
      // Ensure updateApplication exists in EmailApplicationService and updates data
      $application = $emailApplicationService->updateApplication($application, array_merge($validatedData, [
        'certification_accepted' => $this->certification,
        'certification_timestamp' => now(),
        'supporting_officer_id' => $this->supporting_officer_id ?: null,
      ]));

      // Initiate the approval workflow (changes status from 'draft' to 'pending_support', creates first approval)
      // The IDE reports 'Undefined method 'initiateApprovalWorkflow''. Assuming this method exists in your service.
      $emailApplicationService->initiateApprovalWorkflow($application);

      DB::commit();

      // 4. Redirect to the application's show page or a confirmation page with a success message
      session()->flash('success', __('Email application submitted successfully!'));
      return $this->redirect(route('email-applications.show', $application)); // Redirect to the show page

    } catch (\Exception $e) {
      DB::rollBack();
      Log::error('EmailApplicationForm: Failed to submit application or initiate workflow.', ['user_id' => $user->id, 'application_id' => $this->applicationId, 'exception' => $e]);
      session()->flash('error', __('An error occurred while submitting the application.'));
      return null; // Do not redirect on error, return null
    }
  }


  // Add other methods as needed (e.g., confirm cancel)
}
