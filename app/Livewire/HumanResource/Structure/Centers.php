<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Center;
use App\Models\Timeline;
use Carbon\Carbon;
use Livewire\Attributes\Rule;
use Livewire\Component;

class Centers extends Component
{
  // TODO: Show supervisor name beside each center in the table.
  // FIXME: Weekends input (select2) doesn't turn red on validation error.
  // FIXME: Weekends input (select2) doesn't display previously entered values visually when click on edit center.

  // Variables - Start //
  // No need to define $centers here if fetched and passed directly in render
  // public $centers = []; // Remove or keep if needed elsewhere in component logic

  #[Rule('required')]
  public $name;

  #[Rule('required')]
  public $startWorkHour;

  #[Rule('required')]
  public $endWorkHour;

  #[Rule('required')]
  public $weekends = []; // Initialize as array for Select2

  public $center; // To hold the model instance for editing

  public $isEdit = false;

  public $confirmedId;
  // Variables - End //

  public function render()
  {
    // Fetch centers
    // Apply any sorting, filtering, or pagination here
    $centers = Center::all(); // Or use your preferred query

    // Process centers to add computed properties (members_count, days_name)
    // This moves the logic from the Blade template into the component class
    $processedCenters = $centers->map(function ($center) {
      // Add members count as a property
      $center->members_count = $this->getMembersCount($center->id);

      // Add days name string as a property, handle potential null or non-array weekends
      $center->days_name = $this->getDaysName($center->weekends ?? []);

      return $center; // Return the modified center object
    });

    // Pass the processed centers to the view
    // The Blade view will now iterate over $centers and access $center->members_count and $center->days_name
    return view('livewire.human-resource.structure.centers', [
      'centers' => $processedCenters,
    ]);
  }

  public function submitCenter()
  {
    // The validate method is called inside addCenter/editCenter
    $this->isEdit ? $this->editCenter() : $this->addCenter();

    // Reset form properties after submission (whether adding or editing)
    // Moved reset outside the conditional to ensure it always happens on successful submission
    $this->reset(['name', 'startWorkHour', 'endWorkHour', 'weekends', 'center', 'isEdit']); // Specify properties to reset
    $this->confirmedId = null; // Also reset confirmedId
  }

  public function addCenter()
  {
    $this->validate(); // Validate properties defined with #[Rule]

    Center::create([
      'name' => $this->name,
      'start_work_hour' => $this->startWorkHour,
      'end_work_hour' => $this->endWorkHour,
      'weekends' => $this->weekends,
    ]);

    // Dispatch events - assuming these are for closing a modal and showing a toast notification
    $this->dispatch('closeModal', elementId: '#centerModal');
    $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Center created successfully!')); // More specific message
  }

  public function editCenter()
  {
    $this->validate(); // Validate properties defined with #[Rule]

    // Ensure $this->center exists and is a Center model instance before attempting to update
    if (! $this->center instanceof Center) {
      // Optionally log an error or dispatch an error message if $this->center is not set correctly
      $this->dispatch('toastr', type: 'error', message: __('Error: Center not selected for editing.'));
      return; // Stop execution if center is not valid
    }

    $this->center->update([
      'name' => $this->name,
      'start_work_hour' => $this->startWorkHour,
      'end_work_hour' => $this->endWorkHour,
      'weekends' => $this->weekends,
    ]);

    // Dispatch events
    $this->dispatch('closeModal', elementId: '#centerModal');
    $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Center updated successfully!')); // More specific message

    // Reset properties is handled in submitCenter now
    // $this->reset(); // Removed from here
  }

  public function confirmDeleteCenter($id)
  {
    $this->confirmedId = $id;
    // Optionally fetch the center here if you need its name for a confirmation message
    // $center = Center::find($id);
    // $this->dispatch('showConfirmationModal', message: "Are you sure you want to delete center: {$center->name}?");
  }

  // Using route model binding for the Center model
  public function deleteCenter(Center $center)
  {
    $center->delete();

    // Reset confirmedId after deletion
    $this->confirmedId = null;

    // Dispatch toast notification
    $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Center deleted successfully!')); // More specific message
  }

  // Method to show the new center modal
  public function showNewCenterModal()
  {
    $this->reset(['name', 'startWorkHour', 'endWorkHour', 'weekends', 'center', 'isEdit']); // Reset relevant properties
    $this->isEdit = false; // Ensure it's set to create mode
    // Dispatch an event to show the modal (if not automatically handled by Livewire/Alpine)
    $this->dispatch('showModal', elementId: '#centerModal');
    // Dispatch event to signal modal is ready for component initialization (for Select2/Flatpickr)
    $this->dispatch('centerModalShown'); // Custom event used in the Blade JS
  }

  // Method to show the edit center modal
  public function showEditCenterModal(Center $center)
  {
    // Reset properties before populating for edit
    $this->reset(['name', 'startWorkHour', 'endWorkHour', 'weekends', 'center', 'isEdit']); // Reset relevant properties

    $this->isEdit = true; // Set to edit mode
    $this->center = $center; // Store the model instance

    // Populate form properties from the model
    $this->name = $center->name;
    $this->startWorkHour = $center->start_work_hour;
    $this->endWorkHour = $center->end_work_hour;
    // Ensure weekends is an array, even if stored as string/null
    $this->weekends = is_array($center->weekends) ? $center->weekends : json_decode($center->weekends, true) ?? [];

    // Dispatch an event to show the modal (if not automatically handled by Livewire/Alpine)
    $this->dispatch('showModal', elementId: '#centerModal');
    // Dispatch event to signal modal is ready for component initialization (for Select2/Flatpickr)
    $this->dispatch('centerModalShown'); // Custom event used in the Blade JS
  }

  // This method is no longer needed in the component if the calculation is done in render
  // public function getSupervisor($id)
  // {
  //     //
  // }

  // This method is kept to be called from the render method
  public function getMembersCount($center_id)
  {
    return Timeline::where('center_id', $center_id)
      ->whereNull('end_date')
      ->distinct('employee_id')
      ->count();
  }

  // This method is kept to be called from the render method
  public function getDaysName(array $weekends) // Added type hint
  {
    $daysName = [];
    // Ensure $weekends is iterable, though type hint helps
    if (!is_array($weekends)) {
      return ''; // Return empty string or handle error if not an array
    }

    foreach ($weekends as $day) {
      // Ensure $day is a valid integer before using addDays
      if (is_numeric($day)) {
        $daysName[] = mb_substr(
          Carbon::now()
            ->startOfWeek()
            ->addDays((int) $day) // Cast to int
            ->format('l'),
          0,
          3
        );
      }
    }

    return implode(', ', $daysName);
  }

  // Optional: Add a mount method if you need to initialize properties on component load
  // public function mount()
  // {
  //     //
  // }
}
