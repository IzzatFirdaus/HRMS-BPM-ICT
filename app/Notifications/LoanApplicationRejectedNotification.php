<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

// Import the models needed for this notification
use App\Models\LoanApplication;
use App\Models\Approval; // Assuming you pass the Approval record that caused the rejection

class LoanApplicationRejectedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public LoanApplication $loanApplication;
  public Approval $rejectionApproval; // The specific approval record where rejection occurred

  /**
   * Create a new notification instance.
   *
   * @param LoanApplication $loanApplication The loan application that was rejected.
   * @param Approval $rejectionApproval The approval record that marked the application as rejected.
   */
  public function __construct(LoanApplication $loanApplication, Approval $rejectionApproval)
  {
    $this->loanApplication = $loanApplication;
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

    // Get the rejection reason from the Approval record's comments
    $rejectionReason = $this->rejectionApproval->comments ?? 'Tiada sebab dinyatakan.';

    return (new MailMessage)
      ->subject('Permohonan Pinjaman Peralatan ICT MOTAC Ditolak') // Email subject line
      ->greeting('Salam ' . $applicantName . ',') // Greeting to the applicant
      ->line('Dukacita dimaklumkan bahawa permohonan pinjaman peralatan ICT anda telah ditolak.') // Main message line 1
      ->line('Nombor Rujukan Permohonan: #' . $this->loanApplication->id) // Include application ID
      ->line('Tujuan Permohonan: ' . $this->loanApplication->purpose) // Include purpose
      ->line('Status Permohonan: Ditolak') // Explicitly state status
      ->line('Sebab Penolakan:') // Indicate rejection reason section
      ->line($rejectionReason) // Display the rejection reason
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
      'status' => 'rejected',
      'rejection_reason' => $this->rejectionApproval->comments,
      'approved_by_officer_id' => $this->rejectionApproval->officer_id,
      'purpose' => $this->loanApplication->purpose,
    ];
  }

  // Implement toDatabase method if using the 'database' channel
  // public function toDatabase(object $notifiable): array { ... }
}
