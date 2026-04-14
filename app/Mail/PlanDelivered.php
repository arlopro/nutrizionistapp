<?php

namespace App\Mail;

use App\Models\NutritionalPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlanDelivered extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public NutritionalPlan $plan) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Il tuo nuovo piano nutrizionale è pronto!',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.plan-delivered',
        );
    }
}
