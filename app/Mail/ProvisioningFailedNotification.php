<?php

namespace App\Mail;

use App\Models\EmailApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProvisioningFailedNotification extends Mailable implements ShouldQueue // Implement ShouldQueue if sending asynchronously
{
  use Queueable, SerializesModels;

  public EmailApplication $application;
  public string $errorMessage;
  // Add other public properties for data needed in the email view

  /**
   * Create a new message instance.
   */
  public function __construct(EmailApplication $application, string $errorMessage)
  {
    $this->application = $application;
    $this->errorMessage = $errorMessage;
    // Initialize other properties
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    return new Envelope(
      subject: 'Email Provisioning Failed for Application ID: ' . $this->application->id,
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    // Use a Markdown view
    return new Content(
      markdown: 'emails.provisioning-failed', // Ensure this view path exists
      with: [ // Data passed to the view
        'application' => $this->application,
        'errorMessage' => $this->errorMessage,
        // Pass other data needed in the view
      ],
    );

    // Or use a standard Blade view
    // return new Content(
    //     view: 'emails.provisioning-failed-blade', // Ensure this view path exists
    //     with: [ // Data passed to the view
    //         'application' => $this->application,
    //         'errorMessage' => $this->errorMessage,
    //     ],
    // );
  }

  /**
   * Get the attachments for the message.
   *
   * @return array<int, \Illuminate\Mail\Mailables\Attachment>
   */
  public function attachments(): array
  {
    return [];
  }
}
