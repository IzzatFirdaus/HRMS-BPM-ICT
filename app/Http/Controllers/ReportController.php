<?php

namespace App\Http\Controllers;

use App\Models\EmailApplication;
use App\Models\LoanApplication;
use App\Models\User; // Import the User model
use Illuminate\Support\Facades\Gate; // Import Gate or use Policies

class ReportController extends Controller
{
  /**
   * Generate the equipment report.
   */
  public function equipment()
  {
    // Optional: Add authorization check (e.g., only admins or specific roles)
    // $this->authorize('viewEquipmentReport'); // Assuming a Gate or Policy exists
    // Gate::authorize('view-equipment-report'); // Using a Gate directly

    // Fetch loan applications with their items for the equipment report
    $applications = LoanApplication::with('items', 'user')->latest()->get(); // Eager load user as well

    // Return the view for the equipment report
    // Ensure your view file name matches: resources/views/reports/equipment.blade.php
    return view('reports.equipment', compact('applications'));
  }

  /**
   * Generate the email accounts report.
   */
  public function emailAccounts()
  {
    // Optional: Add authorization check
    // $this->authorize('viewEmailAccountsReport'); // Assuming a Gate or Policy exists
    // Gate::authorize('view-email-accounts-report'); // Using a Gate directly

    // Fetch email applications with relevant user data for the email accounts report
    $applications = EmailApplication::with('user')->latest()->get(); // Eager load user

    // Return the view for the email accounts report
    // Ensure your view file name matches: resources/views/reports/email-accounts.blade.php
    return view('reports.email-accounts', compact('applications'));
  }

  /**
   * Generate the user activity report.
   */
  public function userActivity()
  {
    // Optional: Add authorization check
    // $this->authorize('viewUserActivityReport'); // Assuming a Gate or Policy exists
    // Gate::authorize('view-user-activity-report'); // Using a Gate directly

    // Fetch data for the user activity report.
    // This could be a list of users, potentially with counts of their applications,
    // or logs of their actions if you implement activity logging (e.g., using spatie/laravel-activitylog).
    // For a basic report, let's fetch users with counts of their applications and approvals.
    $users = User::withCount(['emailApplications', 'loanApplications', 'approvals'])->get();

    // If you have activity logging, you might fetch activity logs instead or in addition:
    // $activityLogs = \Spatie\Activitylog\Models\Activity::latest()->get(); // Assuming spatie/laravel-activitylog

    // Return the view for the user activity report
    // Ensure your view file name matches: resources/views/reports/user_activity.blade.php
    return view('reports.user_activity', compact('users')); // Pass users data to the view
    // Or pass activity logs: return view('reports.user_activity', compact('activityLogs'));
  }

  // You can add other report methods here if needed
}
