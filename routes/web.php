<?php

// Import Livewire Components
use App\Livewire\ContactUs;
use App\Livewire\Dashboard;

// Settings Livewire Components
use App\Livewire\Settings\Users as SettingsUsers; // Alias for Settings user list
use App\Livewire\Settings\CreateUser as CreateSettingsUser; // Assumed component for creating Settings users
use App\Livewire\Settings\ShowUser as ShowSettingsUser;     // Assumed component for showing Settings users
use App\Livewire\Settings\EditUser as EditSettingsUser;     // Assumed component for editing Settings users
use App\Livewire\Settings\Roles;       // Livewire component for Roles management
use App\Livewire\Settings\Permissions; // Livewire component for Permissions management

// MOTAC User-facing Livewire Components
use App\Livewire\LoanRequestForm;      // Livewire component for Loan Application form
use App\Livewire\EmailApplicationForm; // Livewire component for Email Application form

// HR Livewire Components
use App\Livewire\HumanResource\Holidays;     // Livewire component for Holidays
use App\Livewire\HumanResource\Messages;     // Livewire component for Messages
use App\Livewire\HumanResource\Discounts;    // Livewire component for Discounts
use App\Livewire\HumanResource\Statistics;   // Livewire component for Statistics
use App\Livewire\HumanResource\Structure\Departments; // Livewire component for Departments
use App\Livewire\HumanResource\Structure\EmployeeInfo; // Livewire component for Employee Info
use App\Livewire\HumanResource\Attendance\Fingerprints; // Livewire component for Fingerprints attendance
use App\Livewire\HumanResource\Attendance\Leaves;     // Livewire component for Leaves attendance
use App\Livewire\HumanResource\Structure\Centers; // Livewire component for Centers
use App\Livewire\HumanResource\Structure\Employees; // Livewire component for Employee List
use App\Livewire\HumanResource\Structure\Positions as StructurePositions; // Alias for HR Positions Livewire component

// Assets Livewire Components
use App\Livewire\Assets\Inventory;     // Livewire component for Assets Inventory
use App\Livewire\Assets\Categories;    // Livewire component for Assets Categories

// Other Livewire Components
use App\Livewire\Misc\ComingSoon;    // Livewire component for placeholder pages
use App\Livewire\ApprovalDashboard; // Livewire component for the Approvals Dashboard


// Import Controllers
use App\Http\Controllers\language\LanguageController; // Controller for Language switching
use App\Http\Controllers\ReportController;           // Controller for generating various reports
use App\Http\Controllers\EmailApplicationController; // Controller for Email Application actions
use App\Http\Controllers\LoanApplicationController;  // Controller for Loan Application actions
use App\Http\Controllers\LoanTransactionController;  // Controller for Loan Transaction actions
use App\Http\Controllers\ApprovalController;         // Controller for Approval actions

// Import Admin Controllers
use App\Http\Controllers\Admin\EquipmentController; // Admin Controller for Equipment CRUD
use App\Http\Controllers\Admin\UserController as AdminUserController; // Admin Controller for User management (if using controller)
use App\Http\Controllers\Admin\GradeController;     // Admin Controller for Grade management

// Import Laravel/Framework classes
use Illuminate\Support\Facades\Route;


/**
 * Web Routes
 *
 * This file contains the web routes for the application.
 * These routes are loaded by the RouteServiceProvider and all of them will be assigned to the "web" middleware group.
 * Routes requiring authentication are grouped under the 'auth' middleware group.
 * Routes for the new MOTAC Integrated Resource Management system are grouped logically for users, approvers, and admin/BPM staff.
 */

