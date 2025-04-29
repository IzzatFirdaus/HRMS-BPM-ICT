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
 * Class LoanApplicationApprovedNotification
 *
 * Notification sent to the applicant when their Loan Application has been approved.
 * This notification includes details about the approved loan and instructions for picking up equipment.
 * This notification can be sent via email and optionally stored in the database.
 */
class LoanApplicationApprovedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * The approved loan application model instance.
   *
   * @var \App\Models\LoanApplication
   */
  public LoanApplication $loanApplication;

  /**
   * Create a new notification instance.
   *
   * @param \App\Models\LoanApplication $loanApplication The loan application that was approved.
   * @return void
   */
  public function __construct(LoanApplication $loanApplication) // Added docblock
  {
    $this->loanApplication = $loanApplication;
    // While not strictly needed for the current toMail logic, loading the user
    // here can be beneficial if the notification is expanded or user data is needed elsewhere.
    // $this->loanApplication->loadMissing('user');
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
   * Defines the content, subject, and structure of the email sent to the applicant regarding their approved loan application.
   *
   * @param object $notifiable The entity ($user) being notified (the applicant User model instance).
   * @return \Illuminate\Notifications\Messages\MailMessage The MailMessage instance.
   */
  public function toMail(object $notifiable): MailMessage // Refined docblock
  {
    // Access the applicant's name for the greeting safely
    // Use optional chaining (?->) for robustness, falling back to full_name then name
    $applicantName = $notifiable->full_name ?? $notifiable->name ?? 'Pemohon';

    // Safely get the loan application ID, purpose, and loan dates
    $applicationId = $this->loanApplication->id ?? 'N/A';
    $purpose = $this->loanApplication->purpose ?? 'Tidak Dinyatakan';
    // Access date properties safely using optional chaining before formatting
    $startDate = $this->loanApplication->loan_start_date?->format('Y-m-d') ?? 'Tidak Dinyatakan';
    $endDate = $this->loanApplication->loan_end_date?->format('Y-m-d') ?? 'Tidak Dinyatakan';


    $mailMessage = (new MailMessage)
      ->subject("Permohonan Pinjaman Peralatan ICT MOTAC Diluluskan (#{$applicationId})") // Email subject includes ID
      ->greeting('Salam ' . $applicantName . ',') // Greeting to the applicant
      ->line('Sukacita dimaklumkan bahawa permohonan pinjaman peralatan ICT anda telah diluluskan.') // Main message line 1
      ->line('**Nombor Rujukan Permohonan:** #' . $applicationId) // Include application ID
      ->line('**Tujuan Permohonan:** ' . $purpose) // Include purpose
      ->line('**Tarikh Pinjaman:** ' . $startDate) // Include loan start date
      ->line('**Tarikh Dijangka Pulang:** ' . $endDate) // Include expected return date
      ->line('Status Permohonan: Diluluskan') // Explicitly state status
      ->line('Peralatan yang diluluskan boleh diambil di Bahagian Pengurusan Maklumat (BPM) MOTAC pada tarikh pinjaman yang ditetapkan.') // Inform about next steps (issuance)
      ->line('Sila bawa bersama salinan borang permohonan yang telah lengkap semasa mengambil peralatan.') // Instruction for pickup
      ->line('Sekiranya anda memerlukan maklumat lanjut, sila hubungi Bahagian Pengurusan Maklumat (BPM) MOTAC.'); // Contact information


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
    // Safely get the loan application ID, purpose, applicant ID, and loan dates
    $loanApplicationId = $this->loanApplication->id ?? null;
    $purpose = $this->loanApplication->purpose ?? null;
    $applicantId = $this->loanApplication->user_id ?? null; // Assuming user_id is the applicant
    // Access date properties safely using optional chaining
    $startDate = $this->loanApplication->loan_start_date?->toDateTimeString(); // Store as datetime string
    $endDate = $this->loanApplication->loan_end_date?->toDateTimeString(); // Store as datetime string


    // Safely construct the URL for the in-app notification link
    $applicationUrl = '#'; // Default fallback URL
    if (isset($this->loanApplication->id)) {
      $applicationUrl = url('/loan-applications/' . $this->loanApplication->id); // Assuming a route exists
    }


    return [
      // Store notification data in a database table
      'application_type' => 'Loan Application',
      'loan_application_id' => $loanApplicationId,
      'applicant_id' => $applicantId, // Include applicant ID
      'status' => 'approved', // Store the status that triggered the notification
      // Generic subject for in-app display
      'subject' => 'Permohonan Pinjaman Diluluskan',
      // Message for in-app display
      'message' => "Permohonan Pinjaman Peralatan ICT anda (#{$loanApplicationId}) telah diluluskan.",
      // URL for the in-app notification to link to the application
      'url' => $applicationUrl,
      'purpose' => $purpose, // Include purpose in data
      'loan_start_date' => $startDate, // Include loan dates in data
      'loan_end_date' => $endDate,
    ];
  }

  // Implement toDatabase method if using the 'database' channel for custom storage logic beyond toArray.
  // public function toDatabase(object $notifiable): array { ... }
}
