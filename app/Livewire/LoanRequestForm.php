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
use App\Models\Unit; // Used in mount for applicant details
use Illuminate\View\View; // For render return type hint
// Removed: use Illuminate\Http\RedirectResponse; // <-- Removed this import
use Illuminate\Support\Collection; // Import Collection
use Illuminate\Support\Facades\Log; // Import Log facade for error logging
// Removed unnecessary DB facade import if no manual transactions are used in this method
// use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // For date handling
use Exception; // Import generic Exception
use Illuminate\Http\RedirectResponse; // <-- Re-added the import for the return value of redirect()


class LoanRequestForm extends Component
{
  use AuthorizesRequests; // Trait for policy checks

  // Properties for Loan Details (Bahagian 1 & 2)
  public string $purpose = ''; // Tujuan Permohonan
  public string $location = ''; // Lokasi
  public ?string $loanStartDate = null; // Tarikh Pinjaman
  public ?string $loanEndDate = null; // Tarikh Dijangka Pulang

  // Properties for Applicant Details (auto-populated from User/Employee)
  public string $fullName = ''; // Nama Penuh
  public string $positionGrade = ''; // Jawatan & Gred
  public string $departmentUnit = ''; // Bahagian/Unit
  public string $phoneNumber = ''; // No. Telefon

  // Properties for Responsible Officer (Bahagian 2)
  public bool $isApplicantResponsible = true; // Checkbox state
  public ?string $responsibleOfficerName = null;
  public ?string $responsibleOfficerPositionGrade = null;
  public ?string $responsibleOfficerPhoneNumber = null;

  // Properties for Equipment Items (Bahagian 3)
  // Use an array of arrays or a Collection of objects for multiple items
  public array $loanItems = [
    [
      'equipmentType' => '',
      'quantityRequested' => 1, // Default quantity
      'notes' => '',
    ]
  ];

  // Property for Applicant Confirmation (Bahagian 4)
  public bool $applicantConfirmation = false;

  // Property to hold the Loan Application model instance (for editing if implemented)
  public ?LoanApplication $loanApplication = null;

  // Dependency Injection for the Service
  protected LoanApplicationService $loanApplicationService;

  public function boot(LoanApplicationService $loanApplicationService)
  {
    $this->loanApplicationService = $loanApplicationService;
  }


