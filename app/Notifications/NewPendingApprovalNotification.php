<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

// Import the models needed for this notification
use App\Models\Approval;
use App\Models\EmailApplication; // Import approvable model
use App\Models\LoanApplication; // Import approvable model
use App\Models\User; // Import User model

class NewPendingApprovalNotification extends Notification implements ShouldQueue
{
  use Queueable;

  public Approval $approval;
  // Store the approvable model (EmailApplication or LoanApplication)
  public EmailApplication|LoanApplication $approvable;


  /**
   * Create a new notification instance.
   * This notification is sent to an officer when a new approval task is assigned to them.
   *
   * @param Approval $approval The approval record representing the pending task.
   */
  public function __construct(Approval $approval)
  {
    $this->approval = $approval;
    // Load the approvable relationship for easy access in the notification
    $this->approvable = $approval->approvable;
    // Ensure the applicant user is also loaded on the approvable model
    $this->approvable->loadMissing('user');
  }

  /**
   * Get the notification's delivery channels.
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    // 'notifiable' here would be the User model (the officer assigned to the task)
    return ['mail', 'database']; // Specify that this notification should be sent via email and database
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param object $notifiable The entity ($user) being notified (the officer).
   * @return \Illuminate\Notifications\Messages\MailMessage
   */
  public function toMail(object $notifiable): MailMessage
  {
    // Access the officer's name for the greeting
    $officerName = $notifiable->name ?? 'Pegawai';

    // Determine application type and details
    $applicationType = $this->approvable instanceof EmailApplication ? 'E-mel ICT' : 'Pinjaman Peralatan ICT';
    $applicationId = $this->approvable->id;
    $applicantName = $this->approvable->user->name ?? 'Pemohon Tidak Diketahui';
    // Purpose field might differ slightly or only exist on LoanApplication, handle potentially missing field
    $purpose = $this->approvable->purpose ?? ($this->approvable instanceof EmailApplication ? 'Permohonan Akaun E-mel' : 'Tujuan Tidak Dinyatakan');


    return (new MailMessage)
      ->subject("Tindakan Diperlukan: Anda Mempunyai Tugas Kelulusan Permohonan {$applicationType} Baru") // Email subject line includes app type
      ->greeting('Salam ' . $officerName . ',') // Greeting to the officer
      ->line('Anda mempunyai tugas kelulusan baru yang memerlukan tindakan anda dalam sistem.') // Main message
      ->line('**Butiran Permohonan:**')
      ->line('- **Jenis Permohonan:** ' . $applicationType)
      ->line('- **Nombor Rujukan:** #' . $applicationId)
      ->line('- **Pemohon:** ' . $applicantName)
      ->line('- **Tujuan:** ' . $purpose)
      ->line('Sila log masuk ke sistem untuk menyemak butiran permohonan dan membuat keputusan kelulusan atau penolakan.') // Call to action

      // Optional: Add a button linking directly to the pending approval task or application dashboard
      // This URL would depend on your routing structure for the approval dashboard or task view.
      // ->action('Lihat Tugas Kelulusan', url('/approvals/pending/' . $this->approval->id)); // Example linking to approval record
      // Or link to the application itself, relying on dashboard to show pending task:
      // ->action('Lihat Permohonan', url('/applications/' . strtolower(class_basename($this->approvable)) . '/' . $this->approvable->id)); // Example using model name

      ->salutation('Terima kasih.'); // Closing
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
    $applicationType = $this->approvable instanceof EmailApplication ? 'Email Application' : 'Loan Application';

    return [
      // Store notification data in a database table when using the 'database' channel
      'approval_id' => $this->approval->id,
      // FIX: Access approvable_type and approvable_id from the $this->approval model
      'approvable_type' => $this->approval->approvable_type, // Corrected access
      'approvable_id' => $this->approval->approvable_id, // Corrected access
      'message' => 'Anda mempunyai tugas kelulusan baru untuk ' . $applicationType . ' #' . $this->approvable->id . ' oleh ' . ($this->approvable->user->name ?? 'Pemohon'),
      'application_type' => $applicationType, // Redundant with approvable_type, but can be useful for display
      'application_id' => $this->approvable->id, // Redundant with approvable_id, but can be useful for display
      'applicant_id' => $this->approvable->user_id,
      'officer_id' => $this->approval->officer_id, // Store the assigned officer ID
      'stage' => $this->approval->stage, // Store the approval stage if applicable
    ];
  }

  // Implement toDatabase method if using the 'database' channel (toArray often suffices unless custom logic is needed)
  // public function toDatabase(object $notifiable): array { ... }
}
