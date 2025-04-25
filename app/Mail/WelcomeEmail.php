<?php

namespace App\Mail;

use App\Models\User; // Import User model for type-hinting
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Address; // Optional: if you want to set 'from' address here
use Illuminate\Contracts\Queue\ShouldQueue; // Added ShouldQueue for background sending

class WelcomeEmail extends Mailable implements ShouldQueue // Implement ShouldQueue for background sending
{
  use Queueable, SerializesModels;

  public User $user; // Public property for the user
  public string $motacEmail; // Public property for the provisioned MOTAC email
  public string $password; // Public property for the initial password

  /**
   * Create a new message instance.
   * This Mailable is used to send a welcome email with credentials after email provisioning.
   *
   * @param User $user The user receiving the email (applicant).
   * @param string $motacEmail The provisioned MOTAC email address for the user.
   * @param string $password The initial password for the new MOTAC email account.
   */
  public function __construct(User $user, string $motacEmail, string $password) // Constructor now accepts 3 arguments
  {
    $this->user = $user; // Assign the user data to make it available in the view
    $this->motacEmail = $motacEmail; // Assign the provisioned email
    $this->password = $password; // Assign the password
  }

  /**
   * Get the message envelope.
   * Defines the sender, recipient, and subject of the email.
   */
  public function envelope(): Envelope
  {
    return new Envelope(
      // You can specify the sender here if it differs from your mail.php config
      // from: new Address(config('mail.from.address'), config('mail.from.name')), // Example using config values

      subject: 'Selamat Datang ke MOTAC ICT - Akaun E-mel Anda Disediakan', // More specific subject line
    );
  }

  /**
   * Get the message content definition.
   * Defines the Blade view that will render the email body.
   */
  public function content(): Content
  {
    return new Content(
      // Define the Blade view for the email body (create this view: resources/views/emails/welcome.blade.php)
      view: 'emails.welcome',
      // Public properties on the Mailable ($this->user, $this->motacEmail, $this->password)
      // are automatically available to the email view.
      // You can also pass data explicitly using 'with' if you prefer:
      // with: [
      //     'user' => $this->user,
      //     'motacEmail' => $this->motacEmail,
      //     'password' => $this->password,
      // ],
    );
  }

  /**
   * Get the attachments for the message.
   *
   * @return array<int, \Illuminate\Mail\Mailables\Attachment>
   */
  public function attachments(): array
  {
    return []; // Define any attachments here if needed
  }

  /**
   * Build the message. (Alternative to content() and envelope() for older Laravel versions)
   *
   * @return $this
   */
  // public function build()
  // {
  //     return $this->subject('Selamat Datang ke MOTAC ICT - Akaun E-mel Anda Disediakan')
  //                 ->view('emails.welcome')
  //                 ->with([
  //                     'user' => $this->user,
  //                     'motacEmail' => $this->motacEmail,
  //                     'password' => $this->password,
  //                 ]);
  // }
}
