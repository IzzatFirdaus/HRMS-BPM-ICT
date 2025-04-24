<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Grade; // Assuming you need grades for dropdown
use App\Models\Department; // Assuming you need departments for dropdown
use App\Models\User; // To get current user details
use App\Models\EmailApplication; // To create application records
use App\Services\EmailApplicationService; // Use the service for logic
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


class EmailApplicationForm extends Component
{
  // Public properties that correspond to form fields
  public $service_status = ''; // Bind to service_status select
  public $purpose = '';
  public $proposed_email = ''; // Can be pre-filled or suggested
  public $group_email = '';
  public $group_admin_name = '';
  public $group_admin_email = '';
  public $certification = false; // Bind to certification checkbox

  // Properties to hold dropdown data
  public $grades;
  public $departments;

  // Optional: If updating an existing draft application
  public $applicationId;
  protected $emailApplication;


  // Validation Rules - adjust based on your form and requirements
  //protected $rules = []; // Initialize as an empty array

  public function rules()
  {
    return [
      'service_status' => ['required', Rule::in(['permanent', 'contract', 'mystep', 'intern', 'other_agency'])],
      'purpose' => 'required|string|max:500',
      'proposed_email' => 'nullable|email|max:255', // Validate format if filled
      'group_email' => 'nullable|email|max:255',
      'group_admin_name' => 'nullable|string|max:255',
      'group_admin_email' => 'nullable|email|max:255',
      'certification' => 'accepted', // Requires the checkbox to be ticked and value is '1'
    ];
  }

  // Real-time validation
  public function updated($propertyName)
  {
    $this->validateOnly($propertyName);

    // Example: Suggest email when purpose is filled or service status changes
    if ($propertyName === 'purpose' || $propertyName === 'service_status') {
      // You could call a service method here to suggest email
      // $this->proposed_email = (new EmailApplicationService())->suggestEmailAddress(Auth::user());
    }
  }


  public function mount(?EmailApplication $emailApplication = null)
  {
    $this->emailApplication = $emailApplication;

    // Load dropdown data
    $this->grades = Grade::all();
    $this->departments = Department::all(); // Assuming Department model exists

    // If editing an existing application, populate form fields
    if ($emailApplication) {
      // $this->authorize('update', $emailApplication); // Check policy

      $this->applicationId = $emailApplication->id;
      $this->service_status = $emailApplication->service_status; // Assuming service_status is stored on the application or user? Check your DB.
      // If service status is on User model, retrieve from Auth::user()
      $this->service_status = Auth::user()->service_status ?? '';

      $this->purpose = $emailApplication->purpose;
      $this->proposed_email = $emailApplication->proposed_email;
      $this->group_email = $emailApplication->group_email;
      $this->group_admin_name = $emailApplication->group_admin_name;
      $this->group_admin_email = $emailApplication->group_admin_email;
      $this->certification = $emailApplication->certification_accepted; // Assuming certification state is saved

      // Prevent editing if not in 'draft' status
      if ($emailApplication->status !== 'draft') {
        // Redirect or disable form fields
        // $this->redirect(route('email-applications.show', $emailApplication));
      }
    } else {
      // For new applications, you might pre-fill based on the user
      // $this->service_status = Auth::user()->service_status ?? '';
      // $this->proposed_email = (new EmailApplicationService())->suggestEmailAddress(Auth::user());
    }
  }

  public function render()
  {
    // Pass dropdown data and potentially the application instance to the view
    return view('livewire.email-application-form', [
      'grades' => $this->grades,
      'departments' => $this->departments,
      'emailApplication' => $this->emailApplication, // Pass for conditional rendering in view
    ]);
  }

  public function submit(EmailApplicationService $emailApplicationService)
  {
    // 1. Validate the form data
    $validatedData = $this->validate();

    // Ensure certification is required for submission, even if policy allows draft saving
    if (!$this->certification) {
      $this->addError('certification', 'You must accept the certification to submit.');
      return; // Stop submission if not certified
    }


    // 2. Process the form submission (Create or Update)
    $user = Auth::user();

    if ($this->applicationId) {
      // Update existing application (e.g., from draft to pending_support)
      $application = EmailApplication::findOrFail($this->applicationId);
      // $this->authorize('update', $application); // Policy check

      // Update data - careful with mass assignment
      $application->purpose = $validatedData['purpose'];
      $application->proposed_email = $validatedData['proposed_email'];
      $application->group_email = $validatedData['group_email'];
      $application->group_admin_name = $validatedData['group_admin_name'];
      $application->group_admin_email = $validatedData['group_admin_email'];
      $application->certification_accepted = $validatedData['certification']; // Save certification state

      // Use service to handle status transition and potential notifications
      $emailApplicationService->submitApplication($application, $validatedData);


      session()->flash('message', 'Email application updated and submitted successfully!');
    } else {
      // Create a new application
      // $this->authorize('create', EmailApplication::class); // Policy check

      // Prepare data for creation, including user_id
      $dataToCreate = array_merge($validatedData, [
        'user_id' => $user->id,
        // service_status might come from the user model or form - depends on design
        // 'service_status' => $user->service_status,
        'status' => 'draft', // Create as draft initially, or 'pending_support' if immediate submission
        'certification_accepted' => $validatedData['certification'],
        'certification_timestamp' => now(), // Timestamp on initial submission
      ]);


      // Use service to create the application
      $application = $emailApplicationService->createApplication($user, $dataToCreate);

      // If you want immediate submission, you might call submitApplication here too
      // $emailApplicationService->submitApplication($application, $validatedData);

      session()->flash('message', 'Email application draft saved successfully!');
      // If you want to redirect to the form in edit mode after saving draft:
      // return $this->redirect(route('email-applications.edit', $application));
    }

    // Optional: Redirect to a status page or dashboard after submission
    // $this->redirect(route('email-applications.show', $application));

    // Reset form fields after successful submission/save
    $this->resetExcept(['grades', 'departments']); // Keep dropdown data
  }

  // Add other methods as needed (e.g., saveAsDraft)
}
