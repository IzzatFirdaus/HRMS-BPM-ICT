<?php

namespace App\Livewire;

use App\Jobs\sendPendingMessages;
use App\Models\Center;
use App\Models\Changelog;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\Leave;
use App\Models\Message;
use App\Models\EmailApplication; // Import EmailApplication model
use App\Models\LoanApplication;  // Import LoanApplication model
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // Import DB facade if needed for raw queries
use Illuminate\Support\Number;
use Livewire\Component;
use Throwable;
use Illuminate\Support\Collection; // Import Collection
// Import the Notification model if you are using the default Laravel notifications table
// use Illuminate\Notifications\DatabaseNotification;

class Dashboard extends Component
{
  // ... existing properties ...
  public $accountBalance = ['status' => 400, 'balance' => '---', 'is_active' => '---'];
  public $messagesStatus = ['sent' => 0, 'unsent' => 0];
  public $changelogs;
  public $activeEmployees;
  public $center;
  public $selectedEmployeeId;
  public $leaveTypes;
  public $employeeLeaveId;
  public $employeeLeaveRecord;
  public $isEdit = false;
  public $confirmedId;
  public $leaveRecords = [];
  public $newLeaveInfo = [
    'LeaveId' => '',
    'fromDate' => null,
    'toDate' => null,
    'startAt' => null,
    'endAt' => null,
    'note' => null,
  ];
  public $fromDateLimit;
  public $employeePhoto = 'profile-photos/.default-photo.jpg';

  // Add the public properties for applications, status counts, and notifications
  public Collection $userEmailApplications; // Use Collection type hint
  public Collection $userLoanApplications;  // Use Collection type hint
  public Collection $emailApplicationStatusCounts; // Use Collection type hint
  public Collection $loanApplicationStatusCounts;  // Use Collection type hint
  public Collection $userNotifications; // Add public property for notifications
  public $loggedInUser; // To hold the authenticated user


