<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Center; // Assumed to exist
use App\Models\Department; // Assumed to exist
use App\Models\Employee; // Assumed to exist with 'timelines', 'transitions', 'is_active'
use App\Models\Position; // Assumed to exist
use App\Models\Timeline; // Assumed to exist and cast dates/times
use Carbon\Carbon; // For date/time manipulation
use Exception; // For general exception handling
use Illuminate\Database\Eloquent\Builder; // For type hinting query builders
use Illuminate\Support\Facades\DB; // For database transactions
use Illuminate\Support\Facades\Log; // For logging errors
use Livewire\Attributes\Computed; // For Livewire v3+ computed properties
use Livewire\Attributes\On; // For Livewire v3+ event listeners
use Livewire\Attributes\Validate; // For Livewire v3+ property validation (used within submit methods)
use Livewire\Component; // Base Livewire component
use Livewire\WithPagination; // Trait for pagination (if needed)
use Illuminate\Support\Collection; // For type hinting collections
use Illuminate\Support\Facades\Auth; // Assuming created_by/updated_by are User IDs
use Livewire\Attributes\Locked; // Import the Locked attribute


// Assumptions:
// 1. Employee model has relationships: 'timelines' (hasMany Timeline), 'transitions' (hasMany AssetTransition).
// 2. Timeline model has relationships: 'center' (belongsTo Center), 'department' (belongsTo Department), 'position' (belongsTo Position).
// 3. Timeline model has columns: 'employee_id', 'center_id', 'department_id', 'position_id', 'start_date', 'end_date', 'is_sequent' (tinyint/bool), 'notes'.
// 4. Timeline model casts 'start_date' and 'end_date' to 'date' or 'datetime'.
// 5. AssetTransition model exists and has 'asset' relationship.
// 6. Center, Department, Position models have 'name' attribute.
// 7. Livewire v3+ is used for attributes.
// 8. The view uses modals controlled by JS dispatches ('closeModal', 'toastr', 'clearSelect2Values', 'setSelect2Values').
// 9. created_by/updated_by fields might exist on models (handled by traits or observers).

class EmployeeInfo extends Component
{
  use WithPagination; // Added WithPagination trait - remove if not used for lists

  // 👉 State Variables (Public properties that sync with the view)

  #[Locked] // Prevent external manipulation via view
  public ?Employee $employee = null; // The employee model being viewed/managed, initialized to null

  public ?Timeline $timeline = null; // State: Timeline model being edited

  public array $employeeTimelineInfo = [ // Form data for timeline create/edit
    'center_id' => '', // Changed key names for consistency
    'department_id' => '',
    'position_id' => '',
    'start_date' => '', // Use string for date input binding
    'end_date' => '', // Use string for date input binding (can be null)
    'is_sequent' => true, // Default to true for sequential
    'notes' => '',
  ];

  public bool $isEdit = false; // State: Is the timeline modal in edit mode

  public ?int $confirmedId = null; // State: ID of the timeline pending deletion confirmation

  // Removed redundant selectedCenter, selectedDepartment, selectedPosition


  // 👉 Computed Properties (Livewire v3+ - Cached data that depends on state)

  #[Computed]
  public function centers(): Collection
  {
    // Cache all centers for dropdown
    // Consider optimizing for large lists if needed
    return Center::all();
  }

  #[Computed]
  public function departments(): Collection
  {
    // Cache all departments for dropdown
    // Consider optimizing for large lists if needed
    return Department::all();
  }

  #[Computed]
  public function positions(): Collection
  {
    // Cache all positions for dropdown
    // Consider optimizing for large lists if needed
    return Position::all();
  }

  #[Computed]
  public function employeeTimelines(): Collection // Using Collection as there's no pagination here
  {
    // Fetch the employee's timelines with relationships
    // This computed property will refresh when an action like add/edit/delete timeline occurs
    // because those actions typically update related models or trigger component updates.
    // No need to fetch in render() anymore.
    if (!$this->employee) {
      return collect(); // Return empty if employee is not loaded
    }
    return $this->employee
      ->timelines()
      ->with(['center', 'department', 'position'])
      ->orderBy('start_date', 'desc') // Order by start date, most recent first
      ->get();
  }

