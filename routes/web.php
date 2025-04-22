<?php

use App\Http\Controllers\language\LanguageController;
use App\Livewire\Assets\Categories;
use App\Livewire\Assets\Inventory;
use App\Livewire\ContactUs;
use App\Livewire\Dashboard;
use App\Livewire\HumanResource\Attendance\Fingerprints;
use App\Livewire\HumanResource\Attendance\Leaves;
use App\Livewire\HumanResource\Discounts;
use App\Livewire\HumanResource\Holidays;
use App\Livewire\HumanResource\Messages;
use App\Livewire\HumanResource\Statistics;
use App\Livewire\HumanResource\Structure\Centers;
use App\Livewire\HumanResource\Structure\Departments;
use App\Livewire\HumanResource\Structure\EmployeeInfo;
use App\Livewire\HumanResource\Structure\Employees;
use App\Livewire\HumanResource\Structure\Positions;
use App\Livewire\Misc\ComingSoon;
use App\Livewire\Settings\Users;
use Illuminate\Support\Facades\Route;

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

// Language Switching Route
Route::get('lang/{locale}', [LanguageController::class, 'swap']);

// Authenticated Routes
Route::middleware([
  'auth:sanctum',
  config('jetstream.auth_session'),
  'verified',
  'allow_admin_during_maintenance',
])->group(function () {
  // Dashboard Routes
  Route::group(['middleware' => ['role:Admin|AM|CC|CR|HR']], function () {
    Route::redirect('/', '/dashboard');
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
  });

  // Human Resource Routes
  Route::prefix('hr')->group(function () {
    // Attendance Routes
    Route::prefix('attendance')->middleware(['role:Admin|HR|CC'])->group(function () {
      Route::get('/fingerprints', Fingerprints::class)->middleware(['role:Admin|HR'])->name('attendance-fingerprints');
      Route::get('/leaves', Leaves::class)->name('attendance-leaves');
    });

    // Structure Routes
    Route::prefix('structure')->middleware(['role:Admin|HR'])->group(function () {
      Route::get('/centers', Centers::class)->name('structure-centers');
      Route::get('/departments', Departments::class)->name('structure-departments');
      Route::get('/positions', Positions::class)->name('structure-positions');
      Route::get('/employees', Employees::class)->name('structure-employees');
      Route::get('/employee/{id?}', EmployeeInfo::class)->name('structure-employees-info');
    });

    // Other HR Routes
    Route::middleware(['role:Admin|HR'])->group(function () {
      Route::get('/messages', Messages::class)->name('messages');
      Route::get('/discounts', Discounts::class)->name('discounts');
      Route::get('/holidays', Holidays::class)->name('holidays');
      Route::get('/statistics', Statistics::class)->name('statistics');
    });
  });

  // Settings Routes
  Route::prefix('settings')->middleware(['role:Admin'])->group(function () {
    Route::get('/users', Users::class)->name('settings-users');
    Route::get('/roles', ComingSoon::class)->name('settings-roles');
    Route::get('/permissions', ComingSoon::class)->name('settings-permissions');
  });

  // Assets Routes
  Route::prefix('assets')->group(function () {
    Route::get('/inventory', Inventory::class)->middleware(['role:Admin|AM'])->name('inventory');
    Route::get('/categories', Categories::class)->middleware(['role:Admin|AM'])->name('categories');
    Route::get('/reports', ComingSoon::class)->middleware(['role:Admin|AM|HR'])->name('reports');
    // Route::get('/transfers', ComingSoon::class)->name('transfers');
  });
});

// Public Contact Us Route
Route::get('/contact-us', ContactUs::class)->name('contact-us');

// Deploy Webhook Route
Route::webhooks('/deploy');

// Additional Authenticated Resource Routes (assuming these controllers exist)
Route::middleware(['auth'])->group(function () {
  // Route::resource('email-applications', EmailApplicationController::class);
  // Route::resource('loan-applications', LoanApplicationController::class);
  // Route::resource('transactions', LoanTransactionController::class);
  // Route::resource('approvals', ApprovalController::class);
  // Route::resource('equipment', EquipmentController::class);
});
