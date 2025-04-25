<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

// Import the model needed for this notification
use App\Models\LoanApplication;
use App\Models\User; // Import User model if needed

class EquipmentReturnReminderNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public LoanApplication $loanApplication;
  public int $daysUntilReturn; // Number of days until the return date

  /**
   * Create a new notification instance.
   * This notification is sent to remind the applicant/responsible officer about an upcoming return date.
   *
   * @param LoanApplication $loanApplication The loan application requiring return.
   * @param int $daysUntilReturn The number of days remaining until the return date.
   */
  public function __construct(LoanApplication $loanApplication, int $daysUntilReturn)
  {
    $this->loanApplication = $loanApplication;
    $this->daysUntilReturn = $daysUntilReturn;
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

    $mailMessage = (new MailMessage)
      ->subject('Peringatan: Tarikh Pulang Peralatan Pinjaman ICT Anda Mendekati (' . $endDate . ')') // Email subject line including date
      ->greeting('Salam ' . $recipientName . ',') // Greeting
      ->line('Ini adalah peringatan mesra mengenai pinjaman peralatan ICT anda.') // Main message
      ->line('Tarikh dijangka pulang bagi permohonan anda adalah pada **' . $endDate . '**.') // Mention the specific date

      ->line(
        $this->daysUntilReturn > 0
          ? 'Terdapat lagi **' . $this->daysUntilReturn . ' hari** sebelum tarikh pulang.'
          : 'Tarikh pulang adalah pada hari ini.' // Message if return date is today
      );

    $mailMessage->line('Sila pastikan peralatan pinjaman dipulangkan ke Bahagian Pengurusan Maklumat (BPM) MOTAC pada atau sebelum tarikh tersebut.'); // Instruction for return location/timing

    // Optional: Add a button linking to the application/loan details page
    // ->action('Lihat Butiran Pinjaman', url('/my-loans/' . $this->loanApplication->id));

    $mailMessage->line('Sekiranya anda memerlukan maklumat lanjut atau menghadapi sebarang isu, sila hubungi Bahagian Pengurusan Maklumat (BPM).'); // Contact information

    $mailMessage->salutation('Sekian, terima kasih.'); // Closing

    return $mailMessage;
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
      'status' => 'return_reminder',
      'message' => 'Peringatan pulangan peralatan untuk Permohonan Pinjaman #' . $this->loanApplication->id . '. Tarikh pulang: ' . $this->loanApplication->loan_end_date?->format('Y-m-d'),
      'days_until_return' => $this->daysUntilReturn,
    ];
  }

  // Implement toDatabase method if using the 'database' channel
  // public function toDatabase(object $notifiable): array { ... }
}
