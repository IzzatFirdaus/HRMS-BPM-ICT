<?php

namespace App\Policies;

use App\Models\User; // Import the User model
use App\Models\Grade; // Import the Grade model
use Illuminate\Auth\Access\HandlesAuthorization; // Trait for authorization responses

class GradePolicy
{
  use HandlesAuthorization;

  /**
   * Determine whether the user can view any grade models (e.g., on a list page).
   * This policy check is typically used before fetching a collection of resources.
   */
  public function viewAny(User $user): bool
  {
    // Allow Admins or users with a specific permission ('view_grades') to view the list of grades.
    // Assuming user->is_admin and user->hasPermission() are available methods (e.g., via roles/permissions package).
    return $user->is_admin
      || $user->hasPermission('view_grades'); // Users with specific permission can view
  }

  /**
   * Determine whether the user can view a specific grade model.
   * This policy check is typically used after fetching a single resource (e.g., on a grade details page).
   */
  public function view(User $user, Grade $grade): bool
  {
    // Allow Admins or users with a specific permission ('view_grades') to view any specific grade.
    return $user->is_admin
      || $user->hasPermission('view_grades');
  }

  /**
   * Determine whether the user can create new grade models.
   * This action is typically restricted to administrators or users with configuration roles.
   */
  public function create(User $user): bool
  {
    // Only Admins or users with a specific permission ('manage_grades') can create new grades.
    return $user->is_admin
      || $user->hasPermission('manage_grades'); // 'manage_grades' could be a permission for managing core data
  }

  /**
   * Determine whether the user can update a specific grade model.
   * This action is typically restricted to administrators or users with configuration roles.
   */
  public function update(User $user, Grade $grade): bool
  {
    // Only Admins or users with a specific permission ('manage_grades') can update grades.
    return $user->is_admin
      || $user->hasPermission('manage_grades');
  }

  /**
   * Determine whether the user can delete a specific grade model.
   * This action is typically restricted to administrators or users with configuration roles, and might have additional conditions (e.g., no users are assigned to this grade).
   */
  public function delete(User $user, Grade $grade): bool
  {
    // Only Admins or users with a specific permission ('manage_grades') can delete grades.
    // Add condition: Cannot delete a grade if there are users assigned to it.
    return ($user->is_admin || $user->hasPermission('manage_grades'))
      && $grade->users()->doesntExist(); // Cannot delete if users are assigned to this grade
  }

  /**
   * Determine whether the user can restore a specific grade model (if using soft deletes).
   */
  public function restore(User $user, Grade $grade): bool
  {
    // Define restore logic if using soft deletes and this action is allowed.
    // Likely restricted to Admins or specific roles.
    return $user->is_admin || $user->hasPermission('manage_grades'); // Example: allow users with manage_grades to restore
  }

  /**
   * Determine whether the user can permanently delete a specific grade model (if using soft deletes).
   */
  public function forceDelete(User $user, Grade $grade): bool
  {
    // Define force delete logic if using soft deletes and this action is allowed.
    // This is typically a highly restricted action.
    return $user->is_admin; // Example: only Admin can permanently delete
  }

  // Add other specific policy methods as needed.
}
