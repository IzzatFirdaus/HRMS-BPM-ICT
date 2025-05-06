<?php

namespace App\Livewire\HumanResource\Attendance;

use App\Exports\ExportLeaves; // Keep if used
use App\Imports\ImportLeaves; // Keep if used
use App\Livewire\Sections\Navbar\Navbar; // Keep if used
use App\Models\Center; // Keep if used
use App\Models\Employee; // *** IMPORTANT: Make sure Employee model is imported ***
use App\Models\EmployeeLeave; // Assume this model exists for the pivot
use App\Models\Leave; // Assume this model exists for Leave types
use App\Models\User; // For Auth::id() and potential relationships
use App\Notifications\DefaultNotification; // Keep if used
use Carbon\Carbon; // Keep if used
use Exception; // Import Exception class
use Illuminate\Database\Eloquent\Builder; // Keep if used for scopes/queries
use Illuminate\Support\Facades\Auth; // Keep if used
use Illuminate\Support\Facades\DB; // Keep if used
use Illuminate\Support\Facades\Notification; // Keep if used
use Illuminate\Support\Facades\Log; // Added logging
use Livewire\Component;
use Livewire\WithFileUploads; // Keep if used
use Livewire\WithPagination; // Keep if used
use Maatwebsite\Excel\Facades\Excel; // Keep if used
use Livewire\Attributes\Computed; // *** IMPORTANT: Import Computed attribute ***
use Livewire\Attributes\On; // Keep if used
use Livewire\Attributes\Locked; // Keep if used
use Illuminate\Validation\Rule; // For validation rules
use Illuminate\View\View; // Import View for render return type hint


// Assumptions:
// 1. EmployeeLeave is an Eloquent model representing the pivot table 'employee_leave'.
// 2. EmployeeLeave model has 'employee_id', 'leave_id', 'from_date', 'to_date', 'start_at', 'end_at', 'note', 'is_checked', 'created_by', 'updated_by' columns.
// 3. EmployeeLeave model has belongsTo relationships to Employee and Leave.
// 4. Employee model has 'first_name', 'father_name', 'last_name' attributes and a hasMany relationship to EmployeeLeave (and possibly belongsTo relationships to Department, Position, Center if used).
// 5. Leave model has 'name', 'type' ('daily', 'hourly') attributes.
// 6. The application uses Spatie Permission for roles/permissions.


class Leaves extends Component
{
  use WithPagination, WithFileUploads; // Include traits if used

  // Define public properties used in the view (e.g., for wire:model)
  public $selectedEmployeeId = null; // Assuming this property exists based on your blade code
  public $selectedLeaveId = null; // Assuming this property exists
  public $startDate = null;
  public $endDate = null;
  public $note = '';
  public $startTime = null; // For hourly leaves
  public $endTime = null; // For hourly leaves
  public $leaveFile = null; // For file uploads
  public $is_checked = false; // Assuming this column exists
  public $confirmingLeaveDeletion = false;
  public $leaveToDeleteId = null;


  // Add other public properties or component state here

  // *** FIX: Computed property for active employees to make it available to the view ***
  // This method will be automatically called by Livewire to provide the $activeEmployees variable to the view.
  #[Computed]
  public function activeEmployees()
  {
    // *** Debugging Line: Check if this method is reached ***
    Log::info('Fetching active employees from Leaves component.');

    // Fetch active employees
    // *** IMPORTANT: Adjust the query based on how you determine 'active' employees
    // and the columns you need (select only necessary columns for performance). ***
    // Make sure the Employee model exists and is accessible.
    try {
      $employees = Employee::where('status', 'active') // Example: filter by status
        ->select('id', 'first_name', 'father_name', 'last_name') // Select only needed columns
        ->orderBy('first_name') // Order the results
        ->get();
      Log::info('Successfully fetched active employees.', ['count' => $employees->count()]);
      return $employees;
    } catch (\Exception $e) {
      Log::error('Error fetching active employees: ' . $e->getMessage(), ['exception' => $e]);
      // Return empty collection or re-throw based on desired error handling
      return collect(); // Return empty collection if fetching fails
    }
  }

