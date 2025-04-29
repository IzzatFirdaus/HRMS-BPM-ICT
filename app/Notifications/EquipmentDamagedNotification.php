<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

use App\Models\LoanApplication;
use App\Models\LoanTransaction;
use App\Models\User;
use App\Models\Equipment;

class EquipmentDamagedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public LoanApplication $loanApplication;
  public Collection $damagedTransactions;

  public function __construct(LoanApplication $loanApplication, Collection $damagedTransactions)
  {
    $this->loanApplication        = $loanApplication->loadMissing(['user', 'responsibleOfficer']);
    $this->damagedTransactions    = $damagedTransactions->loadMissing('equipment');
  }

  public function via(object $notifiable): array
  {
    return ['mail'];
  }

  public function toMail(object $notifiable): MailMessage
  {
    $recipientName    = $notifiable->full_name ?? $notifiable->name ?? 'Pemohon/Pegawai Bertanggungjawab';
    $applicationId    = $this->loanApplication->getAttribute('id') ?? 'N/A';

    $mail = (new MailMessage)
      ->subject('Makluman: Peralatan Pinjaman ICT Ditemui Rosak Semasa Pulangan')
      ->greeting('Salam ' . $recipientName . ',')
      ->line('Semasa penerimaan pulangan peralatan pinjaman ICT (Permohonan #' . $applicationId . '), beberapa item ditemui rosak:')
      ->line('---');

    /** @var LoanTransaction $transaction */
    foreach ($this->damagedTransactions as $transaction) {
      /** @var Equipment|null $equipment */
      $equipment = $transaction->equipment;

      if ($equipment instanceof Equipment) {
        $assetType    = $equipment->getAttribute('asset_type') ?? 'Peralatan';
        $brand        = $equipment->getAttribute('brand');
        $model        = $equipment->getAttribute('model');
        $serialNumber = $equipment->getAttribute('serial_number') ?? 'Tidak Dinyatakan';
        $tagId        = $equipment->getAttribute('tag_id') ?? 'Tidak Dinyatakan';
        $notes        = $transaction->getAttribute('return_notes');

        $mail->line(
          "- **{$assetType}**" .
            ($brand        ? " ({$brand})"        : '') .
            ($model        ? " {$model}"           : '')
        );
        $mail->line("  Nombor Siri: {$serialNumber}");
        $mail->line("  ID Tag MOTAC: {$tagId}");
        if ($notes) {
          $mail->line("  Catatan Pulangan: {$notes}");
        }
        $mail->line('---');
      } else {
        $tid = $transaction->getAttribute('id') ?? 'N/A';
        $mail->line("- Peralatan (Butiran tidak tersedia) - Transaksi ID: {$tid}");
        $mail->line('---');
      }
    }

    $mail->line('BPM akan menghubungi anda untuk tindakan lanjut jika perlu.')
      ->line('Sekiranya ada pertanyaan, sila hubungi BPM MOTAC.')
      ->salutation('Sekian, terima kasih.');

    // Action button
    $loanAppId = $this->loanApplication->getAttribute('id');
    if ($loanAppId) {
      $mail->action('Lihat Butiran Pinjaman', url("/loan-applications/{$loanAppId}"));
    }

    return $mail;
  }

  public function toArray(object $notifiable): array
  {
    $damaged = $this->damagedTransactions->map(function (LoanTransaction $transaction) {
      $equipment = $transaction->equipment;

      return [
        'transaction_id' => $transaction->getAttribute('id'),
        'equipment_id'   => $transaction->getAttribute('equipment_id'),
        'asset_type'     => $equipment?->getAttribute('asset_type')     ?? 'N/A',
        'brand'          => $equipment?->getAttribute('brand')          ?? null,
        'model'          => $equipment?->getAttribute('model')          ?? null,
        'serial_number'  => $equipment?->getAttribute('serial_number')  ?? 'N/A',
        'tag_id'         => $equipment?->getAttribute('tag_id')         ?? 'N/A',
        'return_notes'   => $transaction->getAttribute('return_notes')  ?? null,
      ];
    })->toArray();

    $loanAppId   = $this->loanApplication->getAttribute('id');
    $applicantId = $this->loanApplication->getAttribute('user_id');
    $officerId   = $this->loanApplication->getAttribute('responsible_officer_id');

    return [
      'application_type'       => 'Loan Application',
      'loan_application_id'    => $loanAppId,
      'applicant_id'           => $applicantId,
      'responsible_officer_id' => $officerId,
      'status'                 => 'equipment_damaged',
      'subject'                => 'Peralatan Ditemui Rosak',
      'message'                => "Peralatan pinjaman ICT bagi Permohonan #{$loanAppId} ditemui rosak.",
      'url'                    => $loanAppId ? url("/loan-applications/{$loanAppId}") : '#',
      'damaged_items'          => $damaged,
    ];
  }
}
