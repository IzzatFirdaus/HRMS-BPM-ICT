<?php

namespace App\Livewire\HumanResource\Structure;

use App\Models\Center;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Timeline;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate; // If needed for form validation
use Livewire\Component;
use Illuminate\Support\Collection; // For type hinting collections
use Illuminate\Support\Facades\Auth;


class EmployeeInfo extends Component
{
  // Public property to hold the employee model instance, loaded via Route Model Binding
  public Employee $employee;

  // State properties for the Timeline Modal
  public bool $showTimelineModal = false;
  public bool $isEditTimeline = false;
  public ?Timeline $timeline = null; // To hold the timeline instance being edited

  // Properties for the timeline form fields (prefixed with timeline_ to avoid conflicts)
  public ?string $timeline_start_date = null;
  public ?string $timeline_end_date = null;
  public ?int $timeline_center_id = null;
  public ?int $timeline_department_id = null;
  public ?int $timeline_position_id = null;


  /**
   * Mount the component and load the employee using Route Model Binding.
   * Eager loads necessary relationships for the view.
   */
  public function mount(Employee $employee): void
  {
    $this->employee = $employee;
    // Eager load relationships needed in the view to avoid N+1 query issues.
    // This includes relationships directly on Employee and nested relationships on Timelines.
    $this->employee->load('department', 'position', 'grade', 'timelines.center', 'timelines.department', 'timelines.position');

    Log::info('EmployeeInfo component mounted for employee ID: ' . $employee->id);
  }

  /**
   * Computed property to get ordered timelines for the employee.
   * Available as $this->employeeTimelines in the view.
   */
  #[Computed]
  public function employeeTimelines(): Collection
  {
    // Access the loaded employee's timelines relationship and order them.
    return $this->employee->timelines()->orderBy('start_date', 'desc')->orderBy('end_date', 'desc')->get();
  }


  /**
   * Computed property to get all Centers for the timeline modal dropdown.
   */
  #[Computed]
  public function centers(): Collection
  {
    try {
      return Center::orderBy('name')->get();
    } catch (Exception $e) {
      Log::error('Error fetching centers for EmployeeInfo timeline modal: ' . $e->getMessage());
      return collect();
    }
  }

  /**
   * Computed property to get all Departments for the timeline modal dropdown.
   */
  #[Computed]
  public function departments(): Collection
  {
    try {
      return Department::orderBy('name')->get();
    } catch (Exception $e) {
      Log::error('Error fetching departments for EmployeeInfo timeline modal: ' . $e->getMessage());
      return collect();
    }
  }

  /**
   * Computed property to get all Positions for the timeline modal dropdown.
   */
  #[Computed]
  public function positions(): Collection
  {
    try {
      return Position::orderBy('name')->get();
    } catch (Exception $e) {
      Log::error('Error fetching positions for EmployeeInfo timeline modal: ' . $e->getMessage());
      return collect();
    }
  }


  /**
   * Render the component view.
   */
  public function render(): \Illuminate\View\View
  {
    // The public $employee property and computed properties are automatically available.
    return view('livewire.human-resource.structure.employee-info');
  }

  /**
   * Show modal for adding a new timeline entry.
   */
  public function showNewTimelineModal(): void
  {
    $this->resetTimelineForm();
    $this->isEditTimeline = false;
    $this->showTimelineModal = true;
    $this->dispatch('timelineModalShown'); // Dispatch JS event for modal initialization
    Log::info('New timeline modal shown for employee ID: ' . $this->employee->id);
  }

  /**
   * Show modal for editing an existing timeline entry.
   */
  public function showEditTimelineModal(int $timelineId): void
  {
    $this->resetTimelineForm();
    $this->isEditTimeline = true;

    $timelineToEdit = $this->employee->timelines()->find($timelineId);

    if (!$timelineToEdit) {
      Log::warning('Attempted to edit non-existent timeline.', ['employee_id' => $this->employee->id, 'timeline_id' => $timelineId, 'user_id' => Auth::id()]);
      session()->flash('error', __('Timeline not found.'));
      $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
      return;
    }

    $this->timeline = $timelineToEdit;
    // Populate form fields from the timeline model
    $this->timeline_start_date = $this->timeline->start_date?->format('Y-m-d');
    $this->timeline_end_date = $this->timeline->end_date?->format('Y-m-d');
    $this->timeline_center_id = $this->timeline->center_id;
    $this->timeline_department_id = $this->timeline->department_id;
    $this->timeline_position_id = $this->timeline->position_id;

    $this->showTimelineModal = true;
    $this->dispatch('timelineModalShown'); // Dispatch JS event for modal initialization
    Log::info('Edit timeline modal shown for timeline ID: ' . $timelineId . ' for employee ID: ' . $this->employee->id);
  }