  /**
   * Mount is called once when the component is initialized.
   * Use it to fetch initial data.
   */
  public function mount()
  {
    // Get the authenticated user
    $authUser = Auth::user();

    // Initialize application and notification properties as empty collections
    $this->userEmailApplications = collect();
    $this->userLoanApplications = collect();
    $this->emailApplicationStatusCounts = collect();
    $this->loanApplicationStatusCounts = collect();
    $this->userNotifications = collect(); // Initialize notifications property


    // If the user is not logged in, redirect or handle appropriately
    if (!$authUser) {
      // Redirect to login or show an error
      return redirect()->route('login'); // Example redirect
    }

    // Get the associated Employee if needed for other logic:
    // Check if employee_id exists on the User model and is not null before finding Employee
    $employee = ($authUser && $authUser->employee_id) ? Employee::find($authUser->employee_id) : null;


    // Fetch data that depends on the user or employee
    if ($employee) {
      // Fetch center details only if employee is found and has a timeline
      $timeline = $employee->timelines()->where('end_date', null)->first();
      $center = $timeline ? Center::find($timeline->center_id) : null;

      $this->activeEmployees = $center ? $center->activeEmployees() : collect(); // Handle case where center is null

      $this->selectedEmployeeId = $employee->id; // Use employee ID
      $this->employeePhoto = $employee->profile_photo_path;

      // Fetch the authenticated user's pending Email Applications
      // Assuming the main User model ($authUser) has a relationship named 'emailApplications'
      $this->userEmailApplications = $authUser->emailApplications()
        ->whereNotIn('status', ['completed', 'rejected', 'cancelled']) // Exclude final statuses
        ->latest() // Order by latest
        ->get();

      // Fetch the authenticated user's pending Loan Applications
      // Assuming the main User model ($authUser) has a relationship named 'loanApplications'
      $this->userLoanApplications = $authUser->loanApplications()
        ->whereNotIn('status', ['completed', 'rejected', 'cancelled', 'returned']) // Exclude final statuses including 'returned'
        ->latest() // Order by latest
        ->get();

      // Fetch status counts for Email Applications
      $this->emailApplicationStatusCounts = $authUser->emailApplications()
        ->select('status', \DB::raw('count(*) as total')) // Use DB facade for raw count
        ->groupBy('status')
        ->pluck('total', 'status'); // Get counts keyed by status

      // Fetch status counts for Loan Applications
      $this->loanApplicationStatusCounts = $authUser->loanApplications()
        ->select('status', \DB::raw('count(*) as total'))
        ->groupBy('status')
        ->pluck('total', 'status');

      // Fetch the authenticated user's recent notifications
      // Assuming the User model uses the Notifiable trait and has a 'notifications' relationship
      $this->userNotifications = $authUser->notifications()
        ->latest() // Order by latest notifications
        ->limit(10) // Limit the number of notifications shown (adjust as needed)
        ->get();
    } else {
      // Handle case where no employee found (e.g., user record exists but linked employee deleted)
      $this->activeEmployees = collect();
      $this->selectedEmployeeId = null;
      $this->employeePhoto = 'profile-photos/.default-photo.jpg'; // Default photo
      // Application and notification properties are already initialized as empty collections above
    }

    // Store the authenticated user in a public property
    $this->loggedInUser = $authUser;


    $this->leaveTypes = Leave::all();

    try {
      // CheckAccountBalance should probably use $employee or $authUser
      // Ensure CheckAccountBalance method exists and is accessible
      // If it's a protected method, you can call it using $this->CheckAccountBalance()
      // If it's a static method on another class, call it like OtherClass::CheckAccountBalance(...)
      // Assuming it's a method on this component or a trait used by it:
      if (method_exists($this, 'CheckAccountBalance')) {
        $this->accountBalance = $this->CheckAccountBalance($employee ?? $authUser); // Pass the relevant model
      } else {
        // Handle case where method does not exist or is not accessible
        // Log::warning("CheckAccountBalance method not found or not accessible in Dashboard component.");
        // Set a default or error state for accountBalance if the method is missing
        $this->accountBalance = ['status' => 500, 'balance' => 'Error', 'is_active' => 'Error'];
      }
    } catch (Throwable $th) {
      // Log the error instead of just swallowing it in production
      \Log::error("Error checking account balance for user " . ($authUser->id ?? 'N/A') . ": " . $th->getMessage());
      // Set an error state for accountBalance on exception
      $this->accountBalance = ['status' => 500, 'balance' => 'Error', 'is_active' => 'Error'];
    }

    $this->fromDateLimit = Carbon::now()
      ->subDays(30)
      ->format('Y-m-d');
    $this->changelogs = Changelog::latest()->get();
  }

  /**
   * Render the component's view.
   * Public properties are automatically available to the view.
   */
  public function render()
  {
    // These fetch operations are okay to be in render if they should update
    // every time the component re-renders (e.g., when properties change).
    // If they only need to load once, move them to mount().
    // The messagesStatus calculation can remain in render as it's a simple aggregation.
    $messagesStatus = Message::selectRaw(
      'SUM(CASE WHEN is_sent = 1 THEN 1 ELSE 0 END) AS sent, SUM(CASE WHEN is_sent = 0 THEN 1 ELSE 0 END) AS unsent'
    )->first();
    $this->messagesStatus = [
      'sent' => Number::format($messagesStatus['sent'] != null ? $messagesStatus['sent'] : 0),
      'unsent' => Number::format($messagesStatus['unsent'] != null ? $messagesStatus['unsent'] : 0),
    ];

    // Check if loggedInUser is available before using ->name
    // Use the loggedInUser property which is set in mount()
    $loggedInUserName = $this->loggedInUser ? $this->loggedInUser->name : null;
    // Only fetch leave records if a logged-in user with a name exists
    $this->leaveRecords = $loggedInUserName
      ? EmployeeLeave::where('created_by', $loggedInUserName) // Use the variable
      ->whereDate('created_at', Carbon::today()->toDateString()) // Use toDateString() for date comparison
      ->orderBy('created_at')
      ->get()
      : collect(); // Return empty collection if no user name


    return view('livewire.dashboard'); // Renders resources/views/livewire/dashboard.blade.php
  }

