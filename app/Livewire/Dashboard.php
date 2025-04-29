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
use Livewire\Attributes\Computed; // FIX: Import Computed attribute


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
  // The IDE error PHP0410 suggests a Builder is assigned here, but the code
  // only assigns results of find() or null, which should be correct.
  // This might be an IDE analysis issue.
  public ?EmployeeLeave $employeeLeaveRecord = null; // Model instance of the leave record being edited
  public bool $isEdit = false; // Flag for edit mode

  // State for delete confirmation
  public ?int $confirmedId = null; // ID of the record pending deletion

  // Form data for new/edit leave record
  // #[Rule] attribute applied in getLeaveValidationRules() for conditional rules
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

  /**
   * Get the authenticated user's unread notifications.
   *
   * @return \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Support\Collection
   */
  #[Computed] // Use Imported Computed
  public function userNotifications(): Collection
  {
    return $this->loggedInUser?->notifications()->latest()->limit(10)->get() ?? collect();
  }

  /**
   * Get the authenticated user's Email Applications.
   *
   * @return \Illuminate\Database\Eloquent\Collection
   */
  #[Computed] // Use Imported Computed
  public function userEmailApplications(): Collection
  {
    return $this->loggedInUser?->emailApplications()->latest()->get() ?? collect();
  }

  /**
   * Get the authenticated user's Loan Applications.
   *
   * @return \Illuminate\Database\Eloquent\Collection
   */
  #[Computed] // Use Imported Computed
  public function userLoanApplications(): Collection
  {
    return $this->loggedInUser?->loanApplications()->latest()->get() ?? collect();
  }

  /**
   * Get status counts for the authenticated user's Email Applications.
   *
   * @return \Illuminate\Support\Collection
   */
  #[Computed] // Use Imported Computed
  public function emailApplicationStatusCounts(): Collection
  {
    return $this->loggedInUser?->emailApplications()
      ->select('status', DB::raw('count(*) as total'))
      ->groupBy('status')
      ->pluck('total', 'status') ?? collect();
  }

  /**
   * Get status counts for the authenticated user's Loan Applications.
   *
   * @return \Illuminate\Support\Collection
   */
  #[Computed] // Use Imported Computed
  public function loanApplicationStatusCounts(): Collection
  {
    return $this->loggedInUser?->loanApplications()
      ->select('status', DB::raw('count(*) as total'))
      ->groupBy('status')
      ->pluck('total', 'status') ?? collect();
  }

  #[Computed] // Use Imported Computed
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

  #[Computed] // Use Imported Computed
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
  #[Computed] // Use Imported Computed
  public function leaveRecords(): Collection
  {
    $loggedInUserName = $this->loggedInUser?->name ?? null;

    if (!$loggedInUserName) {
      return collect();
    }

    try {
      return EmployeeLeave::where('created_by', $loggedInUserName)
        ->whereDate('created_at', Carbon::today()->toDateString())
        ->orderBy('created_at')
        ->get();
    } catch (\Exception $e) {
      Log::error('Dashboard: Error fetching leave records for user.', ['user_name' => $loggedInUserName, 'exception' => $e]);
      return collect();
    }
  }

  // 👉 Lifecycle Hook

  public function mount(): void
  {
    $this->loggedInUser = Auth::user();

    if (!$this->loggedInUser) {
      redirect()->route('login');
      return;
    }

    $this->loggedInEmployee = $this->loggedInUser->employee_id ? Employee::find($this->loggedInUser->employee_id) : null;

    if ($this->loggedInEmployee) {
      $timeline = $this->loggedInEmployee->timelines()->whereNull('end_date')->first();
      $center = $timeline ? Center::find($timeline->center_id) : null;

      $this->activeEmployees = $center ? $center->activeEmployees() : collect();

      $this->selectedEmployeeId = $this->loggedInEmployee->id;
      $this->employeePhoto = $this->loggedInEmployee->profile_photo_path ?? 'profile-photos/.default-photo.jpg';
    } else {
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
        $this->accountBalance = $this->CheckAccountBalance($this->loggedInEmployee ?? $this->loggedInUser);
      } else {
        $this->accountBalance = ['status' => 500, 'balance' => __('Error'), 'is_active' => __('Error')];
      }
    } catch (Throwable $th) {
      Log::error("Error checking account balance for user " . ($this->loggedInUser->id ?? 'N/A') . ": " . $th->getMessage(), ['exception' => $th]);
      $this->accountBalance = ['status' => 500, 'balance' => __('Error'), 'is_active' => __('Error')];
    }

    $this->fromDateLimit = Carbon::now()
      ->subDays(30)
      ->format('Y-m-d');
  }

  // 👉 Render method

  public function render(): View
  {
    return view('livewire.dashboard');
  }

  // 👉 Hook for selected employee change

  public function updatedSelectedEmployeeId(): void
  {
    $employee = Employee::find($this->selectedEmployeeId);

    if ($employee) {
      $this->employeePhoto = $employee->profile_photo_path ?? 'profile-photos/.default-photo.jpg';
    } else {
      $this->employeePhoto = 'profile-photos/.default-photo.jpg';
    }
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

    if ($this->loggedInUser && $this->loggedInUser->employee_id) {
      $this->selectedEmployeeId = $this->loggedInUser->employee_id;
      $this->dispatch('setSelect2Values', employeeId: $this->selectedEmployeeId, leaveId: null);
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

    $this->validate($rules, $messages);

    // Business logic checks (hourly/daily consistency, time range) after validation
    if ($this->isHourlyLeave($this->newLeaveInfo['LeaveId'])) {
      if (Carbon::parse($this->newLeaveInfo['fromDate'])->toDateString() !== Carbon::parse($this->newLeaveInfo['toDate'])->toDateString()) {
        session()->flash('error', __('Hourly leave must be on the same day.'));
        $this->dispatch('toastr', type: 'error', message: __('Validation Failed!'));
        return;
      }

      if (Carbon::parse($this->newLeaveInfo['startAt'])->gte(Carbon::parse($this->newLeaveInfo['endAt']))) {
        session()->flash('error', __('Check the times entered. "Start Time" can not be greater than or equal to "End Time".'));
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
    } catch (\Exception $e) {
      Log::error('Dashboard: Leave submit failed.', [
        'user_id' => Auth::id(),
        'employee_id' => $this->selectedEmployeeId,
        'leave_data' => $this->newLeaveInfo,
        'is_edit' => $this->isEdit,
        'leave_record_id' => $this->employeeLeaveId,
        'exception' => $e,
      ]);

      session()->flash('error', __('An error occurred while saving the leave record.'));
      $this->dispatch('toastr', type: 'error', message: __('Operation Failed!'));
      $this->dispatch('closeModal', elementId: '#leaveModal');
    } finally {
      $this->reset('isEdit', 'newLeaveInfo', 'employeeLeaveId', 'employeeLeaveRecord');
    }
  }

  protected function createLeave(): void
  {
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

    $record = EmployeeLeave::find($id);

    if ($record) {
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

      $this->dispatch('setSelect2Values', employeeId: $this->selectedEmployeeId, leaveId: $record->leave_id);
    } else {
      Log::warning('Dashboard: Attempted to show edit modal for non-existent leave record.', ['record_id' => $id, 'user_id' => Auth::id()]);
      session()->flash('error', __('Leave record not found!'));
      $this->dispatch('toastr', type: 'error', message: __('Error!'));
    }
  }

  protected function updateLeave(): void
  {
    $record = EmployeeLeave::find($this->employeeLeaveId);

    if ($record) {
      $record->update([
        'employee_id' => $this->selectedEmployeeId,
        'leave_id' => $this->newLeaveInfo['LeaveId'],
        'from_date' => $this->newLeaveInfo['fromDate'],
        'to_date' => $this->newLeaveInfo['toDate'],
        'start_at' => $this->newLeaveInfo['startAt'] ?: null,
        'end_at' => $this->newLeaveInfo['endAt'] ?: null,
        'note' => $this->newLeaveInfo['note'] ?: null,
        'updated_by' => $this->loggedInUser->name ?? 'System',
      ]);
    } else {
      Log::error('Dashboard: Leave record not found for update.', ['record_id' => $this->employeeLeaveId, 'user_id' => Auth::id(), 'leave_data' => $this->newLeaveInfo]);
      throw new \Exception(__('Leave record not found for update!'));
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
        } else {
          Log::warning('Dashboard: Attempted to delete non-existent leave record.', ['record_id' => $this->confirmedId, 'user_id' => Auth::id()]);
          session()->flash('error', __('Leave record not found for deletion!'));
          $this->dispatch('toastr', type: 'error', message: __('Error!'));
        }
      });
    } catch (\Exception $e) {
      Log::error('Dashboard: Failed to delete leave record.', ['record_id' => $this->confirmedId, 'user_id' => Auth::id(), 'exception' => $e]);
      session()->flash('error', __('An error occurred while deleting the leave record.'));
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
      'selectedEmployeeId' => 'required',
    ];

    if ($this->isHourlyLeave($this->newLeaveInfo['LeaveId'])) {
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
      'selectedEmployeeId.required' => __('Employee is required for the leave record.'),
    ];
  }

  // 👉 Helper method for hourly leave check

  protected function isHourlyLeave($leaveId): bool
  {
    $leaveType = $this->leaveTypes->firstWhere('id', $leaveId);

    if ($leaveType) {
      return (bool) ($leaveType->is_hourly ?? false);
    }

    Log::warning('Dashboard: isHourlyLeave fallback used, check Leave model or ID structure dependency.', ['leave_id' => $leaveId]);
    return $leaveId !== null
      && (is_string($leaveId) || is_numeric($leaveId))
      && strlen((string) $leaveId) >= 2
      && substr((string) $leaveId, 1, 1) === '2';
  }

  // 👉 Helper methods for getting names

  public function getEmployeeName(int $id): string
  {
    try {
      $employee = Employee::find($id);
      return $employee ? $employee->FullName : __('N/A');
    } catch (\Exception $e) {
      Log::error('Dashboard: Error getting employee name.', ['employee_id' => $id, 'exception' => $e]);
      return __('Error');
    }
  }

  public function getLeaveType(int $id): string
  {
    try {
      $leaveType = Leave::find($id);
      return $leaveType ? $leaveType->name : __('N/A');
    } catch (\Exception $e) {
      Log::error('Dashboard: Error getting leave type name.', ['leave_id' => $id, 'exception' => $e]);
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
