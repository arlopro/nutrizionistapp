<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Appointment $appointment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from_notifications.address'), config('mail.from_notifications.name')),
            subject: 'Promemoria: appuntamento il ' . $this->appointment->starts_at->translatedFormat('d F Y \a\l\l\e H:i'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.appointment-reminder',
        );
    }
}
