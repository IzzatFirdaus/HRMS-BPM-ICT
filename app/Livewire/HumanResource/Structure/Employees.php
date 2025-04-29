<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Contract; // Assumed to exist
use App\Models\Employee; // Assumed to exist and SoftDeletes if applicable
use Livewire\Attributes\Rule; // For Livewire v3+ property validation
use Livewire\Attributes\Computed; // For Livewire v3+ computed properties
use Livewire\Component; // Base Livewire component
use Livewire\WithPagination; // Trait for pagination
use Illuminate\Database\Eloquent\Builder; // For type hinting query builders
use Illuminate\Support\Facades\DB; // For database transactions
use Illuminate\Support\Facades\Log; // For logging errors
use Exception; // For general exception handling
use Illuminate\Validation\Rule as ValidationRule; // Alias Rule for validation rules
use Illuminate\Support\Facades\Auth; // Assuming created_by/updated_by are User IDs


// Assumptions:
// 1. Employee model exists and has columns: 'id', 'contract_id', 'first_name', 'father_name', 'last_name',
//    'mother_name', 'birth_and_place', 'national_number', 'mobile_number', 'degree', 'gender', 'address', 'notes', 'is_active'.
// 2. Employee model uses SoftDeletes if deletion implies soft deletion.
// 3. Employee model has a 'contract' belongsTo relationship.
// 4. Contract model exists and has 'id' and other attributes.
// 5. Livewire v3+ is used for attributes.
// 6. The view uses Bootstrap pagination styling.
// 7. Modals are controlled via JS dispatch events like 'closeModal', 'toastr'.
// 8. Manual input/setting of employee 'id' is intended and requires uniqueness validation.

class Employees extends Component
{
  use WithPagination;

  // Pagination specific setting (assuming Bootstrap styling)
  protected $paginationTheme = 'bootstrap';

  // 👉 State Variables (Public properties that sync with the view)

  #[Rule('nullable|string|max:255')] // Add max length and nullable for search
  public string $searchTerm = ''; // Search input for employee list

  // Removed public $employees property - now a computed property

  public ?Employee $employee = null; // State: Employee model being edited

  // Form data for employee create/edit - use snake_case keys for consistency with DB columns
  public array $employeeInfo = [
    'id' => '', // Manual employee ID/code input
    'contract_id' => '', // contractId changed to contract_id
    'first_name' => '', // firstName changed to first_name
    'father_name' => '', // fatherName changed to father_name
    'last_name' => '', // lastName changed to last_name
    'mother_name' => '', // motherName changed to mother_name
    'birth_and_place' => '', // birthAndPlace changed to birth_and_place
    'national_number' => '', // nationalNumber changed to national_number
    'mobile_number' => '', // mobileNumber changed to mobile_number
    'degree' => '',
    'gender' => '',
    'address' => '',
    'notes' => '',
    // Add any other employee fields needed in the form, e.g., 'is_active'
    // 'is_active' => true, // Default to active on creation?
  ];

  public bool $isEdit = false; // State: Is the modal in edit mode

  public ?int $confirmedId = null; // State: ID of the employee pending deletion confirmation


  // 👉 Computed Properties (Livewire v3+ - Cached data that depends on state)

  #[Computed]
  public function contracts(): \Illuminate\Database\Eloquent\Collection // Use Collection type hint
  {
    // Cache all contracts for dropdown
    // Consider optimizing for large lists if needed
    return Contract::all();
  }

