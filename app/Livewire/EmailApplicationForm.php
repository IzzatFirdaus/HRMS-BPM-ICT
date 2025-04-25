<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Grade; // Assuming you need grades for dropdown
use App\Models\Department; // Assuming you need departments for dropdown
use App\Models\User; // To get current user details and select supporting officer
use App\Models\EmailApplication; // To create application records
use App\Services\EmailApplicationService; // Use the service for logic
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Validation\Rule; // Import Rule for validation
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Trait for policy checks
use Illuminate\Database\Eloquent\ModelNotFoundException; // Import ModelNotFoundException


class EmailApplicationForm extends Component
{
  use AuthorizesRequests; // Use the AuthorizesRequests trait

  // Public properties that correspond to form fields
  public $service_status = ''; // Bind to service_status select
  public $purpose = ''; // Corresponds to 'Tujuan/Catatan'
  public $proposed_email = ''; // Corresponds to 'Cadangan E-mel/ID'
  public $group_email = '';
  public $group_admin_name = '';
  public $group_admin_email = '';
  public $certification = false; // Bind to certification checkbox ('Perakuan Pemohon')

  // New public property for the Supporting Officer selection
  public $supporting_officer_id = ''; // Bind to supporting officer dropdown

  // Properties to hold dropdown data
  public $grades; // For displaying applicant's grade (likely from User model)
  public $departments; // For displaying applicant's department (likely from User model)
  public $supportingOfficers; // List of users eligible to be supporting officers


  // Optional: If updating an existing draft application
  public $applicationId;
  protected ?EmailApplication $emailApplication = null; // Type hint and initialize as nullable

  // Applicant's details displayed from User model (not form fields)
  public $applicantName;
  public $applicantIC;
  public $applicantGradeName; // Name of the applicant's grade
  public $applicantPositionName; // Name of the applicant's position
  public $applicantDepartmentName; // Name of the applicant's department/unit
  public $applicantMobileNumber;
  public $applicantPersonalEmail;


