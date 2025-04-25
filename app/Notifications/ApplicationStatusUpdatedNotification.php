<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

// Import the models that can be associated with this notification
use App\Models\EmailApplication;
use App\Models\LoanApplication;
use App\Models\User; // Import User model

class ApplicationStatusUpdatedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  // The application model (can be EmailApplication or LoanApplication)
  public EmailApplication|LoanApplication $application;
  public string $oldStatus;
  public string $newStatus;

  /**
   * Create a new notification instance.
   * This notification is sent to the applicant when their application status changes.
   *
   * @param EmailApplication|LoanApplication $application The application whose status was updated.
   * @param string $oldStatus The previous status of the application.
   * @param string $newStatus The new status of the application.
   */
  public function __construct(EmailApplication|LoanApplication $application, string $oldStatus, string $newStatus)
  {
    $this->application = $application;
    $this->oldStatus = $oldStatus;
    $this->newStatus = $newStatus;
    // Ensure the applicant user is loaded on the application model
    $this->application->loadMissing('user');
  }

  /**
   * Get the notification's delivery channels.
   * This notification is sent to the applicant.
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    // 'notifiable' here would be the User model (the applicant)
    return ['mail']; // Specify that this notification should be sent via email
    // Add 'database' here if you want to store notifications in the database (e.g., in the user's dashboard)
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param object $notifiable The entity ($user) being notified (the applicant).
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail(object $notifiable): MailMessage
  {
    // Access the applicant's name for the greeting
    $applicantName = $notifiable->name ?? 'Pemohon';

    // Determine application type for clarity
    $applicationType = $this->application instanceof EmailApplication ? 'E-mel ICT' : 'Pinjaman Peralatan ICT';
    $applicationId = $this->application->id;
    // Get a summary/purpose for the application
    $applicationSummary = $this->application->purpose ?? ($this->application instanceof EmailApplication ? 'Permohonan Akaun E-mel' : 'Permohonan Pinjaman');


    return (new MailMessage)
      ->subject("Status Permohonan {$applicationType} Anda Dikemaskini (#{$applicationId})") // Email subject includes type and ID
      ->greeting('Salam ' . $applicantName . ',') // Greeting to the applicant
      ->line("Status permohonan {$applicationType} anda telah dikemaskini dalam sistem.") // Main message
      ->line('**Nombor Rujukan Permohonan:** #' . $applicationId)
      ->line('**Ringkasan:** ' . $applicationSummary)
      ->line("Status terdahulu: **" . $this->oldStatus . "**") // Show previous status
      ->line("Status terkini: **" . $this->newStatus . "**") // Show new status
      ->line('Sila log masuk ke sistem untuk melihat butiran penuh permohonan anda.') // Call to action

      // Optional: Add a button linking to the application details page
      // This URL would depend on your routing structure for viewing application details.
      // ->action('Lihat Permohonan', url('/my-applications/' . ($this->application instanceof EmailApplication ? 'email' : 'loan') . '/' . $applicationId));


      ->salutation('Sekian, terima kasih.'); // Closing
  }

  /**
   * Get the array representation of the notification.
   * This is used when storing the notification in the database.
   *
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    // Determine application type for database storage display
    $applicationType = $this->application instanceof EmailApplication ? 'Email Application' : 'Loan Application';

    return [
      // Optionally store notification data in a database table if using the 'database' channel
      'application_type' => $applicationType,
      'application_id' => $this->application->id,
      'applicant_id' => $this->application->user_id,
      'message' => "Status permohonan {$applicationType} anda (#{$this->application->id}) telah dikemaskini ke: " . $this->newStatus,
      'old_status' => $this->oldStatus,
      'new_status' => $this->newStatus,
    ];
  }

  // Implement toDatabase method if using the 'database' channel (toArray often suffices unless custom logic is needed)
  // public function toDatabase(object $notifiable): array { ... }
}
