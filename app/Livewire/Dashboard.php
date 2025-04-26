<?php

namespace App\Livewire;

use App\Jobs\sendPendingMessages;
use App\Models\Center;
use App\Models\Changelog;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\Leave;
use App\Models\Message;
use App\Models\EmailApplication;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Number;
use Livewire\Component;
use Throwable;

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

  // <--- Add the public property for email applications
  public $userEmailApplications;
  // You might also want a similar property for loan applications if you have that section
  // public $userLoanApplications;


  public function mount()
  {
    $user = Employee::find(Auth::user()->employee_id);
    // Assuming Auth::user() gives you the main User model,
    // you might need to get the Employee via relationship or ensure employee_id is correct.
    // A common pattern is to get the authenticated user first:
    $authUser = Auth::user();
    // Then get the associated Employee if needed for other logic:
    $employee = $authUser ? Employee::find($authUser->employee_id) : null;


    // Fetch data that depends on the user or employee
    if ($employee) {
      $center = Center::find(
        $employee // Use the employee model
          ->timelines()
          ->where('end_date', null)
          ->first()->center_id ?? null // Add null coalesce for safety
      );
      $this->activeEmployees = $center ? $center->activeEmployees() : collect(); // Handle case where center is null

      $this->selectedEmployeeId = $employee->id; // Use employee ID
      $this->employeePhoto = $employee->profile_photo_path;

      // <--- Fetch the user's email applications here
      // ASSUMPTION: The main User model (from Auth::user()) has a relationship
      // named 'emailApplications' to the EmailApplication model.
      // Adjust the ->where(...) clause to filter for 'pending' statuses
      // based on your EmailApplication model's status values.
      $this->userEmailApplications = $authUser ? $authUser->emailApplications()
        ->whereNotIn('status', ['completed', 'rejected', 'cancelled']) // Example pending statuses
        ->get() : collect(); // Ensure an empty collection if no auth user

      // If fetching loan applications:
      // $this->userLoanApplications = $authUser ? $authUser->loanApplications() // Assume relationship
      //                                         ->whereNotIn('status', ['completed', 'rejected', 'cancelled']) // Adjust statuses
      //                                         ->get() : collect(); // Ensure empty if no auth user

    } else {
      // Handle case where no employee found (e.g., user record exists but linked employee deleted)
      $this->activeEmployees = collect();
      $this->selectedEmployeeId = null;
      $this->employeePhoto = 'profile-photos/.default-photo.jpg'; // Default photo
      $this->userEmailApplications = collect(); // Assign empty collection
      // $this->userLoanApplications = collect(); // For loan applications
    }


    $this->leaveTypes = Leave::all();

    try {
      // CheckAccountBalance should probably use $employee or $authUser
      $this->accountBalance = $this->CheckAccountBalance($employee ?? $authUser); // Pass the relevant model
    } catch (Throwable $th) {
      // Log the error instead of just swallowing it in production
      // Log::error("Error checking account balance for user " . ($authUser->id ?? 'N/A') . ": " . $th->getMessage());
    }

    $this->fromDateLimit = Carbon::now()
      ->subDays(30)
      ->format('Y-m-d');
    $this->changelogs = Changelog::latest()->get();
  }

  public function render()
  {
    // These fetch operations are okay to be in render if they should update
    // every time the component re-renders (e.g., when properties change).
    // If they only need to load once, move them to mount().
    $this->messagesStatus = Message::selectRaw(
      'SUM(CASE WHEN is_sent = 1 THEN 1 ELSE 0 END) AS sent, SUM(CASE WHEN is_sent = 0 THEN 1 ELSE 0 END) AS unsent'
    )->first();
    $this->messagesStatus = [
      'sent' => Number::format($this->messagesStatus['sent'] != null ? $this->messagesStatus['sent'] : 0),
      'unsent' => Number::format($this->messagesStatus['unsent'] != null ? $this->messagesStatus['unsent'] : 0),
    ];

    // Check if auth()->user() is available before using ->name
    $loggedInUserName = Auth::user() ? Auth::user()->name : null;
    $this->leaveRecords = EmployeeLeave::where('created_by', $loggedInUserName) // Use the variable
      ->whereDate('created_at', Carbon::today()->toDateString()) // Use toDateString() for date comparison
      ->orderBy('created_at')
      ->get();

    return view('livewire.dashboard');
  }

  public function updatedSelectedEmployeeId()
  {
    $employee = Employee::find($this->selectedEmployeeId);

    if ($employee) {
      $this->employeePhoto = $employee->profile_photo_path;
    } else {
      $this->reset('employeePhoto'); // Reset photo if employee not found
      // You might also want to reset related data like leave records if they are tied to selectedEmployeeId
    }
    // You might want to refresh leave records here if they are tied to selectedEmployeeId
    // $this->leaveRecords = EmployeeLeave::where('employee_id', $this->selectedEmployeeId)
    //                                ->whereDate('created_at', Carbon::today()->toDateString())
    //                                ->orderBy('created_at')
    //                                ->get();
  }

  public function sendPendingMessages()
  {
    // Check message count using the property
    if ($this->messagesStatus['unsent'] > 0) { // Check if unsent > 0
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
    // $this->selectedEmployeeId = Auth::user()->employee_id; // Set default if needed
  }

  public function createLeave()
  {
    // Add validation before creating
    $this->validate([
      'selectedEmployeeId' => 'required',
      'newLeaveInfo.LeaveId' => 'required',
      'newLeaveInfo.fromDate' => 'required|date',
      'newLeaveInfo.toDate' => 'required|date|after_or_equal:newLeaveInfo.fromDate', // Added check
      // Add validation for startAt/endAt based on LeaveId if necessary, matching submitLeave logic
    ]);

    // Add check for existing record before creating
    $existing = EmployeeLeave::where([
      'employee_id' => $this->selectedEmployeeId,
      'leave_id' => $this->newLeaveInfo['LeaveId'],
      'from_date' => $this->newLeaveInfo['fromDate'],
      'to_date' => $this->newLeaveInfo['toDate'],
      'start_at' => $this->newLeaveInfo['startAt'],
      'end_at' => $this->newLeaveInfo['endAt'],
    ])->exists();

    if ($existing) {
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
      'created_by' => Auth::user()->name ?? 'System', // Ensure created_by is set correctly
    ]);

    session()->flash('success', __('Success, record created successfully!'));
    $this->dispatch('scrollToTop');

    $this->dispatch('closeModal', elementId: '#leaveModal');
    $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));

    // Refresh leave records shown in the table after creation
    $this->leaveRecords = EmployeeLeave::where('created_by', Auth::user()->name) // Re-fetch logic
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
    // Add validation before updating
    $this->validate([
      'selectedEmployeeId' => 'required',
      'newLeaveInfo.LeaveId' => 'required',
      'newLeaveInfo.fromDate' => 'required|date',
      'newLeaveInfo.toDate' => 'required|date|after_or_equal:newLeaveInfo.fromDate', // Added check
      // Add validation for startAt/endAt based on LeaveId if necessary, matching submitLeave logic
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
        'updated_by' => Auth::user()->name ?? 'System', // Ensure updated_by is set correctly
      ]);

      session()->flash('success', __('Success, record updated successfully!'));
      $this->dispatch('scrollToTop');

      $this->dispatch('closeModal', elementId: '#leaveModal');
      $this->dispatch('toastr', type: 'success' /* , title: 'Done!' */, message: __('Going Well!'));

      $this->reset('isEdit', 'newLeaveInfo', 'employeeLeaveId'); // Reset edit state and form
      // Refresh leave records shown in the table after update
      $this->leaveRecords = EmployeeLeave::where('created_by', Auth::user()->name) // Re-fetch logic
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
    if (substr($this->newLeaveInfo['LeaveId'], 1, 1) == 2) { // Check if it's hourly (assuming '2' is the indicator)
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
      if ($this->newLeaveInfo['startAt'] >= $this->newLeaveInfo['endAt']) { // Use >= for time range check
        session()->flash('error', __('Check the times entered. "Start Time" can not be greater than or equal to "End Time"'));
        $this->dispatch('toastr', type: 'error', message: __('Requires Attention!'));
        $this->dispatch('closeModal', elementId: '#leaveModal');
        return;
      }
    }
    // Check daily leave conditions (assuming '1' is the indicator)
    if (substr($this->newLeaveInfo['LeaveId'], 1, 1) == 1) {
      if (!empty($this->newLeaveInfo['startAt']) || !empty($this->newLeaveInfo['endAt'])) {
        session()->flash('error', __('Can\'t add daily leave with time!'));
        $this->dispatch('toastr', type: 'error', message: __('Requires Attention!'));
        $this->dispatch('closeModal', elementId: '#leaveModal');
        return;
      }
      // Daily leave can span multiple days, no date check needed here
    }

    // Check date range
    if ($this->newLeaveInfo['fromDate'] > $this->newLeaveInfo['toDate']) {
      session()->flash('error', __('Check the dates entered. "From Date" can not be greater than "To Date"'));
      $this->dispatch('toastr', type: 'error', message: __('Requires Attention!'));
      $this->dispatch('closeModal', elementId: '#leaveModal');
      return;
    }


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
        $this->leaveRecords = EmployeeLeave::where('created_by', Auth::user()->name) // Re-fetch logic
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
         return ['status' => 200, 'balance' => '1000.00', 'is_active' => 'Yes'];
    }
    */
}
