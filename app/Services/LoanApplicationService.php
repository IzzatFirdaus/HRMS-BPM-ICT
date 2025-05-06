<?php

namespace App\Services;

use App\Models\LoanApplication;
use App\Models\LoanTransaction;
use App\Models\Equipment;
use App\Models\User;
use App\Models\Approval;
use App\Services\ApprovalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Notification; // Import Notification facade

// Assuming these Notification classes exist
// use App\Notifications\EquipmentIssuedNotification;
// use App\Notifications\EquipmentReturnedNotification;


class LoanApplicationService
{
  protected ApprovalService $approvalService;

  public function __construct(ApprovalService $approvalService)
  {
    $this->approvalService = $approvalService;
  }

  /**
   * Creates a new draft loan application.
   *
   * @param  array  $validatedData
   * @param  User   $applicant
   * @return LoanApplication
   * @throws Exception
   */
  public function createApplication(array $validatedData, User $applicant): LoanApplication // Add return type hint
  {
    Log::debug('Creating new loan application draft', ['user_id' => $applicant->id ?? 'N/A']);

    DB::beginTransaction();
    try {
      $app = new LoanApplication();
      $app->user_id = $applicant->id;
      $app->fill($validatedData);
      $app->status = LoanApplication::STATUS_DRAFT; // Use constant
      $app->save();

      Log::info('Draft loan application created', ['application_id' => $app->id]);
      DB::commit();

      return $app->fresh(); // Return fresh instance
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Failed to create draft loan application', ['user_id' => $applicant->id ?? 'N/A', 'error' => $e->getMessage()]);
      throw new Exception('Gagal mencipta permohonan pinjaman peralatan: ' . $e->getMessage()); // Malay message
    }
  }

  /**
   * Update an existing draft loan application.
   *
   * @param  LoanApplication  $application
   * @param  array             $validatedData
   * @param  User              $user
   * @return LoanApplication
   * @throws Exception
   */
  public function updateApplication(LoanApplication $application, array $validatedData, User $user): LoanApplication // Add return type hint
  {
    Log::debug('Updating loan application draft', ['application_id' => $application->id ?? 'N/A']);

    if (!$application->isDraft() || $application->user_id !== $user->id) { // Use isDraft() helper
      throw new Exception('Permohonan tidak sah untuk dikemaskini.'); // Malay message
    }

    DB::beginTransaction();
    try {
      $application->fill($validatedData);
      $application->save();

      Log::info('Draft loan application updated', ['application_id' => $application->id ?? 'N/A']);
      DB::commit();

      return $application->fresh(); // Return fresh instance
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Failed to update draft loan application', ['application_id' => $application->id ?? 'N/A', 'error' => $e->getMessage()]);
      throw new Exception('Gagal mengemaskini permohonan pinjaman peralatan: ' . $e->getMessage()); // Malay message
    }
  }

  /**
   * Submits a draft loan application for approval.
   *
   * @param  LoanApplication  $application
   * @param  User              $applicant
   * @return LoanApplication
   * @throws Exception
   */
  public function submitApplication(LoanApplication $application, User $applicant): LoanApplication // Add return type hint
  {
    Log::debug('Submitting loan application', ['application_id' => $application->id ?? 'N/A']);

    if (!$application->isDraft() || $application->user_id !== $applicant->id) { // Use isDraft() helper
      throw new Exception('Permohonan tidak sah untuk dihantar.'); // Malay message
    }

    DB::beginTransaction();
    try {
      $application->status = LoanApplication::STATUS_PENDING_SUPPORT; // Use constant
      $application->submission_timestamp = now();
      $application->save();

      // Logic to assign supporting officer and create initial approval step
      // This logic should likely live in or be coordinated with an ApprovalService
      // $this->approvalService->startApprovalProcess($application, Approval::STAGE_SUPPORT_REVIEW); // Example call

      // Example: Find first user with 'support officer' role and notify
      $supportOfficer = User::role('support officer')->first(); // Assuming Spatie Roles

      if ($supportOfficer) {
        // Assuming a notification exists
        // Notification::send($supportOfficer, new NewLoanApplicationForApproval($application));
        Log::info('Notified support officer about new loan application.', [
          'application_id' => $application->id ?? 'N/A',
          'officer_id'     => $supportOfficer->id
        ]);
      } else {
        Log::warning('No support officer found to notify for loan application ID: ' . ($application->id ?? 'N/A'));
        // Optionally mark application as stuck or notify admin
      }


      Log::info('Loan application submitted', ['application_id' => $application->id ?? 'N/A']);
      DB::commit();

      return $application->fresh();
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Submission of loan application failed', ['application_id' => $application->id ?? 'N/A', 'error' => $e->getMessage()]);
      throw new Exception('Gagal menghantar permohonan pinjaman peralatan: ' . $e->getMessage()); // Malay message
    }
  }