  #[Computed]
  public function employeeAssets(): Collection // Using Collection as there's no pagination here
  {
    // Fetch the employee's assigned assets
    // This computed property will refresh if asset transitions are updated
    if (!$this->employee) {
      return collect(); // Return empty if employee is not loaded
    }
    return $this->employee
      ->transitions()
      ->with('asset')
      ->orderBy('handed_date', 'desc')
      ->get();
  }


  // 👉 Lifecycle Hook

  // Mount is only responsible for loading the initial employee model and relationships
  public function mount(int $id) // Type hint ID
  {
    // Find the employee. Add error handling if not found.
    $employee = Employee::find($id);

    if (!$employee) {
      // Handle case where employee ID is invalid or not found
      // Redirect to an error page, flash error, or show an error message in the view
      session()->flash('error', __('Employee not found.'));
      // Example: Redirect to employee index or dashboard
      // return redirect()->route('admin.employees.index');
      // For now, set employee to null and return, the view should handle null employee
      $this->employee = null; // Explicitly set to null
      Log::error('EmployeeInfo component: Employee not found in mount.', ['employee_id' => $id, 'user_id' => Auth::id()]);
    } else {
      $this->employee = $employee;
      // Eager load relationships needed for initial display or potential future use
      // $this->employee->load(['timelines.center', 'timelines.department', 'timelines.position', 'transitions.asset']);
      // Note: Timelines and Assets are already fetched by Computed properties,
      // but eager loading here might be slightly faster if they are always needed.
    }

    // Initial data for computed properties (timelines, assets, dropdowns)
    // is automatically loaded by Livewire after mount completes.
  }


  // 👉 Render method
  // No data fetching needed here, it's handled by computed properties
  public function render()
  {
    // Access computed properties directly in the view:
    // $this->employeeTimelines, $this->employeeAssets, $this->centers, etc.
    return view('livewire.human-resource.structure.employee-info');
  }


  // 👉 Employee Active Status Toggle

  // Toggle the active status of the employee
  public function toggleActive(): void // Void return type
  {
    if (!$this->employee) {
      // Should not happen if employee is loaded in mount, but defensive check
      session()->flash('error', __('Employee not loaded. Cannot toggle active status.'));
      $this->dispatch('toastr', type: 'error', message: __('Error!'));
      return;
    }

    DB::transaction(function () { // Use transaction for atomicity
      // Find the current timeline
      $presentTimeline = $this->employee // Use method chain on nullable employee, relies on check above
        ->timelines()
        ->orderBy('start_date', 'desc') // Order by start_date to get the latest
        ->first(); // Get the most recent timeline, can be null

      // Check if a timeline exists before trying to update it
      if ($presentTimeline) {
        if ($this->employee->is_active) { // If currently active, deactivate
          $this->employee->is_active = false;
          // End the current timeline when deactivating the employee
          // Only set end_date if it's not already set, or force update?
          if ($presentTimeline->end_date === null) {
            $presentTimeline->end_date = Carbon::now()->endOfDay(); // Set end of day for date precision
            $presentTimeline->save();
          }
        } else { // If currently inactive, activate
          $this->employee->is_active = true;
          // When activating, usually a *new* timeline is created,
          // or the *most recent* one is re-opened (if it was the one just closed).
          // The original logic re-opens the most recent one. Be careful if there are gaps/overlaps.
          // This might need refinement based on business logic.
          // Current logic: If activating, clear the end_date of the most recent timeline.
          $presentTimeline->end_date = null; // Re-open the most recent timeline
          $presentTimeline->save();

          // Alternative: If activating, require creating a *new* timeline starting now.
          // This would involve showing the timeline modal and setting start_date to now.
        }

        $this->employee->save(); // Save the employee's active status

        session()->flash('success', __('Employee active status updated successfully.')); // Translated
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
      } else {
        // Handle case where employee has no timelines - cannot toggle active status easily
        session()->flash('error', __('Employee has no timeline records. Cannot toggle active status.')); // Translated
        $this->dispatch('toastr', type: 'error', message: __('Action Required!'));
      }
    }); // Transaction ends

    // If using Computed properties, Livewire will handle refreshing the timeline list
    // when the employee model or associated timelines are updated.
  }


