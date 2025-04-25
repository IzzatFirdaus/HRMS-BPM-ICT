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

class LoanApplicationReturned extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  /**
   * The loan application instance.
   *
   * @var \App\Models\LoanApplication
   */
  public $loanApplication;

  /**
   * The loan transaction instance that was just returned.
   *
   * @var \App\Models\LoanTransaction
   */
  public $loanTransaction;


  /**
   * Create a new message instance.
   */
  public function __construct(LoanApplication $loanApplication, LoanTransaction $loanTransaction)
  {
    $this->loanApplication = $loanApplication;
    $this->loanTransaction = $loanTransaction;
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    return new Envelope(
      subject: 'Notifikasi Peralatan Pinjaman ICT Telah Dipulangkan', // Email Subject
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
      view: 'emails.loan-application-returned', // The Blade template
      with: [
        'loanApplication' => $this->loanApplication, // Pass the loan application data
        'loanTransaction' => $this->loanTransaction, // Pass the specific transaction data
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
