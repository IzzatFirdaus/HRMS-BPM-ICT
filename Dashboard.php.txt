<?php

namespace App\Livewire;

// --- Standard Namespace/Class Use Statements (must be before the class) ---
use Livewire\Component;
use App\Models\Approval;
use App\Models\Center;
use App\Models\Changelog;
use App\Models\Employee;
use App\Models\EmployeeLeave;
use App\Models\Leave;
use App\Models\Message;
use App\Models\EmailApplication;
use App\Models\LoanApplication;
use App\Models\User;
use App\Jobs\sendPendingMessages;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Throwable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\View\View;
use Livewire\Attributes\Computed;


class Dashboard extends Component
{
  // --- Trait Use Statements (must be inside the class body) ---
  use \Livewire\WithPagination, \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

  // Set pagination theme (e.g., 'bootstrap', 'tailwind')
  protected string $paginationTheme = 'bootstrap';

  // 👉 State Variables (Public properties that sync with the view)

  // Initialized in mount
  public array $accountBalance = ['status' => 400, 'balance' => '---', 'is_active' => '---'];

  // Fetched in mount
  public Collection $activeEmployees; // Active employees in the user's center

  // State for leave form
  public ?int $selectedEmployeeId = null; // The employee selected in the leave form dropdown

  // Fetched in mount
  public Collection $leaveTypes; // Available leave types for the form

  // State for leave edit
  public ?int $employeeLeaveId = null; // ID of the leave record being edited
  public ?EmployeeLeave $employeeLeaveRecord = null; // Model instance of the leave record being edited
  public bool $isEdit = false; // Flag for edit mode

  // State for delete confirmation
  public ?int $confirmedId = null; // ID of the record pending deletion

  // Form data for new/edit leave record
  public array $newLeaveInfo = [
    'LeaveId' => '',
    'fromDate' => null,
    'toDate' => null,
    'startAt' => null,
    'endAt' => null,
    'note' => null,
  ];

  public ?string $fromDateLimit = null; // Date limit for leave form (derived from Carbon)

  public string $employeePhoto = 'profile-photos/.default-photo.jpg'; // Default employee photo URL


  protected ?User $loggedInUser = null; // To hold the authenticated user (protected)
  protected ?Employee $loggedInEmployee = null; // To hold the authenticated user's employee (protected)


  // 👉 Computed Properties (Livewire v3+)

  #[Computed]
  public function userNotifications(): Collection
  {
    return $this->loggedInUser?->notifications()->latest()->limit(10)->get() ?? collect();
  }

  #[Computed]
  public function userEmailApplications(): Collection
  {
    return $this->loggedInUser?->emailApplications()->latest()->get() ?? collect();
  }

  #[Computed]
  public function userLoanApplications(): Collection
  {
    return $this->loggedInUser?->loanApplications()->latest()->get() ?? collect();
  }

  #[Computed]
  public function emailApplicationStatusCounts(): Collection
  {
    return $this->loggedInUser?->emailApplications()
      ->select('status', DB::raw('count(*) as total'))
      ->groupBy('status')
      ->pluck('total', 'status') ?? collect();
  }

  #[Computed]
  public function loanApplicationStatusCounts(): Collection
  {
    return $this->loggedInUser?->loanApplications()
      ->select('status', DB::raw('count(*) as total'))
      ->groupBy('status')
      ->pluck('total', 'status') ?? collect();
  }

  #[Computed]
  public function messagesStatus(): array
  {
    try {
      $messages = Message::selectRaw(
        'SUM(CASE WHEN is_sent = 1 THEN 1 ELSE 0 END) AS sent, SUM(CASE WHEN is_sent = 0 THEN 1 ELSE 0 END) AS unsent'
      )->first();

      return [
        'sent' => Number::format($messages['sent'] ?? 0),
        'unsent' => Number::format($messages['unsent'] ?? 0),
      ];
    } catch (\Exception $e) {
      Log::error('Dashboard: Error fetching message status counts.', ['exception' => $e]);
      return ['sent' => __('Error'), 'unsent' => __('Error')];
    }
  }

  #[Computed]
  public function changelogs(): Collection
  {
    try {
      return Changelog::latest()->get();
    } catch (\Exception $e) {
      Log::error('Dashboard: Error fetching changelogs.', ['exception' => $e]);
      return collect();
    }
  }

