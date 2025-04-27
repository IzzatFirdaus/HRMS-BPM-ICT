<?php

namespace App\Policies;

use App\Models\User; // Import the User model
use Illuminate\Auth\Access\Response; // Import Response for more descriptive authorization results
use Illuminate\Auth\Access\HandlesAuthorization; // Keep this trait

class UserPolicy
{
  use HandlesAuthorization; // Use this trait

  /**
   * Determine whether the user can view any models.
   * Only administrators or BPM staff can view the list of users.
   *
   * @param  \App\Models\User  $user
   * @return \Illuminate\Auth\Access\Response|bool
   */
  public function viewAny(User $user): Response|bool
  {
    // Check if the user is an administrator or BPM staff
    return $user->isAdmin() || $user->isBpmStaff()
      ? Response::allow()
      : Response::deny('You do not have permission to view users.');
  }

  /**
   * Determine whether the user can view the model.
   * Users can view their own profile. Administrators and BPM staff can view any user's profile.
   *
   * @param  \App\Models\User  $user  The authenticated user performing the action.
   * @param  \App\Models\User  $model The user model being viewed.
   * @return \Illuminate\Auth\Access\Response|bool
   */
  public function view(User $user, User $model): Response|bool
  {
    // Check if the user is viewing their own profile OR is an admin/BPM staff
    return $user->id === $model->id || $user->isAdmin() || $user->isBpmStaff()
      ? Response::allow()
      : Response::deny('You do not have permission to view this user.');
  }

  /**
   * Determine whether the user can create models.
   * Only administrators or BPM staff can create new users.
   *
   * @param  \App\Models\User  $user
   * @return \Illuminate\Auth\Access\Response|bool
   */
  public function create(User $user): Response|bool
  {
    // Check if the user is an administrator or BPM staff
    return $user->isAdmin() || $user->isBpmStaff()
      ? Response::allow()
      : Response::deny('You do not have permission to create users.');
  }

  /**
   * Determine whether the user can update the model.
   * Users can update their own profile. Administrators and BPM staff can update any user's profile (except potentially other admins).
   *
   * @param  \App\Models\User  $user  The authenticated user performing the action.
   * @param  \App\Models\User  $model The user model being updated.
   * @return \Illuminate\Auth\Access\Response|bool
   */
  public function update(User $user, User $model): Response|bool
  {
    // A user can update their own profile
    if ($user->id === $model->id) {
      return Response::allow();
    }

    // Admins can update any user (except maybe other admins, depending on requirements)
    // For simplicity here, admins can update anyone. Add a check like $user->id !== $model->id || !$model->isAdmin() if needed.
    if ($user->isAdmin()) {
      return Response::allow();
    }

    // BPM staff can update users, but perhaps with limitations (e.g., not admins, not other BPM staff, or only certain fields).
    // This example allows BPM staff to update any non-admin user.
    if ($user->isBpmStaff() && !$model->isAdmin()) {
      return Response::allow();
    }

    // Deny if none of the above conditions are met
    return Response::deny('You do not have permission to update this user.');
  }

  /**
   * Determine whether the user can delete the model.
   * Only administrators can delete users. Prevent deleting oneself.
   *
   * @param  \App\Models\User  $user  The authenticated user performing the action.
   * @param  \App\Models\User  $model The user model being deleted.
   * @return \Illuminate\Auth\Access\Response|bool
   */
  public function delete(User $user, User $model): Response|bool
  {
    // Only administrators can delete users, and they cannot delete themselves
    return $user->isAdmin() && $user->id !== $model->id
      ? Response::allow()
      : Response::deny('You do not have permission to delete this user.');
  }

  /**
   * Determine whether the user can restore the model.
   * Only administrators can restore soft-deleted users.
   *
   * @param  \App\Models\User  $user  The authenticated user performing the action.
   * @param  \App\Models\User  $model The user model being restored.
   * @return \Illuminate\Auth\Access\Response|bool
   */
  public function restore(User $user, User $model): Response|bool
  {
    // Only administrators can restore users
    return $user->isAdmin()
      ? Response::allow()
      : Response::deny('You do not have permission to restore users.');
  }

  /**
   * Determine whether the user can permanently delete the model.
   * Only administrators can force delete users.
   *
   * @param  \App\Models\User  $user  The authenticated user performing the action.
   * @param  \App\Models\User  $model The user model being force deleted.
   * @return \Illuminate\Auth\Access\Response|bool
   */
  public function forceDelete(User $user, User $model): Response|bool
  {
    // Only administrators can force delete users
    return $user->isAdmin()
      ? Response::allow()
      : Response::deny('You do not have permission to permanently delete users.');
  }

  /**
   * Determine whether the user can manage roles and permissions for other users.
   * This is a custom policy method, typically only for administrators.
   *
   * @param  \App\Models\User  $user  The authenticated user performing the action.
   * @return \Illuminate\Auth\Access\Response|bool
   */
  public function manageRoles(User $user): Response|bool
  {
    // Only administrators can manage roles and permissions
    return $user->isAdmin()
      ? Response::allow()
      : Response::deny('You do not have permission to manage user roles and permissions.');
  }

  /**
   * Determine whether the user can view sensitive user data (e.g., NRIC, personal email).
   * This is a custom policy method, typically for administrators and BPM staff.
   *
   * @param  \App\Models\User  $user  The authenticated user performing the action.
   * @param  \App\Models\User  $model The user model whose data is being viewed.
   * @return \Illuminate\Auth\Access\Response|bool
   */
  public function viewSensitiveData(User $user, User $model): Response|bool
  {
    // Users can view their own sensitive data. Admins and BPM staff can view any user's sensitive data.
    return $user->id === $model->id || $user->isAdmin() || $user->isBpmStaff()
      ? Response::allow()
      : Response::deny('You do not have permission to view sensitive user data.');
  }


  // Add other custom policy methods as needed for specific actions
}
