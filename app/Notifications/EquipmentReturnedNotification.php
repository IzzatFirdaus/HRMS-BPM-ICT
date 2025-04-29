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

class EquipmentReturnedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  /** @var LoanApplication */
  public LoanApplication $loanApplication;

  /** @var Collection<int, LoanTransaction> */
  public Collection $returnedTransactions;

  /**
   * @param LoanApplication                  $loanApplication
   * @param Collection<int, LoanTransaction> $returnedTransactions
   */
  public function __construct(LoanApplication $loanApplication, Collection $returnedTransactions)
  {
    // Eager-load related users and officers
    $this->loanApplication      = $loanApplication->loadMissing([
      'user',
      'responsibleOfficer',
      'returnAcceptingOfficer',
    ]);
    // Eager-load equipment on each transaction
    $this->returnedTransactions = $returnedTransactions->loadMissing('equipment');
  }

  /**
   * @param  mixed  $notifiable
   * @return array<int,string>
   */
  public function via(object $notifiable): array
  {
    return ['mail'];
  }

  /**
   * @param  mixed  $notifiable
   * @return MailMessage
   */
  public function toMail(object $notifiable): MailMessage
  {
    $recipientName = $notifiable->getAttribute('full_name')
      ?? $notifiable->getAttribute('name')
      ?? 'Pemohon/Pegawai Bertanggungjawab';

    $appId         = $this->loanApplication->getAttribute('id')            ?? 'N/A';
    $purpose       = $this->loanApplication->getAttribute('purpose')       ?? 'Tidak Dinyatakan';
    $startDate     = $this->loanApplication->getAttribute('loan_start_date')
      ? $this->loanApplication->getAttribute('loan_start_date')->format('Y-m-d')
      : 'Tidak Dinyatakan';
    $expectedDate  = $this->loanApplication->getAttribute('loan_end_date')
      ? $this->loanApplication->getAttribute('loan_end_date')->format('Y-m-d')
      : 'Tidak Dinyatakan';
    $actualReturn  = $this->returnedTransactions->isNotEmpty()
      ? ($this->returnedTransactions->first()->getAttribute('returned_at')?->format('Y-m-d'))
      : now()->format('Y-m-d');

    $mail = (new MailMessage)
      ->subject("Peralatan Dipulangkan: Permohonan #{$appId}")
      ->greeting("Salam {$recipientName},")
      ->line("Pinjaman ICT bernombor rujukan **#{$appId}** telah berjaya dipulangkan.")
      ->line("**Tujuan:** {$purpose}")
      ->line("**Tarikh Pinjam:** {$startDate}")
      ->line("**Tarikh Dijangka Pulang:** {$expectedDate}")
      ->line("**Tarikh Pulang Sebenar:** {$actualReturn}")
      ->line('Berikut ialah butiran peralatan yang dipulangkan:');

    /** @var LoanTransaction $tx */
    foreach ($this->returnedTransactions as $tx) {
      $equip = $tx->equipment;
      if ($equip instanceof Equipment) {
        $type     = $equip->getAttribute('asset_type')    ?? 'Peralatan';
        $brand    = $equip->getAttribute('brand');
        $model    = $equip->getAttribute('model');
        $serial   = $equip->getAttribute('serial_number') ?? 'Tidak Dinyatakan';
        $tagId    = $equip->getAttribute('tag_id')         ?? 'Tidak Dinyatakan';

        $mail->line(
          "- **{$type}**"
            . ($brand ? " ({$brand})" : '')
            . ($model ? " {$model}" : '')
        );
        $mail->line("  Nombor Siri: {$serial}");
        $mail->line("  ID Tag MOTAC: {$tagId}");
      } else {
        $txId = $tx->getAttribute('id') ?? 'N/A';
        $mail->line("- Peralatan (Butiran tiada) — Transaksi ID: {$txId}");
      }
      $mail->line('---');
    }

    $url = is_string($appId) && $appId !== 'N/A'
      ? url("/loan-applications/{$appId}")
      : '#';

    if ($url !== '#') {
      $mail->action('Lihat Butiran Pinjaman', $url);
    }

    return $mail->salutation('Sekian, terima kasih.');
  }

  /**
   * @param  mixed  $notifiable
   * @return array<string,mixed>
   */
  public function toArray(object $notifiable): array
  {
    $items = $this->returnedTransactions->map(function (LoanTransaction $tx) {
      $equip = $tx->equipment;
      return [
        'transaction_id'             => $tx->getAttribute('id'),
        'equipment_id'               => $tx->getAttribute('equipment_id'),
        'asset_type'                 => $equip?->getAttribute('asset_type')    ?? 'N/A',
        'serial_number'              => $equip?->getAttribute('serial_number') ?? 'N/A',
        'tag_id'                     => $equip?->getAttribute('tag_id')         ?? 'N/A',
        'returned_at'                => $tx->getAttribute('returned_at')?->toDateTimeString(),
      ];
    })->toArray();

    $loanAppId   = $this->loanApplication->getAttribute('id');
    $applicantId = $this->loanApplication->getAttribute('user_id');
    $respOffId   = $this->loanApplication->getAttribute('responsible_officer_id');
    $returnOffId = $this->loanApplication->getAttribute('return_accepting_officer_id')
      ?? ($this->returnedTransactions->first()?->getAttribute('return_accepting_officer_id'));

    return [
      'application_type'             => 'Loan Application',
      'loan_application_id'          => $loanAppId,
      'applicant_id'                 => $applicantId,
      'responsible_officer_id'       => $respOffId,
      'return_accepting_officer_id'  => $returnOffId,
      'status'                       => 'equipment_returned',
      'subject'                      => 'Peralatan Dipulangkan',
      'message'                      => "Permohonan #{$loanAppId} telah selesai dipulangkan.",
      'url'                          => $loanAppId ? url("/loan-applications/{$loanAppId}") : '#',
      'returned_items'               => $items,
      'returned_at'                  => $this->returnedTransactions->first()?->getAttribute('returned_at')?->toDateTimeString(),
    ];
  }
}