  /**
   * Get the leave records for the logged-in user's employee created today.
   * Filter by created_by name (as per original logic) - consider filtering by employee_id or created_by_id instead.
   *
   * @return \Illuminate\Database\Eloquent\Collection
   */
  #[Computed]
  public function leaveRecords(): Collection
  {
    $loggedInEmployeeId = $this->loggedInEmployee?->id ?? null;

    if (!$loggedInEmployeeId) {
      $loggedInUserName = $this->loggedInUser?->name ?? null;
      if (!$loggedInUserName) {
        Log::warning('Dashboard: Cannot fetch leave records, neither linked employee nor user name available.');
        return collect();
      }
      Log::warning('Dashboard: Filtering leave records by user name instead of employee ID.', ['user_name' => $loggedInUserName]);
      try {
        return EmployeeLeave::with(['employee', 'leaveType'])
          ->whereHas('employee', function (Builder $query) use ($loggedInUserName) {
            $query->where('name', $loggedInUserName);
          })
          ->orWhere('created_by', $loggedInUserName)
          ->whereDate('created_at', Carbon::today()->toDateString())
          ->orderBy('created_at')
          ->get();
      } catch (\Exception $e) {
        Log::error('Dashboard: Error fetching leave records by user name.', ['user_name' => $loggedInUserName, 'exception' => $e]);
        return collect();
      }
    }

    try {
      return EmployeeLeave::with(['employee', 'leaveType'])
        ->where('employee_id', $loggedInEmployeeId)
        ->whereDate('created_at', Carbon::today()->toDateString())
        ->orderBy('created_at')
        ->get();
    } catch (\Exception $e) {
      Log::error('Dashboard: Error fetching leave records for linked employee.', ['employee_id' => $loggedInEmployeeId, 'user_id' => $this->loggedInUser?->id, 'exception' => $e]);
      return collect();
    }
  }


  // 👉 Lifecycle Hook

  public function mount(): void
  {
    $this->loggedInUser = Auth::user();

    if (!$this->loggedInUser) {
      Log::warning('Dashboard mount called for unauthenticated user. Redirecting via middleware.');
      return;
    }

    $this->loggedInUser->load(['employee.timeline.center']);

    $this->loggedInEmployee = $this->loggedInUser->employee;


    if ($this->loggedInEmployee) {
      $center = $this->loggedInEmployee->timeline?->center;
      $this->activeEmployees = $center ? $center->activeEmployees() : collect();
      $this->selectedEmployeeId = $this->loggedInEmployee->id;
      $this->employeePhoto = $this->loggedInEmployee->profile_photo_path ?? 'profile-photos/.default-photo.jpg';
    } else {
      Log::warning('Dashboard: Authenticated user has no linked employee record.', ['user_id' => $this->loggedInUser->id]);
      $this->activeEmployees = collect();
      $this->selectedEmployeeId = null;
      $this->employeePhoto = 'profile-photos/.default-photo.jpg';
    }


    try {
      $this->leaveTypes = Leave::all();
    } catch (\Exception $e) {
      Log::error('Dashboard: Error fetching leave types.', ['exception' => $e]);
      $this->leaveTypes = collect();
    }

    try {
      if (method_exists($this, 'CheckAccountBalance') && is_callable([$this, 'CheckAccountBalance'])) {
        $this->accountBalance = $this->CheckAccountBalance($this->loggedInUser);
      } else {
        Log::warning('Dashboard: CheckAccountBalance method not found or not callable.');
        $this->accountBalance = ['status' => 500, 'balance' => __('Error'), 'is_active' => __('Error')];
      }
    } catch (Throwable $th) {
      Log::error("Error checking account balance for user " . ($this->loggedInUser->id ?? 'N/A') . ": " . $th->getMessage(), ['exception' => $th]);
      $this->accountBalance = ['status' => 500, 'balance' => __('Error'), 'is_active' => __('Error')];
    }


    $this->fromDateLimit = Carbon::now()
      ->subDays(30)
      ->format('Y-m-d');

    $this->newLeaveInfo['LeaveId'] = $this->leaveTypes->first()?->id;

    if ($this->activeEmployees->isNotEmpty()) {
      if (is_null($this->selectedEmployeeId) || !$this->activeEmployees->contains('id', $this->selectedEmployeeId)) {
        $this->selectedEmployeeId = $this->activeEmployees->first()->id;
      }
    } else {
      $this->selectedEmployeeId = null;
    }
  }