// Language Switching Route (Existing)
Route::get('lang/{locale}', [LanguageController::class, 'swap'])->name('language.swap');

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
  // Apply middleware 'role' if using a package like spatie/laravel-laravel-permission
  Route::group(['middleware' => ['role:Admin|AM|CC|CR|HR']], function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
  });

  // Existing HRMS Human Resource Routes
  Route::prefix('hr')->group(function () {
    // Attendance Routes, restricted by roles
    Route::prefix('attendance')->middleware(['role:Admin|HR|CC'])->group(function () {
      // Fingerprints route further restricted to Admin|HR - Route Name: attendance-fingerprints
      Route::get('/fingerprints', Fingerprints::class)->middleware(['role:Admin|HR'])->name('attendance-fingerprints');
      // Leaves route accessible to Admin|HR|CC - Route Name: attendance-leaves
      Route::get('/leaves', Leaves::class)->name('attendance-leaves');
    });

    // Structure Routes for managing HR organizational data, restricted by roles
    Route::prefix('structure')->middleware(['role:Admin|HR'])->group(function () {
      // Route Name: structure-centers
      Route::get('/centers', Centers::class)->name('structure-centers');
      // Route Name: structure-departments
      Route::get('/departments', Departments::class)->name('structure-departments');
      // Route Name: structure-positions
      Route::get('/positions', StructurePositions::class)->name('structure-positions');
      // Route Name: structure-employees (HRMS Employee list)
      Route::get('/employees', Employees::class)->name('structure-employees');
      // Route Name: structure-employees-info (HRMS Employee details)
      Route::get('/employee/{id?}', EmployeeInfo::class)->name('structure-employees-info');
    });

    // Other HR Routes, restricted by roles
    Route::middleware(['role:Admin|HR'])->group(function () {
      // Route Name: messages
      Route::get('/messages', Messages::class)->name('messages');
      // Route Name: discounts
      Route::get('/discounts', Discounts::class)->name('discounts');
      // Route Name: holidays
      Route::get('/holidays', Holidays::class)->name('holidays');
      // Route Name: statistics
      Route::get('/statistics', Statistics::class)->name('statistics');
    });
  });

  // Existing HRMS Settings Routes, restricted to Admin role
  Route::prefix('settings')->middleware(['role:Admin'])->group(function () {
    // User management routes handled by Livewire Components
    // List view - Points to App\Livewire\Settings\Users - Route Name: settings-users
    Route::get('/users', SettingsUsers::class)->name('settings-users');
    // Create view - Points to App\Livewire\Settings\CreateUser - Route Name: settings-users.create (Assumes component exists)
    Route::get('/users/create', CreateSettingsUser::class)->name('settings-users.create');
    // Show view - Points to App\Livewire\Settings\ShowUser (takes a User model or ID) - Route Name: settings-users.show (Assumes component exists)
    Route::get('/users/{user}', ShowSettingsUser::class)->name('settings-users.show');
    // Edit view - Points to App\Livewire\Settings\EditUser (takes a User model or ID) - Route Name: settings-users.edit (Assumes component exists)
    Route::get('/users/{user}/edit', EditSettingsUser::class)->name('settings-users.edit');

    // Roles and Permissions management routes handled by Livewire Components
    // Route Name: settings-roles
    Route::get('/roles', Roles::class)->name('settings-roles');
    // Route Name: settings-permissions
    Route::get('/permissions', Permissions::class)->name('settings-permissions');
  });

  // Existing HRMS Assets Routes (Note: /assets prefix here vs /admin/equipment below for RM)
  Route::prefix('assets')->group(function () {
    // Inventory and Categories accessible based on roles
    // Route Name: inventory
    Route::get('/inventory', Inventory::class)->middleware(['role:Admin|AM'])->name('inventory'); // Existing Assets Inventory
    // Route Name: categories
    Route::get('/categories', Categories::class)->middleware(['role:Admin|AM'])->name('categories'); // Existing Assets Categories

    // Route for the Reports Index page, accessible based on roles
    // Route Name: reports.index
    Route::get('/report', [ReportController::class, 'index'])->middleware(['role:Admin|AM|HR'])->name('reports.index'); // Use the index method of ReportController

    // Placeholder for other asset-related features
    // Route::get('/transfers', ComingSoon::class)->name('transfers'); // Example using ComingSoon component
  });


  // 👇 New MOTAC Integrated Resource Management (IRM) Routes 👇

  // User-facing Application Forms (Accessible to all authenticated users)
  // Apply resource-management prefix and name prefix
  Route::prefix('resource-management')->name('resource-management.')->group(function () {
    // Email Application Form route - Includes optional {emailApplication} parameter for editing
    // Route Name: resource-management.email-applications.create (Handles both create and edit based on parameter)
    Route::get('/email-application/create/{emailApplication?}', EmailApplicationForm::class)->name('email-applications.create');
    // Loan Application Form route
    // Route Name: resource-management.loan-applications.create
    Route::get('/loan-application/create', LoanRequestForm::class)->name('loan-applications.create');
    // ... other resource-management routes for users (e.g., viewing their own applications, maybe in a different group)
  });


  // Routes for users to view their own applications (outside the main 'resource-management' application form group)
  // Apply my-applications prefix and name prefix
  Route::prefix('my-applications')->name('my-applications.')->group(function () {
    // Route to list email applications - Renders the email applications index view via Controller
    // This view should be at resources/views/my-applications/email/index.blade.php
    // Route Name: my-applications.email.index
    Route::get('/email', [EmailApplicationController::class, 'index'])->name('email.index');
    // Route to list loan applications - Renders the loan applications index view via Controller
    // This view should be at resources/views/my-applications/loan/index.blade.php
    // Route Name: my-applications.loan.index
    Route::get('/loan', [LoanApplicationController::class, 'index'])->name('loan.index');
    // Route to show a specific email application
    // Route Name: my-applications.email.show
    Route::get('/email/{emailApplication}', [EmailApplicationController::class, 'show'])->name('email.show');
    // Route to show a specific loan application
    // Route Name: my-applications.loan.show
    Route::get('/loan/{loanApplication}', [LoanApplicationController::class, 'show'])->name('loan.show');
  });


  // Approvals Dashboard (Accessible to users with the required grade level for approval)
  // Uses custom 'grade' middleware based on a config value
  // Route Name: approvals.index
  Route::get('/resource-management/approvals', ApprovalDashboard::class)
    ->middleware('grade:' . config('motac.approval.min_approver_grade_level')) // Custom middleware for approver access
    ->name('approvals.index'); // Dashboard for pending approvals


  // Routes for Approvers to view and act on applications (under 'approvals' name prefix)
  Route::prefix('approvals')->name('approvals.')->middleware('grade:' . config('motac.approval.min_approver_grade_level'))->group(function () {
    // Routes to show applications for approval (might use different views than user's 'show')
    // Route Name: approvals.email.show
    Route::get('/email/{emailApplication}', [EmailApplicationController::class, 'showForApproval'])->name('email.show');
    // Route Name: approvals.loan.show
    Route::get('/loan/{loanApplication}', [LoanApplicationController::class, 'showForApproval'])->name('loan.show');
    // Routes to handle approval/rejection actions (POST requests)
    // Route Name: approvals.email.approve
    Route::post('/email/{emailApplication}/approve', [EmailApplicationController::class, 'approve'])->name('email.approve');
    // Route Name: approvals.email.reject
    Route::post('/email/{emailApplication}/reject', [EmailApplicationController::class, 'reject'])->name('email.reject');
    // Route Name: approvals.loan.approve
    Route::post('/loan/{loanApplication}/approve', [LoanApplicationController::class, 'approve'])->name('loan.approve');
    // Route Name: approvals.loan.reject
    Route::post('/loan/{loanApplication}/reject', [LoanApplicationController::class, 'reject'])->name('loan.reject');

    // Route definition for Approvals History - Pointing to loanHistory METHOD in ReportController
    // This route is called from the sidebar menu (vertical-menu.blade.php).
    // Route Name: approvals.history
    Route::get('/history', [ReportController::class, 'loanHistory'])->name('history'); // Using ReportController::loanHistory for history

    // Note: The admin Loan History Report is at admin.reports.loan-history,
    // also using ReportController::loanHistory(). Ensure consistency in naming/purpose if needed.
  });


  // Admin and BPM Staff Routes for Resource Management (Under 'admin' name prefix within 'resource-management', restricted by role middleware)
  Route::group(['prefix' => 'resource-management/admin', 'as' => 'resource-management.admin.', 'middleware' => ['role:Admin|BPM']], function () {

    // Resource routes for Admin User Controller (typically only for Admin role)
    // If using AdminUserController for CRUD via traditional controllers
    // This uses the AdminUserController and defines standard resource routes.
    // Route names will be prefixed with 'resource-management.admin.'
    // Route Name: resource-management.admin.users.index etc.
    Route::resource('users', AdminUserController::class)->middleware('role:Admin')->names([
      'index' => 'users.index', // This creates the name 'resource-management.admin.users.index'
      'create' => 'users.create',
      'store' => 'users.store',
      'show' => 'users.show',
      'edit' => 'users.edit',
      'update' => 'users.update',
      'destroy' => 'users.destroy',
    ]);

    // Resource routes for Equipment Controller (assuming BPM/Admin manages equipment)
    // Route Names: resource-management.admin.equipment.index etc.
    Route::resource('equipment', EquipmentController::class)->names([
      'index' => 'equipment.index',
      'create' => 'equipment.create',
      'store' => 'equipment.store',
      'show' => 'equipment.show',
      'edit' => 'equipment.edit',
      'update' => 'equipment.update',
      'destroy' => 'equipment.destroy',
    ]);

    // Resource routes for Grade Controller (typically only for Admin role)
    // Route Names: resource-management.admin.grades.index etc.
    Route::resource('grades', GradeController::class)->middleware('role:Admin')->names([
      'index' => 'grades.index',
      'create' => 'grades.create',
      'store' => 'grades.store',
      'show' => 'grades.show',
      'edit' => 'grades.edit',
      'update' => 'grades.update',
      'destroy' => 'grades.destroy',
    ]);

    // BPM Specific Routes (Under 'bpm' name prefix, restricted to BPM role)
    Route::prefix('bpm')->name('bpm.')->middleware('role:BPM')->group(function () {
      // Route Name: resource-management.admin.bpm.outstanding-loans
      Route::get('/outstanding-loans', [LoanApplicationController::class, 'outstandingLoansList'])->name('outstanding-loans');
      // Route Name: resource-management.admin.bpm.issue.form
      Route::get('/issue/{loanApplication}', [LoanApplicationController::class, 'issueEquipmentForm'])->name('issue.form');
      // Route Name: resource-management.admin.bpm.issue
      Route::post('/issue/{loanApplication}', [LoanTransactionController::class, 'issue'])->name('issue');
      // Route Name: resource-management.admin.bpm.issued-loans
      Route::get('/issued-loans', [LoanTransactionController::class, 'issuedLoansList'])->name('issued-loans');
      // Route Name: resource-management.admin.bpm.return.form
      Route::get('/return/{loanTransaction}', [LoanTransactionController::class, 'returnEquipmentForm'])->name('return.form');
      // Route Name: resource-management.admin.bpm.return
      Route::post('/return/{loanTransaction}', [LoanTransactionController::class, 'processReturn'])->name('return');
      // Route Name: resource-management.admin.bpm.transactions.show
      Route::get('/transactions/{loanTransaction}', [LoanTransactionController::class, 'show'])->name('transactions.show');
    });

    // Reporting routes for admins (Under 'reports' name prefix, restricted to Admin role)
    // Uses methods within the ReportController
    Route::prefix('reports')->name('reports.')->middleware('role:Admin')->group(function () {
      // Route names will be prefixed with 'resource-management.admin.reports.'
      // Route Name: resource-management.admin.reports.equipment
      Route::get('/equipment', [ReportController::class, 'equipment'])->name('equipment');          // Report on equipment inventory/status
      // Route Name: resource-management.admin.reports.email-accounts
      Route::get('/email-accounts', [ReportController::class, 'emailAccounts'])->name('email-accounts'); // Report on email accounts
      // Route Name: resource-management.admin.reports.loan-applications
      Route::get('/loan-applications', [ReportController::class, 'loanApplications'])->name('loan-applications'); // Report on loan applications status/history
      // Route Name: resource-management.admin.reports.user-activity
      Route::get('/user-activity', [ReportController::class, 'userActivity'])->name('user-activity');      // Report on user actions/activity
      // Loan History Report (Admin Report) - Uses loanHistory method from ReportController
      // Route Name: resource-management.admin.reports.loan-history
      Route::get('/loan-history', [ReportController::class, 'loanHistory'])->name('loan-history');
      // Add other reports as needed (e.g., damage reports, loss reports)
    });

    // ❓ PLACEHOLDER: Add route for approvals history IF it's an Admin report DISTINCT from the approvals.history route ❓
    // Example: Route::get('/approval-history', [ReportController::class, 'approvalHistory'])->name('approval-history');


    // Add other admin/management routes here (e.g., for managing Definitions, Configurations)
    // Example: Route::resource('definitions', DefinitionController::class);

  }); // End Admin/BPM Middleware Group


  // ☝️ End New MOTAC Integrated Resource Management (IRM) Routes ☝️


  // Add any other authenticated/verified routes here that don't fit in the above groups
  // ...

}); // End Auth middleware group


// --- Fallback and Public Routes Below (Optional, handle carefully) ---

// Ensure that unauthenticated users trying to access 'auth' middleware routes are redirected to login
// This is typically handled automatically by Laravel's built-in 'auth' middleware
// and the Authenticate middleware defined in App\Http\Middleware\Authenticate.php


// Define any public routes here if they don't require authentication
// Example: A public homepage if not redirecting to dashboard
// Route::get('/', [HomeController::class, 'index'])->name('homepage');


// Define a fallback route for 404 errors if needed (optional, Laravel's Exception Handler usually covers this)
// This route will be matched if no other route matches the incoming request
// Route::fallback(function() {
//     // You can return a custom 404 view
//     // return response()->view('errors.404', [], 404);
//     // Or redirect to a specific page, e.g., the homepage or a login page
//     // return redirect('/');
//     // Or just let Laravel's default exception handler return the 404 response
//     abort(404);
// });
