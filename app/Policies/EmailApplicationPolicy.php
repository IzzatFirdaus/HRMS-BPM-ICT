<?php

namespace App\Policies;

use App\Models\User;
use App\Models\EmailApplication;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmailApplicationPolicy
{
  use HandlesAuthorization;

  /**
   * Determine whether the user can view any models.
   */
  public function viewAny(User $user): bool
  {
    // Admins or specific roles can view all applications
    // Users can view their own applications
    return $user->is_admin || $user->hasPermission('view_email_applications'); // Assuming spatie/laravel-permission or similar
  }

  /**
   * Determine whether the user can view the model.
   */
  public function view(User $user, EmailApplication $emailApplication): bool
  {
    // Admins or specific roles can view any application
    // Users can view their own application
    return $user->is_admin || $user->id === $emailApplication->user_id || $user->hasPermission('view_email_applications');
  }

  /**
   * Determine whether the user can create models.
   */
  public function create(User $user): bool
  {
    // Any authenticated user can create an email application for themselves
    return $user->id !== null;
  }

  /**
   * Determine whether the user can update the model.
   */
  public function update(User $user, EmailApplication $emailApplication): bool
  {
    // Only the applicant can update their application, and only if it's in 'draft' status
    return $user->id === $emailApplication->user_id && $emailApplication->status === 'draft';
  }

  /**
   * Determine whether the user can delete the model.
   */
  public function delete(User $user, EmailApplication $emailApplication): bool
  {
    // Only the applicant can delete their application, and only if it's in 'draft' status
    return $user->id === $emailApplication->user_id && $emailApplication->status === 'draft';
  }

  /**
   * Determine whether the user can restore the model.
   */
  public function restore(User $user, EmailApplication $emailApplication): bool
  {
    // Define restore logic if using soft deletes
    return false; // Or add appropriate logic
  }

  /**
   * Determine whether the user can permanently delete the model.
   */
  public function forceDelete(User $user, EmailApplication $emailApplication): bool
  {
    // Define force delete logic if using soft deletes
    return false; // Or add appropriate logic
  }

  /**
   * Determine whether the user can approve the model.
   */
  public function approve(User $user, EmailApplication $emailApplication): bool
  {
    // Only users with a grade level >= min_approver_grade_level and specific roles can approve
    // and only if the application is in a pending status (e.g., 'pending_support')
    $minApprovalGradeLevel = config('motac.approval.min_approver_grade_level');
    return $user->grade && $user->grade->level >= $minApprovalGradeLevel && $user->hasAnyRole(['approver', 'admin']) && $emailApplication->status === 'pending_support';
  }

  /**
   * Determine whether the user can reject the model.
   */
  public function reject(User $user, EmailApplication $emailApplication): bool
  {
    // Same logic as approve, but allow rejection from pending status
    $minApprovalGradeLevel = config('motac.approval.min_approver_grade_level');
    return $user->grade && $user->grade->level >= $minApprovalGradeLevel && $user->hasAnyRole(['approver', 'admin']) && ($emailApplication->status === 'pending_support' || $emailApplication->status === 'pending_admin');
  }

  /**
   * Determine whether the user can process (IT Admin step) the model.
   */
  public function process(User $user, EmailApplication $emailApplication): bool
  {
    // Only IT admin roles can process the application
    return $user->hasAnyRole(['it_admin', 'admin']) && $emailApplication->status === 'pending_admin';
  }
}
