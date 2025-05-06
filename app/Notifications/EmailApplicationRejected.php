<?php

namespace App\Notifications;

use App\Models\EmailApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailApplicationRejected extends Notification implements ShouldQueue
{
  use Queueable;

  protected $application;
  protected $rejectionReason;

  /**
   * Create a new notification instance.
   *
   * @param \App\Models\EmailApplication $application The rejected email application.
   * @param string|null $rejectionReason The reason for rejection.
   */
  public function __construct(EmailApplication $application, ?string $rejectionReason = null)
  {
    $this->application = $application;
    $this->rejectionReason = $rejectionReason ?? $application->rejection_reason; // Use reason from application if not provided
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param object $notifiable The user model ($notifiable is the applicant).
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    return ['mail', 'database']; // Example: send via email and store in database
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param object $notifiable The user model ($notifiable is the applicant).
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail(object $notifiable): MailMessage
  {
    // TODO: Customize email for the applicant when their application is rejected.
    // Send to personal email if available, else primary email.

    $mailMessage = (new MailMessage)
      ->subject(__('Permohonan Akaun E-mel/ID Pengguna Ditolak (#' . ($this->application->id ?? 'N/A') . ')')) // Malay Subject
      ->greeting(__('Salam sejahtera,') . ' ' . ($notifiable->full_name ?? 'Pengguna') . '!')
      ->line(__('Kami ingin memaklumkan bahawa permohonan anda untuk Akaun E-mel / ID Pengguna MOTAC (#' . ($this->application->id ?? 'N/A') . ') telah ditolak.'));

    if ($this->rejectionReason) {
      $mailMessage->line(__('Sebab Penolakan:') . ' ' . $this->rejectionReason);
    }

    $mailMessage->action(__('Lihat Butiran Permohonan'), route('email-applications.show', ($this->application ?? '#')))
      ->line(__('Jika anda mempunyai sebarang pertanyaan, sila hubungi Bahagian Pengurusan Maklumat.'));

    return $mailMessage;
  }

  /**
   * Get the array representation of the notification (for the database channel).
   *
   * @param object $notifiable The user model ($notifiable is the applicant).
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    // TODO: Customize data for database notification.
    return [
      'application_id' => ($this->application->id ?? 'N/A'),
      'status' => ($this->application->status ?? 'N/A'), // Should be 'rejected'
      'rejection_reason' => $this->rejectionReason, // Store rejection reason
      'message' => __('Permohonan e-mel/ID pengguna #' . ($this->application->id ?? 'N/A') . ' telah ditolak.'),
      'url' => route('email-applications.show', ($this->application ?? '#')),
    ];
  }
}