  // 👉 Timeline Management Actions

  // Validation rules for timeline form
  protected function timelineRules()
  {
    // Get the most recent timeline to check for overlaps if adding a new sequential one
    // Note: Accessing Computed property might be cleaner, but getting it here ensures it's fresh before validation
    // Using $this->employee?->... to safely access timeline relationship
    $latestTimeline = $this->employee?->timelines()->orderBy('start_date', 'desc')->first();

    $rules = [
      'employeeTimelineInfo.center_id' => 'required|exists:centers,id', // Validate center exists
      'employeeTimelineInfo.department_id' => 'required|exists:departments,id', // Validate department exists
      'employeeTimelineInfo.position_id' => 'required|exists:positions,id', // Validate position exists
      'employeeTimelineInfo.start_date' => 'required|date', // Validate start date is required and a date
      'employeeTimelineInfo.end_date' => 'nullable|date|after_or_equal:employeeTimelineInfo.start_date', // End date is nullable, a date, and >= start date
      'employeeTimelineInfo.is_sequent' => 'required|boolean', // Validate is_sequent is required and boolean (or 0/1)
      'employeeTimelineInfo.notes' => 'nullable|string|max:500', // Notes are optional string max 500 chars
    ];

    // Add conditional validation based on is_sequent and existing timelines
    if (!$this->isEdit) { // Only apply these checks when adding a new timeline
      // Ensure start_date is a Carbon instance for comparison, default to null if invalid format before validation
      $newStartDate = null;
      try {
        if (!empty($this->employeeTimelineInfo['start_date'])) {
          $newStartDate = Carbon::parse($this->employeeTimelineInfo['start_date']);
        }
      } catch (\Exception $e) {
        // Will be caught by the 'date' validation rule, but helps prevent errors in logic here
        $newStartDate = null; // Ensure it's null if parsing fails
      }


      if ($this->employeeTimelineInfo['is_sequent']) {
        // If creating a new sequential timeline, its start date must be after the previous one's end date
        if ($latestTimeline) {
          if ($latestTimeline->end_date) {
            // Previous timeline was closed: New start date must be AFTER the previous end date
            // Ensure latestTimeline->end_date is a Carbon instance
            $latestEndDate = Carbon::parse($latestTimeline->end_date);
            $rules['employeeTimelineInfo.start_date'] .= '|after:' . $latestEndDate->format('Y-m-d');
          } elseif ($latestTimeline->end_date === null) {
            // Previous timeline is currently open: New start date must be AFTER today (as the previous one will be closed today)
            // Or, more correctly, it should be AFTER the previous start date if overlapping is not allowed even for sequential.
            // Let's enforce the new start date is today or later if the previous was open.
            $rules['employeeTimelineInfo.start_date'] .= '|after_or_equal:today';
            // Also, a sequential timeline shouldn't have an end date set upon creation if it's the *new* current one.
            $rules['employeeTimelineInfo.end_date'] = 'nullable'; // Enforce nullable end_date for sequential
          }
        }
        // If no latest timeline, no 'after' restriction based on previous end_date.
      } else { // If creating a non-sequential timeline
        // Validate that the new timeline's date range does NOT overlap with existing timelines
        // This is complex. A simpler approach might be to prevent adding non-sequential
        // if there's already an open timeline. Or a more complex check for date range overlaps.
        // For now, let's add a basic check that the new start/end dates don't fall *within* an open timeline's active period.
        if ($latestTimeline && $latestTimeline->end_date === null) {
          // If there's an open timeline, the new non-sequential timeline should not start or end within its duration (start_date to now)
          // This logic might need refinement based on exact business rules for overlaps.
          // Example basic check: New start/end dates must be BEFORE the existing open timeline's start date.
          if ($latestTimeline->start_date) { // Ensure latestTimeline->start_date is not null
            $latestStartDate = Carbon::parse($latestTimeline->start_date);
            $rules['employeeTimelineInfo.start_date'] .= '|before:' . $latestStartDate->format('Y-m-d');
            $rules['employeeTimelineInfo.end_date'] .= '|nullable|date|after_or_equal:employeeTimelineInfo.start_date|before:' . $latestStartDate->format('Y-m-d');
          }
        }
      }
    } else { // If in Edit mode
      // When editing, the validation needs to prevent overlaps with *other* timelines
      // excluding the one being edited ($this->timeline). This requires a more complex
      // custom validation rule or logic within the update method after basic validation.
      // For simplicity in this refactor, we'll rely on basic date validation.
      // A full overlap check is more advanced.
    }


    return $rules;
  }

