<?php

namespace App\Services;

use App\Models\LoanApplication;
use App\Models\LoanTransaction; // Import LoanTransaction model
use App\Models\Equipment; // Import Equipment model
use App\Models\User;
use App\Models\Approval;
use App\Models\LoanApplicationItem; // Import LoanApplicationItem model
use App\Services\ApprovalService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Support\Facades\Notification; // Import Notification facade
use Illuminate\Support\Collection; // <-- ADDED THIS USE STATEMENT


// Assuming these Notification classes exist
// use App\Notifications\EquipmentIssuedNotification;
// use App\Notifications\EquipmentReturnedNotification;\


class LoanApplicationService
{
  protected ApprovalService $approvalService;

  public function __construct(ApprovalService $approvalService)
  {
    $this->approvalService = $approvalService;
  }

  /**
   * Creates a new draft loan application along with its items.
   * UPDATED: Accepts loanItemsData array as a third argument.
   *
   * @param  array  $validatedData    Data for the LoanApplication model (purpose, dates, location, etc.).
   * @param  User   $applicant        The user creating the application.
   * @param  array  $loanItemsData    Array of data for LoanApplicationItem models (equipmentType, quantity, notes).
   * @return LoanApplication
   * @throws Exception
   */
  public function createApplication(array $validatedData, User $applicant, array $loanItemsData): LoanApplication // Added $loanItemsData argument
  {
    Log::debug('Creating new loan application draft with items', ['user_id' => $applicant->id, 'items_count' => count($loanItemsData)]);

    // Ensure there are items to create
    if (empty($loanItemsData)) {
      Log::warning('Attempted to create loan application without items.', ['user_id' => $applicant->id]);
      throw new Exception('Permohonan pinjaman peralatan mesti mengandungi sekurang-kurangnya satu item peralatan.'); // Malay message
    }


    DB::beginTransaction();
    try {
      // Create the main Loan Application record
      $app = new LoanApplication();
      $app->user_id = $applicant->id;
      // Fill the application with validated data (excluding loan items data, which is handled separately)
      $app->fill($validatedData);
      // Set initial status as per workflow step 3, form submission moves from draft to pending_support.
      $app->status = LoanApplication::STATUS_PENDING_SUPPORT; // Set status to pending support review
      $app->save();

      Log::info('Loan application record created', ['application_id' => $app->id]);

      // Create associated Loan Application Items
      $items = [];
      foreach ($loanItemsData as $itemData) {
        // Basic validation for item data structure
        if (empty($itemData['equipmentType']) || !isset($itemData['quantityRequested']) || $itemData['quantityRequested'] < 1) {
          Log::warning('Invalid item data provided during application creation.', ['item_data' => $itemData, 'user_id' => $applicant->id]);
          throw new Exception('Data item peralatan tidak sah.'); // Malay message for invalid item
        }
        $items[] = new LoanApplicationItem([
          'equipment_type' => $itemData['equipmentType'],
          'quantity_requested' => $itemData['quantityRequested'],
          'notes' => $itemData['notes'] ?? null, // Use nullish coalescing for optional notes
          // quantity_approved, quantity_issued, quantity_returned default to 0 or null in migration
        ]);
      }
      // Save all items related to the application
      $app->items()->saveMany($items); // Assumes a 'items' relationship on LoanApplication model

      Log::info('Loan application items created', ['application_id' => $app->id, 'items_count' => count($items)]);


      // Trigger notification or further workflow steps (e.g., assign support officer)
      // This logic might belong in the submitApplication method of the Livewire component or controller
      // *after* the service successfully creates the application.
      // However, if the service is responsible for initiating the workflow post-creation:
      // Example: Find first user with 'support officer' role and notify
      $supportOfficer = User::role('support officer')->first(); // Assuming 'support officer' role exists

      if ($supportOfficer) {
        // Assuming a notification exists
        // Notification::send($supportOfficer, new NewLoanApplicationForApproval($app));
        Log::info('Notified support officer about new loan application.', [
          'application_id' => $app->id ?? 'N/A',
          'officer_id'     => $supportOfficer->id
        ]);
      } else {
        Log::warning('No support officer found to notify for loan application ID: ' . ($app->id ?? 'N/A') . ' after creation.');
        // Optionally log a critical error or notify admin if a support officer is mandatory
      }


      DB::commit();

      Log::info('Loan application and items saved successfully', ['application_id' => $app->id]);

      return $app->fresh(); // Return fresh instance with relationships loaded
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Failed to create loan application draft or items', [
        'user_id' => $applicant->id ?? 'N/A',
        'error' => $e->getMessage(),
        'exception' => $e // Log the full exception
      ]);
      // Re-throw the exception after logging and rolling back
      throw new Exception('Gagal mencipta permohonan pinjaman peralatan: ' . $e->getMessage(), 0, $e); // Malay message
    }
  }

  /**
   * Update an existing draft loan application.
   *
   * @param  LoanApplication  $application
   * @param  array             $validatedData
   * @param  User              $user
   * @param  array             $loanItemsData  Array of data for LoanApplicationItem models.
   * @return LoanApplication
   * @throws Exception
   */
  public function updateApplication(LoanApplication $application, array $validatedData, User $user, array $loanItemsData): LoanApplication // Added $loanItemsData argument
  {
    Log::debug('Updating loan application draft', ['application_id' => $application->id ?? 'N/A', 'user_id' => $user->id ?? 'N/A', 'items_count' => count($loanItemsData)]);

    // Ensure the application is in draft status and belongs to the user
    if (!$application->isDraft() || $application->user_id !== $user->id) {
      Log::warning('Attempted to update non-draft or non-owned loan application.', [
        'application_id' => $application->id ?? 'N/A',
        'user_id' => $user->id ?? 'N/A',
        'current_status' => $application->status ?? 'N/A'
      ]);
      throw new Exception('Permohonan tidak sah untuk dikemaskini.'); // Malay message
    }

    // Ensure there are items to update with
    if (empty($loanItemsData)) {
      Log::warning('Attempted to update loan application without providing items.', ['application_id' => $application->id ?? 'N/A']);
      throw new Exception('Permohonan pinjaman peralatan mesti mengandungi sekurang-kurangnya satu item peralatan.'); // Malay message
    }

    DB::beginTransaction();
    try {
      // Update the main Loan Application record
      $application->fill($validatedData);
      $application->save();

      Log::info('Loan application record updated', ['application_id' => $application->id]);

      // Update associated Loan Application Items
      // Simplest approach: Delete existing items and recreate them.
      // More complex/efficient: Compare existing items with new data and update/delete/create as needed.
      // For simplicity, let's delete and recreate for now.
      $application->items()->delete(); // Delete existing items
      Log::debug('Existing loan application items deleted for update.', ['application_id' => $application->id]);


      $items = [];
      foreach ($loanItemsData as $itemData) {
        // Basic validation for item data structure
        if (empty($itemData['equipmentType']) || !isset($itemData['quantityRequested']) || $itemData['quantityRequested'] < 1) {
          Log::warning('Invalid item data provided during application update.', ['item_data' => $itemData, 'application_id' => $application->id]);
          throw new Exception('Data item peralatan tidak sah.'); // Malay message for invalid item
        }
        $items[] = new LoanApplicationItem([
          'equipment_type' => $itemData['equipmentType'],
          'quantity_requested' => $itemData['quantityRequested'],
          'notes' => $itemData['notes'] ?? null, // Use nullish coalescing
          // quantities_approved/issued/returned should remain 0 for draft updates
        ]);
      }
      // Save the new/updated items
      $application->items()->saveMany($items);
      Log::info('Loan application items recreated during update', ['application_id' => $application->id, 'items_count' => count($items)]);


      DB::commit();

      Log::info('Draft loan application and items updated successfully', ['application_id' => $application->id]);

      return $application->fresh();
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Failed to update draft loan application or items', [
        'application_id' => $application->id ?? 'N/A',
        'user_id' => $user->id ?? 'N/A',
        'error' => $e->getMessage(),
        'exception' => $e // Log the full exception
      ]);
      // Re-throw the exception after logging and rolling back
      throw new Exception('Gagal mengemaskini permohonan pinjaman peralatan: ' . $e->getMessage(), 0, $e); // Malay message
    }
  }


  /**
   * Submits a draft loan application for approval.
   * This method transitions the status from DRAFT to PENDING_SUPPORT.
   *
   * @param  LoanApplication  $application
   * @param  User              $applicant
   * @return LoanApplication
   * @throws Exception If application is not in draft status or does not belong to applicant.
   */
  public function submitApplication(LoanApplication $application, User $applicant): LoanApplication // Add return type hint
  {
    Log::debug('Submitting loan application', ['application_id' => $application->id ?? 'N/A', 'user_id' => $applicant->id ?? 'N/A']);

    // Ensure the application is in draft status and belongs to the applicant
    if (!$application->isDraft() || $application->user_id !== $applicant->id) {
      Log::warning('Attempted to submit non-draft or non-owned loan application.', [
        'application_id' => $application->id ?? 'N/A',
        'user_id' => $applicant->id ?? 'N/A',
        'current_status' => $application->status ?? 'N/A'
      ]);
      throw new Exception('Permohonan tidak sah untuk dihantar.'); // Malay message
    }

    // Optional: Check if the application has items before submission is allowed
    if ($application->items->count() === 0) {
      Log::warning('Attempted to submit loan application with no items.', ['application_id' => $application->id ?? 'N/A']);
      throw new Exception('Permohonan pinjaman peralatan mesti mengandungi sekurang-kurangnya satu item peralatan sebelum dihantar.'); // Malay message
    }


    DB::beginTransaction();
    try {
      // Update status and timestamp
      $application->status = LoanApplication::STATUS_PENDING_SUPPORT; // Transition to the first approval stage
      $application->submission_timestamp = now(); // Record submission time
      $application->save();

      Log::info('Loan application status changed to PENDING_SUPPORT', ['application_id' => $application->id]);

      // Trigger the start of the approval process (handled by ApprovalService)
      // This service call should create the initial Approval record for the support officer
      // Assumes ApprovalService has a method like startLoanApprovalProcess
      // $this->approvalService->startLoanApprovalProcess($application, Approval::STAGE_SUPPORT_REVIEW); // Example call


      // Notify the first approver (e.g., Support Officer)
      // This logic might be better within the ApprovalService's start process method.
      // However, if handled here:
      $supportOfficer = User::role('support officer')->first(); // Assuming 'support officer' role exists

      if ($supportOfficer) {
        // Assuming a notification exists
        // Notification::send($supportOfficer, new NewLoanApplicationForApproval($application));
        Log::info('Notified support officer about new loan application.', [
          'application_id' => $application->id ?? 'N/A',
          'officer_id'     => $supportOfficer->id
        ]);
      } else {
        Log::warning('No support officer found to notify for loan application ID: ' . ($application->id ?? 'N/A') . ' during submission.');
        // Depending on your workflow, this might be a critical error or require alternative notification.
      }


      DB::commit();

      Log::info('Loan application submitted successfully.', ['application_id' => $application->id]);

      return $application->fresh();
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Submission of loan application failed', [
        'application_id' => $application->id ?? 'N/A',
        'user_id' => $applicant->id ?? 'N/A',
        'error' => $e->getMessage(),
        'exception' => $e // Log the full exception
      ]);
      throw new Exception('Gagal menghantar permohonan pinjaman peralatan: ' . $e->getMessage(), 0, $e); // Malay message
    }
  }

  /**
   * Deletes a draft loan application.
   *
   * @param  LoanApplication  $application The application to delete.
   * @param  User              $user         The user attempting to delete.
   * @return bool True if deletion was successful.
   * @throws Exception If application is not in draft status or does not belong to the user.
   */
  public function deleteApplication(LoanApplication $application, User $user): bool // Added return type hint
  {
    Log::debug('Attempting to delete loan application draft', ['application_id' => $application->id ?? 'N/A', 'user_id' => $user->id ?? 'N/A']);

    // Ensure the application is in draft status and belongs to the user
    if (!$application->isDraft() || $application->user_id !== $user->id) {
      Log::warning('Attempted to delete non-draft or non-owned loan application.', [
        'application_id' => $application->id ?? 'N/A',
        'user_id' => $user->id ?? 'N/A',
        'current_status' => $application->status ?? 'N/A'
      ]);
      throw new Exception('Permohonan tidak sah untuk dibuang.'); // Malay message
    }

    DB::beginTransaction();
    try {
      // LoanApplicationItems will be soft-deleted automatically if relationships are set up correctly
      // and LoanApplicationItem also uses SoftDeletes. If not, you might need to delete items first.
      // $application->items()->delete(); // Uncomment if LoanApplicationItem doesn't use SoftDeletes

      $deleted = $application->delete(); // Soft delete the application

      DB::commit();
      Log::info('Draft loan application deleted successfully', ['application_id' => $application->id ?? 'N/A']);
      return $deleted;
    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Deletion of loan application failed', [
        'application_id' => $application->id ?? 'N/A',
        'user_id' => $user->id ?? 'N/A',
        'error' => $e->getMessage(),
        'exception' => $e // Log the full exception
      ]);
      throw new Exception('Gagal membuang permohonan pinjaman peralatan: ' . $e->getMessage(), 0, $e); // Malay message
    }
  }


  /**
   * Handles approval decisions for a loan application.
   * This method is likely called by an ApprovalService or controller processing an approval action.
   *
   * @param  LoanApplication  $application The application being acted upon.
   * @param  string           $decision    'approved' or 'rejected'.
   * @param  User             $officer     The officer making the decision.
   * @param  string|null      $comments    Optional comments.
   * @param  string           $stage       The approval stage (e.g., 'support_review', 'it_admin').
   * @return LoanApplication  The updated application.
   * @throws Exception        If the decision is invalid or application status is wrong.
   */
  public function handleApprovalDecision(
    LoanApplication $application,
    string $decision,
    User $officer,
    ?string $comments,
    string $stage // Add stage parameter
  ): LoanApplication // Add return type hint
  {
    Log::debug('Handling approval decision for loan application ID: ' . ($application->id ?? 'N/A'), [
      'decision' => $decision,
      'officer_id' => $officer->id ?? 'N/A',
      'stage' => $stage // Log the stage
    ]);
    // Basic validation for decision against Approval constants
    if (!in_array($decision, [Approval::STATUS_APPROVED, Approval::STATUS_REJECTED])) {
      Log::warning('Invalid approval decision provided.', ['decision' => $decision, 'application_id' => $application->id ?? 'N/A']);
      throw new Exception('Keputusan kelulusan tidak sah.'); // Malay message
    }

    // You might need to add checks here to ensure the application's current status
    // is appropriate for receiving a decision at this specific stage.
    // Example: If stage is support_review, status should be pending_support.
    // If your workflow is sequential, this is crucial.
    // if ($stage === Approval::STAGE_SUPPORT_REVIEW && $application->status !== LoanApplication::STATUS_PENDING_SUPPORT) {
    //     Log::warning('Approval decision received for application in incorrect status.', [
    //         'application_id' => $application->id ?? 'N/A',
    //         'stage' => $stage,
    //         'current_status' => $application->status ?? 'N/A'
    //     ]);
    //     throw new Exception('Permohonan tidak dalam status menunggu sokongan.'); // Malay message
    // }
    // Add similar checks for other stages...


    DB::beginTransaction();
    try {
      // Record the approval/rejection transaction (create an Approval record)
      // This should ideally be handled by the ApprovalService, which would then
      // call this LoanApplicationService method to update the application status.
      // If this method *is* called by a controller after the Approval record is saved,
      // the Approval record itself doesn't need to be created here.
      // Let's assume an Approval record is created elsewhere and this method
      // is focused on updating the LoanApplication status based on the *decision*.

      if ($decision === Approval::STATUS_REJECTED) {
        // If rejected at any stage, the application status becomes REJECTED
        $application->status = LoanApplication::STATUS_REJECTED;
        // Store rejection reason and the officer/stage that rejected it
        $application->rejection_reason = 'Ditolak oleh pegawai ' . ($officer->name ?? 'Tidak Dikenali') . ' (Peringkat: ' . $stage . '): ' . ($comments ?? 'Tiada catatan.'); // Malay message
        $application->save();
        Log::info('Loan application rejected.', ['application_id' => $application->id ?? 'N/A', 'officer_id' => $officer->id ?? 'N/A', 'stage' => $stage]);

        // Optional: Notify applicant of rejection
        // Notification::send($application->user, new LoanApplicationRejected($application, $comments)); // Assumes notification exists


      } elseif ($decision === Approval::STATUS_APPROVED) {
        // If approved, the application status depends on the stage and workflow
        // Example sequential workflow: pending_support -> pending_it_admin -> pending_bpm -> approved
        // This requires logic to determine the next status based on the current stage.
        // This logic might be better managed by the ApprovalService or a dedicated workflow manager.

        // Assuming for simplicity in this method that an 'approved' decision at a certain
        // point means the application is fully approved and ready for BPM action/issuance.
        // **If your workflow is multi-stage, replace this simplified logic.**
        $application->status = LoanApplication::STATUS_APPROVED; // Set to final APPROVED status

        $application->save();
        Log::info('Loan application approved.', ['application_id' => $application->id ?? 'N/A', 'officer_id' => $officer->id ?? 'N/A', 'stage' => $stage]);

        // If fully approved, trigger IT Admin processing / notification for issuance
        // This step might also involve creating tasks for the IT team or BPM staff
        if ($application->status === LoanApplication::STATUS_APPROVED) {
          // Notify BPM Staff or IT Admin team that an application is ready for issuance/processing
          $bpmStaff = User::role('BPM Staff')->get(); // Assuming 'BPM Staff' role exists
          $itAdmins = User::role('IT Admin')->get(); // Assuming 'IT Admin' role exists

          // Combine recipients, avoiding duplicates
          $recipients = $bpmStaff->merge($itAdmins)->unique('id');

          if ($recipients->isNotEmpty()) {
            // Assuming a notification exists
            // Notification::send($recipients, new LoanApplicationApprovedForProcessing($application));
            Log::info('Notified BPM Staff/IT Admins about approved loan application.', ['application_id' => $application->id ?? 'N/A', 'recipient_count' => $recipients->count()]);
          } else {
            Log::warning('No BPM Staff or IT Admins found to notify for approved loan application ID: ' . ($application->id ?? 'N/A'));
          }
        }
      }

      // Save changes to the application (status, rejection reason)
      // $application->save(); // Moved save inside the if/elseif blocks

      DB::commit();

      // Trigger notification to applicant about the decision
      // This might also be handled by the ApprovalService or a separate event listener.
      // Notification::send($application->user, new LoanApplicationDecision($application, $decision)); // Assuming a notification exists


      return $application->fresh(); // Return the updated application model

    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Failed to process approval decision for loan application ID ' . ($application->id ?? 'N/A') . '.', [
        'decision' => $decision,
        'officer_id' => $officer->id ?? 'N/A',
        'stage' => $stage,
        'error' => $e->getMessage(),
        'exception' => $e // Log the full exception
      ]);
      throw new Exception('Gagal memproses keputusan kelulusan: ' . $e->getMessage(), 0, $e); // Malay message
    }
  }


  /**
   * Handles the issuance of equipment items for a loan application.
   * This method is typically called by a controller action (e.g., in LoanTransactionController).
   * It creates LoanTransaction records and updates equipment/application statuses.
   *
   * @param  \App\Models\LoanApplication  $application        The parent loan application.
   * @param  \Illuminate\Support\Collection<\App\Models\Equipment> $equipmentToIssue Collection of Equipment models to issue.
   * @param  \App\Models\User|null        $receivingOfficer   The officer receiving on behalf of applicant (nullable).
   * @param  \App\Models\User             $issuingOfficer     The officer performing the issuance (BPM Staff/Admin).
   * @param  array                        $issueDetails       Optional details (accessories checklist, notes) per equipment item if applicable, or a single array for all.
   * @return \Illuminate\Support\Collection<\App\Models\LoanTransaction> Collection of created loan transaction records.
   * @throws \Exception                                     If issuance fails or equipment is unavailable/already on loan.
   */
  public function issueEquipment(
    LoanApplication $application,
    Collection $equipmentToIssue, // Expect a Collection of Equipment models
    ?User $receivingOfficer, // Nullable User model
    User $issuingOfficer, // User model
    array $issueDetails = [] // Optional array for general issue details or details per equipment
  ): Collection // Returns a Collection of LoanTransaction models
  {
    Log::debug('Attempting to issue multiple equipment items for application ID: ' . ($application->id ?? 'N/A'), [
      'equipment_count'     => $equipmentToIssue->count(),
      'issuing_officer_id'  => $issuingOfficer->id ?? 'N/A',
      'receiving_officer_id' => $receivingOfficer->id ?? null,
    ]);

    // Basic checks: Application status must be APPROVED or PARTIALLY_ISSUED
    if (!in_array($application->status, [LoanApplication::STATUS_APPROVED, LoanApplication::STATUS_PARTIALLY_ISSUED])) {
      Log::warning('Attempted to issue equipment for application not in APPROVED or PARTIALLY_ISSUED status.', [
        'application_id' => $application->id ?? 'N/A',
        'current_status' => $application->status ?? 'N/A'
      ]);
      throw new Exception('Permohonan tidak dalam status yang membenarkan pengeluaran peralatan.'); // Malay message
    }

    // Check if all selected equipment is available
    $unavailableEquipment = $equipmentToIssue->filter(fn($equipment) => $equipment->availability_status !== Equipment::AVAILABILITY_AVAILABLE);

    if ($unavailableEquipment->isNotEmpty()) {
      $assetTags = $unavailableEquipment->pluck('asset_tag')->join(', ');
      Log::warning('Attempted to issue unavailable equipment items.', [
        'application_id' => $application->id ?? 'N/A',
        'unavailable_asset_tags' => $assetTags
      ]);
      throw new Exception('Peralatan dengan tag aset berikut tidak tersedia untuk dikeluarkan: ' . $assetTags); // Malay message
    }


    DB::beginTransaction();
    try {
      $createdTransactions = new Collection(); // Collection to hold created transactions

      foreach ($equipmentToIssue as $equipment) {
        // Create a LoanTransaction record for *each* equipment item being issued
        $transaction = new LoanTransaction();
        $transaction->loan_application_id            = $application->id;
        $transaction->equipment_id                   = $equipment->id; // Link to the specific Equipment model
        $transaction->issuing_officer_id             = $issuingOfficer->id;
        $transaction->receiving_officer_id           = $receivingOfficer->id ?? null; // Use nullish coalescing for nullable user
        // Decide how to handle accessories/notes if details are per item vs general
        // Assuming issueDetails is a general array for all items for now.
        $transaction->accessories_checklist_on_issue = $issueDetails['accessories_checklist_on_issue'] ?? null;
        $transaction->issue_timestamp                = $issueDetails['issue_timestamp'] ?? now(); // Use provided timestamp or now()
        $transaction->status                         = LoanTransaction::STATUS_ISSUED; // Use constant
        $transaction->save();

        Log::debug('Created transaction for equipment ID: ' . ($equipment->id ?? 'N/A'), ['transaction_id' => $transaction->id]);

        // Update the status of the specific Equipment item
        $equipment->availability_status = Equipment::AVAILABILITY_ON_LOAN;
        $equipment->save();
        Log::debug('Updated equipment status to ON_LOAN for ID: ' . ($equipment->id ?? 'N/A'));


        // Update related LoanApplicationItem's quantity_issued
        // Find the specific item type requested on the application that matches this equipment's type
        // and increment its issued quantity.
        $application->load('items'); // Ensure items relationship is loaded

        // Assuming Equipment model has a foreign key like equipment_type_id that links it to the item type requested
        $applicationItem = $application->items
          // Assuming Equipment has an asset_type that matches the LoanApplicationItem's equipment_type
          // This linkage might need adjustment based on your actual schema (equipment_type_id FK is better)
          ->firstWhere('equipment_type', $equipment->asset_type ?? 'N/A'); // Use asset_type from Equipment to match item type

        if ($applicationItem) {
          $applicationItem->quantity_issued++;
          $applicationItem->save();
          Log::debug('Updated quantity_issued for application item type: ' . ($applicationItem->equipment_type ?? 'N/A'), [
            'item_id' => $applicationItem->id,
            'new_quantity_issued' => $applicationItem->quantity_issued,
          ]);
        } else {
          Log::warning('LoanApplicationItem not found for equipment type ' . ($equipment->asset_type ?? 'N/A') . ' during issuance. Cannot update quantity_issued.', [
            'application_id' => $application->id ?? 'N/A',
            'equipment_id'   => $equipment->id ?? 'N/A',
          ]);
          // This indicates a data inconsistency: an equipment type being issued wasn't requested.
          // Decide if this should be a critical error or just a warning.
        }

        $createdTransactions->push($transaction); // Add transaction to the collection
      }

      // Update the parent LoanApplication's status based on total issued vs approved quantity across all items
      $application->load('items'); // Re-load items to get latest counts
      $totalApprovedItems = $application->items()->sum('quantity_approved'); // Total approved quantity across all item types
      $totalIssuedItems   = $application->items()->sum('quantity_issued'); // Total issued quantity across all item types (sum of quantity_issued on items)

      // Check if all approved items have been issued
      $allApprovedIssued = ($totalApprovedItems > 0 && $totalIssuedItems >= $totalApprovedItems);


      if ($totalIssuedItems > 0 && $totalIssuedItems < $totalApprovedItems) {
        // Some items issued, but not all approved quantities have been met
        $application->status = LoanApplication::STATUS_PARTIALLY_ISSUED;
      } elseif ($allApprovedIssued) {
        // All approved quantities have been issued
        $application->status = LoanApplication::STATUS_ISSUED;
      }
      // If totalIssuedItems is 0, status remains as is (e.g., APPROVED)

      $application->save();
      Log::info('LoanApplication status updated after equipment issuance.', [
        'application_id' => $application->id ?? 'N/A',
        'status' => $application->status ?? 'N/A',
        'total_approved' => $totalApprovedItems,
        'total_issued' => $totalIssuedItems
      ]);


      DB::commit();

      Log::info('Equipment issuance completed successfully for application ID: ' . ($application->id ?? 'N/A'), ['issued_count' => $createdTransactions->count()]);

      // 5. Trigger post-issuance notifications (optional)
      // Notify the applicant/user that items have been issued.
      // Notification::send($application->user, new EquipmentIssuedNotification($createdTransactions)); // Assumes notification exists


      return $createdTransactions->fresh(); // Return fresh instances of created transactions

    } catch (Exception $e) {
      DB::rollBack();
      Log::error('Failed to issue equipment for application ID: ' . ($application->id ?? 'N/A') . ': ' . $e->getMessage(), ['exception' => $e]);
      throw new Exception('Gagal memproses pengeluaran peralatan: ' . $e->getMessage(), 0, $e); // Malay message
    }
  }


  /**
   * Handles the return process for an issued equipment transaction.
   * This method updates the transaction, equipment, and application statuses.
   *
   * @param  \App\Models\LoanTransaction  $transaction           The loan transaction being returned.
   * @param  array                        $returnDetails         Details about the return (condition, notes, checklist, returning officer ID).
   * @param  \App\Models\User             $returnAcceptingOfficer The officer accepting the return (BPM Staff/Admin).
   * @return \App\Models\LoanTransaction                       The updated loan transaction record.
   * @throws \Exception                                      If return processing fails or transaction status is invalid.
   */
  public function handleReturn(LoanTransaction $transaction, array $returnDetails, User $returnAcceptingOfficer): LoanTransaction // Added return type hint
  {
    Log::debug('Attempting to process return for transaction ID: ' . ($transaction->id ?? 'N/A'), [
      'accepting_officer_id' => $returnAcceptingOfficer->id ?? 'N/A',
      'current_status'       => $transaction->status ?? 'N/A',
    ]);

    // Ensure the transaction is in a state that can be returned (e.g., ISSUED, ON_LOAN, UNDER_MAINTENANCE_ON_LOAN)
    // Use helper method isCurrentlyOnLoan() defined in LoanTransaction model
    if (!$transaction->isCurrentlyOnLoan()) {
      Log::warning('Attempted to return transaction not in valid return status.', [
        'transaction_id' => $transaction->id ?? 'N/A',
        'status' => $transaction->status ?? 'N/A',
      ]);
      throw new Exception('Transaksi tidak dalam status yang boleh dipulangkan.'); // Malay message
    }

    // Load the related equipment if not already loaded
    $transaction->load('equipment');
    $equipment = $transaction->equipment;

    // Ensure the equipment exists
    if (!$equipment) {
      Log::critical('Equipment relationship missing for transaction ID: ' . ($transaction->id ?? 'N/A') . ' during return process.');
      throw new Exception('Ralat sistem: Peralatan tidak ditemui untuk transaksi ini.'); // Malay message
    }


    DB::beginTransaction();
    try {
      // 1. Update the LoanTransaction record with return details
      $transaction->returning_officer_id        = $returnDetails['returning_officer_id'] ?? null; // Nullable FK
      $transaction->return_accepting_officer_id = $returnAcceptingOfficer->id; // Mandatory FK
      $transaction->accessories_checklist_on_return = $returnDetails['accessories_checklist_on_return'] ?? null; // Nullable JSON
      $transaction->equipment_condition_on_return = $returnDetails['equipment_condition_on_return'] ?? null; // Nullable string
      $transaction->return_notes                  = $returnDetails['return_notes'] ?? null; // Nullable string
      $transaction->return_timestamp              = $returnDetails['return_timestamp'] ?? now(); // Use provided timestamp or now()


      // Determine final transaction status based on return condition provided in $returnDetails
      // Use Equipment condition constants if available, fallback to provided string
      $conditionOnReturn = $returnDetails['equipment_condition_on_return'] ?? null;

      switch ($conditionOnReturn) {
        case Equipment::CONDITION_DAMAGED:
          $transaction->status = LoanTransaction::STATUS_DAMAGED_ON_RETURN; // Use constant
          break;
        case Equipment::CONDITION_BAD:
          // The next case uses the newly added constant
        case LoanTransaction::STATUS_UNDER_MAINTENANCE_ON_RETURN: // <-- Using the constant
        case 'needs_maintenance': // Also treat 'bad' condition as needing maintenance, match the constant value if needed
          $transaction->status = LoanTransaction::STATUS_UNDER_MAINTENANCE_ON_RETURN; // Use constant
          break;
        case 'lost': // Assuming 'lost' is a possible input value
          $transaction->status = LoanTransaction::STATUS_LOST_ON_RETURN; // Use constant
          break;
        case Equipment::CONDITION_GOOD:
        case Equipment::CONDITION_FINE:
        default:
          // Default to RETURNED if condition is good/fine or unknown
          // unless explicitly specified as lost/damaged/needs maintenance.
          // If no condition is provided, assume returned in good condition.
          $transaction->status = LoanTransaction::STATUS_RETURNED; // Use constant
          break;
      }

      $transaction->save();
      Log::info('LoanTransaction record updated with return details and final status.', ['transaction_id' => $transaction->id ?? 'N/A', 'status' => $transaction->status ?? 'N/A']);


      // 2. Update the Equipment status based on the transaction's final status
      switch ($transaction->status) {
        case LoanTransaction::STATUS_RETURNED:
          $equipment->availability_status = Equipment::AVAILABILITY_AVAILABLE; // Ready for next loan
          $equipment->condition_status    = $transaction->equipment_condition_on_return ?? Equipment::CONDITION_GOOD; // Update equipment condition
          break;
        case LoanTransaction::STATUS_DAMAGED_ON_RETURN:
        case LoanTransaction::STATUS_UNDER_MAINTENANCE_ON_RETURN: // Use the constant
          $equipment->availability_status = Equipment::AVAILABILITY_UNDER_MAINTENANCE; // Needs repair/service
          $equipment->condition_status    = $transaction->equipment_condition_on_return ?? Equipment::CONDITION_BAD; // Update equipment condition
          break;
        case LoanTransaction::STATUS_LOST_ON_RETURN:
          $equipment->availability_status = Equipment::AVAILABILITY_LOST; // Mark as lost
          // Condition status might not be applicable for a lost item, or set to 'Unknown'
          // $equipment->condition_status = 'Unknown'; // Example
          break;
        default:
          Log::warning('Equipment status not updated after return due to unexpected transaction status.', [
            'transaction_id' => $transaction->id ?? 'N/A',
            'transaction_status' => $transaction->status ?? 'N/A',
            'equipment_id' => $equipment->id ?? 'N/A',
          ]);
          break;
      }
      $equipment->save();
      Log::info('Equipment status updated after return processing.', ['equipment_id' => $equipment->id ?? 'N/A', 'status' => $equipment->availability_status ?? 'N/A']);


      // 3. Update the related LoanApplicationItem's quantity_returned
      // Find the specific item type requested on the application that matches this equipment's type
      // and increment its returned quantity.
      $application = $transaction->loanApplication; // Get the parent application
      $application->load('items'); // Ensure items relationship is loaded on the application

      // Assuming Equipment model has a foreign key like equipment_type_id that links it to the item type requested
      $applicationItem = $application->items
        // Assuming Equipment has an asset_type that matches the LoanApplicationItem's equipment_type
        // This linkage might need adjustment based on your actual schema (equipment_type_id FK is better)
        ->firstWhere('equipment_type', $equipment->asset_type ?? 'N/A'); // Use asset_type from Equipment to match item type


      if ($applicationItem) {
        $applicationItem->quantity_returned++;
        $applicationItem->save();
        Log::debug('Updated quantity_returned for application item type: ' . ($applicationItem->equipment_type ?? 'N/A'), [
          'item_id' => $applicationItem->id,
          'new_quantity_returned' => $applicationItem->quantity_returned,
        ]);
      } else {
        Log::warning('LoanApplicationItem not found for equipment type ' . ($equipment->asset_type ?? 'N/A') . ' during return process. Cannot update quantity_returned.', [
          'transaction_id' => $transaction->id ?? 'N/A',
          'application_id' => $application->id ?? 'N/A',
          'equipment_id'   => $equipment->id ?? 'N/A',
        ]);
        // This indicates a data inconsistency: an equipment type being returned wasn't requested.
        // Decide if this should be a critical error or just a warning.
      }


      // 4. Update the parent LoanApplication's status based on quantities issued/returned across all items
      // Reload application to get latest item counts after updating quantity_returned
      $application->load('items');
      $totalApprovedItems = $application->items()->sum('quantity_approved'); // Total approved quantity across all item types
      $totalIssuedItems   = $application->items()->sum('quantity_issued'); // Total issued quantity across all item types (sum of quantity_issued on items)
      $totalReturnedItems = $application->items()->sum('quantity_returned'); // Total returned quantity across all item types (sum of quantity_returned on items)

      // Determine if all issued items have been returned
      $allIssuedReturned = ($totalIssuedItems > 0 && $totalReturnedItems >= $totalIssuedItems);

      // Determine if all approved items were ever issued (this is needed to know if the application is fully fulfilled)
      $allApprovedWereIssued = ($totalApprovedItems > 0 && $totalIssuedItems >= $totalApprovedItems);


      if ($allIssuedReturned && $allApprovedWereIssued) {
        // All items that were approved have been issued AND all issued items have been returned
        $application->status = LoanApplication::STATUS_RETURNED; // Application is now fully returned/closed successfully
      } elseif ($totalReturnedItems > 0 && $totalReturnedItems < $totalIssuedItems) {
        // Some items issued have been returned, but not all issued items are back yet
        $application->status = LoanApplication::STATUS_PARTIALLY_RETURNED; // Assumes this constant exists
      }
      // If totalReturnedItems is 0 but totalIssuedItems > 0, status remains ISSUED or PARTIALLY_ISSUED (handled by issuance logic)


      $application->save();
      Log::info('LoanApplication status updated after return processing.', [
        'application_id' => $application->id ?? 'N/A',
        'status' => $application->status ?? 'N/A',
        'total_approved_items' => $totalApprovedItems,
        'total_issued_items' => $totalIssuedItems,
        'total_returned_items' => $totalReturnedItems,
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
      throw new Exception('Gagal memproses pemulangan peralatan: ' . $e->getMessage(), 0, $e); // Malay message
    }
  }


  // Add other service methods related to Loan Applications or Transactions as needed
}
