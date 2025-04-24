<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth; // Ensure Auth facade is imported

class CheckGradeLevel
{
  /**
   * Handle an incoming request.
   *
   * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
   * @param  int $minGrade The minimum grade level required to access the route.
   */
  public function handle(Request $request, Closure $next, $minGrade): Response
  {
    // 1. Check if the user is authenticated
    if (!Auth::check()) {
      // User is not logged in, redirect to login or show unauthorized page
      // Use the existing HRMS login route or Laravel's default
      return redirect()->route('login')->with('error', 'Please log in to access this page.');
      // Or abort(401, 'Unauthorized.');
    }

    $user = Auth::user();

    // 2. Check if the user has a grade assigned and if the grade relationship is loaded
    // Ensure your User model has a 'grade' relationship
    if (!$user->grade) {
      // User does not have a grade assigned, potentially an issue with user data
      \Log::warning("User ID " . $user->id . " attempted to access grade-restricted route but has no grade assigned.");
      abort(403, 'Access denied. Your account is not configured with a grade.');
    }

    // 3. Get the user's grade level and compare it with the minimum required grade
    // Assuming your Grade model has a 'level' integer column
    if ($user->grade->level < (int) $minGrade) {
      // User's grade level is below the minimum required
      \Log::warning("User ID " . $user->id . " (Grade Level: " . $user->grade->level . ") was denied access to route: " . $request->route()->getName() . " (Requires Grade Level: " . $minGrade . ").");
      abort(403, 'Access denied. Your grade level is insufficient to access this resource.'); // Forbidden
    }

    // If the user meets the criteria, proceed with the request
    return $next($request);
  }
}
