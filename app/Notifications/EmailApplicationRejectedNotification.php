<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

// Import the models needed for this notification
use App\Models\EmailApplication;
use App\Models\Approval; // Assuming you pass the Approval record that caused the rejection

class EmailApplicationRejectedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public EmailApplication $emailApplication;
  public Approval $rejectionApproval; // The specific approval record where rejection occurred

  /**
   * Create a new notification instance.
   *
   * @param EmailApplication $emailApplication The email application that was rejected.
   * @param Approval $rejectionApproval The approval record that marked the application as rejected.
   */
  public function __construct(EmailApplication $emailApplication, Approval $rejectionApproval)
  {
    $this->emailApplication = $emailApplication;
    $this->rejectionApproval = $rejectionApproval;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    return ['mail']; // Specify that this notification should be sent via email
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param object $notifiable The entity ($user) being notified.
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail(object $notifiable): MailMessage
  {
    // You can access the user being notified via the $notifiable variable
    $applicantName = $notifiable->name ?? 'Pemohon'; // Use user's name if available

    // Get the rejection reason from the Approval record's comments
    $rejectionReason = $this->rejectionApproval->comments ?? 'Tiada sebab dinyatakan.';

    return (new MailMessage)
      ->subject('Permohonan E-mel ICT MOTAC Ditolak') // Email subject line
      ->greeting('Salam ' . $applicantName . ',') // Greeting to the applicant
      ->line('Dukacita dimaklumkan bahawa permohonan akaun e-mel ICT MOTAC anda telah ditolak.') // Main message line 1
      ->line('Nombor Rujukan Permohonan: #' . $this->emailApplication->id) // Include application ID
      ->line('Status Permohonan: Ditolak') // Explicitly state status
      ->line('Sebab Penolakan:') // Indicate rejection reason section
      ->line($rejectionReason) // Display the rejection reason
      ->line('Sekiranya anda memerlukan maklumat lanjut, sila hubungi Bahagian Pengurusan Maklumat (BPM) MOTAC.') // Contact information
      ->salutation('Sekian, terima kasih.'); // Closing

    // Optional: Add a button linking to the application details page (if you have one)
    // ->action('Lihat Permohonan Anda', url('/email-applications/' . $this->emailApplication->id)); // Example button link
  }

  /**
   * Get the array representation of the notification.
   *
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    return [
      // Optionally store notification data in a database table if using the 'database' channel
      'email_application_id' => $this->emailApplication->id,
      'status' => 'rejected',
      'rejection_reason' => $this->rejectionApproval->comments,
      'approved_by_officer_id' => $this->rejectionApproval->officer_id,
    ];
  }

  // Implement toDatabase method if using the 'database' channel
  // public function toDatabase(object $notifiable): array { ... }
}
