<?php

namespace App\Mail;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Message $message) {}

    public function envelope(): Envelope
    {
        $senderName = $this->message->sender->full_name;

        return new Envelope(
            from: new Address(config('mail.from_notifications.address'), config('mail.from_notifications.name')),
            subject: "Nuovo messaggio da {$senderName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-message',
        );
    }
}
