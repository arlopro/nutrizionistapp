<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ClientInvitation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $token,
        public User $nutritionist,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $nutritionistName = $this->nutritionist->full_name;
        $clientName = $notifiable->name;

        return (new MailMessage)
            ->subject('Benvenuto su NutrizionistApp — Accedi alla tua area riservata')
            ->from(config('mail.from_support.address'), config('mail.from_support.name'))
            ->view('emails.client-invitation', [
                'resetUrl'        => $resetUrl,
                'nutritionistName'=> $nutritionistName,
                'clientName'      => $clientName,
                'appName'         => config('app.name'),
                'appUrl'          => config('app.url'),
            ]);
    }
}
