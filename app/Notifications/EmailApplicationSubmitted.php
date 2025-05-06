<?php

namespace App\Notifications;

use App\Models\EmailApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailApplicationSubmitted extends Notification implements ShouldQueue
{
  use Queueable;

  protected $application;

  /**
   * Create a new notification instance.
   * --- FIX FOR ERROR: Too many arguments to function __construct() ---
   * This constructor signature matches how the notification is called in the EmailApplicationService.
   * If your linter showed 'Too many arguments', it means this constructor was missing or incorrect.
   * --- END FIX ---
   * @param \App\Models\EmailApplication $application The submitted email application.
   */
  public function __construct(EmailApplication $application)
  {
    $this->application = $application;
  }

  /**
   * Get the notification's delivery channels.
   * You can specify channels like 'mail', 'database', 'broadcast', 'vonage' (for SMS), etc.
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
    // TODO: Customize the email subject, content, and potentially use a dedicated Blade view.
    // Ensure the applicant's personal email is used as the recipient in your Mailable if needed,
    // or use the $notifiable->email which is usually the primary user email.

    return (new MailMessage)
      ->subject(__('Permohonan Akaun E-mel/ID Pengguna Dihantar (#' . ($this->application->id ?? 'N/A') . ')')) // Malay Subject
      ->greeting(__('Salam sejahtera,') . ' ' . ($notifiable->full_name ?? 'Pengguna') . '!') // Assumes full_name exists on User model
      ->line(__('Permohonan anda untuk Akaun E-mel / ID Pengguna MOTAC (#' . ($this->application->id ?? 'N/A') . ') telah berjaya dihantar.'))
      ->line(__('Status semasa permohonan anda ialah: ') . ($this->application->status_translated ?? 'Tidak Diketahui')) // Assumes status_translated accessor exists
      ->action(__('Lihat Status Permohonan'), route('email-applications.show', ($this->application ?? '#'))) // Link to application show page
      ->line(__('Anda akan dimaklumkan mengenai kemas kini permohonan anda.'));
    // You might want to attach the original application PDF or summary here.
  }

  /**
   * Get the array representation of the notification (for the database channel).
   *
   * @param object $notifiable The user model ($notifiable is the applicant).
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    // TODO: Customize the data stored in the database 'notifications' table.
    // This data is typically used to display notifications within the application UI.
    return [
      'application_id' => ($this->application->id ?? 'N/A'),
      'status' => ($this->application->status ?? 'N/A'),
      'message' => __('Permohonan e-mel/ID pengguna #' . ($this->application->id ?? 'N/A') . ' telah dihantar.'),
      'url' => route('email-applications.show', ($this->application ?? '#')), // Store URL for easy linking
    ];
  }

  /**
   * Optionally, define the Mailable to use for the 'mail' channel.
   * If you prefer a custom Mailable class over the default MailMessage.
   * Ensure the Mailable class (e.g., App\Mail\ApplicationSubmittedMail) exists.
   */
  /*
     public function toMail(object $notifiable)
     {
         return (new App\Mail\ApplicationSubmittedMail($this->application))
                     ->to($notifiable->personal_email ?? $notifiable->email); // Send to personal email if available, else primary email
     }
     */
}
