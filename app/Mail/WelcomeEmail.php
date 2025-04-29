<?php

namespace App\Mail;

use App\Models\User; // Import User model for type-hinting
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address; // Import Address for 'to' and 'from' addresses
use Illuminate\Contracts\Queue\ShouldQueue; // Added ShouldQueue for background sending
use Illuminate\Support\Facades\Log; // Import Log facade for error logging

/**
 * Class WelcomeEmail
 *
 * Mailable class for sending a welcome email to a user with their newly provisioned MOTAC email credentials.
 * This email is intended to be queued for better performance.
 */
class WelcomeEmail extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  /**
   * The user model instance receiving the email.
   * This user is the applicant for the email account.
   *
   * @var \App\Models\User
   */
  public User $user;

  /**
   * The provisioned MOTAC email address for the user.
   *
   * @var string
   */
  public string $motacEmail;

  /**
   * The initial password for the new MOTAC email account.
   *
   * @var string
   */
  public string $password;

  /**
   * Create a new message instance.
   * This Mailable is used to send a welcome email with credentials after email provisioning.
   *
   * @param \App\Models\User $user The user receiving the email (applicant).
   * @param string $motacEmail The provisioned MOTAC email address for the user.
   * @param string $password The initial password for the new MOTAC email account.
   * @return void
   */
  public function __construct(User $user, string $motacEmail, string $password)
  {
    $this->user = $user;
    $this->motacEmail = $motacEmail;
    $this->password = $password;
  }

  /**
   * Get the message envelope.
   * Defines the sender, recipient, and subject of the email.
   * Also includes tags and metadata for tracking.
   *
   * @return \Illuminate\Mail\Mailables\Envelope
   */
  public function envelope(): Envelope
  {
    // Safely get the recipient's personal email address from the user model
    // Use optional chaining (?->) to handle cases where $this->user or user->email might be null.
    $recipientEmail = $this->user?->personal_email ?? $this->user?->email; // Prioritize personal_email, fallback to email
    // Safely get the user's name for the recipient address
    $recipientName = $this->user?->full_name ?? $this->user?->name ?? null; // Use full_name then name, fallback to null

    // Ensure there is a valid recipient email before creating the Envelope
    $to = [];
    if ($recipientEmail) {
      // Use Address object for better recipient handling, including name if available
      $to[] = new Address($recipientEmail, $recipientName);
    } else {
      // Handle the case where the recipient personal email is not found, e.g., log an error
      Log::error("WelcomeEmail Mailable: Recipient personal email not found for User ID: {$this->user->id}");
      // Optionally, you could set a fallback recipient from configuration:
      // $to[] = config('mail.from.address'); // Send to a default admin address
    }


    return new Envelope(
      // You can specify the sender here if it differs from your mail.php config
      // from: new Address(config('mail.from.address'), config('mail.from.name')), // Example using config values

      // Set the recipient to the user's personal email address
      to: $to,

      // Subject: Clearly state the purpose
      subject: 'Selamat Datang ke MOTAC ICT - Akaun E-mel Anda Disediakan', // More specific subject line

      // Add tags for tracking emails in services like Postmark, Mailgun, or AWS SES.
      tags: ['welcome-email', 'email-provisioning'],

      // Add metadata for tracking emails.
      metadata: [
        'user_id' => $this->user->id, // Assuming user ID is directly available
        'motac_email' => $this->motacEmail,
      ],

      // You can also set 'cc', 'bcc', 'replyTo' here if needed
      // cc: [...],
      // bcc: [...],
      // replyTo: [...],
    );
  }

  /**
   * Get the message content definition.
   * Defines the Blade view that will render the email body and passes data to it.
   *
   * @return \Illuminate\Mail\Mailables\Content
   */
  public function content(): Content
  {
    return new Content(
      // Define the Blade view for the email body (create this view: resources/views/emails/welcome.blade.php)
      view: 'emails.welcome',
      // Public properties on the Mailable ($this->user, $this->motacEmail, $this->password)
      // are automatically available to the email view.
      // The explicit 'with' data is not needed here as properties are public,
      // but you could add it for explicit clarity if preferred:
      // with: [
      //      'user' => $this->user,
      //      'motacEmail' => $this->motacEmail,
      //      'password' => $this->password,
      // ],
    );
  }

  /**
   * Get the attachments for the message.
   * Optionally attach files (e.g., user guide for the email account).
   *
   * @return array<int, \Illuminate\Mail\Mailables\Attachment> An array of Attachment objects.
   */
  public function attachments(): array
  {
    return [
      // Define any attachments here if needed
      // Example:
      // Attachment::fromPath('/path/to/your/user_guide.pdf')
      //           ->as('MOTAC_Email_User_Guide.pdf')
      //           ->withMime('application/pdf'),
    ];
  }

  // Removed separate metadata() and tags() methods to resolve compatibility errors.
  // Metadata and tags are now defined directly in the envelope() method.


  // The build() method is an alternative for older Laravel versions and is not needed
  // when using envelope() and content(). Removed for clarity.
}
