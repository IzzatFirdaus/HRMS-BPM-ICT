<?php

namespace App\Services; // Ensure this namespace is correct for your project

use App\Models\User; // Import the User model
use App\Models\Grade; // Import Grade model for relationships/filtering
use App\Models\Department; // Import Department model for relationships/filtering
use Illuminate\Database\Eloquent\Collection; // Import Collection for return types
use Illuminate\Support\Facades\Hash; // Import Hash facade for password hashing
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Exception; // Import base Exception class
use Illuminate\Database\QueryException; // Import if interacting directly with DB
use Illuminate\Support\Facades\DB; // Import DB facade if using transactions or manual queries
use Illuminate\Support\Facades\Auth; // Import Auth facade if used for business rules (e.g., self-deletion check)
use Illuminate\Database\Eloquent\Model; // Import Model for type hinting (e.g., approvable models)


/**
 * Service to encapsulate core user-related business logic for the HRMS system.
 * This includes user creation, update, deletion (CRUD), and complex retrieval/lookup operations
 * based on roles, grades, departments, and potentially other criteria.
 */
class UserService
{
  // Inject any dependencies needed for user-related logic here
  // (e.g., repositories, external system clients, notification service)

  /**
   * Create a new user record in the system.
   *
   * @param array $data Validated data for user creation, typically from a StoreUserRequest.
   * Expected keys should match fillable attributes on the User model
   * (e.g., 'full_name', 'motac_email', 'password', 'nric', 'mobile_number',
   * 'personal_email', 'department_id', 'position_id', 'grade_id',
   * 'service_status', 'appointment_type', 'status').
   * @return \App\Models\User The newly created user instance.
   * @throws \Exception
   */
  public function createUser(array $data): User
  {
    Log::info('Attempting to create user via service.', ['data_keys' => array_keys($data)]);

    try {
      // Ensure the password is hashed before creating the user
      // The 'password' field should be required and confirmed in the Form Request
      if (isset($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        // This case indicates a validation issue if password is required
        Log::error('Password data is missing in createUser service method.', ['data_keys' => array_keys($data)]);
        throw new Exception("Password data is missing for user creation.");
      }

      // Set default status if not provided
      // if (!isset($data['status'])) {
      $data['status'] = 'active'; // Assuming 'active' as default status for new users
      // }

      // Create the user record using mass assignment (ensure fillable properties are set on User model)
      $user = User::create($data);

      // Optional: Assign default roles/permissions here using your role/permission package
      // Example using Spatie roles:
      // $user->assignRole('user');

      // Optional: Trigger events or send initial welcome email/notification
      // event(new UserRegisteredViaAdmin($user)); // Custom event for admin registration


      Log::info('User created successfully via service.', ['user_id' => $user->id, 'email' => $user->motac_email ?? $user->personal_email]);

      return $user;
    } catch (QueryException $e) {
      // Log specific database error
      Log::error('Database error creating user via service: ' . $e->getMessage(), ['data' => $data]);
      throw new Exception("Database error occurred while creating user.", 0, $e); // Wrap and re-throw
    } catch (Exception $e) {
      // Log general exception (including business rule errors if any were added above)
      Log::error('Failed to create user via service: ' . $e->getMessage(), ['data' => $data]);
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Update an existing user record.
   *
   * @param \App\Models\User $user The user instance to update.
   * @param array $data Validated data for user update, typically from an UpdateUserRequest.
   * Expected keys should match fillable attributes on the User model.
   * @return bool True if updated, false otherwise.
   * @throws \Exception
   */
  public function updateUser(User $user, array $data): bool
  {
    Log::info('Attempting to update user via service.', ['user_id' => $user->id, 'data_keys' => array_keys($data)]);

    try {
      // Handle password update if it's included in the data and not empty
      // The 'password' field should be nullable and confirmed in the Form Request for updates
      if (isset($data['password']) && !empty($data['password'])) {
        $data['password'] = Hash::make($data['password']);
      } else {
        // Remove password from data array if it's not being updated (to prevent saving empty password or causing issues)
        unset($data['password']);
      }

      // Update the user record using mass assignment (ensure fillable properties are set or use forceFill)
      $updated = $user->update($data);

      // Optional: Handle updates to roles/permissions here if applicable
      // Example if role assignment is done via this update method and data contains a 'role' key:
      // if (isset($data['role'])) {
      //      $user->syncRoles([$data['role']]); // Assuming Spatie/Laravel-Permission, replace all current roles
      // }


      if ($updated) {
        Log::info('User updated successfully via service.', ['user_id' => $user->id]);
      } else {
        // Note: update() returns false if no attributes were changed. This is not always an error.
        Log::warning('User update via service did not result in changes.', ['user_id' => $user->id, 'data' => $data]);
      }

      // Optional: Trigger events after update
      // event(new UserUpdated($user));

      return $updated;
    } catch (QueryException $e) {
      // Log specific database error
      Log::error('Database error updating user via service: ' . $e->getMessage(), ['user_id' => $user->id, 'data' => $data]);
      throw new Exception("Database error occurred while updating user.", 0, $e); // Wrap and re-throw
    } catch (Exception $e) {
      // Log general exception (including business rule errors if any)
      Log::error('Failed to update user via service: ' . $e->getMessage(), ['user_id' => $user->id, 'data' => $data]);
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Delete a user record.
   * Handles business rules (e.g., cannot delete self, checks for related records).
   * Implements soft deletion strategy if User model uses SoftDeletes trait.
   *
   * @param \App\Models\User $user The user instance to delete.
   * @return bool True if deleted, false otherwise.
   * @throws \Exception If deletion is not allowed based on business rules or database errors occur.
   */
  public function deleteUser(User $user): bool
  {
    Log::info('Attempting to delete user via service.', ['user_id' => $user->id]);

    // --- IMPORTANT BUSINESS RULES FOR DELETION ---
    // Implement checks here based on your system design:
    // 1. Prevent deleting the currently authenticated user.
    if ($user->id === Auth::id()) {
      Log::warning('Service detected attempt to self-delete user.', ['user_id' => $user->id]);
      throw new Exception("Self-deletion is not allowed."); // Throw exception to be caught in controller
    }
    // 2. Prevent deleting users with certain crucial roles (e.g., the sole administrator).
    // Example check assuming Spatie roles:
    // if ($user->hasRole('admin') && User::role('admin')->count() === 1) {
    //     Log::warning('Service detected attempt to delete the last admin user.', ['user_id' => $user->id]);
    //     throw new Exception("Cannot delete the last administrator account.");
    // }
    // 3. Handle related records (applications, transactions, etc.).
    //    - Option A: Prevent deletion if related records exist (requires manual deletion/transfer).
    //    - Option B: Soft delete the user AND associated records (if relationships allow cascading soft deletes or handled manually here).
    //    - Option C: Reassign related records to a generic user or nullify foreign keys (less common/complex).
    // Example check for related records (if preventing deletion):
    // if ($user->loanApplications()->exists() || $user->emailApplications()->exists() /* Add other relationships */) {
    //      Log::warning('Service detected attempt to delete user with associated applications.', ['user_id' => $user->id]);
    //      throw new Exception("Cannot delete user with existing applications. Please delete or reassign related items first."); // Throw exception
    // }
    // --- End Business Rules ---


    // Begin a database transaction if multiple DB operations are involved (e.g., deleting/updating related records)
    // DB::beginTransaction();

    try {
      // Perform the deletion (this will perform a soft delete if the User model uses the SoftDeletes trait)
      $deleted = $user->delete();

      // Optional: Perform cleanup or actions for related records if soft deleting the user
      // and you need to soft delete related items or nullify foreign keys that don't cascade.
      // if ($deleted && $user->trashed()) { // Check if soft deleted
      // Example: Soft delete all applications belonging to this user
      // $user->emailApplications()->delete();
      // $user->loanApplications()->delete();
      // Example: Nullify foreign keys on other tables
      // \App\Models\SomeModel::where('user_id', $user->id)->update(['user_id' => null]);
      // }


      // DB::commit(); // Commit transaction

      if ($deleted) {
        Log::info('User deleted successfully via service.', ['user_id' => $user->id]);
      } else {
        // This might happen if the record was already soft deleted or a before-delete event returned false
        Log::warning('User deletion via service did not result in deletion.', ['user_id' => $user->id]);
      }

      return $deleted;
    } catch (QueryException $e) {
      // DB::rollBack(); // Rollback transaction
      // Log specific database error
      Log::error('Database error deleting user via service: ' . $e->getMessage(), ['user_id' => $user->id]);
      // Check if it's a foreign key constraint violation (might happen if checks above were not exhaustive or cascade is not set)
      if (str_contains($e->getMessage(), 'foreign key constraint')) {
        throw new Exception("Cannot delete user due to related records in the database. Please delete or reassign related items first.", 0, $e); // More specific error
      }
      throw new Exception("Database error occurred while deleting user.", 0, $e); // Wrap and re-throw
    } catch (Exception $e) {
      // DB::rollBack(); // Rollback transaction
      // Log general exception (including those thrown by business rule checks at the beginning of the method)
      Log::error('Failed to delete user via service: ' . $e->getMessage(), ['user_id' => $user->id]);
      throw $e; // Re-throw the exception
    }
  }


  // --- User Retrieval / Lookup Methods ---
  // These methods were included in your provided code draft.

  /**
   * Finds users who have a specific role.
   * Assumes a roles/permissions package like Spatie is used.
   *
   * @param string $roleName The name of the role (e.g., 'admin', 'bpm_staff', 'it_admin').
   * @return Collection<User> A collection of users with the specified role.
   * @throws \Exception
   */
  public function getUsersByRole(string $roleName): Collection
  {
    Log::info("Attempting to get users by role: '{$roleName}'");
    try {
      // Assuming the User model uses a trait for roles (e.g., HasRoles trait from Spatie)
      // This method depends on your specific role/permission package implementation.
      // Example using Spatie roles:
      return User::role($roleName)->get();
    } catch (Exception $e) {
      Log::error("Failed to get users by role '" . $roleName . "': " . $e->getMessage());
      // Log and re-throw or handle the exception as appropriate for your application
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Finds users who have a grade level greater than or equal to a specified level.
   * Assumes User model has a 'grade' relationship and Grade model has a 'level' attribute (integer).
   *
   * @param int $minGradeLevel The minimum integer grade level required (e.g., 41).
   * @return Collection<User> A collection of users meeting the minimum grade level.
   * @throws \Exception
   */
  public function getUsersByMinGradeLevel(int $minGradeLevel): Collection
  {
    Log::info("Attempting to get users by minimum grade level: '{$minGradeLevel}'");
    try {
      // Assuming User model has a 'grade' relationship and Grade model has a 'level' attribute
      return User::whereHas('grade', fn($query) => $query->where('level', '>=', $minGradeLevel))->get();
    } catch (Exception $e) {
      Log::error("Failed to get users by minimum grade level '" . $minGradeLevel . "': " . $e->getMessage());
      Log::error($e->getTraceAsString()); // Log stack trace for detailed debugging
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Finds users within a specific department or division.
   * Assumes User model has a 'department' relationship and Department model has an 'id' (integer) or 'name' (string) attribute.
   *
   * @param int|string $departmentIdentifier The ID (int) or name (string) of the department.
   * @return Collection<User> A collection of users in the specified department.
   * @throws \Exception
   */
  public function getUsersByDepartment(int|string $departmentIdentifier): Collection
  {
    Log::info("Attempting to get users by department: '{$departmentIdentifier}'");
    try {
      // Assuming User model has a 'department' relationship and Department model has an 'id' or 'name'
      $query = User::query();

      if (is_int($departmentIdentifier)) {
        $query->whereHas('department', fn($q) => $q->where('id', $departmentIdentifier));
      } else { // Assuming string name is provided
        $query->whereHas('department', fn($q) => $q->where('name', $departmentIdentifier));
      }

      return $query->get();
    } catch (Exception $e) {
      Log::error("Failed to get users by department '" . $departmentIdentifier . "': " . $e->getMessage());
      Log::error($e->getTraceAsString()); // Log stack trace for detailed debugging
      throw $e; // Re-throw the exception
    }
  }


  /**
   * Finds potential next approvers based on an application and the current stage.
   * This logic could be centralized here if complex and needed by multiple services,
   * or reside in the ApprovalService if tightly coupled to approval workflow.
   * (Note: This is similar to logic that might be in ApprovalService for now).
   *
   * @param Model $approvable The application model (EmailApplication or LoanApplication).
   * @param string $currentStage The identifier for the current approval stage (e.g., 'pending_support', 'pending_admin').
   * @return Collection<User> A collection of potential next approvers (Users).
   * @throws \Exception
   */
  // public function findNextApprovers(Model $approvable, string $currentStage): Collection
  // {
  // Log::info("Attempting to find next approvers for " . get_class($approvable) . ":{$approvable->id} at stage '{$currentStage}'");
  // try {
  // Implement complex logic here to find the next approvers based on:
  // - Type of application ($approvable instanceof EmailApplication or LoanApplication)
  // - Current approval stage ($currentStage)
  // - Applicant's department/grade/position ($approvable->user->...)
  // - Approval matrix rules defined in your system.
  // This might involve querying users by role, grade, department, or specific assignments.

  // Example placeholder logic (replace with your actual approval matrix rules):
  // if ($currentStage === 'initial_submission') {
  //      // Find supporting officers in the applicant's department with a specific grade/role
  //      return $this->getUsersByDepartment($approvable->user->department_id)
  //                  ->filter(fn($user) => $user->grade->level >= 41 && $user->hasPermissionTo('certify applications')); // Example filter
  // } elseif ($currentStage === 'certified') {
  //      // Find IT/Admin approvers
  //      return $this->getUsersByRole('it_admin'); // Example lookup by role
  // }
  // // Return empty collection if no approvers found for the stage
  // return collect();

  // } catch (Exception $e) {
  //      Log::error("Failed to find next approvers for " . get_class($approvable) . ":{$approvable->id} at stage '{$currentStage}': " . $e->getMessage());
  //      throw $e; // Re-throw exception
  // }
  // }


  // --- Other Potential User Management Methods via Service ---
  // Consider adding methods here to encapsulate other user-related business processes.

  /**
   * Syncs user data with an external system or updates specific fields via service.
   *
   * @param int $userId The ID of the user to sync.
   * @param array $newData The data to sync/update.
   * @return bool True if sync/update was successful, false otherwise.
   * @throws \Exception
   */
  // public function syncUserData(int $userId, array $newData): bool
  // {
  // Log::info("Attempting to sync data for user: {$userId}");
  // try {
  // Find the user
  // $user = User::findOrFail($userId);
  // Implement logic to update user data or interact with external API
  // $user->update($newData); // Example direct update
  // Call external system API...

  // Log success
  // Log::info("User data synced successfully for user: {$userId}");
  // return true; // Return success status
  // } catch (\Exception $e) {
  // Log error
  // Log::error("Failed to sync data for user {$userId}: " . $e->getMessage());
  // throw $e; // Re-throw
  // }
  // }

  /**
   * Assigns a specific role to a user via service logic.
   * Assumes a roles/permissions package is used.
   *
   * @param User $user The user instance.
   * @param string $roleName The name of the role to assign.
   * @return bool True if role was assigned, false otherwise.
   * @throws \Exception
   */
  // public function assignRoleToUser(User $user, string $roleName): bool
  // {
  // Log::info("Attempting to assign role '{$roleName}' to user: {$user->id}");
  // try {
  // Implement logic to assign role using your package's methods
  // $user->assignRole($roleName); // Example using Spatie roles

  // Log success
  // Log::info("Role '{$roleName}' assigned successfully to user: {$user->id}");
  // return true;
  // } catch (\Exception $e) {
  // Log error
  // Log::error("Failed to assign role '{$roleName}' to user {$user->id}: " . $e->getMessage());
  // throw $e;
  // }
  // }

  /**
   * Removes a specific role from a user via service logic.
   *
   * @param User $user The user instance.
   * @param string $roleName The name of the role to remove.
   * @return bool True if role was removed, false otherwise.
   * @throws \Exception
   */
  // public function removeRoleFromUser(User $user, string $roleName): bool
  // {
  // Log::info("Attempting to remove role '{$roleName}' from user: {$user->id}");
  // try {
  // Implement logic to remove role using your package's methods
  // $user->removeRole($roleName); // Example using Spatie roles

  // Log success
  // Log::info("Role '{$roleName}' removed successfully from user: {$user->id}");
  // return true;
  // } catch (\Exception $e) {
  // Log error
  // Log::error("Failed to remove role '{$roleName}' from user {$user->id}: " . $e->getMessage());
  // throw $e;
  // }
  // }

  /**
   * Changes the password for a user via service logic.
   * Encapsulates password hashing and saving.
   *
   * @param User $user The user instance.
   * @param string $newPassword The new plain text password.
   * @return bool True if password was updated, false otherwise.
   * @throws \Exception
   */
  // public function changePassword(User $user, string $newPassword): bool
  // {
  // Log::info("Attempting to change password for user: {$user->id}");
  // try {
  // Hash the new password
  // $hashedPassword = Hash::make($newPassword);
  // Update the user's password
  // $user->password = $hashedPassword;
  // $user->save();

  // Log success
  // Log::info("Password changed successfully for user: {$user->id}");
  // return true;
  // } catch (\Exception $e) {
  // Log error
  // Log::error("Failed to change password for user {$user->id}: " . $e->getMessage());
  // throw $e;
  // }
  // }

  /**
   * Activates a user account.
   *
   * @param User $user The user instance.
   * @return bool True if user was activated, false otherwise.
   * @throws \Exception
   */
  // public function activateUser(User $user): bool
  // {
  //     Log::info("Attempting to activate user: {$user->id}");
  //     try {
  //         // Assuming 'status' field on User model
  //         if ($user->status === 'active') {
  //              Log::warning("User {$user->id} is already active.");
  //              return false; // Or throw exception if activating an active user is an error
  //         }
  //         $updated = $user->update(['status' => 'active']);
  //         if ($updated) {
  //             Log::info("User activated successfully: {$user->id}");
  //         }
  //         return $updated;
  //     } catch (\Exception $e) {
  //         Log::error("Failed to activate user {$user->id}: " . $e->getMessage());
  //         throw $e;
  //     }
  // }

  /**
   * Deactivates a user account.
   *
   * @param User $user The user instance.
   * @return bool True if user was deactivated, false otherwise.
   * @throws \Exception
   */
  // public function deactivateUser(User $user): bool
  // {
  //     Log::info("Attempting to deactivate user: {$user->id}");
  //     try {
  //         // Assuming 'status' field on User model
  //         if ($user->status === 'inactive') {
  //              Log::warning("User {$user->id} is already inactive.");
  //              return false; // Or throw exception if deactivating an inactive user is an error
  //         }
  //          // Prevent deactivating currently logged in user if needed
  //          if ($user->id === Auth::id()) {
  //               Log::warning("Attempted to deactivate currently logged in user: {$user->id}");
  //               throw new Exception("Cannot deactivate your own account.");
  //          }
  //         $updated = $user->update(['status' => 'inactive']);
  //         if ($updated) {
  //             Log::info("User deactivated successfully: {$user->id}");
  //         }
  //         return $updated;
  //     } catch (\Exception $e) {
  //         Log::error("Failed to deactivate user {$user->id}: " . $e->getMessage());
  //         throw $e;
  //     }
  // }

}
