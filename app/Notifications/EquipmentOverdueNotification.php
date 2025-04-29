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
 * Class EquipmentOverdueNotification
 *
 * Notification sent to the applicant (and potentially responsible officer) to inform them that equipment
 * from their loan application is overdue for return.
 * This notification emphasizes urgency and next steps.
 * This notification can be sent via email and optionally stored in the database.
 */
class EquipmentOverdueNotification extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * The loan application model instance that is overdue.
   *
   * @var \App\Models\LoanApplication
   */
  public LoanApplication $loanApplication;

  /**
   * The number of days remaining until the return date.
   * Can be 0 if the return date is today, or negative if overdue (though this notification is for reminders, not overdue).
   *
   * @var int
   */
  public int $daysUntilReturn; // Number of days until the return date

  /**
   * Create a new notification instance.
   * This notification is sent to inform the applicant/responsible officer that their loan is overdue.
   *
   * @param \App\Models\LoanApplication $loanApplication The loan application that is overdue.
   * @param int $daysUntilReturn The number of days remaining until the return date.
   * @return void
   */
  public function __construct(LoanApplication $loanApplication, int $daysUntilReturn) // Added docblock
  {
    $this->loanApplication = $loanApplication;
    $this->daysUntilReturn = $daysUntilReturn;
    // Ensure the applicant user and responsible officer relationships are loaded
    // as they might be the recipients of this notification.
    $this->loanApplication->loadMissing(['user', 'responsibleOfficer']);
  }

  /**
   * Get the notification's delivery channels.
   * Determines how the notification will be sent (e.g., mail, database).
   * This notification is typically sent to the applicant and potentially the responsible officer.
   *
   * @param object $notifiable The notifiable entity (typically the applicant or responsible officer User model instance).
   * @return array<int, string> An array of channel names (e.g., 'mail', 'database').
   */
  public function via(object $notifiable): array // Added type hint for notifiable, refined docblock
  {
    // 'notifiable' here would be the User model (the applicant or responsible officer)
    // Return ['mail', 'database'] if you want to store notifications in the database as well.
    return ['mail']; // Specify that this notification should be sent via email
  }

  /**
   * Get the mail representation of the notification.
   * Defines the content, subject, and structure of the email sent to the applicant/responsible officer regarding overdue equipment.
   *
   * @param object $notifiable The entity ($user) being notified (applicant or responsible officer User model instance).
   * @return \Illuminate\Notifications\Messages\MailMessage The MailMessage instance.
   */
  public function toMail(object $notifiable): MailMessage // Refined docblock
  {
    // Determine the recipient's name for the greeting safely
    // Use optional chaining (?->) for robustness, falling back to name then a generic greeting
    $recipientName = $notifiable->full_name ?? $notifiable->name ?? 'Pemohon/Pegawai Bertanggungjawab';

    // Safely get the loan application ID, purpose, and end date
    $applicationId = $this->loanApplication->id ?? 'N/A';
    $purpose = $this->loanApplication->purpose ?? 'Tidak Dinyatakan';
    // Access date property safely using optional chaining before formatting
    $endDate = $this->loanApplication->loan_end_date?->format('Y-m-d') ?? 'Tidak Dinyatakan';


    $mailMessage = (new MailMessage)
      // Email subject line includes the overdue date for immediate clarity
      ->subject("Tindakan Diperlukan: Peralatan Pinjaman ICT Anda Telah Lewat Pulang (" . $endDate . ")")
      ->greeting('Salam ' . $recipientName . ',') // Greeting
      ->line('Dimaklumkan bahawa peralatan pinjaman ICT di bawah tanggungjawab anda bagi permohonan berikut telah **lewat pulang**:') // Main message
      ->line('**Nombor Rujukan Permohonan:** #' . $applicationId)
      ->line('**Tujuan Permohonan:** ' . $purpose)
      ->line('**Tarikh Dijangka Pulang:** ' . $endDate) // Show the overdue date
      ->line('Sila pulangkan peralatan tersebut ke Bahagian Pengurusan Maklumat (BPM) MOTAC dengan serta-merta.') // Call to action
      ->line('Sekiranya terdapat sebab kelewatan atau isu lain, sila hubungi Bahagian Pengurusan Maklumat (BPM) secepat mungkin untuk makluman dan tindakan selanjutnya.'); // Contact information and urgency


    // Optional: Add a button linking to the application/loan details page
    // This URL would depend on your routing structure for viewing loan application details by the applicant/responsible officer.
    // Safely construct the loan application URL
    $loanApplicationUrl = '#'; // Default fallback URL
    // Safely access the application ID for the URL
    $loanAppIdForUrl = $this->loanApplication->id ?? null;
    if ($loanAppIdForUrl) {
      // Assuming a route exists for viewing loan applications
      $loanApplicationUrl = url('/loan-applications/' . $loanAppIdForUrl);
    }

    // Add the action button only if a valid URL was constructed
    if ($loanApplicationUrl !== '#') {
      $mailMessage->action('Lihat Butiran Pinjaman', $loanApplicationUrl);
    }


    return $mailMessage->salutation('Sekian, terima kasih.'); // Closing;
  }

  /**
   * Get the array representation of the notification.
   * This data is used when storing the notification in the database if the 'database' channel is enabled.
   *
   * @param object $notifiable The notifiable entity (typically the applicant or responsible officer User model instance).
   * @return array<string, mixed> An array of data to be stored in the database.
   */
  public function toArray(object $notifiable): array // Refined docblock
  {
    // Safely get the loan application ID, applicant ID, responsible officer ID, and end date
    $loanApplicationId = $this->loanApplication->id ?? null;
    $applicantId = $this->loanApplication->user_id ?? null; // Assuming user_id is the applicant
    $responsibleOfficerId = $this->loanApplication->responsible_officer_id ?? null; // Assuming responsible_officer_id exists
    // Access date property safely using optional chaining before formatting
    $endDate = $this->loanApplication->loan_end_date?->format('Y-m-d') ?? 'Tidak Dinyatakan';


    // Safely construct the URL for the in-app notification link
    $loanApplicationUrl = '#'; // Default fallback URL
    $loanAppIdForUrl = $this->loanApplication->id ?? null; // Safely access ID again for URL
    if ($loanAppIdForUrl) {
      $loanApplicationUrl = url('/loan-applications/' . $loanAppIdForUrl); // Assuming a route exists
    }


    return [
      // Store notification data in a database table
      'application_type' => 'Loan Application',
      'loan_application_id' => $loanApplicationId,
      'applicant_id' => $applicantId, // Include applicant ID
      'responsible_officer_id' => $responsibleOfficerId, // Include responsible officer ID
      'status' => 'overdue', // Store the status that triggered the notification
      // Generic subject for in-app display
      'subject' => 'Peralatan Pinjaman Lewat Pulang',
      // Message for in-app display
      'message' => 'Peralatan pinjaman ICT bagi Permohonan #' . ($loanApplicationId ?? 'N/A') . ' telah lewat pulang sejak ' . $endDate . '.', // Safely access ID in message
      // URL for the in-app notification to link to the application
      'url' => $loanApplicationUrl,
      'days_until_return' => $this->daysUntilReturn, // Store the days until return
      'return_date' => $endDate, // Store the return date in the data array
    ];
  }

  // Implement toDatabase method if using the 'database' channel for custom storage logic beyond toArray.
  // public function toDatabase(object $notifiable): array { ... }
}
