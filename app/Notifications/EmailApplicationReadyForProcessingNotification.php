<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Database\Eloquent\Model; // Import base Model for type hinting flexibility

// Import the model needed for this notification
use App\Models\EmailApplication;
use App\Models\User; // Import User model if needed


/**
 * Class EmailApplicationReadyForProcessingNotification
 *
 * Notification sent to designated IT Admin/BPM staff when an Email Application has been approved by support
 * and is ready for the final provisioning process.
 * This notification can be sent via email and optionally stored in the database.
 */
class EmailApplicationReadyForProcessingNotification extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * The email application model instance that is ready for processing.
   *
   * @var \App\Models\EmailApplication
   */
  public EmailApplication $emailApplication;

  /**
   * Create a new notification instance.
   * This notification is sent to IT Admin/BPM when an email application is approved and ready for provisioning.
   *
   * @param \App\Models\EmailApplication $emailApplication The email application that is ready for processing.
   * @return void
   */
  public function __construct(EmailApplication $emailApplication) // Added docblock
  {
    $this->emailApplication = $emailApplication;
    // Ensure the applicant user relationship is loaded to access applicant details safely
    $this->emailApplication->loadMissing('user');
  }

  /**
   * Get the notification's delivery channels.
   * Determines how the notification will be sent (e.g., mail, database).
   * This notification is typically sent to a group of users (e.g., IT Admin role).
   *
   * @param object $notifiable The entity (e.g., a User representing an IT Admin) being notified.
   * @return array<int, string> An array of channel names (e.g., 'mail', 'database').
   */
  public function via(object $notifiable): array // Added type hint for notifiable, refined docblock
  {
    // 'notifiable' here would likely be a User model or a collection of Users (e.g., all IT Admins)
    // Return ['mail', 'database'] if you want to store notifications in the database as well.
    return ['mail']; // Specify that this notification should be sent via email
  }

  /**
   * Get the mail representation of the notification.
   * Defines the content, subject, and structure of the email sent to IT Admin/BPM staff.
   *
   * @param object $notifiable The entity (e.g., a User representing an IT Admin) being notified.
   * @return \Illuminate\Notifications\Messages\MailMessage The MailMessage instance.
   */
  public function toMail(object $notifiable): MailMessage // Refined docblock
  {
    // Access applicant details via the emailApplication relationship safely
    // Use optional chaining (?->) for robustness
    $applicantName = $this->emailApplication->user?->full_name ?? $this->emailApplication->user?->name ?? 'Pemohon Tidak Diketahui';
    // Assuming 'ic_number' attribute exists on the User model
    $applicantIc = $this->emailApplication->user?->nric ?? 'Tidak Diketahui'; // Using 'nric' based on User model updates
    $proposedEmail = $this->emailApplication->proposed_email ?? 'Tiada Cadangan';
    $purpose = $this->emailApplication->purpose ?? 'Tiada Tujuan Dinyatakan';

    // Safely get the application ID
    $applicationId = $this->emailApplication->id ?? 'N/A';


    $mailMessage = (new MailMessage)
      ->subject('Permohonan E-mel Baru Diluluskan & Sedia Untuk Penyediaan') // Email subject line
      ->greeting('Salam Petugas BPM/ICT,') // Greeting to the IT Admin/BPM staff
      ->line('Terdapat permohonan akaun e-mel ICT MOTAC baru yang telah diluluskan dan sedia untuk proses penyediaan (provisioning).') // Main message
      ->line('**Nombor Rujukan Permohonan:** #' . $applicationId)
      ->line('**Pemohon:** ' . $applicantName . ' (No. KP: ' . $applicantIc . ')')
      ->line('**Cadangan E-mel/ID:** ' . $proposedEmail)
      ->line('**Tujuan Permohonan:** ' . $purpose)
      ->line('Sila log masuk ke sistem untuk melihat butiran penuh permohonan dan melaksanakan proses penyediaan akaun e-mel.'); // Call to action


    // Optional: Add a button linking to the application details page in the admin/BPM interface
    // Make sure this URL is correct for your application details route.
    // Safely construct the admin application URL
    $adminApplicationUrl = '#'; // Default fallback URL
    if (isset($this->emailApplication->id)) {
      // Assuming an admin route structure, e.g., /admin/email-applications/{id}
      $adminApplicationUrl = url('/admin/email-applications/' . $this->emailApplication->id);
    }

    // Add the action button only if a valid URL was constructed
    if ($adminApplicationUrl !== '#') {
      $mailMessage->action('Lihat Permohonan', $adminApplicationUrl);
    }


    return $mailMessage->salutation('Terima kasih.'); // Closing
    // Optionally add a footer with contact information
    // ->salutation('Sekian,')
    // ->line('Bahagian Pengurusan Maklumat (BPM) MOTAC');
  }

  /**
   * Get the array representation of the notification.
   * This data is used when storing the notification in the database if the 'database' channel is enabled.
   *
   * @param object $notifiable The entity (e.g., a User representing an IT Admin) being notified.
   * @return array<string, mixed> An array of data to be stored in the database.
   */
  public function toArray(object $notifiable): array // Refined docblock
  {
    // Safely get the application ID and applicant ID
    $applicationId = $this->emailApplication->id ?? null;
    $applicantId = $this->emailApplication->user_id ?? null;
    // Safely access applicant name for the message
    $applicantName = $this->emailApplication->user?->full_name ?? $this->emailApplication->user?->name ?? 'Pemohon';

    // Safely construct the URL for the in-app notification link to the admin view
    $adminApplicationUrl = '#'; // Default fallback URL
    if (isset($this->emailApplication->id)) {
      $adminApplicationUrl = url('/admin/email-applications/' . $this->emailApplication->id); // Assuming an admin route exists
    }


    return [
      // Store notification data in a database table if using the 'database' channel
      'application_type' => 'Email Application',
      'application_id' => $applicationId,
      'applicant_id' => $applicantId,
      // Generic subject for in-app display
      'subject' => 'Permohonan E-mel Sedia Untuk Penyediaan',
      // Message for in-app display
      'message' => "Permohonan E-mel ICT (#{$applicationId}) oleh {$applicantName} sedia untuk penyediaan.",
      // URL for the in-app notification to link to the admin view
      'url' => $adminApplicationUrl,
      'status' => 'ready_for_processing', // Store the status that triggered the notification
    ];
  }

  // Implement toDatabase method if using the 'database' channel for custom storage logic beyond toArray.
  // public function toDatabase(object $notifiable): array { ... }
}