  // Custom validation messages for timeline form
  protected function timelineMessages()
  {
    return [
      'employeeTimelineInfo.center_id.required' => __('Center is required.'),
      'employeeTimelineInfo.center_id.exists' => __('Invalid center selected.'),
      'employeeTimelineInfo.department_id.required' => __('Department is required.'),
      'employeeTimelineInfo.department_id.exists' => __('Invalid department selected.'),
      'employeeTimelineInfo.position_id.required' => __('Position is required.'),
      'employeeTimelineInfo.position_id.exists' => __('Invalid position selected.'),
      'employeeTimelineInfo.start_date.required' => __('Start Date is required.'),
      'employeeTimelineInfo.start_date.date' => __('Start Date must be a valid date.'),
      'employeeTimelineInfo.start_date.after' => __('Start Date must be after the end date of the previous timeline (:date).'), // Use :date placeholder
      'employeeTimelineInfo.start_date.after_or_equal' => __('Start Date must be today or later if the previous timeline is open.'), // If using after_or_equal:today
      // Update message for before if used for non-sequential
      'employeeTimelineInfo.start_date.before' => __('Start Date cannot be on or after the start date of the current open timeline.'),
      'employeeTimelineInfo.end_date.date' => __('End Date must be a valid date.'),
      'employeeTimelineInfo.end_date.after_or_equal' => __('End Date must be on or after the Start Date.'),
      // Update message for before if used for non-sequential
      'employeeTimelineInfo.end_date.before' => __('End Date cannot be on or after the start date of the current open timeline.'),
      'employeeTimelineInfo.is_sequent.required' => __('Sequential status is required.'),
      'employeeTimelineInfo.is_sequent.boolean' => __('Invalid value for sequential status.'),
      'employeeTimelineInfo.notes.max' => __('Notes cannot exceed :max characters.'),
      // Add messages for other rules
    ];
  }


