<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

// Import the model needed for this notification
use App\Models\EmailApplication;
use App\Models\User; // Import User model if needed

class EmailApplicationReadyForProcessingNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public EmailApplication $emailApplication;

  /**
   * Create a new notification instance.
   * This notification is sent to IT Admin/BPM when an email application is approved and ready for provisioning.
   *
   * @param EmailApplication $emailApplication The email application that is ready for processing.
   */
  public function __construct(EmailApplication $emailApplication)
  {
    $this->emailApplication = $emailApplication;
  }

  /**
   * Get the notification's delivery channels.
   * This notification is typically sent to a group of users (e.g., IT Admin role).
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    // 'notifiable' here would likely be a User model or a collection of Users (e.g., all IT Admins)
    return ['mail']; // Specify that this notification should be sent via email
    // Add 'database' here if you want to store notifications in the database (e.g., in an admin dashboard)
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param object $notifiable The entity (e.g., a User representing an IT Admin) being notified.
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail(object $notifiable): MailMessage
  {
    // Access applicant details via the emailApplication relationship
    $applicantName = $this->emailApplication->user->name ?? 'Pemohon Tidak Diketahui';
    $applicantIc = $this->emailApplication->user->ic_number ?? 'Tidak Diketahui';
    $proposedEmail = $this->emailApplication->proposed_email ?? 'Tiada Cadangan';
    $purpose = $this->emailApplication->purpose ?? 'Tiada Tujuan Dinyatakan';

    return (new MailMessage)
      ->subject('Permohonan E-mel Baru Diluluskan & Sedia Untuk Penyediaan') // Email subject line
      ->greeting('Salam Petugas BPM/ICT,') // Greeting to the IT Admin/BPM staff
      ->line('Terdapat permohonan akaun e-mel ICT MOTAC baru yang telah diluluskan dan sedia untuk proses penyediaan (provisioning).') // Main message
      ->line('**Nombor Rujukan Permohonan:** #' . $this->emailApplication->id)
      ->line('**Pemohon:** ' . $applicantName . ' (No. KP: ' . $applicantIc . ')')
      ->line('**Cadangan E-mel/ID:** ' . $proposedEmail)
      ->line('**Tujuan Permohonan:** ' . $purpose)
      ->line('Sila log masuk ke sistem untuk melihat butiran penuh permohonan dan melaksanakan proses penyediaan akaun e-mel.') // Call to action

      // Optional: Add a button linking to the application details page in the admin/BPM interface
      // Make sure this URL is correct for your application details route.
      // ->action('Lihat Permohonan', url('/admin/email-applications/' . $this->emailApplication->id));

      ->salutation('Terima kasih.'); // Closing
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
      'applicant_id' => $this->emailApplication->user_id,
      'status' => 'ready_for_processing',
      'subject' => 'Permohonan E-mel Baru Diluluskan & Sedia Untuk Penyediaan',
      'message' => 'Permohonan #' . $this->emailApplication->id . ' oleh ' . ($this->emailApplication->user->name ?? 'Pemohon') . ' sedia untuk penyediaan.',
    ];
  }

  // Implement toDatabase method if using the 'database' channel
  // public function toDatabase(object $notifiable): array { ... }
}
