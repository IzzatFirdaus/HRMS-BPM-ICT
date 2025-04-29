<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Database\Eloquent\Model; // Import base Model for type hinting flexibility
use App\Models\EmailApplication; // Import specific application models
use App\Models\LoanApplication;
use App\Models\User; // Import User model as the notifiable object is a User
use Illuminate\Support\Facades\Log; // Import Log facade for error logging


/**
 * Class ApplicationApproved
 *
 * Notification sent to an applicant when their application (e.g., Email or Loan) has been approved.
 * This notification can be sent via email and stored in the database.
 */
class ApplicationApproved extends Notification implements ShouldQueue // Implement ShouldQueue for background sending
{
  use Queueable;

  /**
   * The approved application model instance (EmailApplication or LoanApplication).
   *
   * @var \App\Models\EmailApplication|\App\Models\LoanApplication|Model
   */
  public $application; // Use public property to be available to views/toArray

  /**
   * Create a new notification instance.
   *
   * @param \App\Models\EmailApplication|\App\Models\LoanApplication|Model $application The approved application model instance.
   * @return void
   */
  public function __construct($application) // Accept the application model
  {
    // Ensure the provided application is an instance of one of the expected types or a general Model
    // You might add more specific validation here if needed.
    $this->application = $application;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @param object $notifiable The notifiable entity (typically a User model instance).
   * @return array<int, string> An array of channel names (e.g., 'mail', 'database').
   */
  public function via(object $notifiable): array // Added type hint for notifiable, refined docblock
  {
    // Determine channels based on notifiable preferences or application type if needed
    return ['mail', 'database']; // Also send to database for in-app notifications
  }

  /**
   * Get the mail representation of the notification.
   *
   * @param object $notifiable The notifiable entity (typically a User model instance).
   * @return \Illuminate\Notifications\Messages\MailMessage The MailMessage instance.
   */
  public function toMail(object $notifiable): MailMessage // Refined docblock
  {
    // Safely access applicant name using conditional logic compatible with older PHP versions
    $applicantName = (isset($notifiable->full_name) && $notifiable->full_name) ? $notifiable->full_name : ((isset($notifiable->name) && $notifiable->name) ? $notifiable->name : 'Pemohon');

    // Determine application type for subject and content
    $applicationType = '';
    $applicationUrl = '#'; // Default fallback URL
    $applicationId = isset($this->application->id) ? $this->application->id : 'N/A'; // Safely get application ID

    if ($this->application instanceof EmailApplication) {
      $applicationType = 'Permohonan E-mel';
      // Construct URL assuming a base path
      if (isset($this->application->id)) {
        $applicationUrl = url('/email-applications/' . $this->application->id); // Assuming a route exists
      }
    } elseif ($this->application instanceof LoanApplication) {
      $applicationType = 'Permohonan Pinjaman Peralatan ICT';
      // Construct URL assuming a base path
      if (isset($this->application->id)) {
        $applicationUrl = url('/loan-applications/' . $this->application->id); // Assuming a route exists
      }
    } else {
      // Handle unexpected application types or log an error
      $applicationType = 'Aplikasi Tidak Dikenali';
      $appId = isset($this->application->id) ? $this->application->id : 'N/A';
      $appClass = is_object($this->application) ? get_class($this->application) : 'N/A';
      Log::error("ApplicationApproved Notification: Unknown application type passed.", ['application_id' => $appId, 'application_class' => $appClass]);
    }


    return (new MailMessage)
      ->subject("{$applicationType} Anda (#{$applicationId}) Telah Diluluskan") // Dynamic Subject
      ->greeting("Salam sejahtera, {$applicantName},") // Personalized Greeting
      ->line("Kami ingin memaklumkan bahawa {$applicationType} anda dengan Nombor Rujukan #{$applicationId} telah *diluluskan*.") // Confirmation message
      ->line('Sila log masuk ke sistem untuk melihat butiran permohonan yang diluluskan dan tindakan selanjutnya (jika ada).') // Call to action

      // Action button linking to the application details page
      ->action('Lihat Butiran Permohonan', $applicationUrl)

      ->line('Terima kasih kerana menggunakan sistem kami.'); // Closing line
    // Optionally add a footer with contact information
    // ->salutation('Sekian,')
    // ->line('Unit ICT MOTAC');
  }

  /**
   * Get the array representation of the notification.
   * This data will be stored in the 'database' notification channel.
   *
   * @param object $notifiable The notifiable entity (typically a User model instance).
   * @return array<string, mixed> An array of data to be stored.
   */
  public function toArray(object $notifiable): array // Refined docblock
  {
    // Safely access applicant name using conditional logic compatible with older PHP versions
    $applicantName = (isset($notifiable->full_name) && $notifiable->full_name) ? $notifiable->full_name : ((isset($notifiable->name) && $notifiable->name) ? $notifiable->name : 'Pemohon');

    // Safely get application ID and type
    $applicationId = isset($this->application->id) ? $this->application->id : null;
    $applicationType = is_object($this->application) ? get_class($this->application) : 'N/A';
    $applicationMorphClass = method_exists($this->application, 'getMorphClass') ? $this->application->getMorphClass() : 'application';


    // Determine URL for in-app notification link
    $applicationUrl = '#'; // Default fallback URL
    if ($this->application instanceof EmailApplication && isset($this->application->id)) {
      $applicationUrl = url('/email-applications/' . $this->application->id); // Assuming a route exists
    } elseif ($this->application instanceof LoanApplication && isset($this->application->id)) {
      $applicationUrl = url('/loan-applications/' . $this->application->id); // Assuming a route exists
    }


    // Include relevant data about the approved application
    return [
      'application_id' => $applicationId,
      'application_type' => $applicationType, // Store the class name to identify the type
      // Generic subject for in-app display
      'subject' => "Aplikasi Diluluskan (#{$applicationId})",
      // Example message for in-app display
      'message' => "{$applicantName}'s {$applicationMorphClass} application has been approved.",
      // URL for the in-app notification to link to
      'url' => $applicationUrl,
    ];
  }

  // You can add other representations if needed, e.g., toDatabase, toBroadcast, toSMS, etc.
}
