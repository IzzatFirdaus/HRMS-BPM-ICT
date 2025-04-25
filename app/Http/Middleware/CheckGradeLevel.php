<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // Ensure Auth facade is imported
use Illuminate\Support\Facades\Log; // FIX: Import Log facade

class CheckGradeLevel
{
  /**
   * Handle an incoming request.
   * This middleware checks if the authenticated user's grade level
   * is greater than or equal to the minimum grade specified for the route.
   *
   * @param \Illuminate\Http\Request $request The incoming request.
   * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next The next middleware or request handler.
   * @param int $minGrade The minimum grade level required to access the route.
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function handle(Request $request, Closure $next, $minGrade): Response
  {
    // 1. Check if the user is authenticated
    if (!Auth::check()) {
      // User is not logged in, redirect to login or show unauthorized page
      Log::warning("Unauthenticated user attempted to access grade-restricted route: " . $request->route()?->getName()); // Log the attempt
      // Use the existing HRMS login route or Laravel's default
      return redirect()->route('login')->with('error', 'Please log in to access this page.');
      // Or abort(401, 'Unauthorized.'); // Alternatively, return a 401 Unauthorized response
    }

    $user = Auth::user();

    // 2. Check if the user has a grade assigned and if the grade relationship is loaded
    // Ensure your User model has a 'grade' relationship defined
    if (!$user->grade) {
      // User does not have a grade assigned, potentially an issue with user data
      Log::warning("User ID " . $user->id . " attempted to access grade-restricted route but has no grade assigned. Route: " . $request->route()?->getName()); // FIX: Removed leading backslash from Log
      abort(403, 'Access denied. Your account is not configured with a grade.'); // Forbidden
    }

    // 3. Get the user's grade level and compare it with the minimum required grade
    // Assuming your Grade model has a 'level' integer column
    $userGradeLevel = $user->grade->level;
    $requiredGradeLevel = (int) $minGrade; // Cast middleware parameter to integer

    if ($userGradeLevel < $requiredGradeLevel) {
      // User's grade level is below the minimum required
      Log::warning("User ID " . $user->id . " (Grade Level: " . $userGradeLevel . ") was denied access to route: " . $request->route()?->getName() . " (Requires Grade Level: " . $requiredGradeLevel . ")."); // FIX: Removed leading backslash from Log
      abort(403, 'Access denied. Your grade level is insufficient to access this resource.'); // Forbidden
    }

    // If the user meets the criteria, proceed with the request
    return $next($request);
  }
}
