<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Department; // Assumed to exist with users() relationship
use App\Models\Timeline; // Not directly used in this component's logic as written
use Livewire\Attributes\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Database\Eloquent\Builder; // For type hinting query builders
use Illuminate\Support\Facades\DB; // For database transactions
use Illuminate\Support\Facades\Log; // For logging errors
use Exception; // Import the Exception class
use Illuminate\Validation\Rule as ValidationRule; // Alias Rule for validation rules
use Illuminate\Support\Facades\Auth; // Import Auth Facade


// Assumptions:
// 1. Department model exists and has 'name', 'branch_type', 'code', 'description' attributes.
// 2. Department model has a 'users()' relationship to count members via withCount.
// 3. Department model uses SoftDeletes if deletion implies soft deletion.
// 4. Livewire v3+ is used for attributes.
// 5. The view uses Bootstrap pagination styling.
// 6. The department modal is controlled via a public $showModal property bound with Alpine's @entangle
//    and shows/hides using x-show.
// 7. The department modal might need JS plugin initialization triggered by a 'departmentModalShown' event.

class Departments extends Component
{
  use WithPagination;

  // Pagination specific setting (assuming Bootstrap styling)
  protected $paginationTheme = 'bootstrap';

  // 👉 State Variables (Public properties that sync with the view)

  // Property for the search input field
  #[Rule('nullable|string|max:255')]
  public string $search = '';

  // Form fields for the add/edit modal
  #[Rule('required|string|max:255')] // Base validation for name
  public string $name = '';

  #[Rule('required|in:state,headquarters')] // Branch type validation
  public string $branch_type = '';

  #[Rule('nullable|string|max:10')] // Code validation
  public string $code = '';

  #[Rule('nullable|string|max:500')] // Description validation
  public string $description = '';

  // State: Department model instance being edited
  public ?Department $department = null;

  // State: Flag indicating if the modal is in edit mode
  public bool $isEdit = false;

  // State: ID of the department pending deletion confirmation
  public ?int $confirmedId = null;

  // State: Controls the visibility of the main department modal (for Alpine/JS binding)
  public bool $showModal = false; // Added for modal visibility control


  // 👉 Computed Properties (Livewire v3+ - Cached data that depends on state)