  #[Computed]
  public function employees(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
  {
    // Fetch and filter employees based on search term
    $query = Employee::query()
      ->when($this->searchTerm, function (Builder $query, string $term) {
        $query->where('first_name', 'like', '%' . $term . '%')
          ->orWhere('last_name', 'like', '%' . $term . '%')
          ->orWhere('father_name', 'like', '%' . $term . '%') // Added father name search
          ->orWhere('mother_name', 'like', '%' . $term . '%') // Added mother name search
          ->orWhere('national_number', 'like', '%' . $term . '%') // Added national number search
          ->orWhere('mobile_number', 'like', '%' . $term . '%') // Added mobile number search
          ->orWhere('id', 'like', '%' . $term . '%'); // Keep ID search with LIKE as per original
        // If ID is numeric and should be exact match:
        // ->orWhere('id', $term); // For exact ID match if term is numeric
      })
      ->orderBy('last_name') // Order by last name by default
      ->orderBy('first_name'); // Then by first name

    // Return paginated results
    return $query->paginate(20); // Adjust pagination size as needed
  }


  // 👉 Lifecycle Hooks

  // Hook to reset pagination when the search term changes
  public function updatedSearchTerm(): void
  {
    $this->resetPage(); // Reset pagination to the first page
  }

  // Note: Mount is only responsible for initial setup, contracts are fetched by Computed property
  // public function mount() {} // Original mount function is no longer needed if contracts are Computed


  // 👉 Render method
  // No data fetching needed here, it's handled by computed properties
  public function render()
  {
    // Access computed property $this->employees directly in the view
    // Access computed property $this->contracts directly in the view for modal dropdown
    return view('livewire.human-resource.structure.employees');
  }


  // 👉 Employee Management Actions

  // Submit employee creation or update form
  public function submitEmployee()
  {
    // Validation rules for employee form
    $rules = [
      // Add uniqueness rule for ID if it's manually set as primary key
      'employeeInfo.id' => [
        'required',
        'string', // Assuming ID can be string or number, validate as string
        'max:255', // Add max length
        ValidationRule::unique('employees', 'id')->ignore($this->isEdit ? $this->employee?->id : null) // Use aliased Rule, ignore current employee ID if editing
      ],
      'employeeInfo.contract_id' => 'required|exists:contracts,id', // Validate contract exists
      'employeeInfo.first_name' => 'required|string|max:255',
      'employeeInfo.father_name' => 'required|string|max:255',
      'employeeInfo.last_name' => 'required|string|max:255',
      'employeeInfo.mother_name' => 'required|string|max:255',
      'employeeInfo.birth_and_place' => 'required|string|max:255', // Assuming string format
      'employeeInfo.national_number' => [
        'required',
        'string', // Validate as string if it can contain non-digits, otherwise integer/numeric
        'min:11',
        'max:11',
        ValidationRule::unique('employees', 'national_number')->ignore($this->isEdit ? $this->employee?->id : null) // Add unique rule
      ],
      'employeeInfo.mobile_number' => [
        'required',
        'string', // Validate as string if it can contain leading zero or non-digits, otherwise numeric
        'min:9',
        'max:9', // Ensure exactly 9 digits (or characters)
        // Original regex /^[1-9][0-9]*$/ allows numbers > 9 digits and must start with 1-9.
        // A simpler regex for 9 digits might be /^\d{9}$/ or /^[0-9]{9}$/
        // If the requirement is "exactly 9 digits, not starting with 0": /^[1-9]\d{8}$/
        'regex:/^[1-9]\d{8}$/', // Example regex for 9 digits not starting with 0
        ValidationRule::unique('employees', 'mobile_number')->ignore($this->isEdit ? $this->employee?->id : null) // Add unique rule
      ],
      'employeeInfo.degree' => 'required|string|max:255', // Assuming string
      'employeeInfo.gender' => 'required|in:male,female,other', // Assuming specific gender values
      'employeeInfo.address' => 'required|string|max:500', // Assuming string
      'employeeInfo.notes' => 'nullable|string|max:500', // Notes are optional string max 500 chars
      // Add rules for any other employee fields in the form
    ];

    // Validate form data using defined rules
    $this->validate($rules);

    // Use a database transaction for atomicity
    try {
      DB::transaction(function () { // Use imported DB
        if ($this->isEdit) {
          $this->editEmployee();
        } else {
          $this->addEmployee();
        }
      });

      // Success feedback and dispatch events after successful transaction
      session()->flash('success', $this->isEdit ? __('Employee Updated Successfully!') : __('Employee Added Successfully!')); // Translated
      $this->dispatch('closeModal', elementId: '#employeeModal'); // Assuming JS event to close modal
      $this->dispatch('toastr', type: 'success', message: __('Going Well!')); // Assuming JS event for toastr

      // Refresh the employee list by invalidating the computed property cache
      // This happens automatically when underlying data changes.

    } catch (Exception $e) { // Use imported Exception
      // Log the exception
      Log::error('Employee submit failed: ' . $e->getMessage(), [ // Use imported Log
        'user_id' => Auth::id(), // Use imported Auth
        'employee_data' => $this->employeeInfo,
        'is_edit' => $this->isEdit,
        'employee_id' => $this->employee?->id, // Use null-safe operator
        'exception' => $e,
      ]);

      // Flash and dispatch error feedback
      session()->flash('error', __('An error occurred while saving the employee record.') . ' ' . $e->getMessage()); // Show exception message for debugging, or a generic message
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!')); // Assuming JS event for toastr
      // Keep modal open on error? Original closed. Let's keep original behavior.
      // If you want to keep the modal open on validation errors, remove the closeModal dispatch here.
      $this->dispatch('closeModal', elementId: '#employeeModal');
    } finally {
      // Reset modal state and clear form fields regardless of success or failure
      // Use specific resets instead of reset()
      $this->reset('isEdit', 'employee', 'employeeInfo');
      // If you have Select2 or other JS inputs in the modal, dispatch event to clear them
      // $this->dispatch('clearEmployeeModalInputs');
    }
  }

  // Show modal for creating a new employee
  public function showCreateEmployeeModal(): void // Void return type
  {
    $this->resetValidation(); // Clear validation errors
    $this->reset('isEdit', 'employee', 'employeeInfo'); // Reset modal state
    // Initialize form data with defaults if necessary
    $this->employeeInfo = [
      'id' => '',
      'contract_id' => '',
      'first_name' => '',
      'father_name' => '',
      'last_name' => '',
      'mother_name' => '',
      'birth_and_place' => '',
      'national_number' => '',
      'mobile_number' => '',
      'degree' => '',
      'gender' => '',
      'address' => '',
      'notes' => '',
      // Add other default values if needed
      // 'is_active' => true,
    ];
    $this->isEdit = false; // Explicitly set mode
    // If you have Select2 or other JS inputs in the modal, dispatch event to clear them
    // $this->dispatch('clearEmployeeModalInputs');
    // Dispatch JS event to open modal if not handled by wire:click
    // $this->dispatch('openModal', elementId: '#employeeModal');
  }

  // Store a new employee record (called from submitEmployee)
  protected function addEmployee() // Void return type
  {
    // Validation is handled in submitEmployee()

    // Use array merging to ensure all form fields are included in create
    $createdEmployee = Employee::create(array_merge($this->employeeInfo, [
      // Override any fields if needed, e.g., force is_active true on creation
      'is_active' => $this->employeeInfo['is_active'] ?? true, // Default to active, or add to form
      'notes' => $this->employeeInfo['notes'] ?: null, // Store empty string as null
      // created_by might be handled by a trait or observer
      // 'created_by' => Auth::id(), // Use imported Auth
    ]));


    // Original code redirects after adding. This breaks typical Livewire flow
    // of staying on the page and updating the list.
    // If redirecting is the *intended* behavior to go to a detail page, keep it.
    // If you want to stay on the list page and just update the list, remove the redirect
    // and session flash below.

    // Uncomment the following lines if redirecting to the info page is the intended flow after creation
    // session()->flash('openTimelineModal', true); // Flash to session for next page
    // return redirect()->route('structure-employees-info', ['id' => $createdEmployee->id]); // Redirect to detail page


    // If NOT redirecting, the success feedback and modal close are already handled
    // in the submitEmployee() finally block. No need to duplicate them here.
    // The list will refresh automatically via the computed property.

  }

  // Show modal for editing an existing employee
  public function showEditEmployeeModal(Employee $employee): void // Use route model binding, void return type
  {
    $this->resetValidation(); // Clear validation errors
    $this->reset('isEdit', 'employee', 'employeeInfo'); // Reset modal state

    $this->isEdit = true; // Set edit mode
    $this->employee = $employee; // Store the model for update

    // Populate the form fields with the record's data
    // Use nullish coalescing (?? '') for potentially null database fields
    $this->employeeInfo = [
      'id' => $employee->id, // Manual ID input
      'contract_id' => $employee->contract_id,
      'first_name' => $employee->first_name ?? '',
      'father_name' => $employee->father_name ?? '',
      'last_name' => $employee->last_name ?? '',
      'mother_name' => $employee->mother_name ?? '',
      'birth_and_place' => $employee->birth_and_place ?? '',
      'national_number' => $employee->national_number ?? '',
      'mobile_number' => $employee->mobile_number ?? '',
      'degree' => $employee->degree ?? '',
      'gender' => $employee->gender ?? '',
      'address' => $employee->address ?? '',
      'notes' => $employee->notes ?? '', // Use empty string for null
      // Populate any other employee fields, e.g., 'is_active'
      // 'is_active' => $employee->is_active ?? true,
    ];

    // If you have Select2 or other JS inputs in the modal, dispatch event to set them
    // $this->dispatch('setEmployeeModalInputs', data: $this->employeeInfo);

    // Dispatch JS event to open modal if not handled by wire:click
    // $this->dispatch('openModal', elementId: '#employeeModal');
  }

  // Update an existing employee record (called from submitEmployee)
  protected function editEmployee() // Void return type
  {
    // Validation is handled in submitEmployee()

    if (!$this->employee) {
      // Should not happen if flow is correct, but defensive check
      Log::error('Employees component: Attempted to update non-existent employee model.', ['user_id' => Auth::id(), 'is_edit' => $this->isEdit]);
      throw new Exception(__('Employee record not found for update.')); // Throw exception for transaction rollback
    }

    // Use array merging to ensure all form fields are included in update
    // Be cautious updating the 'id' if it's the primary key. This might fail or have side effects.
    // If 'id' is the primary key and shouldn't be manually updated, remove it from employeeInfo and update array.
    $this->employee->update(array_merge($this->employeeInfo, [
      // Override any fields if needed
      'notes' => $this->employeeInfo['notes'] ?: null, // Store empty string as null
      // updated_by might be handled by a trait or observer
      // 'updated_by' => Auth::id(), // Use imported Auth
    ]));

    // Success feedback and dispatching is handled in submitEmployee() after transaction
    // Reset modal state is handled in submitEmployee()
  }

  // 👉 Delete employee

  // Confirm deletion of an employee
  // Refactored to set confirmedId first
  public function confirmDeleteEmployee(int $id): void // Accept ID, void return type
  {
    $this->confirmedId = $id; // Store the ID for confirmation
    // Dispatch event to show confirmation modal (e.g., SweetAlert, Bootstrap modal)
    // $this->dispatch('openConfirmModal', elementId: '#deleteEmployeeConfirmationModal');
  }

  // Perform the deletion after confirmation
  // Refactored to use confirmedId for consistency
  public function deleteEmployee(): void // Void return type
  {
    // Check if an ID is confirmed
    if ($this->confirmedId === null) {
      return; // No ID to delete
    }

    // Find the record to delete
    $record = Employee::find($this->confirmedId);

    if (!$record) {
      // Record not found (maybe already deleted by another user)
      session()->flash('error', __('Employee record not found for deletion.')); // Translated
      $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
    } else {
      // Perform the deletion within a transaction
      try {
        DB::transaction(function () use ($record) { // Use transaction, pass $record
          // Consider adding logic here to handle related records (timelines, assets etc.)
          // before deleting the employee, if not handled by database CASCADE or model events.
          // Example: $record->timelines()->delete(); // If timelines should be deleted with employee

          $record->delete(); // Use model delete method (handles soft deletes if enabled)
        });

        session()->flash('success', __('Employee deleted successfully!')); // Translated
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
      } catch (Exception $e) { // Use imported Exception
        // Handle potential database errors during deletion (e.g., foreign key constraints)
        Log::error('Employees component: Failed to delete employee record.', ['user_id' => Auth::id(), 'employee_id' => $this->confirmedId, 'exception' => $e]); // Use imported Log
        session()->flash('error', __('An error occurred while deleting the employee.') . ' ' . $e->getMessage()); // Show exception message or generic error
        $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
      }
    }

    // Reset confirmedId after attempting deletion
    $this->confirmedId = null;
    // Dispatch event to close confirmation modal (if using one)
    // $this->dispatch('closeConfirmModal', elementId: '#deleteEmployeeConfirmationModal');

    // Livewire will automatically re-fetch the 'employees' computed property
    // because the underlying data in the database has changed.
  }
}
