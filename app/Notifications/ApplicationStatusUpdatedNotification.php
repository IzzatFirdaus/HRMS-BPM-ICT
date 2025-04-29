<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Database\Eloquent\Model; // Import base Model for type hinting flexibility

// Import the models that can be associated with this notification
use App\Models\EmailApplication;
use App\Models\LoanApplication;
use App\Models\User; // Import User model as the notifiable object is a User


/**
 * Class ApplicationStatusUpdatedNotification
 *
 * Notification sent to an applicant when the status of their application (e.g., Email or Loan) changes.
 * This notification can be sent via email and optionally stored in the database.
 */
class ApplicationStatusUpdatedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * The application model instance whose status was updated.
   * Can be an instance of EmailApplication, LoanApplication, or any model intended to be notified about.
   *
   * @var \App\Models\EmailApplication|\App\Models\LoanApplication|Model
   */
  public $application;

  /**
   * The previous status of the application.
   *
   * @var string
   */
  public string $oldStatus;

  /**
   * The new status of the application.
   *
   * @var string
   */
  public string $newStatus;

  /**
   * Create a new notification instance.
   * This notification is sent to the applicant when their application status changes.
   *
   * @param \App\Models\EmailApplication|\App\Models\LoanApplication|Model $application The application whose status was updated.
   * @param string $oldStatus The previous status of the application.
   * @param string $newStatus The new status of the application.
   * @return void
   */
  public function __construct($application, string $oldStatus, string $newStatus)
  {
    $this->application = $application;
    $this->oldStatus = $oldStatus;
    $this->newStatus = $newStatus;
    // Ensure the applicant user is loaded on the application model to avoid N+1 issues in toMail/toArray
    $this->application->loadMissing('user');
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
   * Defines the content, subject, and structure of the email sent to the applicant.
   *
   * @param object $notifiable The entity ($user) being notified (the applicant).
   * @return \Illuminate\Notifications\Messages\MailMessage The MailMessage instance.
   */
  public function toMail(object $notifiable): MailMessage // Refined docblock
  {
    // Access the applicant's name for the greeting safely
    $applicantName = isset($notifiable->full_name) ? $notifiable->full_name : (isset($notifiable->name) ? $notifiable->name : 'Pemohon');

    // Determine application type for clarity
    $applicationType = $this->application instanceof EmailApplication ? 'E-mel ICT' : 'Pinjaman Peralatan ICT';
    // Safely get application ID
    $applicationId = isset($this->application->id) ? $this->application->id : 'N/A';
    // Get a summary/purpose for the application safely
    $applicationSummary = isset($this->application->purpose) ? $this->application->purpose : ($this->application instanceof EmailApplication ? 'Permohonan Akaun E-mel' : 'Permohonan Pinjaman');


    $mailMessage = (new MailMessage)
      ->subject("Status Permohonan {$applicationType} Anda Dikemaskini (#{$applicationId})") // Email subject includes type and ID
      ->greeting('Salam ' . $applicantName . ',') // Greeting to the applicant
      ->line("Status permohonan {$applicationType} anda telah dikemaskini dalam sistem.") // Main message
      ->line('**Nombor Rujukan Permohonan:** #' . $applicationId)
      ->line('**Ringkasan:** ' . $applicationSummary)
      ->line("Status terdahulu: **" . $this->oldStatus . "**") // Show previous status
      ->line("Status terkini: **" . $this->newStatus . "**") // Show new status
      ->line('Sila log masuk ke sistem untuk melihat butiran penuh permohonan anda.'); // Call to action


    // Optional: Add a button linking to the application details page
    // This URL would depend on your routing structure for viewing application details.
    $applicationUrl = '#'; // Default fallback URL
    if ($this->application instanceof EmailApplication && isset($this->application->id)) {
      $applicationUrl = url('/email-applications/' . $this->application->id); // Assuming a route exists
    } elseif ($this->application instanceof LoanApplication && isset($this->application->id)) {
      $applicationUrl = url('/loan-applications/' . $this->application->id); // Assuming a route exists
    }

    if ($applicationUrl !== '#') {
      $mailMessage->action('Lihat Permohonan', $applicationUrl);
    }


    return $mailMessage->salutation('Sekian, terima kasih.'); // Closing
    // Optionally add a footer with contact information
    // ->salutation('Sekian,')
    // ->line('Unit ICT MOTAC');
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
    // Determine application type for database storage display
    $applicationType = $this->application instanceof EmailApplication ? 'Email Application' : 'Loan Application';

    // Safely get application ID and applicant ID
    $applicationId = isset($this->application->id) ? $this->application->id : null;
    $applicantId = isset($this->application->user_id) ? $this->application->user_id : null;
    // Get the applicant's name for the message safely
    $applicantName = isset($notifiable->full_name) ? $notifiable->full_name : (isset($notifiable->name) ? $notifiable->name : 'Pemohon');


    // Determine URL for in-app notification link
    $applicationUrl = '#'; // Default fallback URL
    if ($this->application instanceof EmailApplication && isset($this->application->id)) {
      $applicationUrl = url('/email-applications/' . $this->application->id); // Assuming a route exists
    } elseif ($this->application instanceof LoanApplication && isset($this->application->id)) {
      $applicationUrl = url('/loan-applications/' . $this->application->id); // Assuming a route exists
    }


    return [
      // Store notification data in a database table if using the 'database' channel
      'application_type' => $applicationType,
      'application_id' => $applicationId,
      'applicant_id' => $applicantId,
      // Generic subject for in-app display
      'subject' => "Status Permohonan Dikemaskini (#{$applicationId})",
      // Message for in-app display
      'message' => "Status permohonan {$applicationType} anda (#{$applicationId}) telah dikemaskini dari **{$this->oldStatus}** ke **{$this->newStatus}**.",
      // URL for the in-app notification to link to
      'url' => $applicationUrl,
      'old_status' => $this->oldStatus,
      'new_status' => $this->newStatus,
    ];
  }

  // Implement toDatabase method if using the 'database' channel for custom storage logic
  // public function toDatabase(object $notifiable): array { ... }
}
