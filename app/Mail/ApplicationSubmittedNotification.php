<?php

namespace App\Mail; // Ensure the namespace is correct for your project

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model; // To handle polymorphic relationship (EmailApplication or LoanApplication)
use Illuminate\Support\Facades\Log; // Import Log facade for logging


/**
 * Mailable notification sent to the next required approver or BPM staff
 * when a new Email Application or Loan Application is submitted.
 * Implements ShouldQueue to send emails asynchronously.
 */
class ApplicationSubmittedNotification extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  /**
   * The application instance (EmailApplication or LoanApplication) that was submitted.
   *
   * @var \App\Models\EmailApplication|\App\Models\LoanApplication
   */
  public Model $application; // Type hint with Model as the base class, can refine PHPDoc


  /**
   * Create a new message instance.
   *
   * @param \Illuminate\Database\Eloquent\Model $application The submitted application model instance.
   */
  public function __construct(Model $application)
  {
    // Ensure the provided model is an instance of EmailApplication or LoanApplication
    if (!($application instanceof \App\Models\EmailApplication) && !($application instanceof \App\Models\LoanApplication)) {
      Log::error('ApplicationSubmittedNotification: Invalid model type provided to constructor.', ['model_type' => get_class($application), 'application_id' => $application->id ?? 'N/A']);
      // Depending on how strict you want to be, you might throw an exception here
      // throw new \InvalidArgumentException('Invalid application model provided.');
    } else {
      Log::info('ApplicationSubmittedNotification: New Mailable instance created.', ['application_type' => get_class($application), 'application_id' => $application->id]);
    }

    $this->application = $application;
    // Mark the mailable for queuing
    $this->onQueue('emails'); // Specify a queue name (optional, defaults to default queue)
  }

  /**
   * Get the message envelope definition.
   * Defines the subject, from, and potentially recipient(s) if not set dynamically when sending.
   *
   * @return \Illuminate\Mail\Mailables\Envelope
   */
  public function envelope(): Envelope
  {
    // Determine the application type and applicant's name for the subject
    $applicationType = '';
    $applicantName = $this->application->user->full_name ?? $this->application->user->name ?? 'Pemohon Tidak Diketahui'; // Safely get applicant name
    $applicationId = $this->application->id ?? 'N/A';

    if ($this->application instanceof \App\Models\EmailApplication) {
      $applicationType = 'Permohonan Akaun Emel ICT';
    } elseif ($this->application instanceof \App\Models\LoanApplication) {
      $applicationType = 'Permohonan Pinjaman Peralatan ICT';
    } else {
      // Fallback for unexpected model types (should ideally not happen if constructor check is strict)
      $applicationType = 'Permohonan Baru';
      $applicantName = 'Pengguna Sistem';
    }

    $subject = "Tindakan Diperlukan: {$applicationType} Baru Dihantar oleh {$applicantName} (#{$applicationId})"; // More descriptive subject

    Log::info('ApplicationSubmittedNotification: Preparing email envelope.', [
      'application_type' => get_class($this->application),
      'application_id' => $applicationId,
      'subject' => $subject,
    ]);

    return new Envelope(
      subject: $subject, // Email Subject includes type, applicant, and ID
      // The recipient (approver/BPM staff) would typically be set when sending this Mailable,
      // e.g., Mail::to($recipientEmail)->send(new ApplicationSubmittedNotification($application));
      // You can set a default 'from' address in your config/mail.php
      // from: new Address('no-reply@motac.gov.my', 'MOTAC HRMS'), // Example custom from address
    );
  }

  /**
   * Get the message content definition.
   * Defines the Blade view and data passed to it.
   *
   * @return \Illuminate\Mail\Mailables\Content
   */
  public function content(): Content
  {
    Log::info('ApplicationSubmittedNotification: Preparing email content.', [
      'application_type' => get_class($this->application),
      'application_id' => $this->application->id ?? 'N/A',
      'view' => 'emails.application-submitted-notification',
    ]);

    return new Content(
      view: 'emails.application-submitted-notification', // The Blade template file (resources/views/emails/application-submitted-notification.blade.php)
      with: [
        'application' => $this->application, // Pass the application model instance to the view
        // You can pass other variables to the view here if needed
        // 'applicantName' => $this->application->user->full_name ?? $this->application->user->name,
        // 'linkToApplication' => route('loan-applications.show', $this->application), // Example passing a link
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
    // If you need to attach files related to the application, define them here.
    // Example:
    // if ($this->application->hasAttachment) {
    //      return [
    //          Attachment::fromPath(storage_path('app/attachments/' . $this->application->attachment_path))
    //                    ->as($this->application->attachment_filename)
    //                    ->withMime('application/pdf'),
    //      ];
    // }
    return []; // No attachments by default
  }

  /**
   * Get the headers for the message.
   *
   * @return array<string, \Illuminate\Mail\Mailables\Header>
   */
  // Optional method to add custom headers (e.g., Reply-To)
  // public function headers(): array
  // {
  //     return [
  //         // Example: Reply-To the applicant
  //         // Header::replyTo($this->application->user->email, $this->application->user->full_name),
  //     ];
  // }
}