  /**
   * Mount the component, typically used to load data for editing.
   * @param  LoanApplication|null  $loanApplication Optional application to load for editing.
   */
  public function mount(?LoanApplication $loanApplication = null)
  {
    // Load existing application data if provided (for editing)
    if ($loanApplication) {
      $this->loanApplication = $loanApplication;

      // Populate properties from the existing application
      $this->purpose = $loanApplication->purpose;
      $this->location = $loanApplication->location;
      // Use null-safe operator and optional formatting for date casts
      $this->loanStartDate = $loanApplication->loan_start_date?->format('Y-m-d');
      $this->loanEndDate = $loanApplication->loan_end_date?->format('Y-m-d');

      // Applicant details (should ideally come from the user relationship)
      // Safely access relationships using null-safe operator
      $this->fullName = $loanApplication->user?->name ?? ''; // Assuming user name is the full name
      // *** FIX: Use null-safe operators for safer access to employee and related properties ***
      $this->positionGrade = ($loanApplication->user?->employee?->position?->name ?? '') . ' ' . ($loanApplication->user?->employee?->grade?->name ?? ''); // Combine position and grade safely, handle nulls
      $this->departmentUnit = ($loanApplication->user?->employee?->department?->name ?? '') . ' / ' . ($loanApplication->user?->employee?->unit?->name ?? ''); // Combine department and unit safely, handle nulls
      $this->phoneNumber = $loanApplication->user?->employee?->phone_number ?? '';


      // Responsible Officer details
      // Check if responsible officer ID is set and is NOT the applicant's user ID
      $this->isApplicantResponsible = ($loanApplication->responsible_officer_id === null || $loanApplication->responsible_officer_id === $loanApplication->user_id);

      if (!$this->isApplicantResponsible && $loanApplication->responsibleOfficer) {
        // Safely access responsible officer details using null-safe operator
        $this->responsibleOfficerName = $loanApplication->responsibleOfficer->name ?? null;
        // *** FIX: Use null-safe operators for safer access to employee and related properties ***
        $this->responsibleOfficerPositionGrade = ($loanApplication->responsibleOfficer->employee?->position?->name ?? '') . ' ' . ($loanApplication->responsibleOfficer->employee?->grade?->name ?? ''); // Combine safely, handle nulls
        $this->responsibleOfficerPhoneNumber = $loanApplication->responsibleOfficer->employee?->phone_number ?? null;
      } else {
        // If applicant is responsible or responsible officer is null/not found, ensure these are null
        $this->responsibleOfficerName = null;
        $this->responsibleOfficerPositionGrade = null;
        $this->responsibleOfficerPhoneNumber = null;
      }


      // Load loan items from the existing application
      $this->loanItems = $loanApplication->items->map(function ($item) {
        return [
          'equipmentType' => $item->equipment_type,
          'quantityRequested' => $item->quantity_requested,
          'notes' => $item->notes,
          // Do not load quantity_approved/issued/returned into the form for creation/basic edit
        ];
      })->toArray();

      // Ensure at least one item if the loaded application had none (shouldn't happen normally unless data is inconsistent)
      if (empty($this->loanItems)) {
        $this->loanItems[] = [
          'equipmentType' => '',
          'quantityRequested' => 1,
          'notes' => '',
        ];
      }

      // Applicant Confirmation (if the application has already been submitted/confirmed)
      $this->applicantConfirmation = ($loanApplication->applicant_confirmation_timestamp !== null);
    } else {
      // Populating properties for a new application
      $user = Auth::user(); // Get the authenticated user

      // Safely access employee details using null-safe operator
      $this->fullName = $user?->name ?? ''; // Assuming user name is the full name
      // *** FIX: Use null-safe operators for safer access to employee and related properties ***
      $this->positionGrade = ($user?->employee?->position?->name ?? '') . ' ' . ($user?->employee?->grade?->name ?? ''); // Combine position and grade safely, handle nulls
      $this->departmentUnit = ($user?->employee?->department?->name ?? '') . ' / ' . ($user?->employee?->unit?->name ?? ''); // Combine department and unit safely, handle nulls
      $this->phoneNumber = $user?->employee?->phone_number ?? '';

      // Default: applicant is responsible
      $this->isApplicantResponsible = true;
    }
  }

  /**
   * Validation rules for the form.
   * Use conditional rules based on isApplicantResponsible.
   */
  protected function rules(): array
  {
    return [
      'purpose' => 'required|string|max:255',
      'location' => 'required|string|max:255',
      'loanStartDate' => 'required|date',
      'loanEndDate' => 'required|date|after_or_equal:loanStartDate',
      'isApplicantResponsible' => 'boolean',
      'responsibleOfficerName' => Rule::requiredIf(!$this->isApplicantResponsible) . '|nullable|string|max:255',
      'responsibleOfficerPositionGrade' => Rule::requiredIf(!$this->isApplicantResponsible) . '|nullable|string|max:255',
      'responsibleOfficerPhoneNumber' => Rule::requiredIf(!$this->isApplicantResponsible) . '|nullable|string|max:20', // Adjust max length as needed
      'loanItems' => 'required|array|min:1', // Must have at least one item
      'loanItems.*.equipmentType' => 'required|string|max:255',
      'loanItems.*.quantityRequested' => 'required|integer|min:1',
      'loanItems.*.notes' => 'nullable|string|max:500',
      'applicantConfirmation' => 'accepted', // Requires the checkbox to be ticked
    ];
  }

