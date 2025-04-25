<?php

namespace App\Policies;

use App\Models\User; // Import the User model
use App\Models\LoanApplication; // Import the LoanApplication model
use Illuminate\Auth\Access\HandlesAuthorization; // Trait for authorization responses
use Illuminate\Support\Facades\Config; // Import Config facade to access config values

class LoanApplicationPolicy
{
  use HandlesAuthorization;

  /**
   * Determine whether the user can view any loan applications (e.g., on a list page).
   * This policy check is typically used before fetching a collection of resources.
   */
  public function viewAny(User $user): bool
  {
    // Allow any authenticated user to view their own list of applications.
    // Additionally, allow users with specific roles ('bpm_staff') or permissions ('view_loan_applications') or admins to view all applications.
    // Assuming user->id !== null checks if user is authenticated.
    // Assuming user->hasAnyRole() and user->is_admin are available methods (e.g., via roles/permissions package).
    return $user->id !== null // Any authenticated user can view their own list
      || $user->hasAnyRole(['bpm_staff', 'view_loan_applications']) // BPM staff or users with specific permission can view more
      || $user->is_admin; // Admins can view any/all
  }

  /**
   * Determine whether the user can view a specific loan application.
   * This policy check is typically used after fetching a single resource.
   */
  public function view(User $user, LoanApplication $loanApplication): bool
  {
    // Admins or users with specific roles/permissions ('bpm_staff', 'view_loan_applications') can view any application.
    // The applicant (user_id matches the application's user_id) can always view their own application.
    // This logic is already correct.
    return $user->is_admin
      || $user->id === $loanApplication->user_id // Applicant can view their own
      || $user->hasAnyRole(['bpm_staff', 'view_loan_applications']); // BPM staff or users with specific permission can view others
  }

  /**
   * Determine whether the user can create new loan applications.
   */
  public function create(User $user): bool
  {
    // Any authenticated user can create a loan application for themselves.
    // This check simply confirms the user is logged in.
    // This logic is already correct.
    return $user->id !== null; // Check if user is authenticated
  }

  /**
   * Determine whether the user can update a specific loan application.
   */
  public function update(User $user, LoanApplication $loanApplication): bool
  {
    // Only the applicant can update their application.
    // Updates are typically only allowed when the application is in 'draft' status.
    // This logic is already correct.
    return $user->id === $loanApplication->user_id // Must be the applicant
      && $loanApplication->status === 'draft'; // Application must be in draft status
  }

  /**
   * Determine whether the user can delete a specific loan application.
   */
  public function delete(User $user, LoanApplication $loanApplication): bool
  {
    // Only the applicant can delete their application.
    // Deletion is typically only allowed when the application is in 'draft' status.
    // This logic is already correct.
    return $user->id === $loanApplication->user_id // Must be the applicant
      && $loanApplication->status === 'draft'; // Application must be in draft status
  }

  /**
   * Determine whether the user can restore a specific loan application (if using soft deletes).
   */
  public function restore(User $user, LoanApplication $loanApplication): bool
  {
    // Define restore logic if using soft deletes and this action is allowed.
    // For now, disallow restoration via policy unless specific logic is added.
    return false; // Default to false
  }

  /**
   * Determine whether the user can permanently delete a specific loan application (if using soft deletes).
   */
  public function forceDelete(User $user, LoanApplication $loanApplication): bool
  {
    // Define force delete logic if using soft deletes and this action is allowed.
    // For now, disallow force deletion via policy unless specific logic is added.
    return false; // Default to false
  }

  /**
   * Determine whether the user has the general ability to *approve* loan applications.
   * In a workflow system with separate Approval tasks (managed by Approval model),
   * the primary authorization for making a decision on an assigned task happens in the ApprovalPolicy.
   * This method in LoanApplicationPolicy might be used as a supplementary check
   * or for displaying UI elements (e.g., "Show approval buttons if user can approve this type of application").
   */
  public function approve(User $user, LoanApplication $loanApplication): bool
  {
    // Check if the user meets the criteria to be an approver (minimum grade level, specific role).
    // Check if the application is in a status where approval is currently possible (e.g., 'pending_support').
    // Assuming user->grade relationship and grade->level property exist.
    // Assuming user->hasAnyRole() method is available.
    $minApprovalGradeLevel = Config::get('motac.approval.min_approver_grade_level'); // Access config using facade

    return $user->grade !== null // User must have a grade assigned
      && $user->grade->level >= $minApprovalGradeLevel // User's grade must meet the minimum required level
      && $user->hasAnyRole(['approver', 'admin']) // User must have 'approver' or 'admin' role
      && $loanApplication->status === 'pending_support'; // Application must be in 'pending_support' status for this approval step
    // Note: In a multi-stage workflow, check the *current* pending Approval record's stage and if assigned to this user (handled in ApprovalPolicy).
  }

  /**
   * Determine whether the user has the general ability to *reject* loan applications.
   * Similar to the `approve` method, the primary authorization for rejecting a specific
   * assigned task is often handled in the ApprovalPolicy.
   * This method might serve as a supplementary check or for UI logic.
   */
  public function reject(User $user, LoanApplication $loanApplication): bool
  {
    // Check if the user meets the criteria to be an approver/rejector (minimum grade, role).
    // Check if the application is in a pending status where rejection is possible ('pending_support' or 'pending_admin').
    $minApprovalGradeLevel = Config::get('motac.approval.min_approver_grade_level'); // Access config using facade

    return $user->grade !== null
      && $user->grade->level >= $minApprovalGradeLevel
      && $user->hasAnyRole(['approver', 'admin'])
      && ($loanApplication->status === 'pending_support' || $loanApplication->status === 'pending_admin'); // Rejection possible from these pending statuses
    // FIX: Added 'pending_admin' to rejection statuses for consistency with EmailPolicy and potential multi-stage workflow.
    // Note: In a multi-stage workflow, check the current pending Approval record's stage and if assigned to this user (handled in ApprovalPolicy).
  }

  /**
   * Determine whether the user can issue equipment for the loan application.
   * This action is typically performed by BPM staff after the application is approved.
   */
  public function issue(User $user, LoanApplication $loanApplication): bool
  {
    // Only BPM staff or admins can issue equipment.
    // Issuance is only possible if the application status is 'approved' or 'partially_issued' (if managing partial issues).
    // Assuming user->hasAnyRole() is available.
    return $user->hasAnyRole(['bpm_staff', 'admin']) // User must have 'bpm_staff' or 'admin' role
      && ($loanApplication->status === 'approved' || $loanApplication->status === 'partially_issued'); // Application must be in approved or partially issued status
  }

  /**
   * Determine whether the user can process return for the loan application.
   * This action is typically performed by BPM staff when equipment is returned.
   */
  public function processReturn(User $user, LoanApplication $loanApplication): bool
  {
    // Only BPM staff or admins can process returns.
    // Return processing is possible if the application status is 'issued' or 'overdue'.
    // Assuming user->hasAnyRole() is available.
    return $user->hasAnyRole(['bpm_staff', 'admin']) // User must have 'bpm_staff' or 'admin' role
      && ($loanApplication->status === 'issued' || $loanApplication->status === 'overdue'); // Application must be in issued or overdue status
  }

  // Add other specific policy methods as needed for different actions in the workflow
  // For example, 'assign_equipment', 'complete_loan' etc.
}