  /**
   * Render the component view.
   * Explicitly pass all variables used in the view, including the user's role.
   *
   * @return \Illuminate\View\View
   */
  public function render(): View
  {
    // Access computed properties to ensure they are evaluated
    $userNotifications = $this->userNotifications;
    $userEmailApplications = $this->userEmailApplications;
    $userLoanApplications = $this->userLoanApplications;
    $messagesStatus = $this->messagesStatus;
    $changelogs = $this->changelogs;
    $leaveRecords = $this->leaveRecords;

    // Fetch the logged-in user's role(s)
    // Assuming Spatie/laravel-permission package is used and User model uses HasRoles trait
    $userRole = $this->loggedInUser?->getRoleNames()->first(); // Get the name of the first role, or null


    return view('livewire.dashboard', [
      'accountBalance' => $this->accountBalance,
      'messagesStatus' => $messagesStatus,
      'activeEmployees' => $this->activeEmployees,
      'leaveRecords' => $leaveRecords,
      'userEmailApplications' => $userEmailApplications,
      'userLoanApplications' => $userLoanApplications,
      'changelogs' => $changelogs,
      'confirmedId' => $this->confirmedId,
      'leaveTypes' => $this->leaveTypes,
      'selectedEmployeeId' => $this->selectedEmployeeId,
      'newLeaveInfo' => $this->newLeaveInfo,
      'isEdit' => $this->isEdit,
      'employeeLeaveId' => $this->employeeLeaveId,
      'employeeLeaveRecord' => $this->employeeLeaveRecord,
      'fromDateLimit' => $this->fromDateLimit,
      'employeePhoto' => $this->employeePhoto,
      'userRole' => $userRole, // <-- Pass the user's role here as $userRole
    ]);
  }

  // 👉 Hook for selected employee change

  public function updatedSelectedEmployeeId(): void
  {
    $employee = Employee::find($this->selectedEmployeeId);

    if ($employee) {
      $this->employeePhoto = $employee->profile_photo_path ?? 'profile-photos/.default-photo.jpg';
      $employee->load('timeline.center');
      $center = $employee->timeline?->center;
      $this->activeEmployees = $center ? $center->activeEmployees() : collect();
    } else {
      $this->employeePhoto = 'profile-photos/.default-photo.jpg';
      $this->activeEmployees = collect();
    }
    $this->dispatch('setSelect2Value', elementId: '#selectedEmployeeId', value: $this->selectedEmployeeId);
  }

  // 👉 Action to send pending messages

  public function sendPendingMessages(): void
  {
    if (isset($this->messagesStatus()['unsent']) && (int) $this->messagesStatus()['unsent'] > 0) {
      try {
        sendPendingMessages::dispatch();
        session()->flash('info', __('Let\'s go! Messages on their way!'));
        $this->dispatch('toastr', type: 'info', message: __('Sending messages...'));
      } catch (\Exception $e) {
        Log::error('Dashboard: Error dispatching sendPendingMessages job.', ['user_id' => Auth::id(), 'exception' => $e]);
        session()->flash('error', __('An error occurred while trying to send messages.'));
        $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
      }
    } else {
      $this->dispatch('toastr', type: 'info' /* , title: 'Done!' */, message: __('Everything has sent already!'));
    }
  }

  // 👉 Leave Management Actions

  public function showCreateLeaveModal(): void
  {
    $this->resetValidation();
    $this->reset('newLeaveInfo', 'isEdit', 'employeeLeaveId', 'employeeLeaveRecord');
    $this->confirmedId = null;

    if ($this->loggedInUser && $this->loggedInUser->employee_id) {
      $this->selectedEmployeeId = $this->loggedInUser->employee_id;
      $this->dispatch('setSelect2Value', elementId: '#selectedEmployeeId', value: $this->selectedEmployeeId);
      if (empty($this->newLeaveInfo['LeaveId'])) {
        $this->newLeaveInfo['LeaveId'] = $this->leaveTypes->first()?->id;
      }
      $this->dispatch('setSelect2Value', elementId: '#leaveTypeId', value: $this->newLeaveInfo['LeaveId']);
    } else {
      Log::warning('Dashboard: Attempted to show create leave modal for user without linked employee.', ['user_id' => Auth::id()]);
      session()->flash('error', __('Cannot create leave record: User account is not linked to an employee profile.'));
      $this->dispatch('toastr', type: 'error', message: __('Error!'));
      return;
    }

    $this->isEdit = false;
  }

