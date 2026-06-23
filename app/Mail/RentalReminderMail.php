<?php

namespace App\Mail;

use App\Models\Rental;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RentalReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Rental $rental;

    public string $customMessage;

    public function __construct(Rental $rental, string $customMessage)
    {
        $this->rental = $rental;
        $this->customMessage = $customMessage;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Friendly Reminder — ' . $this->rental->room->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.rental-reminder',
        );
    }
}
