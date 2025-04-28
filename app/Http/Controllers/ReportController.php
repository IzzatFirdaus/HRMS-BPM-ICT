<?php

namespace App\Http\Controllers;

use App\Models\EmailApplication;
use App\Models\LoanApplication;
use App\Models\User; // Import the User model
use Illuminate\Support\Facades\Gate; // Import Gate or use Policies
use Illuminate\View\View; // Import View for return type hinting
use Spatie\Activitylog\Models\Activity; // Import Activity model if using spatie/laravel-activitylog

class ReportController extends Controller
{
  /**
   * Display the index page for reports.
   * Lists available reports with links.
   *
   * @return View
   */
  public function index(): View
  {
    // Optional: Add authorization check if only certain users can see the report index
    // Gate::authorize('view-report-index');

    // You can optionally fetch some data here if needed for the index page,
    // but for a simple list of links, it's not strictly necessary.

    return view('reports.index'); // This will load the new index view
  }

  /**
   * Generate the equipment report.
   * Fetches all loan applications with their requested items and the associated user.
   *
   * @return View
   */
  public function equipment(): View
  {
    // Optional: Add authorization check (e.g., only admins or specific roles)
    // Uncomment one of the lines below if you have defined Gates or Policies
    // $this->authorize('viewEquipmentReport'); // Assuming a Policy exists with a viewEquipmentReport method
    // Gate::authorize('view-equipment-report'); // Using a Gate directly

    // Fetch all loan applications. Eager load 'items' (requested equipment) and 'user' (the applicant).
    // Order by the latest application first.
    $applications = LoanApplication::with('items', 'user')->latest()->get();

    // Return the view located at resources/views/reports/equipment.blade.php,
    // passing the fetched loan applications data.
    return view('reports.equipment', compact('applications'));
  }

  /**
   * Generate the email accounts report.
   * Fetches all email applications with the associated user data.
   *
   * @return View
   */
  public function emailAccounts(): View
  {
    // Optional: Add authorization check
    // Uncomment one of the lines below if you have defined Gates or Policies
    // $this->authorize('viewEmailAccountsReport'); // Assuming a Policy exists
    // Gate::authorize('view-email-accounts-report'); // Using a Gate directly

    // Fetch all email applications. Eager load the 'user' relationship.
    // Order by the latest application first.
    $applications = EmailApplication::with('user')->latest()->get();

    // Return the view located at resources/views/reports/email-accounts.blade.php,
    // passing the fetched email applications data.
    return view('reports.email-accounts', compact('applications'));
  }

  /**
   * Generate the user activity report.
   * Fetches users and their associated application counts.
   * Can be extended to include activity logs if using a package like spatie/laravel-activitylog.
   *
   * @return View
   */
  public function userActivity(): View
  {
    // Optional: Add authorization check
    // Uncomment one of the lines below if you have defined Gates or Policies
    // $this->authorize('viewUserActivityReport'); // Assuming a Policy exists
    // Gate::authorize('view-user-activity-report'); // Using a Gate directly

    // Fetch all users. Use withCount() to efficiently get the number of
    // email applications, loan applications, and approvals related to each user.
    // This assumes the User model has 'emailApplications', 'loanApplications', and 'approvals' relationships.
    $users = User::withCount(['emailApplications', 'loanApplications', 'approvals'])->get();

    // If you have activity logging (e.g., using spatie/laravel-activitylog),
    // you might fetch activity logs instead or in addition:
    // $activityLogs = Activity::latest()->get(); // Fetch all activity logs, ordered by latest

    // Return the view for the user activity report, located at resources/views/reports/user_activity.blade.php.
    // Pass either the $users data or the $activityLogs data (or both) to the view.
    return view('reports.user_activity', compact('users'));
    // Or pass activity logs: return view('reports.user_activity', compact('activityLogs'));
  }

  // You can add other report methods here as needed for different reports.
  // Remember to create corresponding views in the 'resources/views/reports' directory
  // and define routes for these methods.
}