  public function submitLeave(): void
  {
    $rules = $this->getLeaveValidationRules();
    $messages = $this->getLeaveValidationMessages();

    if (is_null($this->selectedEmployeeId) && isset($rules['selectedEmployeeId'])) {
      $this->addError('selectedEmployeeId', $messages['selectedEmployeeId.required']);
      session()->flash('error', __('Please select an employee.'));
      $this->dispatch('toastr', type: 'error', message: __('Validation Failed!'));
      return;
    }

    $this->validate($rules, $messages);

    if ($this->isHourlyLeave($this->newLeaveInfo['LeaveId'])) {
      if (empty($this->newLeaveInfo['startAt']) || empty($this->newLeaveInfo['endAt'])) {
        session()->flash('error', __('Start and End times are required for hourly leave.'));
        $this->dispatch('toastr', type: 'error', message: __('Validation Failed!'));
        return;
      }
    } else {
      if (!empty($this->newLeaveInfo['startAt']) || !empty($this->newLeaveInfo['endAt'])) {
        session()->flash('error', __('Daily leave cannot include specific times.'));
        $this->dispatch('toastr', type: 'error', message: __('Validation Failed!'));
        return;
      }
    }

    try {
      DB::transaction(function () {
        if ($this->isEdit) {
          $this->updateLeave();
        } else {
          $this->createLeave();
        }
      });

      session()->flash('success', $this->isEdit ? __('Leave record updated successfully!') : __('Leave record created successfully!'));
      $this->dispatch('scrollToTop');
      $this->dispatch('closeModal', elementId: '#leaveModal');
      $this->dispatch('toastr', type: 'success', message: __('Going Well!'));

      $this->leaveRecords();
    } catch (\Exception $e) {
      Log::error('Dashboard: Leave submit failed.', [
        'user_id' => Auth::id(),
        'employee_id' => $this->selectedEmployeeId,
        'leave_data' => $this->newLeaveInfo,
        'is_edit' => $this->isEdit,
        'leave_record_id' => $this->employeeLeaveId,
        'exception' => $e,
      ]);

      session()->flash('error', __('An error occurred while saving the leave record: ') . $e->getMessage());
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
      $this->dispatch('closeModal', elementId: '#leaveModal');
    } finally {
      $this->reset('isEdit', 'newLeaveInfo', 'employeeLeaveId', 'employeeLeaveRecord');
      $this->confirmedId = null;
    }
  }

  protected function createLeave(): void
  {
    if (is_null($this->selectedEmployeeId)) {
      Log::error('Dashboard: Attempted to create leave without selected employee.', ['user_id' => Auth::id(), 'leave_data' => $this->newLeaveInfo]);
      throw new \Exception(__('Employee not selected for leave record.'));
    }

    $existingQuery = EmployeeLeave::where([
      'employee_id' => $this->selectedEmployeeId,
      'leave_id' => $this->newLeaveInfo['LeaveId'],
      'from_date' => $this->newLeaveInfo['fromDate'],
      'to_date' => $this->newLeaveInfo['toDate'],
    ]);

    if ($this->isHourlyLeave($this->newLeaveInfo['LeaveId'])) {
      $existingQuery->where('start_at', $this->newLeaveInfo['startAt'])
        ->where('end_at', $this->newLeaveInfo['endAt']);
    } else {
      $existingQuery->whereNull('start_at')->whereNull('end_at');
    }

    if ($existingQuery->exists()) {
      Log::warning('Dashboard: Attempted to create duplicate leave record.', [
        'user_id' => Auth::id(),
        'employee_id' => $this->selectedEmployeeId,
        'leave_data' => $this->newLeaveInfo
      ]);
      throw new \Exception(__('This exact leave record already exists.'));
    }

    EmployeeLeave::create([
      'employee_id' => $this->selectedEmployeeId,
      'leave_id' => $this->newLeaveInfo['LeaveId'],
      'from_date' => $this->newLeaveInfo['fromDate'],
      'to_date' => $this->newLeaveInfo['toDate'],
      'start_at' => $this->newLeaveInfo['startAt'] ?: null,
      'end_at' => $this->newLeaveInfo['endAt'] ?: null,
      'note' => $this->newLeaveInfo['note'] ?: null,
      'created_by' => $this->loggedInUser->name ?? 'System',
    ]);
  }

