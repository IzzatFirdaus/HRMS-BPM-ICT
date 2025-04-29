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
    // Use a dynamic subject including the application ID and applicant name for clarity
    $applicantName = $this->application->user->name ?? 'Pemohon'; // Assuming user relationship and name attribute

    return new Envelope(
      subject: "Ralat Peruntukan Emel: Gagal Untuk Permohonan #{$this->application->id} ({$applicantName})", // Dynamic and translated Email Subject
      // Recipient is typically set when sending this Mailable, e.g., to IT support staff
      // Mail::to(config('app.it_support_email'))->send(new ProvisioningFailedNotification(...));
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
    //     'application' => $this->application,
    //     'errorMessage' => $this->errorMessage,
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
