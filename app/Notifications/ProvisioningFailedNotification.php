<?php

namespace App\Notifications;

use App\Models\EmailApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProvisioningFailedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  protected $application;
  protected $errorMessage;

  /**
   * Create a new notification instance.
   *
   * @param \App\Models\EmailApplication $application The application related to the failure.
   * @param string $errorMessage The error message from the provisioning attempt.
   */
  public function __construct(EmailApplication $application, string $errorMessage)
  {
    $this->application = $application;
    $this->errorMessage = $errorMessage;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param object $notifiable The user model ($notifiable is an IT Admin).
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    return ['mail', 'database']; // Example: send via email and store in database
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param object $notifiable The user model ($notifiable is an IT Admin).
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail(object $notifiable): MailMessage
  {
    // TODO: Customize email content for IT Admin.
    // Send to the admin's primary email.

    return (new MailMessage)
      ->subject(__('RALAT: Gagal Proses Akaun E-mel/ID Pengguna (#' . ($this->application->id ?? 'N/A') . ')')) // Malay Subject
      ->greeting(__('Salam Admin,') . ' ' . ($notifiable->full_name ?? 'Admin') . '!')
      ->line(__('Terdapat permohonan Akaun E-mel / ID Pengguna MOTAC (#' . ($this->application->id ?? 'N/A') . ') yang gagal diproses secara automatik.'))
      ->line(__('Butiran Kegagalan:'))
      ->line(__('- ID Permohonan: ') . ($this->application->id ?? 'N/A'))
      ->line(__('- Pemohon: ') . ($this->application->user->full_name ?? __('Tidak Dikenali')))
      ->line(__('- Status API: Gagal'))
      ->line(__('- Mesej Ralat: ') . $this->errorMessage)
      ->action(__('Semak Permohonan dan Ralat'), route('email-applications.show', ($this->application ?? '#'))) // Link to application show page
      ->line(__('Sila semak butiran permohonan dan log ralat sistem untuk menyiasat punca kegagalan ini dan ambil tindakan pembetulan.'));
  }

  /**
   * Get the array representation of the notification (for the database channel).
   *
   * @param object $notifiable The user model ($notifiable is an IT Admin).
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    // TODO: Customize data for database notification.
    return [
      'application_id' => ($this->application->id ?? 'N/A'),
      'status' => ($this->application->status ?? 'N/A'), // Should be 'provisioning_failed' or similar
      'error_message' => $this->errorMessage, // Store the error message
      'message' => __('Gagal proses akaun e-mel/ID pengguna #' . ($this->application->id ?? 'N/A')),
      'url' => route('email-applications.show', ($this->application ?? '#')),
    ];
  }

  /**
   * Optionally add a method to identify who should receive this notification.
   * Example: in a Listener or Controller calling Notification::send($recipients, new ...).
   * Or, if sending via $user->notify(), ensure $user is an Admin with the Notifiable trait.
   */
}
