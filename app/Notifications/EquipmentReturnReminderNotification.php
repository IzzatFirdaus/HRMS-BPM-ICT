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
 * Class EquipmentReturnReminderNotification
 *
 * Notification sent to the applicant (and potentially responsible officer) to remind them
 * about an upcoming or present equipment return date for their loan application.
 * This notification emphasizes the return date and location.
 * This notification can be sent via email and optionally stored in the database.
 */
class EquipmentReturnReminderNotification extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * The loan application model instance requiring a return reminder.
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
   * This notification is sent to remind the applicant/responsible officer about an upcoming return date.
   *
   * @param \App\Models\LoanApplication $loanApplication The loan application requiring return.
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
   * This notification is typically sent to the applicant and optionally the responsible officer.
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
   * Defines the content, subject, and structure of the email sent to the applicant/responsible officer regarding the upcoming return.
   *
   * @param object $notifiable The entity ($user) being notified (applicant or responsible officer User model instance).
   * @return \Illuminate\Notifications\Messages\MailMessage The MailMessage instance.
   */
  public function toMail(object $notifiable): MailMessage // Refined docblock
  {
    // Determine the recipient's name for the greeting safely
    // Use optional chaining (?->) for robustness, falling back to name then a generic greeting
    $recipientName = $notifiable->full_name ?? $notifiable->name ?? 'Pemohon/Pegawai Bertanggungjawab';

    // Safely get the loan application ID and end date
    $applicationId = $this->loanApplication->id ?? 'N/A';
    // Access date property safely using optional chaining before formatting
    $endDate = $this->loanApplication->loan_end_date?->format('Y-m-d') ?? 'Tidak Dinyatakan';

    $mailMessage = (new MailMessage)
      // Email subject line including the return date for clarity
      ->subject('Peringatan: Tarikh Pulang Peralatan Pinjaman ICT Anda Mendekati (' . $endDate . ')')
      ->greeting('Salam ' . $recipientName . ',') // Greeting
      ->line('Ini adalah peringatan mesra mengenai pinjaman peralatan ICT anda.') // Main message
      ->line('Tarikh dijangka pulang bagi permohonan anda adalah pada **' . $endDate . '**.') // Mention the specific date
      ->line(
        // Dynamic line based on the number of days remaining
        $this->daysUntilReturn > 0
          ? 'Terdapat lagi **' . $this->daysUntilReturn . ' hari** sebelum tarikh pulang.'
          : 'Tarikh pulang adalah pada hari ini.' // Message if return date is today
      );

    $mailMessage->line('Sila pastikan peralatan pinjaman dipulangkan ke Bahagian Pengurusan Maklumat (BPM) MOTAC pada atau sebelum tarikh tersebut.'); // Instruction for return location/timing
    $mailMessage->line('Sekiranya anda memerlukan maklumat lanjut atau menghadapi sebarang isu, sila hubungi Bahagian Pengurusan Maklumat (BPM).'); // Contact information

    // Optional: Add a button linking to the application/loan details page
    // This URL would depend on your routing structure for viewing loan application details by the applicant.
    // Safely construct the loan application URL
    $loanApplicationUrl = '#'; // Default fallback URL
    if (isset($this->loanApplication->id)) {
      // Assuming a route exists for viewing loan applications
      $loanApplicationUrl = url('/loan-applications/' . $this->loanApplication->id);
    }

    // Add the action button only if a valid URL was constructed
    if ($loanApplicationUrl !== '#') {
      $mailMessage->action('Lihat Butiran Pinjaman', $loanApplicationUrl);
    }


    return $mailMessage->salutation('Sekian, terima kasih.'); // Closing
    // Optionally add a footer with contact information
    // ->salutation('Sekian,')
    // ->line('Unit ICT MOTAC');
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
    if (isset($this->loanApplication->id)) {
      $loanApplicationUrl = url('/loan-applications/' . $this->loanApplication->id); // Assuming a route exists
    }


    return [
      // Store notification data in a database table
      'application_type' => 'Loan Application',
      'loan_application_id' => $loanApplicationId,
      'applicant_id' => $applicantId, // Include applicant ID
      'responsible_officer_id' => $responsibleOfficerId, // Include responsible officer ID
      'status' => 'return_reminder', // Store the status that triggered the notification
      // Generic subject for in-app display
      'subject' => 'Peringatan Pulangan Peralatan',
      // Message for in-app display
      'message' => 'Peringatan pulangan peralatan untuk Permohonan Pinjaman #' . $loanApplicationId . '. Tarikh pulang: ' . $endDate . '.',
      // URL for the in-app notification to link to the application
      'url' => $loanApplicationUrl,
      'days_until_return' => $this->daysUntilReturn, // Store the days until return
      'return_date' => $endDate, // Store the return date in the data array
    ];
  }

  // Implement toDatabase method if using the 'database' channel for custom storage logic beyond toArray.
  // public function toDatabase(object $notifiable): array { ... }
}
