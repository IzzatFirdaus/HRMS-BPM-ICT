<?php

namespace App\Notifications;

use App\Models\EmailApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailProvisionedNotification extends Notification implements ShouldQueue // Implement ShouldQueue if you want queuing
{
  use Queueable;

  protected $emailApplication;

  /**
   * Create a new notification instance.
   */
  public function __construct(EmailApplication $emailApplication)
  {
    $this->emailApplication = $emailApplication;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    // Send via mail and potentially store in database
    return ['mail', 'database'];
  }

  /**
   * Get the mail representation of the notification.
   */
  public function toMail(object $notifiable): MailMessage
  {
    $mailMessage = (new MailMessage)
      ->subject('Your MOTAC Email Account/User ID is Ready')
      ->greeting('Hello ' . $notifiable->full_name . ','); // Assuming User model has full_name

    if ($this->emailApplication->final_assigned_email) {
      $mailMessage->line('Your MOTAC email account has been successfully provisioned.');
      $mailMessage->line('Your email address is: **' . $this->emailApplication->final_assigned_email . '**');
      // Avoid sending passwords in email! Instruct users on how to set/retrieve password securely.
      $mailMessage->line('You will receive separate instructions on how to set up your password and access your email.');
    } elseif ($this->emailApplication->final_assigned_user_id) {
      $mailMessage->line('Your MOTAC user ID has been successfully provisioned.');
      $mailMessage->line('Your assigned User ID is: **' . $this->emailApplication->final_assigned_user_id . '**');
      $mailMessage->line('You will receive separate instructions on how to use your User ID.');
    } else {
      // Fallback message if neither is assigned (should not happen if status is completed)
      $mailMessage->line('Your email/user ID application (Ref: #' . $this->emailApplication->id . ') processing is complete.');
      $mailMessage->line('Please check the system for your assigned credentials or await further instructions.');
    }

    $mailMessage->action('View Application Status', url('/email-applications/' . $this->emailApplication->id)) // Assuming a web route for viewing applications
      ->line('Thank you for using the system!');

    return $mailMessage;
  }

  /**
   * Get the array representation of the notification.
   *
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    return [
      'email_application_id' => $this->emailApplication->id,
      'message' => 'Your MOTAC email account/user ID provisioning is complete.',
      'assigned_email' => $this->emailApplication->final_assigned_email,
      'assigned_user_id' => $this->emailApplication->final_assigned_user_id,
      'status' => $this->emailApplication->status,
    ];
  }
}
