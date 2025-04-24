<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
  use Queueable, SerializesModels;

  public $user; // You can pass data to the email view

  /**
   * Create a new message instance.
   */
  public function __construct($user)
  {
    $this->user = $user; // Assign the user data to a public property
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    return new Envelope(
      subject: 'Welcome to MOTAC HRMS!', // Define the email subject
      // You can also set the sender here if it's different from your mail.php config
      // from: new Address('noreply@motac.gov.my', 'MOTAC HRMS'),
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      // Define the Blade view for the email body
      view: 'emails.welcome',
      // You can also pass data to the view explicitly if you prefer,
      // but public properties on the Mailable are automatically available.
      // with: [
      //     'user' => $this->user,
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
}
