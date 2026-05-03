<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ServerErrorReport extends Mailable
{
    use Queueable, SerializesModels;

    public string $errorClass;
    public string $errorMessage;
    public string $stackTrace;
    public string $url;
    public string $method;
    public ?int $userId;
    public string $ip;
    public string $occurredAt;

    public function __construct(\Throwable $exception, Request $request)
    {
        $this->errorClass   = get_class($exception);
        $this->errorMessage = $exception->getMessage();
        $this->stackTrace   = collect(explode("\n", $exception->getTraceAsString()))->take(20)->implode("\n");
        $this->url          = $request->fullUrl();
        $this->method       = $request->method();
        $this->userId       = $request->user()?->id;
        $this->ip           = $request->ip();
        $this->occurredAt   = now()->toDateTimeString();
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from_errors.address'), config('mail.from_errors.name')),
            subject: '[NutrizionistApp] Errore 500: ' . $this->errorClass,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.server-error',
        );
    }
}
