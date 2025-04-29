<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Database\Eloquent\Model; // Import base Model for type hinting flexibility

// Import the model needed for this notification
use App\Models\EmailApplication;
use App\Models\User; // Import User model as the notifiable object is a User


/**
 * Class EmailApplicationApprovedNotification
 *
 * Notification sent to the applicant when their Email Application has been approved.
 * This notification can be sent via email and optionally stored in the database.
 */
class EmailApplicationApprovedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  /**
   * The approved email application model instance.
   *
   * @var \App\Models\EmailApplication
   */
  public EmailApplication $emailApplication;

  /**
   * Create a new notification instance.
   *
   * @param \App\Models\EmailApplication $emailApplication The email application that was approved.
   * @return void
   */
  public function __construct(EmailApplication $emailApplication) // Added docblock
  {
    $this->emailApplication = $emailApplication;
    // While not strictly needed for the current toMail logic, loading the user
    // here can be beneficial if the notification is expanded or user data is needed elsewhere.
    // $this->emailApplication->loadMissing('user');
  }

  /**
   * Get the notification's delivery channels.
   * Determines how the notification will be sent (e.g., mail, database).
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
   * Defines the content, subject, and structure of the email sent to the applicant.
   *
   * @param object $notifiable The entity ($user) being notified (the applicant).
   * @return \Illuminate\Notifications\Messages\MailMessage The MailMessage instance.
   */
  public function toMail(object $notifiable): MailMessage // Refined docblock
  {
    // Access the applicant's name for the greeting safely
    // Use optional chaining (?->) for robustness
    $applicantName = $notifiable->full_name ?? $notifiable->name ?? 'Pemohon';

    // Safely get the application ID
    $applicationId = $this->emailApplication->id ?? 'N/A';


    $mailMessage = (new MailMessage)
      ->subject("Permohonan E-mel ICT MOTAC Diluluskan (#{$applicationId})") // Email subject includes ID
      ->greeting('Salam ' . $applicantName . ',') // Greeting to the applicant
      ->line('Sukacita dimaklumkan bahawa permohonan akaun e-mel ICT MOTAC anda telah diluluskan.') // Main message line 1
      ->line('Nombor Rujukan Permohonan: #' . $applicationId) // Include application ID
      ->line('Status Permohonan: Diluluskan') // Explicitly state status
      ->line('Proses penyediaan akaun e-mel anda akan dilaksanakan oleh Bahagian Pengurusan Maklumat (BPM). Anda akan dimaklumkan melalui e-mel berasingan setelah akaun e-mel anda berjaya disediakan, termasuk maklumat akaun dan kata laluan sementara.') // Inform about next steps (provisioning)
      ->line('Sekiranya anda memerlukan maklumat lanjut, sila hubungi Bahagian Pengurusan Maklumat (BPM) MOTAC.'); // Contact information


    // Optional: Add a button linking to the application details page (if you have one)
    // This URL would depend on your routing structure for viewing application details.
    // Safely construct the application URL
    $applicationUrl = '#'; // Default fallback URL
    if (isset($this->emailApplication->id)) {
      $applicationUrl = url('/email-applications/' . $this->emailApplication->id); // Assuming a route exists
    }

    // Add the action button only if a valid URL was constructed
    if ($applicationUrl !== '#') {
      $mailMessage->action('Lihat Permohonan Anda', $applicationUrl);
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
   * @param object $notifiable The entity ($user) being notified (the applicant).
   * @return array<string, mixed> An array of data to be stored in the database.
   */
  public function toArray(object $notifiable): array // Refined docblock
  {
    // Safely get the application ID and applicant ID
    $applicationId = $this->emailApplication->id ?? null;
    $applicantId = $this->emailApplication->user_id ?? null;

    // Safely construct the URL for the in-app notification link
    $applicationUrl = '#'; // Default fallback URL
    if (isset($this->emailApplication->id)) {
      $applicationUrl = url('/email-applications/' . $this->emailApplication->id); // Assuming a route exists
    }


    return [
      // Store notification data in a database table if using the 'database' channel
      'application_type' => 'Email Application',
      'application_id' => $applicationId,
      'applicant_id' => $applicantId,
      // Generic subject for in-app display
      'subject' => 'Permohonan E-mel Diluluskan',
      // Message for in-app display
      'message' => "Permohonan E-mel ICT anda (#{$applicationId}) telah diluluskan.",
      // URL for the in-app notification to link to
      'url' => $applicationUrl,
      'status' => 'approved', // Store the status that triggered the notification
    ];
  }

  // Implement toDatabase method if using the 'database' channel for custom storage logic beyond toArray.
  // public function toDatabase(object $notifiable): array { ... }
}
