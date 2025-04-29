<?php

namespace App\Livewire\HumanResource\Attendance;

use App\Exports\ExportLeaves;
use App\Imports\ImportLeaves;
use App\Livewire\Sections\Navbar\Navbar;
use App\Models\Center;
use App\Models\Employee;
use App\Models\EmployeeLeave; // Assume this model exists for the pivot
use App\Models\Leave;
use App\Models\User; // For Auth::id() and potential relationships
use App\Notifications\DefaultNotification;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log; // Added logging
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Locked;
use Illuminate\Validation\Rule; // For validation rules


// Assumptions:
// 1. EmployeeLeave is an Eloquent model representing the pivot table 'employee_leave'.
// 2. EmployeeLeave model has 'employee_id', 'leave_id', 'from_date', 'to_date', 'start_at', 'end_at', 'note', 'is_checked', 'created_by', 'updated_by' columns.
// 3. EmployeeLeave model has belongsTo relationships to Employee and Leave.
// 4. Employee model has 'first_name', 'last_name' attributes and a hasMany relationship to EmployeeLeave (and possibly belongsToMany to Leave).
// 5. Leave model has 'name' attribute and a 'type' column (e.g., 'daily', 'hourly').
// 6. 'created_by' and 'updated_by' columns in 'employee_leave' store the User ID (Auth::id()).
// 7. Center model has an activeEmployees() method that returns a collection with 'id' and other attributes.
// 8. User model has hasAnyRole() method and 'employee_id', 'name' attributes.
// 9. The notifications, Excel exports, and imports work correctly based on data structure.
// 10. Livewire v3+ is used for #[Computed], #[On], #[Locked] attributes.

class Leaves extends Component
{
  use WithFileUploads, WithPagination;

  // Pagination specific setting
  protected $paginationTheme = 'bootstrap'; // Assuming Bootstrap pagination styling is used in the view

  // 👉 State Variables (Public properties that sync with the view)

  public $selectedEmployeeId; // Filter: Employee
  public $selectedLeaveId; // Filter: Leave Type
  public $dateRange; // Filter: Date range input string

  public array $newLeaveInfo = [ // State for create/edit form
    'leave_id' => '',
    'from_date' => '',
    'to_date' => '',
    'start_at' => '', // Use empty string for nullable time inputs
    'end_at' => '',   // Use empty string for nullable time inputs
    'note' => '',
  ];

  public bool $isEdit = false; // State: Is the modal in edit mode
  public ?int $employeeLeaveId = null; // State: ID of the leave record being edited

  public ?int $confirmedId = null; // State: ID of the leave record pending deletion confirmation

  #[Locked] // Prevent external manipulation via view
  public $file; // State: Uploaded file for import

  // 👉 Computed Properties (Livewire v3+ - Cached data that depends on state)

  #[Computed]
  public function leaveTypes(): \Illuminate\Database\Eloquent\Collection
  {
    // Cache all leave types
    return Leave::all();
  }

  #[Computed]
  public function activeEmployees(): \Illuminate\Support\Collection
  {
    // Fetch active employees for the logged-in user's current center
    // This logic is kept as per the original mount method
    $user = Employee::find(Auth::user()->employee_id);

    if (!$user) {
      Log::warning('Leaves component: Authenticated user has no associated employee record for fetching active employees.', ['user_id' => Auth::id()]);
      // Return empty collection gracefully
      return collect();
    }

    // Find the current timeline for the employee
    $currentTimeline = $user->timelines()->where('end_date', null)->first();

    if (!$currentTimeline || !$currentTimeline->center_id) {
      Log::info('Leaves component: Authenticated employee has no active timeline or center for fetching active employees.', ['user_id' => Auth::id(), 'employee_id' => $user->id]);
      // Return empty collection gracefully
      return collect();
    }

    // Find the center
    $center = Center::find($currentTimeline->center_id);

    if (!$center) {
      Log::warning('Leaves component: Center found in timeline does not exist.', ['user_id' => Auth::id(), 'center_id' => $currentTimeline->center_id]);
      // Return empty collection gracefully
      return collect();
    }

    // Assuming activeEmployees() method on Center returns a Collection/array of employees
    // E.g., $center->employees()->where('status', 'active')->get()
    return $center->activeEmployees();
  }

