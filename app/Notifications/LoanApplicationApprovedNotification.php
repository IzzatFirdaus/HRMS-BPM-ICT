<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

// Import the model needed for this notification
use App\Models\LoanApplication;
use App\Models\User; // Import User model if needed for accessing applicant details

class LoanApplicationApprovedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public LoanApplication $loanApplication;

  /**
   * Create a new notification instance.
   *
   * @param LoanApplication $loanApplication The loan application that was approved.
   */
  public function __construct(LoanApplication $loanApplication)
  {
    $this->loanApplication = $loanApplication;
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
      ->subject('Permohonan Pinjaman Peralatan ICT MOTAC Diluluskan') // Email subject line
      ->greeting('Salam ' . $applicantName . ',') // Greeting to the applicant
      ->line('Sukacita dimaklumkan bahawa permohonan pinjaman peralatan ICT anda telah diluluskan.') // Main message line 1
      ->line('Nombor Rujukan Permohonan: #' . $this->loanApplication->id) // Include application ID
      ->line('Tujuan Permohonan: ' . $this->loanApplication->purpose) // Include purpose
      ->line('Status Permohonan: Diluluskan') // Explicitly state status
      ->line('Peralatan yang diluluskan boleh diambil di Bahagian Pengurusan Maklumat (BPM) MOTAC pada tarikh pinjaman yang ditetapkan.') // Inform about next steps (issuance)
      ->line('Sila bawa bersama salinan borang permohonan yang telah lengkap semasa mengambil peralatan.') // Instruction for pickup
      ->line('Sekiranya anda memerlukan maklumat lanjut, sila hubungi Bahagian Pengurusan Maklumat (BPM) MOTAC.') // Contact information
      ->salutation('Sekian, terima kasih.'); // Closing

    // Optional: Add a button linking to the application details page (if you have one)
    // ->action('Lihat Permohonan Anda', url('/loan-applications/' . $this->loanApplication->id)); // Example button link
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
      'loan_application_id' => $this->loanApplication->id,
      'status' => 'approved',
      'purpose' => $this->loanApplication->purpose,
      // Add other relevant data you want to store in the database notification record
    ];
  }

  // Implement toDatabase method if using the 'database' channel
  // public function toDatabase(object $notifiable): array { ... }
}
