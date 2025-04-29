<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Database\Eloquent\Model; // Import base Model for type hinting flexibility

// Import the model needed for this notification
use App\Models\EmailApplication;
use App\Models\User; // Import User model as the notifiable object is a User


/**
 * Class EmailProvisionedNotification
 *
 * Notification sent to the applicant when their requested MOTAC email account or user ID
 * has been successfully provisioned by BPM/ICT staff.
 * This notification includes the assigned credentials (excluding password) and next steps.
 * This notification can be sent via email and stored in the database.
 */
class EmailProvisionedNotification extends Notification implements ShouldQueue // Implement ShouldQueue if you want queuing
{
  use Queueable;

  /**
   * The email application model instance that has been provisioned.
   *
   * @var \App\Models\EmailApplication
   */
  protected EmailApplication $emailApplication; // Added property type hint

  /**
   * Create a new notification instance.
   *
   * @param \App\Models\EmailApplication $emailApplication The email application that was provisioned.
   * @return void
   */
  public function __construct(EmailApplication $emailApplication) // Added docblock
  {
    $this->emailApplication = $emailApplication;
    // While not strictly needed for the current toMail logic, loading the user
    // here can be beneficial if the notification is expanded or user data is needed elsewhere.
    // $this->emailApplication->loadMissing('user');
  }

  /**
   * Get the notification's delivery channels.
   * Determines how the notification will be sent (e.g., mail, database).
   * This notification is typically sent to the applicant.
   *
   * @param object $notifiable The notifiable entity (typically the applicant User model instance).
   * @return array<int, string> An array of channel names (e.g., 'mail', 'database').
   */
  public function via(object $notifiable): array // Added type hint for notifiable, refined docblock
  {
    // Send via mail and also store in database for in-app notifications
    return ['mail', 'database'];
  }

  /**
   * Get the mail representation of the notification.
   * Defines the content, subject, and structure of the email sent to the applicant regarding their provisioned email/user ID.
   *
   * @param object $notifiable The entity ($user) being notified (the applicant User model instance).
   * @return \Illuminate\Notifications\Messages\MailMessage The MailMessage instance.
   */
  public function toMail(object $notifiable): MailMessage // Refined docblock
  {
    // Access the applicant's name for the greeting safely
    // Use optional chaining (?->) for robustness, falling back to name then a generic greeting
    $applicantName = $notifiable->full_name ?? $notifiable->name ?? 'Pemohon';


    $mailMessage = (new MailMessage)
      ->subject('Akaun E-mel/ID Pengguna ICT MOTAC Anda Telah Disediakan') // More specific subject line
      ->greeting('Salam ' . $applicantName . ','); // Greeting to the applicant

    // Conditional content based on what was assigned (email or user ID)
    // Safely access properties using optional chaining (?->)
    if ($this->emailApplication->final_assigned_email) {
      $mailMessage->line('Akaun e-mel ICT MOTAC anda telah berjaya disediakan.');
      $mailMessage->line('Alamat e-mel anda ialah: **' . $this->emailApplication->final_assigned_email . '**');
      // Avoid sending passwords in email! Instruct users on how to set/retrieve password securely.
      $mailMessage->line('Anda akan menerima arahan berasingan mengenai cara untuk menetapkan kata laluan anda dan mengakses e-mel.');
    } elseif ($this->emailApplication->final_assigned_user_id) {
      $mailMessage->line('ID pengguna ICT MOTAC anda telah berjaya disediakan.');
      $mailMessage->line('ID Pengguna yang diberikan kepada anda ialah: **' . $this->emailApplication->final_assigned_user_id . '**');
      $mailMessage->line('Anda akan menerima arahan berasingan mengenai cara menggunakan ID Pengguna anda.');
    } else {
      // Fallback message if neither is assigned (should not happen if status is completed/provisioned)
      // Safely get application ID
      $applicationId = $this->emailApplication->id ?? 'N/A';
      $mailMessage->line('Proses penyediaan permohonan akaun e-mel/ID pengguna ICT anda (Ruj: #' . $applicationId . ') telah selesai.');
      $mailMessage->line('Sila semak sistem untuk butiran akaun anda atau nantikan maklumat lanjut.');
    }

    // Safely construct the action button URL
    $applicationUrl = '#'; // Default fallback URL
    if (isset($this->emailApplication->id)) {
      // Assuming an applicant route exists for viewing email application details
      $applicationUrl = url('/email-applications/' . $this->emailApplication->id);
    }

    // Add the action button only if a valid URL was constructed
    if ($applicationUrl !== '#') {
      $mailMessage->action('Lihat Status Permohonan', $applicationUrl); // Assuming a web route for viewing applications
    }

    $mailMessage->line('Terima kasih kerana menggunakan sistem!');

    return $mailMessage;
  }

  /**
   * Get the array representation of the notification.
   * This data is used when storing the notification in the database via the 'database' channel.
   *
   * @param object $notifiable The notifiable entity (typically the applicant User model instance).
   * @return array<string, mixed> An array of data to be stored in the database.
   */
  public function toArray(object $notifiable): array // Refined docblock
  {
    // Safely get the application ID and applicant ID
    $applicationId = $this->emailApplication->id ?? null;
    $applicantId = $this->emailApplication->user_id ?? null;

    // Safely get assigned credentials and status
    $assignedEmail = $this->emailApplication->final_assigned_email ?? null;
    $assignedUserId = $this->emailApplication->final_assigned_user_id ?? null;
    $status = $this->emailApplication->status ?? 'provisioned'; // Assuming 'provisioned' status triggered this


    // Determine message based on what was assigned
    $messageText = 'Proses penyediaan akaun/ID pengguna ICT anda telah selesai.';
    if ($assignedEmail) {
      $messageText = 'Akaun e-mel ICT anda telah disediakan.';
    } elseif ($assignedUserId) {
      $messageText = 'ID pengguna ICT anda telah disediakan.';
    }


    // Safely construct the URL for the in-app notification link to the applicant's view
    $applicationUrl = '#'; // Default fallback URL
    if (isset($this->emailApplication->id)) {
      $applicationUrl = url('/email-applications/' . $this->emailApplication->id); // Assuming an applicant route exists
    }


    return [
      // Store notification data in a database table
      'application_type' => 'Email Application',
      'application_id' => $applicationId,
      'applicant_id' => $applicantId, // Include applicant ID
      // Generic subject for in-app display
      'subject' => 'Akaun E-mel/ID Disediakan',
      // Message for in-app display
      'message' => $messageText,
      // URL for the in-app notification to link to the applicant's view
      'url' => $applicationUrl,
      'status' => $status, // Store the status that triggered the notification
      'assigned_email' => $assignedEmail, // Store the assigned email
      'assigned_user_id' => $assignedUserId, // Store the assigned user ID
    ];
  }

  // Implement toDatabase method if using the 'database' channel for custom storage logic beyond toArray.
  // public function toDatabase(object $notifiable): array { ... }
}