  // Computed property for leave types
  #[Computed]
  public function leaveTypes()
  {
    // Fetch all leave types
    return Leave::orderBy('name')->get();
  }


  // Computed property for displaying leaves based on filters
  #[Computed]
  public function leaves()
  {
    $query = EmployeeLeave::with(['employee', 'leave']); // Eager load relationships

    // Apply filters
    if ($this->selectedEmployeeId) {
      $query->where('employee_id', $this->selectedEmployeeId);
    }

    if ($this->selectedLeaveId) {
      $query->where('leave_id', $this->selectedLeaveId);
    }

    if ($this->startDate && $this->endDate) {
      // Filter leaves that overlap with the selected date range
      $query->where(function (Builder $q) {
        $q->whereBetween('from_date', [$this->startDate, $this->endDate])
          ->orWhereBetween('to_date', [$this->startDate, $this->endDate])
          ->orWhere(function (Builder $q2) {
            $q2->where('from_date', '<=', $this->startDate)
              ->where('to_date', '>=', $this->endDate);
          });
      });
    }

    // Add sorting
    $query->latest('from_date'); // Order by the start date descending

    // Return paginated results
    return $query->paginate(10); // Adjust pagination size as needed
  }


  // Lifecycle hook - runs once when the component is initialized
  public function mount()
  {
    // You might initialize default filter values here
    $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
    $this->endDate = Carbon::now()->endOfYear()->format('Y-m-d');
  }

  // This method is automatically called when the view needs to render
  public function render(): View // Added return type hint
  {
    // Using Computed properties means you don't typically need to pass
    // the data explicitly to the view via this return statement
    // Livewire automatically makes public properties and computed properties available to the view.
    return view('livewire.human-resource.attendance.leaves');

    // --- Debugging Fallback: Uncomment this block to pass explicitly if Computed property fails ---
    // try {
    //      $activeEmployees = Employee::where('status', 'active')->orderBy('first_name')->get();
    // } catch (\Exception $e) {
    //     Log::error('Error fetching active employees in render method: ' . $e->getMessage(), ['exception' => $e]);
    //     $activeEmployees = collect(); // Return empty collection on error
    // }
    //
    // return view('livewire.human-resource.attendance.leaves', [
    //     'activeEmployees' => $activeEmployees,
    //     // Pass other variables needed by the view, likely from other computed properties
    //     'leaveTypes' => $this->leaveTypes,
    //     'leaves' => $this->leaves,
    // ]);
    // --- End Debugging Fallback ---
  }

  // Example method to apply date range filter (called via wire:click or wire:submit)
  public function applyDateFilter()
  {
    $this->resetPage(); // Reset pagination when filters change
  }

  // Example method to apply employee/leave type filters
  public function applyFilters()
  {
    $this->resetPage(); // Reset pagination when filters changes
  }


  // Method to determine if a leave type is hourly (used in the view)
  public function isHourly($leaveTypeId)
  {
    // Access the computed property and find the leave type
    $leave = $this->leaveTypes->firstWhere('id', $leaveTypeId);
    // Check if the leave type exists and its 'type' attribute is 'hourly'
    return $leave ? $leave->type === 'hourly' : false;
  }


