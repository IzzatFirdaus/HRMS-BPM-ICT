<?php

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
    // Assumes a 'viewIndex' policy exists for ReportController.
    $this->authorize('viewIndex', ReportController::class);

    Log::info('User accessing report index page.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // This view should contain links to the specific report methods.
    // The links in the view should use the correct route names (e.g., admin.reports.equipment).
    return view('reports.index');
  }

  /**
   * Generate the ICT Equipment Report.
   * Fetches equipment inventory data with pagination.
   *
   * @return View
   */
  public function equipment(): View
  {
    // Authorize if the user can view the equipment report.
    // Assumes a 'viewEquipment' policy exists for ReportController.
    $this->authorize('viewEquipment', ReportController::class);

    Log::info('Generating Equipment Report.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Fetch equipment with pagination.
    // Eager load relationships needed for the report (e.g., current borrower, location).
    $equipment = Equipment::with([
      'currentLoanItem.loanApplication.user', // Example: assuming Equipment -> LoanApplicationItem -> LoanApplication -> User
      'location', // Assuming an equipment has a location relationship
    ])
      ->latest() // Order by latest equipment (e.g., added to inventory)
      ->paginate(15); // Add pagination

    // Return the view located at resources/views/reports/equipment.blade.php
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
    // Authorize if the user can view the email accounts report.
    // Assumes a 'viewEmailAccounts' policy exists for ReportController.
    $this->authorize('viewEmailAccounts', ReportController::class);

    Log::info('Generating Email Accounts Report.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Fetch email applications with pagination.
    // Eager load relationships needed for the report (e.g., user who applied).
    $emailApplications = EmailApplication::with([
      'user', // Assuming EmailApplication has a user relationship
      'approvals.user', // Assuming approvals relationship and user on approval
    ])
      ->latest()
      ->paginate(15);

    // Return the view located at resources/views/reports/email_accounts.blade.php
    return view('reports.email_accounts', compact('emailApplications'));
  }


  /**
   * Generate the Loan Applications Report.
   * Fetches loan application data with pagination.
   *
   * @return View
   */
  public function loanApplications(): View
  {
    // Authorize if the user can view the loan applications report.
    // Assumes a 'viewLoanApplications' policy exists for ReportController.
    $this->authorize('viewLoanApplications', ReportController::class);

    Log::info('Generating Loan Applications Report.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Fetch loan applications with pagination.
    // Eager load relationships needed for the report.
    $applications = LoanApplication::with([
      'user.department',
      'user.position',
      'user.grade',
      'items', // Include items to potentially list them in the report
      'approvals.user', // Include approvals and the user who approved
    ])
      ->latest()
      ->paginate(15);

    // Return the view located at resources/views/reports/loan_applications.blade.php
    return view('reports.loan_applications', compact('applications'));
  }


  /**
   * Generate the Loan History Report.
   * Fetches loan transaction data with pagination.
   * This method serves both the admin report and approvals history route.
   *
   * @return View
   */
  public function loanHistory(): View
  {
    // Authorize if the user can view the loan history report.
    // Assumes a 'viewLoanHistory' policy exists for ReportController.
    // Note: Authorization logic might need to differentiate between admin vs approver access
    // if their permissions or data visibility differ for the same report data.
    $this->authorize('viewLoanHistory', ReportController::class);

    Log::info('Generating Loan History Report.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
      'route_name' => request()->route()->getName(), // Log the route name to see which one was accessed (approvals.history or admin.reports.loan-history)
    ]);

    // Fetch loan transactions with pagination.
    // Eager load relationships needed for the report (e.g., equipment, borrower, officers).
    $transactions = LoanTransaction::with([
      'loanApplication.user', // Borrower via loan application
      'equipment', // The equipment item involved
      'issuingOfficer', // Officer who issued
      'receivingOfficer', // Person who received on behalf of applicant (if different)
      'returningOfficer', // Person who returned on behalf of applicant (if different)
      'returnAcceptingOfficer', // BPM staff who accepted the return
    ])
      // CORRECTED: Changed 'issued_at' to the correct column name 'issue_timestamp'
      ->orderBy('issue_timestamp', 'desc') // Order by issue timestamp, latest first
      ->paginate(15); // Add pagination

    // Return the view located at resources/views/reports/loan_history.blade.php
    // This view should display loan transaction details.
    return view('reports.loan_history', compact('transactions'));
  }


  /**
   * Generate the User Activity Report.
   * Fetches users with counts of their applications/approvals with pagination.
   *
   * @return View
   */
  public function userActivity(): View
  {
    // Authorize if the user can view the user activity report.
    // Assumes a 'viewUserActivity' policy exists for ReportController.
    $this->authorize('viewUserActivity', ReportController::class);

    Log::info('Generating User Activity Report.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Fetch users with counts, with pagination
    // Assuming user has relationships like emailApplications, loanApplications, approvals (either directly or via employee)
    $users = User::withCount(['emailApplications', 'loanApplications', 'approvals']) // Assuming these relationships exist on the User model
      ->paginate(15);

    // If you were fetching activity logs with pagination, it might look like:
    // $activityLogs = Activity::with('causer', 'subject')
    //    ->latest()
    //    ->paginate(50);
    // return view('reports.user_activity', compact('activityLogs'));

    // Passing the paginated $users to the view
    return view('reports.user_activity', compact('users'));
  }

  // The approvals.history route is now pointing to loanHistory().
  // If a separate approvalHistory method with different logic is needed,
  // you would define it here and update the route in web.php accordingly.
  // public function approvalHistory(): View
  // {
  //    // ... specific logic for approvals history ...
  // }

}
