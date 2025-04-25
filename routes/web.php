<?php

use App\Livewire\ContactUs;
use App\Livewire\Dashboard;
use App\Livewire\Settings\Users as SettingsUsers; // Alias existing Users Livewire component
use App\Livewire\LoanRequestForm; // New MOTAC Livewire Component
use App\Livewire\Misc\ComingSoon;
use App\Livewire\Assets\Inventory;
use App\Livewire\ApprovalDashboard; // New MOTAC Livewire Component
use App\Livewire\Assets\Categories;
use Illuminate\Support\Facades\Route;
use App\Livewire\EmailApplicationForm; // New MOTAC Livewire Component
use App\Livewire\HumanResource\Holidays;
use App\Livewire\HumanResource\Messages;
use App\Livewire\HumanResource\Discounts;
use App\Livewire\HumanResource\Statistics;

// Import new MOTAC Controllers
use App\Http\Controllers\EmailApplicationController; // New MOTAC Controller
use App\Http\Controllers\LoanApplicationController; // New MOTAC Controller
use App\Http\Controllers\LoanTransactionController; // New MOTAC Controller
// Correct the import for the EquipmentController to use the Admin namespace
use App\Http\Controllers\Admin\EquipmentController; // Corrected Import
use App\Http\Controllers\Admin\UserController as AdminUserController; // New/Updated Admin User Controller

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
Route::get('lang/{locale}', [LanguageController::class, 'swap']);

// Authenticated Routes - Apply core authentication and verification middleware
Route::middleware([
  'auth:sanctum',
  config('jetstream.auth_session'), // Keep if using Jetstream
  'verified',
  'allow_admin_during_maintenance', // Keep existing HRMS middleware
])->group(function () {

  // Existing HRMS Dashboard Routes
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
    Route::get('/roles', ComingSoon::class)->name('settings-roles');
    Route::get('/permissions', ComingSoon::class)->name('settings-permissions');
  });

  // Existing HRMS Assets Routes (Note: /assets prefix here vs /admin/equipment below)
  Route::prefix('assets')->group(function () {
    // This might be the user-facing view of assets or a different admin view
    Route::get('/inventory', Inventory::class)->middleware(['role:Admin|AM'])->name('inventory');
    Route::get('/categories', Categories::class)->middleware(['role:Admin|AM'])->name('categories');
    Route::get('/reports', ComingSoon::class)->middleware(['role:Admin|AM|HR'])->name('reports'); // Existing Assets reports
    // Route::get('/transfers', ComingSoon::class)->name('transfers');
  });


  // 👇 New MOTAC Integrated Resource Management Routes 👇

  // User-facing Application Forms
  Route::get('/request-email', EmailApplicationForm::class)->name('request-email');
  Route::get('/request-loan', LoanRequestForm::class)->name('request-loan');

  // Approvals Dashboard (Accessible to users with the required grade level)
  Route::get('/approvals', ApprovalDashboard::class)
    ->middleware('grade:' . config('motac.approval.min_approver_grade_level')) // Use the registered 'grade' middleware
    ->name('approvals');

  // Resource routes for viewing submitted applications (users can view their own, admins/approvers can view others)
  // Policies defined in AuthServiceProvider.php will handle authorization logic (can('view', $application))
  // Note: You might want these outside the 'admin' group if regular users view their own applications.
  Route::resource('email-applications', EmailApplicationController::class)->only(['index', 'show']);
  Route::resource('loan-applications', LoanApplicationController::class)->only(['index', 'show']);

  // Route for showing a specific loan transaction (needed for BPM workflow links)
  Route::get('loan-transactions/{transaction}', [LoanTransactionController::class, 'show'])->name('loan-transactions.show');


  // Admin and BPM Staff Routes
  Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'admin']], function () {
    // ... Keep existing admin routes from HRMS

    // MOTAC Admin/Management Routes
    // Manage Users
    Route::resource('users', AdminUserController::class); // Uses App\Http\Controllers\Admin\UserController

    // Manage Equipment Assets
    // Uses App\Http\Controllers\Admin\EquipmentController
    Route::resource('equipment', EquipmentController::class);

    // Manage Organizational Data specific to MOTAC (Grades)
    // Assuming you have Controllers for these
    // Route::resource('grades', App\Http\Controllers\Admin\GradeController::class); // Example using Admin namespace

    // BPM Staff Interface for Issuance and Return
    // Protect these routes with roles/permissions appropriate for BPM staff
    Route::prefix('bpm')->group(function () {
      // Form to view a loan application and issue equipment
      // Uses App\Http\Controllers\LoanApplicationsController
      Route::get('/equipment/issue/{loanApplication}', [LoanApplicationController::class, 'issueEquipmentForm'])->name('bpm.issue.form');
      // Route to process the equipment issuance
      // Uses App\Http\Controllers\LoanApplicationsController
      Route::post('/equipment/issue/{loanApplication}', [LoanApplicationController::class, 'issueEquipment'])->name('bpm.issue');

      // Form to view a loan transaction and record return
      // Uses App\Http\Controllers\LoanApplicationsController
      // Note: This route binds LoanTransaction
      Route::get('/equipment/return/{transaction}', [LoanApplicationController::class, 'returnEquipmentForm'])->name('bpm.return.form');
      // Route to process the equipment return
      // Uses App\Http\Controllers\LoanApplicationsController
      // Note: This route binds LoanTransaction
      Route::post('/equipment/return/{transaction}', [LoanApplicationController::class, 'processReturn'])->name('bpm.return');

      // Optionally, a list of outstanding loans for BPM staff
      // Route::get('/equipment/outstanding-loans', [LoanApplicationsController::class, 'outstandingLoans'])->name('bpm.outstanding-loans');
    });

    // Reporting routes for admins
    // Assuming ReportController is in the root App\Http\Controllers namespace or Admin namespace
    // If in Admin namespace: Route::get('/reports/equipment', [Admin\ReportController::class, 'equipment'])->name('reports.equipment');
    Route::get('/reports/equipment', [\App\Http\Controllers\ReportController::class, 'equipment'])->name('reports.equipment');
    Route::get('/reports/email-accounts', [\App\Http\Controllers\ReportController::class, 'emailAccounts'])->name('reports.email-accounts');
    Route::get('/reports/user-activity', [\App\Http\Controllers\ReportController::class, 'userActivity'])->name('reports.user-activity');
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