  // Submit timeline creation or update form
  public function submitTimeline(): void // Void return type
  {
    if (!$this->employee) {
      session()->flash('error', __('Employee not loaded. Cannot save timeline.'));
      $this->dispatch('toastr', type: 'error', message: __('Error!'));
      // $this->dispatch('closeModal', elementId: '#timelineModal'); // Close modal if employee is missing?
      return;
    }

    // Validate form data using defined rules and messages
    $this->validate($this->timelineRules(), $this->timelineMessages());

    // Use a database transaction for atomicity
    try {
      DB::transaction(function () { // Use imported DB
        if ($this->isEdit) {
          $this->updateTimeline();
        } else {
          $this->storeTimeline();
        }
      });

      // Success feedback and dispatch events after successful transaction
      session()->flash('success', $this->isEdit ? __('Timeline updated successfully!') : __('Timeline added successfully!')); // Translated
      $this->dispatch('closeModal', elementId: '#timelineModal'); // Assuming JS event to close modal
      $this->dispatch('toastr', type: 'success', message: __('Going Well!')); // Assuming JS event for toastr

      // Livewire will automatically re-fetch the 'employeeTimelines' computed property
      // because the underlying data in the database has changed.

    } catch (Exception $e) { // Use imported Exception
      // Log the exception
      Log::error('Timeline submit failed: ' . $e->getMessage(), [ // Use imported Log
        'user_id' => Auth::id(), // Use imported Auth
        'employee_id' => $this->employee->id,
        'timeline_data' => $this->employeeTimelineInfo,
        'is_edit' => $this->isEdit,
        'timeline_id' => $this->timeline?->id, // Use null-safe operator
        'exception' => $e,
      ]);

      // Flash and dispatch error feedback
      session()->flash('error', __('An error occurred while saving the timeline record.')); // Generic translated error
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!')); // Assuming JS event for toastr
      // Keep modal open on error? Original closed. Let's keep original behavior.
      // If you want to keep the modal open on validation errors, remove the closeModal dispatch here.
      $this->dispatch('closeModal', elementId: '#timelineModal');
    } finally {
      // Reset modal state and clear form fields regardless of success or failure
      $this->reset('isEdit', 'timeline', 'employeeTimelineInfo');
      $this->dispatch('clearSelect2Values'); // Dispatch JS to clear select2 elements
    }
  }

  // Show modal for storing a new timeline record
  public function showStoreTimelineModal(): void // Void return type
  {
    $this->resetValidation(); // Clear validation errors
    $this->reset('isEdit', 'timeline', 'employeeTimelineInfo'); // Reset timeline modal state
    // Initialize form data with defaults if necessary
    $this->employeeTimelineInfo = [
      'center_id' => '',
      'department_id' => '',
      'position_id' => '',
      'start_date' => Carbon::now()->format('Y-m-d'), // Default start date to today
      'end_date' => '', // Default end date to empty string
      'is_sequent' => true, // Default sequential to true
      'notes' => '',
    ];
    $this->isEdit = false; // Explicitly set mode
    $this->dispatch('clearSelect2Values'); // Dispatch JS to clear select2
    // Dispatch JS event to open modal if not handled by wire:click
    // $this->dispatch('openModal', elementId: '#timelineModal');
  }

  // Store a new timeline record (called from submitTimeline)
  protected function storeTimeline(): void // Use protected as called internally, void return type
  {
    // Validation is handled in submitTimeline()

    // Find the most recent timeline for this employee
    $presentTimeline = $this->employee
      ->timelines()
      ->orderBy('start_date', 'desc')
      ->first(); // Can be null

    // Logic to close out the previous timeline IF the new one is sequential
    if ($this->employeeTimelineInfo['is_sequent']) {
      if ($presentTimeline && $presentTimeline->end_date === null) {
        // If the previous timeline is open, set its end_date to the day before the new one starts
        // Ensure start_date is a Carbon instance before subtracting days
        // Validation ensures start_date is a valid date string here.
        $startDate = Carbon::parse($this->employeeTimelineInfo['start_date']);
        // Set end_date to the end of the day *before* the new start date
        $presentTimeline->end_date = $startDate->copy()->subDay()->endOfDay();
        $presentTimeline->save();
      }
      // If previous timeline was already closed, or if no previous timeline, do nothing to it.
    }
    // If is_sequent is FALSE, we do NOT modify the end_date of the previous timeline.


    // Create the new timeline record
    Timeline::create([
      'employee_id' => $this->employee->id,
      'center_id' => $this->employeeTimelineInfo['center_id'],
      'department_id' => $this->employeeTimelineInfo['department_id'],
      'position_id' => $this->employeeTimelineInfo['position_id'],
      'start_date' => $this->employeeTimelineInfo['start_date'],
      // Set end_date to null if empty string, or parse if a date is provided
      'end_date' => $this->employeeTimelineInfo['end_date'] ? Carbon::parse($this->employeeTimelineInfo['end_date'])->endOfDay() : null, // Ensure end of day for date precision
      'is_sequent' => $this->employeeTimelineInfo['is_sequent'],
      'notes' => $this->employeeTimelineInfo['notes'] ?: null, // Store empty string as null
      // created_by might be handled by a trait or observer
      // 'created_by' => Auth::id(), // Use imported Auth
    ]);

    // Success feedback and dispatching is handled in submitTimeline() after transaction
  }

