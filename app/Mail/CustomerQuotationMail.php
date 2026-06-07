<?php

namespace App\Mail;

use App\Models\CustomerQuotation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerQuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public CustomerQuotation $quotation;

    public function __construct(CustomerQuotation $quotation)
    {
        $this->quotation = $quotation;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Fabric Quotation Request from ' . $this->quotation->customer_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.customer-quotation',
        );
    }
}
