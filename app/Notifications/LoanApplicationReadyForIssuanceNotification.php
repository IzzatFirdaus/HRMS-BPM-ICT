<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

// Import the model needed for this notification
use App\Models\LoanApplication;
use App\Models\User; // Import User model if needed

class LoanApplicationReadyForIssuanceNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public LoanApplication $loanApplication;

  /**
   * Create a new notification instance.
   * This notification is sent to BPM staff when a loan application is approved and ready for issuance.
   *
   * @param LoanApplication $loanApplication The loan application that is ready for issuance.
   */
  public function __construct(LoanApplication $loanApplication)
  {
    $this->loanApplication = $loanApplication;
  }

  /**
   * Get the notification's delivery channels.
   * This notification is typically sent to a group of users (e.g., BPM Staff role).
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    // 'notifiable' here would likely be a User model or a collection of Users (e.g., all BPM staff)
    return ['mail']; // Specify that this notification should be sent via email
    // Add 'database' here if you want to store notifications in the database (e.g., in a BPM dashboard)
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param object $notifiable The entity (e.g., a User representing BPM staff) being notified.
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail(object $notifiable): MailMessage
  {
    // Access applicant details via the loanApplication relationship
    $applicantName = $this->loanApplication->user->name ?? 'Pemohon Tidak Diketahui';
    $purpose = $this->loanApplication->purpose ?? 'Tiada Tujuan Dinyatakan';
    $location = $this->loanApplication->location ?? 'Tidak Dinyatakan';
    $startDate = $this->loanApplication->loan_start_date?->format('Y-m-d') ?? 'Tidak Dinyatakan';
    $endDate = $this->loanApplication->loan_end_date?->format('Y-m-d') ?? 'Tidak Dinyatakan';


    return (new MailMessage)
      ->subject('Permohonan Pinjaman Peralatan ICT Baru Diluluskan & Sedia Untuk Pengeluaran') // Email subject line
      ->greeting('Salam Petugas BPM,') // Greeting to the BPM staff
      ->line('Terdapat permohonan pinjaman peralatan ICT baru yang telah diluluskan dan sedia untuk proses pengeluaran (issuance).') // Main message
      ->line('**Nombor Rujukan Permohonan:** #' . $this->loanApplication->id)
      ->line('**Pemohon:** ' . $applicantName)
      ->line('**Tujuan Permohonan:** ' . $purpose)
      ->line('**Lokasi Penggunaan:** ' . $location)
      ->line('**Tarikh Pinjaman:** ' . $startDate)
      ->line('**Tarikh Dijangka Pulang:** ' . $endDate)
      ->line('Sila log masuk ke sistem untuk melihat butiran penuh permohonan, menyemak inventori peralatan, dan merekodkan pengeluaran peralatan.') // Call to action

      // Optional: Add a button linking to the application details/issuance page in the BPM interface
      // Make sure this URL is correct for your application details/checklist route.
      // ->action('Lihat Permohonan & Rekod Pengeluaran', url('/bpm/loan-applications/' . $this->loanApplication->id . '/checklist'));

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
      'loan_application_id' => $this->loanApplication->id,
      'applicant_id' => $this->loanApplication->user_id,
      'status' => 'ready_for_issuance',
      'subject' => 'Permohonan Pinjaman Peralatan ICT Baru Diluluskan & Sedia Untuk Pengeluaran',
      'message' => 'Permohonan Pinjaman #' . $this->loanApplication->id . ' oleh ' . ($this->loanApplication->user->name ?? 'Pemohon') . ' sedia untuk pengeluaran.',
    ];
  }

  // Implement toDatabase method if using the 'database' channel
  // public function toDatabase(object $notifiable): array { ... }
}