  // Show modal for updating an existing timeline record
  public function showUpdateTimelineModal(Timeline $timeline): void // Use route model binding, void return type
  {
    $this->resetValidation(); // Clear validation errors
    $this->reset('isEdit', 'timeline', 'employeeTimelineInfo'); // Reset timeline modal state

    $this->isEdit = true; // Set edit mode
    $this->timeline = $timeline; // Store the model for update

    // Populate the form fields with the record's data
    $this->employeeTimelineInfo = [
      'center_id' => $timeline->center_id,
      'department_id' => $timeline->department_id,
      'position_id' => $timeline->position_id,
      'start_date' => $timeline->start_date ? Carbon::parse($timeline->start_date)->format('Y-m-d') : '', // Format date, use empty string for null
      'end_date' => $timeline->end_date ? Carbon::parse($timeline->end_date)->format('Y-m-d') : '', // Format date, use empty string for null
      'is_sequent' => (bool) $timeline->is_sequent, // Cast to boolean for form binding
      'notes' => $timeline->notes ?? '', // Use empty string for null
    ];

    // Dispatch events to set Select2 values if used in the modal
    $this->dispatch(
      'setSelect2Values',
      centerId: $timeline->center_id,
      departmentId: $timeline->department_id,
      positionId: $timeline->position_id
    );
    // Dispatch JS event to open modal if not handled by wire:click
    // $this->dispatch('openModal', elementId: '#timelineModal');
  }

  // Update an existing timeline record (called from submitTimeline)
  protected function updateTimeline(): void // Use protected as called internally, void return type
  {
    // Validation is handled in submitTimeline()

    // Find the record again within the transaction for safety (optional if $this->timeline is reliable)
    // Or trust $this->timeline as it was loaded in showUpdateTimelineModal
    if (!$this->timeline) {
      // Should not happen if flow is correct, but defensive check
      Log::error('EmployeeInfo component: Attempted to update non-existent timeline model.', ['user_id' => Auth::id(), 'employee_id' => $this->employee?->id, 'is_edit' => $this->isEdit]);
      throw new Exception(__('Timeline record not found for update.')); // Throw exception for transaction rollback
    }

    // Update the timeline record
    $this->timeline->update([
      'center_id' => $this->employeeTimelineInfo['center_id'],
      'department_id' => $this->employeeTimelineInfo['department_id'],
      'position_id' => $this->employeeTimelineInfo['position_id'],
      'start_date' => $this->employeeTimelineInfo['start_date'],
      // Set end_date to null if empty string, or parse if a date is provided
      'end_date' => $this->employeeTimelineInfo['end_date'] ? Carbon::parse($this->employeeTimelineInfo['end_date'])->endOfDay() : null, // Ensure end of day for date precision
      'is_sequent' => $this->employeeTimelineInfo['is_sequent'],
      'notes' => $this->employeeTimelineInfo['notes'] ?: null, // Store empty string as null
      // updated_by might be handled by a trait or observer
      // 'updated_by' => Auth::id(), // Use imported Auth
    ]);

    // Success feedback and dispatching is handled in submitTimeline() after transaction
    // Reset modal state is handled in submitTimeline()
  }

  // Confirm deletion of a timeline
  // Refactored to set confirmedId first
  public function confirmDeleteTimeline(int $timelineId): void // Accept ID, void return type
  {
    $this->confirmedId = $timelineId; // Store the ID for confirmation
    // Dispatch event to show confirmation modal (e.g., SweetAlert, Bootstrap modal)
    // $this->dispatch('openConfirmModal', elementId: '#deleteTimelineConfirmationModal');
  }

