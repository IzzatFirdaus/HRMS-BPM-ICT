<?php

namespace App\Services;

use App\Models\LoanApplication;
use App\Models\LoanApplicationItem;
use App\Models\LoanTransaction;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Support\Facades\DB; // Example: if transactions are needed
use Illuminate\Support\Facades\Log; // For logging
use Illuminate\Support\Facades\Notification; // For sending notifications

class LoanApplicationService
{
  protected $approvalService; // Inject ApprovalService if needed within this service

  public function __construct(ApprovalService $approvalService) // Inject dependency
  {
    $this->approvalService = $approvalService;
  }


  /**
   * Create a new loan application.
   *
   * @param User $applicant
   * @param array $applicationData Main application data
   * @param array $itemsData Array of equipment item data
   * @return LoanApplication
   * @throws \Exception
   */
  public function createApplication(User $applicant, array $applicationData, array $itemsData): LoanApplication
  {
    // Ensure policy check is done before calling this method

    DB::beginTransaction(); // Use database transactions for atomicity

    try {
      // Create the main application record
      $application = new LoanApplication($applicationData);
      $application->user()->associate($applicant);
      // Set initial status - either 'draft' or 'pending_support' immediately
      $application->status = 'pending_support'; // Assuming immediate submission triggers review
      if (!isset($applicationData['applicant_confirmation_timestamp'])) {
        $application->applicant_confirmation_timestamp = now(); // Timestamp if not provided
      }


      $application->save();

      // Attach requested equipment items
      foreach ($itemsData as $itemData) {
        // Ensure itemData has 'equipment_type', 'quantity_requested', 'notes'
        $application->items()->create($itemData); // Assuming LoanApplication has 'items' relationship
      }

      // Optional: Create initial 'pending' approval record if workflow requires it immediately on submission
      // $this->approvalService->recordApproval($application, $applicant, 'pending', 'Submitted for approval');

      DB::commit(); // Commit the transaction

      // Trigger notification to approver(s) - find appropriate approvers here
      // Example: Find Grade 41+ users
      // $approvers = User::whereHas('grade', fn($q) => $q->where('level', '>=', config('motac.approval.min_approver_grade_level')))->get();
      // Notification::send($approvers, new \App\Notifications\NewLoanApplicationPending($application)); // Create this notification


      Log::info("New loan application created: " . $application->id . " by user: " . $applicant->id);

      return $application;
    } catch (\Exception $e) {
      DB::rollBack(); // Rollback the transaction on error
      Log::error("Failed to create loan application for user: " . $applicant->id . ". Error: " . $e->getMessage());
      throw $e; // Re-throw the exception
    }
  }

  /**
   * Update an existing loan application (e.g., from draft) and potentially submit it.
   *
   * @param LoanApplication $application The application to update
   * @param array $applicationData Updated main application data
   * @param array $itemsData Updated array of equipment item data
   * @return LoanApplication
   * @throws \Exception
   */
  public function updateApplication(LoanApplication $application, array $applicationData, array $itemsData): LoanApplication
  {
    // Ensure policy check is done before calling this method

    DB::beginTransaction(); // Use database transactions

    try {
      // Update the main application data
      $application->fill($applicationData);

      // If status is being changed to pending_support, record timestamp
      if ($application->isDirty('status') && $application->status === 'pending_support') {
        $application->applicant_confirmation_timestamp = now();
      }
      $application->save();


      // Sync items - delete existing and create new ones or update
      // Simple approach: delete all existing items and recreate
      $application->items()->delete();
      foreach ($itemsData as $itemData) {
        $application->items()->create($itemData);
      }

      // Optional: Update or create initial 'pending' approval record


      DB::commit(); // Commit the transaction

      // Trigger notifications if status changed to pending_support


      Log::info("Loan application updated: " . $application->id);

      return $application;
    } catch (\Exception $e) {
      DB::rollBack(); // Rollback the transaction on error
      Log::error("Failed to update loan application: " . $application->id . ". Error: " . $e->getMessage());
      throw $e; // Re-throw the exception
    }
  }

