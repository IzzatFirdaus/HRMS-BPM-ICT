<?php

use App\Livewire\ContactUs;
use App\Livewire\Dashboard;
use App\Livewire\Settings\Users as SettingsUsers; // Alias existing Users Livewire component
use App\Livewire\LoanRequestForm; // New MOTAC Livewire Component for user request
use App\Livewire\Misc\ComingSoon;
use App\Livewire\Assets\Inventory; // Existing HRMS Inventory
use App\Livewire\ApprovalDashboard; // New MOTAC Livewire Component for approvers
use App\Livewire\Assets\Categories; // Existing HRMS Asset Categories
use Illuminate\Support\Facades\Route;
use App\Livewire\EmailApplicationForm; // New MOTAC Livewire Component for user request
use App\Livewire\HumanResource\Holidays;
use App\Livewire\HumanResource\Messages;
use App\Livewire\HumanResource\Discounts;
use App\Livewire\HumanResource\Statistics;

// Import new MOTAC Controllers
use App\Http\Controllers\EmailApplicationController; // Controller for Email Application CRUD/actions
use App\Http\Controllers\LoanApplicationController; // Controller for Loan Application CRUD/actions
use App\Http\Controllers\LoanTransactionController; // Controller for Loan Transaction actions (issuance/return)

// Import Admin Controllers (including the corrected EquipmentController)
use App\Http\Controllers\Admin\EquipmentController; // Admin Controller for Equipment CRUD
use App\Http\Controllers\Admin\UserController as AdminUserController; // Admin Controller for User management
use App\Http\Controllers\Admin\GradeController; // Assuming a GradeController for managing grades

// Import Report Controller
use App\Http\Controllers\ReportController; // Controller for generating various reports

// Import existing HRMS Controllers and Livewire components (ensure correct namespaces)
use App\Http\Controllers\language\LanguageController;
use App\Livewire\HumanResource\Structure\Departments;
use App\Livewire\HumanResource\Structure\EmployeeInfo;
use App\Livewire\HumanResource\Attendance\Fingerprints;
use App\Livewire\HumanResource\Attendance\Leaves;
use App\Livewire\HumanResource\Structure\Centers;
use App\Livewire\HumanResource\Structure\Employees; // Assuming this is the Employee Livewire component
use App\Livewire\HumanResource\Structure\Positions as StructurePositions; // Alias existing Positions Livewire component


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Language Switching Route (Existing)
Route::get('lang/{locale}', [LanguageController::class, 'swap'])->name('language.swap'); // Added name for clarity

