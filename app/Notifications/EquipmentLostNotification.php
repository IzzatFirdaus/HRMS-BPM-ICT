<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;

use App\Models\LoanApplication;
use App\Models\LoanTransaction;
use App\Models\Equipment;
use App\Models\User;

class EquipmentLostNotification extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * @var LoanApplication
   */
  public LoanApplication $loanApplication;

  /**
   * @var Collection<int, LoanTransaction>
   */
  public Collection $lostTransactions;

  /**
   * Create a new notification instance.
   *
   * @param LoanApplication $loanApplication
   * @param Collection<int, LoanTransaction> $lostTransactions
   */
  public function __construct(LoanApplication $loanApplication, Collection $lostTransactions)
  {
    // Eager-load relationships
    $this->loanApplication  = $loanApplication->loadMissing(['user', 'responsibleOfficer']);
    $this->lostTransactions = $lostTransactions->loadMissing('equipment');
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param  mixed  $notifiable
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    return ['mail'];
  }

  /**
   * Build the mail representation of the notification.
   *
   * @param  mixed  $notifiable
   * @return MailMessage
   */
  public function toMail(object $notifiable): MailMessage
  {
    $recipientName = $notifiable->getAttribute('full_name')
      ?? $notifiable->getAttribute('name')
      ?? 'Pemohon/Pegawai Bertanggungjawab';

    $appId = $this->loanApplication->getAttribute('id') ?? 'N/A';

    $mail = (new MailMessage)
      ->subject("Tindakan Diperlukan: Peralatan Pinjaman ICT Dilaporkan Hilang")
      ->greeting("Salam {$recipientName},")
      ->line("Permohonan #: **{$appId}** telah dilaporkan mengandungi peralatan hilang.")
      ->line('Senarai peralatan yang dilaporkan hilang:')
      ->line('---');

    /** @var LoanTransaction $tx */
    foreach ($this->lostTransactions as $tx) {
      /** @var Equipment|null $equip */
      $equip = $tx->equipment;

      if ($equip instanceof Equipment) {
        $type         = $equip->getAttribute('asset_type')    ?? 'Peralatan';
        $brand        = $equip->getAttribute('brand');
        $model        = $equip->getAttribute('model');
        $serial       = $equip->getAttribute('serial_number') ?? 'Tidak Dinyatakan';
        $tagId        = $equip->getAttribute('tag_id')         ?? 'Tidak Dinyatakan';
        $returnNotes  = $tx->getAttribute('return_notes');

        $mail->line(
          "- **{$type}**"
            . ($brand ? " ({$brand})" : '')
            . ($model ? " {$model}" : '')
        );
        $mail->line("  Nombor Siri: {$serial}");
        $mail->line("  ID Tag MOTAC: {$tagId}");
        if ($returnNotes) {
          $mail->line("  Catatan Pulangan: {$returnNotes}");
        }
      } else {
        $txId = $tx->getAttribute('id') ?? 'N/A';
        $mail->line("- Peralatan (Butiran tidak tersedia) — Transaksi ID: {$txId}");
      }

      $mail->line('---');
    }

    $appUrl = $appId !== 'N/A'
      ? url("/loan-applications/{$appId}")
      : '#';

    if ($appUrl !== '#') {
      $mail->action('Lihat Butiran Pinjaman', $appUrl);
    }

    return $mail->salutation('Sekian, terima kasih.');
  }

  /**
   * Get the array representation for database channel.
   *
   * @param  mixed  $notifiable
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    $items = $this->lostTransactions->map(function (LoanTransaction $tx) {
      $equip = $tx->equipment;
      return [
        'transaction_id' => $tx->getAttribute('id'),
        'equipment_id'   => $tx->getAttribute('equipment_id'),
        'asset_type'     => $equip?->getAttribute('asset_type')    ?? 'N/A',
        'serial_number'  => $equip?->getAttribute('serial_number') ?? 'N/A',
        'tag_id'         => $equip?->getAttribute('tag_id')        ?? 'N/A',
        'return_notes'   => $tx->getAttribute('return_notes')      ?? null,
      ];
    })->toArray();

    $loanAppId = $this->loanApplication->getAttribute('id');
    $applicant = $this->loanApplication->getAttribute('user_id');
    $officer   = $this->loanApplication->getAttribute('responsible_officer_id');

    return [
      'application_type'       => 'Loan Application',
      'loan_application_id'    => $loanAppId,
      'applicant_id'           => $applicant,
      'responsible_officer_id' => $officer,
      'status'                 => 'equipment_lost',
      'subject'                => 'Peralatan Dilaporkan Hilang',
      'message'                => "Peralatan bagi Permohonan #{$loanAppId} telah dilaporkan hilang.",
      'url'                    => $loanAppId ? url("/loan-applications/{$loanAppId}") : '#',
      'lost_items'             => $items,
    ];
  }
}
