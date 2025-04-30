<?php

namespace App\Http\Controllers; // Ensure the namespace is correct for your project

use App\Http\Controllers\Controller; // Extend the base Controller
use Illuminate\View\View; // Import View for type hinting
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Illuminate\Http\Request; // Import Request
use Illuminate\Support\Facades\Auth; // Import Auth facade for accessing authenticated user


class MiscError extends Controller
{
  /**
   * Display the miscellaneous error page.
   *
   * This method is typically routed to display a generic error view (e.g., 404 Not Found, 500 Internal Server Error).
   * It sets up basic page configurations and returns the error view.
   *
   * @param \Illuminate\Http\Request $request The incoming request instance.
   * @return \Illuminate\View\View The view for the error page.
   */
  public function index(Request $request): View // Added Request parameter and return type hint
  {
    // Set page configurations specific to this error page layout
    $pageConfigs = ['myLayout' => 'blank']; // Example configuration

    // Log that the miscellaneous error page was accessed
    // This can help track errors users encounter.
    Log::warning('Miscellaneous error page accessed.', [
      'url' => $request->fullUrl(), // Log the URL the user was trying to reach
      'method' => $request->method(), // Log the HTTP method
      'user_id' => Auth::check() ? Auth::id() : null, // Log user ID if authenticated
      'ip_address' => $request->ip(), // Log the user's IP address
      // You might add more context here if available (e.g., previous URL via $request->headers->get('referer'))
    ]);


    // Return the view for the error page, passing configurations
    // Ensure the view path 'content.pages-misc-error' is correct for your project structure
    return view('content.pages-misc-error', ['pageConfigs' => $pageConfigs]);
  }

  // Note: This controller is usually simple and doesn't require other resource methods.
  // Complex error handling is typically managed in App\Exceptions\Handler.php
}
