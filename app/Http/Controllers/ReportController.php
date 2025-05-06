<?php
// App/Http/Controllers/ReportController.php - Corrected Log::info syntax

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmailApplication;
use App\Models\LoanApplication; // Import LoanApplication model
use App\Models\LoanTransaction; // Import LoanTransaction model
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate; // Keep if used for policies/gates
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity; // Keep if Activity model is used
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request; // Keep if Request is used in methods
// Import necessary models for eager loading in the new method
use App\Models\Department; // Keep if used for relationships
use App\Models\Position; // Keep if used for relationships
use App\Models\Grade; // Keep if used for relationships
// Ensure these imports are correct based on your models directory
use App\Models\EquipmentCategory; // Assuming this model exists at App\Models\EquipmentCategory.php
use App\Models\Location; // Assuming this model exists at App\Models\Location.php


class ReportController extends Controller
{
  /**
   * Apply authentication middleware to this controller.
   */
  public function __construct()
  {
    $this->middleware('auth');
    // Optionally, add other middleware like authorization checks here,
    // or handle authorization within each method using $this->authorize().
    // $this->middleware('role:Admin|AM|HR')->only(['index']); // Example
    // $this->middleware('role:Admin|AM')->only(['equipment']); // Example
  }

  /**
   * Display the index page for reports.
   *
   * @return View
   */
  public function index(): View
  {
    // Authorize if the user can view the report index page.
    // $this->authorize('viewAny', ReportController::class); // Example authorization check

    Log::info('Accessing Reports Index.', [ // Corrected: Removed '\' after '['
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Return the view for the reports index page
    // Assumes view exists at resources/views/reports/index.blade.php
    return view('reports.index');
  }


  /**
   * Generate the Equipment Report.
   * Fetches equipment data with related loan information and pagination.
   *
   * @return View
   */
  public function equipment(): View
  {
    // Authorization check within the method if not fully handled by middleware
    // $this->authorize('viewEquipmentReport', ReportController::class); // Example authorization check

    Log::info('Generating Equipment Report.', [ // Corrected: Removed '\' after '['
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Fetch equipment with pagination.
    // Eager load relationships needed for the report (e.g., current borrower, location).
    // Uses the currentTransaction relationship chain.
    $equipment = Equipment::with([
      // Correct path: Equipment -> currentTransaction -> LoanApplication -> User (the applicant)
      'currentTransaction.loanApplication.user',
      // Optional: Eager load items for the current loan application if needed in the view
      // 'currentTransaction.loanApplication.items',

      'location', // Assuming equipment has a location relationship and Location model is imported
      'category', // Assuming equipment has a category relationship and EquipmentCategory model is imported
    ])
      ->latest() // Order by latest equipment
      ->paginate(15); // Add pagination


    // Return the view located at resources/views/reports/equipment.blade.php
    // Assumes this view file exists.
    return view('reports.equipment', compact('equipment'));
  }


  /**
   * Generate the Email Accounts Report.
   * Fetches email application data with pagination.
   *
   * @return View
   */
  public function emailAccounts(): View
  {
    // Authorization check within the method if not fully handled by middleware
    // $this->authorize('viewEmailAccountsReport', ReportController::class); // Example authorization check

    Log::info('Generating Email Accounts Report.', [ // Corrected: Removed '\' after '['
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Fetch email applications with pagination.
    $emailApplications = EmailApplication::with(['user']) // Eager load applicant user
      ->latest() // Order by latest application
      ->paginate(15); // Add pagination

    // Return the view located at resources/views/reports/email-accounts.blade.php
    // Assumes this view file exists.
    return view('reports.email-accounts', compact('emailApplications'));
  }


  /**
   * Generate the Loan Applications Report (Admin List view).
   * Fetches loan application data with pagination.
   *
   * @return View
   */
  public function loanApplications(): View
  {
    // Authorization check within the method if not fully handled by middleware
    // $this->authorize('viewLoanApplicationsReport', ReportController::class); // Example authorization check

    Log::info('Generating Loan Applications Report (Admin List).', [ // Corrected: Removed '\' after '['
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Fetch loan applications with pagination.
    $loanApplications = LoanApplication::with([
      'user', // Applicant user
      'responsibleOfficer', // Responsible officer user
      'items', // Requested items
      'approvals', // Approval history
      'transactions', // Related issue/return transactions
    ])
      ->latest() // Order by latest application
      ->paginate(15); // Add pagination

    // Return the view located at resources/views/reports/loan-applications.blade.php
    return view('reports.loan-applications', compact('loanApplications'));
  }


  /**
   * Generate the Loan History Report.
   * Fetches loan transaction data, typically for a specific period or equipment.
   *
   * @param  Request  $request
   * @return View
   */
  public function loanHistory(Request $request): View
  {
    // Authorization check within the method if not fully handled by middleware
    // $this->authorize('viewLoanHistoryReport', ReportController::class); // Example authorization check

    Log::info('Generating Loan History Report.', [ // Corrected: Removed '\' after '['
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
      'filters' => $request->all(),
    ]);

    // Start building the query for Loan Transactions
    $query = LoanTransaction::with([
      'loanApplication.user', // Applicant user via application
      'equipment.category', // Equipment details and category (Requires Equipment and EquipmentCategory models/imports)
      'issuingOfficer', // Issuing officer
      'returnAcceptingOfficer', // Return accepting officer
      // Add other relationships as needed
    ]);

    // Apply filters from the request
    if ($request->has('start_date') && $request->has('end_date')) {
      $query->whereBetween('issue_timestamp', [$request->start_date, $request->end_date]);
    }
    if ($request->has('equipment_id')) {
      $query->where('equipment_id', $request->equipment_id);
    }
    if ($request->has('user_id')) {
      $query->whereHas('loanApplication', function ($q) use ($request) {
        $q->where('user_id', $request->user_id);
      });
    }
    if ($request->has('status')) {
      $query->where('status', $request->status);
    }

    // Order the results and paginate
    $loanTransactions = $query->latest('issue_timestamp')
      ->paginate(15);

    // Return the view for the loan history report
    // Assumes view exists at resources/views/reports/loan-history.blade.php
    return view('reports.loan-history', compact('loanTransactions', 'request'));
  }


  /**
   * Generate the User Activity Report.
   * Fetches user data and counts of their applications/approvals with pagination.
   *
   * @return View
   */
  public function userActivity(): View
  {
    // Authorization check within the method
    // $this->authorize('viewUserActivity', ReportController::class); // Example authorization check using a policy

    Log::info('Generating User Activity Report.', [ // Corrected: Removed '\' after '['
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Fetch users with counts, with pagination
    // Assuming user has relationships like emailApplications, loanApplications, approvals (either directly or via employee)
    $users = User::withCount(['emailApplications', 'loanApplications', 'approvals'])
      ->latest()
      ->paginate(15);

    // Passing the paginated $users to the view
    // Assumes view exists at resources/views/reports/user-activity.blade.php
    return view('reports.user-activity', compact('users'));
  }

  // The approvals.history route is now pointing to loanHistory().
  // If a separate approvalHistory method with different logic is needed,
  // you would define it here and update the route in web.php accordingly.
  // public function approvalHistory(): View
  // {\
  //    // ... specific logic for approvals history ...\
  // }


}
