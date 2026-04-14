<?php

namespace App\Mail;

use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CheckInReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ClientProfile $client,
        public User $nutritionist,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Promemoria: compila il tuo check-in settimanale',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.checkin-reminder',
        );
    }
}