  public function updatedSelectedEmployeeId()
  {
    $employee = Employee::find($this->selectedEmployeeId);

    if ($employee) {
      $this->employeePhoto = $employee->profile_photo_path;
    } else {
      $this->reset('employeePhoto'); // Reset photo if employee not found
      // You might also want to reset related data like leave records if they are tied to selectedEmployeeId
      // $this->leaveRecords = collect(); // Example: clear leave records
    }
    // You might want to refresh leave records here if they are tied to selectedEmployeeId
    // $this->leaveRecords = EmployeeLeave::where('employee_id', $this->selectedEmployeeId)
    //     ->whereDate('created_at', Carbon::today()->toDateString())
    //     ->orderBy('created_at')
    //     ->get();
  }

  public function sendPendingMessages()
  {
    // Check message count using the property
    // Ensure messagesStatus is initialized before checking
    if (isset($this->messagesStatus['unsent']) && $this->messagesStatus['unsent'] > 0) { // Check if unsent > 0
      sendPendingMessages::dispatch();
      session()->flash('info', __('Let\'s go! Messages on their way!'));
    } else {
      $this->dispatch('toastr', type: 'info' /* , title: 'Done!' */, message: __('Everything has sent already!'));
    }
  }

  public function showCreateLeaveModal()
  {
    $this->dispatch('clearSelect2Values');
    $this->reset('newLeaveInfo', 'isEdit');
    // Ensure selectedEmployeeId is set if it's tied to the form
    // Use the loggedInUser property
    if ($this->loggedInUser && $this->loggedInUser->employee_id) {
      $this->selectedEmployeeId = $this->loggedInUser->employee_id; // Set default if needed
      // Dispatch event to set default employee in Select2 if needed
      $this->dispatch('setSelect2Values', employeeId: $this->selectedEmployeeId, leaveId: null);
    } else {
      // Handle case where loggedInUser or employee_id is missing
      session()->flash('error', __('Cannot create leave record: User or Employee not linked.'));
      $this->dispatch('toastr', type: 'error', message: __('Error!'));
      // Prevent modal from showing or disable form elements
      return; // Exit the method
    }
  }

