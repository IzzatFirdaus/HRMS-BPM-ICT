<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Database\Eloquent\Model; // Import base Model for type hinting flexibility

// Import the models needed for this notification
use App\Models\EmailApplication;
use App\Models\Approval; // Assuming you pass the Approval record that caused the rejection
use App\Models\User; // Import User model as the notifiable object is a User


/**
 * Class EmailApplicationRejectedNotification
 *
 * Notification sent to the applicant when their Email Application has been rejected during the approval process.
 * This notification includes the reason for the rejection.
 * This notification can be sent via email and optionally stored in the database.
 */
class EmailApplicationRejectedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * The email application model instance that was rejected.
   *
   * @var \App\Models\EmailApplication
   */
  public EmailApplication $emailApplication;

  /**
   * The specific approval record where the rejection occurred.
   * This record should contain the rejection comments.
   *
   * @var \App\Models\Approval
   */
  public Approval $rejectionApproval; // The specific approval record where rejection occurred

  /**
   * Create a new notification instance.
   *
   * @param \App\Models\EmailApplication $emailApplication The email application that was rejected.
   * @param \App\Models\Approval $rejectionApproval The approval record that marked the application as rejected (should contain comments).
   * @return void
   */
  public function __construct(EmailApplication $emailApplication, Approval $rejectionApproval) // Added docblock
  {
    $this->emailApplication = $emailApplication;
    $this->rejectionApproval = $rejectionApproval;
    // While not strictly needed for the current toMail logic, loading the user
    // here can be beneficial if the notification is expanded or user data is needed elsewhere.
    // $this->emailApplication->loadMissing('user');
  }

  /**
   * Get the notification's delivery channels.
   * Determines how the notification will be sent (e.g., mail, database).
   *
   * @param object $notifiable The notifiable entity (typically a User model instance, the applicant).
   * @return array<int, string> An array of channel names (e.g., 'mail', 'database').
   */
  public function via(object $notifiable): array // Added type hint for notifiable, refined docblock
  {
    // 'notifiable' here would be the User model (the applicant)
    // Return ['mail', 'database'] if you want to store notifications in the database as well.
    return ['mail']; // Specify that this notification should be sent via email
  }

  /**
   * Get the mail representation of the notification.
   * Defines the content, subject, and structure of the email sent to the applicant regarding their rejected email application.
   *
   * @param object $notifiable The entity ($user) being notified (the applicant).
   * @return \Illuminate\Notifications\Messages\MailMessage The MailMessage instance.
   */
  public function toMail(object $notifiable): MailMessage // Refined docblock
  {
    // Access the applicant's name for the greeting safely
    // Use optional chaining (?->) for robustness
    $applicantName = $notifiable->full_name ?? $notifiable->name ?? 'Pemohon';

    // Safely get the application ID
    $applicationId = $this->emailApplication->id ?? 'N/A';

    // Get the rejection reason from the Approval record's comments safely
    $rejectionReason = $this->rejectionApproval->comments ?? 'Tiada sebab dinyatakan.';


    $mailMessage = (new MailMessage)
      ->subject("Permohonan E-mel ICT MOTAC Ditolak (#{$applicationId})") // Email subject includes ID
      ->greeting('Salam ' . $applicantName . ',') // Greeting to the applicant
      ->line('Dukacita dimaklumkan bahawa permohonan akaun e-mel ICT MOTAC anda telah ditolak.') // Main message line 1
      ->line('Nombor Rujukan Permohonan: #' . $applicationId) // Include application ID
      ->line('Status Permohonan: Ditolak') // Explicitly state status
      ->line('Sebab Penolakan:') // Indicate rejection reason section
      ->line($rejectionReason) // Display the rejection reason
      ->line('Sekiranya anda memerlukan maklumat lanjut, sila hubungi Bahagian Pengurusan Maklumat (BPM) MOTAC.'); // Contact information


    // Optional: Add a button linking to the application details page (if you have one)
    // This URL would depend on your routing structure for viewing email application details by the applicant.
    // Safely construct the application URL
    $applicationUrl = '#'; // Default fallback URL
    if (isset($this->emailApplication->id)) {
      $applicationUrl = url('/email-applications/' . $this->emailApplication->id); // Assuming an applicant route exists
    }

    // Add the action button only if a valid URL was constructed
    if ($applicationUrl !== '#') {
      $mailMessage->action('Lihat Permohonan Anda', $applicationUrl);
    }


    return $mailMessage->salutation('Sekian, terima kasih.'); // Closing
    // Optionally add a footer with contact information
    // ->salutation('Sekian,')
    // ->line('Bahagian Pengurusan Maklumat (BPM) MOTAC');
  }

  /**
   * Get the array representation of the notification.
   * This data is used when storing the notification in the database if the 'database' channel is enabled.
   *
   * @param object $notifiable The entity ($user) being notified (the applicant).
   * @return array<string, mixed> An array of data to be stored in the database.
   */
  public function toArray(object $notifiable): array // Refined docblock
  {
    // Safely get the application ID and applicant ID
    $applicationId = $this->emailApplication->id ?? null;
    $applicantId = $this->emailApplication->user_id ?? null;

    // Safely get rejection details
    $rejectionReason = $this->rejectionApproval->comments ?? 'Tiada sebab dinyatakan.';
    $approvedByOfficerId = $this->rejectionApproval->officer_id ?? null;


    // Safely construct the URL for the in-app notification link to the applicant's view
    $applicationUrl = '#'; // Default fallback URL
    if (isset($this->emailApplication->id)) {
      $applicationUrl = url('/email-applications/' . $this->emailApplication->id); // Assuming an applicant route exists
    }


    return [
      // Store notification data in a database table if using the 'database' channel
      'application_type' => 'Email Application',
      'application_id' => $applicationId,
      'applicant_id' => $applicantId, // Include applicant ID
      // Generic subject for in-app display
      'subject' => 'Permohonan E-mel Ditolak',
      // Message for in-app display
      'message' => "Permohonan E-mel ICT anda (#{$applicationId}) telah ditolak.",
      // URL for the in-app notification to link to the applicant's view
      'url' => $applicationUrl,
      'status' => 'rejected', // Store the status that triggered the notification
      'rejection_reason' => $rejectionReason, // Store the rejection reason
      'rejected_by_officer_id' => $approvedByOfficerId, // Store the officer who rejected
    ];
  }

  // Implement toDatabase method if using the 'database' channel for custom storage logic beyond toArray.
  // public function toDatabase(object $notifiable): array { ... }
}
