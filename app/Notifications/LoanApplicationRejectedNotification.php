<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Database\Eloquent\Model; // Import base Model for type hinting flexibility


// Import the models needed for this notification
use App\Models\LoanApplication;
use App\Models\Approval; // Assuming you pass the Approval record that caused the rejection
use App\Models\User; // Import User model as the notifiable object is a User
use App\Models\Officer; // Assuming Officer model exists and is related to Approval


/**
 * Class LoanApplicationRejectedNotification
 *
 * Notification sent to the applicant when their Loan Application has been rejected during the approval process.
 * This notification includes the reason for the rejection.
 * This notification can be sent via email and optionally stored in the database.
 */
class LoanApplicationRejectedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * The loan application model instance that was rejected.
   *
   * @var \App\Models\LoanApplication
   */
  public LoanApplication $loanApplication;

  /**
   * The specific approval record where the rejection occurred.
   * This record should contain the rejection comments and the officer who rejected it.
   *
   * @var \App\Models\Approval
   */
  public Approval $rejectionApproval; // The specific approval record where rejection occurred

  /**
   * Create a new notification instance.
   *
   * @param \App\Models\LoanApplication $loanApplication The loan application that was rejected.
   * @param \App\Models\Approval $rejectionApproval The approval record that marked the application as rejected (should contain comments and officer).
   * @return void
   */
  public function __construct(LoanApplication $loanApplication, Approval $rejectionApproval) // Added docblock
  {
    $this->loanApplication = $loanApplication;
    $this->rejectionApproval = $rejectionApproval;
    // Ensure the applicant user relationship on the loan application is loaded
    $this->loanApplication->loadMissing('user');
    // Ensure the officer relationship on the rejection approval is loaded
    $this->rejectionApproval->loadMissing('officer'); // Assuming 'officer' relation exists on Approval
  }

  /**
   * Get the notification's delivery channels.
   * Determines how the notification will be sent (e.g., mail, database).
   * This notification is typically sent to the applicant.
   *
   * @param object $notifiable The notifiable entity (typically a User model instance, the applicant).
   * @return array<int, string> An array of channel names (e.g., 'mail', 'database').
   */
  public function via(object $notifiable): array // Added type hint for notifiable, refined docblock
  {
    // 'notifiable' here would be the User model (the applicant)
    // Return ['mail', 'database'] if you want to store notifications in the database as well.
    return ['mail']; // Specify that this notification should be sent via email
  }

  /**
   * Get the mail representation of the notification.
   * Defines the content, subject, and structure of the email sent to the applicant regarding their rejected loan application.
   *
   * @param object $notifiable The entity ($user) being notified (the applicant User model instance).
   * @return \Illuminate\Notifications\Messages\MailMessage The MailMessage instance.
   */
  public function toMail(object $notifiable): MailMessage // Refined docblock
  {
    // Access the applicant's name for the greeting safely
    // Use optional chaining (?->) for robustness, falling back to full_name then name
    $applicantName = $notifiable->full_name ?? $notifiable->name ?? 'Pemohon';

    // Safely get the loan application ID and purpose
    $applicationId = $this->loanApplication->id ?? 'N/A';
    $purpose = $this->loanApplication->purpose ?? 'Tiada Tujuan Dinyatakan';

    // Get the rejection reason from the Approval record's comments safely
    $rejectionReason = $this->rejectionApproval->comments ?? 'Tiada sebab dinyatakan.';

    // Get the name of the officer who rejected (if officer relationship exists and is loaded)
    $rejectingOfficerName = $this->rejectionApproval->officer?->name ?? 'Pegawai Tidak Diketahui';


    $mailMessage = (new MailMessage)
      ->subject("Permohonan Pinjaman Peralatan ICT MOTAC Ditolak (#{$applicationId})") // Email subject includes ID
      ->greeting('Salam ' . $applicantName . ',') // Greeting to the applicant
      ->line('Dukacita dimaklumkan bahawa permohonan pinjaman peralatan ICT anda telah ditolak.') // Main message line 1
      ->line('**Nombor Rujukan Permohonan:** #' . $applicationId) // Include application ID
      ->line('**Tujuan Permohonan:** ' . $purpose) // Include purpose
      ->line('Status Permohonan: Ditolak') // Explicitly state status
      ->line('Sebab Penolakan:') // Indicate rejection reason section
      ->line($rejectionReason); // Display the rejection reason

    // Optionally include the officer who rejected, if desired
    // $mailMessage->line('Ditolak oleh: ' . $rejectingOfficerName);

    $mailMessage->line('Sekiranya anda memerlukan maklumat lanjut, sila hubungi Bahagian Pengurusan Maklumat (BPM) MOTAC.'); // Contact information


    // Optional: Add a button linking to the application details page (if you have one)
    // This URL would depend on your routing structure for viewing loan application details by the applicant.
    // Safely construct the application URL
    $applicationUrl = '#'; // Default fallback URL
    if (isset($this->loanApplication->id)) {
      // Assuming a route exists for viewing loan applications by applicant
      $applicationUrl = url('/loan-applications/' . $this->loanApplication->id);
    }

    // Add the action button only if a valid URL was constructed
    if ($applicationUrl !== '#') {
      $mailMessage->action('Lihat Permohonan Anda', $applicationUrl);
    }


    return $mailMessage->salutation('Sekian, terima kasih.'); // Closing
    // Optionally add a footer with contact information
    // ->salutation('Sekian,')
    // ->line('Bahagian Pengurusan Maklumat (BPM) MOTAC');
  }

  /**
   * Get the array representation of the notification.
   * This data is used when storing the notification in the database if the 'database' channel is enabled.
   *
   * @param object $notifiable The notifiable entity (typically the applicant User model instance).
   * @return array<string, mixed> An array of data to be stored in the database.
   */
  public function toArray(object $notifiable): array // Refined docblock
  {
    // Safely get the loan application ID, purpose, and applicant ID
    $loanApplicationId = $this->loanApplication->id ?? null;
    $purpose = $this->loanApplication->purpose ?? null;
    $applicantId = $this->loanApplication->user_id ?? null; // Assuming user_id is the applicant

    // Safely get rejection details
    $rejectionReason = $this->rejectionApproval->comments ?? 'Tiada sebab dinyatakan.';
    $rejectedByOfficerId = $this->rejectionApproval->officer_id ?? null;
    // Get the name of the officer who rejected for the message (if officer relationship exists and is loaded)
    $rejectingOfficerNameForMessage = $this->rejectionApproval->officer?->name ?? 'Pegawai';


    // Safely construct the URL for the in-app notification link to the applicant's view
    $applicationUrl = '#'; // Default fallback URL
    if (isset($this->loanApplication->id)) {
      $applicationUrl = url('/loan-applications/' . $this->loanApplication->id); // Assuming an applicant route exists
    }


    return [
      // Store notification data in a database table
      'application_type' => 'Loan Application',
      'loan_application_id' => $loanApplicationId,
      'applicant_id' => $applicantId, // Include applicant ID
      'status' => 'rejected', // Store the status that triggered the notification
      // Generic subject for in-app display
      'subject' => 'Permohonan Pinjaman Ditolak',
      // Message for in-app display
      'message' => "Permohonan Pinjaman Peralatan ICT anda (#{$loanApplicationId}) telah ditolak oleh {$rejectingOfficerNameForMessage}.",
      // URL for the in-app notification to link to the applicant's view
      'url' => $applicationUrl,
      'purpose' => $purpose, // Include purpose in data
      'rejection_reason' => $rejectionReason, // Store the rejection reason
      'rejected_by_officer_id' => $rejectedByOfficerId, // Store the officer who rejected
    ];
  }

  // Implement toDatabase method if using the 'database' channel for custom storage logic beyond toArray.
  // public function toDatabase(object $notifiable): array { ... }
}