  #[Computed]
  public function selectedEmployee(): ?Employee
  {
    // Fetch the selected employee model if an ID is set
    return $this->selectedEmployeeId ? Employee::find($this->selectedEmployeeId) : null;
  }

  #[Computed]
  public function selectedLeave(): ?Leave
  {
    // Fetch the selected leave type model if an ID is set
    return $this->selectedLeaveId ? Leave::find($this->selectedLeaveId) : null;
  }

  #[Computed]
  public function leaves(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
  {
    // This computed property replaces the render/applyFilter logic
    // It will be automatically re-evaluated when its dependencies ($selectedEmployeeId, $selectedLeaveId, $dateRange, pagination page) change

    // Parse date range string into Carbon dates
    $fromDate = null;
    $toDate = null;
    if ($this->dateRange) {
      $dates = explode(' to ', $this->dateRange);
      if (count($dates) === 2) {
        try {
          $fromDate = Carbon::parse(trim($dates[0]))->startOfDay();
          $toDate = Carbon::parse(trim($dates[1]))->endOfDay();
        } catch (\Exception $e) {
          // Handle invalid date format gracefully, maybe log and ignore filter
          Log::warning('Leaves component: Invalid dateRange format received: ' . $this->dateRange, ['exception' => $e]);
          $fromDate = null;
          $toDate = null;
        }
      }
    }

    // Start with a base query on the EmployeeLeave model
    $query = EmployeeLeave::query()
      ->with(['employee', 'leave']) // Eager load relationships
      ->orderBy('from_date', 'desc'); // Default order

    // Filter by Employee if selected
    if ($this->selectedEmployeeId) {
      $query->where('employee_id', $this->selectedEmployeeId);
    } else {
      // Default to logged-in user's employee ID if no employee filter is explicitly set
      $query->where('employee_id', Auth::user()->employee_id);
    }

    // Filter by Leave Type if selected
    if ($this->selectedLeaveId) {
      $query->where('leave_id', $this->selectedLeaveId);
    }

    // Filter by Date Range (checking for overlap)
    if ($fromDate && $toDate) {
      // Find leaves that *overlap* with the selected date range
      // A leave (from_date, to_date) overlaps with range (fromDate, toDate) if:
      // (from_date <= toDate AND to_date >= fromDate)
      $query->where(function ($q) use ($fromDate, $toDate) {
        $q->where('from_date', '<=', $toDate)
          ->where('to_date', '>=', $fromDate);
      });
      // Note: The original code used whereBetween('from_date', [$this->fromDate, $this->toDate]),
      // which only selects leaves *starting* within the range. The logic above finds *overlapping* leaves.
      // If original logic was intentional, revert to: $query->whereBetween('from_date', [$fromDate, $toDate]);
    }


    // Apply Role-based Filtering (Admin/HR vs. others)
    // Admin/HR see all leaves for the selected employee within the date range
    // Others only see their own unchecked leaves within the date range
    if (! Auth::user()->hasAnyRole(['Admin', 'HR'])) {
      // Non-admins/HR only see their own unchecked leaves.
      // The employee_id filter above already restricts to their own leaves (as it defaults to Auth::user()->employee_id)
      $query->where('is_checked', 0);
    }

    // Return paginated results
    return $query->paginate(7);
  }

  // 👉 Lifecycle Hooks

  public function mount()
  {
    // Set initial selected employee to the logged-in user's employee ID
    $this->selectedEmployeeId = Auth::user()->employee_id;

    // Initialize date range input string
    $currentDate = Carbon::now();
    $previousMonth = $currentDate->copy()->subMonth();
    // Format date range string for the view input, matching expected format (e.g., YYYY-MM-DD to YYYY-MM-DD)
    $this->dateRange = $previousMonth->format('Y-m-d') . ' to ' . $currentDate->format('Y-m-d');

    // Initial data fetch is implicitly handled by the 'leaves' computed property when the component renders
  }

  // No explicit updated methods are needed for $selectedEmployeeId, $selectedLeaveId, $dateRange
  // because the 'leaves' computed property depends on them and will re-evaluate automatically.
  // Pagination reset is handled by Livewire when dependencies change.

  // Render method simply returns the view
  public function render()
  {
    return view('livewire.human-resource.attendance.leaves'); // No need to pass data explicitly if using Computed properties
  }


  // 👉 Leave Management Actions

  // Validation rules
  protected function rules()
  {
    // Fetch the selected Leave model to check its type for conditional validation
    // Access computed property for leave types and find the selected one
    $selectedLeaveModel = $this->leaveTypes->firstWhere('id', $this->newLeaveInfo['leave_id']);

    $rules = [
      'selectedEmployeeId' => 'required', // Employee must be selected
      'newLeaveInfo.leave_id' => 'required', // Leave type must be selected
      'newLeaveInfo.from_date' => 'required|date', // From date is required and valid date
      'newLeaveInfo.to_date' => 'required|date|after_or_equal:newLeaveInfo.from_date', // To date required, valid date, and >= from date
      'newLeaveInfo.note' => 'nullable|string|max:500', // Note is optional string, max 500 chars
      'newLeaveInfo.start_at' => 'nullable', // Start time is nullable initially
      'newLeaveInfo.end_at' => 'nullable', // End time is nullable initially
    ];

    // Conditional validation based on leave type (daily/hourly) if the leave type model is found
    if ($selectedLeaveModel) {
      // Assuming 'type' attribute exists on Leave model ('daily' or 'hourly')
      if ($this->isHourlyLeave($selectedLeaveModel->id)) { // Use helper method
        // For hourly leave, start_at and end_at are required and must be valid times
        $rules['newLeaveInfo.start_at'] = 'required|date_format:H:i';
        $rules['newLeaveInfo.end_at'] = 'required|date_format:H:i|after:newLeaveInfo.start_at';
        // Hourly leave must also be on the same day
        $rules['newLeaveInfo.to_date'] = $rules['newLeaveInfo.to_date'] . '|same:newLeaveInfo.from_date'; // Add 'same' rule
      } elseif ($this->isDailyLeave($selectedLeaveModel->id)) { // Use helper method
        // For daily leave, start_at and end_at must be empty/null
        // Using 'prohibits' rule ensures they are not present if the other is present, but doesn't force them to be null if both are absent.
        // A simpler check is often sufficient: ensure they are null or empty strings in the submit method if the type is daily.
        // Or validate they are not filled:
        $rules['newLeaveInfo.start_at'] = 'nullable|prohibits:newLeaveInfo.start_at'; // Cannot be filled if daily
        $rules['newLeaveInfo.end_at'] = 'nullable|prohibits:newLeaveInfo.end_at'; // Cannot be filled if daily
        // Note: 'prohibits' prevents the field from being present AT ALL if the other is present.
        // Maybe 'required_if' is better here in reverse, or simply checking in submitLeave
        // Let's add checks in submitLeave for clarity.
      }
      // Add other leave types if necessary
    }

    return $rules;
  }

  // Custom validation messages
  protected function messages()
  {
    // Define custom messages, use __() for translation
    return [
      'selectedEmployeeId.required' => __('Please select an employee.'),
      'newLeaveInfo.leave_id.required' => __('Please select a leave type.'),
      'newLeaveInfo.from_date.required' => __('"From Date" is required.'),
      'newLeaveInfo.from_date.date' => __('"From Date" must be a valid date.'),
      'newLeaveInfo.to_date.required' => __('"To Date" is required.'),
      'newLeaveInfo.to_date.date' => __('"To Date" must be a valid date.'),
      'newLeaveInfo.to_date.after_or_equal' => __('"From Date" cannot be greater than "To Date".'),
      'newLeaveInfo.to_date.same' => __('Hourly leave must be on the same day.'),
      'newLeaveInfo.start_at.required_if' => __('Start time is required for hourly leave.'), // If using required_if
      'newLeaveInfo.end_at.required_if' => __('End time is required for hourly leave.'), // If using required_if
      'newLeaveInfo.start_at.date_format' => __('"Start At" must be in HH:MM format.'),
      'newLeaveInfo.end_at.date_format' => __('"End At" must be in HH:MM format.'),
      'newLeaveInfo.end_at.after' => __('"Start At" cannot be greater than "End At".'),
      'newLeaveInfo.start_at.prohibits' => __('Start time is not allowed for daily leave.'), // If using prohibits
      'newLeaveInfo.end_at.prohibits' => __('End time is not allowed for daily leave.'), // If using prohibits
      'newLeaveInfo.note.string' => __('Notes must be text.'),
      'newLeaveInfo.note.max' => __('Notes cannot exceed :max characters.'),
      // Add messages for other rules/fields as needed
    ];
  }


  // Submit the leave creation or update form
  public function submitLeave()
  {
    // Validate the form data using the rules() method
    $this->validate();

    // Fetch the selected Leave model again to re-verify type for submission logic
    $selectedLeaveModel = $this->leaveTypes->firstWhere('id', $this->newLeaveInfo['leave_id']);

    // Additional checks based on leave type (if not fully covered by rules)
    if ($selectedLeaveModel) {
      if ($this->isDailyLeave($selectedLeaveModel->id)) {
        // Ensure start_at and end_at are nullified for daily leave upon submission
        $this->newLeaveInfo['start_at'] = null;
        $this->newLeaveInfo['end_at'] = null;
      }
      // If hourly, rules already check dates are same and times are present/valid
    }


    // Use a database transaction for atomicity (all or nothing)
    try {
      DB::transaction(function () {
        if ($this->isEdit) {
          $this->updateLeave();
        } else {
          $this->createLeave();
        }
      });

      // Success feedback and dispatch events after successful transaction
      session()->flash('success', $this->isEdit ? __('Success, record updated successfully!') : __('Success, record created successfully!'));
      $this->dispatch('scrollToTop'); // Assuming a JS event to scroll
      $this->dispatch('closeModal', elementId: '#leaveModal'); // Assuming a JS event to close modal
      $this->dispatch('toastr', type: 'success', message: __('Going Well!')); // Assuming a JS event for toastr

    } catch (\Exception $e) {
      // Log the exception
      Log::error('Leave submit failed: ' . $e->getMessage(), [
        'user_id' => Auth::id(),
        'leave_data' => $this->newLeaveInfo,
        'is_edit' => $this->isEdit,
        'employee_leave_id' => $this->employeeLeaveId,
      ]);

      // Flash and dispatch error feedback
      session()->flash('error', __('An error occurred while saving the leave record.') . ' ' . $e->getMessage()); // Show exception message for debugging, or a generic message
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!')); // Assuming a JS event for toastr
      // Keep modal open on error? Or close? Original closed. Let's keep original behavior.
      $this->dispatch('closeModal', elementId: '#leaveModal');
    } finally {
      // Reset modal state and clear form fields regardless of success or failure
      $this->reset('isEdit', 'newLeaveInfo', 'employeeLeaveId');
      $this->dispatch('clearSelect2Values'); // Dispatch JS to clear select2 elements
    }
  }


  // Create a new leave record
  protected function createLeave(): void // Use protected as called internally, void return type
  {
    // Assumes EmployeeLeave model exists and is fillable/guarded correctly
    // created_by/updated_by might be handled by a trait or observer
    EmployeeLeave::create([
      'employee_id' => $this->selectedEmployeeId,
      'leave_id' => $this->newLeaveInfo['leave_id'],
      'from_date' => $this->newLeaveInfo['from_date'],
      'to_date' => $this->newLeaveInfo['to_date'],
      'start_at' => $this->newLeaveInfo['start_at'] ?: null, // Store empty strings from input as null
      'end_at' => $this->newLeaveInfo['end_at'] ?: null,     // Store empty strings from input as null
      'note' => $this->newLeaveInfo['note'] ?: null,       // Store empty strings from input as null
      'created_by' => Auth::id(), // Assuming created_by stores User ID
      'is_checked' => 0, // Assuming new leaves are unchecked by default
    ]);

    // Success feedback and dispatching is handled in submitLeave()
  }

  // Show modal for updating a leave record
  public function showUpdateLeaveModal(int $employeeLeaveId): void // Accept ID, void return type
  {
    $this->reset('newLeaveInfo'); // Reset form fields only

    $this->isEdit = true;
    $this->employeeLeaveId = $employeeLeaveId; // Store the ID of the record being edited

    // Use the EmployeeLeave model to find the record
    $record = EmployeeLeave::find($this->employeeLeaveId);

    if (!$record) {
      // Handle case where record is not found (e.g., deleted by another user)
      session()->flash('error', __('Leave record not found for editing.'));
      $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
      // Dispatch JS to close the modal if it was triggered to open
      $this->dispatch('closeModal', elementId: '#leaveModal');
      return;
    }

    // Populate the form fields with the record's data
    $this->selectedEmployeeId = $record->employee_id; // Also update employee filter dropdown if it exists and is tied to this property
    $this->newLeaveInfo = [
      'leave_id' => $record->leave_id,
      'from_date' => $record->from_date ? Carbon::parse($record->from_date)->format('Y-m-d') : '', // Format date for input
      'to_date' => $record->to_date ? Carbon::parse($record->to_date)->format('Y-m-d') : '',     // Format date for input
      'start_at' => $record->start_at ?? '', // Use empty string for null time
      'end_at' => $record->end_at ?? '',     // Use empty string for null time
      'note' => $record->note ?? '',         // Use empty string for null note
    ];

    // Dispatch events to set Select2 values if used in the modal
    $this->dispatch('setSelect2Values', employeeId: $this->selectedEmployeeId, leaveId: $record->leave_id);
    // Dispatch JS event to open the modal if not handled by wire:click
    // $this->dispatch('openModal', elementId: '#leaveModal');
  }

  // Update an existing leave record
  protected function updateLeave(): void // Use protected as called internally, void return type
  {
    // Find the record again within the transaction for safety
    $employeeLeave = EmployeeLeave::find($this->employeeLeaveId);

    if (!$employeeLeave) {
      // Should ideally not happen if showUpdateLeaveModal was called, but defensive check
      Log::error('Leaves component: Attempted to update non-existent leave record.', ['user_id' => Auth::id(), 'employee_leave_id' => $this->employeeLeaveId]);
      throw new Exception(__('Leave record not found for update.')); // Throw exception to trigger transaction rollback and error handling
    }

    // Update the record attributes
    $employeeLeave->update([
      'employee_id' => $this->selectedEmployeeId,
      'leave_id' => $this->newLeaveInfo['leave_id'],
      'from_date' => $this->newLeaveInfo['from_date'],
      'to_date' => $this->newLeaveInfo['to_date'],
      'start_at' => $this->newLeaveInfo['start_at'] ?: null, // Store empty strings as null
      'end_at' => $this->newLeaveInfo['end_at'] ?: null,     // Store empty strings as null
      'note' => $this->newLeaveInfo['note'] ?: null,       // Store empty strings as null
      'updated_by' => Auth::id(), // Assuming updated_by stores User ID
      // 'is_checked' might need logic here if updating allows changing check status
    ]);

    // Success feedback and dispatching is handled in submitLeave()
    // Reset modal state is handled in submitLeave()
  }

  // Confirm deletion of a leave record
  public function confirmDestroyLeave(int $employeeLeaveId): void // Accept ID, void return type
  {
    $this->confirmedId = $employeeLeaveId; // Store the ID for confirmation
    // Dispatch event to show confirmation modal (e.g., SweetAlert, Bootstrap modal)
    // $this->dispatch('openConfirmModal', elementId: '#deleteConfirmationModal');
  }

  // Perform the deletion after confirmation
  public function destroyLeave(): void // Void return type
  {
    // Check if an ID is confirmed
    if ($this->confirmedId === null) {
      return; // No ID to delete
    }

    // Find the record to delete
    $record = EmployeeLeave::find($this->confirmedId);

    if (!$record) {
      // Record not found (maybe already deleted)
      session()->flash('error', __('Leave record not found for deletion.'));
      $this->dispatch('toastr', type: 'error', message: __('Not Found!'));
    } else {
      // Perform the deletion
      try {
        $record->delete(); // Use model delete method (handles soft deletes if enabled)

        session()->flash('success', __('Success, record deleted successfully!'));
        $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
      } catch (\Exception $e) {
        // Handle potential database errors during deletion
        Log::error('Leaves component: Failed to delete leave record.', ['user_id' => Auth::id(), 'employee_leave_id' => $this->confirmedId, 'exception' => $e]);
        session()->flash('error', __('An error occurred while deleting the leave record.') . ' ' . $e->getMessage()); // Show exception message or generic error
        $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
      }
    }

    // Reset confirmedId and close confirmation modal (if using one)
    $this->confirmedId = null;
    // $this->dispatch('closeConfirmModal', elementId: '#deleteConfirmationModal');
  }

  // 👉 Import/Export Actions

  // Validation rules for import
  protected function importRules()
  {
    return [
      'file' => 'required|mimes:xlsx,xls', // Accept both xlsx and xls
    ];
  }

  // Custom validation messages for import
  protected function importMessages()
  {
    return [
      'file.required' => __('Please select a file to upload.'),
      'file.mimes' => __('Only Excel files (xlsx, xls) are accepted.'),
    ];
  }


  // Handle Excel import
  public function importFromExcel()
  {
    // Validate the uploaded file using dedicated rules/messages
    $this->validate($this->importRules(), $this->importMessages());

    try {
      // Assuming ImportLeaves handles data correctly and potentially reports errors internally
      // Consider passing current user ID or center ID to the importer if needed for context
      $import = new ImportLeaves();
      Excel::import($import, $this->file);

      // Optional: Check for failures or errors reported by the importer
      // if ($import->failures()->isNotEmpty()) { ... handle import failures ... }

      // Send notification upon successful import
      Notification::send(
        Auth::user(),
        new DefaultNotification(Auth::user()->id, __('Successfully imported the leaves file.')) // Translated notification message
      );
      $this->dispatch('refreshNotifications')->to(Navbar::class); // Dispatch event to refresh navbar

      // Flash success message
      session()->flash('success', __('Well done! The file has been imported successfully.'));
    } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
      // Catch validation exceptions specifically from Maatwebsite/Excel
      $failures = $e->failures();
      $errorMessage = __('Import failed due to validation errors:') . ' ' . count($failures) . ' ' . __('rows failed.');
      // You might want to pass $failures to the view to display detailed errors
      // $this->dispatch('showImportValidationErrors', errors: $failures);
      Log::error('Leave import validation failed: ' . $errorMessage, ['user_id' => Auth::id(), 'failures' => $failures]);
      session()->flash('error', $errorMessage);
    } catch (Exception $e) {
      // Catch other exceptions during import
      Log::error('Leave import failed: ' . $e->getMessage(), ['user_id' => Auth::id(), 'file' => $this->file ? $this->file->getClientOriginalName() : 'N/A', 'exception' => $e]);

      // Provide a user-friendly error message
      session()->flash('error', __('An error occurred during import. Please check the file format and content.')); // Generic error message
    } finally {
      // Ensure file property is reset and import modal is closed regardless of success or failure
      $this->reset('file'); // Clear the file input field state
      $this->dispatch('closeModal', elementId: '#importModal'); // Assuming a JS event to close modal
      $this->dispatch('toastr', type: session()->has('success') ? 'success' : 'error', message: session()->get('success') ?? session()->get('error')); // Dispatch toastr based on flashed message
    }
  }

