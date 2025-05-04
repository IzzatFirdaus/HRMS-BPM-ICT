<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Center;
use App\Models\Timeline;
use Carbon\Carbon;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Illuminate\Support\Facades\Log; // Import Log Facade
use Illuminate\Support\Facades\Auth; // Import Auth Facade
use Illuminate\Pagination\LengthAwarePaginator; // Import for return type hint (optional)

// Add the WithPagination trait
use Livewire\WithPagination;


class Centers extends Component
{
  // Use the WithPagination trait
  use WithPagination;

  // TODO: Show supervisor name beside each center in the table.
  // FIXME: Weekends input (select2) doesn't turn red on validation error.
  // FIXME: Weekends input (select2) doesn't display previously entered values visually when click on edit center.
  // (These Fixme/Todo comments are kept from your original code)

  // Variables - Start //

  #[Rule('required')]
  public $name;

  #[Rule('required')]
  public $startWorkHour;

  #[Rule('required')]
  public $endWorkHour;

  // Store weekends as an array of numbers (0-6)
  #[Rule('required')]
  public $weekends = [];

  // Use type-hinting for the model instance for editing
  public ?Center $center = null;

  // Boolean flag for toggling between add/edit mode
  public bool $isEdit = false;

  // Property to hold the ID for the delete confirmation UI
  public $confirmedId = null;

  // Property for pagination - number of items per page
  // #[Rule('numeric', min: 1)] // Add rule if user can change per page
  public int $perPage = 10; // Default items per page


  // Variables - End //


  /**
   * Render the component view.
   * Fetches and paginates centers, adds computed properties, and passes the paginator to the view.
   *
   * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
   */
  public function render() //: \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory // Optional return type hint
  {
    // Start with the query builder for Centers
    $centersQuery = Center::query();

    // Apply any sorting or filtering here based on other component properties if needed
    // Example: $centersQuery->orderBy('name');

    // Apply pagination FIRST to get a Paginator instance
    $paginator = $centersQuery->paginate($this->perPage);

    // Process the collection *within* the paginator to add computed properties
    // This preserves the pagination links and metadata
    $processedItems = $paginator->getCollection()->map(function ($center) {
      // Add members count as a property
      // Assumes getMembersCount method exists below
      $center->members_count = $this->getMembersCount($center->id);

      // Add days name string as a property
      // Uses the weekends_formatted accessor from the Center model (recommended)
      // Assumes you have added the weekendsFormatted accessor to App\Models\Center.php
      $center->days_name = $center->weekends_formatted;


      return $center; // Return the modified center object
    });

    // Set the processed items back onto the paginator instance
    $paginator->setCollection($processedItems);


    // Pass the PAGINATOR instance to the view
    return view('livewire.human-resource.structure.centers', [
      'centers' => $paginator, // Pass the Paginator object to the view
    ]);
  }

  /**
   * Handle form submission (either add or edit).
   */
  public function submitCenter()
  {
    // Validation is handled within addCenter/editCenter
    // Call the appropriate method based on isEdit flag
    $this->isEdit ? $this->editCenter() : $this->addCenter();

    // Reset form properties and confirmedId after successful submission
    $this->reset(['name', 'startWorkHour', 'endWorkHour', 'weekends', 'center', 'isEdit', 'confirmedId']);
    // Also reset validation errors
    $this->resetValidation();

    // The render method will automatically re-run and update the list with the new/updated center
  }

  /**
   * Add a new center record.
   */
  public function addCenter()
  {
    // Validate component properties defined with #[Rule]
    $this->validate();

    Center::create([
      'name' => $this->name,
      'start_work_hour' => $this->startWorkHour,
      'end_work_hour' => $this->endWorkHour,
      // Store weekends as JSON string in the database
      // Assumes the 'weekends' column in the database is TEXT or JSON and cast to 'array' in the Model
      'weekends' => json_encode($this->weekends),
    ]);

    // Dispatch events for modal closing and toast notification
    $this->dispatch('closeModal', elementId: '#centerModal'); // Assuming this event closes your modal
    $this->dispatch('toastr', type: 'success', message: __('Center created successfully!')); // Toast notification
  }

  /**
   * Edit an existing center record.
   * Requires $this->center to be populated with the model instance.
   */
  public function editCenter()
  {
    // Validate component properties defined with #[Rule]
    $this->validate();

    // Ensure $this->center exists and is a Center model instance before attempting to update
    if (!$this->center instanceof Center) {
      Log::error('Attempted to edit center but $this->center is not a Center model instance.', [
        'user_id' => Auth::id() ?? 'guest', // Log the current user ID
        'center_data' => $this->center, // Log the value of $this->center for debugging
      ]);
      // Dispatch an error toast notification
      $this->dispatch('toastr', type: 'error', message: __('Error: Center not selected for editing.'));
      return; // Stop execution if center is not valid
    }

    // Update the existing center record
    $this->center->update([
      'name' => $this->name,
      'start_work_hour' => $this->startWorkHour,
      'end_work_hour' => $this->endWorkHour,
      // Store weekends as JSON string in the database
      'weekends' => json_encode($this->weekends),
    ]);

    // Dispatch events for modal closing and toast notification
    $this->dispatch('closeModal', elementId: '#centerModal'); // Assuming this event closes your modal
    $this->dispatch('toastr', type: 'success', message: __('Center updated successfully!')); // Toast notification

    // Reset properties is handled in submitCenter
    // The render method will automatically re-run and update the list
  }

