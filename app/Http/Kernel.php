<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
  /**
   * The application's global HTTP middleware stack.
   *
   * These middleware are run during every request to your application.
   *
   * @var array<int, class-string|string>
   */
  protected $middleware = [
    // \App\Http\Middleware\TrustHosts::class, // Uncomment if you need to specify trusted hosts
    \App\Http\Middleware\TrustProxies::class,
    \Illuminate\Http\Middleware\HandleCors::class, // Handle CORS (Cross-Origin Resource Sharing)
    \App\Http\Middleware\PreventRequestsDuringMaintenance::class, // Redirect to maintenance page if app is down
    \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class, // Validate uploaded file size
    \App\Http\Middleware\TrimStrings::class, // Trim whitespace from request strings
    \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class, // Convert empty strings to null
  ];

  /**
   * The application's route middleware groups.
   *
   * @var array<string, array<int, class-string|string>>
   */
  protected $middlewareGroups = [
    'web' => [
      \App\Http\Middleware\EncryptCookies::class, // Encrypt cookies
      \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class, // Add queued cookies to response
      \Illuminate\Session\Middleware\StartSession::class, // Start the session
      // \Illuminate\Session\Middleware\AuthenticateSession::class, // Optional: Authenticate session
      \Illuminate\View\Middleware\ShareErrorsFromSession::class, // Share validation errors from session
      \App\Http\Middleware\VerifyCsrfToken::class, // Verify CSRF token for form submissions
      \Illuminate\Routing\Middleware\SubstituteBindings::class, // Substitute route model bindings
      \App\Http\Middleware\LocaleMiddleware::class, // Assuming this is an existing HRMS middleware for localization
    ],

    'api' => [
      // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class, // For SPA authentication with Sanctum
      \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api', // Throttle API requests
      \Illuminate\Routing\Middleware\SubstituteBindings::class, // Substitute route model bindings
    ],
  ];

  /**
   * The application's middleware aliases.
   *
   * Aliases may be used to conveniently assign middleware to routes and groups.
   *
   * @var array<string, class-string|string>
   */
  protected $middlewareAliases = [
    'allow_admin_during_maintenance' => \App\Http\Middleware\AllowAdminDuringMaintenance::class, // Existing HRMS middleware
    'auth' => \App\Http\Middleware\Authenticate::class, // Authenticate user
    'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class, // HTTP Basic Authentication
    'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class, // Route model binding
    'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class, // Authenticate session (if not in web group)
    'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class, // Set HTTP cache headers
    'can' => \Illuminate\Auth\Middleware\Authorize::class, // Authorize actions using policies/gates
    'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class, // Redirect authenticated users away from guest routes
    'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class, // Require password confirmation
    'signed' => \App\Http\Middleware\ValidateSignature::class, // Validate signed URLs
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class, // Throttle requests
    'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class, // Ensure email is verified
    'role' => \Spatie\Permission\Middleware\RoleMiddleware::class, // Assuming Spatie permissions: Check user role
    'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class, // Assuming Spatie permissions: Check user permission
    'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class, // Assuming Spatie permissions: Check user role or permission
    'view_logs' => \App\Http\Middleware\ViewLogs::class, // Existing HRMS middleware
    'admin' => \App\Http\Middleware\AdminMiddleware::class, // Existing HRMS middleware: Check admin status

    // Register the new middleware alias for checking user grade level
    'grade' => \App\Http\Middleware\CheckGradeLevel::class,
  ];
}
