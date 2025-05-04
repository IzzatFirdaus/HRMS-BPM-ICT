<?php

namespace App\Livewire;

// --- Standard Namespace/Class Use Statements (must be before the class) ---
use Livewire\Component;
use App\Models\User;
use App\Models\Employee;
use App\Models\Message; // For SMS stats
use App\Models\EmailApplication; // For Email/User ID stats/lists
use App\Models\LoanApplication; // For Loan stats/lists
use App\Models\LoanTransaction; // For Loan transaction lists
use App\Models\Equipment; // For Available Equipment count (using availability_status)
use App\Models\Changelog; // If still needed on the dashboard (using string audit columns)
use App\Models\Approval; // For pending approvals count/lists
use App\Jobs\sendPendingMessages; // If SMS sending is still triggered from dashboard
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB; // For raw queries if needed for counts
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number; // For formatting numbers
use Throwable;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder; // For type hinting query builders
use Illuminate\View\View; // For type hinting render return type
use Livewire\Attributes\Computed; // For computed properties

// Import necessary models for eager loading relations if needed (e.g., for lists)
// use App\Models\Leave; // Keep if fetching leave types for "Add New" dropdown (though moved to blade now)
// use App\Models\EmployeeLeave; // Removed, assuming dedicated Leaves component handles this
// use App\Models\Position; // To interact with the positions table
// use App\Models\Center; // To interact with the centers table
// use App\Models\Department; // To interact with the departments table
// use App\Models\Grade; // To interact with the grades table
// use App\Models\Contract; // To interact with the contracts table
// use App\Models\Timeline; // To interact with the timelines table


class Dashboard extends Component
{
  // --- Trait Use Statements (must be inside the class body) ---
  // Removed WithPagination as it's unlikely the dashboard table needs pagination
  use \Illuminate\Foundation\Auth\Access\AuthorizesRequests; // Keep for authorization

  // 👉 State Variables (Public properties that sync with the view)
  // Minimize state variables for the dashboard - mostly display derived data

  // Initialized in mount
  public array $accountBalance = ['status' => 400, 'balance' => '---', 'is_active' => '---'];

  // Properties to hold data for the view - Initialize with default/empty values
  public ?string $employeeFirstName = null;
  public ?string $employeePhoto = 'profile-photos/.default-photo.jpg'; // Default employee photo URL

  // New properties for integrated system statistics - Initialize with 0 or empty
  public int $userEmailApplicationsPendingCount = 0;
  public int $pendingEmailApprovalsCount = 0;
  public int $pendingEmailProvisioningCount = 0;
  public int $userLoanApplicationsPendingCount = 0;
  public int $pendingLoanApprovalsCount = 0;
  public int $pendingLoanIssuanceCount = 0;
  public int $equipmentAvailableCount = 0;
  public int $activeEmployeesCount = 0; // Updated from Collection to count if only count is needed

  // Properties for integrated system lists - Initialize with empty collections
  public Collection $userPendingApplications; // Mixed collection of Email and Loan Applications
  public Collection $pendingLoanIssuanceList; // List of LoanApplications pending issuance for BPM
  public Collection $itemsDueForReturnList; // List of LoanTransactions due for return for BPM

  // Existing property for SMS status
  public array $messagesStatus = ['sent' => 0, 'unsent' => 0];

  // Existing property for changelogs (if still needed)
  public Collection $changelogs;


  // Protected properties to hold authenticated user/employee
  protected ?User $loggedInUser = null;
  protected ?Employee $loggedInEmployee = null;


  // 👉 Computed Properties (Livewire v3+) - Define computed properties for data that is expensive to compute on every render
  // or that is derived from other properties.

