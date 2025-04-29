<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;

use App\Models\LoanApplication;
use App\Models\LoanTransaction;
use App\Models\User;
use App\Models\Equipment;

class EquipmentIssuedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * @var LoanApplication
   */
  public LoanApplication $loanApplication;

  /**
   * @var Collection<int, LoanTransaction>
   */
  public Collection $issuedTransactions;

  /**
   * Create a new notification instance.
   *
   * @param LoanApplication $loanApplication
   * @param Collection<int, LoanTransaction> $issuedTransactions
   */
  public function __construct(LoanApplication $loanApplication, Collection $issuedTransactions)
  {
    // Eager‐load relationships to avoid N+1
    $this->loanApplication    = $loanApplication->loadMissing(['user', 'responsibleOfficer']);
    $this->issuedTransactions = $issuedTransactions->loadMissing('equipment');
  }

  /**
   * Channels the notification will be sent on.
   *
   * @param  mixed  $notifiable
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    return ['mail'];
  }

  /**
   * Build the mail message.
   *
   * @param  mixed  $notifiable
   * @return MailMessage
   */
  public function toMail(object $notifiable): MailMessage
  {
    // Greeting
    $applicantName = $notifiable->getAttribute('full_name')
      ?? $notifiable->getAttribute('name')
      ?? 'Pemohon';

    // Core details
    $applicationId = $this->loanApplication->getAttribute('id') ?? 'N/A';
    $purpose       = $this->loanApplication->getAttribute('purpose') ?? 'Tidak Dinyatakan';
    $location      = $this->loanApplication->getAttribute('location') ?? 'Tidak Dinyatakan';
    $startDate     = optional($this->loanApplication->getAttribute('loan_start_date'))->format('Y-m-d') ?? 'Tidak Dinyatakan';
    $endDate       = optional($this->loanApplication->getAttribute('loan_end_date'))->format('Y-m-d')   ?? 'Tidak Dinyatakan';

    $mail = (new MailMessage)
      ->subject("Peralatan Pinjaman ICT Telah Dikeluarkan (Permohonan #{$applicationId})")
      ->greeting("Salam {$applicantName},")
      ->line("Permohonan #: **{$applicationId}**")
      ->line("Tujuan Permohonan: {$purpose}")
      ->line("Lokasi Penggunaan: {$location}")
      ->line("Tarikh Pinjaman: {$startDate}")
      ->line("Tarikh Dijangka Pulang: {$endDate}")
      ->line('')
      ->line('Berikut adalah senarai peralatan yang telah dikeluarkan:')
      ->line('---');

    /** @var LoanTransaction $tx */
    foreach ($this->issuedTransactions as $tx) {
      /** @var Equipment|null $equipment */
      $equipment = $tx->equipment;

      if ($equipment instanceof Equipment) {
        $assetType    = $equipment->getAttribute('asset_type')     ?? 'Peralatan';
        $brand        = $equipment->getAttribute('brand');
        $model        = $equipment->getAttribute('model');
        $serialNumber = $equipment->getAttribute('serial_number')  ?? 'Tidak Dinyatakan';
        $tagId        = $equipment->getAttribute('tag_id')         ?? 'Tidak Dinyatakan';
        $notes        = $tx->getAttribute('issue_notes');

        $mail->line(
          "- **{$assetType}**"
            . ($brand ? " ({$brand})" : '')
            . ($model ? " {$model}" : '')
        );
        $mail->line("  Nombor Siri: {$serialNumber}");
        $mail->line("  ID Tag MOTAC: {$tagId}");
        if ($notes) {
          $mail->line("  Catatan Pengeluaran: {$notes}");
        }
      } else {
        $txId = $tx->getAttribute('id') ?? 'N/A';
        $mail->line("- Peralatan (Butiran tidak tersedia) — Transaksi ID: {$txId}");
      }

      $mail->line('---');
    }

    // Action button to loan-application details
    $loanAppId = $this->loanApplication->getAttribute('id');
    if ($loanAppId) {
      $mail->action('Lihat Butiran Pinjaman', url("/loan-applications/{$loanAppId}"));
    }

    return $mail->salutation('Sekian, terima kasih.');
  }

  /**
   * Array payload for “database” channel.
   *
   * @param  mixed  $notifiable
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    $items = $this->issuedTransactions
      ->map(function (LoanTransaction $tx) {
        $equip = $tx->equipment;
        return [
          'transaction_id' => $tx->getAttribute('id'),
          'equipment_id'   => $tx->getAttribute('equipment_id'),
          'asset_type'     => $equip?->getAttribute('asset_type')    ?? 'N/A',
          'serial_number'  => $equip?->getAttribute('serial_number') ?? 'N/A',
          'tag_id'         => $equip?->getAttribute('tag_id')        ?? 'N/A',
          'issue_notes'    => $tx->getAttribute('issue_notes')       ?? null,
        ];
      })
      ->toArray();

    $loanAppId = $this->loanApplication->getAttribute('id');
    $applicant = $this->loanApplication->getAttribute('user_id');
    $officer   = $this->loanApplication->getAttribute('responsible_officer_id');

    return [
      'application_type'       => 'Loan Application',
      'loan_application_id'    => $loanAppId,
      'applicant_id'           => $applicant,
      'responsible_officer_id' => $officer,
      'status'                 => 'equipment_issued',
      'subject'                => 'Peralatan Pinjaman Dikeluarkan',
      'message'                => "Peralatan bagi Permohonan #{$loanAppId} telah dikeluarkan.",
      'url'                    => $loanAppId ? url("/loan-applications/{$loanAppId}") : '#',
      'issued_items'           => $items,
    ];
  }
}
