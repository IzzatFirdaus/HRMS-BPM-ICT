<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Department; // Assumed to exist with users() relationship
use App\Models\Timeline; // Used in original getMembersCount, now less needed directly
use Livewire\Attributes\Rule; // For Livewire v3+ property validation
use Livewire\Attributes\Computed; // For Livewire v3+ computed properties
use Livewire\Component; // Base Livewire component
use Livewire\WithPagination; // Trait for pagination
use Illuminate\Database\Eloquent\Builder; // For type hinting query builders
use Illuminate\Support\Facades\DB; // For database transactions
use Illuminate\Support\Facades\Log; // For logging errors
use Exception; // Import the Exception class
use Illuminate\Validation\Rule as ValidationRule; // Alias Rule for validation rules
use Illuminate\Support\Facades\Auth; // Import Auth Facade


// Assumptions:
// 1. Department model exists and has 'name' attribute and a 'users()' relationship.
// 2. Department model uses SoftDeletes if deletion implies soft deletion.
// 3. Livewire v3+ is used for attributes.
// 4. The view uses Bootstrap pagination styling.
// 5. Modals are controlled via JS dispatch events like 'closeModal', 'toastr'.
// 6. The Department model has 'id', 'name', 'branch_type', 'code', 'description' (based on previous turns).

class Departments extends Component
{
  use WithPagination;

  // Pagination specific setting (assuming Bootstrap styling)
  protected $paginationTheme = 'bootstrap';

  // 👉 State Variables (Public properties that sync with the view)

  #[Rule('nullable|string|max:255')] // Add max length and nullable for search
  public string $search = ''; // Search input for department list

  #[Rule('required|string|max:255')] // Base validation for name
  public string $name = ''; // Form field for department name

  // Form fields for add/edit modal (based on DepartmentController validation from earlier)
  // Rules moved to submitDepartment for conditional ignore logic
  public string $branch_type = '';
  public string $code = '';
  public string $description = '';


  public ?Department $department = null; // State: Department model being edited

  public bool $isEdit = false; // State: Is the modal in edit mode

  public ?int $confirmedId = null; // State: ID of the department pending deletion confirmation


  // 👉 Computed Properties (Livewire v3+ - Cached data that depends on state)

