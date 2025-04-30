<?php

namespace App\Http\Controllers; // Ensure the namespace is correct for your project

use App\Http\Controllers\Controller; // Extend the base Controller
use App\Models\EmailApplication; // Import EmailApplication model
use App\Models\LoanApplication; // Import LoanApplication model
use App\Models\LoanTransaction; // Import LoanTransaction model for Loan History report
use App\Models\Equipment; // Import Equipment model *** Added this import ***
use App\Models\User; // Import User model
use Illuminate\Support\Facades\Auth; // Import Auth facade for authenticated user
use Illuminate\Support\Facades\Gate; // Import Gate (less needed if using Policies)
use Illuminate\View\View; // Import View for return type hinting
use Spatie\Activitylog\Models\Activity; // Import Activity model if using spatie/laravel-activitylog
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Illuminate\Http\Request; // Import Request if needed for logging/parameters (not strictly needed for these methods as written)


class ReportController extends Controller
{
  /**
   * Apply authentication middleware to this controller.
   * Reports should generally be accessible only to authenticated users.
   */
  public function __construct()
  {
    // Ensure the user is authenticated to access any report functionality
    $this->middleware('auth');
  }

  /**
   * Display the index page for reports.
   * Lists available reports with links.
   *
   * @return View
   */
  public function index(): View
  {
    // Authorize if the user can view the report index page.
    // Assumes a ReportPolicy exists with a 'viewIndex' method, or you use a Gate like 'view-report-index'.
    // Policies are generally preferred.
    $this->authorize('viewIndex', ReportController::class); // Assuming a ReportPolicy with 'viewIndex' applied to the controller class


    // Optional: Add logging for accessing the report index
    Log::info('User accessing report index page.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);


    // You can optionally fetch some data here if needed for the index page,
    // but for a simple list of links, it's not strictly necessary.

    // Return the view located at resources/views/reports/index.blade.php
    return view('reports.index');
  }

  /**
   * Generate the ICT Equipment Report.
   * Fetches equipment inventory data.
   *
   * @return View
   */
  public function equipment(): View
  {
    // Authorize if the user can view the Equipment Report.
    // Assumes a ReportPolicy exists with a 'viewEquipment' method.
    $this->authorize('viewEquipment', ReportController::class); // Assuming a Policy action

    // Log report generation
    Log::info('Generating ICT Equipment Report.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);


    // Fetch all equipment. Eager load relationships needed for the report (e.g., current loan, department, position).
    // Order by asset tag or a relevant field.
    $equipment = Equipment::query()
      ->with(['activeLoanTransaction.user', 'department', 'position']) // Eager load relevant relationships
      ->orderBy('tag_id') // Order by asset tag ID
      ->get(); // Fetch all data (consider pagination or streaming for very large inventories)

    // IMPORTANT: For large datasets, consider using pagination, chunking, or streaming
    // data to avoid memory issues when fetching all records with get().
    // $equipment = Equipment::with(...)->orderBy(...)->paginate(50);


    // Return the view located at resources/views/reports/equipment.blade.php,
    // passing the fetched equipment data.
    return view('reports.equipment', compact('equipment'));
  }

  /**
   * Generate the Email Accounts Report.
   * Fetches email application data.
   *
   * @return View
   */
  public function emailAccounts(): View
  {
    // Authorize if the user can view the Email Accounts Report.
    // Assumes a ReportPolicy exists with a 'viewEmailAccounts' method.
    $this->authorize('viewEmailAccounts', ReportController::class); // Assuming a Policy action

    // Log report generation
    Log::info('Generating Email Accounts Report.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);


    // Fetch all email applications. Eager load the 'user' relationship (the applicant).
    // Order by latest application.
    $applications = EmailApplication::with('user') // Eager load the applicant user
      ->latest() // Order by latest application creation date
      ->get(); // Fetch all data (consider pagination/streaming)

    // IMPORTANT: For large datasets, consider using pagination or streaming
    // data to avoid memory issues when fetching all records with get().
    // $applications = EmailApplication::with(...)->latest()->paginate(50);


    // Return the view located at resources/views/reports/email-accounts.blade.php,
    // passing the fetched email applications data.
    return view('reports.email-accounts', compact('applications'));
  }

  /**
   * Generate the ICT Loan History Report.
   * Fetches loan transaction data.
   *
   * @return View
   */
  public function loanHistory(): View // Added method for Loan History Report
  {
    // Authorize if the user can view the Loan History Report.
    // Assumes a ReportPolicy exists with a 'viewLoanHistory' method.
    $this->authorize('viewLoanHistory', ReportController::class); // Assuming a Policy action

    // Log report generation
    Log::info('Generating ICT Loan History Report.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);


    // Fetch all loan transactions. Eager load all relevant relationships needed for the report.
    // Order by transaction issuance date or return date.
    $transactions = LoanTransaction::query()
      ->with([
        'loanApplication.user', // Application and applicant
        'equipment', // Equipment involved
        'issuingOfficer', // Officer who issued
        'receivingOfficer', // User who received
        'returningOfficer', // User who returned
        'returnAcceptingOfficer', // Officer who accepted return
      ])
      ->orderBy('issued_at', 'desc') // Order by issuance date (descending)
      ->get(); // Fetch all data (consider pagination/streaming)

    // IMPORTANT: For large datasets, consider using pagination or streaming
    // data to avoid memory issues when fetching all records with get().
    // $transactions = LoanTransaction::with(...)->orderBy(...)->paginate(50);


    // Return the view located at resources/views/reports/loan_history.blade.php,
    // passing the fetched transaction data.
    return view('reports.loan_history', compact('transactions'));
  }

  /**
   * Generate the User Activity Report.
   * Fetches users and their associated application counts, or activity logs.
   * Can be extended to include activity logs if using spatie/laravel-activitylog.
   *
   * @return View
   */
  public function userActivity(): View
  {
    // Authorize if the user can view the User Activity Report.
    // Assumes a ReportPolicy exists with a 'viewUserActivity' method.
    $this->authorize('viewUserActivity', ReportController::class); // Assuming a Policy action

    // Log report generation
    Log::info('Generating User Activity Report.', [
      'user_id' => Auth::id(),
      'ip_address' => request()->ip(),
    ]);


    // Fetch all users with counts of their related applications and approvals.
    // This assumes the User model has 'emailApplications', 'loanApplications', and 'approvals' relationships defined.
    $users = User::withCount(['emailApplications', 'loanApplications', 'approvals'])
      ->get(); // Fetch all users (consider pagination for many users)

    // If you have activity logging (e.g., using spatie/laravel-activitylog),
    // you might fetch activity logs instead or in addition:
    // $activityLogs = Activity::latest()->get(); // Fetch all activity logs, ordered by latest
    // IMPORTANT: Fetching *all* activity logs with get() can be very memory intensive.
    // Consider adding filters (by user, date range, activity type) and pagination/streaming.
    // $activityLogs = Activity::with('causer', 'subject') // Eager load user who caused activity and the subject
    //                          ->latest()
    //                          ->paginate(50);


    // Return the view for the user activity report, located at resources/views/reports/user_activity.blade.php.
    // Pass either the $users data or the $activityLogs data (or both) to the view.
    return view('reports.user_activity', compact('users'));
    // Or pass activity logs: return view('reports.user_activity', compact('activityLogs'));
  }

  // You can add other report methods here as needed for different reports.
  // Remember to create corresponding views in the 'resources/views/reports' directory
  // and define routes for these methods.
}
