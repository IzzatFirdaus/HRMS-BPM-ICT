<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Position;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Positions extends Component
{
  // Variables - Start //

  // Public properties for the form fields
  #[Rule('required|string|max:255')] // Added string and max length validation
  public $name;

  #[Rule('required|integer|min:0')] // Added integer and min 0 validation
  public $vacanciesCount;

  // Property to hold the Position model instance when editing
  public $position;

  // Flag to indicate if the modal is in edit mode
  public $isEdit = false;

  // Property to hold the ID of the position being confirmed for deletion
  public $confirmedId;

  // Variables - End //


  // Method called when the component is initially rendered or refreshed
  public function render()
  {
    // Fetch all positions to display in the table (assuming the main view lists them)
    $positions = Position::all(); // Consider pagination if you have many positions

    // Return the view with the positions data
    return view('livewire.human-resource.structure.positions', [
      'positions' => $positions,
    ]);
  }

  // Method called by the form submission (wire:submit.prevent="submitPosition")
  public function submitPosition()
  {
    // Delegate to add or edit method based on the $isEdit flag
    $this->isEdit ? $this->editPosition() : $this->addPosition();
  }

  // Method to add a new position
  public function addPosition()
  {
    // Validate the form inputs using the rules defined in the rules() method
    $this->validate();

    // Create a new Position record in the database
    Position::create([
      'name' => $this->name,
      'vacancies_count' => $this->vacanciesCount,
    ]);

    // Dispatch events
    // 'closeModal': Closes the Bootstrap modal using custom JS/event listener in the main layout/view.
    // 'toastr': Shows a success notification using the Toastr library (assuming you have JS for this event).
    $this->dispatch('closeModal', elementId: '#positionModal');
    $this->dispatch('toastr', type: 'success', message: __('Position Added Successfully!')); // More specific message

    // Reset the form properties and re-fetch positions to update the list in the main view
    $this->resetForm(); // Use the new resetForm method
  }

  // Method to update an existing position
  public function editPosition()
  {
    // Validate the form inputs
    $this->validate();

    // Ensure a position model instance is loaded for editing
    if (!isset($this->position)) {
      // Dispatch an error notification if no position is selected for update
      $this->dispatch('toastr', type: 'error', message: __('No position selected for update.'));
      return;
    }

    // Update the loaded position model instance with the new data
    $this->position->update([
      'name' => $this->name,
      'vacancies_count' => $this->vacanciesCount,
    ]);

    // Dispatch events
    $this->dispatch('closeModal', elementId: '#positionModal');
    $this->dispatch('toastr', type: 'success', message: __('Position Updated Successfully!')); // More specific message

    // Reset the form properties and re-fetch positions
    $this->resetForm(); // Use the new resetForm method
    // Note: The render() method will automatically re-run after this action if needed,
    // refreshing the list of positions displayed in the main view.
  }

  // Method to show the modal for adding a new position
  public function showNewPositionModal()
  {
    // Reset the form state before showing the modal for a new position
    $this->resetForm(); // Use the new resetForm method
    $this->isEdit = false; // Ensure it's not in edit mode

    // Dispatch event to show the modal
    $this->dispatch('showModal', elementId: '#positionModal');
  }

  // Method to show the modal for editing an existing position
  // Livewire's route model binding injects the Position model based on the ID passed from the view.
  public function showEditPositionModal(Position $position)
  {
    // Reset the form state before populating with edit data
    $this->resetForm(); // Use the new resetForm method

    // Set the component properties with the data from the loaded Position model
    $this->isEdit = true;
    $this->position = $position; // Store the model instance
    $this->name = $position->name;
    $this->vacanciesCount = $position->vacancies_count; // Correct property name to match model column

    // Dispatch event to show the modal
    $this->dispatch('showModal', elementId: '#positionModal');
  }

  // Method to confirm deletion (optional, if using a separate confirmation modal)
  // In the Blade, you call wire:click="confirmDeletePosition(position.id)" to set confirmedId.
  public function confirmDeletePosition($id)
  {
    $this->confirmedId = $id;
    // You might dispatch an event here to show a confirmation modal if you have one
    // $this->dispatch('showModal', elementId: '#deleteConfirmModal');
  }

  // Method to delete a position
  // In the Blade, you call wire:click="deletePosition(position)" or wire:click="deletePosition(confirmedId)"
  // if using confirmDeletePosition first. Let's assume direct deletion from table row for simplicity
  // as per the original showEditPositionModal pattern.
  public function deletePosition(Position $position)
  {
    $position->delete();

    // Dispatch events
    $this->dispatch('toastr', type: 'success', message: __('Position Deleted Successfully!')); // More specific message

    // After deletion, reset confirmedId and re-fetch positions (render will handle re-fetch)
    $this->confirmedId = null; // Clear confirmed ID
    // $this->resetForm(); // No need to reset form, as modal might not be open

    // If you used a confirmation modal, you'd close it here:
    // $this->dispatch('closeModal', elementId: '#deleteConfirmModal');
  }


  /**
   * Resets the form properties and validation errors.
   * Called when the modal is hidden/closed (via JS event listener in Blade)
   * and before showing the modal for a new position or editing.
   */
  public function resetForm()
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

    // Removed the incorrect console.log() call
  }
}
