<?php

namespace App\Policies;

use App\Models\User; // Import the User model
use App\Models\Equipment; // Import the Equipment model
use Illuminate\Auth\Access\HandlesAuthorization; // Trait for authorization responses

class EquipmentPolicy
{
  use HandlesAuthorization;

  /**
   * Determine whether the user can view any equipment assets (e.g., on an inventory list page).
   * This policy check is typically used before fetching a collection of resources.
   */
  public function viewAny(User $user): bool
  {
    // Allow BPM staff, Admins, or users with a specific permission ('view_equipment') to view the equipment inventory.
    // Assuming user->hasAnyRole() and user->hasPermission() are available methods (e.g., via roles/permissions package).
    return $user->is_admin
      || $user->hasAnyRole(['bpm_staff']) // BPM staff can view inventory
      || $user->hasPermission('view_equipment'); // Users with specific permission can view
  }

  /**
   * Determine whether the user can view a specific equipment asset.
   * This policy check is typically used after fetching a single resource (e.g., on an equipment details page).
   */
  public function view(User $user, Equipment $equipment): bool
  {
    // Allow BPM staff, Admins, or users with a specific permission ('view_equipment') to view any equipment asset.
    // This policy check doesn't typically restrict based on who "owns" the equipment, as equipment are shared resources.
    return $user->is_admin
      || $user->hasAnyRole(['bpm_staff'])
      || $user->hasPermission('view_equipment');
  }

  /**
   * Determine whether the user can create new equipment assets (add to inventory).
   * This action is typically restricted to inventory managers like BPM staff or Admins.
   */
  public function create(User $user): bool
  {
    // Only BPM staff or Admins can create new equipment records.
    return $user->is_admin
      || $user->hasAnyRole(['bpm_staff', 'manage_equipment']); // 'manage_equipment' could be a more specific permission
  }

  /**
   * Determine whether the user can update a specific equipment asset's details (e.g., status, location, notes).
   * This action is typically restricted to inventory managers like BPM staff or Admins.
   */
  public function update(User $user, Equipment $equipment): bool
  {
    // Only BPM staff or Admins can update equipment records.
    return $user->is_admin
      || $user->hasAnyRole(['bpm_staff', 'manage_equipment']);
  }

  /**
   * Determine whether the user can delete a specific equipment asset from inventory.
   * This action is typically restricted to inventory managers like BPM staff or Admins, and might have additional conditions (e.g., equipment is not on loan).
   */
  public function delete(User $user, Equipment $equipment): bool
  {
    // Only BPM staff or Admins can delete equipment records.
    // Add condition: Equipment should not be currently on loan or have outstanding transactions.
    return ($user->is_admin || $user->hasAnyRole(['bpm_staff', 'manage_equipment']))
      && $equipment->status !== 'on_loan'; // Cannot delete if currently on loan
    // You might add checks for other statuses or related pending transactions
  }

  /**
   * Determine whether the user can restore a specific equipment asset (if using soft deletes).
   */
  public function restore(User $user, Equipment $equipment): bool
  {
    // Define restore logic if using soft deletes and this action is allowed.
    // Likely restricted to Admins or specific roles.
    return $user->is_admin || $user->hasAnyRole(['bpm_staff', 'manage_equipment']); // Example: allow BPM to restore
  }

  /**
   * Determine whether the user can permanently delete a specific equipment asset (if using soft deletes).
   */
  public function forceDelete(User $user, Equipment $equipment): bool
  {
    // Define force delete logic if using soft deletes and this action is allowed.
    // This is typically a highly restricted action.
    return $user->is_admin; // Example: only Admin can permanently delete
  }

  /**
   * Determine whether the user can issue a specific equipment asset as part of a loan transaction.
   * This action is performed by BPM staff when equipment is given to the applicant.
   * The policy checks if the user has the role and if the equipment is in a state to be issued.
   * Note: This policy checks the user's ability on the Equipment model. The calling code
   * (e.g., EquipmentChecklist component or LoanApplicationService) should also verify
   * that the *Loan Application* is in an approvable status (e.g., 'approved').
   * @param User $user The user performing the action.
   * @param Equipment $equipment The specific equipment asset being issued.
   * @return bool
   */
  public function issue(User $user, Equipment $equipment): bool
  {
    // Only BPM staff or Admins can issue equipment.
    // Equipment must be in 'available' status to be issued.
    return ($user->is_admin || $user->hasAnyRole(['bpm_staff']))
      && $equipment->status === 'available'; // Equipment must be available to be issued
  }

  /**
   * Determine whether the user can process the return of a specific equipment asset.
   * This action is performed by BPM staff when equipment is returned by the applicant.
   * The policy checks if the user has the role and if the equipment is in a state to be returned.
   * Note: This policy checks the user's ability on the Equipment model. The calling code
   * (e.g., EquipmentChecklist component or LoanApplicationService) should also verify
   * that the associated *Loan Transaction* is in an 'issued' status.
   * @param User $user The user performing the action.
   * @param Equipment $equipment The specific equipment asset being returned.
   * @return bool
   */
  public function processReturn(User $user, Equipment $equipment): bool
  {
    // Only BPM staff or Admins can process equipment returns.
    // Equipment must be in 'on_loan' status to be returned.
    return ($user->is_admin || $user->hasAnyRole(['bpm_staff']))
      && $equipment->status === 'on_loan'; // Equipment must be on loan to be returned
  }

  // Add other specific policy methods as needed for different actions related to equipment assets.
}