  // Handle Excel export
  public function exportToExcel()
  {
    // Fetch data for export again, based on current user's center and filters if needed
    // This logic seems specific to exporting recent, unchecked leaves created by the current user in their center
    $user = Employee::find(Auth::user()->employee_id);
    if (!$user) {
      session()->flash('error', __('Unable to find employee for export filter.'));
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
      return;
    }

    $currentTimeline = $user->timelines()->where('end_date', null)->first();
    if (!$currentTimeline || !$currentTimeline->center_id) {
      session()->flash('error', __('Unable to determine active center for export filter.'));
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
      return;
    }

    $center = Center::find($currentTimeline->center_id);
    if (!$center) {
      session()->flash('error', __('Center not found for export filter.'));
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
      return;
    }

    // Get employee IDs for the center
    $centerEmployeesIds = $center->activeEmployees()->pluck('id');

    // --- Data Fetching for Export ---
    // Start with EmployeeLeave model
    $query = EmployeeLeave::query()
      ->select([
        'employee_leave.id AS ID',
        // Join employees table to get first/last name
        DB::raw('CONCAT(employees.first_name, " ", employees.last_name) AS Employee'),
        // Join leaves table to get leave name
        'leaves.name AS Leave',
        'employee_leave.from_date AS From Date',
        'employee_leave.to_date AS To Date',
        'employee_leave.start_at AS Start At',
        'employee_leave.end_at AS End At',
        'employee_leave.note AS Note',
        // Assuming created_by/updated_by are User IDs or have relationships for names
        // If CreatedUpdatedDeletedBy trait adds relationships, eager load and select names
        // Otherwise, join users table
        'employee_leave.created_by', // Get the ID
        'employee_leave.updated_by', // Get the ID
      ])
      ->leftJoin('employees', 'employee_leave.employee_id', '=', 'employees.id')
      ->leftJoin('leaves', 'employee_leave.leave_id', '=', 'leaves.id');
    // Join users table twice to get created_by and updated_by user names if they store IDs
    // ->leftJoin('users AS created_users', 'employee_leave.created_by', '=', 'created_users.id')
    // ->leftJoin('users AS updated_users', 'employee_leave.updated_by', '=', 'updated_users.id')
    // And select user names: DB::raw('created_users.name AS Created By User'), etc.


    // Apply Filters based on the original logic (last 7 days, unchecked, created by current user's ID)
    $query->whereIn('employee_leave.employee_id', $centerEmployeesIds)
      ->where('employee_leave.created_at', '>=', Carbon::now()->subDays(7)->startOfDay()) // Use startOfDay for precision
      ->where('is_checked', 0)
      // Filter by the User ID of the creator
      ->where('employee_leave.created_by', Auth::id()); // Assuming created_by stores User ID


    $leavesToExport = $query->get(); // Get the data

    if ($leavesToExport->isEmpty()) {
      session()->flash('error', __('No leaves found matching the export criteria for the last 7 days.'));
      $this->dispatch('toastr', type: 'warning', message: __('No Data!'));
      return; // Stop here if no data
    }


    session()->flash('success', __('Well done! The file has been exported successfully.'));
    $this->dispatch('toastr', type: 'success', message: __('Going Well!')); // Dispatch toastr on success

    // Generate filename
    $filename = 'Leaves - ' . Auth::user()->name . ' - ' .
      Carbon::now()->subDays(7)->format('Y-m-d') . ' --- ' .
      Carbon::now()->format('Y-m-d') . '.xlsx';

    // Return the Excel download response
    return Excel::download(
      new ExportLeaves($leavesToExport), // Assuming ExportLeaves accepts the collection
      $filename
    );
  }


  // 👉 Helper Methods (Internal logic, not directly callable from view)

  // Check if a Leave type is hourly
  // Requires the Leave model to have a 'type' column (e.g., enum, string)
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

  // Check if a Leave type is daily
  protected function isDailyLeave(?int $leaveId): bool
  {
    if ($leaveId === null) {
      return false; // Cannot be daily if no leave type selected
    }
    $leave = $this->leaveTypes->firstWhere('id', $leaveId);
    return $leave ? $leave->type === 'daily' : false;
  }
}
