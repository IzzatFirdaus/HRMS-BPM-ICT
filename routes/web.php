<?php

use App\Livewire\ContactUs;
use App\Livewire\Dashboard;
use App\Livewire\Settings\Users as SettingsUsers; // Alias existing Users Livewire component
use App\Livewire\Settings\CreateUser as CreateSettingsUser; // Import Livewire component for creating users (Hypothetical)
use App\Livewire\Settings\ShowUser as ShowSettingsUser; // Import Livewire component for showing a user (Hypothetical)
use App\Livewire\Settings\EditUser as EditSettingsUser; // Import Livewire component for editing a user (Hypothetical)

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
use App\Livewire\HumanResource\Attendance\Fingerprints; // Ensure this Livewire component is imported
use App\Livewire\HumanResource\Attendance\Leaves; // Ensure this Livewire component is imported
use App\Livewire\HumanResource\Structure\Centers;
use App\Livewire\HumanResource\Structure\Employees; // Assuming this is the Employee Livewire component
use App\Livewire\HumanResource\Structure\Positions as StructurePositions; // Alias existing Positions Livewire component


/**
 * Web Routes
 *
 * This file contains the web routes for the application.
 * These routes are loaded by the RouteServiceProvider and all of them will be assigned to the "web" middleware group.
 * Routes requiring authentication are grouped under the 'auth' middleware group.
 * Routes for the new MOTAC Integrated Resource Management system are grouped logically for users, approvers, and admin/BPM staff.
 */

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

// Public Contact Us Route (Existing)
Route::get('/contact-us', ContactUs::class)->name('contact-us');

// Deploy Webhook Route (Existing) - Typically used for automated deployments
Route::webhooks('/deploy');

// Authentication routes are typically handled by packages like Breeze/Jetstream and included automatically
// For example: require __DIR__.'/auth.php'; if using Breeze/Jetstream