  // Computed property for SMS status (kept as is, relies on 'messages' table)
  #[Computed]
  public function messagesStatus(): array
  {
    try {
      // Using the 'messages' table defined in 2023_11_10_162228_create_messages_table.php
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

  // Computed property for changelogs (kept as is, relies on 'changelogs' table)
  #[Computed]
  public function changelogs(): Collection
  {
    try {
      // Using the 'changelogs' table defined in 2024_04_16_105426_create_changelogs_table.php
      // Note: This table uses string audit columns, but that doesn't affect fetching here.
      return Changelog::latest()->limit(5)->get(); // Limit for dashboard display
    } catch (\Exception $e) {
      Log::error('Dashboard: Error fetching changelogs.', ['exception' => $e]);
      return collect();
    }
  }


  // 👉 Lifecycle Hook - mount is called once when the component is initialized

  public function mount(): void
  {
    $this->loggedInUser = Auth::user();

    if (!$this->loggedInUser) {
      // Middleware should handle this, but a safeguard is fine.
      Log::warning('Dashboard mount called for unauthenticated user.');
      return;
    }

    // Eager load necessary relationships for the logged-in user and their employee
    // Loading 'employee.timeline.center' uses 'timelines', 'centers', and 'positions' tables
    // Loading 'roles', 'permissions' uses Spatie package tables
    $this->loggedInUser->load(['employee.timeline.center', 'roles', 'permissions']);


    $this->loggedInEmployee = $this->loggedInUser->employee;

    if ($this->loggedInEmployee) {
      $this->employeeFirstName = $this->loggedInEmployee->full_name ?? $this->loggedInEmployee->name ?? null;
      $this->employeePhoto = $this->loggedInEmployee->profile_photo_path ?? 'profile-photos/.default-photo.jpg';
      // Fetch active employees count (or list if needed, though count is probably sufficient for stats)
      // This relies on the 'employees' table and its 'status' column (from add_motac_columns_to_users_table?)
      // Assuming Employee model has a scope or method for active employees
      $center = $this->loggedInEmployee->timeline?->center;
      // Assuming activeEmployees() is a method or scope on Center/Employee model that queries the 'employees' table
      $this->activeEmployeesCount = $center ? $center->activeEmployees()->count() : 0;
    } else {
      Log::warning('Dashboard: Authenticated user has no linked employee record.', ['user_id' => $this->loggedInUser->id]);
      $this->employeeFirstName = $this->loggedInUser->name ?? __('User'); // Fallback to user name
      $this->employeePhoto = 'profile-photos/.default-photo.jpg';
      $this->activeEmployeesCount = 0;
    }

    // --- Fetch Data for Integrated System Dashboard ---

    // Fetch user's pending applications (for isRegularUser section)
    // This requires querying EmailApplication and LoanApplication tables for the logged-in user
    // with 'draft' or 'pending_support' statuses, and merging the results.
    // Eager load relationships needed for the Blade view (e.g., 'user', 'items' for LoanApplication)
    $this->userPendingApplications = $this->fetchUserPendingApplications($this->loggedInUser);


    // Fetch statistics and lists based on user roles
    // Using hasAnyRole check from Spatie for cleaner logic based on web.php roles
    $isAdmin = $this->loggedInUser->hasRole('Admin');
    $isApprover = $this->loggedInUser->hasAnyRole(['Approver', 'AM', 'CC', 'CR', 'HR']);
    $isBpmStaff = $this->loggedInUser->hasRole('BPM');

    if ($isAdmin || $isApprover) {
      // Fetch pending email approvals for approvers/admins
      // This queries the 'approvals' table
      $this->pendingEmailApprovalsCount = $this->fetchPendingApprovalsCount($this->loggedInUser, EmailApplication::class);
      // Fetch pending loan approvals for approvers/admins
      $this->pendingLoanApprovalsCount = $this->fetchPendingApprovalsCount($this->loggedInUser, LoanApplication::class);
    }

    if ($isAdmin || $isBpmStaff) {
      // Fetch pending email provisioning count for BPM/admins
      // This queries the 'email_applications' table status
      $this->pendingEmailProvisioningCount = EmailApplication::whereIn('status', ['pending_admin', 'processing'])->count();

      // Fetch available equipment count for BPM/admins
      // This queries the 'equipment' table using the 'availability_status' column name
      $this->equipmentAvailableCount = Equipment::where('availability_status', 'available')->count();

      // Fetch list of loan applications pending issuance for BPM/admins
      // This queries the 'loan_applications' table status
      // Eager load 'user' and 'items' relationships
      $this->pendingLoanIssuanceList = $this->fetchPendingLoanIssuanceList();

      // Fetch list of items due for return for BPM/admins
      // This queries the 'loan_transactions' table status and related loan applications end date
      // Eager load necessary relationships (e.g., 'loanApplication.user')
      $this->itemsDueForReturnList = $this->fetchItemsDueForReturnList();
    }

    // Fetch SMS status using the computed property
    $this->messagesStatus = $this->messagesStatus(); // Assign to public property for view

    // Fetch Changelogs using the computed property
    $this->changelogs = $this->changelogs(); // Assign to public property for view


    // --- Handle Account Balance Check (from original HRMS) ---
    try {
      // Check if the method exists before calling it
      if (method_exists($this, 'CheckAccountBalance') && is_callable([$this, 'CheckAccountBalance'])) {
        // Determine whether to pass user or employee based on what CheckAccountBalance expects
        // Assuming it might need the User model or related data from Employee
        $this->accountBalance = $this->CheckAccountBalance($this->loggedInUser); // Assuming it takes the User model
      } else {
        // Log a warning if the method is missing, but don't block component rendering
        Log::warning('Dashboard: CheckAccountBalance method not found or not callable. Using default values.');
        $this->accountBalance = ['status' => 500, 'balance' => __('Error'), 'is_active' => __('Error')];
      }
    } catch (Throwable $th) {
      Log::error("Error checking account balance for user " . ($this->loggedInUser->id ?? 'N/A') . ": " . $th->getMessage(), ['exception' => $th]);
      $this->accountBalance = ['status' => 500, 'balance' => __('Error'), 'is_active' => __('Error')];
    }

    // --- Removed Leave Management Specific Initialization ---
  }

  /**
   * Render the component view.
   * Explicitly pass all variables used in the view, including the user's role flags.
   *
   * @return \Illuminate\View\View
   */
  public function render(): View
  {
    // Access computed properties here if needed, or rely on them being accessed directly in blade
    $messagesStatus = $this->messagesStatus; // Use public property assigned in mount
    $changelogs = $this->changelogs; // Use public property assigned in mount

    // Determine user roles for view logic (done in mount, but can be passed explicitly for clarity)
    $user = $this->loggedInUser;
    $isAdmin = optional($user)->hasRole('Admin') ?? false;
    $isApprover = optional($user)->hasAnyRole(['Approver', 'AM', 'CC', 'CR', 'HR']) ?? false; // Check for any of the approver roles
    $isBpmStaff = optional($user)->hasRole('BPM') ?? false;
    $isRegularUser = !$isAdmin && !$isApprover && !$isBpmStaff; // Simplified check


    return view('livewire.dashboard', [
      'accountBalance' => $this->accountBalance,
      'messagesStatus' => $messagesStatus,
      'activeEmployeesCount' => $this->activeEmployeesCount, // Pass the count
      // 'leaveRecords' => $this->leaveRecords, // Removed, assuming dedicated Leaves component
      'userEmailApplicationsPendingCount' => $this->userEmailApplicationsPendingCount,
      'pendingEmailApprovalsCount' => $this->pendingEmailApprovalsCount,
      'pendingEmailProvisioningCount' => $this->pendingEmailProvisioningCount,
      'userLoanApplicationsPendingCount' => $this->userLoanApplicationsPendingCount,
      'pendingLoanApprovalsCount' => $this->pendingLoanApprovalsCount,
      'pendingLoanIssuanceCount' => $this->pendingLoanIssuanceCount,
      'equipmentAvailableCount' => $this->equipmentAvailableCount,
      'userPendingApplications' => $this->userPendingApplications,
      'pendingLoanIssuanceList' => $this->pendingLoanIssuanceList,
      'itemsDueForReturnList' => $this->itemsDueForReturnList,
      'changelogs' => $changelogs,
      'employeeFirstName' => $this->employeeFirstName,
      'employeePhoto' => $this->employeePhoto,
      // Pass the boolean role flags for view logic
      'isAdmin' => $isAdmin,
      'isApprover' => $isApprover,
      'isBpmStaff' => $isBpmStaff,
      'isRegularUser' => $isRegularUser,
      // Removed leave-specific state variables
    ]);
  }

  // 👉 Hook for selected employee change
  // Removed as selectedEmployeeId state and related logic are moved out

  // 👉 Action to send pending messages (kept as is)

  public function sendPendingMessages(): void
  {
    // Using the computed property's result stored in the public property
    if (($this->messagesStatus['unsent'] ?? 0) > 0) { // Added null check and default
      try {
        sendPendingMessages::dispatch();
        session()->flash('info', __('Let\'s go! Messages on their way!'));
        $this->dispatch('toastr', type: 'info', message: __('Sending messages...'));
        // Re-fetch messages status after dispatching job (optional, job might update status async)
        $this->messagesStatus = $this->messagesStatus(); // Update the public property
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
  // Removed all leave-specific methods as they belong to the dedicated Leaves component.


  // 👉 Methods to Fetch Data for Integrated System (Implemented based on your migration schemas)

  /**
   * Fetches the pending applications for the given user (Email and Loan).
   * Queries email_applications and loan_applications tables.
   *
   * @param User $user
   * @return Collection A collection of mixed EmailApplication and LoanApplication models.
   */
  protected function fetchUserPendingApplications(User $user): Collection
  {
    if (!$user) {
      return collect();
    }

    // Querying 'email_applications' table with statuses from migration
    $pendingEmailApps = $user->emailApplications()
      ->whereIn('status', ['draft', 'pending_support'])
      ->get();

    // Querying 'loan_applications' table with statuses from migration
    // Eager load relationships 'user' (the applicant) and 'items' (loan_application_items)
    $pendingLoanApps = $user->loanApplications()
      ->whereIn('status', ['draft', 'pending_support'])
      ->with(['user', 'items']) // 'user' relation likely defined on LoanApplication model
      ->get();

    // Combine the collections
    $allPendingApps = $pendingEmailApps->merge($pendingLoanApps);

    // Sort the combined collection
    $allPendingApps = $allPendingApps->sortByDesc('created_at');

    // Update the public pending counts
    $this->userEmailApplicationsPendingCount = $pendingEmailApps->count();
    $this->userLoanApplicationsPendingCount = $pendingLoanApps->count();

    return $allPendingApps;
  }

  /**
   * Fetches the count of pending approvals for the given user and approvable type.
   * Queries the 'approvals' table.
   *
   * @param User $user
   * @param string $approvableType The class name of the approvable model (e.g., EmailApplication::class).
   * @return int
   */
  protected function fetchPendingApprovalsCount(User $user, string $approvableType): int
  {
    // Fetch approvals where the user is the assigned officer and the status is 'pending'
    // and the linked approvable item has a status indicating it's still in the approval flow.
    // This logic is dependent on your specific approval workflow implementation
    // and how statuses transition. The example below is a basic count.

    // Using the 'approvals' table defined in 2025_04_22_083504_create_approvals_table.php
    try {
      $count = Approval::where('officer_id', $user->id)
        ->where('status', 'pending')
        ->where('approvable_type', $approvableType)
        // Add conditions to check the status of the linked approvable item
        // This requires a polymorphic relationship setup on the Approval model
        ->whereHasMorph('approvable', [$approvableType], function (Builder $query) {
          // Example: Only count if the application status is 'pending_support' or 'pending_admin'
          // Adjust statuses based on where in the workflow this approval step occurs
          $query->whereIn('status', ['pending_support', 'pending_admin']);
        })
        ->count();
      return $count;
    } catch (\Exception $e) {
      Log::error("Dashboard: Error fetching pending approvals count for {$approvableType}.", ['user_id' => $user->id, 'exception' => $e]);
      return 0; // Return 0 on error
    }
  }

  /**
   * Fetches the list of Loan Applications that are approved and pending issuance by BPM staff.
   * Queries the 'loan_applications' table.
   *
   * @return Collection A collection of LoanApplication models.
   */
  protected function fetchPendingLoanIssuanceList(): Collection
  {
    // Ensure the logged-in user has the correct role before fetching
    if (!$this->loggedInUser || !($this->loggedInUser->hasRole('Admin') || $this->loggedInUser->hasRole('BPM'))) {
      return collect();
    }

    // Using the 'loan_applications' table defined in 2025_04_22_083504_create_loan_applications_table.php
    // Fetch applications with status 'approved' or 'partially_issued'
    // Eager load 'user' (the applicant) and 'items' (loan_application_items) relationships
    try {
      $list = LoanApplication::whereIn('status', ['approved', 'partially_issued'])
        ->with(['user', 'items']) // 'user' and 'items' relations expected on LoanApplication model
        ->orderBy('updated_at', 'asc')
        ->get();
      return $list;
    } catch (\Exception $e) {
      Log::error('Dashboard: Error fetching pending loan issuance list.', ['user_id' => $this->loggedInUser->id, 'exception' => $e]);
      return collect(); // Return empty collection on error
    }
  }

  /**
   * Fetches the list of ICT Equipment Loan Transactions that are currently issued
   * and due for return soon or overdue, for BPM staff.
   * Queries the 'loan_transactions' table and linked 'loan_applications'.
   *
   * @return Collection A collection of LoanTransaction models.
   */
  protected function fetchItemsDueForReturnList(): Collection
  {
    // Ensure the logged-in user has the correct role before fetching
    if (!$this->loggedInUser || !($this->loggedInUser->hasRole('Admin') || $this->loggedInUser->hasRole('BPM'))) {
      return collect();
    }

    // Using the 'loan_transactions' table defined in 2025_04_22_105519_create_loan_transactions_table.php
    // Using the 'loan_applications' table defined in 2025_04_22_083504_create_loan_applications_table.php
    try {
      // Fetch 'issued' loan transactions
      $list = LoanTransaction::where('status', 'issued')
        // Filter by the loan end date from the linked loan application
        ->whereHas('loanApplication', function (Builder $query) {
          // Get transactions where the linked loan application's end date is today or in the past (overdue)
          // or within the next 7 days (approaching due).
          $query->where('loan_end_date', '<=', Carbon::now()->addDays(7)->toDateString());
        })
        // Eager load the loan application and its user (the borrower)
        ->with(['loanApplication.user']) // Relationships loanApplication on LoanTransaction, user on LoanApplication
        ->orderBy('issue_timestamp', 'asc') // Order by issue date, or could order by loan_application.loan_end_date
        ->get();
      return $list;
    } catch (\Exception $e) {
      Log::error('Dashboard: Error fetching items due for return list.', ['user_id' => $this->loggedInUser->id, 'exception' => $e]);
      return collect(); // Return empty collection on error
    }
  }


  // Add a placeholder for the CheckAccountBalance method if it's not defined elsewhere in your application.
  // This method is called in mount() but its implementation was not provided in the migrations or other shared files.
  // You need to implement the actual logic for this method if it's used.
  /*
        protected function CheckAccountBalance($userOrEmployee): array
        {
             // Implement actual logic to check account balance
             Log::info('CheckAccountBalance placeholder method called.');
             // Example placeholder return:
             // return ['status' => 200, 'balance' => '1234.50', 'is_active' => 'Yes'];
             // Or an error state:
             // return ['status' => 500, 'balance' => 'Error', 'is_active' => 'Error'];
             return ['status' => 400, 'balance' => '---', 'is_active' => '---']; // Default as initialized state
        }
     */
}
