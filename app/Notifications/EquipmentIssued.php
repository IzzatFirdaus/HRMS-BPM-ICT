<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;

use App\Models\LoanApplication;
use App\Models\LoanTransaction;
use App\Models\User;
use App\Models\Equipment;

class EquipmentIssued extends Notification implements ShouldQueue
{
  use Queueable;

  public LoanApplication $loanApplication;
  public Collection $issuedTransactions;

  public function __construct(LoanApplication $loanApplication, Collection $issuedTransactions)
  {
    // Eager load applicant & officer
    $this->loanApplication     = $loanApplication->loadMissing(['user', 'responsibleOfficer']);
    // Eager load equipment on each transaction
    $this->issuedTransactions  = $issuedTransactions->loadMissing('equipment');
  }

  public function via(object $notifiable): array
  {
    return ['mail'];
  }

  public function toMail(object $notifiable): MailMessage
  {
    $applicantName = $notifiable->getAttribute('full_name')
      ?? $notifiable->getAttribute('name')
      ?? 'Pemohon';
    $appId         = $this->loanApplication->getAttribute('id') ?? 'N/A';

    $mail = (new MailMessage)
      ->subject("Peralatan Pinjaman ICT Telah Dikeluarkan (Permohonan #{$appId})")
      ->greeting("Salam {$applicantName},")
      ->line("Peralatan pinjaman ICT untuk Permohonan #{$appId} telah dikeluarkan:")
      ->line('---');

    /** @var LoanTransaction $tx */
    foreach ($this->issuedTransactions as $tx) {
      $equipment = $tx->equipment;
      if ($equipment instanceof Equipment) {
        $assetType    = $equipment->getAttribute('asset_type') ?? 'Peralatan';
        $brand        = $equipment->getAttribute('brand');
        $model        = $equipment->getAttribute('model');
        $serialNumber = $equipment->getAttribute('serial_number') ?? 'Tidak Dinyatakan';
        $tagId        = $equipment->getAttribute('tag_id') ?? 'Tidak Dinyatakan';
        $notes        = $tx->getAttribute('issue_notes');

        $mail->line(
          "- **{$assetType}**" .
            ($brand ? " ({$brand})" : '') .
            ($model ? " {$model}" : '')
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

    $loanAppId = $this->loanApplication->getAttribute('id');
    if ($loanAppId) {
      $mail->action(
        'Lihat Butiran Pinjaman',
        url("/loan-applications/{$loanAppId}")
      );
    }

    return $mail->salutation('Sekian, terima kasih.');
  }

  public function toArray(object $notifiable): array
  {
    $items = $this->issuedTransactions->map(function (LoanTransaction $tx) {
      $equip = $tx->equipment;
      return [
        'transaction_id' => $tx->getAttribute('id'),
        'equipment_id'   => $tx->getAttribute('equipment_id'),
        'asset_type'     => $equip?->getAttribute('asset_type')     ?? 'N/A',
        'serial_number'  => $equip?->getAttribute('serial_number')  ?? 'N/A',
        'tag_id'         => $equip?->getAttribute('tag_id')         ?? 'N/A',
        'issue_notes'    => $tx->getAttribute('issue_notes')        ?? null,
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
      'status'                 => 'equipment_issued',
      'subject'                => 'Peralatan Pinjaman Dikeluarkan',
      'message'                => "Peralatan untuk Permohonan #{$loanAppId} telah dikeluarkan.",
      'url'                    => $loanAppId ? url("/loan-applications/{$loanAppId}") : '#',
      'issued_items'           => $items,
    ];
  }
}
