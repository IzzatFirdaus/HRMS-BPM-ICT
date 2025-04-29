<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Position; // Assumed to exist and SoftDeletes if applicable
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
// 1. Position model exists and has 'name' and 'vacancies_count' attributes.
// 2. Position model uses SoftDeletes if deletion implies soft deletion.
// 3. Livewire v3+ is used for attributes.
// 4. The view uses Bootstrap pagination styling.
// 5. Modals are controlled via JS dispatch events like 'closeModal', 'toastr', 'showModal'.

class Positions extends Component
{
  use WithPagination;

  // Pagination specific setting (assuming Bootstrap styling)
  protected $paginationTheme = 'bootstrap';

  // 👉 State Variables (Public properties that sync with the view)

  #[Rule('nullable|string|max:255')] // Add max length and nullable for search
  public string $search = ''; // Search input for positions list

  // Properties for the form fields - Validation rules moved to rules() method
  public string $name = '';
  public ?int $vacanciesCount = null; // Use nullable int, default to null


  // Property to hold the Position model instance when editing
  public ?Position $position = null; // Use nullable type hint

  // Flag to indicate if the modal is in edit mode
  public bool $isEdit = false;

  // Property to hold the ID of the position being confirmed for deletion
  public ?int $confirmedId = null; // Use nullable int


  // 👉 Computed Properties (Livewire v3+ - Cached data that depends on state)