  public function showEditLeaveModal(int $id): void
  {
    $this->resetValidation();
    $this->reset('newLeaveInfo', 'isEdit', 'employeeLeaveId', 'employeeLeaveRecord');
    $this->confirmedId = null;

    try {
      $record = EmployeeLeave::findOrFail($id);
    } catch (ModelNotFoundException $e) {
      Log::warning('Dashboard: Attempted to show edit modal for non-existent leave record.', ['record_id' => $id, 'user_id' => Auth::id()]);
      session()->flash('error', __('Leave record not found!'));
      $this->dispatch('toastr', type: 'error', message: __('Error!'));
      return;
    }


    $this->isEdit = true;
    $this->employeeLeaveId = $record->id;
    $this->employeeLeaveRecord = $record;

    $this->selectedEmployeeId = $record->employee_id;
    $this->newLeaveInfo = [
      'LeaveId' => (int) $record->leave_id,
      'fromDate' => $record->from_date,
      'toDate' => $record->to_date,
      'startAt' => $record->start_at,
      'endAt' => $record->end_at,
      'note' => $record->note ?? '',
    ];

    $this->dispatch('setSelect2Value', elementId: '#selectedEmployeeId', value: $this->selectedEmployeeId);
    $this->dispatch('setSelect2Value', elementId: '#leaveTypeId', value: $this->newLeaveInfo['LeaveId']);
  }

  protected function updateLeave(): void
  {
    if (!$this->employeeLeaveRecord || $this->employeeLeaveRecord->id !== $this->employeeLeaveId) {
      Log::error('Dashboard: Leave record instance mismatch or not loaded for update.', ['record_id' => $this->employeeLeaveId, 'user_id' => Auth::id(), 'leave_data' => $this->newLeaveInfo]);
      throw new \Exception(__('Leave record not loaded correctly for update!'));
    }

    try {
      $this->employeeLeaveRecord->update([
        'employee_id' => $this->selectedEmployeeId,
        'leave_id' => $this->newLeaveInfo['LeaveId'],
        'from_date' => $this->newLeaveInfo['fromDate'],
        'to_date' => $this->newLeaveInfo['toDate'],
        'start_at' => $this->newLeaveInfo['startAt'] ?: null,
        'end_at' => $this->newLeaveInfo['endAt'] ?: null,
        'note' => $this->newLeaveInfo['note'] ?: null,
        'updated_by' => $this->loggedInUser->name ?? 'System',
      ]);
    } catch (\Exception $e) {
      Log::error('Dashboard: Failed to update leave record.', ['record_id' => $this->employeeLeaveId, 'user_id' => Auth::id(), 'leave_data' => $this->newLeaveInfo, 'exception' => $e]);
      throw new \Exception(__('An error occurred during leave record update: ') . $e->getMessage());
    }
  }

  public function confirmDestroyLeave(int $id): void
  {
    $this->confirmedId = $id;
  }

  public function destroyLeave(): void
  {
    if ($this->confirmedId === null) {
      return;
    }

    try {
      DB::transaction(function () {
        $record = EmployeeLeave::find($this->confirmedId);

        if ($record) {
          $record->delete();
          session()->flash('success', __('Leave record deleted successfully!'));
          $this->dispatch('toastr', type: 'success', message: __('Going Well!'));
          $this->leaveRecords();
        } else {
          Log::warning('Dashboard: Attempted to delete non-existent leave record.', ['record_id' => $this->confirmedId, 'user_id' => Auth::id()]);
          session()->flash('error', __('Leave record not found for deletion!'));
          $this->dispatch('toastr', type: 'error', message: __('Error!'));
        }
      });
    } catch (\Exception $e) {
      Log::error('Dashboard: Failed to delete leave record.', ['record_id' => $this->confirmedId, 'user_id' => Auth::id(), 'exception' => $e]);
      session()->flash('error', __('An error occurred while deleting the leave record: ') . $e->getMessage());
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
    } finally {
      $this->confirmedId = null;
    }
  }

  // 👉 Helper methods for validation rules and messages

