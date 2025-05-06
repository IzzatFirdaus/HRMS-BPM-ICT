<?php

namespace App\Services;

use App\Models\Approval;
use App\Models\User;
use App\Models\EmailApplication;
use App\Models\LoanApplication;
use App\Services\EmailProvisioningService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Exception;

class ApprovalService
{
  // Declaring property as nullable is good practice
  protected ?EmailProvisioningService $emailProvisioningService;

  /**
   * Inject the EmailProvisioningService dependency.
   *
   * @param  \App\Services\EmailProvisioningService|null $emailProvisioningService The email provisioning service instance (nullable).
   */
  public function __construct(?EmailProvisioningService $emailProvisioningService = null) // FIX: Added nullable type hint '?'
  {
    $this->emailProvisioningService = $emailProvisioningService;
  }

  /**
   * Record an approval decision.
   *
   * @param  Approval  $approval The approval record to process.
   * @param  User  $approver The user making the approval decision.
   * @param  string|null  $comments Optional comments from the approver.
   * @return Approval The updated approval record.
   * @throws Exception If the approval is not pending, not assigned, or an error occurs.
   */
  public function recordApprovalDecision(Approval $approval, User $approver, ?string $comments = null): Approval
  {
    // Ensure policy check is done in the controller before calling this method (e.g., can('update', $approval))
    // Additional check here for safety/robustness: ensure the approval is still pending and assigned to the user
    if ($approval->status !== Approval::STATUS_PENDING || $approval->officer_id !== $approver->id) {
      Log::warning("Attempted to record approval on non-pending or unassigned Approval ID: " . $approval->id . " by user: " . $approver->id);
      throw new Exception("This approval task is not pending or not assigned to you.");
    }


    DB::beginTransaction(); // Start database transaction

    try {
      // 1. Update the status of the specific Approval record being processed
      $approval->status = Approval::STATUS_APPROVED; // Mark this specific approval step as approved
      $approval->approval_timestamp = now(); // Set the timestamp
      $approval->comments = $comments; // Store comments on the approval record
      $approval->save();

      // 2. Update the status of the related approvable model based on the approval decision and workflow stage
      // This method contains the core workflow transition logic.
      $this->updateApprovableStatus($approval->approvable, Approval::STATUS_APPROVED, $approver);

      DB::commit(); // Commit the transaction

      // 3. Trigger notifications or next steps based on the *new* status of the approvable model
      // This includes finding the next approver, creating the next Approval record, or triggering final actions.
      // Pass the deciding officer as they might be needed for triggering subsequent steps (e.g., process)
      $this->triggerWorkflowSteps($approval->approvable, $approval, $approver);


      Log::info("Approval decision recorded for Approval ID: " . $approval->id . ". Decision: Approved by officer: " . $approver->id . ". Approvable status updated to: " . ($approval->approvable->status ?? 'N/A'));

      return $approval; // Return the updated approval record (with relationships loaded if needed)
    } catch (Exception $e) {
      // Check if transaction is active before rolling back
      if (DB::transactionLevel() > 0) {
        DB::rollBack(); // Rollback the transaction on error
      }
      Log::error("Failed to record approval decision for Approval ID: " . ($approval->id ?? 'N/A') . ". Error: " . $e->getMessage(), ['exception' => $e]);
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Record a rejection decision.
   *
   * @param  Approval  $approval The specific approval record being processed (must be in 'pending' status).
   * @param  User  $rejector The user making the rejection decision (must be the assigned officer).
   * @param  string|null $comments Optional comments from the rejector.
   * @return Approval The updated approval record.
   * @throws Exception If the approval is not pending or unassigned, or an error occurs.
   */
  public function recordRejectionDecision(Approval $approval, User $rejector, ?string $comments = null): Approval
  {
    // Ensure policy check is done in the controller before calling this method (e.g., can('update', $approval))
    // Additional check here for safety/robustness: ensure the approval is still pending and assigned to the user
    if ($approval->status !== Approval::STATUS_PENDING || $approval->officer_id !== $rejector->id) {
      Log::warning("Attempted to record rejection on non-pending or unassigned Approval ID: " . $approval->id . " by user: " . $rejector->id);
      throw new Exception("This approval task is not pending or not assigned to you.");
    }


    DB::beginTransaction(); // Start database transaction

    try {
      // 1. Update the status of the specific Approval record being processed
      $approval->status = Approval::STATUS_REJECTED; // Mark this specific approval step as rejected
      $approval->approval_timestamp = now(); // Set the timestamp
      $approval->comments = $comments; // Store comments on the approval record
      $approval->save();

      // 2. Update the status of the related approvable model to 'rejected'
      // Rejection at any stage typically marks the entire application as rejected and stops the workflow.
      $this->updateApprovableStatus($approval->approvable, Approval::STATUS_REJECTED, $rejector);

      DB::commit(); // Commit the transaction

      // 3. Trigger notifications or next steps based on the *new* status ('rejected')
      // This typically involves notifying the applicant.
      $this->triggerWorkflowSteps($approval->approvable, $approval, $rejector);

      Log::info("Approval decision recorded for Approval ID: " . $approval->id . ". Decision: Rejected by officer: " . $rejector->id . ". Approvable status set to: " . ($approval->approvable->status ?? 'N/A'));

      return $approval; // Return the updated approval record
    } catch (Exception $e) {
      // Check if transaction is active before rolling back
      if (DB::transactionLevel() > 0) {
        DB::rollBack(); // Rollback the transaction on error
      }
      Log::error("Failed to record rejection decision for Approval ID: " . ($approval->id ?? 'N/A') . ". Error: " . $e->getMessage(), ['exception' => $e]);
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Update the status of the underlying model based on a decision.
   *
   * @param  Model  $approvable The model being approved/rejected (EmailApplication or LoanApplication).
   * @param  string  $decision 'approved' or 'rejected' status of the current approval step.
   * @param  User  $decidingOfficer The user who made the decision.
   * @return void
   */
  protected function updateApprovableStatus(Model $approvable, string $decision, User $decidingOfficer): void
  {
    // --- Implement your specific workflow state transitions here ---
    // This logic determines the NEXT status of the approvable based on its CURRENT status and the decision made.

    if ($decision === Approval::STATUS_REJECTED) {
      // If *any* approval step is rejected, the entire application is rejected.
      // Only update status if it's not already a final state (like completed, provisioned_failed, etc.)
      // Assuming 'rejected' is always a valid final state from any pending state.
      // Use constants from the specific model (EmailApplication::STATUS_REJECTED or LoanApplication::STATUS_REJECTED)
      if ($approvable instanceof EmailApplication) {
        if ($approvable->status !== EmailApplication::STATUS_REJECTED) {
          $approvable->status = EmailApplication::STATUS_REJECTED;
          $approvable->rejection_reason = $decidingOfficer->name
            . ($approvable->approvals()->latest()->first()?->comments ? ': ' . $approvable->approvals()->latest()->first()->comments : '');
          $approvable->save();
          Log::info("EmailApplication ID: " . $approvable->id . " set to rejected.");
        } else {
          Log::info("EmailApplication ID: " . $approvable->id . " was already 'rejected'. Status not changed.");
        }
      } elseif ($approvable instanceof LoanApplication) {
        if ($approvable->status !== LoanApplication::STATUS_REJECTED) {
          $approvable->status = LoanApplication::STATUS_REJECTED;
          $approvable->rejection_reason = $decidingOfficer->name
            . ($approvable->approvals()->latest()->first()?->comments ? ': ' . $approvable->approvals()->latest()->first()->comments : '');
          $approvable->save();
          Log::info("LoanApplication ID: " . $approvable->id . " set to rejected.");
        } else {
          Log::info("LoanApplication ID: " . $approvable->id . " was already 'rejected'. Status not changed.");
        }
      }
      // Add elseif blocks for other approvable types if needed

    } elseif ($decision === Approval::STATUS_APPROVED) {
      // If approved, determine the next status based on the current status and approvable type.
      if ($approvable instanceof EmailApplication) {
        match ($approvable->status) {
          EmailApplication::STATUS_PENDING_SUPPORT => $approvable->status = EmailApplication::STATUS_PENDING_ADMIN,
          EmailApplication::STATUS_PENDING_ADMIN  => $approvable->status = EmailApplication::STATUS_APPROVED, // Internally approved, ready for processing
          default => Log::warning("Approved EmailApplication ID: " . $approvable->id . " in unexpected status for transition: " . $approvable->status),
        };
      } elseif ($approvable instanceof LoanApplication) {
        if ($approvable->status === LoanApplication::STATUS_PENDING_SUPPORT) {
          $approvable->status = LoanApplication::STATUS_APPROVED; // Internally approved, ready for issuance by BPM
        } else {
          Log::warning("Approved LoanApplication ID: " . $approvable->id . " in unexpected status for transition: " . $approvable->status);
        }
      }
      // Add elseif blocks for other approvable types if needed

      // Save the approvable model with the updated status if it was changed
      if ($approvable->isDirty('status')) {
        $approvable->save();
        Log::info(get_class($approvable) . " ID: " . $approvable->id . " moved to " . $approvable->status);
      } else {
        Log::info(get_class($approvable) . " ID: " . $approvable->id . " status did not change in updateApprovableStatus.");
      }
    }

    // --- End of workflow status transition logic ---
  }

  /**
   * Trigger next workflow steps or notifications after a decision.
   *
   * @param  Model  $approvable The model that was updated (EmailApplication or LoanApplication).
   * @param  Approval  $processedApproval The specific approval record that was processed.
   * @param  User  $decidingOfficer The user who made the decision for the processed approval.
   * @return void
   */
  protected function triggerWorkflowSteps(Model $approvable, Approval $processedApproval, User $decidingOfficer): void
  {
    // --- Implement actions based on the approvable's NEW status ---

    // Action on Rejection: Notify the applicant
    if (($approvable instanceof EmailApplication && $approvable->status === EmailApplication::STATUS_REJECTED) ||
      ($approvable instanceof LoanApplication && $approvable->status === LoanApplication::STATUS_REJECTED)
    ) {
      // Ensure the approvable has a 'user' relationship to the applicant
      if ($approvable->user) {
        // TODO: Implement specific EmailApplicationRejectedNotification and LoanApplicationRejectedNotification
        // Example using Notification facade:
        if ($approvable instanceof EmailApplication) {
          // Notification::send($approvable->user, new EmailApplicationRejectedNotification($approvable, $processedApproval)); // Create this notification
        } elseif ($approvable instanceof LoanApplication) {
          // Notification::send($approvable->user, new LoanApplicationRejectedNotification($approvable, $processedApproval)); // Create this notification
        }
        Log::info("Notified user " . $approvable->user_id . " about rejected " . get_class($approvable) . " ID: " . $approvable->id);
      } else {
        Log::warning("Cannot notify user for rejected " . get_class($approvable) . " ID: " . $approvable->id . ". User relationship missing.");
      }
      return; // Stop further workflow steps on rejection
    }

    // Action on Approval leading to next pending stage (Email Application only)
    if ($approvable instanceof EmailApplication) {
      if ($approvable->status === EmailApplication::STATUS_PENDING_ADMIN) {
        // Application moved to IT Admin review. Find IT Admins and create the next Approval record.
        $nextStage = Approval::STAGE_IT_ADMIN; // Use constants
        $nextApproverRole = 'it_admin'; // Role for the next approver(s)

        // Find users who should approve the next stage (adjust query based on your role/permission logic)
        $nextApprovers = User::whereHas('roles', fn($q) => $q->where('name', $nextApproverRole))->get(); // Example using Spatie roles
        // Or find based on grade, department, etc.

        if ($nextApprovers->isEmpty()) {
          Log::critical("No users found with role '" . $nextApproverRole . "' to assign next approval for EmailApplication ID: " . $approvable->id);
          // TODO: Consider updating application status to indicate workflow stuck and notifying an admin
        } else {
          // Create the next Approval record(s) assigned to the next approver(s)
          foreach ($nextApprovers as $nextApprover) {
            $this->createNextApproval($approvable, $nextApprover, $nextStage); // Create one approval record per officer
          }
          // TODO: Notify the next approver(s) that they have a pending approval task
          // Notification::send($nextApprovers, new NewEmailApplicationPendingAdminNotification($approvable, $processedApproval)); // Create this notification
          Log::info("Created next approval task(s) for EmailApplication ID: " . $approvable->id . " and notified IT Admins.");
        }
      } elseif ($approvable->status === EmailApplication::STATUS_APPROVED && $this->emailProvisioningService) {
        // Email Application is fully internally approved and ready for processing/provisioning by IT Admin.
        // The 'process' method in EmailApplicationService handles this.
        // This trigger step simply logs that it's ready and should be picked up by IT Admin or a scheduled job.
        // Or, if processing is *automatically* triggered here, call the service method directly.
        // Based on EmailAccountController, IT Admin manually triggers 'process'.
        // So, the trigger here is mainly about reaching this status and potentially notifying IT Admin.

        Log::info("Email Application ID: " . $approvable->id . " reached APPROVED status, ready for IT Admin processing.");
        // TODO: Notify IT Admin team that an application is ready for processing (if not already done by the next pending approval notification)
        // This notification might be different from the initial approval task notification.
        // $this->emailProvisioningService->notifyAdminsApplicationReadyForProcessing($approvable); // Example method in EmailProvisioningService
      }
      // Add cases for other potential intermediate statuses if your workflow has them
    }

    // Action on Approval leading to final stage (Loan Application only)
    if ($approvable instanceof LoanApplication) {
      if ($approvable->status === LoanApplication::STATUS_APPROVED) {
        // Loan Application is fully internally approved and ready for BPM issuance.
        // Notify BPM staff that a new loan application is approved and ready for issuance.
        $nextStage = Approval::STAGE_BPM_ISSUE; // Use constants
        $bpmStaffRole = 'bpm_staff'; // Role for BPM staff

        // Find BPM staff users (adjust query based on your role/permission logic)
        $bpmStaff = User::whereHas('roles', fn($q) => $q->where('name', $bpmStaffRole))->get(); // Example using Spatie roles

        if ($bpmStaff->isEmpty()) {
          Log::warning("No users found with role '" . $bpmStaffRole . "' to assign next approval/notify about approved LoanApplication ID: " . $approvable->id);
          // TODO: Consider notifying an admin
        } else {
          // Create the next Approval record(s) assigned to the BPM staff (if you track issuance as an approval step)
          // If BPM simply *acts* on the approved status without a formal 'Approval' task, you only need the notification.
          // Assuming a formal approval step for issuance confirmation:
          foreach ($bpmStaff as $staff) {
            $this->createNextApproval($approvable, $staff, $nextStage); // Create one approval record per staff member
          }
          // TODO: Implement NewLoanApplicationApprovedBPMNotification
          // Notification::send($bpmStaff, new NewLoanApplicationApprovedBPMNotification($approvable, $processedApproval)); // Create this notification
          Log::info("Notified BPM staff about approved LoanApplication ID: " . $approvable->id . ".");
        }
        // The actual issuance is triggered separately by BPM staff via the UI (EquipmentChecklist component or LoanTransactionController).
      }
      // Add cases for other potential intermediate statuses if your workflow has them
    }

    // Add other status-based triggers here if needed (e.g., notify applicant when status becomes 'issued' for loan)
    // These notifications could be sent when LoanTransaction status is updated in LoanApplicationService.

    // Optional: Send a generic notification about the decision being recorded to the officer who made the decision
    // $processedApproval->officer->notify(new ApprovalDecisionRecorded($processedApproval)); // Create this notification
  }


  /**
   * Helper method to create the next approval record in a multi-stage workflow.
   *
   * @param  Model  $approvable The approvable model (EmailApplication or LoanApplication).
   * @param  User  $nextOfficer The user who will be assigned the next approval task.
   * @param  string  $stage The identifier for the next approval stage (e.g., 'admin_review', 'bpm_confirmation').
   * @return Approval The newly created approval record.
   * @throws \Exception If the approval record fails to save.
   */
  protected function createNextApproval(Model $approvable, User $nextOfficer, string $stage): Approval
  {
    // Create the new pending approval record assigned to the next officer
    $nextApproval = new Approval([
      'officer_id' => $nextOfficer->id, // Assign to the next officer
      'status' => Approval::STATUS_PENDING, // Set status to pending using constant
      'stage' => $stage, // Identify the stage (e.g., 'admin_review')
      // Add other relevant data like due date if applicable
    ]);

    // Associate the approval polymorphically with the approvable model
    // Use the relationship to ensure it's linked correctly
    $approvable->approvals()->save($nextApproval);


    Log::info("Created next approval record (Stage: {$stage}) for " . get_class($approvable) . " ID: " . ($approvable->id ?? 'N/A') . " assigned to officer: " . ($nextOfficer->id ?? 'N/A'));

    // TODO: Implement notification to the next officer that they have a pending approval task
    // Notification::send($nextOfficer, new YouHaveAPendingApprovalNotification($nextApproval)); // Create this notification

    return $nextApproval;
  }


  // You can add other methods related to approvals here, e.g.,
  // public function getPendingApprovalsForUser(User $user): Collection; // This logic is handled by ApprovalDashboard Livewire component directly querying Approval model
  // public function getApprovalHistoryForApprovable(Model $approvable): Collection; // Method to retrieve all approval records for an application
}
