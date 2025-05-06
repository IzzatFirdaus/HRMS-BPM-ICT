<?php

namespace App\Mail;

use App\Models\EmailApplication;
use App\Models\User; // Import User model
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
  // *** FIX 1: Renamed property to match argument passed from service ***
  public string $reason;
  // *** FIX 2: Added public property for the admin user ***
  public ?User $adminUser;

  // Add other public properties for data needed in the email view

  /**
   * Create a new message instance.
   */
  // *** FIX 3: Modified constructor to accept 3 arguments matching the service call ***
  public function __construct(EmailApplication $application, string $reason, ?User $adminUser = null)
  {
    $this->application = $application;
    // *** FIX 4: Assign the reason argument ***
    $this->reason = $reason;
    // *** FIX 5: Assign the adminUser argument ***
    $this->adminUser = $adminUser;
    // Initialize other properties
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    // Use a dynamic subject including the application ID and applicant name for clarity
    $applicantName = $this->application->user->name ?? 'Pemohon Tidak Dikenali'; // Assuming user relationship and name attribute

    return new Envelope(
      // *** FIX 6: Use the $reason property in the subject ***
      subject: "HRMS: Ralat Peruntukan Emel - Gagal Untuk Permohonan #{$this->application->id} ({$applicantName})", // Dynamic and translated Email Subject
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
        // *** FIX 7: Pass the reason property to the view ***
        'reason' => $this->reason,
        // *** FIX 8: Pass the adminUser property to the view ***
        'adminUser' => $this->adminUser,
        // Pass other data needed in the view
      ],
    );

    // Or use a standard Blade view
    // return new Content(
    //     view: 'emails.provisioning-failed-blade', // Ensure this view path exists
    //     with: [ // Data passed to the view
    //     'application' => $this->application,
    //     'errorMessage' => $this->errorMessage, // Or use 'reason' if adapting
    //     ],
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
