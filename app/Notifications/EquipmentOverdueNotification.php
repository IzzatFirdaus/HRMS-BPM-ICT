<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

// Import the model needed for this notification
use App\Models\LoanApplication;
use App\Models\User; // Import User model if needed
use Illuminate\Support\Carbon; // Import Carbon for date formatting

class EquipmentOverdueNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public LoanApplication $loanApplication;

  /**
   * Create a new notification instance.
   * This notification is sent to inform the applicant/responsible officer that their loan is overdue.
   *
   * @param LoanApplication $loanApplication The loan application that is overdue.
   */
  public function __construct(LoanApplication $loanApplication)
  {
    $this->loanApplication = $loanApplication;
  }

  /**
   * Get the notification's delivery channels.
   * This notification is typically sent to the applicant and optionally the responsible officer.
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    // 'notifiable' here would be the User model (the applicant or responsible officer)
    return ['mail']; // Specify that this notification should be sent via email
    // Add 'database' here if you want to store notifications in the database
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param object $notifiable The entity ($user) being notified (applicant or responsible officer).
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail(object $notifiable): MailMessage
  {
    // Determine the recipient's name for the greeting
    $recipientName = $notifiable->name ?? 'Pemohon/Pegawai Bertanggungjawab';

    // Format expected return date
    $endDate = $this->loanApplication->loan_end_date?->format('Y-m-d') ?? 'Tidak Dinyatakan';

    return (new MailMessage)
      ->subject('Tindakan Diperlukan: Peralatan Pinjaman ICT Anda Telah Lewat Pulang (' . $endDate . ')') // Email subject line
      ->greeting('Salam ' . $recipientName . ',') // Greeting
      ->line('Dimaklumkan bahawa peralatan pinjaman ICT di bawah tanggungjawab anda telah **lewat pulang**.') // Main message
      ->line('**Nombor Rujukan Permohonan:** #' . $this->loanApplication->id)
      ->line('**Tujuan Permohonan:** ' . $this->loanApplication->purpose)
      ->line('**Tarikh Dijangka Pulang:** ' . $endDate) // Show the overdue date
      ->line('Sila pulangkan peralatan tersebut ke Bahagian Pengurusan Maklumat (BPM) MOTAC dengan serta-merta.') // Call to action
      ->line('Sekiranya terdapat sebab kelewatan atau isu lain, sila hubungi Bahagian Pengurusan Maklumat (BPM) secepat mungkin untuk makluman dan tindakan selanjutnya.') // Contact information and urgency

      // Optional: Add a button linking to the application/loan details page
      // ->action('Lihat Butiran Pinjaman', url('/my-loans/' . $this->loanApplication->id));

      ->salutation('Sekian, terima kasih.'); // Closing;
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
      'status' => 'overdue',
      'message' => 'Peralatan untuk Permohonan Pinjaman #' . $this->loanApplication->id . ' telah lewat pulang sejak ' . $this->loanApplication->loan_end_date?->format('Y-m-d'),
      'overdue_date' => $this->loanApplication->loan_end_date,
    ];
  }

  // Implement toDatabase method if using the 'database' channel
  // public function toDatabase(object $notifiable): array { ... }
}