  public function createLeave()
  {
    // Ensure loggedInUser is available before proceeding
    if (!$this->loggedInUser) {
      session()->flash('error', __('Cannot create leave record: User not authenticated.'));
      $this->dispatch('toastr', type: 'error', message: __('Error!'));
      $this->dispatch('closeModal', elementId: '#leaveModal');
      return;
    }

    // Add validation before creating
    $this->validate([
      'selectedEmployeeId' => 'required',
      'newLeaveInfo.LeaveId' => 'required',
      'newLeaveInfo.fromDate' => 'required|date',
      'newLeaveInfo.toDate' => 'required|date|after_or_equal:newLeaveInfo.fromDate', // Added check
      // Add validation for startAt/endAt based on LeaveId if necessary, matching submitLeave logic
      // Example: 'newLeaveInfo.startAt' => $this->isHourlyLeave() ? 'required' : 'nullable',
      // Example: 'newLeaveInfo.endAt' => $this->isHourlyLeave() ? 'required' : 'nullable|after:newLeaveInfo.startAt',
    ]);

    // Add check for existing record before creating
    $existing = EmployeeLeave::where([
      'employee_id' => $this->selectedEmployeeId,
      'leave_id' => $this->newLeaveInfo['LeaveId'],
      'from_date' => $this->newLeaveInfo['fromDate'],
      'to_date' => $this->newLeaveInfo['toDate'],
      // Only include time checks if it's an hourly leave type
      // Use conditional array merging or check within the where clause
    ]);

    // Add time checks conditionally
    if ($this->isHourlyLeave($this->newLeaveInfo['LeaveId'])) {
      $existing->where('start_at', $this->newLeaveInfo['startAt'])
        ->where('end_at', $this->newLeaveInfo['endAt']);
    } else {
      // For daily leave, ensure start_at and end_at are null or empty
      $existing->whereNull('start_at')->whereNull('end_at');
    }

    if ($existing->exists()) {
      session()->flash('error', __('This exact leave record already exists.'));
      $this->dispatch('toastr', type: 'error', message: __('Duplicate Entry!'));
      $this->dispatch('closeModal', elementId: '#leaveModal');
      return;
    }

    EmployeeLeave::create([ // Use create instead of firstOrCreate unless you specifically need firstOrCreate logic
      'employee_id' => $this->selectedEmployeeId,
      'leave_id' => $this->newLeaveInfo['LeaveId'],
      'from_date' => $this->newLeaveInfo['fromDate'],
      'to_date' => $this->newLeaveInfo['toDate'],
      'start_at' => $this->newLeaveInfo['startAt'],
      'end_at' => $this->newLeaveInfo['endAt'],
      'note' => $this->newLeaveInfo['note'],
      // created_by is likely handled by your trait or model observer
      'created_by' => $this->loggedInUser->name ?? 'System', // Ensure created_by is set correctly using loggedInUser
    ]);

    session()->flash('success', __('Success, record created successfully!'));
    $this->dispatch('scrollToTop');

    $this->dispatch('closeModal', elementId: '#leaveModal');
    $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));

    // Refresh leave records shown in the table after creation
    // Use the loggedInUser property for filtering
    $this->leaveRecords = EmployeeLeave::where('created_by', $this->loggedInUser->name ?? null) // Re-fetch logic, handle null user name
      ->whereDate('created_at', Carbon::today()->toDateString())
      ->orderBy('created_at')
      ->get();
  }

  public function showEditLeaveModal($id)
  {
    $this->reset('newLeaveInfo'); // Reset form state

    $this->isEdit = true;
    $this->employeeLeaveId = $id;

    $record = EmployeeLeave::find($id); // Use Eloquent find

    if ($record) {
      $this->selectedEmployeeId = $record->employee_id;
      $this->newLeaveInfo = [
        'LeaveId' => $record->leave_id,
        'fromDate' => $record->from_date, // Assuming date field is stored as string
        'toDate' => $record->to_date,
        'startAt' => $record->start_at, // Assuming time field is stored as string
        'endAt' => $record->end_at,
        'note' => $record->note,
      ];

      // Dispatch event to update Select2 if needed
      $this->dispatch('setSelect2Values', employeeId: $this->selectedEmployeeId, leaveId: $record->leave_id);
    } else {
      // Handle case where record is not found
      session()->flash('error', __('Leave record not found!'));
      $this->dispatch('toastr', type: 'error', message: __('Error!'));
      $this->reset('isEdit', 'employeeLeaveId'); // Reset edit state
    }
  }

  public function updateLeave()
  {
    // Ensure loggedInUser is available before proceeding
    if (!$this->loggedInUser) {
      session()->flash('error', __('Cannot update leave record: User not authenticated.'));
      $this->dispatch('toastr', type: 'error', message: __('Error!'));
      // Decide if you want to close the modal here or just show the error
      // $this->dispatch('closeModal', elementId: '#leaveModal');
      return;
    }

    // Add validation before updating
    $this->validate([
      'selectedEmployeeId' => 'required',
      'newLeaveInfo.LeaveId' => 'required',
      'newLeaveInfo.fromDate' => 'required|date',
      'newLeaveInfo.toDate' => 'required|date|after_or_equal:newLeaveInfo.fromDate', // Added check
      // Add validation for startAt/endAt based on LeaveId if necessary, matching submitLeave logic
      // Example: 'newLeaveInfo.startAt' => $this->isHourlyLeave($this->newLeaveInfo['LeaveId']) ? 'required' : 'nullable',
      // Example: 'newLeaveInfo.endAt' => $this->isHourlyLeave($this->newLeaveInfo['LeaveId']) ? 'required' : 'nullable|after:newLeaveInfo.startAt',
    ]);

    $record = EmployeeLeave::find($this->employeeLeaveId);

    if ($record) {
      $record->update([
        'employee_id' => $this->selectedEmployeeId,
        'leave_id' => $this->newLeaveInfo['LeaveId'],
        'from_date' => $this->newLeaveInfo['fromDate'],
        'to_date' => $this->newLeaveInfo['toDate'],
        'start_at' => $this->newLeaveInfo['startAt'],
        'end_at' => $this->newLeaveInfo['endAt'],
        'note' => $this->newLeaveInfo['note'],
        // updated_by is likely handled by your trait or model observer
        'updated_by' => $this->loggedInUser->name ?? 'System', // Ensure updated_by is set correctly using loggedInUser
      ]);

      session()->flash('success', __('Success, record updated successfully!'));
      $this->dispatch('scrollToTop');

      $this->dispatch('closeModal', elementId: '#leaveModal');
      $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));

      $this->reset('isEdit', 'newLeaveInfo', 'employeeLeaveId'); // Reset edit state and form
      // Refresh leave records shown in the table after update
      // Use the loggedInUser property for filtering
      $this->leaveRecords = EmployeeLeave::where('created_by', $this->loggedInUser->name ?? null) // Re-fetch logic, handle null user name
        ->whereDate('created_at', Carbon::today()->toDateString())
        ->orderBy('created_at')
        ->get();
    } else {
      // Handle case where record to update is not found (shouldn't happen if showEditLeaveModal worked)
      session()->flash('error', __('Leave record not found for update!'));
      $this->dispatch('toastr', type: 'error', message: __('Error!'));
      $this->reset('isEdit', 'newLeaveInfo', 'employeeLeaveId'); // Reset edit state and form
    }
  }

  public function submitLeave()
  {
    // Validation is moved to createLeave and updateLeave methods
    // to be run only when the respective logic is executed.
    // The logic below now checks conditions *after* base validation (if any was here).
    // Basic date validation is added in createLeave/updateLeave.

    // Check hourly leave conditions
    // Use a helper method to determine if a leave type is hourly based on LeaveId
    if ($this->isHourlyLeave($this->newLeaveInfo['LeaveId'])) {
      if (empty($this->newLeaveInfo['startAt']) || empty($this->newLeaveInfo['endAt'])) {
        session()->flash('error', __('Can\'t add hourly leave without time!'));
        $this->dispatch('toastr', type: 'error', message: __('Requires Attention!'));
        $this->dispatch('closeModal', elementId: '#leaveModal');
        return;
      }
      if ($this->newLeaveInfo['fromDate'] !== $this->newLeaveInfo['toDate']) {
        session()->flash('error', __('Hourly leave must be on the same day'));
        $this->dispatch('toastr', type: 'error', message: __('Requires Attention!'));
        $this->dispatch('closeModal', elementId: '#leaveModal');
        return;
      }
      // Compare times directly as strings if they are in HH:MM format
      if ($this->newLeaveInfo['startAt'] >= $this->newLeaveInfo['endAt']) { // Use >= for time range check
        session()->flash('error', __('Check the times entered. "Start Time" can not be greater than or equal to "End Time"'));
        $this->dispatch('toastr', type: 'error', message: __('Requires Attention!'));
        $this->dispatch('closeModal', elementId: '#leaveModal');
        return;
      }
    } else {
      // Check daily leave conditions (assuming not hourly means daily)
      if (!empty($this->newLeaveInfo['startAt']) || !empty($this->newLeaveInfo['endAt'])) {
        session()->flash('error', __('Can\'t add daily leave with time!'));
        $this->dispatch('toastr', type: 'error', message: __('Requires Attention!'));
        $this->dispatch('closeModal', elementId: '#leaveModal');
        return;
      }
      // Daily leave can span multiple days, no date equality check needed here
    }


    // Check date range (already covered by validation in create/update, but keeping this check here as it was in original code)
    // This check is redundant if validation is correctly implemented in create/update.
    // if ($this->newLeaveInfo['fromDate'] > $this->newLeaveInfo['toDate']) {
    //     session()->flash('error', __('Check the dates entered. "From Date" can not be greater than "To Date"'));
    //     $this->dispatch('toastr', type: 'error', message: __('Requires Attention!'));
    //     $this->dispatch('closeModal', elementId: '#leaveModal');
    //     return;
    // }


    // If all checks pass, call create or update
    $this->isEdit ? $this->updateLeave() : $this->createLeave();
  }

  public function confirmDestroyLeave($id)
  {
    $this->confirmedId = $id;
    // You might want to dispatch a modal here to confirm deletion visually
    // $this->dispatch('showConfirmDeleteModal'); // Example
  }

  public function destroyLeave()
  {
    // Add check if confirmedId is set and valid
    if ($this->confirmedId) {
      $record = EmployeeLeave::find($this->confirmedId);
      if ($record) {
        $record->delete(); // Use Eloquent delete
        session()->flash('success', __('Leave record deleted successfully!'));
        $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));

        // Refresh leave records shown in the table after deletion
        // Use the loggedInUser property for filtering
        $this->leaveRecords = EmployeeLeave::where('created_by', $this->loggedInUser->name ?? null) // Re-fetch logic, handle null user name
          ->whereDate('created_at', Carbon::today()->toDateString())
          ->orderBy('created_at')
          ->get();
      } else {
        session()->flash('error', __('Leave record not found for deletion!'));
        $this->dispatch('toastr', type: 'error', message: __('Error!'));
      }
      $this->confirmedId = null; // Reset confirmedId
    }
    // If using a modal, dispatch hide modal here
    // $this->dispatch('hideConfirmDeleteModal'); // Example
  }

  // Assuming these methods are used elsewhere and not just in render/mount, keep them
  // If only used internally, consider making them protected or private
  public function getEmployeeName($id)
  {
    $employee = Employee::find($id);
    return $employee ? $employee->FullName : 'N/A'; // Add check for existence
  }

  public function getLeaveType($id)
  {
    $leaveType = Leave::find($id);
    return $leaveType ? $leaveType->name : 'N/A'; // Add check for existence
  }

  /**
   * Helper method to determine if a leave type is hourly based on its ID.
   * Assumes the ID structure indicates hourly leave (e.g., second character is '2').
   *
   * @param string|int|null $leaveId
   * @return bool
   */
  protected function isHourlyLeave($leaveId): bool
  {
    // Check if leaveId is not null and is a string/can be cast to string with length >= 2
    // and the second character is '2'.
    return $leaveId !== null
      && is_string($leaveId) // Ensure it's a string or handle other types if needed
      && strlen($leaveId) >= 2
      && substr($leaveId, 1, 1) === '2';
  }


  // Add a placeholder for the CheckAccountBalance method if it's not defined elsewhere
  // If it's defined in a trait used by this class, you don't need this placeholder.
  // If it's meant to be abstract, the class should be abstract.
  // Assuming it's a simple method returning array:
  /*
        protected function CheckAccountBalance($userOrEmployee)
        {
            // Implement your logic here to check the account balance
            // based on the provided $userOrEmployee model.
            // This is a placeholder. Replace with your actual logic.
            // Example:
            // if ($userOrEmployee instanceof \App\Models\Employee) {
            //     // Logic for Employee
            // } elseif ($userOrEmployee instanceof \App\Models\User) {
            //     // Logic for User
            // }
            return ['status' => 200, 'balance' => '1000.00', 'is_active' => 'Yes'];
        }
    */
}