  /**
   * Deletes a draft loan application.
   *
   * @param  LoanApplication  $application
   * @param  User              $user
   * @return bool
   * @throws Exception
   */
  public function deleteApplication(LoanApplication $application, User $user): bool // Add return type hint
  {
    Log::debug('Deleting loan application draft', ['application_id' => $application->id ?? 'N/A']);

    if (!$application->isDraft() || $application->user_id !== $user->id) { // Use isDraft() helper
      throw new Exception('Permohonan tidak sah untuk dibuang.'); // Malay message
    }

    DB::beginTransaction();
    try {
      $deleted = $application->delete();
      DB::commit();
      Log::info('Draft loan application deleted', ['application_id' => $application->id ?? 'N/A']);
      return $deleted;
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Deletion of loan application failed', ['application_id' => $application->id ?? 'N/A', 'error' => $e->getMessage()]);
      throw new Exception('Gagal membuang permohonan pinjaman peralatan: ' . $e->getMessage()); // Malay message
    }
  }

  /**
   * Handles approval decisions for a loan application.
   * This method is likely called by an ApprovalService.
   *
   * @param  LoanApplication  $application The application being acted upon.
   * @param  string           $decision    'approved' or 'rejected'.
   * @param  User             $officer     The officer making the decision.
   * @param  string|null      $comments    Optional comments.
   * @param  string           $stage       The approval stage (e.g., 'support', 'head').
   * @return LoanApplication  The updated application.
   * @throws Exception        If the decision is invalid or application status is wrong.
   */
  public function handleApprovalDecision(
    LoanApplication $application,
    string $decision,
    User $officer,
    ?string $comments,
    string $stage
  ): LoanApplication // Add return type hint
  {
    Log::debug('Handling approval decision for loan application ID: ' . ($application->id ?? 'N/A'), [
      'decision' => $decision,
      'officer_id' => $officer->id ?? 'N/A',
      'stage' => $stage
    ]);
    // Basic validation for decision
    if (!in_array($decision, [Approval::STATUS_APPROVED, Approval::STATUS_REJECTED])) { // Use Approval constants
      throw new Exception('Keputusan kelulusan tidak sah.'); // Malay message
    }

    // Ensure the application is in a pending status relevant to the stage
    // Example: if stage is 'support', status should be 'pending_support'
    // This logic might be better handled within an ApprovalService
    // if ($stage === Approval::STAGE_SUPPORT_REVIEW && $application->status !== LoanApplication::STATUS_PENDING_SUPPORT) {
    //     throw new Exception('Permohonan tidak dalam status menunggu sokongan.');
    // }
    // if ($stage === Approval::STAGE_HEAD_REVIEW && $application->status !== LoanApplication::STATUS_PENDING_HEAD) {
    //     throw new Exception('Permohonan tidak dalam status menunggu kelulusan ketua.');
    // }


    DB::beginTransaction();
    try {
      // Record the approval/rejection transaction
      // This should likely create an Approval record, not update the application directly yet
      // $approval = $this->approvalService->recordDecision($application, $decision, $officer, $comments, $stage); // Example call to ApprovalService

      // Update application status based on decision and stage
      // This logic can be complex depending on your workflow (sequential/parallel approvals)
      if ($decision === Approval::STATUS_REJECTED) { // Use constant
        $application->status = LoanApplication::STATUS_REJECTED; // Use constant
        $application->rejection_reason = $comments ?? 'Ditolak oleh pegawai ' . ($officer->name ?? 'Tidak Dikenali'); // Store rejection reason
        $application->save();
        Log::info('Loan application rejected.', ['application_id' => $application->id ?? 'N/A', 'officer_id' => $officer->id ?? 'N/A', 'stage' => $stage]);
      } elseif ($decision === Approval::STATUS_APPROVED) { // Use constant
        // If it's the final approval stage (e.g., 'head'), set status to APPROVED
        // If it's an intermediate stage, set status to the next pending stage
        // This requires workflow logic here or in ApprovalService
        // Example simplified logic: if this was the 'support' stage approval:
        // $application->status = LoanApplication::STATUS_PENDING_HEAD; // Move to next stage
        // Or if this was the final 'head' stage approval:
        $application->status = LoanApplication::STATUS_APPROVED; // Set to final APPROVED status
        $application->save();
        Log::info('Loan application approved.', ['application_id' => $application->id ?? 'N/A', 'officer_id' => $officer->id ?? 'N/A', 'stage' => $stage]);

        // If fully approved, trigger IT Admin processing / notification
        // This step might also involve creating tasks for the IT team
        if ($application->status === LoanApplication::STATUS_APPROVED) {
          // Notify IT Admin team that an application is ready for issuance
          $itAdmins = User::role('IT Admin')->get(); // Example: Get all IT Admins
          if ($itAdmins->isNotEmpty()) {
            // Assuming a notification exists
            // Notification::send($itAdmins, new LoanApplicationApprovedForProcessing($application));
            Log::info('Notified IT Admins about approved loan application.', ['application_id' => $application->id ?? 'N/A']);
          } else {
            Log::warning('No IT Admins found to notify for approved loan application ID: ' . ($application->id ?? 'N/A'));
          }
        }
      }

      // Save changes to the application (status, rejection reason)
      $application->save();

      // Save the Approval record (if using a dedicated Approval model/service)
      // $approval->update(['status' => $decision, 'comments' => $comments, 'approval_timestamp' => now()]);

      DB::commit();

      // Trigger notification to applicant about the decision
      // Notification::send($application->user, new LoanApplicationDecision($application, $decision)); // Assuming a notification exists

      return $application->fresh(); // Return updated application

    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Failed to process approval decision for loan application ID ' . ($application->id ?? 'N/A') . '.', ['error' => $e->getMessage()]);
      throw new Exception('Gagal memproses keputusan kelulusan: ' . $e->getMessage()); // Malay message
    }
  }


