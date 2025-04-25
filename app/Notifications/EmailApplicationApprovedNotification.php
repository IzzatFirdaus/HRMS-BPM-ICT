<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

// Import the model needed for this notification
use App\Models\EmailApplication;
use App\Models\User; // Import User model if needed for accessing applicant details

class EmailApplicationApprovedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public EmailApplication $emailApplication;

  /**
   * Create a new notification instance.
   *
   * @param EmailApplication $emailApplication The email application that was approved.
   */
  public function __construct(EmailApplication $emailApplication)
  {
    $this->emailApplication = $emailApplication;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    return ['mail']; // Specify that this notification should be sent via email
    // Add 'database' here if you want to store notifications in the database
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param object $notifiable The entity ($user) being notified (the applicant).
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail(object $notifiable): MailMessage
  {
    // You can access the user being notified via the $notifiable variable
    $applicantName = $notifiable->name ?? 'Pemohon'; // Use user's name if available

    return (new MailMessage)
      ->subject('Permohonan E-mel ICT MOTAC Diluluskan') // Email subject line
      ->greeting('Salam ' . $applicantName . ',') // Greeting to the applicant
      ->line('Sukacita dimaklumkan bahawa permohonan akaun e-mel ICT MOTAC anda telah diluluskan.') // Main message line 1
      ->line('Nombor Rujukan Permohonan: #' . $this->emailApplication->id) // Include application ID
      ->line('Status Permohonan: Diluluskan') // Explicitly state status
      ->line('Proses penyediaan akaun e-mel anda akan dilaksanakan oleh Bahagian Pengurusan Maklumat (BPM). Anda akan dimaklumkan melalui e-mel berasingan setelah akaun e-mel anda berjaya disediakan, termasuk maklumat akaun dan kata laluan sementara.') // Inform about next steps (provisioning)
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
      'status' => 'approved',
      // Add other relevant data you want to store in the database notification record
    ];
  }

  // Implement toDatabase method if using the 'database' channel
  // public function toDatabase(object $notifiable): array { ... }
}