  #[Computed]
  public function departments(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
  {
    // Fetch and filter departments based on search term
    // Eager load the count of related users ('members') using withCount
    $query = Department::query()
      ->withCount('users') // Count the number of related users (members)
      ->when($this->search, function (Builder $query, string $term) {
        $query->where('name', 'like', '%' . $term . '%')
          ->orWhere('code', 'like', '%' . $term . '%') // Also search by code
          ->orWhere('description', 'like', '%' . $term . '%'); // Also search by description
      })
      ->orderBy('name'); // Order departments alphabetically

    // Return paginated results
    return $query->paginate(10); // Adjust pagination size as needed
  }


  // 👉 Lifecycle Hooks

  // Hook to reset pagination when the search term changes
  public function updatedSearch(): void
  {
    $this->resetPage(); // Reset pagination to the first page
  }


  // Render method simply returns the view
  public function render()
  {
    // Computed property $this->departments is automatically available to the view
    return view('livewire.human-resource.structure.departments');
  }


  // 👉 Department Management Actions

  // Submit department creation or update form
  public function submitDepartment()
  {
    // Add validation for uniqueness of name and code, ignoring current record if editing
    $this->validate([
      'name' => [
        'required',
        'string',
        'max:255',
        ValidationRule::unique('departments', 'name')->ignore($this->isEdit ? $this->department?->id : null) // Use aliased Rule, use null-safe operator for ->id
      ],
      'code' => [
        'nullable',
        'string',
        'max:10',
        ValidationRule::unique('departments', 'code')->ignore($this->isEdit ? $this->department?->id : null) // Use aliased Rule, use null-safe operator for ->id
      ],
      'branch_type' => 'required|in:state,headquarters', // Re-validate branch_type here
      'description' => 'nullable|string|max:500', // Re-validate description here
    ]);

    // Use a database transaction for atomicity (all or nothing)
    try {
      DB::transaction(function () { // Use imported DB
        if ($this->isEdit) {
          $this->editDepartment();
        } else {
          $this->addDepartment();
        }
      });

      // Success feedback and dispatch events after successful transaction
      session()->flash('success', $this->isEdit ? __('Department Updated Successfully!') : __('Department Added Successfully!'));
      $this->dispatch('closeModal', elementId: '#departmentModal'); // Assuming JS event to close modal
      $this->dispatch('toastr', type: 'success', message: __('Going Well!')); // Assuming JS event for toastr

      // Refresh the departments list by invalidating the computed property cache
      // This happens automatically when state changes (like new record created or existing updated)
      // No need to force re-fetch here usually, Livewire handles it.
      // Unless the update/create doesn't change the state properties that the computed property depends on, which is not the case here.

    } catch (Exception $e) { // Use imported Exception
      // Log the exception
      Log::error('Department submit failed: ' . $e->getMessage(), [ // Use imported Log
        'user_id' => Auth::id(), // Use imported Auth
        'department_data' => [
          'name' => $this->name,
          'branch_type' => $this->branch_type,
          'code' => $this->code,
          'description' => $this->description
        ],
        'is_edit' => $this->isEdit,
        'department_id' => $this->department?->id, // Use null-safe operator
        'exception' => $e,
      ]);

      // Flash and dispatch error feedback
      session()->flash('error', __('An error occurred while saving the department.') . ' ' . $e->getMessage()); // Show exception message for debugging, or a generic message
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!')); // Assuming JS event for toastr
      // Keep modal open on error? Original closed. Let's keep original behavior.
      $this->dispatch('closeModal', elementId: '#departmentModal');
    } finally {
      // Reset modal state and clear form fields regardless of success or failure
      // Use specific resets instead of reset()
      $this->reset('isEdit', 'department', 'name', 'branch_type', 'code', 'description');
    }
  }

  // Add a new department record
  protected function addDepartment(): void // Use protected as called internally, void return type
  {
    // Validation is handled in submitDepartment()

    Department::create([
      'name' => $this->name,
      'branch_type' => $this->branch_type,
      'code' => $this->code ?: null, // Store empty string as null
      'description' => $this->description ?: null, // Store empty string as null
      // created_by/updated_by might be handled by a trait or observer
      // 'created_by' => Auth::id(), // Use imported Auth
    ]);

    // Success feedback and dispatching is handled in submitDepartment() after transaction
  }

  // Show modal for editing an existing department
  public function showEditDepartmentModal(Department $department): void // Use route model binding, void return type
  {
    $this->reset('isEdit', 'department', 'name', 'branch_type', 'code', 'description'); // Reset modal state
    $this->resetValidation(); // Clear validation errors
    $this->isEdit = true; // Set edit mode

    $this->department = $department; // Store the model for update

    // Populate the form fields with the record's data
    $this->name = $department->name;
    $this->branch_type = $department->branch_type;
    $this->code = $department->code ?? ''; // Use empty string for null
    $this->description = $department->description ?? ''; // Use empty string for null

    // Dispatch JS event to open modal if not handled by wire:click
    // $this->dispatch('openModal', elementId: '#departmentModal');
  }

  // Update an existing department record
  protected function editDepartment(): void // Use protected as called internally, void return type
  {
    // Validation is handled in submitDepartment()

    // Find the record again within the transaction for safety (optional if $this->department is reliable)
    // Or trust $this->department as it was loaded in showEditDepartmentModal
    if (!$this->department) {
      // Should not happen if flow is correct, but defensive check
      Log::error('Departments component: Attempted to update non-existent department model.', ['user_id' => Auth::id(), 'is_edit' => $this->isEdit]); // Use imported Log
      throw new Exception(__('Department record not found for update.')); // Throw exception to trigger transaction rollback, Use imported Exception
    }

    $this->department->update([
      'name' => $this->name,
      'branch_type' => $this->branch_type,
      'code' => $this->code ?: null, // Store empty string as null
      'description' => $this->description ?: null, // Store empty string as null
      // updated_by might be handled by a trait or observer
      // 'updated_by' => Auth::id(), // Use imported Auth
    ]);

    // Success feedback and dispatching is handled in submitDepartment() after transaction
    // Reset modal state is handled in submitDepartment()
  }

  // Confirm deletion of a department
  public function confirmDeleteDepartment(int $id): void // Accept ID, void return type
  {
    $this->confirmedId = $id; // Store the ID for confirmation
    // Dispatch event to show confirmation modal (e.g., SweetAlert, Bootstrap modal)
    // $this->dispatch('openConfirmModal', elementId: '#deleteConfirmationModal');
  }

  // Perform the deletion after confirmation
  // Refactored to use confirmedId for consistency
  public function deleteDepartment(): void // Void return type
  {
    // Check if an ID is confirmed
    if ($this->confirmedId === null) {
      return; // No ID to delete
    }

    // Find the record to delete
    $record = Department::find($this->confirmedId);

    if (!$record) {
      // Record not found (maybe already deleted)
      session()->flash('error', __('Department record not found for deletion.'));
      $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
    } else {
      // Perform the deletion
      try {
        // Consider adding checks for related records (e.g., users, employees, timelines)
        // if deletion should be prevented or cascaded based on business rules.
        // The SoftDeletes trait on the model handles soft deletion if configured.

        $record->delete(); // Use model delete method

        session()->flash('success', __('Department Deleted Successfully!'));
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
      } catch (Exception $e) { // Use imported Exception
        // Handle potential database errors during deletion (e.g., foreign key constraints if not cascaded)
        Log::error('Departments component: Failed to delete department record.', ['user_id' => Auth::id(), 'department_id' => $this->confirmedId, 'exception' => $e]); // Use imported Log
        session()->flash('error', __('An error occurred while deleting the department.') . ' ' . $e->getMessage()); // Show exception message or generic error
        $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
      }
    }

    // Reset confirmedId after attempting deletion
    $this->confirmedId = null;
    // Dispatch event to close confirmation modal (if using one)
    // $this->dispatch('closeConfirmModal', elementId: '#deleteConfirmationModal');
  }


  // Show modal for adding a new department
  public function showNewDepartmentModal(): void // Void return type
  {
    // Use specific resets instead of reset()
    $this->reset('isEdit', 'department', 'name', 'branch_type', 'code', 'description'); // Reset modal state
    $this->resetValidation(); // Clear validation errors
    $this->isEdit = false; // Explicitly set mode
    // Dispatch JS event to open modal if not handled by wire:click
    // $this->dispatch('openModal', elementId: '#departmentModal');
  }

  // These methods are no longer needed with the refactored approach
  // public function getCoordinator($id) { }
  // public function getMembersCount($department_id) { }
}
