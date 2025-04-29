<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon; // Import Carbon for date formatting and type hinting
use Illuminate\Database\Eloquent\Model; // Import base Model for type hinting flexibility


// Import the model needed for this notification
use App\Models\LoanApplication;
use App\Models\User; // Import User model as the notifiable object is a User


/**
 * Class LoanApprovedNotification
 *
 * Notification sent to the applicant when their Loan Application has been approved.
 * This notification includes details about the approved loan, such as purpose, location, and loan period,
 * and provides instructions for picking up equipment.
 * This notification can be sent via email and optionally stored in the database.
 */
class LoanApprovedNotification extends Notification implements ShouldQueue // Implement ShouldQueue for background sending
{
  use Queueable;

  /**
   * The approved loan application model instance.
   *
   * @var \App\Models\LoanApplication
   */
  protected LoanApplication $loanApplication; // Added property type hint

  /**
   * Create a new notification instance.
   *
   * @param \App\Models\LoanApplication $loanApplication The loan application that was approved.
   * @return void
   */
  public function __construct(LoanApplication $loanApplication) // Added docblock
  {
    $this->loanApplication = $loanApplication;
    // Ensure the applicant user and responsible officer relationships are loaded
    // as they might be needed for notification content or data storage.
    $this->loanApplication->loadMissing(['user', 'responsibleOfficer']);
  }

  /**
   * Get the notification's delivery channels.
   * Determines how the notification will be sent (e.g., mail, database).
   * This notification is typically sent to the applicant.
   *
   * @param object $notifiable The notifiable entity (typically the applicant User model instance).
   * @return array<int, string> An array of channel names (e.g., 'mail', 'database').
   */
  public function via(object $notifiable): array // Added type hint for notifiable, refined docblock
  {
    // You can add other channels like 'database', 'slack', etc.
    // Return ['mail', 'database'] if you want to store notifications in the database as well.
    return ['mail']; // Specify that this notification should be sent via email
  }

  /**
   * Get the mail representation of the notification.
   * Defines the content, subject, and structure of the email sent to the applicant regarding their approved loan application.
   *
   * @param object $notifiable The entity ($user) being notified (the applicant User model instance).
   * @return \Illuminate\Notifications\Messages\MailMessage The MailMessage instance.
   */
  public function toMail(object $notifiable): MailMessage // Refined docblock
  {
    // Access the applicant's name for the greeting safely
    // Use optional chaining (?->) for robustness, falling back to name then a generic greeting
    $applicantName = $notifiable->full_name ?? $notifiable->name ?? 'Pemohon';

    // Safely get the loan application ID, purpose, location, and loan dates
    $applicationId = $this->loanApplication->id ?? 'N/A';
    $purpose = $this->loanApplication->purpose ?? 'Tidak Dinyatakan';
    $location = $this->loanApplication->location ?? 'Tidak Dinyatakan';
    // Access date properties safely using optional chaining before formatting
    $startDate = $this->loanApplication->loan_start_date?->format('Y-m-d') ?? 'Tidak Dinyatakan';
    $endDate = $this->loanApplication->loan_end_date?->format('Y-m-d') ?? 'Tidak Dinyatakan';


    $mailMessage = (new MailMessage)
      // Localized and informative subject line
      ->subject("Permohonan Pinjaman Peralatan ICT Anda Telah Diluluskan (#{$applicationId})")
      // Localized greeting
      ->greeting('Salam ' . $applicantName . ',')
      // Localized email body content
      ->line('Sukacita dimaklumkan bahawa permohonan pinjaman peralatan ICT anda telah diluluskan.')
      ->line('Butiran Pinjaman:')
      ->line('- Nombor Rujukan: #' . $applicationId) // Include application ID
      ->line('- Tujuan: ' . $purpose) // Include purpose
      ->line('- Lokasi Penggunaan: ' . $location) // Include location
      ->line('- Tempoh Pinjaman: ' . $startDate . ' hingga ' . $endDate) // Include formatted loan dates
      ->line('Status Permohonan: Diluluskan'); // Explicitly state status


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
      // Localized action button text
      $mailMessage->action('Lihat Permohonan Anda', $applicationUrl);
    }

    $mailMessage->line('Sila berkoordinasi dengan kakitangan Bahagian Pengurusan Maklumat (BPM) untuk pengambilan peralatan.') // Instructions for pickup coordination
      ->line('Sekiranya anda mempunyai sebarang pertanyaan, sila hubungi Bahagian Pengurusan Maklumat (BPM) MOTAC.') // Contact information
      ->line('Terima kasih kerana menggunakan sistem!'); // Closing


    return $mailMessage;
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
    // Safely get the loan application ID, purpose, location, loan dates, applicant ID, and responsible officer ID
    $loanApplicationId = $this->loanApplication->id ?? null;
    $purpose = $this->loanApplication->purpose ?? null;
    $location = $this->loanApplication->location ?? null;
    $applicantId = $this->loanApplication->user_id ?? null; // Assuming user_id is the applicant
    $responsibleOfficerId = $this->loanApplication->responsible_officer_id ?? null; // Assuming responsible_officer_id exists

    // Access date properties safely using optional chaining
    $startDate = $this->loanApplication->loan_start_date?->toDateTimeString(); // Store as datetime string
    $endDate = $this->loanApplication->loan_end_date?->toDateTimeString(); // Store as datetime string


    // Safely construct the URL for the in-app notification link to the applicant's view
    $applicationUrl = '#'; // Default fallback URL
    if (isset($this->loanApplication->id)) {
      $applicationUrl = url('/loan-applications/' . $this->loanApplication->id); // Assuming a route exists
    }


    return [
      // Store notification data in a database table
      'application_type' => 'Loan Application',
      'loan_application_id' => $loanApplicationId,
      'applicant_id' => $applicantId, // Include applicant ID
      'responsible_officer_id' => $responsibleOfficerId, // Include responsible officer ID
      'status' => 'approved', // Store the status that triggered the notification
      // Generic subject for in-app display (localized)
      'subject' => 'Permohonan Pinjaman Diluluskan',
      // Message for in-app display (localized)
      'message' => "Permohonan Pinjaman Peralatan ICT anda (#{$loanApplicationId}) telah diluluskan.",
      // URL for the in-app notification to link to the applicant's view
      'url' => $applicationUrl,
      'purpose' => $purpose, // Include purpose in data
      'location' => $location, // Include location in data
      'loan_start_date' => $startDate, // Include loan dates in data
      'loan_end_date' => $endDate,
    ];
  }

  // Implement toDatabase method if using the 'database' channel for custom storage logic beyond toArray.
  // public function toDatabase(object $notifiable): array { ... }
}