  /**
   * BPM Staff issues equipment for an approved loan application.
   *
   * @param LoanApplication $application
   * @param Equipment $equipment The specific equipment asset being issued
   * @param array $transactionData Transaction details (e.g., accessories, notes)
   * @param User $issuingOfficer The BPM staff issuing
   * @return LoanTransaction
   * @throws \Exception
   */
  public function issueEquipment(LoanApplication $application, Equipment $equipment, array $transactionData, User $issuingOfficer): LoanTransaction
  {
    // Ensure policy check is done before calling this method (e.g., can('issue', $application))

    DB::beginTransaction();
    try {
      // Create the loan transaction record
      $transaction = $application->transactions()->create([ // Assuming hasMany transactions relationship
        'equipment_id' => $equipment->id,
        'issuing_officer_id' => $issuingOfficer->id,
        'receiving_officer_id' => $transactionData['receiving_officer_id'] ?? $application->user_id, // Default to applicant
        'accessories_checklist_on_issue' => $transactionData['accessories'] ?? null,
        'issue_timestamp' => now(),
        'status' => 'issued',
        'notes' => $transactionData['notes'] ?? null, // Or dedicated notes field
      ]);

      // Update the specific equipment asset status
      $equipment->status = 'on_loan';
      $equipment->save();

      // Update the loan application status (e.g., 'issued' or 'partially_issued')
      // You would check if all requested items have been issued
      $application->status = 'issued'; // Simplified
      $application->save();

      DB::commit();

      // Notify the applicant that equipment has been issued
      // $application->user->notify(new \App\Notifications\EquipmentIssuedNotification($transaction)); // Create this notification


      Log::info("Equipment issued for loan application " . $application->id . ": Transaction " . $transaction->id);

      return $transaction;
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error("Failed to issue equipment for loan application " . $application->id . ". Error: " . $e->getMessage());
      throw $e;
    }
  }

  /**
   * BPM Staff processes the return of equipment.
   *
   * @param LoanTransaction $transaction The transaction record for the issued equipment
   * @param array $returnDetails Return details (e.g., accessories checklist on return, notes)
   * @param User $acceptingOfficer The BPM staff accepting the return
   * @return LoanTransaction
   * @throws \Exception
   */
  public function processReturn(LoanTransaction $transaction, array $returnDetails, User $acceptingOfficer): LoanTransaction
  {
    // Ensure policy check is done before calling this method (e.g., can('processReturn', $transaction->loanApplication))

    DB::beginTransaction();
    try {
      // Update the loan transaction record with return details
      $transaction->fill([
        'returning_officer_id' => $returnDetails['returning_officer_id'] ?? $transaction->loanApplication->user_id, // Default to applicant
        'return_accepting_officer_id' => $acceptingOfficer->id, // Current user is accepting officer
        'return_timestamp' => now(),
        'accessories_checklist_on_return' => $returnDetails['accessories'] ?? null,
        'return_notes' => $returnDetails['notes'] ?? null,
        'status' => $returnDetails['status'] ?? 'returned', // Allow specifying status like 'returned', 'damaged', 'lost'
      ]);
      $transaction->save();

      // Update the specific equipment asset status
      $equipment = $transaction->equipment;
      if ($equipment) {
        $equipment->status = $returnDetails['equipment_status_on_return'] ?? 'available'; // e.g., 'available', 'under_maintenance', 'disposed'
        $equipment->save();
      }


      // Update the loan application status (e.g., 'returned')
      // Check if all items related to the application are returned before setting application status to 'returned'
      $loanApplication = $transaction->loanApplication;
      // Example simple check: if this is the only issued transaction or all issued transactions are now returned
      $allTransactionsReturned = $loanApplication->transactions()->where('status', '!=', 'returned')->count() === 0;
      if ($allTransactionsReturned) {
        $loanApplication->status = 'returned';
        $loanApplication->save();
        // Notify applicant?
      }


      DB::commit();

      // Notify the applicant that equipment has been returned
      // $loanApplication->user->notify(new \App\Notifications\EquipmentReturnedNotification($transaction)); // Create this notification


      Log::info("Equipment return processed for transaction " . $transaction->id);

      return $transaction;
    } catch (\Exception $e) {
      DB::rollBack();
      Log::error("Failed to process equipment return for transaction " . $transaction->id . ". Error: " . $e->getMessage());
      throw $e;
    }
  }

  // Add methods for checking equipment availability, generating reports, etc.
}