  /**
   * Save a new or update an existing timeline entry.
   * Includes validation.
   */
  public function saveTimeline(): void
  {
    $rules = [
      'timeline_start_date' => ['required', 'date'],
      'timeline_end_date' => ['nullable', 'date', 'after_or_equal:timeline_start_date'],
      'timeline_center_id' => ['nullable', 'exists:centers,id'],
      'timeline_department_id' => ['nullable', 'exists:departments,id'],
      'timeline_position_id' => ['nullable', 'exists:positions,id'],
    ];

    $this->validate($rules);

    DB::beginTransaction();
    try {
      $data = [
        'employee_id' => $this->employee->id,
        'start_date' => $this->timeline_start_date,
        'end_date' => $this->timeline_end_date,
        'center_id' => $this->timeline_center_id,
        'department_id' => $this->timeline_department_id,
        'position_id' => $this->timeline_position_id,
      ];

      if ($this->isEditTimeline && $this->timeline) {
        $this->timeline->update($data);
        Log::info('Timeline updated.', ['timeline_id' => $this->timeline->id, 'employee_id' => $this->employee->id, 'user_id' => Auth::id()]);
        session()->flash('success', __('Timeline updated successfully!'));
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
      } else {
        $timeline = $this->employee->timelines()->create($data);
        Log::info('Timeline created.', ['timeline_id' => $timeline->id, 'employee_id' => $this->employee->id, 'user_id' => Auth::id()]);
        session()->flash('success', __('Timeline saved successfully!'));
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
      }

      DB::commit();

      $this->showTimelineModal = false;
      $this->resetTimelineForm();
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Error saving timeline for employee ID ' . $this->employee->id . ': ' . $e->getMessage());
      session()->flash('error', __('An error occurred while saving the timeline: ') . $e->getMessage());
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
    }
  }

  /**
   * Confirm deletion of a timeline entry.
   */
  public function confirmDeleteTimeline(int $timelineId): void
  {
    $this->timeline = $this->employee->timelines()->find($timelineId);
    if (!$this->timeline) {
      Log::warning('Attempted to confirm deletion of non-existent timeline.', ['employee_id' => $this->employee->id, 'timeline_id' => $timelineId, 'user_id' => Auth::id()]);
      session()->flash('error', __('Timeline not found for deletion.'));
      $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
      return;
    }
    Log::info('Confirm delete initiated for timeline ID: ' . $timelineId . ' for employee ID: ' . $this->employee->id);
    // TODO: Dispatch JS event to show confirmation modal
  }

  /**
   * Delete the confirmed timeline entry (soft delete).
   */
  public function deleteTimeline(): void
  {
    if (is_null($this->timeline)) {
      Log::warning('Attempted to delete timeline without selecting one.', ['employee_id' => $this->employee->id, 'user_id' => Auth::id()]);
      session()->flash('error', __('No timeline selected for deletion.'));
      $this->dispatch('toastr', type: 'warning', message: __('No Selection!'));
      return;
    }

    DB::beginTransaction();
    try {
      $timelineIdToDelete = $this->timeline->id;
      $this->timeline->delete();

      DB::commit();

      Log::info('Timeline deleted successfully.', ['timeline_id' => $timelineIdToDelete, 'employee_id' => $this->employee->id, 'user_id' => Auth::id()]);
      session()->flash('success', __('Timeline deleted successfully!'));
      $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Error deleting timeline ID ' . ($this->timeline->id ?? 'unknown') . ' for employee ID ' . $this->employee->id . ': ' . $e->getMessage());
      session()->flash('error', __('An error occurred while deleting the timeline: ') . $e->getMessage());
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
    }

    $this->resetTimelineForm();
  }

  /**
   * Set the selected timeline as the employee's present (current) timeline.
   * Sets the selected timeline's end_date to null and sets the end_date
   * of any previously present timeline for this employee.
   */
  public function setPresentTimeline(int $timelineId): void
  {
    DB::transaction(function () use ($timelineId) {
      $newPresentTimeline = $this->employee->timelines()->find($timelineId);

      if (!$newPresentTimeline) {
        Log::warning('Attempted to set non-existent timeline as present.', ['employee_id' => $this->employee->id, 'timeline_id' => $timelineId, 'user_id' => Auth::id()]);
        session()->flash('error', __('Timeline not found.') . ' ' . __('Operation Failed!'));
        $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
        return;
      }

      $currentPresentTimeline = $this->employee->timelines()->whereNull('end_date')->where('id', '!=', $timelineId)->first();

      if ($currentPresentTimeline) {
        $currentPresentTimeline->end_date = Carbon::now()->endOfDay();
        $currentPresentTimeline->save();
      }

      $newPresentTimeline->end_date = null;
      $newPresentTimeline->save();

      if (!$this->employee->is_active) {
        $this->employee->is_active = true;
        $this->employee->save();
      }

      session()->flash('success', __('The selected timeline has been set as the current position.'));
      $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
    });
  }

  /**
   * Resets the timeline form properties and validation errors.
   * Listens for 'hide-timeline-modal' event from JS.
   */
  #[On('hide-timeline-modal')]
  public function resetTimelineForm(): void
  {
    $this->reset([
      'showTimelineModal',
      'isEditTimeline',
      'timeline',
      'timeline_start_date',
      'timeline_end_date',
      'timeline_center_id',
      'timeline_department_id',
      'timeline_position_id',
    ]);

    $this->resetValidation();
    Log::info('Timeline form reset for employee ID: ' . ($this->employee->id ?? 'N/A'));
  }
}