  /**
   * Handles the issuance of a specific equipment item for a loan application.
   *
   * @param  \App\Models\LoanApplication  $application        The parent loan application.
   * @param  \App\Models\Equipment        $equipmentToIssue   The equipment being issued.
   * @param  \App\Models\User|null        $receivingOfficer   The officer receiving on behalf of applicant (nullable).
   * @param  \App\Models\User             $issuingOfficer     The officer performing the issuance.
   * @param  array                        $issueDetails       Optional details (accessories checklist, notes).
   * @return \App\Models\LoanTransaction                      The created loan transaction record.
   * @throws \Exception                                     If issuance fails or equipment is unavailable.
   */
  public function issueEquipment(
    LoanApplication $application,
    Equipment $equipmentToIssue,
    ?User $receivingOfficer, // Make nullable explicit if it can be null
    User $issuingOfficer,
    array $issueDetails = []
  ): LoanTransaction // Add return type hint
  {
    Log::debug('Attempting to issue equipment ID ' . ($equipmentToIssue->id ?? 'N/A') . ' for application ID ' . ($application->id ?? 'N/A'), [
      'issuing_officer_id'  => $issuingOfficer->id ?? 'N/A',
      'receiving_officer_id' => $receivingOfficer->id ?? null,
    ]);

    // Basic check: Is the equipment available?
    if (!($equipmentToIssue->availability_status === Equipment::AVAILABILITY_AVAILABLE)) { // Use constant
      Log::warning('Attempted to issue unavailable equipment.', [
        'equipment_id' => $equipmentToIssue->id ?? 'N/A',
        'status'       => $equipmentToIssue->availability_status ?? 'N/A',
        'application_id' => $application->id ?? 'N/A',
      ]);
      throw new Exception('Peralatan dengan tag aset ' . ($equipmentToIssue->asset_tag ?? 'N/A') . ' tidak tersedia untuk dikeluarkan.'); // Malay message
    }

    DB::beginTransaction();
    try {
      // Create the LoanTransaction record
      $transaction = new LoanTransaction();
      $transaction->loan_application_id            = $application->id;
      $transaction->equipment_id                   = $equipmentToIssue->id;
      $transaction->issuing_officer_id             = $issuingOfficer->id;
      $transaction->receiving_officer_id           = $receivingOfficer->id ?? null; // Use null coalescing operator
      $transaction->accessories_checklist_on_issue = $issueDetails['accessories_checklist_on_issue'] ?? null; // Check for key existence
      $transaction->issue_timestamp                = now();
      $transaction->status                         = LoanTransaction::STATUS_ISSUED; // Use constant
      $transaction->save();

      // Update equipment status
      $equipmentToIssue->availability_status = Equipment::AVAILABILITY_ON_LOAN; // Use constant
      $equipmentToIssue->save();

      // Update related LoanApplicationItem's quantity_issued
      // Find the specific item for this equipment type in the application
      // Ensure items relationship is loaded on application or load here
      $application->load('items');
      $applicationItem = $application->items
        ->where('equipment_type_id', $equipmentToIssue->equipment_type_id) // Assumes equipment_type_id exists and matches
        ->first(); // Get the first matching item (assuming one item per type in application)

      if ($applicationItem) {
        $applicationItem->quantity_issued++;
        $applicationItem->save();
        Log::debug('Updated quantity_issued for application item.', [
          'item_id' => $applicationItem->id,
          'new_quantity_issued' => $applicationItem->quantity_issued,
        ]);
      } else {
        Log::warning('LoanApplicationItem not found for equipment type during issuance.', [
          'application_id' => $application->id ?? 'N/A',
          'equipment_id'   => $equipmentToIssue->id ?? 'N/A',
          'equipment_type_id' => $equipmentToIssue->equipment_type_id ?? 'N/A',
        ]);
        // Decide if this should throw an error or just log a warning.
        // For now, just log and continue.
      }


      // Update application status (partial vs full issuance) based on total issued vs approved quantity across all items
      // $application->load('items'); // Ensure items are loaded to sum quantities - already done above
      $totalApproved = $application->items->sum('quantity_approved');
      $totalIssued   = $application->items->sum('quantity_issued'); // Sum issued quantity across all items

      // $totalIssued   = $application->transactions() // Alternative: count issued transactions directly
      //   ->where('status', LoanTransaction::STATUS_ISSUED)
      //   ->count();

      if ($totalIssued > 0 && $totalIssued < $totalApproved) {
        $application->status = LoanApplication::STATUS_PARTIALLY_ISSUED; // Use constant
      } elseif ($totalIssued >= $totalApproved && $totalApproved > 0) { // Check totalApproved > 0 to avoid setting ISSUED for 0 approved items
        $application->status = LoanApplication::STATUS_ISSUED; // Use constant
      }
      // If $totalIssued is 0, status remains as APPROVED

      $application->save();


      DB::commit();

      Log::info('Equipment issuance completed.', [
        'transaction_id' => $transaction->id ?? 'N/A',
        'application_id' => $application->id ?? 'N/A',
        'equipment_id'   => $equipmentToIssue->id ?? 'N/A',
      ]);

      // Optionally send notification to applicant about issuance
      // Notification::send($application->user, new EquipmentIssuedNotification($transaction)); // Assumes notification exists

      return $transaction->fresh(); // Return the fresh transaction model

    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Failed to issue equipment.', [
        'application_id' => $application->id ?? 'N/A',
        'equipment_id'   => $equipmentToIssue->id ?? 'N/A',
        'error'          => $e->getMessage()
      ]);
      throw new Exception('Gagal memproses pengeluaran peralatan: ' . $e->getMessage()); // Malay message
    }
  }

  /**
   * Handles the return process for an issued equipment transaction.
   *
   * @param  \App\Models\LoanTransaction  $transaction           The loan transaction being returned.
   * @param  array                        $returnDetails         Details about the return (condition, notes, checklist, returning officer ID).
   * @param  \App\Models\User             $returnAcceptingOfficer The officer accepting the return (IT Admin).
   * @return \App\Models\LoanTransaction                       The updated loan transaction record.
   * @throws \Exception                                      If return processing fails or transaction status is invalid.
   */
  public function handleReturn(LoanTransaction $transaction, array $returnDetails, User $returnAcceptingOfficer): LoanTransaction
  {
    Log::debug('Attempting to process return for transaction ID: ' . ($transaction->id ?? 'N/A'), [
      'accepting_officer_id' => $returnAcceptingOfficer->id ?? 'N/A',
      'current_status'       => $transaction->status ?? 'N/A',
    ]);

    // Ensure the transaction is in a state that can be returned
    // Use isIssued() helper method or check against relevant constants
    if (!$transaction->isIssued() && !$transaction->isUnderMaintenanceOnLoan()) { // Example check using helper methods/constants
      Log::warning('Attempted to return transaction not in valid return status.', [
        'transaction_id' => $transaction->id ?? 'N/A',
        'status' => $transaction->status ?? 'N/A',
      ]);
      throw new Exception('Transaksi tidak dalam status yang boleh dipulangkan.'); // Malay message
    }

    // Eager load equipment relationship if it's not already loaded, needed for status update
    $transaction->load('equipment');
    $equipment = $transaction->equipment;

    // Critical check: Ensure equipment relationship exists
    if (!$equipment) {
      Log::critical('Equipment relationship missing for transaction ID: ' . ($transaction->id ?? 'N/A') . ' during return process.');
      throw new Exception('Ralat sistem: Peralatan tidak ditemui untuk transaksi ini.'); // Malay message
    }


    DB::beginTransaction();
    try {
      // 1. Update the LoanTransaction record with return details
      $transaction->returning_officer_id        = $returnDetails['returning_officer_id'] ?? null; // Get from $returnDetails array
      $transaction->return_accepting_officer_id = $returnAcceptingOfficer->id; // The IT Admin user
      $transaction->accessories_checklist_on_return = $returnDetails['accessories_checklist_on_return'] ?? null;
      $transaction->equipment_condition_on_return = $returnDetails['equipment_condition_on_return'] ?? null; // Condition reported on return
      $transaction->return_notes                  = $returnDetails['return_notes'] ?? null;
      $transaction->return_timestamp              = $returnDetails['return_timestamp'] ?? now(); // Use provided timestamp or now()

      // Determine final transaction status based on return condition
      // Assumes constants like STATUS_RETURNED, STATUS_DAMAGED_ON_RETURN, STATUS_LOST_ON_RETURN, STATUS_UNDER_MAINTENANCE_ON_RETURN exist
      switch ($transaction->equipment_condition_on_return) { // Base status on condition
        case Equipment::CONDITION_DAMAGED: // Use constant from Equipment model? Or define in Transaction model?
          $transaction->status = LoanTransaction::STATUS_DAMAGED_ON_RETURN; // Assumes this constant exists
          // Potentially trigger process for damage assessment/repair
          break;
        case 'needs_maintenance': // Example condition value not necessarily in Equipment model constants
        case Equipment::CONDITION_BAD: // Example
          $transaction->status = LoanTransaction::STATUS_UNDER_MAINTENANCE_ON_RETURN; // Assumes this constant exists
          // Potentially trigger process for maintenance
          break;
        case 'lost': // Example
          $transaction->status = LoanTransaction::STATUS_LOST_ON_RETURN; // Assumes this constant exists
          // Potentially trigger process for reporting loss
          break;
        case Equipment::CONDITION_GOOD: // Use constant
        case Equipment::CONDITION_FINE: // Use constant
        default: // Assume good condition if not specified or recognised
          $transaction->status = LoanTransaction::STATUS_RETURNED; // Use constant
          break;
      }

      $transaction->save();
      Log::info('LoanTransaction record updated with return details.', ['transaction_id' => $transaction->id ?? 'N/A', 'status' => $transaction->status ?? 'N/A']);


      // 2. Update the Equipment status based on the return outcome
      switch ($transaction->status) {
        case LoanTransaction::STATUS_RETURNED:
          $equipment->availability_status = Equipment::AVAILABILITY_AVAILABLE; // Use constant
          $equipment->condition_status    = $transaction->equipment_condition_on_return; // Update equipment condition
          break;
        case LoanTransaction::STATUS_DAMAGED_ON_RETURN:
        case LoanTransaction::STATUS_UNDER_MAINTENANCE_ON_RETURN:
          $equipment->availability_status = Equipment::AVAILABILITY_UNDER_MAINTENANCE; // Use constant
          $equipment->condition_status    = $transaction->equipment_condition_on_return; // Update equipment condition
          break;
        case LoanTransaction::STATUS_LOST_ON_RETURN:
          $equipment->availability_status = Equipment::AVAILABILITY_LOST; // Use constant
          // Condition status might remain as is or be set to null/unknown
          break;
        // If statuses like CANCELLED, OVERDUE are possible final states, handle them
        default:
          // If the transaction status doesn't imply a change back to AVAILABLE,
          // keep the equipment status as is (e.g., still ON_LOAN if transaction was cancelled mid-loan)
          Log::warning('Equipment status not updated after return due to unexpected transaction status.', [
            'transaction_id' => $transaction->id ?? 'N/A',
            'transaction_status' => $transaction->status ?? 'N/A',
            'equipment_id' => $equipment->id ?? 'N/A',
          ]);
          break;
      }
      $equipment->save();
      Log::info('Equipment status updated after return.', ['equipment_id' => $equipment->id ?? 'N/A', 'status' => $equipment->availability_status ?? 'N/A']);


      // 3. Update the related LoanApplicationItem's quantity_returned
      // Ensure loanApplication relationship is loaded on transaction
      $transaction->load('loanApplication.items');
      $applicationItem = $transaction->loanApplication->items
        ->where('equipment_type_id', $equipment->equipment_type_id) // Assumes equipment_type_id exists and matches
        ->first(); // Get the first matching item (assuming one item per type in application)

      if ($applicationItem) {
        $applicationItem->quantity_returned++;
        $applicationItem->save();
        Log::debug('Updated quantity_returned for application item.', [
          'item_id' => $applicationItem->id,
          'new_quantity_returned' => $applicationItem->quantity_returned,
        ]);
      } else {
        Log::warning('LoanApplicationItem not found for equipment type during return process.', [
          'transaction_id' => $transaction->id ?? 'N/A',
          'application_id' => $transaction->loan_application_id ?? 'N/A',
          'equipment_id'   => $equipment->id ?? 'N/A',
          'equipment_type_id' => $equipment->equipment_type_id ?? 'N/A',
        ]);
        // Log a warning, but don't necessarily throw an error as the transaction/equipment updates are key.
      }


      // 4. Update the parent LoanApplication's status based on quantities issued/returned across all items
      $application = $transaction->loanApplication; // Get the parent application
      $application->load('items'); // Ensure items are loaded for the application
      $totalApproved  = $application->items->sum('quantity_approved');
      $totalIssued    = $application->items->sum('quantity_issued');
      $totalReturned  = $application->items->sum('quantity_returned');

      if ($totalReturned >= $totalApproved && $totalApproved > 0) { // Check if all approved items are returned
        $application->status = LoanApplication::STATUS_RETURNED; // Use constant
      } elseif ($totalReturned > 0 && $totalReturned < $totalApproved) { // Check if some, but not all, approved items are returned
        $application->status = LoanApplication::STATUS_PARTIALLY_RETURNED; // *** Use the newly added constant ***
      }
      // If totalReturned is 0, status remains as ISSUED or PARTIALLY_ISSUED
      $application->save();
      Log::info('LoanApplication status updated after return processing.', [
        'application_id' => $application->id ?? 'N/A',
        'status' => $application->status ?? 'N/A',
      ]);


      DB::commit();

      Log::info('Equipment return processing completed for transaction ID: ' . ($transaction->id ?? 'N/A') . '. Final Transaction Status: ' . ($transaction->status ?? 'N/A'));

      // 5. Trigger post-return notifications (optional)
      // Notify the applicant/user that the item has been returned/processed.
      // Notification::send($application->user, new EquipmentReturnedNotification($transaction)); // Assumes notification exists


      return $transaction->fresh(); // Return the updated transaction model

    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Failed to process return for transaction ID: ' . ($transaction->id ?? 'N/A') . ': ' . $e->getMessage(), ['exception' => $e]);
      throw new Exception('Gagal memproses pemulangan peralatan: ' . $e->getMessage()); // Malay message
    }
  }


  // Add other service methods related to Loan Applications or Transactions as needed
}
