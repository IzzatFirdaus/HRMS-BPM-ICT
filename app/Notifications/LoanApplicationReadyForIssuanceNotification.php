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
use App\Models\User; // Import User model if needed


/**
 * Class LoanApplicationReadyForIssuanceNotification
 *
 * Notification sent to designated BPM staff when a Loan Application has been approved by support
 * and is ready for the equipment issuance process.
 * This notification includes key application details for BPM staff.
 * This notification can be sent via email and optionally stored in the database.
 */
class LoanApplicationReadyForIssuanceNotification extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * The loan application model instance that is ready for issuance.
   *
   * @var \App\Models\LoanApplication
   */
  public LoanApplication $loanApplication;

  /**
   * Create a new notification instance.
   * This notification is sent to BPM staff when a loan application is approved and ready for issuance.
   *
   * @param \App\Models\LoanApplication $loanApplication The loan application that is ready for issuance.
   * @return void
   */
  public function __construct(LoanApplication $loanApplication) // Added docblock
  {
    $this->loanApplication = $loanApplication;
    // Ensure the applicant user and responsible officer relationships are loaded
    // as their details might be needed for BPM staff context.
    $this->loanApplication->loadMissing(['user', 'responsibleOfficer']);
  }

  /**
   * Get the notification's delivery channels.
   * Determines how the notification will be sent (e.g., mail, database).
   * This notification is typically sent to a group of users (e.g., BPM Staff role).
   *
   * @param object $notifiable The notifiable entity (e.g., a User representing BPM staff) being notified.
   * @return array<int, string> An array of channel names (e.g., 'mail', 'database').
   */
  public function via(object $notifiable): array // Added type hint for notifiable, refined docblock
  {
    // 'notifiable' here would likely be a User model or a collection of Users (e.g., all BPM staff)
    // Return ['mail', 'database'] if you want to store notifications in the database as well.
    return ['mail']; // Specify that this notification should be sent via email
  }

  /**
   * Get the mail representation of the notification.
   * Defines the content, subject, and structure of the email sent to BPM staff regarding a loan application ready for issuance.
   *
   * @param object $notifiable The entity (e.g., a User representing BPM staff) being notified.
   * @return \Illuminate\Notifications\Messages\MailMessage The MailMessage instance.
   */
  public function toMail(object $notifiable): MailMessage // Refined docblock
  {
    // Access applicant details via the loanApplication relationship safely
    // Use optional chaining (?->) for robustness
    $applicantName = $this->loanApplication->user?->full_name ?? $this->loanApplication->user?->name ?? 'Pemohon Tidak Diketahui';
    // Safely get application details
    $applicationId = $this->loanApplication->id ?? 'N/A';
    $purpose = $this->loanApplication->purpose ?? 'Tiada Tujuan Dinyatakan';
    $location = $this->loanApplication->location ?? 'Tidak Dinyatakan';
    // Access date properties safely using optional chaining before formatting
    $startDate = $this->loanApplication->loan_start_date?->format('Y-m-d') ?? 'Tidak Dinyatakan';
    $endDate = $this->loanApplication->loan_end_date?->format('Y-m-d') ?? 'Tidak Dinyatakan';


    $mailMessage = (new MailMessage)
      ->subject("Permohonan Pinjaman Peralatan ICT Baru Diluluskan & Sedia Untuk Pengeluaran (#{$applicationId})") // Email subject includes ID
      ->greeting('Salam Petugas BPM,') // Greeting to the BPM staff
      ->line('Terdapat permohonan pinjaman peralatan ICT baru yang telah diluluskan dan sedia untuk proses pengeluaran (issuance).') // Main message
      ->line('**Nombor Rujukan Permohonan:** #' . $applicationId)
      ->line('**Pemohon:** ' . $applicantName)
      ->line('**Tujuan Permohonan:** ' . $purpose)
      ->line('**Lokasi Penggunaan:** ' . $location)
      ->line('**Tarikh Pinjaman:** ' . $startDate)
      ->line('**Tarikh Dijangka Pulang:** ' . $endDate)
      ->line('Sila log masuk ke sistem untuk melihat butiran penuh permohonan, menyemak inventori peralatan, dan merekodkan pengeluaran peralatan.'); // Call to action


    // Optional: Add a button linking to the application details/issuance page in the BPM interface
    // Make sure this URL is correct for your application details/checklist route.
    // Safely construct the admin/BPM application issuance URL
    $bpmApplicationIssuanceUrl = '#'; // Default fallback URL
    if (isset($this->loanApplication->id)) {
      // Assuming a BPM/admin route structure, e.g., /bpm/loan-applications/{id}/issuance
      $bpmApplicationIssuanceUrl = url('/bpm/loan-applications/' . $this->loanApplication->id . '/issuance');
      // Or simply to the application details page if issuance is done there:
      // $bpmApplicationIssuanceUrl = url('/bpm/loan-applications/' . $this->loanApplication->id);
    }

    // Add the action button only if a valid URL was constructed
    if ($bpmApplicationIssuanceUrl !== '#') {
      $mailMessage->action('Lihat Permohonan & Rekod Pengeluaran', $bpmApplicationIssuanceUrl);
    }


    return $mailMessage->salutation('Terima kasih.'); // Closing
    // Optionally add a footer with contact information
    // ->salutation('Sekian,')
    // ->line('Bahagian Pengurusan Maklumat (BPM) MOTAC');
  }

  /**
   * Get the array representation of the notification.
   * This data is used when storing the notification in the database if the 'database' channel is enabled.
   *
   * @param object $notifiable The notifiable entity (e.g., a User representing BPM staff) being notified.
   * @return array<string, mixed> An array of data to be stored in the database.
   */
  public function toArray(object $notifiable): array // Refined docblock
  {
    // Safely get the loan application ID, applicant ID, responsible officer ID, and other details
    $loanApplicationId = $this->loanApplication->id ?? null;
    $applicantId = $this->loanApplication->user_id ?? null; // Assuming user_id is the applicant
    $responsibleOfficerId = $this->loanApplication->responsible_officer_id ?? null; // Assuming responsible_officer_id exists
    $purpose = $this->loanApplication->purpose ?? null;
    $location = $this->loanApplication->location ?? null;
    // Access date properties safely using optional chaining
    $startDate = $this->loanApplication->loan_start_date?->toDateTimeString(); // Store as datetime string
    $endDate = $this->loanApplication->loan_end_date?->toDateTimeString(); // Store as datetime string

    // Safely access applicant name for the message
    $applicantName = $this->loanApplication->user?->full_name ?? $this->loanApplication->user?->name ?? 'Pemohon';


    // Safely construct the URL for the in-app notification link to the admin/BPM view
    $bpmApplicationIssuanceUrl = '#'; // Default fallback URL
    if (isset($this->loanApplication->id)) {
      // Assuming a BPM/admin route structure
      $bpmApplicationIssuanceUrl = url('/bpm/loan-applications/' . $this->loanApplication->id);
    }


    return [
      // Store notification data in a database table
      'application_type' => 'Loan Application',
      'loan_application_id' => $loanApplicationId,
      'applicant_id' => $applicantId, // Include applicant ID
      'responsible_officer_id' => $responsibleOfficerId, // Include responsible officer ID
      'status' => 'ready_for_issuance', // Store the status that triggered the notification
      // Generic subject for in-app display
      'subject' => 'Permohonan Pinjaman Sedia Pengeluaran',
      // Message for in-app display (Corrected variable name from $applicationId to $loanApplicationId)
      'message' => "Permohonan Pinjaman Peralatan ICT (#{$loanApplicationId}) oleh {$applicantName} sedia untuk pengeluaran.",
      // URL for the in-app notification to link to the BPM/admin view
      'url' => $bpmApplicationIssuanceUrl,
      'purpose' => $purpose, // Include purpose in data
      'location' => $location, // Include location in data
      'loan_start_date' => $startDate, // Include loan dates in data
      'loan_end_date' => $endDate,
    ];
  }

  // Implement toDatabase method if using the 'database' channel for custom storage logic beyond toArray.
  // public function toDatabase(object $notifiable): array { ... }
}
