<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EmailApplication;
use App\Models\LoanApplication; // Import LoanApplication model
use App\Models\LoanTransaction;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
// Import necessary models for eager loading in the new method
use App\Models\Department;
use App\Models\Position;
use App\Models\Grade;


class ReportController extends Controller
{
  /**
   * Apply authentication middleware to this controller.
   */
  public function __construct()
  {
    $this->middleware('auth');
  }

  /**
   * Display the index page for reports.
   *
   * @return View
   */
  public function index(): View
  {
    // Authorize if the user can view the report index page.
    $this->authorize('viewIndex', ReportController::class);

    Log::info('User accessing report index page.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

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
    $this->authorize('viewEquipment', ReportController::class);

    Log::info('Generating ICT Equipment Report.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Fetch equipment with pagination
    $equipment = Equipment::query()
      ->with(['activeLoanTransaction.user', 'department', 'position'])
      ->orderBy('tag_id')
      ->paginate(15);

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
    $this->authorize('viewEmailAccounts', ReportController::class);

    Log::info('Generating Email Accounts Report.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Fetch email applications with pagination
    $applications = EmailApplication::with('user')
      ->latest()
      ->paginate(15);

    return view('reports.email-accounts', compact('applications'));
  }

  /**
   * Generate the ICT Loan History Report.
   * Fetches loan transaction data with pagination.
   *
   * @return View
   */
  public function loanHistory(): View
  {
    $this->authorize('viewLoanHistory', ReportController::class);

    Log::info('Generating ICT Loan History Report.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Fetch loan transactions with pagination
    $transactions = LoanTransaction::query()
      ->with([
        'loanApplication.user',
        'equipment',
        'issuingOfficer',
        'receivingOfficer',
        'returningOfficer',
        'returnAcceptingOfficer',
      ])
      ->orderBy('issued_at', 'desc')
      ->paginate(15);

    return view('reports.loan_history', compact('transactions'));
  }

  /**
   * Generate the Loan Applications Report.
   * Fetches loan application data with pagination.
   *
   * @return View
   */
  public function loanApplications(): View // <-- This method was missing
  {
    // Authorize if the user can view the Loan Applications Report.
    $this->authorize('viewLoanApplications', ReportController::class);

    // Log report generation
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
    ])
      ->latest()
      ->paginate(15);

    // Return the view located at resources/views/reports/loan_applications.blade.php
    return view('reports.loan_applications', compact('applications'));
  }


  /**
   * Generate the User Activity Report.
   * Fetches users with counts of their applications/approvals with pagination.
   *
   * @return View
   */
  public function userActivity(): View
  {
    $this->authorize('viewUserActivity', ReportController::class);

    Log::info('Generating User Activity Report.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);

    // Fetch users with counts, with pagination
    $users = User::withCount(['emailApplications', 'loanApplications', 'approvals'])
      ->paginate(15);

    // If you were fetching activity logs with pagination, it might look like:
    // $activityLogs = Activity::with('causer', 'subject')
    //    ->latest()
    //    ->paginate(50);
    // return view('reports.user_activity', compact('activityLogs'));

    // Passing the paginated $users to the view
    return view('reports.user_activity', compact('users'));
  }

  // Add other report methods here as needed.
}