  // Method to handle saving a new leave request
  public function saveLeave()
  {
    // *** IMPORTANT: Implement validation rules here ***
    $rules = [
      'selectedEmployeeId' => 'required|exists:employees,id',
      'selectedLeaveId' => 'required|exists:leaves,id',
      'startDate' => 'required|date',
      'endDate' => 'required|date|after_or_equal:startDate',
      'note' => 'nullable|string',
      'is_checked' => 'boolean', // Assuming this is a checkbox value
      'leaveFile' => 'nullable|file|max:1024', // Max 1MB
      // Conditional validation for start_time and end_time if leave type is hourly
      'startTime' => Rule::requiredIf($this->isHourly($this->selectedLeaveId)) . '|nullable|date_format:H:i',
      'endTime' => Rule::requiredIf($this->isHourly($this->selectedLeaveId)) . '|nullable|date_format:H:i|after:startTime',
    ];

    $this->validate($rules); // Run validation

    DB::beginTransaction();
    try {
      $employeeLeave = new EmployeeLeave();
      $employeeLeave->employee_id = $this->selectedEmployeeId;
      $employeeLeave->leave_id = $this->selectedLeaveId;
      $employeeLeave->from_date = $this->startDate;
      $employeeLeave->to_date = $this->endDate;
      $employeeLeave->note = $this->note;
      $employeeLeave->is_checked = $this->is_checked;

      // Handle hourly times if applicable
      if ($this->isHourly($this->selectedLeaveId)) {
        $employeeLeave->start_at = $this->startTime;
        $employeeLeave->end_at = $this->endTime;
      } else {
        // Ensure hourly times are null for daily leaves
        $employeeLeave->start_at = null;
        $employeeLeave->end_at = null;
      }

      // Handle file upload if applicable
      if ($this->leaveFile) {
        $fileName = $this->leaveFile->store('leaves', 'public'); // Store in 'storage/app/public/leaves'
        $employeeLeave->file_path = $fileName; // Save the path in the database
      }

      // Set audit columns (assuming trait handles created_by, updated_by)
      // The trait should set these automatically on save if you're using it correctly.
      // If not, you might need to set them manually:
      // $employeeLeave->created_by = Auth::id(); // Or Auth::user() if trait expects model

      $employeeLeave->save(); // Save the record

      DB::commit();

      session()->flash('message', __('Leave request saved successfully.'));
      $this->resetForm(); // Reset form fields after successful save
      $this->dispatch('leaveSaved'); // Dispatch browser event for any JS listeners (e.g., close modal)

    } catch (Exception $e) { // Catch imported Exception
      DB::rollBack();
      Log::error('Error saving leave request: ' . $e->getMessage(), ['exception' => $e]);
      session()->flash('error', __('An error occurred while saving the leave request: ') . $e->getMessage());
      // You might want to dispatch an event to show an error message on the frontend
      // $this->dispatch('show-error-message', ['message' => __('An error occurred...')]);
    }
  }

  // Method to confirm deletion
  public function confirmDelete($leaveId)
  {
    $this->confirmingLeaveDeletion = true;
    $this->leaveToDeleteId = $leaveId;
  }

  // Method to delete a leave request
  public function deleteLeave()
  {
    if ($this->leaveToDeleteId === null) {
      return; // Should not happen if confirmDelete is called first
    }

    DB::beginTransaction();
    try {
      $leaveRequest = EmployeeLeave::findOrFail($this->leaveToDeleteId);

      // Optional: Add authorization check here
      // $this->authorize('delete', $leaveRequest); // Assuming a policy exists

      // Optional: Delete associated file if it exists
      // if ($leaveRequest->file_path) {
      //     Storage::disk('public')->delete($leaveRequest->file_path);
      // }

      $leaveRequest->delete(); // Soft deletes the record
      // Note: If using soft deletes, the record isn't truly removed from the DB.

      DB::commit();

      session()->flash('message', __('Leave request deleted successfully.'));
      $this->confirmingLeaveDeletion = false; // Close confirmation modal/section
      $this->leaveToDeleteId = null; // Reset ID
      $this->dispatch('leaveDeleted'); // Dispatch event
      // No need to manually refresh $leaves computed property, Livewire handles it on subsequent renders.

    } catch (Exception $e) { // Catch imported Exception
      DB::rollBack();
      Log::error('Error deleting leave request ID ' . $this->leaveToDeleteId . ': ' . $e->getMessage(), ['exception' => $e]);
      session()->flash('error', __('An error occurred while deleting the leave request: ') . $e->getMessage());
    }
  }


  // Helper method to reset form fields
  protected function resetForm()
  {
    $this->reset([
      'selectedEmployeeId',
      'selectedLeaveId',
      'startDate',
      'endDate',
      'note',
      'startTime',
      'endTime',
      'leaveFile',
      'is_checked',
    ]);
    // Re-initialize default dates if needed
    $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
    $this->endDate = Carbon::now()->endOfYear()->format('Y-m-d');
  }


