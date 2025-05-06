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
use App\Livewire\HumanResource\Structure\Employees as HrmsEmployees; // Assuming this is the Employee Livewire component, using alias to avoid conflict if App\Models\Employee is used frequently without namespace
use App\Livewire\HumanResource\Structure\Positions as StructurePositions; // Alias existing Positions Livewire component


// *** NEW: Import the new Livewire components for Settings ***
use App\Livewire\Settings\Roles as SettingsRoles; // <-- Added/Uncommented this line
use App\Livewire\Settings\Permissions as SettingsPermissions; // <-- Added/Uncommented this line
// *** END NEW IMPORTS ***


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
      Route::get('/employees', HrmsEmployees::class)->name('structure-employees'); // HRMS Employee list
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
    // 👇 NEW: Pointing to actual Livewire components for Roles and Permissions
    Route::get('/roles', SettingsRoles::class)->name('settings-roles');
    Route::get('/permissions', SettingsPermissions::class)->name('settings-permissions');
    // 👆 END NEW

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

    // Email Application Routes (Livewire Components for user)
    // UPDATED: Added optional {emailApplication} parameter for editing functionality
    // Note: If EmailApplicationForm component handles create and edit via optional parameter:
    Route::get('/email-application/create/{emailApplication?}', EmailApplicationForm::class)->name('email-applications.create');
    // If show/edit are separate:
    // Route::get('/email-application/{emailApplication}', [EmailApplicationController::class, 'show'])->name('email-applications.show'); // Example show route
    // Route::get('/email-application/{emailApplication}/edit', EmailApplicationForm::class)->name('email-applications.edit'); // Example edit route

    // Loan Application Routes
    // The create route points directly to the Livewire component (no model needed)
    Route::get('/loan-application/create', LoanRequestForm::class)->name('loan-applications.create');

    // Assuming index is a controller for listing
    // Route::get('/loan-applications', [LoanApplicationController::class, 'index'])->name('loan-applications.index');

    // Show and Edit routes use a Controller to load the model and pass it to the Livewire component
    // Route Model Binding {loanApplication} handles finding the model and injecting it into the controller method
    Route::get('/loan-application/{loanApplication}', [LoanApplicationController::class, 'show'])->name('loan-applications.show');
    Route::get('/loan-application/{loanApplication}/edit', [LoanApplicationController::class, 'edit'])->name('loan-applications.edit');

    // Store, Update, Destroy actions would also typically be controller methods or Livewire actions
    // If your Livewire form handles submission/update internally, you might not need store/update routes here,
    // but store is generally used for initial POST request if not using Livewire's initial page load for create.
    // Route::post('/loan-application', [LoanApplicationController::class, 'store'])->name('loan-applications.store'); // Store action (handled by Livewire submitApplication)
    // Route::put('/loan-application/{loanApplication}', [LoanApplicationController::class, 'update'])->name('loan-applications.update'); // Update action (if not handled by Livewire component submitting to itself)
    // Route::delete('/loan-application/{loanApplication}', [LoanApplicationController::class, 'destroy'])->name('loan-applications.destroy'); // Delete action (if not handled by Livewire)


    // ... Email Application Routes (already adjusted above) ...


    // Loan Transaction Routes (Controller)
    // Note: These routes are typically admin/BPM staff actions, might need middleware
    // These routes seem to duplicate BPM routes below. Review and consolidate.
    // Route::prefix('loan-transactions')->name('loan-transactions.')->group(function () {
    //   Route::get('/', [LoanTransactionController::class, 'index'])->name('index'); // List all transactions
    //   Route::get('/issue/{loanApplication}', [LoanTransactionController::class, 'issueEquipmentForm'])->name('issue.form'); // Form to issue equipment for an application
    //   Route::post('/issue/{loanApplication}', [LoanTransactionController::class, 'issueEquipment'])->name('issue'); // Process equipment issuance
    //   Route::get('/issued', [LoanTransactionController::class, 'issuedLoansList'])->name('issued-loans'); // List currently issued loans
    //   Route::get('/return/{loanTransaction}', [LoanTransactionController::class, 'returnEquipmentForm'])->name('return.form'); // Form to return equipment
    //   Route::post('/return/{loanTransaction}', [LoanTransactionController::class, 'processReturn'])->name('return'); // Process equipment return
    //   Route::get('/transactions/{loanTransaction}', [LoanTransactionController::class, 'show'])->name('transactions.show'); // Show a single transaction detail (maybe redundant with return.form/issue.form?)
    // });

    // Reporting routes for users/approvers (if needed, e.g., user history)
    // Route::get('/reports/my-loans', [ReportController::class, 'userLoanHistory'])->name('reports.my-loans'); // Example user-specific report

  }); // ☝️ End User-facing Resource Management Routes ☝️


  // Routes for users to view their own applications
  Route::prefix('my-applications')->name('my-applications.')->group(function () {
    Route::get('/email', [EmailApplicationController::class, 'index'])->name('email.index');
    Route::get('/loan', [LoanApplicationController::class, 'index'])->name('loan.index');
    Route::get('/email/{emailApplication}', [EmailApplicationController::class, 'show'])->name('email.show'); // Ensure this show method exists and is distinct if needed
    Route::get('/loan/{loanApplication}', [LoanApplicationController::class, 'show'])->name('loan.show'); // Ensure this show method exists and is distinct if needed
  });


  // Approvals Dashboard (Accessible to users with the required grade level for approval)
  Route::get('/resource-management/approvals', ApprovalDashboard::class)
    ->middleware('grade:' . config('motac.approval.min_approver_grade_level')) // Custom middleware for approver access
    ->name('approvals.index'); // Dashboard for pending approvals


  // Routes for Approvers to view and act on applications
  Route::prefix('approvals')->name('approvals.')->group(function () {
    // Show methods for approvals (can be handled by controllers or Livewire, check implementation)
    // These might use different views or logic than my-applications.show
    Route::get('/email/{emailApplication}', [EmailApplicationController::class, 'showForApproval'])->name('email.show');
    Route::get('/loan/{loanApplication}', [LoanApplicationController::class, 'showForApproval'])->name('loan.show');

    // Approval/Rejection actions (assuming controller methods)
    Route::post('/email/{emailApplication}/approve', [EmailApplicationController::class, 'approve'])->name('email.approve');
    Route::post('/email/{emailApplication}/reject', [EmailApplicationController::class, 'reject'])->name('email.reject'); // Corrected method name
    Route::post('/loan/{loanApplication}/approve', [LoanApplicationController::class, 'approve'])->name('loan.approve');
    Route::post('/loan/{loanApplication}/reject', [LoanApplicationController::class, 'reject'])->name('loan.reject');


    // Route definition for Approvals History - NOW POINTING TO loanHistory METHOD
    // This route is called from the sidebar menu (vertical-menu.blade.php).
    // The ReportController has loanHistory(), NOT approvalHistory().
    // UPDATED: Pointing this route to the existing loanHistory method.
    Route::get('/history', [ReportController::class, 'loanHistory'])->name('history'); // <-- UPDATED METHOD CALL

    // Note: The admin Loan History Report is at admin.reports.loan-history,
    // also using ReportController::loanHistory(). This might indicate these
    // two routes/views are intended for different audiences or contexts
    // (e.g., admin view vs approver view filtered by their approvals),
    // but they currently point to the same controller method.
    // If different logic is needed, ReportController::loanHistory() might
    // need to handle different contexts (e.g., check if the user is an admin vs approver)
    // or a separate approvalHistory method should be added to ReportController.
    // For now, fixing the method call to one that exists.
  });


  // Admin and BPM Staff Routes for Resource Management
  // This group is prefixed with 'resource-management/admin' and has 'admin.' as the name prefix
  Route::group(['prefix' => 'resource-management/admin', 'as' => 'admin.', 'middleware' => ['role:Admin|BPM']], function () {

    Route::resource('users', AdminUserController::class)->middleware('role:Admin'); // Only Admin can manage users
    Route::resource('equipment', EquipmentController::class); // Both Admin and BPM might manage equipment

    // Assuming grades are managed only by Admin
    Route::resource('grades', GradeController::class)->middleware('role:Admin');


    // BPM specific routes (subset of admin tasks related to processing loans)
    Route::prefix('bpm')->name('bpm.')->middleware('role:BPM')->group(function () {
      // BPM staff list outstanding loans (likely using a controller index method)
      Route::get('/outstanding-loans', [LoanApplicationController::class, 'outstandingLoansList'])->name('outstanding-loans');
      // BPM staff issue form and process
      // Note: These routes seem to duplicate the loan-transactions routes defined earlier.
      // It's better to consolidate them under one logical prefix/group.
      // Keeping them here based on the provided structure, but note the potential duplication.
      Route::get('/issue/{loanApplication}', [LoanApplicationController::class, 'issueEquipmentForm'])->name('issue.form');
      Route::post('/issue/{loanApplication}', [LoanTransactionController::class, 'issue'])->name('issue'); // Points to LoanTransactionController

      // BPM staff list issued loans
      Route::get('/issued-loans', [LoanTransactionController::class, 'issuedLoansList'])->name('issued-loans'); // Points to LoanTransactionController

      // BPM staff return form and process
      Route::get('/return/{loanTransaction}', [LoanTransactionController::class, 'returnEquipmentForm'])->name('return.form'); // Points to LoanTransactionController
      Route::post('/return/{loanTransaction}', [LoanTransactionController::class, 'processReturn'])->name('return'); // Points to LoanTransactionController

      // BPM staff show specific transaction
      Route::get('/transactions/{loanTransaction}', [LoanTransactionController::class, 'show'])->name('transactions.show'); // Points to LoanTransactionController
    });

    // Reporting routes for admins (Admin only) - Duplicates the reports group under resource-management, review and consolidate
    Route::prefix('reports')->name('reports.')->middleware('role:Admin')->group(function () {
      Route::get('/equipment', [ReportController::class, 'equipment'])->name('equipment');
      Route::get('/email-accounts', [ReportController::class, 'emailAccounts'])->name('email-accounts');
      Route::get('/loan-applications', [ReportController::class, 'loanApplications'])->name('loan-applications'); // Likely the admin list view
      Route::get('/user-activity', [ReportController::class, 'userActivity'])->name('user-activity');
      // Loan History Report (Admin Report) - Uses loanHistory method
      Route::get('/loan-history', [ReportController::class, 'loanHistory'])->name('loan-history');
      // Add other reports as needed
    });

    // Add other admin/management routes here
  });

  // ☝️ End New MOTAC Integrated Resource Management (IRM) Routes ☝️


  // Routes for other HRMS features like Assets (already covered under assets prefix)
  // and Reports (already covered under assets prefix).


  // Example of a catch-all for /resource-management/admin if no specific route matched
  // Route::get('/resource-management/admin/{any}', ComingSoon::class)->where('any', '.*');


}); // End Auth middleware group


// Ensure that unauthenticated users trying to access 'auth' middleware routes are redirected to login
// This is handled by the Authenticate middleware (part of default auth scaffolding)

// Catch-all or fallback routes could be defined here if needed

// Fallback for 404 errors (optional, can be handled by exception handler)
// Route::fallback(function() {
//     return response()->view('errors.404', [], 404);
// });
