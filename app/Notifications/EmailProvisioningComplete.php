<?php

namespace App\Notifications;

use App\Models\EmailApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailProvisioningComplete extends Notification implements ShouldQueue
{
  use Queueable;

  protected $application;

  /**
   * Create a new notification instance.
   *
   * @param \App\Models\EmailApplication $application The completed email application.
   */
  public function __construct(EmailApplication $application)
  {
    $this->application = $application;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param object $notifiable The user model ($notifiable is the applicant).
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    // IMPORTANT: Decide where this notification should be sent.
    // For sending credentials/completion, sending to the user's PERSONAL email is often safer
    // if the new MOTAC email requires configuration or is not yet accessible.
    // This might require overriding the routeNotificationFor('mail') method in the User model (as added in the User.php code).
    return ['mail', 'database'];
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param object $notifiable The user model ($notifiable is the applicant).
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail(object $notifiable): MailMessage
  {
    // --- FIX FOR ERROR: Syntax error: unexpected token '@' ---
    // Blade syntax (@if) cannot be used directly inside a PHP class method.
    // Use standard PHP if statements and method chaining.
    // --- END FIX ---

    // TODO: Customize the email content with the new account details (email address, NOT password).
    // You should NOT include the temporary password in this email for security reasons.
    // The temporary password should be sent via a separate, potentially more secure channel (like a dedicated welcome email Mailable sent by the provisioning service itself, as designed).
    // This notification confirms completion and provides the *assigned email address*.

    $mailMessage = (new MailMessage)
      ->subject(__('Akaun E-mel / ID Pengguna MOTAC Anda Telah Ditetapkan (#' . ($this->application->id ?? 'N/A') . ')'))
      ->greeting(__('Tahniah,') . ' ' . ($notifiable->full_name ?? 'Pengguna') . '!')
      ->line(__('Permohonan anda untuk Akaun E-mel / ID Pengguna MOTAC (#' . ($this->application->id ?? 'N/A') . ') telah berjaya diproses dan akaun anda telah ditetapkan.'));

    // Add lines conditionally using standard PHP if statements
    // Ensure 'motac_email' and 'user_id_assigned' are on the User model and were updated by the provisioning process.
    if ($notifiable->motac_email) {
      $mailMessage->line(__('Akaun E-mel MOTAC anda ialah: ') . $notifiable->motac_email);
    }

    if ($notifiable->user_id_assigned) {
      $mailMessage->line(__('ID Pengguna anda (Sistem Luar) ialah: ') . $notifiable->user_id_assigned);
    }

    $mailMessage->action(__('Lihat Butiran Permohonan'), route('email-applications.show', ($this->application ?? '#')))
      ->line(__('Untuk mendapatkan kata laluan awal dan arahan log masuk, sila rujuk e-mel alu-aluan berasingan yang dihantar ke e-mel peribadi anda (jika berkenaan).'))
      ->line(__('Jika anda tidak menerima e-mel alu-aluan dalam masa terdekat, sila hubungi Bahagian Pengurusan Maklumat.'));

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
      'status' => ($this->application->status ?? 'N/A'), // Should be 'completed' or similar
      'assigned_email' => ($notifiable->motac_email ?? 'N/A'), // Store assigned email
      'assigned_user_id' => ($notifiable->user_id_assigned ?? 'N/A'), // Store assigned ID
      'message' => __('Akaun e-mel/ID pengguna #' . ($this->application->id ?? 'N/A') . ' telah ditetapkan.'),
      'url' => route('email-applications.show', ($this->application ?? '#')),
    ];
  }

  /**
   * Override routeNotificationFor method in User model if you need to send to personal email.
   * Example in User.php:
   * public function routeNotificationForMail($notification): array|string
   * {
   * // For EmailProvisioningComplete notification, use personal_email
   * if ($notification instanceof \App\Notifications\EmailProvisioningComplete) {
   * return $this->personal_email; // Ensure personal_email is not null and is valid
   * }
   * // For all other notifications, use the default email
   * return $this->email;
   * }
   */
}
