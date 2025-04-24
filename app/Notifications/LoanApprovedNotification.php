<?php

namespace App\Notifications;

use App\Models\LoanApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LoanApprovedNotification extends Notification
{
  use Queueable;

  protected $loanApplication;

  /**
   * Create a new notification instance.
   */
  public function __construct(LoanApplication $loanApplication)
  {
    $this->loanApplication = $loanApplication;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    // You can add other channels like 'database', 'slack', etc.
    return ['mail'];
  }

  /**
   * Get the mail representation of the notification.
   */
  public function toMail(object $notifiable): MailMessage
  {
    return (new MailMessage)
      ->subject('Your Equipment Loan Application Has Been Approved')
      ->greeting('Hello ' . $notifiable->full_name . ',') // Assuming User model has full_name
      ->line('Your ICT equipment loan application (Ref: #' . $this->loanApplication->id . ') has been approved.')
      ->line('Loan Details:')
      ->line('- Purpose: ' . $this->loanApplication->purpose)
      ->line('- Location: ' . $this->loanApplication->location)
      ->line('- Loan Period: ' . $this->loanApplication->loan_start_date->format('Y-m-d') . ' to ' . $this->loanApplication->loan_end_date->format('Y-m-d'))
      ->action('View Application', url('/loan-applications/' . $this->loanApplication->id)) // Assuming a web route for viewing applications
      ->line('Please coordinate with the BPM staff for equipment collection.')
      ->line('Thank you for using the system!');
  }

  /**
   * Get the array representation of the notification.
   *
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    return [
      'loan_application_id' => $this->loanApplication->id,
      'message' => 'Your equipment loan application (Ref: #' . $this->loanApplication->id . ') has been approved.',
      'status' => $this->loanApplication->status,
    ];
  }
}