  protected function getLeaveValidationRules(): array
  {
    $rules = [
      'newLeaveInfo.LeaveId' => 'required|exists:leaves,id',
      'newLeaveInfo.fromDate' => 'required|date',
      'newLeaveInfo.toDate' => 'required|date|after_or_equal:newLeaveInfo.fromDate',
      'newLeaveInfo.note' => 'nullable|string|max:500',
    ];

    $selectedLeaveType = $this->leaveTypes->firstWhere('id', $this->newLeaveInfo['LeaveId']);
    $isHourly = $selectedLeaveType?->is_hourly ?? false;


    if ($isHourly) {
      $rules['newLeaveInfo.startAt'] = 'required|date_format:H:i';
      $rules['newLeaveInfo.endAt'] = 'required|date_format:H:i|after:newLeaveInfo.startAt';
      $rules['newLeaveInfo.toDate'] .= '|same:newLeaveInfo.fromDate';
    } else {
      $rules['newLeaveInfo.startAt'] = 'nullable';
      $rules['newLeaveInfo.endAt'] = 'nullable';
    }

    return $rules;
  }

  protected function getLeaveValidationMessages(): array
  {
    return [
      'newLeaveInfo.LeaveId.required' => __('Leave Type is required.'),
      'newLeaveInfo.LeaveId.exists' => __('Invalid Leave Type selected.'),
      'newLeaveInfo.fromDate.required' => __('From Date is required.'),
      'newLeaveInfo.fromDate.date' => __('From Date must be a valid date.'),
      'newLeaveInfo.toDate.required' => __('To Date is required.'),
      'newLeaveInfo.toDate.date' => __('To Date must be a valid date.'),
      'newLeaveInfo.toDate.after_or_equal' => __('To Date must be on or after From Date.'),
      'newLeaveInfo.toDate.same' => __('Hourly leave must be on the same day.'),
      'newLeaveInfo.startAt.required' => __('Start Time is required for hourly leave.'),
      'newLeaveInfo.startAt.date_format' => __('Start Time must be in HH:MM format.'),
      'newLeaveInfo.endAt.required' => __('End Time is required for hourly leave.'),
      'newLeaveInfo.endAt.date_format' => __('End Time must be in HH:MM format.'),
      'newLeaveInfo.endAt.after' => __('End Time must be after Start Time.'),
      'newLeaveInfo.note.max' => __('Note cannot exceed :max characters.'),
    ];
  }

  // 👉 Helper method for hourly leave check

  protected function isHourlyLeave($leaveId): bool
  {
    $leaveType = $this->leaveTypes->firstWhere('id', $leaveId);

    if (!$leaveType) {
      Log::warning('Dashboard: isHourlyLeave could not find Leave Type model.', ['leave_id' => $leaveId]);
      return $leaveId !== null
        && (is_string($leaveId) || is_numeric($leaveId))
        && strlen((string) $leaveId) >= 2
        && substr((string) $leaveId, 1, 1) === '2';
    }

    return (bool) ($leaveType->is_hourly ?? false);
  }


  // 👉 Helper methods for getting names - These are used in the Blade view
  // Since they are used in the view and perform queries, they should be #[Computed] methods
  // or the data should be eager loaded and accessed directly from relationships.
  // Let's make them #[Computed] as discussed, but direct relationship access is preferable if possible.

  #[Computed]
  public function getEmployeeName(int $id): string
  {
    try {
      $employee = Employee::find($id);
      return $employee ? $employee->full_name ?? $employee->name ?? __('N/A') : __('N/A');
    } catch (\Exception $e) {
      Log::error('Dashboard: Error getting employee name in computed property.', ['employee_id' => $id, 'exception' => $e]);
      return __('Error');
    }
  }

  #[Computed]
  public function getLeaveType(int $id): string
  {
    try {
      $leaveType = Leave::find($id);
      return $leaveType ? $leaveType->name : __('N/A');
    } catch (\Exception $e) {
      Log::error('Dashboard: Error getting leave type name in computed property.', ['leave_id' => $id, 'exception' => $e]);
      return __('Error');
    }
  }


  // Add a placeholder for the CheckAccountBalance method if it's not defined elsewhere
  /*
     protected function CheckAccountBalance($userOrEmployee): array
     {
         Log::info('CheckAccountBalance placeholder method called.');
         return ['status' => 200, 'balance' => '1000.00', 'is_active' => 'Yes'];
     }
     */
}