  #[Computed]
  public function positions(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
  {
    // Fetch and filter positions based on search term
    $query = Position::query()
      ->when($this->search, function (Builder $query, string $term) {
        $query->where('name', 'like', '%' . $term . '%');
        // Optionally add searching by ID or other fields
        // ->orWhere('id', 'like', '%' . $term . '%');
      })
      ->orderBy('name'); // Order positions alphabetically

    // Return paginated results
    return $query->paginate(10); // Adjust pagination size as needed
  }


  // 👉 Lifecycle Hook

  // Hook to reset pagination when the search term changes
  public function updatedSearch(): void
  {
    $this->resetPage(); // Reset pagination to the first page
  }


  // 👉 Render method
  // No data fetching needed here, it's handled by computed properties
  public function render()
  {
    // Computed property $this->positions is automatically available to the view
    return view('livewire.human-resource.structure.positions');
  }


  // 👉 Position Management Actions

  // Validation rules for position form
  protected function rules()
  {
    // Define validation rules here
    return [
      'name' => [
        'required',
        'string',
        'max:255',
        // Add uniqueness rule, ignore current position if editing
        ValidationRule::unique('positions', 'name')->ignore($this->isEdit ? $this->position?->id : null) // Use aliased Rule, use null-safe operator for ->id
      ],
      'vacanciesCount' => 'required|integer|min:0',
      // Add rules for any other position fields
    ];
  }

  // Custom validation messages (optional)
  protected function messages()
  {
    return [
      'name.required' => __('Position name is required.'),
      'name.string' => __('Position name must be a string.'),
      'name.max' => __('Position name cannot exceed :max characters.'),
      'name.unique' => __('A position with this name already exists.'),
      'vacanciesCount.required' => __('Vacancies count is required.'),
      'vacanciesCount.integer' => __('Vacancies count must be an integer.'),
      'vacanciesCount.min' => __('Vacancies count cannot be less than :min.'),
      // Add messages for other rules/fields
    ];
  }


  // Method called by the form submission (wire:submit.prevent="submitPosition")
  public function submitPosition()
  {
    // Validate the form inputs using the rules() method
    $this->validate();

    // Use a database transaction for atomicity
    try {
      DB::transaction(function () { // Use imported DB
        if ($this->isEdit) {
          $this->editPosition();
        } else {
          $this->addPosition();
        }
      });

      // Success feedback and dispatch events after successful transaction
      session()->flash('success', $this->isEdit ? __('Position Updated Successfully!') : __('Position Added Successfully!')); // Translated
      $this->dispatch('closeModal', elementId: '#positionModal'); // Assuming JS event to close modal
      $this->dispatch('toastr', type: 'success', message: __('Going Well!')); // Assuming JS event for toastr

      // Refresh the positions list by invalidating the computed property cache
      // This happens automatically when underlying data changes.

    } catch (Exception $e) { // Use imported Exception
      // Log the exception
      Log::error('Position submit failed: ' . $e->getMessage(), [ // Use imported Log
        'user_id' => Auth::id(), // Use imported Auth
        'position_data' => [
          'name' => $this->name,
          'vacancies_count' => $this->vacanciesCount
        ],
        'is_edit' => $this->isEdit,
        'position_id' => $this->position?->id, // Use null-safe operator
        'exception' => $e,
      ]);

      // Flash and dispatch error feedback
      session()->flash('error', __('An error occurred while saving the position.') . ' ' . $e->getMessage()); // Show exception message for debugging, or a generic message
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!')); // Assuming JS event for toastr
      // Keep modal open on error? Original closed. Let's keep original behavior.
      // If you want to keep the modal open on validation errors, remove the closeModal dispatch here.
      $this->dispatch('closeModal', elementId: '#positionModal');
    } finally {
      // Reset modal state and clear form fields regardless of success or failure
      $this->resetForm(); // Use the helper method
    }
  }

  // Method to add a new position (called from submitPosition)
  protected function addPosition(): void // Use protected as called internally, void return type
  {
    // Validation is handled in submitPosition()

    Position::create([
      'name' => $this->name,
      'vacancies_count' => $this->vacanciesCount ?? 0, // Use nullish coalescing for nullable int default
      // created_by might be handled by a trait or observer
      // 'created_by' => Auth::id(), // Use imported Auth
    ]);

    // Success feedback and dispatching is handled in submitPosition() after transaction
    // Reset form is handled in submitPosition() finally block
  }

  // Method to update an existing position (called from submitPosition)
  protected function editPosition(): void // Use protected as called internally, void return type
  {
    // Validation is handled in submitPosition()

    // Ensure a position model instance is loaded for editing
    if (!$this->position) {
      // Should not happen if flow is correct, but defensive check
      Log::error('Positions component: Attempted to update non-existent position model.', ['user_id' => Auth::id(), 'is_edit' => $this->isEdit]); // Use imported Log
      throw new Exception(__('No position selected for update.')); // Throw exception for transaction rollback, Use imported Exception
    }

    // Update the loaded position model instance with the new data
    $this->position->update([
      'name' => $this->name,
      'vacancies_count' => $this->vacanciesCount ?? 0, // Use nullish coalescing
      // updated_by might be handled by a trait or observer
      // 'updated_by' => Auth::id(), // Use imported Auth
    ]);

    // Success feedback and dispatching is handled in submitPosition() after transaction
    // Reset form is handled in submitPosition() finally block
  }

  // Method to show the modal for adding a new position
  public function showNewPositionModal(): void // Void return type
  {
    // Reset the form state before showing the modal for a new position
    $this->resetForm(); // Use the helper method
    $this->isEdit = false; // Ensure it's not in edit mode

    // Dispatch event to show the modal if not handled by wire:click
    // $this->dispatch('showModal', elementId: '#positionModal');
  }

  // Method to show the modal for editing an existing position
  // Livewire's route model binding injects the Position model based on the ID passed from the view.
  public function showEditPositionModal(Position $position): void // Use route model binding, void return type
  {
    // Reset the form state before populating with edit data
    $this->resetForm(); // Use the helper method

    // Set the component properties with the data from the loaded Position model
    $this->isEdit = true;
    $this->position = $position; // Store the model instance
    $this->name = $position->name;
    $this->vacanciesCount = $position->vacancies_count; // Populate from model attribute

    // Dispatch event to show the modal if not handled by wire:click
    // $this->dispatch('showModal', elementId: '#positionModal');
  }

  // Method to confirm deletion (optional, if using a separate confirmation modal)
  // In the Blade, you call wire:click="confirmDeletePosition(position.id)" to set confirmedId.
  public function confirmDeletePosition(int $id): void // Accept ID, use int type hint, void return type
  {
    $this->confirmedId = $id;
    // You might dispatch an event here to show a confirmation modal if you have one
    // $this->dispatch('showModal', elementId: '#deleteConfirmModal');
  }

  // Method to delete a position
  // Refactored to use confirmedId for consistency
  public function deletePosition(): void // Void return type
  {
    // Check if an ID is confirmed
    if ($this->confirmedId === null) {
      return; // No ID to delete
    }

    // Find the record to delete
    $record = Position::find($this->confirmedId);

    if (!$record) {
      // Record not found (maybe already deleted by another user)
      session()->flash('error', __('Position record not found for deletion.')); // Translated
      $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
    } else {
      // Perform the deletion within a transaction
      try {
        DB::transaction(function () use ($record) { // Use transaction, pass $record
          // Consider adding logic here to handle related records (e.g., employees, timelines)
          // before deleting the position, if not handled by database CASCADE or model events.
          // Example: $record->timelines()->delete(); // If related timelines should be deleted

          $record->delete(); // Use model delete method (handles soft deletes if enabled)
        });

        session()->flash('success', __('Position Deleted Successfully!')); // Translated
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
      } catch (Exception $e) { // Use imported Exception
        // Handle potential database errors during deletion (e.g., foreign key constraints)
        Log::error('Positions component: Failed to delete position record.', ['user_id' => Auth::id(), 'position_id' => $this->confirmedId, 'exception' => $e]); // Use imported Log
        session()->flash('error', __('An error occurred while deleting the position.') . ' ' . $e->getMessage()); // Show exception message or generic error
        $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
      }
    }

    // Reset confirmedId after attempting deletion
    $this->confirmedId = null;
    // If you used a confirmation modal, you'd close it here:
    // $this->dispatch('closeModal', elementId: '#deleteConfirmModal');

    // Livewire will automatically re-fetch the 'positions' computed property
    // because the underlying data in the database has changed.
  }


  /**
   * Resets the form properties and validation errors.
   * Called when the modal is hidden/closed (via JS event listener in Blade)
   * and before showing the modal for a new position or editing.
   */
  public function resetForm(): void // Use void return type
  {
    // Use Livewire's reset method to clear the specified public properties
    $this->reset([
      'name',
      'vacanciesCount',
      'isEdit', // Reset edit mode flag
      'position', // Clear the loaded model instance
      'confirmedId', // Clear the confirmed ID for deletion
    ]);

    // Clear any validation errors that might be displayed
    $this->resetValidation();
  }
}
