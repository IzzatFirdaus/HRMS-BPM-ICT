<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LoanApplication;
use Illuminate\Auth\Access\HandlesAuthorization;

class LoanApplicationPolicy
{
  use HandlesAuthorization;

  /**
   * Determine whether the user can view any models.
   */
  public function viewAny(User $user): bool
  {
    // Admins, specific roles (BPM), or users viewing their own applications
    return $user->is_admin || $user->hasAnyRole(['bpm_staff', 'view_loan_applications']); // Adjust roles/permissions
  }

  /**
   * Determine whether the user can view the model.
   */
  public function view(User $user, LoanApplication $loanApplication): bool
  {
    // Admins, specific roles (BPM), or the applicant can view
    return $user->is_admin || $user->id === $loanApplication->user_id || $user->hasAnyRole(['bpm_staff', 'view_loan_applications']); // Adjust roles/permissions
  }

  /**
   * Determine whether the user can create models.
   */
  public function create(User $user): bool
  {
    // Any authenticated user can create a loan application
    return $user->id !== null;
  }

  /**
   * Determine whether the user can update the model.
   */
  public function update(User $user, LoanApplication $loanApplication): bool
  {
    // Only the applicant can update their application, and only if it's in 'draft' status
    return $user->id === $loanApplication->user_id && $loanApplication->status === 'draft';
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, LoanApplication $loanApplication): bool
  {
    // Only the applicant can delete their application, and only if it's in 'draft' status
    return $user->id === $loanApplication->user_id && $loanApplication->status === 'draft';
  }

  /**
   * Determine whether the user can restore the model.
   */
  public function restore(User $user, LoanApplication $loanApplication): bool
  {
    // Define restore logic if using soft deletes
    return false; // Or add appropriate logic
  }

  /**
   * Determine whether the user can permanently delete the model.
   */
  public function forceDelete(User $user, LoanApplication $loanApplication): bool
  {
    // Define force delete logic if using soft deletes
    return false; // Or add appropriate logic
  }

  /**
   * Determine whether the user can approve the model.
   */
  public function approve(User $user, LoanApplication $loanApplication): bool
  {
    // Only users with a grade level >= min_approver_grade_level and specific roles can approve
    // and only if the application is in a pending status (e.g., 'pending_support')
    $minApprovalGradeLevel = config('motac.approval.min_approver_grade_level');
    return $user->grade && $user->grade->level >= $minApprovalGradeLevel && $user->hasAnyRole(['approver', 'admin']) && $loanApplication->status === 'pending_support';
  }

  /**
   * Determine whether the user can reject the model.
   */
  public function reject(User $user, LoanApplication $loanApplication): bool
  {
    // Same logic as approve, but allow rejection from pending status
    $minApprovalGradeLevel = config('motac.approval.min_approver_grade_level');
    return $user->grade && $user->grade->level >= $minApprovalGradeLevel && $user->hasAnyRole(['approver', 'admin']) && $loanApplication->status === 'pending_support';
  }

  /**
   * Determine whether the user can issue equipment for the loan.
   */
  public function issue(User $user, LoanApplication $loanApplication): bool
  {
    // Only BPM staff or admins can issue equipment
    return $user->hasAnyRole(['bpm_staff', 'admin']) && ($loanApplication->status === 'approved' || $loanApplication->status === 'partially_issued');
  }

  /**
   * Determine whether the user can process return for the loan.
   */
  public function processReturn(User $user, LoanApplication $loanApplication): bool
  {
    // Only BPM staff or admins can process returns
    return $user->hasAnyRole(['bpm_staff', 'admin']) && ($loanApplication->status === 'issued' || $loanApplication->status === 'overdue');
  }
}
