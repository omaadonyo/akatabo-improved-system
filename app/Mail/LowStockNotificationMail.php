<?php

namespace App\Mail;

use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LowStockNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Business $business,
        public Collection $products,
        public Collection $fabrics,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Low Stock Alert — :business', ['business' => $this->business->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.low-stock-notification',
        );
    }
}