  /**
   * Custom validation messages.
   */
  protected function messages(): array
  {
    return [
      'purpose.required' => 'Tujuan Permohonan wajib diisi.',
      'location.required' => 'Lokasi wajib diisi.',
      'loanStartDate.required' => 'Tarikh Pinjaman wajib diisi.',
      'loanEndDate.required' => 'Tarikh Dijangka Pulang wajib diisi.',
      'loanEndDate.after_or_equal' => 'Tarikh Dijangka Pulang mestilah pada atau selepas Tarikh Pinjaman.',
      'responsibleOfficerName.required_if' => 'Nama Penuh Pegawai Bertanggungjawab wajib diisi jika Pemohon bukan Pegawai Bertanggungjawab.',
      'responsibleOfficerPositionGrade.required_if' => 'Jawatan & Gred Pegawai Bertanggungjawab wajib diisi jika Pemohon bukan Pegawai Bertanggungjawab.',
      'responsibleOfficerPhoneNumber.required_if' => 'No. Telefon Pegawai Bertanggungjawab wajib diisi jika Pemohon bukan Pegawai Bertanggungjawab.',
      'loanItems.required' => 'Sekurang-kurangnya satu Peralatan wajib ditambah.',
      'loanItems.min' => 'Sekurang-kurangnya satu Peralatan wajib ditambah.',
      'loanItems.*.equipmentType.required' => 'Jenis Peralatan untuk setiap item wajib diisi.',
      'loanItems.*.quantityRequested.required' => 'Kuantiti untuk setiap item wajib diisi.',
      'loanItems.*.quantityRequested.integer' => 'Kuantiti mestilah nombor bulat.',
      'loanItems.*.quantityRequested.min' => 'Kuantiti mestilah sekurang-kurangnya 1.',
      'applicantConfirmation.accepted' => 'Pengesahan Pemohon wajib ditanda.',
    ];
  }


  /**
   * Add a new empty item row to the loan items list.
   */
  public function addLoanItem()
  {
    $this->loanItems[] = [
      'equipmentType' => '',
      'quantityRequested' => 1,
      'notes' => '',
    ];
  }

  /**
   * Remove an item row from the loan items list by index.
   * @param int $index The index of the item to remove.
   */
  public function removeLoanItem(int $index)
  {
    // Prevent removing the last item
    if (count($this->loanItems) > 1) {
      unset($this->loanItems[$index]);
      // Re-index the array to prevent Livewire issues with keys
      $this->loanItems = array_values($this->loanItems);
    } else {
      session()->flash('error', 'Sekurang-kurangnya satu item peralatan diperlukan.'); // Malay message
    }
  }


