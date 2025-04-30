<?php

namespace App\Http\Middleware; // Ensure the namespace is correct for your project

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Import the Auth facade
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Illuminate\Support\Facades\Config; // Import Config facade


class AllowAdminDuringMaintenance
{
  /**
   * Handle an incoming request.
   *
   * This middleware checks if the application is down for maintenance.
   * If it is, it allows access only to authenticated users who have one
   * of the roles defined in the application's configuration for maintenance bypass.
   * Other users receive a 503 Service Unavailable response.
   *
   * @param  \Illuminate\Http\Request  $request The incoming request.
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next The next middleware in the pipeline.
   * @return \Symfony\Component\HttpFoundation\Response
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   */
  public function handle(Request $request, Closure $next): Response
  {
    // Log the attempt to access a route while maintenance mode is active
    if (app()->isDownForMaintenance()) {
      Log::info('Maintenance mode active. Checking bypass permissions.', [
        'url' => $request->fullUrl(),
        'user_id' => Auth::id() ?? null, // Log user ID if authenticated
        'ip_address' => $request->ip(),
      ]);
    }

    // Check if the application is currently in maintenance mode
    if (app()->isDownForMaintenance()) {

      // Get the list of roles allowed to bypass maintenance mode from configuration.
      // Define an array like 'maintenance_bypass_roles' => ['admin', 'it_admin']
      // in your config/app.php or a custom config file.
      $bypassRoles = Config::get('app.maintenance_bypass_roles', ['admin']); // Default to 'admin' role if config not found

      // Check if the user is authenticated
      if (!Auth::check()) {
        Log::warning('Maintenance mode: Unauthenticated user attempted bypass.', [
          'url' => $request->fullUrl(),
          'ip_address' => $request->ip(),
        ]);
        // If not authenticated, redirect to the login page.
        // The maintenance mode response (503) will likely be triggered anyway
        // unless your login route is excluded from maintenance mode entirely.
        // Consider adding your login route to the 'except' array in the maintenance middleware configuration.
        return redirect()->route('login'); // Assuming you have a named 'login' route
      }

      // Get the authenticated user
      $user = Auth::user();

      // Check if the authenticated user has ANY of the roles allowed to bypass maintenance mode.
      // This assumes you are using a role/permission package like Spatie/Laravel-Permission.
      $canBypass = $user->hasAnyRole($bypassRoles); // Use hasAnyRole for multiple bypass roles

      if ($canBypass) {
        Log::info('Maintenance mode bypass granted.', [
          'user_id' => $user->id,
          'roles' => $user->getRoleNames()->toArray(), // Log user's roles
          'url' => $request->fullUrl(),
        ]);
        // If the user has a bypass role, allow the request to proceed.
        return $next($request);
      } else {
        Log::warning('Maintenance mode: User lacks bypass role.', [
          'user_id' => $user->id,
          'roles' => $user->getRoleNames()->toArray(), // Log user's roles
          'url' => $request->fullUrl(),
          'ip_address' => $request->ip(),
        ]);
        // If the user is authenticated but does not have a bypass role, throw a 503 HttpException.
        throw new HttpException(503, 'Laman web ini sedang dalam proses penyelenggaraan. Sila cuba sebentar lagi.'); // Malay message
      }
    }

    // If the application is NOT in maintenance mode, simply proceed with the request.
    return $next($request);
  }

  // Note: To use this middleware for maintenance mode bypass, you typically need to
  // replace Laravel's default `Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode`
  // middleware in your `app/Http/Kernel.php` file's `$middleware` array with this custom middleware.
  // Make sure to register this middleware in `$middlewareAliases` first if you use an alias.
}