// Authenticated Routes - Apply core authentication and verification middleware
Route::middleware([
  'auth:sanctum',
  config('jetstream.auth_session'), // Keep if using Jetstream
  'verified',
  'allow_admin_during_maintenance', // Keep existing HRMS middleware
])->group(function () {

  // Existing HRMS Dashboard Routes
  // Ensure roles match your application's role system
  Route::group(['middleware' => ['role:Admin|AM|CC|CR|HR']], function () {
    Route::redirect('/', '/dashboard');
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
  });

  // Existing HRMS Human Resource Routes
  Route::prefix('hr')->group(function () {
    // Attendance Routes
    Route::prefix('attendance')->middleware(['role:Admin|HR|CC'])->group(function () {
      Route::get('/fingerprints', Fingerprints::class)->middleware(['role:Admin|HR'])->name('attendance-fingerprints');
      Route::get('/leaves', Leaves::class)->name('attendance-leaves');
    });

    // Structure Routes
    // Note: Departments, Positions, Employees Livewire components exist.
    // We will add routes for managing MOTAC specific Grades separately,
    // potentially under the 'admin' group or a new 'organizational-structure' group.
    Route::prefix('structure')->middleware(['role:Admin|HR'])->group(function () {
      Route::get('/centers', Centers::class)->name('structure-centers');
      Route::get('/departments', Departments::class)->name('structure-departments');
      Route::get('/positions', StructurePositions::class)->name('structure-positions');
      Route::get('/employees', Employees::class)->name('structure-employees'); // HRMS Employee list
      Route::get('/employee/{id?}', EmployeeInfo::class)->name('structure-employees-info'); // HRMS Employee details
    });

    // Other HR Routes
    Route::middleware(['role:Admin|HR'])->group(function () {
      Route::get('/messages', Messages::class)->name('messages');
      Route::get('/discounts', Discounts::class)->name('discounts');
      Route::get('/holidays', Holidays::class)->name('holidays');
      Route::get('/statistics', Statistics::class)->name('statistics');
    });
  });

  // Existing HRMS Settings Routes
  Route::prefix('settings')->middleware(['role:Admin'])->group(function () {
    Route::get('/users', SettingsUsers::class)->name('settings-users'); // Existing Livewire User management
    Route::get('/roles', ComingSoon::class)->name('settings-roles'); // Placeholder
    Route::get('/permissions', ComingSoon::class)->name('settings-permissions'); // Placeholder
  });

  // Existing HRMS Assets Routes (Note: /assets prefix here vs /admin/equipment below)
  Route::prefix('assets')->group(function () {
    // This might be the user-facing view of assets or a different admin view
    Route::get('/inventory', Inventory::class)->middleware(['role:Admin|AM'])->name('inventory'); // Existing Assets Inventory
    Route::get('/categories', Categories::class)->middleware(['role:Admin|AM'])->name('categories'); // Existing Assets Categories

    // Updated: Route for the Reports Index page
    Route::get('/report', [ReportController::class, 'index'])->middleware(['role:Admin|AM|HR'])->name('reports.index'); // Use the index method

    // Route::get('/transfers', ComingSoon::class)->name('transfers'); // Placeholder
  });


  // 👇 New MOTAC Integrated Resource Management Routes 👇

  // User-facing Application Forms (Accessible to all authenticated users)
  Route::get('/resource-management/email-application/create', EmailApplicationForm::class)->name('email-applications.create');
  Route::get('/resource-management/loan-application/create', LoanRequestForm::class)->name('loan-applications.create');

  // Routes for users to view their own applications
  Route::prefix('my-applications')->name('my-applications.')->group(function () {
    // Uses the index method of the respective controllers, policies will filter results
    Route::get('/email', [EmailApplicationController::class, 'index'])->name('email.index');
    Route::get('/loan', [LoanApplicationController::class, 'index'])->name('loan.index');
    // Route to view a specific application detail (show method)
    Route::get('/email/{emailApplication}', [EmailApplicationController::class, 'show'])->name('email.show');
    Route::get('/loan/{loanApplication}', [LoanApplicationController::class, 'show'])->name('loan.show');
  });


  // Approvals Dashboard (Accessible to users with the required grade level for approval)
  // Ensure the 'grade' middleware is registered and configured (e.g., in app/Http/Kernel.php)
  // config('motac.approval.min_approver_grade_level') should be defined in a config file
  Route::get('/resource-management/approvals', ApprovalDashboard::class)
    ->middleware('grade:' . config('motac.approval.min_approver_grade_level'))
    ->name('approvals.index'); // Name changed to index for consistency

  // Routes for Approvers to view and act on applications
  Route::prefix('approvals')->name('approvals.')->group(function () {
    // Route to view a specific application detail for approval
    Route::get('/email/{emailApplication}', [EmailApplicationController::class, 'showForApproval'])->name('email.show'); // Assuming a dedicated method for approval view
    Route::get('/loan/{loanApplication}', [LoanApplicationController::class, 'showForApproval'])->name('loan.show'); // Assuming a dedicated method for approval view

    // Routes for handling approval actions (approve/reject)
    // Policies should protect these actions (can('approve', $application))
    Route::post('/email/{emailApplication}/approve', [EmailApplicationController::class, 'approve'])->name('email.approve');
    Route::post('/email/{emailApplication}/reject', [EmailApplicationController::class, 'reject'])->name('email.reject');
    Route::post('/loan/{loanApplication}/approve', [LoanApplicationController::class, 'approve'])->name('loan.approve');
    Route::post('/loan/{loanApplication}/reject', [LoanApplicationController::class, 'reject'])->name('loan.reject');

    // Route for Approval History (Accessible to approvers)
    Route::get('/history', [ReportController::class, 'approvalHistory'])->name('history'); // Using ReportController for history listing
  });


  // Admin and BPM Staff Routes for Resource Management
  // Grouping under 'resource-management/admin' prefix for clarity
  Route::group(['prefix' => 'resource-management/admin', 'as' => 'resource-management.admin.', 'middleware' => ['auth', 'role:Admin|BPM']], function () { // Assuming 'BPM' role for BPM staff

    // Manage Users (Admin only)
    // Uses App\Http\Controllers\Admin\UserController
    Route::resource('users', AdminUserController::class)->middleware('role:Admin'); // Restrict to Admin role

    // Manage Equipment Assets (Admin/BPM)
    // Uses App\Http\Controllers\Admin\EquipmentController
    Route::resource('equipment', EquipmentController::class); // Accessible to Admin and BPM roles

    // Manage Organizational Data specific to MOTAC (Grades, etc.) (Admin only)
    // Assuming you have Controllers for these in the Admin namespace
    Route::resource('grades', GradeController::class)->middleware('role:Admin'); // Example using Admin namespace

    // BPM Staff Interface for Issuance and Return
    // Protect these routes with roles/permissions appropriate for BPM staff
    Route::prefix('bpm')->name('bpm.')->middleware('role:BPM')->group(function () { // Restrict to BPM role
      // List of outstanding loans for BPM staff to issue
      // Uses LoanApplicationController to list applications ready for issuance
      Route::get('/outstanding-loans', [LoanApplicationController::class, 'outstandingLoansList'])->name('outstanding-loans'); // Assuming a method to list outstanding loans

      // Form to view a loan application and issue equipment
      // Uses LoanApplicationController
      Route::get('/issue/{loanApplication}', [LoanApplicationController::class, 'issueEquipmentForm'])->name('issue.form');
      // Route to process the equipment issuance
      // Uses LoanTransactionController for creating the transaction
      Route::post('/issue/{loanApplication}', [LoanTransactionController::class, 'issue'])->name('issue');

      // List of currently issued loans for BPM staff to return
      // Uses LoanTransactionController to list issued transactions
      Route::get('/issued-loans', [LoanTransactionController::class, 'issuedLoansList'])->name('issued-loans'); // Assuming a method to list issued loans

      // Form to view a loan transaction and record return
      // Uses LoanTransactionController
      Route::get('/return/{loanTransaction}', [LoanTransactionController::class, 'returnEquipmentForm'])->name('return.form');
      // Route to process the equipment return
      // Uses LoanTransactionController
      Route::post('/return/{loanTransaction}', [LoanTransactionController::class, 'processReturn'])->name('return');

      // Route to view a specific loan transaction detail
      Route::get('/transactions/{loanTransaction}', [LoanTransactionController::class, 'show'])->name('transactions.show');
    });

    // Reporting routes for admins (Admin only)
    // Using ReportController
    Route::prefix('reports')->name('reports.')->middleware('role:Admin')->group(function () { // Restrict to Admin role
      Route::get('/equipment', [ReportController::class, 'equipment'])->name('equipment');
      Route::get('/email-accounts', [ReportController::class, 'emailAccounts'])->name('email-accounts');
      // Assuming you have a loanApplications method in ReportController
      Route::get('/loan-applications', [ReportController::class, 'loanApplications'])->name('loan-applications');
      Route::get('/user-activity', [ReportController::class, 'userActivity'])->name('user-activity');
      // Add other reports as needed
    });

    // Add other admin/management routes here (e.g., for managing Departments, Positions if needed in Admin RM)

  });

  // ☝️ End New MOTAC Integrated Resource Management Routes ☝️

});


// Public Contact Us Route (Existing)
Route::get('/contact-us', ContactUs::class)->name('contact-us');

// Deploy Webhook Route (Existing)
Route::webhooks('/deploy');

// Authentication routes are typically handled by Breeze/Jetstream and included automatically
// For example: require __DIR__.'/auth.php'; if using Breeze

// Ensure that unauthenticated users trying to access 'auth' middleware routes are redirected to login
// This is handled by the Authenticate middleware in app/Http/Middleware/Authenticate.php