  /**
   * Handle the form submission (Create or Update).
   * Creates a new loan application record and its associated items via the service.
   *
   * @return void|\Illuminate\Http\RedirectResponse Returns a RedirectResponse on success, otherwise null.
   */
  public function submitApplication() // <-- Removed ": RedirectResponse" return type hint here
  {
    // Authorize the action (e.g., create loan application policy)
    // $this->authorize('create', LoanApplication::class); // Assuming a policy exists

    // Validate the form data
    try {
      $this->validate();
      Log::debug('Loan application form validated successfully.');
    } catch (ValidationException $e) {
      Log::warning('Loan application form validation failed in component submit method.', ['user_id' => Auth::id(), 'errors' => $e->errors()]);
      session()->flash('error', 'Sila semak semula borang permohonan. Terdapat ralat.'); // Malay message
      // Re-throw the exception so Livewire handles displaying errors in the view
      throw $e;
    }


    // Prepare data for the service
    // $this->all() gets all public properties, but we need to structure it
    // according to what the service's createApplication method expects.
    $dataForApplication = [
      'purpose' => $this->purpose,
      'location' => $this->location,
      'loan_start_date' => $this->loanStartDate,
      'loan_end_date' => $this->loanEndDate,
      // Responsible officer ID - derive from name/position or search in User model
      // This requires finding the responsible officer in the database if not the applicant
      'responsible_officer_id' => $this->isApplicantResponsible ? Auth::id() : null, // Default to applicant ID if applicant is responsible
      'applicant_confirmation_timestamp' => $this->applicantConfirmation ? now() : null, // Record timestamp if confirmed
      // The service will set the initial status to PENDING_SUPPORT
    ];

    // Find the responsible officer user if different from applicant
    // This is a placeholder and needs robust implementation if you need to link to a specific User/Employee
    // If linking by Employee ID:
    // $responsibleOfficerEmployee = Employee::where('name', $this->responsibleOfficerName)->first();
    // if ($responsibleOfficerEmployee && $responsibleOfficerEmployee->user_id) {
    //      $dataForApplication['responsible_officer_id'] = $responsibleOfficerEmployee->user_id; // Link to their user ID
    // } else {
    //     // Handle case where responsible officer employee is not found or has no linked user
    //     Log::warning('Responsible officer not found by name or has no linked user.', ['responsible_officer_name' => $this->responsibleOfficerName, 'user_id' => Auth::id()]);
    //     // The responsible_officer_id will remain null as initialized if not found.
    //     // You might want to add validation or feedback to the user if the responsible officer is required but not found.
    // }
    // Or if linking directly by User name (less reliable):
    // $responsibleOfficerUser = User::where('name', $this->responsibleOfficerName)->first();
    // if ($responsibleOfficerUser) {
    //     $dataForApplication['responsible_officer_id'] = $responsibleOfficerUser->id;
    // } else {
    //      Log::warning('Responsible officer User model not found by name.', ['responsible_officer_name' => $this->responsibleOfficerName, 'user_id' => Auth::id()]);
    // }
    if (!$this->isApplicantResponsible && $this->responsibleOfficerName) {
      Log::warning('Logic to find responsible officer by name is not yet implemented. responsible_officer_id may be null.', ['responsible_officer_name' => $this->responsibleOfficerName, 'user_id' => Auth::id()]);
    }


    try {
      Log::debug('Calling service to create loan application.', ['user_id' => Auth::id(), 'items_count' => count($this->loanItems)]);

      // Pass $this->loanItems as the third argument to the service method
      $loanApplication = $this->loanApplicationService->createApplication(
        $dataForApplication, // Arg 1: Data for the LoanApplication model
        Auth::user(), // Arg 2: The applicant User model
        $this->loanItems // Arg 3: The array of loan items data
      );

      Log::info('Loan application created successfully by service.', ['application_id' => $loanApplication->id, 'user_id' => Auth::id()]);

      // Redirect to the show page of the newly created application
      // Changed route name to 'my-applications.loan.show' based on updated web.php
      session()->flash('success', 'Permohonan pinjaman peralatan ICT berjaya dihantar!'); // Malay success message
      // Use the global redirect() helper which returns a RedirectResponse
      return redirect()->route('my-applications.loan.show', $loanApplication);
    } catch (ValidationException $e) {
      // Livewire's default error handling will display validation errors
      Log::warning('Loan application submission validation failed in component submit method.', ['user_id' => Auth::id(), 'errors' => $e->errors()]);
      session()->flash('error', 'Sila semak semula borang permohonan. Terdapat ralat.'); // Malay message
      // Re-throw the exception so Livewire handles displaying errors in the view
      throw $e; // Keep throwing the validation exception
    } catch (Exception $e) {
      // Handle other exceptions from the service or database
      Log::error('Error submitting loan application via service.', ['user_id' => Auth::id(), 'error' => $e->getMessage(), 'exception' => $e]);
      session()->flash('error', 'Gagal menghantar permohonan pinjaman peralatan disebabkan ralat: ' . $e->getMessage()); // Malay message
      // In Livewire, you typically update the component's state to show an error message
      // and prevent redirect on error. Returning null is appropriate here.
      return null; // <-- Returns null on generic failure
    }
  }


  /**
   * Render the component's view.
   */
  public function render(): View // Added return type hint
  {
    // This assumes the view file is located at:
    // resources/views/livewire/loan-request-form/loan-request-form.blade.php
    // or resources/views/livewire/loan-request-form.blade.php depending on naming conventions
    return view('livewire.loan-request-form'); // Ensure this matches your view file name
  }


  // Add other methods as needed (e.g., updateApplication for editing)
}
