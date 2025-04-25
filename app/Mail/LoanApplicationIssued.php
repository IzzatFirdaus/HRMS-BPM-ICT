<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\LoanApplication; // Assuming your LoanApplication model is in App\Models

class LoanApplicationIssued extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  /**
   * The loan application instance.
   *
   * @var \App\Models\LoanApplication
   */
  public $loanApplication;

  /**
   * Create a new message instance.
   */
  public function __construct(LoanApplication $loanApplication)
  {
    $this->loanApplication = $loanApplication;
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    return new Envelope(
      subject: 'Notifikasi Peralatan Pinjaman ICT Telah Dikeluarkan', // Email Subject
      // You might want to set the recipient here if not doing it when sending
      // to: $this->loanApplication->user->email, // Assuming user relationship and email attribute
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      view: 'emails.loan-application-issued', // The Blade template
      with: [
        'loanApplication' => $this->loanApplication, // Pass the loan application data to the view
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