  // Perform the deletion after confirmation
  // Refactored to use confirmedId for consistency
  public function deleteTimeline(): void // Void return type
  {
    // Check if an ID is confirmed
    if ($this->confirmedId === null) {
      return; // No ID to delete
    }

    // Find the record to delete
    $record = Timeline::find($this->confirmedId);

    if (!$record) {
      // Record not found (maybe already deleted by another user)
      session()->flash('error', __('Timeline record not found for deletion.'));
      $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
    } else {
      // Perform the deletion
      try {
        // Add logic here if deleting a timeline has specific consequences
        // E.g., if deleting the *last* timeline, maybe deactivate the employee or set end_date of previous one.
        // The original code just deletes the timeline.

        $record->delete(); // Use model delete method (handles soft deletes if enabled)

        session()->flash('success', __('Timeline record deleted successfully!')); // Translated
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
      } catch (Exception $e) { // Use imported Exception
        // Handle potential database errors during deletion (e.g., foreign key constraints)
        Log::error('EmployeeInfo component: Failed to delete timeline record.', ['user_id' => Auth::id(), 'employee_id' => $this->employee?->id, 'timeline_id' => $this->confirmedId, 'exception' => $e]); // Use imported Log
        session()->flash('error', __('An error occurred while deleting the timeline record.') . ' ' . $e->getMessage()); // Show exception message or generic error
        $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
      }
    }

    // Reset confirmedId after attempting deletion
    $this->confirmedId = null;
    // Dispatch event to close confirmation modal (if using one)
    // $this->dispatch('closeConfirmModal', elementId: '#deleteTimelineConfirmationModal');

    // Livewire will automatically re-fetch the 'employeeTimelines' computed property
    // because the underlying data in the database has changed.
  }


  // 👉 Set a timeline as the current/present one
  public function setPresentTimeline(int $timelineId): void // Accept ID, void return type
  {
    if (!$this->employee) {
      session()->flash('error', __('Employee not loaded. Cannot set present timeline.')); // Translated
      $this->dispatch('toastr', type: 'error', message: __('Error!'));
      return;
    }

    DB::transaction(function () use ($timelineId) { // Use transaction, pass $timelineId
      // Find the timeline to set as present
      $newPresentTimeline = $this->employee->timelines()->find($timelineId);

      if (!$newPresentTimeline) {
        // Handle case where timeline is not found
        session()->flash('error', __('Timeline record not found.')); // Translated
        $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
        return; // Exit transaction
      }

      // Check if the selected timeline is already the current one before proceeding
      if ($newPresentTimeline->end_date === null) {
        session()->flash('info', __('This timeline is already the current one.')); // Translated info message
        $this->dispatch('toastr', type: 'info', message: __('Info!'));
        return; // Exit transaction
      }

      // Find the *current* present timeline (if any) and set its end_date
      // Exclude the timeline we are about to set as present in the search for the old present one
      $currentPresentTimeline = $this->employee->timelines()->whereNull('end_date')->where('id', '!=', $timelineId)->first();


      if ($currentPresentTimeline) {
        // If there is a different current present timeline, set its end_date
        $currentPresentTimeline->end_date = Carbon::now()->endOfDay(); // Set end of day for date precision
        $currentPresentTimeline->save();
      }
      // If no current present timeline found (or if the selected one was the current one, handled above), proceed.


      // Set the selected timeline's end_date to null
      $newPresentTimeline->end_date = null;
      $newPresentTimeline->save();

      // Ensure the employee is marked as active if a timeline is set as present
      if (!$this->employee->is_active) {
        $this->employee->is_active = true;
        $this->employee->save();
      }

      session()->flash('success', __('The selected timeline has been set as the current position.')); // Translated message
      $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    }); // Transaction ends

    // Livewire will automatically re-fetch computed properties
  }

  // 👉 Helper Methods (Internal logic, not directly callable from view)

  // Add any helper methods needed here
}