  /**
   * Computed property to fetch and paginate departments.
   * Includes user count and filters based on search term.
   *
   * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
   */
  #[Computed]
  public function departments(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
  {
    // Start with the query builder
    $query = Department::query()
      // Eager load the count of related users (assuming Department has a 'users()' relationship)
      ->withCount('users')
      // Apply search filter if search term is present
      ->when($this->search, function (Builder $query, string $term) {
        $query->where('name', 'like', '%' . $term . '%')
          ->orWhere('code', 'like', '%' . $term . '%')
          ->orWhere('description', 'like', '%' . $term . '%');
      })
      // Order departments alphabetically by name
      ->orderBy('name');

    // Return paginated results (uses default perPage from WithPagination or component property if set)
    return $query->paginate(10); // Adjust pagination size as needed or use $this->perPage if added as a property
  }


  // 👉 Lifecycle Hooks

  /**
   * Hook called when the 'search' property is updated.
   * Resets pagination to the first page to avoid empty pages.
   */
  public function updatedSearch(): void
  {
    $this->resetPage(); // Reset pagination to the first page
  }

  /**
   * Hook called when the 'showModal' property is updated.
   * Resets validation errors when the modal is hidden.
   */
  public function updatedShowModal(bool $value): void
  {
    if (!$value) { // If modal is being closed
      $this->resetValidation(); // Clear validation errors when modal hides
      // Optional: Reset form fields when modal is closed manually (alternative to resetForm)
      // $this->reset(['name', 'branch_type', 'code', 'description', 'department', 'isEdit']);
    }
  }


  // Render method simply returns the view
  /**
   * Render the component view.
   */
  public function render() //: \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory // Optional return type hint
  {
    // The 'departments' computed property is automatically available to the view
    return view('livewire.human-resource.structure.departments');
  }


  // 👉 Department Management Actions

  /**
   * Handle form submission for creating or updating a department.
   * Uses a database transaction for atomicity.
   */
  public function submitDepartment()
  {
    // Validation rules, including unique checks ignoring the current record if editing
    $rules = [
      'name' => [
        'required',
        'string',
        'max:255',
        ValidationRule::unique('departments', 'name')->ignore($this->isEdit ? $this->department?->id : null)
      ],
      'code' => [
        'nullable',
        'string',
        'max:10',
        ValidationRule::unique('departments', 'code')->ignore($this->isEdit ? $this->department?->id : null)
      ],
      'branch_type' => 'required|in:state,headquarters',
      'description' => 'nullable|string|max:500',
    ];

    // Perform validation
    $this->validate($rules);

    // Use a database transaction for atomicity
    DB::beginTransaction(); // Use imported DB facade method

    try {
      if ($this->isEdit) {
        $this->editDepartment();
      } else {
        $this->addDepartment();
      }

      DB::commit(); // Commit the transaction on success

      // Success feedback and dispatch events after successful transaction
      session()->flash('success', $this->isEdit ? __('Department Updated Successfully!') : __('Department Added Successfully!'));
      $this->dispatch('closeModal', elementId: '#departmentModal'); // Assuming JS event to close modal
      $this->dispatch('toastr', type: 'success', message: __('Operation Successful!')); // Adjusted toastr message

      // The departments list in the view should automatically update due to computed property and state changes
      // No manual refresh needed.

    } catch (Exception $e) { // Use imported Exception
      DB::rollBack(); // Rollback the transaction on exception

      // Log the exception for debugging
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
      session()->flash('error', __('An error occurred while saving the department. Please try again.')); // Generic user message
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!')); // Toastr error notification

      // Keep modal open or close? Original closed. Let's explicitly close modal on error.
      $this->dispatch('closeModal', elementId: '#departmentModal');
      // Or dispatch an error modal specific event if needed
      // $this->dispatch('showErrorModal', message: $e->getMessage()); // Example

    } finally {
      // Reset modal state and clear form fields regardless of success or failure
      // Use specific resets
      $this->reset('isEdit', 'department', 'name', 'branch_type', 'code', 'description');
      // Also reset confirmedId if needed, though usually only set for delete confirmation
      $this->confirmedId = null;
    }
  }

  /**
   * Add a new department record.
   * Called internally from submitDepartment within a transaction.
   */
  protected function addDepartment(): void
  {
    // Validation is handled in submitDepartment()

    Department::create([
      'name' => $this->name,
      'branch_type' => $this->branch_type,
      'code' => $this->code ?: null, // Store empty string as null
      'description' => $this->description ?: null, // Store empty string as null
      // created_by/updated_by might be handled by a trait or observer
      // 'created_by' => Auth::id(), // Example if manual assignment needed
    ]);

    // Success feedback and dispatching handled in submitDepartment() after transaction
  }

  /**
   * Show modal for editing an existing department.
   * Uses route model binding to inject the Department model.
   *
   * @param Department $department The Department model instance to edit.
   */
  public function showEditDepartmentModal(Department $department): void
  {
    // Reset state and validation before populating for edit
    $this->reset(['isEdit', 'department', 'name', 'branch_type', 'code', 'description', 'confirmedId']);
    $this->resetValidation();

    $this->isEdit = true; // Set edit mode
    $this->department = $department; // Store the model instance

    // Populate the form fields from the record's data
    $this->name = $department->name;
    $this->branch_type = $department->branch_type;
    $this->code = $department->code ?? ''; // Use empty string for null DB value in form
    $this->description = $department->description ?? ''; // Use empty string for null DB value in form

    // Set showModal to true to open the modal via Alpine.js binding
    $this->showModal = true;
    // Dispatch JS event to signal modal is shown and ready for component initialization (e.g., Select2, Flatpickr)
    $this->dispatch('departmentModalShown'); // Custom event for JS initialization
  }

  /**
   * Update an existing department record.
   * Called internally from submitDepartment within a transaction.
   */
  protected function editDepartment(): void
  {
    // Validation is handled in submitDepartment()

    // Ensure the department model instance is loaded
    if (!$this->department) {
      // Log error and throw exception if model is not set (should not happen in normal flow)
      Log::error('Departments component: Attempted to update with no department model loaded.', ['user_id' => Auth::id(), 'is_edit' => $this->isEdit]);
      throw new Exception(__('Department record not found for update.')); // Exception triggers transaction rollback
    }

    // Update the model attributes
    $this->department->update([
      'name' => $this->name,
      'branch_type' => $this->branch_type,
      'code' => $this->code ?: null, // Store empty string as null
      'description' => $this->description ?: null, // Store empty string as null
      // updated_by might be handled by a trait or observer
      // 'updated_by' => Auth::id(), // Example if manual assignment needed
    ]);

    // Success feedback and dispatching handled in submitDepartment() after transaction
    // Reset modal state handled in submitDepartment() finally block
  }

  /**
   * Set the ID of the department to be confirmed for deletion.
   * Called from the view (e.g., delete button in table).
   *
   * @param int $id The ID of the department to confirm deletion for.
   */
  public function confirmDeleteDepartment(int $id): void
  {
    $this->confirmedId = $id; // Store the ID for confirmation
    // Dispatch event to show confirmation modal/UI if using one
    // $this->dispatch('openConfirmModal', elementId: '#deleteConfirmationModal');
  }

  /**
   * Perform the deletion of the confirmed department.
   * Called from the confirmation button ("Sure?") in the view.
   */
  public function deleteDepartment(): void
  {
    // Check if a department ID is confirmed for deletion
    if ($this->confirmedId === null) {
      // No confirmed ID, do nothing or log an error
      Log::warning('Departments component: deleteDepartment called with no confirmed ID.', ['user_id' => Auth::id()]);
      $this->dispatch('toastr', type: 'warning', message: __('No department selected for deletion.'));
      return;
    }

    // Find the department record using the confirmed ID
    $record = Department::find($this->confirmedId);

    // Check if the record exists
    if (!$record) {
      // Record not found (might have been deleted already)
      session()->flash('error', __('Department record not found for deletion.'));
      $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
    } else {
      // Record found, attempt deletion
      // Consider wrapping delete in a transaction if needed, but Eloquent's delete is often sufficient
      try {
        // Before deleting, consider business rules:
        // - Prevent deletion if there are associated records (users, timelines)?
        // - Or cascade delete? (DB foreign keys or Eloquent relationships)
        // Assuming SoftDeletes trait is used, this performs a soft delete.

        $record->delete(); // Perform the deletion (soft or hard based on model traits)

        session()->flash('success', __('Department Deleted Successfully!'));
        $this->dispatch('toastr', type: 'success', message: __('Operation Successful!'));
      } catch (Exception $e) { // Catch any exceptions during deletion
        // Log the error
        Log::error('Departments component: Failed to delete department record.', ['user_id' => Auth::id(), 'department_id' => $this->confirmedId, 'exception' => $e]);
        // Flash and dispatch error feedback
        session()->flash('error', __('An error occurred while deleting the department. Please check for associated records.')); // User message
        $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
      }
    }

    // Reset confirmedId after the deletion attempt (success or failure)
    $this->confirmedId = null;
    // Dispatch event to close confirmation modal (if using one)
    // $this->dispatch('closeConfirmModal', elementId: '#deleteConfirmationModal');

    // Livewire will re-render and the list will update
  }

  /**
   * Show modal for adding a new department.
   * Called from the "Add New Department" button in the view.
   */
  public function showNewDepartmentModal(): void
  {
    // Reset all relevant properties to clear the form for a new entry
    $this->reset(['isEdit', 'department', 'name', 'branch_type', 'code', 'description', 'confirmedId']);
    // Clear any previous validation errors
    $this->resetValidation();

    $this->isEdit = false; // Explicitly set mode to add new

    // Set showModal to true to open the modal via Alpine.js binding
    $this->showModal = true;
    // Dispatch JS event to signal modal is shown and ready for component initialization (e.g., Select2, Flatpickr)
    $this->dispatch('departmentModalShown'); // Custom event for JS initialization
  }

  /**
   * Reset the form fields and state.
   * Can be called from a modal close or cancel button if needed.
   */
  public function resetForm(): void
  {
    $this->reset(['isEdit', 'department', 'name', 'branch_type', 'code', 'description', 'confirmedId']);
    $this->resetValidation();
    // Optionally set showModal to false here if needed, but Alpine binding often handles this
    // $this->showModal = false;
  }


  // These methods are no longer needed with the refactored approach using withCount and computed property
  // public function getCoordinator($id) { }
  // public function getMembersCount($department_id) { }
}
