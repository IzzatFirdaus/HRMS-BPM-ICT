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

class EquipmentIssuedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public LoanApplication $loanApplication;
  public Collection $issuedTransactions; // Collection of LoanTransaction models for the issued items

  /**
   * Create a new notification instance.
   * This notification is sent to the applicant/responsible officer when equipment is issued.
   *
   * @param LoanApplication $loanApplication The loan application related to the issuance.
   * @param Collection<LoanTransaction> $issuedTransactions A collection of LoanTransaction records for the specific items issued.
   */
  public function __construct(LoanApplication $loanApplication, Collection $issuedTransactions)
  {
    $this->loanApplication = $loanApplication;
    $this->issuedTransactions = $issuedTransactions;
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

    // Format loan dates
    $startDate = $this->loanApplication->loan_start_date?->format('Y-m-d') ?? 'Tidak Dinyatakan';
    $endDate = $this->loanApplication->loan_end_date?->format('Y-m-d') ?? 'Tidak Dinyatakan';

    $mailMessage = (new MailMessage)
      ->subject('Peralatan Pinjaman ICT Anda Telah Dikeluarkan') // Email subject line
      ->greeting('Salam ' . $recipientName . ',') // Greeting
      ->line('Dimaklumkan bahawa peralatan pinjaman ICT bagi permohonan anda telah berjaya dikeluarkan.') // Main message
      ->line('**Nombor Rujukan Permohonan:** #' . $this->loanApplication->id)
      ->line('**Tujuan Permohonan:** ' . $this->loanApplication->purpose)
      ->line('**Tarikh Pinjaman:** ' . $startDate)
      ->line('**Tarikh Dijangka Pulang:** ' . $endDate)
      ->line('Berikut adalah senarai peralatan yang dikeluarkan untuk pinjaman ini:');

    // Add details for each issued equipment item from the transactions
    $mailMessage->line('---'); // Separator
    foreach ($this->issuedTransactions as $transaction) {
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
        // Optionally include accessory checklist for this item if stored per transaction/item
        // if ($transaction->accessories_checklist_on_issue) {
        //      $mailMessage->line('  Aksesori: ' . implode(', ', $transaction->accessories_checklist_on_issue));
        // }
      } else {
        $mailMessage->line('- Peralatan (Butiran tidak tersedia)'); // Fallback if equipment details are missing
      }
      $mailMessage->line('---'); // Separator
    }


    $mailMessage->line('Sila pastikan peralatan dijaga dengan baik sepanjang tempoh pinjaman dan dipulangkan tepat pada masanya.'); // Reminder
    $mailMessage->line('Sila hubungi Bahagian Pengurusan Maklumat (BPM) sekiranya terdapat sebarang isu atau jika anda memerlukan pelanjutan tempoh pinjaman (tertakluk kepada kelulusan).'); // Contact/Extension info

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
    $issuedItemsData = $this->issuedTransactions->map(function ($transaction) {
      $transaction->loadMissing('equipment');
      return [
        'transaction_id' => $transaction->id,
        'equipment_id' => $transaction->equipment_id,
        'asset_type' => $transaction->equipment->asset_type ?? 'N/A',
        'serial_number' => $transaction->equipment->serial_number ?? 'N/A',
        'tag_id' => $transaction->equipment->tag_id ?? 'N/A',
      ];
    })->toArray();


    return [
      // Optionally store notification data in a database table if using the 'database' channel
      'loan_application_id' => $this->loanApplication->id,
      'status' => 'equipment_issued',
      'message' => 'Peralatan untuk Permohonan Pinjaman #' . $this->loanApplication->id . ' telah dikeluarkan.',
      'issued_items' => $issuedItemsData, // Store details of issued items
    ];
  }

  // Implement toDatabase method if using the 'database' channel
  // public function toDatabase(object $notifiable): array { ... }
}
