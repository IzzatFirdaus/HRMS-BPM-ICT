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
      // Fingerprints route further restricted to Admin|HR
      Route::get('/fingerprints', Fingerprints::class)->middleware(['role:Admin|HR'])->name('attendance-fingerprints');
      // Leaves route accessible to Admin|HR|CC
      Route::get('/leaves', Leaves::class)->name('attendance-leaves');
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
    Route::get('/users', SettingsUsers::class)->name('settings-users'); // Existing Livewire User management
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
    Route::get('/email-application/create', EmailApplicationForm::class)->name('email-applications.create');
    Route::get('/loan-application/create', LoanRequestForm::class)->name('loan-applications.create');
  });


  // Routes for users to view their own applications
  Route::prefix('my-applications')->name('my-applications.')->group(function () {
    // Lists the current user's email and loan applications. Policies will enforce ownership.
    Route::get('/email', [EmailApplicationController::class, 'index'])->name('email.index');
    Route::get('/loan', [LoanApplicationController::class, 'index'])->name('loan.index');
    // Routes to view a specific application detail (show method). Policies will enforce ownership.
    Route::get('/email/{emailApplication}', [EmailApplicationController::class, 'show'])->name('email.show');
    Route::get('/loan/{loanApplication}', [LoanApplicationController::class, 'show'])->name('loan.show');
  });


  // Approvals Dashboard (Accessible to users with the required grade level for approval)
  // Ensure the 'grade' middleware is registered and configured (e.g., in app/Http/Kernel.php)
  // config('motac.approval.min_approver_grade_level') should be defined in a config file
  Route::get('/resource-management/approvals', ApprovalDashboard::class)
    ->middleware('grade:' . config('motac.approval.min_approver_grade_level')) // Custom middleware for approver access
    ->name('approvals.index'); // Dashboard for pending approvals


  // Routes for Approvers to view and act on applications
  Route::prefix('approvals')->name('approvals.')->group(function () {
    // Routes to view a specific application detail for approval. Policies will check if the user is an assigned approver.
    // Assuming dedicated controller methods like showForApproval for this context.
    Route::get('/email/{emailApplication}', [EmailApplicationController::class, 'showForApproval'])->name('email.show');
    Route::get('/loan/{loanApplication}', [LoanApplicationController::class, 'showForApproval'])->name('loan.show');

    // Routes for handling approval actions (approve/reject). Policies should protect these actions.
    // These routes typically receive a form submission with approval/rejection decision and comments.
    Route::post('/email/{emailApplication}/approve', [EmailApplicationController::class, 'approve'])->name('email.approve');
    Route::post('/email/{emailApplication}/reject', [EmailApplicationController::class, 'reject'])->name('email.reject');
    Route::post('/loan/{loanApplication}/approve', [LoanApplicationController::class, 'approve'])->name('loan.approve');
    Route::post('/loan/{loanApplication}/reject', [LoanApplicationController::class, 'reject'])->name('loan.reject');

    // Route for Approval History (Accessible to approvers or Admin/BPM)
    // Decide on the specific role middleware needed here. ReportController likely handles filtering by user if needed.
    Route::get('/history', [ReportController::class, 'approvalHistory'])->name('history'); // Using ReportController for history listing
  });


  // Admin and BPM Staff Routes for Resource Management
  // Grouping under 'resource-management/admin' prefix for clarity and applying roles
  Route::group(['prefix' => 'resource-management/admin', 'as' => 'resource-management.admin.', 'middleware' => ['role:Admin|BPM']], function () { // Apply Admin or BPM role middleware

    // Manage Users (Admin only) - Uses App\Http\Controllers\Admin\UserController
    // Resource routes for full CRUD on users, restricted to Admin role.
    Route::resource('users', AdminUserController::class)->middleware('role:Admin');

    // Manage Equipment Assets (Admin/BPM) - Uses App\Http\Controllers\Admin\EquipmentController
    // Resource routes for full CRUD on equipment, accessible to Admin and BPM roles.
    Route::resource('equipment', EquipmentController::class);

    // Manage Organizational Data specific to MOTAC (Grades, etc.) (Admin only)
    // Assuming Controllers for these in the Admin namespace and resource routes for CRUD.
    Route::resource('grades', GradeController::class)->middleware('role:Admin'); // Example using Admin namespace

    // BPM Staff Interface for Issuance and Return processes
    // Protect these routes with roles/permissions appropriate for BPM staff (e.g., 'role:BPM')
    Route::prefix('bpm')->name('bpm.')->middleware('role:BPM')->group(function () { // Restrict to BPM role

      // List of loan applications approved and ready for issuance by BPM staff
      // Uses LoanApplicationController to list applications with status 'ready_for_issuance'.
      Route::get('/outstanding-loans', [LoanApplicationController::class, 'outstandingLoansList'])->name('outstanding-loans'); // Assuming a method to list outstanding loans

      // Form to view a loan application and record equipment issuance
      // Uses LoanApplicationController to display the form.
      Route::get('/issue/{loanApplication}', [LoanApplicationController::class, 'issueEquipmentForm'])->name('issue.form');
      // Route to process the equipment issuance (creates LoanTransaction record(s))
      // Uses LoanTransactionController for the action.
      Route::post('/issue/{loanApplication}', [LoanTransactionController::class, 'issue'])->name('issue');

      // List of currently issued loan transactions for BPM staff to manage returns
      // Uses LoanTransactionController to list transactions with status 'issued'.
      Route::get('/issued-loans', [LoanTransactionController::class, 'issuedLoansList'])->name('issued-loans'); // Assuming a method to list issued loans

      // Form to view an issued loan transaction and record equipment return
      // Uses LoanTransactionController to display the form.
      Route::get('/return/{loanTransaction}', [LoanTransactionController::class, 'returnEquipmentForm'])->name('return.form');
      // Route to process the equipment return (updates LoanTransaction status/details)
      // Uses LoanTransactionController for the action.
      Route::post('/return/{loanTransaction}', [LoanTransactionController::class, 'processReturn'])->name('return');

      // Route to view a specific loan transaction detail (could be issuance or return)
      Route::get('/transactions/{loanTransaction}', [LoanTransactionController::class, 'show'])->name('transactions.show');
    });

    // Reporting routes for admins (Admin only) - Using ReportController
    Route::prefix('reports')->name('reports.')->middleware('role:Admin')->group(function () { // Restrict to Admin role
      Route::get('/equipment', [ReportController::class, 'equipment'])->name('equipment'); // Report on equipment inventory/status
      Route::get('/email-accounts', [ReportController::class, 'emailAccounts'])->name('email-accounts'); // Report on email accounts
      // Assuming you have a loanApplications method in ReportController for loan application reports
      Route::get('/loan-applications', [ReportController::class, 'loanApplications'])->name('loan-applications'); // Report on loan applications status/history
      Route::get('/user-activity', [ReportController::class, 'userActivity'])->name('user-activity'); // Report on user actions/activity
      // Add other reports as needed (e.g., damage reports, loss reports)
    });

    // Add other admin/management routes here (e.g., for managing Definitions, Configurations)

  });

  // ☝️ End New MOTAC Integrated Resource Management (IRM) Routes ☝️

});

// Ensure that unauthenticated users trying to access 'auth' middleware routes are redirected to login
// This is handled by the Authenticate middleware in app/Http/Middleware/Authenticate.php (part of default auth scaffolding)
