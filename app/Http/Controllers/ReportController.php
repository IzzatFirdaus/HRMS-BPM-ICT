<?php

namespace App\Http\Controllers;

use App\Models\EmailApplication;
use App\Models\LoanApplication;
use App\Models\User; // Import the User model
use Illuminate\Support\Facades\Gate; // Import Gate or use Policies
use Illuminate\View\View; // Import View for return type hinting

class ReportController extends Controller
{
  /**
   * Generate the equipment report.
   *
   * @return View
   */
  public function equipment(): View
  {
    // Optional: Add authorization check (e.g., only admins or specific roles)
    // Uncomment one of the lines below if you have defined Gates or Policies
    // $this->authorize('viewEquipmentReport'); // Assuming a Policy exists with a viewEquipmentReport method
    // Gate::authorize('view-equipment-report'); // Using a Gate directly

    // Fetch loan applications with their items and the related user for the equipment report.
    // 'latest()' orders the results by the latest created_at timestamp.
    $applications = LoanApplication::with('items', 'user')->latest()->get();

    // Return the view for the equipment report.
    // The view file should be located at resources/views/reports/equipment.blade.php
    // The 'compact' function passes the $applications variable to the view.
    return view('reports.equipment', compact('applications'));
  }

  /**
   * Generate the email accounts report.
   *
   * @return View
   */
  public function emailAccounts(): View
  {
    // Optional: Add authorization check
    // Uncomment one of the lines below if you have defined Gates or Policies
    // $this->authorize('viewEmailAccountsReport'); // Assuming a Policy exists
    // Gate::authorize('view-email-accounts-report'); // Using a Gate directly

    // Fetch email applications with the related user data for the email accounts report.
    // 'latest()' orders the results by the latest created_at timestamp.
    $applications = EmailApplication::with('user')->latest()->get(); // Eager load user

    // Return the view for the email accounts report.
    // The view file should be located at resources/views/reports/email-accounts.blade.php
    // The 'compact' function passes the $applications variable to the view.
    return view('reports.email-accounts', compact('applications'));
  }

  /**
   * Generate the user activity report.
   *
   * @return View
   */
  public function userActivity(): View
  {
    // Optional: Add authorization check
    // Uncomment one of the lines below if you have defined Gates or Policies
    // $this->authorize('viewUserActivityReport'); // Assuming a Policy exists
    // Gate::authorize('view-user-activity-report'); // Using a Gate directly

    // Fetch data for the user activity report.
    // We are fetching all users and eager loading the counts of their related
    // email applications, loan applications, and approvals using withCount().
    $users = User::withCount(['emailApplications', 'loanApplications', 'approvals'])->get();

    // If you have activity logging (e.g., using spatie/laravel-activitylog),
    // you might fetch activity logs instead or in addition:
    // Make sure to import the Activity model if you use this:
    // use Spatie\Activitylog\Models\Activity;
    // $activityLogs = Activity::latest()->get();

    // Return the view for the user activity report.
    // The view file should be located at resources/views/reports/user_activity.blade.php
    // The 'compact' function passes the $users variable to the view.
    return view('reports.user_activity', compact('users'));
    // Or pass activity logs: return view('reports.user_activity', compact('activityLogs'));
  }

  // You can add other report methods here as needed for different reports.
  // Remember to create corresponding views in the 'resources/views/reports' directory.
}
