<?php

namespace App\Mail; // Ensure the namespace is correct for your project

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\LoanApplication; // Import your LoanApplication model
use Illuminate\Mail\Mailables\Address; // Import Address for 'to', 'from', etc.
use Illuminate\Mail\Mailables\Tag; // Import Tag for envelope tags - ADDED THIS IMPORT
use Illuminate\Mail\Mailables\Metadata; // Import Metadata for envelope metadata
use Illuminate\Support\Facades\Log; // Import Log facade for logging
use Illuminate\Mail\Mailables\Attachment; // Import Attachment for attachments (if needed)
use Illuminate\Mail\Mailables\Headers; // Import Headers for custom headers (if needed)


/**
 * Mailable notification sent to the applicant when their loan application equipment has been issued.
 * This email is intended to be queued for better performance.
 */
class LoanApplicationIssued extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  /**
   * The loan application instance that has been issued.
   *
   * @var \App\Models\LoanApplication
   */
  public LoanApplication $loanApplication;

  /**
   * Create a new message instance.
   *
   * @param \App\Models\LoanApplication $loanApplication The loan application model instance.
   */
  public function __construct(LoanApplication $loanApplication)
  {
    // Log that the Mailable instance is being created
    Log::info('LoanApplicationIssued Mailable: New Mailable instance created.', ['loan_application_id' => $loanApplication->id]);

    $this->loanApplication = $loanApplication;
    // Mark the mailable for queuing
    $this->onQueue('emails'); // Specify a queue name (optional, defaults to default queue)
  }

  /**
   * Get the message envelope definition.
   * Defines the subject, sender, and recipients of the email.
   * Also includes tags and metadata for tracking.
   *
   * @return \Illuminate\Mail\Mailables\Envelope
   */
  public function envelope(): Envelope
  {
    // Use a dynamic subject including the application ID and applicant name for clarity
    // Safely access applicant name using optional chaining and null coalescing
    $applicantName = $this->loanApplication->user?->full_name ?? $this->loanApplication->user?->name ?? 'Pemohon Tidak Diketahui'; // Assuming user relationship and full_name/name attribute
    $applicationId = $this->loanApplication->id ?? 'N/A';

    // Safely get the recipient's email address from the applicant's user model
    // Use optional chaining (?->) to handle cases where $this->loanApplication->user or user->email might be null.
    $recipientEmail = $this->loanApplication->user?->email;

    // Ensure there is a valid recipient email before adding to the 'to' array
    $to = [];
    if ($recipientEmail) {
      // Use Address object for better recipient handling, including name if available
      $to[] = new Address($recipientEmail, $applicantName);
      Log::info("LoanApplicationIssued Mailable: Recipient found for Loan Application ID: {$applicationId}", ['recipient_email' => $recipientEmail]);
    } else {
      // Handle the case where the recipient email is not found, e.g., log a warning
      Log::warning("LoanApplicationIssued Mailable: Recipient email not found for Loan Application ID: {$applicationId}. Cannot send notification.", ['applicant_user_id' => $this->loanApplication->user_id ?? 'N/A']);
      // Optionally, you could set a fallback recipient from configuration:
      // $fallbackEmail = config('mail.admin_notification_email'); // Define this in config/mail.php
      // if ($fallbackEmail) {
      //      $to[] = new Address($fallbackEmail, 'Admin Notifikasi');
      //      Log::info("LoanApplicationIssued Mailable: Using fallback recipient for Loan Application ID: {$applicationId}");
      // } else {
      // If no fallback, the 'to' array will be empty, and the email won't be sent by Laravel.
      // Log an error indicating failure to send.
      Log::error("LoanApplicationIssued Mailable: Failed to find any recipient for Loan Application ID: {$applicationId}. Email will not be sent.");
      // }
    }


    Log::info('LoanApplicationIssued Mailable: Preparing email envelope.', [
      'loan_application_id' => $applicationId,
      'subject' => "Notifikasi Peralatan Pinjaman ICT Telah Dikeluarkan (Permohonan #{$applicationId} - {$applicantName})",
      'to_recipients' => $to, // Log the determined recipients
    ]);


    return new Envelope(
      // Subject: Use dynamic elements for clarity
      subject: "Notifikasi Peralatan Pinjaman ICT Telah Dikeluarkan (Permohonan #{$applicationId} - {$applicantName})", // Dynamic Email Subject

      // Set the recipient(s). Use the array of Address objects or email strings.
      to: $to,

      // You can set a default 'from' address in your config/mail.php
      // from: new Address(config('mail.from.address'), config('mail.from.name')), // Example custom from address

      // Add tags for tracking emails in services like Postmark, Mailgun, or AWS SES.
      tags: [new Tag('loan-application'), new Tag('issued-notification')], // Using imported Tag class

      // Add metadata for tracking emails.
      metadata: [
        'loan_application_id' => $this->loanApplication->id,
        'applicant_user_id' => $this->loanApplication->user_id, // Assuming user_id is directly available
      ],

      // You can also set 'cc', 'bcc', 'replyTo' here if needed
      // cc: [...],
      // bcc: [...],
      // replyTo: [...],
    );
  }

  /**
   * Get the message content definition.
   * Defines the Blade view and data passed to the view.
   *
   * @return \Illuminate\Mail\Mailables\Content
   */
  public function content(): Content
  {
    Log::info('LoanApplicationIssued Mailable: Preparing email content.', [
      'loan_application_id' => $this->loanApplication->id ?? 'N/A',
      'view' => 'emails.loan-application-issued',
    ]);

    // You should pass specific details needed in the email view here.
    // Eager load necessary relationships on $this->loanApplication before passing it
    // if they are not already loaded when the Mailable is constructed.
    // Example: $this->loanApplication->load(['user', 'transactions.equipment', 'transactions.issuingOfficer']);

    return new Content(
      view: 'emails.loan-application-issued', // The Blade template file path (resources/views/emails/loan-application-issued.blade.php)
      with: [
        'loanApplication' => $this->loanApplication, // Pass the loan application model instance to the view
        // Pass specific details derived from the application or related transactions:
        // 'issuedItems' => $this->loanApplication->issuedTransactions->pluck('equipment'), // Example if issuedTransactions relationship exists
        // 'issuanceDate' => $this->loanApplication->latestIssuedTransaction->issued_at, // Example
        // 'issuingOfficerName' => $this->loanApplication->latestIssuedTransaction->issuingOfficer->full_name, // Example
        // 'linkToApplication' => route('loan-applications.show', $this->loanApplication), // Example passing a link
      ],
    );
  }

  /**
   * Get the attachments for the message.
   * Optionally attach files (e.g., PDF receipt, terms and conditions).
   *
   * @return array<int, \Illuminate\Mail\Mailables\Attachment> An array of Attachment objects.
   */
  public function attachments(): array
  {
    // If you need to attach files related to the application issuance, define them here.
    // Example: Attach a dynamically generated PDF of the issuance receipt.
    // use Illuminate\Mail\Mailables\Attachment; // Already imported above
    // use Barryvdh\DomPDF\Facade\Pdf; // If using DomPDF
    //
    // $pdf = Pdf::loadView('pdf.issuance_receipt', ['transaction' => $this->loanApplication->latestIssuedTransaction]);
    // return [
    //      Attachment::fromData(fn () => $pdf->output(), 'issuance_receipt.pdf')
    //                 ->withMime('application/pdf'),
    // ];
    return []; // No attachments by default
  }

  /**
   * Get the headers for the message.
   *
   * @return array<string, \Illuminate\Mail\Mailables\Header>
   */
  // Optional method to add custom headers (e.g., Reply-To, CC BPM staff)
  // use Illuminate\Mail\Mailables\Headers; // Already imported above
  // public function headers(): Headers
  // {
  //     // Example: Reply-To the applicant and CC the BPM Staff email group
  //     return Headers::make([
  //          'Reply-To' => $this->loanApplication->user->email,
  //          'Cc' => config('mail.bpm_email_group'), // Define this in config/mail.php
  //     ]);
  // }
}
