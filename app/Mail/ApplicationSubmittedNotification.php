<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model; // To handle polymorphic relationship

class ApplicationSubmittedNotification extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  /**
   * The application instance (EmailApplication or LoanApplication).
   *
   * @var \Illuminate\Database\Eloquent\Model
   */
  public $application;

  /**
   * Create a new message instance.
   */
  public function __construct(Model $application)
  {
    $this->application = $application;
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    // Determine the application type for the subject
    $applicationType = '';
    if ($this->application instanceof \App\Models\EmailApplication) {
      $applicationType = 'Permohonan Akaun Emel ICT';
    } elseif ($this->application instanceof \App\Models\LoanApplication) {
      $applicationType = 'Permohonan Pinjaman Peralatan ICT';
    } else {
      $applicationType = 'Permohonan Baru';
    }

    return new Envelope(
      subject: 'Tindakan Diperlukan: ' . $applicationType . ' Baru Dihantar (#' . $this->application->id . ')', // Email Subject
      // The recipient (approver) would typically be set when sending this Mailable,
      // e.g., Mail::to($approver->email)->send(new ApplicationSubmittedNotification($application));
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      view: 'emails.application-submitted-notification', // The Blade template
      with: [
        'application' => $this->application, // Pass the application data to the view
      ],
    );
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
