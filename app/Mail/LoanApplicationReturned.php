<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\LoanApplication; // Assuming your LoanApplication model
use App\Models\LoanTransaction; // Assuming your LoanTransaction model
use Illuminate\Mail\Mailables\Address; // Import Address for 'to' recipient
use Illuminate\Support\Facades\Log; // Import Log facade for error logging


/**
 * Class LoanApplicationReturned
 *
 * Mailable class for notifying the applicant when their loan application equipment has been returned.
 * This email is intended to be queued for better performance.
 */
class LoanApplicationReturned extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  /**
   * The loan application instance.
   *
   * @var \App\Models\LoanApplication
   */
  public LoanApplication $loanApplication;

  /**
   * The loan transaction instance that was just returned.
   *
   * @var \App\Models\LoanTransaction
   */
  public LoanTransaction $loanTransaction;


  /**
   * Create a new message instance.
   *
   * @param \App\Models\LoanApplication $loanApplication The loan application model instance.
   * @param \App\Models\LoanTransaction $loanTransaction The loan transaction model instance for the returned item.
   * @return void
   */
  public function __construct(LoanApplication $loanApplication, LoanTransaction $loanTransaction)
  {
    $this->loanApplication = $loanApplication;
    $this->loanTransaction = $loanTransaction;
  }

  /**
   * Get the message envelope.
   * Defines the subject, sender, and recipients of the email.
   * Also includes tags and metadata for tracking.
   *
   * @return \Illuminate\Mail\Mailables\Envelope
   */
  public function envelope(): Envelope
  {
    // Use a dynamic subject including the application ID, applicant name, and equipment details
    // Safely access applicant name using optional chaining and null coalescing
    $applicantName = $this->loanApplication->user?->full_name ?? $this->loanApplication->user?->name ?? 'Pemohon'; // Assuming user relationship and full_name/name attribute
    $applicationId = $this->loanApplication->id;

    // Attempt to get equipment details if transaction has an equipment relationship
    // Safely access equipment details using optional chaining and null coalescing
    // Assuming Equipment model has 'model' or 'tag_id' attributes for description
    $equipmentDetails = $this->loanTransaction->equipment?->model ?? $this->loanTransaction->equipment?->tag_id ?? 'Peralatan ICT'; // Assuming equipment relationship and model/tag_id attributes

    // Safely get the recipient's email address
    // Use optional chaining (?->) to handle cases where $this->loanApplication->user or user->email might be null.
    $recipientEmail = $this->loanApplication->user?->email;

    // Ensure there is a valid recipient email before creating the Envelope
    $to = [];
    if ($recipientEmail) {
      // Use Address object for better recipient handling, including name if available
      $to[] = new Address($recipientEmail, $applicantName);
    } else {
      // Handle the case where the recipient email is not found, e.g., log an error
      Log::error("LoanApplicationReturned Mailable: Recipient email not found for Loan Application ID: {$applicationId}");
      // Optionally, you could set a fallback recipient from configuration:
      // $to[] = config('mail.from.address'); // Send to a default admin address
    }


    return new Envelope(
      // Subject: Use dynamic elements for clarity
      subject: "Notifikasi {$equipmentDetails} Telah Dipulangkan (Permohonan #{$applicationId} - {$applicantName})", // Dynamic Email Subject

      // Set the recipient(s). Use the array of Address objects or email strings.
      to: $to,

      // Add tags for tracking emails in services like Postmark, Mailgun, or AWS SES.
      tags: ['loan-application', 'returned-notification'],

      // Add metadata for tracking emails.
      metadata: [
        'loan_application_id' => $this->loanApplication->id,
        'loan_transaction_id' => $this->loanTransaction->id,
        'applicant_id' => $this->loanApplication->user_id, // Assuming user_id is directly available
        'equipment_id' => $this->loanTransaction->equipment_id, // Assuming equipment_id is directly available
      ],

      // You can also set 'from', 'cc', 'bcc', 'replyTo' here if needed
      // from: new Address(config('mail.from.address'), config('mail.from.name')),
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
    return new Content(
      view: 'emails.loan-application-returned', // The Blade template file path
      with: [
        'loanApplication' => $this->loanApplication, // Pass the loan application data
        'loanTransaction' => $this->loanTransaction, // Pass the specific transaction data
        // Add any other data needed in the email view
      ],
    );
  }

  /**
   * Get the attachments for the message.
   * Optionally attach files (e.g., copy of the return receipt).
   *
   * @return array<int, \Illuminate\Mail\Mailables\Attachment> An array of Attachment objects.
   */
  public function attachments(): array
  {
    return [
      // Example of attaching a file:
      // Attachment::fromPath('/path/to/your/return_receipt.pdf')
      //           ->as('return_receipt.pdf')
      //           ->withMime('application/pdf'),
    ];
  }

  // Removed separate metadata() and tags() methods to resolve compatibility errors.
  // Metadata and tags are now defined directly in the envelope() method.
}
