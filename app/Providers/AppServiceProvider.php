<?php

namespace App\Providers;

use App\Models\AnamnesisTemplate;
use App\Models\Appointment;
use App\Models\CheckIn;
use App\Models\ClientProfile;
use App\Models\Food;
use App\Models\NutritionalPlan;
use App\Models\Recipe;
use App\Policies\AnamnesisTemplatePolicy;
use App\Policies\AppointmentPolicy;
use App\Policies\CheckInPolicy;
use App\Policies\ClientProfilePolicy;
use App\Policies\FoodPolicy;
use App\Policies\NutritionalPlanPolicy;
use App\Policies\RecipePolicy;
use App\Events\ImpersonationStarted;
use App\Events\ImpersonationStopped;
use App\Listeners\LogImpersonationStarted;
use App\Listeners\LogImpersonationStopped;
use App\Listeners\LogSuccessfulLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use App\Services\PaymentCredentials;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        PaymentCredentials::applyToConfig();

        Event::listen(Login::class, LogSuccessfulLogin::class);
        Event::listen(ImpersonationStarted::class, LogImpersonationStarted::class);
        Event::listen(ImpersonationStopped::class, LogImpersonationStopped::class);

        // Reset password in italiano
        ResetPassword::toMailUsing(function (object $notifiable, string $token) {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Reimposta la tua password — ' . config('app.name'))
                ->from(config('mail.from.address'), config('mail.from.name'))
                ->greeting('Ciao ' . $notifiable->name . '!')
                ->line('Hai richiesto di reimpostare la password del tuo account.')
                ->action('Reimposta password', $url)
                ->line('Il link scade tra **60 minuti**.')
                ->line('Se non hai richiesto il reset della password, ignora questa email — il tuo account è al sicuro.')
                ->salutation('Il team di ' . config('app.name'));
        });

        // Verifica email in italiano
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verifica il tuo indirizzo email — ' . config('app.name'))
                ->from(config('mail.from.address'), config('mail.from.name'))
                ->greeting('Ciao ' . $notifiable->name . '!')
                ->line('Clicca sul pulsante per verificare il tuo indirizzo email.')
                ->action('Verifica email', $url)
                ->line('Il link scade tra 60 minuti.')
                ->line('Se non hai creato un account, ignora questa email.')
                ->salutation('Il team di ' . config('app.name'));
        });

        Gate::policy(NutritionalPlan::class, NutritionalPlanPolicy::class);
        Gate::policy(ClientProfile::class, ClientProfilePolicy::class);
        Gate::policy(Appointment::class, AppointmentPolicy::class);
        Gate::policy(Recipe::class, RecipePolicy::class);
        Gate::policy(Food::class, FoodPolicy::class);
        Gate::policy(AnamnesisTemplate::class, AnamnesisTemplatePolicy::class);
        Gate::policy(CheckIn::class, CheckInPolicy::class);

        // Dev role bypasses all authorization gates
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('dev')) {
                return true;
            }
        });
    }
}
