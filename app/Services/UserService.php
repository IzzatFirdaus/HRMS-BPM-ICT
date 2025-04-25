<?php

namespace App\Services;

use App\Models\User; // Import the User model
use App\Models\Grade; // Import Grade model if filtering by grade
use App\Models\Department; // Import Department model if filtering by department
use Illuminate\Database\Eloquent\Collection; // Import Collection for return types
use Exception; // Import base Exception class
use Illuminate\Support\Facades\Log; // Import Log facade

/**
 * Optional service to encapsulate complex user-related business logic.
 * This can include retrieving users by roles/grades/departments,
 * syncing with external systems, managing user hierarchy, etc.
 */
class UserService
{
  // Inject any dependencies needed for user-related logic (e.g., repositories, external system clients)

  /**
   * Finds users who have a specific role.
   * Assumes a roles/permissions package like Spatie is used.
   *
   * @param string $roleName The name of the role (e.g., 'admin', 'bpm_staff', 'it_admin').
   * @return Collection<User> A collection of users with the specified role.
   */
  public function getUsersByRole(string $roleName): Collection
  {
    try {
      // Assuming the User model has a 'role' method or uses a trait for roles
      // Example using Spatie roles:
      return User::role($roleName)->get();
    } catch (Exception $e) {
      Log::error("Failed to get users by role '" . $roleName . "': " . $e->getMessage());
      // Decide how to handle errors, e.g., return empty collection or re-throw
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Finds users who have a grade level greater than or equal to a specified level.
   * Assumes User model has a 'grade' relationship and Grade model has a 'level' attribute.
   *
   * @param int $minGradeLevel The minimum grade level required.
   * @return Collection<User> A collection of users meeting the minimum grade level.
   */
  public function getUsersByMinGradeLevel(int $minGradeLevel): Collection
  {
    try {
      // Assuming User model has a 'grade' relationship and Grade model has a 'level' attribute
      return User::whereHas('grade', fn($query) => $query->where('level', '>=', $minGradeLevel))->get();
    } catch (Exception $e) {
      Log::error("Failed to get users by minimum grade level '" . $minGradeLevel . "': " . $e->getMessage());
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Finds users within a specific department or division.
   * Assumes User model has a 'department' relationship and Department model has an 'id' or 'name' attribute.
   *
   * @param int|string $departmentIdentifier The ID or name of the department.
   * @return Collection<User> A collection of users in the specified department.
   */
  public function getUsersByDepartment(int|string $departmentIdentifier): Collection
  {
    try {
      // Assuming User model has a 'department' relationship and Department model has an 'id' or 'name'
      $query = User::query();

      if (is_int($departmentIdentifier)) {
        $query->whereHas('department', fn($q) => $q->where('id', $departmentIdentifier));
      } else {
        $query->whereHas('department', fn($q) => $q->where('name', $departmentIdentifier));
      }

      return $query->get();
    } catch (Exception $e) {
      Log::error("Failed to get users by department '" . $departmentIdentifier . "': " . $e->getMessage());
      throw $e; // Re-throw the exception
    }
  }


  /**
   * Finds potential next approvers based on an application and the current stage.
   * This logic could be centralized here if complex and needed by multiple services.
   * (Note: This is similar to logic already implemented in ApprovalService for now).
   *
   * @param Model $approvable The application model (EmailApplication or LoanApplication).
   * @param string $currentStage The identifier for the current approval stage.
   * @return Collection<User> A collection of potential next approvers.
   */
  // public function findNextApprovers(Model $approvable, string $currentStage): Collection
  // {
  // Implement logic to find the next approvers based on approvable type, current stage,
  // applicant's department, grade hierarchy, etc.
  // Return a collection of User models.
  //     return collect(); // Placeholder
  // }


  // Add methods for syncing user data, managing roles/permissions via service, etc.
  // public function syncUserData(int $userId, array $newData): bool;
  // public function assignRoleToUser(User $user, string $roleName): bool;
}