  /**
   * Set the ID of the center to be confirmed for deletion.
   * This is typically called from the view before showing a "Sure?" button.
   */
  public function confirmDeleteCenter($centerId)
  {
    $this->confirmedId = $centerId;
    // Optional: Fetch center name for a more informative confirmation message in the UI
    // $center = Center::find($centerId);
    // if ($center) {
    //     // Dispatch an event to show a specific confirmation prompt in the view (optional)
    //     // $this->dispatch('showConfirmationPrompt', message: "Are you sure you want to delete center: {$center->name}?");
    // }
  }

  /**
   * Delete a center record.
   * Uses Livewire Model Binding to automatically resolve the Center model based on the ID passed from the view.
   *
   * @param Center $center The Center model instance to delete.
   */
  public function deleteCenter(Center $center) // Livewire Model Binding handles fetching the model by ID
  {
    try {
      // Attempt to delete the center
      $center->delete();

      // Dispatch a success toast notification
      $this->dispatch('toastr', type: 'success', message: __('Center deleted successfully!'));
    } catch (\Exception $e) {
      // Log the error for debugging
      Log::error('Error deleting center.', [
        'center_id' => $center->id ?? 'N/A', // Log the ID of the center being deleted
        'error' => $e->getMessage(),
        'user_id' => Auth::id() ?? 'guest', // Log the current user ID
      ]);
      // Dispatch an error toast notification
      $this->dispatch('toastr', type: 'error', message: __('Error deleting center.'));
    }

    // Reset confirmedId after the delete attempt (success or failure)
    $this->confirmedId = null;

    // The render method will automatically re-run due to the confirmedId change or the implicit update from delete
    // No need to manually call $this->render()
  }

  /**
   * Show the modal for creating a new center.
   */
  public function showNewCenterModal()
  {
    // Reset all relevant properties for a clean form for new entry
    $this->reset(['name', 'startWorkHour', 'endWorkHour', 'weekends', 'center', 'isEdit', 'confirmedId']);
    // Reset validation errors
    $this->resetValidation();

    $this->isEdit = false; // Ensure it's set to create mode

    // Dispatch events to control the modal and signal for JS component initialization
    $this->dispatch('showModal', elementId: '#centerModal'); // Assuming this event shows your modal
    $this->dispatch('centerModalShown'); // Custom event for JS initialization (Select2, Flatpickr)
  }

  /**
   * Show the modal for editing an existing center.
   * Uses Livewire Model Binding to resolve the Center model based on the ID passed from the view.
   *
   * @param Center $center The Center model instance to edit.
   */
  public function showEditCenterModal(Center $center) // Livewire Model Binding handles fetching the model by ID
  {
    // Reset properties before populating for edit to clear previous data/errors
    $this->reset(['name', 'startWorkHour', 'endWorkHour', 'weekends', 'center', 'isEdit', 'confirmedId']);
    // Reset validation errors
    $this->resetValidation();


    $this->isEdit = true; // Set to edit mode
    $this->center = $center; // Store the resolved model instance


    // Populate form properties from the model attributes
    $this->name = $center->name;
    $this->startWorkHour = $center->start_work_hour;
    $this->endWorkHour = $center->end_work_hour;

    // Populate weekends property
    // Assumes 'weekends' is cast to 'array' in the Center model.
    // It will be an array (or null) containing the stored day numbers (0-6).
    $this->weekends = $center->weekends ?? [];


    // Dispatch events to control the modal and signal for JS component initialization
    $this->dispatch('showModal', elementId: '#centerModal'); // Assuming this event shows your modal
    $this->dispatch('centerModalShown'); // Custom event for JS initialization (Select2, Flatpickr)
  }


  // Helper method to get the count of active members in a center
  // This method is called from the render method
  public function getMembersCount(int $center_id): int // Added type hints
  {
    // Find Timeline records for the given center_id with no end date (active assignment)
    // Count distinct employee IDs
    return Timeline::where('center_id', $center_id)
      ->whereNull('end_date') // Assuming 'end_date' null means currently assigned
      ->distinct('employee_id') // Count unique employees
      ->count();
  }

  // The getDaysName method is no longer needed here if using the model's weekendsFormatted accessor in render.
  /*
    public function getDaysName(array $weekends): string
    {
       // ... (remove this method if using the model accessor) ...
    }
    */

  // Optional mount method if you need to initialize properties on component load
  // public function mount()
  // {
  //     //
  // }
}
