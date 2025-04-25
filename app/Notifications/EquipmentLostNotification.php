<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection; // Import Collection

// Import the models needed for this notification
use App\Models\LoanApplication;
use App\Models\LoanTransaction;
use App\Models\User; // Import User model if needed

class EquipmentLostNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public LoanApplication $loanApplication;
  public Collection $lostTransactions; // Collection of LoanTransaction models for the lost items

  /**
   * Create a new notification instance.
   * This notification is sent to the applicant/responsible officer when equipment from their loan is marked as lost.
   *
   * @param LoanApplication $loanApplication The loan application related to the lost equipment.
   * @param Collection<LoanTransaction> $lostTransactions A collection of LoanTransaction records for the specific items marked as lost.
   */
  public function __construct(LoanApplication $loanApplication, Collection $lostTransactions)
  {
    $this->loanApplication = $loanApplication;
    $this->lostTransactions = $lostTransactions;
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

    $mailMessage = (new MailMessage)
      ->subject('Tindakan Diperlukan: Peralatan Pinjaman ICT Dilaporkan Hilang') // Email subject line
      ->greeting('Salam ' . $recipientName . ',') // Greeting
      ->line('Dimaklumkan bahawa beberapa item peralatan yang dipinjam di bawah permohonan anda telah dilaporkan sebagai **hilang** atau tidak ditemui semasa proses pemulangan.') // Main message
      ->line('**Nombor Rujukan Permohonan:** #' . $this->loanApplication->id)
      ->line('Berdasarkan rekod kami, peralatan berikut telah dilaporkan hilang:');

    // Add details for each lost equipment item from the transactions
    $mailMessage->line('---'); // Separator
    foreach ($this->lostTransactions as $transaction) {
      // Ensure the equipment relationship is loaded on the transaction
      $transaction->loadMissing('equipment');
      $equipment = $transaction->equipment;

      if ($equipment) {
        $mailMessage->line(
          '- **' . ($equipment->asset_type ?? 'Peralatan') . '**'
            . ($equipment->brand ? ' (' . $equipment->brand . ')' : '')
            . ($equipment->model ? ' ' . $equipment->model : '')
        );
        $mailMessage->line('  Nombor Siri: ' . ($equipment->serial_number ?? 'Tidak Dinyatakan'));
        $mailMessage->line('  ID Tag MOTAC: ' . ($equipment->tag_id ?? 'Tidak Dinyatakan'));
        if ($transaction->return_notes) { // Include return notes if they describe the situation
          $mailMessage->line('  Catatan Pulangan: ' . $transaction->return_notes);
        }
      } else {
        $mailMessage->line('- Peralatan (Butiran tidak tersedia)'); // Fallback if equipment details are missing
      }
      $mailMessage->line('---'); // Separator
    }

    // Referencing the loan form's clause about responsibility
    $mailMessage->line('Merujuk kepada perakuan permohonan, kehilangan dan kekurangan pada peralatan pinjaman adalah di bawah tanggungjawab pemohon.');
    $mailMessage->line('Sila hubungi Bahagian Pengurusan Maklumat (BPM) MOTAC dengan segera untuk membincangkan perkara ini dan melaporkan insiden kehilangan secara rasmi.'); // Call to action for reporting

    $mailMessage->line('Sekiranya anda mempunyai sebarang pertanyaan lanjut atau memerlukan bantuan dalam proses pelaporan, sila hubungi Bahagian Pengurusan Maklumat (BPM).'); // Contact information

    // Optional: Add a button linking to the application/loan details page
    // ->action('Lihat Butiran Pinjaman', url('/my-loans/' . $this->loanApplication->id));

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
    // Prepare data for database storage
    $lostItemsData = $this->lostTransactions->map(function ($transaction) {
      $transaction->loadMissing('equipment');
      return [
        'transaction_id' => $transaction->id,
        'equipment_id' => $transaction->equipment_id,
        'asset_type' => $transaction->equipment->asset_type ?? 'N/A',
        'serial_number' => $transaction->equipment->serial_number ?? 'N/A',
        'tag_id' => $transaction->equipment->tag_id ?? 'N/A',
        'return_notes' => $transaction->return_notes ?? null,
      ];
    })->toArray();

    return [
      // Optionally store notification data in a database table if using the 'database' channel
      'loan_application_id' => $this->loanApplication->id,
      'status' => 'equipment_lost',
      'message' => 'Peralatan untuk Permohonan Pinjaman #' . $this->loanApplication->id . ' dilaporkan hilang.',
      'lost_items' => $lostItemsData, // Store details of lost items
    ];
  }

  // Implement toDatabase method if using the 'database' channel
  // public function toDatabase(object $notifiable): array { ... }
}
