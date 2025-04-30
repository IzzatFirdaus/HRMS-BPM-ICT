<?php

namespace App\Http\Controllers; // Ensure the namespace is correct for your project

use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Http\Request; // Import Request
use Illuminate\View\View; // Import View for type hinting
use Illuminate\Http\RedirectResponse; // Import RedirectResponse for type hinting
use Illuminate\Support\Facades\Log; // Import Log facade for logging


class DashboardController extends Controller
{
  /**
   * Apply authentication middleware to this controller.
   */
  public function __construct()
  {
    // Ensure the user is authenticated to access any dashboard
    $this->middleware('auth');
  }

  /**
   * Display the appropriate dashboard view based on the user's role.
   *
   * Checks the authenticated user's role(s) and returns the corresponding
   * dashboard view ('admin', 'approver', 'bpm_staff', or 'user').
   *
   * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse The dashboard view or a redirect.
   */
  public function index(): View|RedirectResponse
  {
    $user = Auth::user();

    // Log the user accessing the dashboard
    Log::info('User accessing dashboard.', [
      'user_id' => Auth::id(),
      'roles' => $user->getRoleNames()->toArray(), // Log user roles (assuming Spatie/Laravel-Permission)
      'ip_address' => request()->ip(),
    ]);

    // IMPORTANT: The role names checked below ('admin', 'approver', 'bpm_staff')
    // must exactly match the role names defined in your role/permission system.
    // The order of checks might matter if a user can have multiple roles;
    // typically, you check for more privileged roles first.

    // Check for Administrator role
    if ($user->hasRole('admin')) {
      Log::info('User directed to Admin dashboard.', ['user_id' => Auth::id()]);
      // Return the admin dashboard view (e.g., resources/views/dashboard/admin.blade.php)
      return view('dashboard.admin');
    }

    // Check for Approver role (e.g., Grade 41+ officers who review applications)
    // A user might be an Approver and also BPM staff; you need to decide which dashboard takes precedence or combine them.
    // This check assumes a specific 'approver' role exists. Alternatively, you might check user's grade level or a permission.
    if ($user->hasRole('approver')) {
      Log::info('User directed to Approver dashboard.', ['user_id' => Auth::id()]);
      // Return the approver dashboard view (e.g., resources/views/dashboard/approver.blade.php)
      // This dashboard would typically show pending approvals assigned to the user.
      return view('dashboard.approver');
    }

    // Check for BPM Equipment Staff role (those who manage equipment issuance/return)
    // This check assumes a specific 'bpm_staff' role exists.
    if ($user->hasRole('bpm_staff')) {
      Log::info('User directed to BPM Staff dashboard.', ['user_id' => Auth::id()]);
      // Return the BPM staff dashboard view (e.g., resources/views/dashboard/bpm_staff.blade.php)
      // This dashboard would typically show equipment status, pending issuances/returns.
      return view('dashboard.bpm_staff');
    }

    // Default to the standard User dashboard if none of the specific roles match
    Log::info('User directed to Standard User dashboard.', ['user_id' => Auth::id()]);
    // Return the standard user dashboard view (e.g., resources/views/dashboard/user.blade.php)
    // This dashboard would typically show the user's own application statuses, loan items, etc.
    return view('dashboard.user');

    // Optional: If a user somehow reaches here without any expected role and you want
    // to redirect them away or show an error:
    // Log::warning('User with no specific dashboard role reached dashboard index.', ['user_id' => Auth::id()]);
    // return redirect('/unauthorized')->with('error', 'You do not have a specific dashboard assigned.');
  }
}