  // No static $rules property, we will use validate() calls in methods

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
      'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      'purpose' => 'nullable|string|max:500', // Can be null for draft
      'proposed_email' => 'nullable|email|max:255',
      'group_email' => 'nullable|email|max:255',
      'group_admin_name' => 'nullable|string|max:255',
      'group_admin_email' => 'nullable|email|max:255',
      'supporting_officer_id' => 'nullable|exists:users,id', // Can be null for draft
      'certification' => 'boolean', // Certification is optional for draft
    ];

    try {
      $this->validateOnly($propertyName, $realtimeRules);
    } catch (\Illuminate\Validation\ValidationException $e) {
      // Keep existing errors for other properties
      $this->setErrorBag($this->getErrorBag()->merge($e->errors()));
      // Do not re-throw to prevent halting real-time updates
    }


    // Example: Suggest email when purpose is filled or service status changes (if needed in real-time)
    // This might be better triggered manually or in mount/saveAsDraft
    // if ($propertyName === 'purpose' || $propertyName === 'service_status') {
    //     // You could call a service method here to suggest email
    //     // $this->proposed_email = app(EmailApplicationService::class)->suggestEmailAddress(Auth::user());
    // }
  }


  /**
   * Mount the component.
   *
   * @param EmailApplication|null $emailApplication Optional existing application for editing.
   */
  public function mount(?EmailApplication $emailApplication = null)
  {
    // Ensure user is authenticated to access the form
    if (!Auth::check()) {
      return $this->redirect(route('login')); // Redirect to login if not authenticated
    }

    $user = Auth::user(); // The authenticated user is the applicant

    $this->emailApplication = $emailApplication;

    // Load data needed for dropdowns
    // $this->grades = Grade::all(); // Grades might not be needed as a dropdown for applicant info display
    // $this->departments = Department::all(); // Departments might not be needed as a dropdown for applicant info display

    // Load users eligible to be supporting officers (e.g., Grade 9 and above)
    // Assuming User model has a 'grade' relationship and Grade model has a 'level' attribute
    $this->supportingOfficers = User::with('grade')
      ->whereHas('grade', fn($query) => $query->where('level', '>=', config('motac.approval.min_supporting_officer_grade_level', 9))) // Filter by min grade level from config or default
      ->orderBy('name') // Order by name for display
      ->get();


    // Populate applicant's details from the authenticated user model for display
    $this->applicantName = $user->full_name ?? $user->name; // Adjust based on your User model attributes
    $this->applicantIC = $user->ic_number; // Assuming ic_number attribute
    $this->applicantGradeName = $user->grade->name ?? 'N/A'; // Assuming grade relationship and name attribute
    $this->applicantPositionName = $user->position->name ?? 'N/A'; // Assuming position relationship and name attribute
    $this->applicantDepartmentName = $user->department->name ?? 'N/A'; // Assuming department relationship and name attribute
    $this->applicantMobileNumber = $user->mobile_number; // Assuming mobile_number attribute
    $this->applicantPersonalEmail = $user->email; // Assuming 'email' is personal email


    // If editing an existing application, populate form fields
    if ($emailApplication) {
      // Authorize if the user can view/update this specific application (should be a draft owned by the user)
      $this->authorize('view', $emailApplication); // Check policy for viewing
      $this->authorize('update', $emailApplication); // Check policy for updating (should only be allowed for 'draft')


      // Prevent editing if the application is not in 'draft' status
      if ($emailApplication->status !== 'draft') {
        // If it's not a draft, redirect to the show page or an error page
        // You cannot edit an application that is already submitted or processed.
        session()->flash('error', 'Cannot edit application as it is not in draft status.');
        $this->redirect(route('email-applications.show', $emailApplication));
        return; // Stop mount execution
      }

      // Populate properties from the existing application
      $this->applicationId = $emailApplication->id;
      // service_status should be stored on the Application model if the user selects it per application
      $this->service_status = $emailApplication->service_status;

      $this->purpose = $emailApplication->purpose;
      $this->proposed_email = $emailApplication->proposed_email;
      $this->group_email = $emailApplication->group_email;
      $this->group_admin_name = $emailApplication->group_admin_name;
      $this->group_admin_email = $emailApplication->group_admin_email;
      $this->certification = (bool) $emailApplication->certification_accepted; // Cast to boolean

      // Populate supporting officer ID if stored on the application
      $this->supporting_officer_id = $emailApplication->supporting_officer_id;
    } else {
      // For new applications, pre-fill fields based on the authenticated user if available
      // service_status comes from the User model if not selected per application
      $this->service_status = $user->service_status ?? '';
      // You might suggest an initial proposed email based on user data
      // $this->proposed_email = app(EmailApplicationService::class)->suggestEmailAddress($user);

      // Set default for supporting officer if needed (e.g., the user's supervisor)
      // $this->supporting_officer_id = $user->supervisor_id ?? ''; // Assuming a supervisor_id field
    }
  }

  /**
   * Render the component view.
   *
   * @return \Illuminate\View\View
   */
  public function render()
  {
    // Pass dropdown data and the application instance to the view
    return view('livewire.email-application-form', [
      'grades' => $this->grades, // Pass dropdown data (if used in view)
      'departments' => $this->departments, // Pass dropdown data (if used in view)
      'supportingOfficers' => $this->supportingOfficers, // Pass supporting officers list
      'emailApplication' => $this->emailApplication, // Pass the application instance if editing
    ]);
  }


  /**
   * Save the application form data as a draft.
   *
   * @param EmailApplicationService $emailApplicationService
   * @return void
   */
  public function saveAsDraft(EmailApplicationService $emailApplicationService)
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
      'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      'purpose' => 'nullable|string|max:500', // Purpose is optional for draft
      'proposed_email' => 'nullable|email|max:255',
      'group_email' => 'nullable|email|max:255',
      'group_admin_name' => 'nullable|string|max:255',
      'group_admin_email' => 'nullable|email|max:255',
      'supporting_officer_id' => 'nullable|exists:users,id', // Supporting officer optional for draft
      'certification' => 'boolean', // Certification optional for draft
    ];

    // 2. Validate the form data against draft rules
    // Catch validation exception to prevent halting execution on error
    try {
      $validatedData = $this->validate($draftRules);
    } catch (\Illuminate\Validation\ValidationException $e) {
      $this->setErrorBag($e->errors());
      session()->flash('error', 'Please fix the errors in the form.');
      return;
    }


    if ($this->applicationId) {
      // If applicationId exists, update the existing application (which must be a draft)
      try {
        // Retrieve the single EmailApplication model instance
        $application = EmailApplication::where('id', $this->applicationId)->firstOrFail(); // Corrected retrieval
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
        return $this->redirect(route('email-applications.show', $application));
      }


      // Call service method to update the draft
      // Ensure updateApplication method exists in EmailApplicationService and accepts these arguments
      $application = $emailApplicationService->updateApplication($application, $validatedData); // Line 252

      session()->flash('message', 'Email application draft updated successfully!');
    } else {
      // If no applicationId, create a new application record with status 'draft'
      // Authorize if the user can create an application
      $this->authorize('create', EmailApplication::class);

      // Prepare data for creation, including user_id and initial status
      $dataToCreate = array_merge($validatedData, [
        'user_id' => $user->id,
        'status' => 'draft', // Set initial status to draft
        'certification_accepted' => $this->certification, // Save certification state even if not required for draft
        // certification_timestamp is set on submission
        'service_status' => $this->service_status, // Ensure service status is included
        'supporting_officer_id' => $this->supporting_officer_id, // Include supporting officer
      ]);

      // Call service method to create the application
      // Ensure createApplication method exists in EmailApplicationService and accepts these arguments
      $application = $emailApplicationService->createApplication($user, $dataToCreate);

      // Store the new application ID to allow future updates/submission
      $this->applicationId = $application->id;
      $this->emailApplication = $application; // Update the internal property as well

      session()->flash('message', 'Email application draft saved successfully!');
    }

    // Keep the user on the form and display the flash message
  }


  /**
   * Submit the application form for approval.
   * This transitions a draft application to 'pending_support' status.
   *
   * @param EmailApplicationService $emailApplicationService
   * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse|void
   */
  public function submitApplication(EmailApplicationService $emailApplicationService)
  {
    // Ensure user is authenticated
    if (!Auth::check()) {
      session()->flash('error', 'You must be logged in to submit an application.');
      return;
    }
    $user = Auth::user();


    // 1. Define validation rules for final submission.
    // These rules are typically more strict and include required fields like purpose, supporting officer, and certification.
    $submitRules = [
      'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      'purpose' => 'required|string|max:500', // Purpose is required for submission
      'proposed_email' => 'nullable|email|max:255',
      'group_email' => 'nullable|email|max:255',
      'group_admin_name' => 'nullable|string|max:255',
      'group_admin_email' => 'nullable|email|max:255',
      'supporting_officer_id' => ['required', 'exists:users,id'], // Supporting officer is required for submission
      'certification' => 'accepted', // Certification must be true ('accepted' rule checks for '1' or true)
    ];

    // 2. Validate the form data against submission rules
    // Catch validation exception to prevent halting execution on error
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
      // Retrieve the single EmailApplication model instance
      $application = EmailApplication::where('id', $this->applicationId)->firstOrFail(); // Corrected retrieval
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
      return $this->redirect(route('email-applications.show', $application));
    }

    // 3. Use service methods to update the application data and initiate the workflow
    // Ensure updateApplication exists in EmailApplicationService and updates data
    $application = $emailApplicationService->updateApplication($application, array_merge($validatedData, [
      'certification_accepted' => $this->certification, // Ensure certification status is saved on submission
      'certification_timestamp' => now(), // Set submission timestamp
      // service_status is likely set on draft, but ensure it's in validatedData
      // supporting_officer_id is in validatedData
    ])); // Line 354


    // Initiate the approval workflow (changes status from 'draft' to 'pending_support', creates first approval)
    // Ensure initiateApprovalWorkflow method exists in EmailApplicationService
    $application = $emailApplicationService->initiateApprovalWorkflow($application);


    // 4. Redirect to the application's show page or a confirmation page with a success message
    session()->flash('message', 'Email application submitted successfully!');
    return $this->redirect(route('email-applications.show', $application)); // Redirect to the show page

    // Alternative redirect: Redirect to the dashboard
    // return $this->redirect(route('dashboard'));
  }


  // Add other methods as needed (e.g., confirm cancel)
}
