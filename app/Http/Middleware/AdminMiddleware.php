<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import the Auth facade
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
  /**
   * Handle an incoming request.
   *
   * This middleware checks if the authenticated user has administrator privileges.
   * If the user is not authenticated or does not have admin privileges,
   * it redirects them or aborts the request with a 403 Forbidden error.
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function handle(Request $request, Closure $next): Response
  {
    // Check if the user is authenticated
    if (!Auth::check()) {
      // If not authenticated, redirect to login or abort
      // You can customize this behavior based on your application's needs
      // Option 1: Redirect to login
      return redirect()->route('login'); // Assuming you have a 'login' route

      // Option 2: Abort with 403 Forbidden
      // abort(403, 'Unauthorized action.');
    }

    // Check if the authenticated user has admin privileges.
    // This is where you implement your specific logic for checking admin status.
    // Examples:
    // 1. Check for a specific 'is_admin' boolean column on the User model:
    // if (!Auth::user()->is_admin) {
    //     abort(403, 'Unauthorized action.');
    // }

    // 2. Check for a specific role using a package like Spatie/Laravel-Permission:
    // if (!Auth::user()->hasRole('admin')) {
    //     abort(403, 'Unauthorized action.');
    // }

    // 3. Check for a specific permission using a package like Spatie/Laravel-Permission:
    // if (!Auth::user()->can('access-admin-panel')) {
    //     abort(403, 'Unauthorized action.');
    // }

    // For this example, let's assume there's an 'is_admin' column or a simple check
    // Replace this with your actual admin check logic
    if (!Auth::user() || !Auth::user()->is_admin) { // Example check: assuming an 'is_admin' column
      // If the user is authenticated but not an admin, abort with 403
      abort(403, 'Anda tidak mempunyai kebenaran untuk mengakses halaman ini.'); // Malay message for Forbidden
    }


    // If the user is authenticated and is an admin, proceed with the request
    return $next($request);
  }
}
