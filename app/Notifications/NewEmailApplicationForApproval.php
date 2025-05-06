<?php

namespace App\Notifications;

use App\Models\EmailApplication;
use App\Models\Approval; // Import Approval model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewEmailApplicationForApproval extends Notification implements ShouldQueue
{
  use Queueable;

  protected $application;
  protected $approval; // The specific approval record for this officer

  /**
   * Create a new notification instance.
   * --- FIX FOR ERROR: Use of unknown class ---
   * This constructor signature matches how the notification is called in the EmailApplicationService.
   * --- END FIX ---
   * @param \App\Models\EmailApplication $application The email application needing approval.
   * @param \App\Models\Approval $approval The specific approval record for this officer.
   */
  public function __construct(EmailApplication $application, Approval $approval)
  {
    $this->application = $application;
    $this->approval = $approval;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param object $notifiable The user model ($notifiable is the supporting officer).
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    return ['mail', 'database']; // Example: send via email and store in database
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param object $notifiable The user model ($notifiable is the supporting officer).
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail(object $notifiable): MailMessage
  {
    // TODO: Customize email for the supporting officer.
    // Ensure the officer's primary email is used as the recipient.

    return (new MailMessage)
      ->subject(__('Permohonan Akaun E-mel/ID Pengguna Menunggu Kelulusan Anda (#' . ($this->application->id ?? 'N/A') . ')')) // Malay Subject
      ->greeting(__('Salam sejahtera,') . ' ' . ($notifiable->full_name ?? 'Pegawai') . '!')
      ->line(__('Terdapat permohonan Akaun E-mel / ID Pengguna MOTAC yang memerlukan semakan dan kelulusan anda.'))
      ->line(__('Butiran Permohonan:'))
      ->line(__('- ID Permohonan: ') . ($this->application->id ?? 'N/A'))
      ->line(__('- Pemohon: ') . ($this->application->user->full_name ?? __('Tidak Dikenali'))) // Assumes user relationship is loaded
      ->line(__('- Tujuan: ') . ($this->application->purpose ?? __('Tiada Tujuan Dinyatakan')))
      ->action(__('Semak dan Lulus/Tolak Permohonan'), route('email-applications.show', ($this->application ?? '#'))) // Link to application show page (where approval actions are)
      ->line(__('Sila semak permohonan ini melalui sistem dan ambil tindakan sewajarnya.'));
  }

  /**
   * Get the array representation of the notification (for the database channel).
   *
   * @param object $notifiable The user model ($notifiable is the supporting officer).
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    // TODO: Customize data for database notification.
    return [
      'application_id' => ($this->application->id ?? 'N/A'),
      'applicant_name' => ($this->application->user->full_name ?? __('Tidak Dikenali')), // Store applicant name
      'approval_stage' => ($this->approval->stage ?? __('Tidak Dinyatakan')), // Store approval stage
      'message' => __('Permohonan e-mel/ID pengguna #' . ($this->application->id ?? 'N/A') . ' memerlukan kelulusan anda.'),
      'url' => route('email-applications.show', ($this->application ?? '#')),
    ];
  }
}
