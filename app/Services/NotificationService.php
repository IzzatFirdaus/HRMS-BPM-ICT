<?php

namespace App\Services;

use App\Models\User; // Import User model
use Illuminate\Database\Eloquent\Model; // Import base Model class if notifying generic models
use Illuminate\Support\Facades\Notification; // Import Notification facade
use Exception; // Import base Exception class
use Illuminate\Support\Facades\Log; // Import Log facade

// Import specific Notification classes that this service might dispatch
// use App\Notifications\ApplicationStatusUpdated;
// use App\Notifications\NewPendingApproval;
// use App\Notifications\EquipmentIssuedNotification;
// use App\Notifications\EquipmentReturnedNotification;


/**
 * Optional service to encapsulate logic for formatting and dispatching notifications.
 * This can be used to centralize notification sending logic or handle complex notification scenarios.
 */
class NotificationService
{
  // Inject any dependencies needed for notification logic (e.g., other services, repositories)

  /**
   * Dispatches a notification about an application status update to the applicant.
   *
   * @param Model $application The application model (e.g., EmailApplication, LoanApplication).
   * @param string $newStatus The new status of the application.
   * @param array $extraData Any extra data to include in the notification.
   * @return void
   */
  public function notifyApplicantStatusUpdate(Model $application, string $newStatus, array $extraData = []): void
  {
    try {
      // Ensure the application model has a 'user' relationship to the applicant
      if ($application->user) {
        // Create and dispatch the specific notification class
        // Example: Notification::send($application->user, new ApplicationStatusUpdated($application, $newStatus, $extraData));
        Log::info("Attempting to notify applicant ID " . $application->user->id . " about status update for " . get_class($application) . " ID " . $application->id . ". New status: " . $newStatus);
        // TODO: Replace with actual notification dispatching logic
      } else {
        Log::warning("Cannot notify applicant for " . get_class($application) . " ID " . $application->id . ". Applicant user relationship missing.");
      }
    } catch (Exception $e) {
      Log::error("Failed to send status update notification for " . get_class($application) . " ID " . $application->id . ": " . $e->getMessage());
      // Decide how to handle notification failures (log, retry, notify admin)
    }
  }

  /**
   * Dispatches a notification about a new pending approval task to an officer.
   *
   * @param User $officer The officer who needs to be notified.
   * @param Model $approvable The model requiring approval.
   * @param string $stage The approval stage.
   * @param array $extraData Any extra data to include.
   * @return void
   */
  public function notifyOfficerOfPendingApproval(User $officer, Model $approvable, string $stage, array $extraData = []): void
  {
    try {
      // Create and dispatch the specific notification class
      // Example: Notification::send($officer, new NewPendingApproval($approvable, $stage, $extraData));
      Log::info("Attempting to notify officer ID " . $officer->id . " about pending approval for " . get_class($approvable) . " ID " . $approvable->id . ". Stage: " . $stage);
      // TODO: Replace with actual notification dispatching logic
    } catch (Exception $e) {
      Log::error("Failed to notify officer ID " . $officer->id . " about pending approval for " . get_class($approvable) . " ID " . $approvable->id . ": " . $e->getMessage());
    }
  }

  /**
   * Dispatches a notification to a group of users (e.g., all BPM staff, all IT Admins).
   *
   * @param iterable<User> $users Collection of users to notify.
   * @param string $type A type identifier for the notification logic.
   * @param Model $relatedModel A model related to the notification (e.g., LoanApplication).
   * @param array $extraData Any extra data.
   * @return void
   */
  public function notifyGroup(iterable $users, string $type, Model $relatedModel, array $extraData = []): void
  {
    try {
      if (count($users) === 0) {
        Log::warning("Attempted to send group notification of type '" . $type . "' but the user collection is empty.");
        return; // Nothing to notify
      }

      // Determine the specific notification class or logic based on the $type and $relatedModel
      // Example:
      // if ($type === 'loan_approved_for_bpm' && $relatedModel instanceof \App\Models\LoanApplication) {
      //     Notification::send($users, new \App\Notifications\LoanApplicationApprovedForBPM($relatedModel, $extraData));
      // } elseif ($type === 'email_ready_for_processing' && $relatedModel instanceof \App\Models\EmailApplication) {
      //     Notification::send($users, new \App\Notifications\EmailReadyForProcessing($relatedModel, $extraData));
      // }
      Log::info("Attempting to send group notification of type '" . $type . "' to " . count($users) . " users related to " . get_class($relatedModel) . " ID " . $relatedModel->id);
      // TODO: Replace with actual group notification dispatching logic based on type

    } catch (Exception $e) {
      Log::error("Failed to send group notification of type '" . $type . "' related to " . get_class($relatedModel) . " ID " . $relatedModel->id . ": " . $e->getMessage());
    }
  }


  // Add other helper methods for different notification scenarios as needed
  // e.g., notifyAdminOfError(...), notifyResponsibleOfficer(...)

}
