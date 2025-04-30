<?php

namespace App\Http\Middleware; // Ensure the namespace is correct for your project

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import the Auth facade
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log; // Import Log facade for logging


class AdminMiddleware
{
  /**
   * Handle an incoming request.
   *
   * This middleware checks if the authenticated user has the 'admin' role.
   * If the user is not authenticated or does not have the 'admin' role,
   * it redirects them to the login page or aborts the request with a 403 Forbidden error.
   *
   * @param  \Illuminate\Http\Request  $request The incoming request.
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next The next middleware in the pipeline.
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function handle(Request $request, Closure $next): Response
  {
    // Log the attempt to access a route protected by AdminMiddleware
    Log::info('Admin middleware check triggered.', [
      'url' => $request->fullUrl(),
      'user_id' => Auth::id() ?? null, // Log user ID if authenticated
      'ip_address' => $request->ip(),
    ]);


    // 1. Check if the user is authenticated
    if (!Auth::check()) {
      Log::warning('Admin middleware: Unauthenticated user attempted access.', [
        'url' => $request->fullUrl(),
        'ip_address' => $request->ip(),
      ]);
      // If not authenticated, redirect to the login page.
      // This is the standard behavior for the 'auth' middleware, but explicit here.
      return redirect()->route('login'); // Assuming you have a named 'login' route
    }

    // Get the authenticated user
    $user = Auth::user();

    // 2. Check if the authenticated user has the 'admin' role.
    // This assumes you are using a role/permission package like Spatie/Laravel-Permission
    // that provides the hasRole() method on the User model.
    // Replace 'admin' with the exact name of your administrator role if it's different.
    if (!$user->hasRole('admin')) {
      Log::warning('Admin middleware: User lacks admin role.', [
        'user_id' => $user->id,
        'roles' => $user->getRoleNames()->toArray(), // Log user's roles
        'url' => $request->fullUrl(),
        'ip_address' => $request->ip(),
      ]);
      // If the user is authenticated but does not have the 'admin' role, abort with 403 Forbidden.
      // The Malay message provides a user-friendly error.
      abort(403, 'Anda tidak mempunyai kebenaran untuk mengakses halaman ini.'); // Malay message
    }

    // If the user is authenticated and has the 'admin' role, proceed with the request
    Log::info('Admin middleware: Access granted.', [
      'user_id' => $user->id,
      'url' => $request->fullUrl(),
    ]);

    return $next($request);
  }

  // Note: To use this middleware, you must register it in your
  // `app/Http/Kernel.php` file in the `$middlewareAliases` array
  // or directly in the `$middleware` or `$middlewareGroups` arrays.
  // Example registration in $middlewareAliases:
  // 'admin' => \App\Http\Middleware\AdminMiddleware::class,
  // Then you can apply it to routes in your web.php file:
  // Route::middleware(['admin'])->group(function () {
  //     // Admin routes here
  // });
}
