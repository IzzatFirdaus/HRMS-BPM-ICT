<?php

namespace App\Policies;

use App\Models\User; // Import the User model
use App\Models\LoanTransaction; // Import the LoanTransaction model
use Illuminate\Auth\Access\HandlesAuthorization; // Trait for authorization responses

class LoanTransactionPolicy
{
  use HandlesAuthorization;

  /**
   * Determine whether the user can view any loan transactions.
   * Used for list views (index).
   *
   * @param  \App\Models\User  $user
   * @return bool
   */
  public function viewAny(User $user): bool
  {
    // Allow users with 'bpm_staff' role or 'admin' role to view any transaction list.
    // Policy scopes can be used on the query in the controller's index method
    // if you need non-BPM/admin users to see only their related transactions.
    return $user->hasAnyRole(['bpm_staff', 'admin']); // Assumes Spatie HasRoles trait is used
    // OR using permissions: return $user->can('view_any_loan_transaction') || $user->is_admin;
  }

  /**
   * Determine whether the user can view the specified loan transaction.
   * Used for detail views (show).
   *
   * @param  \App\Models\User  $user
   * @param  \App\Models\LoanTransaction  $loanTransaction
   * @return bool
   */
  public function view(User $user, LoanTransaction $loanTransaction): bool
  {
    // Admins or BPM staff can view any transaction.
    // The applicant or responsible officer of the related application can view the transaction.
    // Any officer directly involved in the transaction (issuing, receiving, returning, accepting) can view it.
    // Assumes relationships like loanApplication, issuingOfficer, receivingOfficer, etc. are loaded or loadable
    // on the LoanTransaction model.
    return $user->is_admin
      || $user->hasAnyRole(['bpm_staff']) // BPM staff can view any transaction
      // Check if the user is related to the application the transaction belongs to
      || ($loanTransaction->loanApplication && $user->id === $loanTransaction->loanApplication->user_id) // Is the applicant of the related application
      || ($loanTransaction->loanApplication && $user->id === $loanTransaction->loanApplication->responsible_officer_id) // Is the responsible officer of the related application
      // Check if the user was any of the involved officers in the transaction
      || ($loanTransaction->issuingOfficer && $user->id === $loanTransaction->issuing_officer_id) // Was the issuing officer
      || ($loanTransaction->receivingOfficer && $user->id === $loanTransaction->receiving_officer_id) // Was the receiving officer
      || ($loanTransaction->returningOfficer && $user->id === $loanTransaction->returning_officer_id) // Was the returning officer
      || ($loanTransaction->returnAcceptingOfficer && $user->id === $loanTransaction->return_accepting_officer_id); // Was the return accepting officer
  }

  /**
   * Determine whether the user can create loan transactions.
   * (Loan transactions are typically created via the 'issue' action on Loan Applications, not directly).
   *
   * @param  \App\Models\User  $user
   * @return bool
   */
  public function create(User $user): bool
  {
    // Direct creation of LoanTransactions via a standard form is unlikely in this workflow.
    // The creation happens as part of the 'issue' action on a LoanApplication.
    // The authorization for that is handled in the LoanApplicationPolicy@issue.
    return false; // Disallow direct creation via resource controller method
  }

  /**
   * Determine whether the user can update the specified loan transaction.
   * (Loan transactions are primarily 'updated' via the 'processReturn' action).
   *
   * @param  \App\Models\User  $user
   * @param  \App\Models\LoanTransaction  $loanTransaction
   * @return bool
   */
  public function update(User $user, LoanTransaction $loanTransaction): bool
  {
    // Direct updates to transactions via a standard form are unlikely in this workflow,
    // except perhaps for admin edits.
    // The 'return processing' is handled by the processReturn policy action.
    // Allow admins to update any transaction.
    return $user->is_admin;
    // You might add specific permission/role checks here if BPM staff can edit transactions beyond return processing.
  }

  /**
   * Determine whether the user can delete the specified loan transaction.
   *
   * @param  \App\Models\User  $user
   * @param  \App\Models\LoanTransaction  $loanTransaction
   * @return bool
   */
  public function delete(User $user, LoanTransaction $loanTransaction): bool
  {
    // Typically, only admins can delete transactions, if at all.
    // BPM staff might have permission, but standard users likely won't.
    return $user->is_admin;
    // Or using permissions: return $user->can('delete_loan_transaction') || $user->is_admin;
  }

  /**
   * Determine whether the user can restore the specified loan transaction.
   *
   * @param  \App\Models\User  $user
   * @param  \App\Models\LoanTransaction  $loanTransaction
   * @return bool
   */
  public function restore(User $user, LoanTransaction $loanTransaction): bool
  {
    // Define restore logic if using soft deletes. Usually restricted to admins.
    return $user->is_admin;
  }

  /**
   * Determine whether the user can permanently delete the specified loan transaction.
   *
   * @param  \App\Models\User  $user
   * @param  \App\Models\LoanTransaction  $loanTransaction
   * @return bool
   */
  public function forceDelete(User $user, LoanTransaction $loanTransaction): bool
  {
    // Define force delete logic. Usually restricted to admins.
    return $user->is_admin;
  }

  /**
   * Determine whether the user can process the return of the specified loan transaction.
   * This aligns with the processReturn method in the controller.
   *
   * @param  \App\Models\User  $user
   * @param  \App\Models\LoanTransaction  $loanTransaction
   * @return bool
   */
  public function processReturn(User $user, LoanTransaction $loanTransaction): bool
  {
    // Only BPM staff or admins can process returns.
    // The transaction must be in a status ready for return processing (e.g., 'on_loan').
    // Assumes LoanTransaction model defines a constant for 'on_loan' status, e.g., LoanTransaction::STATUS_ON_LOAN
    // You will need to define this constant in your LoanTransaction model.
    return $user->hasAnyRole(['bpm_staff', 'admin']) // User must have 'bpm_staff' or 'admin' role
      && $loanTransaction->status === (LoanTransaction::STATUS_ON_LOAN ?? 'on_loan'); // Check status using constant (fallback to string if constant not defined yet)
  }

  // Add other specific policy methods as needed for different actions related to transactions.
  // For example, 'view_checklist', 'update_checklist' etc.
}