// Authenticated Routes - Apply core authentication, session, and verification middleware
Route::middleware([
  'auth:sanctum', // Ensure user is authenticated via Sanctum (common for SPAs or API-backed)
  config('jetstream.auth_session'), // Ensure standard session authentication
  'verified', // Ensure user's email is verified
  'allow_admin_during_maintenance', // Keep existing HRMS middleware for maintenance mode bypass
])->group(function () {

  // Existing HRMS Dashboard Routes
  // Redirect root to dashboard for authenticated users
  Route::redirect('/', '/dashboard');

  // Dashboard accessible based on specific HRMS roles
  // Ensure roles match your application's role system configuration (e.g., spatie/laravel-permission)
  Route::group(['middleware' => ['role:Admin|AM|CC|CR|HR']], function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
  });

  // Existing HRMS Human Resource Routes
  Route::prefix('hr')->group(function () {
    // Attendance Routes, restricted by roles
    Route::prefix('attendance')->middleware(['role:Admin|HR|CC'])->group(function () {
      // Fingerprints route further restricted to Admin|HR - Route Name: attendance-fingerprints
      Route::get('/fingerprints', Fingerprints::class)->middleware(['role:Admin|HR'])->name('attendance-fingerprints'); // <-- ENSURE THIS LINE IS PRESENT AND HAS THE CORRECT NAME
      // Leaves route accessible to Admin|HR|CC - Route Name: attendance-leaves
      Route::get('/leaves', Leaves::class)->name('attendance-leaves'); // <-- ENSURE THIS LINE IS PRESENT AND HAS THE CORRECT NAME
    });

    // Structure Routes for managing HR organizational data, restricted by roles
    Route::prefix('structure')->middleware(['role:Admin|HR'])->group(function () {
      Route::get('/centers', Centers::class)->name('structure-centers');
      Route::get('/departments', Departments::class)->name('structure-departments');
      Route::get('/positions', StructurePositions::class)->name('structure-positions');
      Route::get('/employees', Employees::class)->name('structure-employees'); // HRMS Employee list
      Route::get('/employee/{id?}', EmployeeInfo::class)->name('structure-employees-info'); // HRMS Employee details
    });

    // Other HR Routes, restricted by roles
    Route::middleware(['role:Admin|HR'])->group(function () {
      Route::get('/messages', Messages::class)->name('messages');
      Route::get('/discounts', Discounts::class)->name('discounts');
      Route::get('/holidays', Holidays::class)->name('holidays');
      Route::get('/statistics', Statistics::class)->name('statistics');
    });
  });

  // Existing HRMS Settings Routes, restricted to Admin role
  Route::prefix('settings')->middleware(['role:Admin'])->group(function () {
    // User management routes
    // List view
    Route::get('/users', SettingsUsers::class)->name('settings-users');
    // Create view - Assumes App\Livewire\Settings\CreateUser exists
    Route::get('/users/create', CreateSettingsUser::class)->name('settings-users.create');
    // Show view - Assumes App\Livewire\Settings\ShowUser exists and takes a User model or ID
    Route::get('/users/{user}', ShowSettingsUser::class)->name('settings-users.show'); // <-- ADD THIS LINE
    // Edit view - Assumes App\Livewire\Settings\EditUser exists and takes a User model or ID
    Route::get('/users/{user}/edit', EditSettingsUser::class)->name('settings-users.edit'); // <-- ADD THIS LINE


    Route::get('/roles', ComingSoon::class)->name('settings-roles'); // Placeholder
    Route::get('/permissions', ComingSoon::class)->name('settings-permissions'); // Placeholder
  });

  // Existing HRMS Assets Routes (Note: /assets prefix here vs /admin/equipment below for RM)
  Route::prefix('assets')->group(function () {
    // Inventory and Categories accessible based on roles
    Route::get('/inventory', Inventory::class)->middleware(['role:Admin|AM'])->name('inventory'); // Existing Assets Inventory
    Route::get('/categories', Categories::class)->middleware(['role:Admin|AM'])->name('categories'); // Existing Assets Categories

    // Route for the Reports Index page, accessible based on roles
    Route::get('/report', [ReportController::class, 'index'])->middleware(['role:Admin|AM|HR'])->name('reports.index'); // Use the index method

    // Placeholder for other asset-related features
    // Route::get('/transfers', ComingSoon::class)->name('transfers');
  });


  // 👇 New MOTAC Integrated Resource Management (IRM) Routes 👇

  // User-facing Application Forms (Accessible to all authenticated users)
  Route::prefix('resource-management')->name('resource-management.')->group(function () {
    // UPDATED: Added optional {emailApplication} parameter for editing functionality
    Route::get('/email-application/create/{emailApplication?}', EmailApplicationForm::class)->name('email-applications.create');
    Route::get('/loan-application/create', LoanRequestForm::class)->name('loan-applications.create');
    // ... other resource-management routes
  });


  // Routes for users to view their own applications
  Route::prefix('my-applications')->name('my-applications.')->group(function () {
    // Route to list email applications - Renders the email applications index view
    Route::get('/email', [EmailApplicationController::class, 'index'])->name('email.index'); // <-- View needs to be at resources/views/my-applications/email/index.blade.php
    Route::get('/loan', [LoanApplicationController::class, 'index'])->name('loan.index');
    // Route to show a specific email application
    Route::get('/email/{emailApplication}', [EmailApplicationController::class, 'show'])->name('email.show');
    Route::get('/loan/{loanApplication}', [LoanApplicationController::class, 'show'])->name('loan.show');
  });


  // Approvals Dashboard (Accessible to users with the required grade level for approval)
  Route::get('/resource-management/approvals', ApprovalDashboard::class)
    ->middleware('grade:' . config('motac.approval.min_approver_grade_level')) // Custom middleware for approver access
    ->name('approvals.index'); // Dashboard for pending approvals


  // Routes for Approvers to view and act on applications
  Route::prefix('approvals')->name('approvals.')->group(function () {
    Route::get('/email/{emailApplication}', [EmailApplicationController::class, 'showForApproval'])->name('email.show');
    Route::get('/loan/{loanApplication}', [LoanApplicationController::class, 'showForApproval'])->name('loan.show');
    Route::post('/email/{emailApplication}/approve', [EmailApplicationController::class, 'approve'])->name('email.approve');
    Route::post('/loan/{loanApplication}/reject', [LoanApplicationController::class, 'reject'])->name('loan.reject');
    Route::post('/loan/{loanApplication}/approve', [LoanApplicationController::class, 'approve'])->name('loan.approve');
    Route::post('/loan/{loanApplication}/reject', [LoanApplicationController::class, 'reject'])->name('loan.reject');

    // Route definition for Approvals History - Pointing to loanHistory METHOD in ReportController
    // This route is called from the sidebar menu (vertical-menu.blade.php).
    Route::get('/history', [ReportController::class, 'loanHistory'])->name('history'); // Route name: approvals.history

    // Note: The admin Loan History Report is at admin.reports.loan-history,
    // also using ReportController::loanHistory().
  });


  // Admin and BPM Staff Routes for Resource Management
  Route::group(['prefix' => 'resource-management/admin', 'as' => 'admin.', 'middleware' => ['role:Admin|BPM']], function () {

    Route::resource('users', AdminUserController::class)->middleware('role:Admin'); // Resource routes for Admin User Controller
    Route::resource('equipment', EquipmentController::class); // Resource routes for Equipment Controller
    Route::resource('grades', GradeController::class)->middleware('role:Admin'); // Resource routes for Grade Controller

    Route::prefix('bpm')->name('bpm.')->middleware('role:BPM')->group(function () {
      Route::get('/outstanding-loans', [LoanApplicationController::class, 'outstandingLoansList'])->name('outstanding-loans');
      Route::get('/issue/{loanApplication}', [LoanApplicationController::class, 'issueEquipmentForm'])->name('issue.form');
      Route::post('/issue/{loanApplication}', [LoanTransactionController::class, 'issue'])->name('issue');
      Route::get('/issued-loans', [LoanTransactionController::class, 'issuedLoansList'])->name('issued-loans');
      Route::get('/return/{loanTransaction}', [LoanTransactionController::class, 'returnEquipmentForm'])->name('return.form');
      Route::post('/return/{loanTransaction}', [LoanTransactionController::class, 'processReturn'])->name('return');
      Route::get('/transactions/{loanTransaction}', [LoanTransactionController::class, 'show'])->name('transactions.show');
    });

    // Reporting routes for admins (Admin only)
    Route::prefix('reports')->name('reports.')->middleware('role:Admin')->group(function () {
      // Route names: admin.reports.*
      Route::get('/equipment', [ReportController::class, 'equipment'])->name('equipment'); // Report on equipment inventory/status - View: resources/views/reports/equipment.blade.php
      Route::get('/email-accounts', [ReportController::class, 'emailAccounts'])->name('email-accounts'); // Report on email accounts - View: resources/views/reports/email_accounts.blade.php
      Route::get('/loan-applications', [ReportController::class, 'loanApplications'])->name('loan-applications'); // Report on loan applications status/history - View: resources/views/reports/loan_applications.blade.php
      Route::get('/user-activity', [ReportController::class, 'userActivity'])->name('user-activity'); // Report on user actions/activity - View: resources/views/reports/user-activity.blade.php
      // Loan History Report (Admin Report) - Uses loanHistory method - Route name: admin.reports.loan-history - View: resources/views/reports/loan-history.blade.php
      Route::get('/loan-history', [ReportController::class, 'loanHistory'])->name('loan-history');
      // Add other reports as needed
    });

    // Add other admin/management routes here
  });

  // ☝️ End New MOTAC Integrated Resource Management (IRM) Routes ☝️

});

// Ensure that unauthenticated users trying to access 'auth' middleware routes are redirected to login
// This is handled by the Authenticate middleware (part of default auth scaffolding)
