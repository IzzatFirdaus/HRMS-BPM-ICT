<?php

namespace App\Policies;

use App\Models\User; // Import the User model
use App\Models\EmailApplication; // Import the EmailApplication model
use Illuminate\Auth\Access\HandlesAuthorization; // Trait for authorization responses
use Illuminate\Support\Facades\Config; // Import Config facade to access config values

class EmailApplicationPolicy
{
  use HandlesAuthorization;

  /**
   * Determine whether the user can view any email applications (e.g., on a list page).
   * This policy check is typically used before fetching a collection of resources.
   */
  public function viewAny(User $user): bool
  {
    // Allow any authenticated user to view their own list of applications.
    // Additionally, allow users with specific permission ('view_email_applications') or admins to view all applications.
    // Assuming user->id !== null checks if user is authenticated.
    // Assuming user->hasPermission() and user->is_admin are available methods (e.g., via roles/permissions package).
    return $user->id !== null // Any authenticated user can view their own list
      || $user->hasPermission('view_email_applications') // Users with specific permission can view more
      || $user->is_admin; // Admins can view any/all
  }

  /**
   * Determine whether the user can view a specific email application.
   * This policy check is typically used after fetching a single resource.
   */
  public function view(User $user, EmailApplication $emailApplication): bool
  {
    // Admins or users with specific permission ('view_email_applications') can view any application.
    // The applicant (user_id matches the application's user_id) can always view their own application.
    // This logic is already correct.
    return $user->is_admin
      || $user->id === $emailApplication->user_id // Applicant can view their own
      || $user->hasPermission('view_email_applications'); // Users with specific permission can view others
  }

  /**
   * Determine whether the user can create new email applications.
   */
  public function create(User $user): bool
  {
    // Any authenticated user can create an email application for themselves.
    // This check simply confirms the user is logged in.
    // This logic is already correct.
    return $user->id !== null; // Check if user is authenticated
  }

  /**
   * Determine whether the user can update a specific email application.
   */
  public function update(User $user, EmailApplication $emailApplication): bool
  {
    // Only the applicant can update their application.
    // Updates are typically only allowed when the application is in 'draft' status.
    // This logic is already correct.
    return $user->id === $emailApplication->user_id // Must be the applicant
      && $emailApplication->status === 'draft'; // Application must be in draft status
  }

  /**
   * Determine whether the user can delete a specific email application.
   */
  public function delete(User $user, EmailApplication $emailApplication): bool
  {
    // Only the applicant can delete their application.
    // Deletion is typically only allowed when the application is in 'draft' status.
    // This logic is already correct.
    return $user->id === $emailApplication->user_id // Must be the applicant
      && $emailApplication->status === 'draft'; // Application must be in draft status
  }

  /**
   * Determine whether the user can restore a specific email application (if using soft deletes).
   */
  public function restore(User $user, EmailApplication $emailApplication): bool
  {
    // Define restore logic if using soft deletes and this action is allowed.
    // For now, disallow restoration via policy unless specific logic is added.
    return false; // Default to false
  }

  /**
   * Determine whether the user can permanently delete a specific email application (if using soft deletes).
   */
  public function forceDelete(User $user, EmailApplication $emailApplication): bool
  {
    // Define force delete logic if using soft deletes and this action is allowed.
    // For now, disallow force deletion via policy unless specific logic is added.
    return false; // Default to false
  }

  /**
   * Determine whether the user has the general ability to *approve* email applications.
   * In a workflow system with separate Approval tasks, the primary authorization
   * for making a decision on an assigned task happens in the ApprovalPolicy.
   * This method in EmailApplicationPolicy might be used as a supplementary check
   * or for displaying UI elements (e.g., "Show approval buttons if user can approve").
   */
  public function approve(User $user, EmailApplication $emailApplication): bool
  {
    // Check if the user meets the criteria to be an approver (e.g., minimum grade level, specific role).
    // Check if the application is in a status where approval is currently possible (e.g., 'pending_support').
    // Assuming user->grade relationship and grade->level property exist.
    // Assuming user->hasAnyRole() method is available.
    $minApprovalGradeLevel = Config::get('motac.approval.min_approver_grade_level'); // Access config using facade

    return $user->grade !== null // User must have a grade assigned
      && $user->grade->level >= $minApprovalGradeLevel // User's grade must meet the minimum required level
      && $user->hasAnyRole(['approver', 'admin']) // User must have 'approver' or 'admin' role
      && $emailApplication->status === 'pending_support'; // Application must be in 'pending_support' status for this approval step
    // Note: In a multi-stage workflow, you might need to check the *current* pending Approval record's stage.
  }

  /**
   * Determine whether the user has the general ability to *reject* email applications.
   * Similar to the `approve` method, the primary authorization for rejecting a specific
   * assigned task is often handled in the ApprovalPolicy.
   * This method might serve as a supplementary check or for UI logic.
   */
  public function reject(User $user, EmailApplication $emailApplication): bool
  {
    // Check if the user meets the criteria to be an approver/rejector (minimum grade, role).
    // Check if the application is in a pending status where rejection is possible ('pending_support' or 'pending_admin').
    $minApprovalGradeLevel = Config::get('motac.approval.min_approver_grade_level'); // Access config using facade

    return $user->grade !== null
      && $user->grade->level >= $minApprovalGradeLevel
      && $user->hasAnyRole(['approver', 'admin'])
      && ($emailApplication->status === 'pending_support' || $emailApplication->status === 'pending_admin'); // Rejection possible from these pending statuses
    // Note: In a multi-stage workflow, check the current pending Approval record's stage.
  }

  /**
   * Determine whether the user has the general ability to *process* (IT Admin step) email applications.
   */
  public function process(User $user, EmailApplication $emailApplication): bool
  {
    // Only users with specific IT admin roles ('it_admin' or 'admin') can perform the processing step.
    // The application must be in 'pending_admin' status for this action.
    // Assuming user->hasAnyRole() is available.
    return $user->hasAnyRole(['it_admin', 'admin']) // User must have 'it_admin' or 'admin' role
      && $emailApplication->status === 'pending_admin'; // Application must be in 'pending_admin' status
  }

  // Add other specific policy methods as needed for different actions in the workflow
  // For example, 'provision', 'close', 'assign_final_details' etc.
}