  // 痩 Helper Methods (Internal logic, not directly callable from view)

  // Check if a Leave type is hourly (Redundant if using the isHourly($leaveTypeId) method)
  // Keeping for completeness based on potential original code structure.
  protected function isHourlyLeave(?int $leaveId): bool
  {
    if ($leaveId === null) {
      return false; // Cannot be hourly if no leave type selected
    }
    // Access computed property for leave types and find the selected one
    $leave = $this->leaveTypes->firstWhere('id', $leaveId);
    // Assuming 'type' attribute exists on Leave model ('daily' or 'hourly')
    return $leave ? $leave->type === 'hourly' : false;
  }

  // Check if a Leave type is daily (Redundant if using the isHourly($leaveTypeId) method)
  // Keeping for completeness based on potential original code structure.
  protected function isDailyLeave(?int $leaveId): bool
  {
    if ($leaveId === null) {
      return false; // Cannot be daily if no leave type selected
    }
    $leave = $this->leaveTypes->firstWhere('id', $leaveId);
    // Assuming 'type' attribute exists on Leave model ('daily' or 'hourly')
    return $leave ? $leave->type === 'daily' : false;
  }

  // *** Placeholder for potential code causing the type hint error that was at line 526 previously ***
  // If you previously had an error related to passing an integer where a User model was expected
  // around line 526 in your original Leaves.php file, you need to address that specific code here.
  // Example: If you were setting a relationship: $model->user_relation()->associate(Auth::id()); // This causes the error
  // FIX: $model->user_relation()->associate(Auth::user()); // Pass the User model instance
  // Or if setting a foreign key directly: $model->created_by = Auth::id(); // This is usually fine if DB column is integer
  // but if a method you call expects a User model, pass Auth::user() instead of Auth::id().
  // Check the code around that line in your original file and apply the fix in this updated code.


  // Example of a method that might be called for exporting leaves, which could have caused
  // a User model type hint error if passing Auth::id() where a User was expected.
  // public function exportLeaves()
  // {
  //     try {
  //          // Fetch the data to export, applying current filters if needed
  //          $leavesToExport = $this->leaves->items(); // Get the collection from the paginator
  //          // Or refetch data without pagination if you need all results for export
  //          // $leavesToExport = EmployeeLeave::with(['employee', 'leave'])->filter($this->filters)->get(); // Assuming a filter method/scope exists

  //         Log::info('Exporting Leaves report.', [
  //             'user_id' => Auth::id(), // This is typically okay if the log accepts int
  //             'user_email' => Auth::user()->email, // Accessing user model property is fine
  //             'filters' => [
  //                 'employee_id' => $this->selectedEmployeeId,
  //                 'leave_id' => $this->selectedLeaveId,
  //                 'date_range' => [$this->startDate, $this->endDate],
  //             ],
  //         ]);

  //         session()->flash('success', __('Well done! The file has been exported successfully.'));
  //         $this->dispatch('toastr', type: 'success', message: __('Going Well!')); // Dispatch toastr on success

  //         // Generate filename
  //         $filename = 'Leaves - ' . Auth::user()->name . ' - ' .
  //           Carbon::now()->subDays(7)->format('Y-m-d') . ' --- ' . // Adjust date range for filename as needed
  //           Carbon::now()->format('Y-m-d') . '.xlsx';

  //         // Return the Excel download response
  //         return Excel::download(
  //           new ExportLeaves($leavesToExport), // Assuming ExportLeaves accepts the collection
  //           $filename
  //         );

  //     } catch (Exception $e) { // Catch imported Exception
  //         Log::error('Error exporting leaves report: ' . $e->getMessage(), ['exception' => $e]);
  //         session()->flash('error', __('An error occurred while exporting the report: ') . $e->getMessage());
  //         // You might want to dispatch an event to show an error message on the frontend
  //         // $this->dispatch('show-error-message', ['message' => __('An error occurred during export.')]);
  //         // You might return null or a specific error response if not a download method
  //         return null;
  //     }
  // }


}
