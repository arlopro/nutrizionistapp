<?php

namespace App\Console\Commands;

use App\Mail\AppointmentReminder;
use App\Mail\CheckInReminder;
use App\Models\Appointment;
use App\Models\NutritionistProfile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendScheduledNotifications extends Command
{
    protected $signature = 'notifications:send-scheduled';

    protected $description = 'Send appointment reminders and weekly check-in reminders';

    public function handle(): int
    {
        $this->sendAppointmentReminders();
        $this->sendCheckInReminders();

        return self::SUCCESS;
    }

    private function sendAppointmentReminders(): void
    {
        $nutritionists = NutritionistProfile::whereNotNull('notification_settings')->get();

        // Also include those without settings (use defaults)
        $allNutritionists = NutritionistProfile::with('user')->get();

        foreach ($allNutritionists as $profile) {
            $settings = $profile->mergedNotificationSettings();

            if (! $settings['appointment_reminder']) {
                continue;
            }

            $hours = $settings['appointment_reminder_hours'] ?? 24;

            $windowStart = now()->addHours($hours)->subMinutes(30);
            $windowEnd   = now()->addHours($hours)->addMinutes(30);

            $appointments = Appointment::where('nutritionist_id', $profile->user_id)
                ->whereNotNull('client_id')
                ->whereNotIn('status', ['cancelled', 'completed', 'no_show'])
                ->whereBetween('starts_at', [$windowStart, $windowEnd])
                ->with(['client.user', 'nutritionist'])
                ->get();

            foreach ($appointments as $appointment) {
                $clientEmail = $appointment->client?->user?->email;
                if (! $clientEmail) {
                    continue;
                }

                Mail::to($clientEmail)->queue(new AppointmentReminder($appointment));
                $this->info("Appointment reminder sent to {$clientEmail} for appointment #{$appointment->id}");
            }
        }
    }

    private function sendCheckInReminders(): void
    {
        $today = strtolower(now()->translatedFormat('l'));

        $allNutritionists = NutritionistProfile::with('user')->get();

        foreach ($allNutritionists as $profile) {
            $settings = $profile->mergedNotificationSettings();

            if (! $settings['checkin_reminder']) {
                continue;
            }

            $dayMap = [
                'monday' => 'lunedì', 'tuesday' => 'martedì', 'wednesday' => 'mercoledì',
                'thursday' => 'giovedì', 'friday' => 'venerdì', 'saturday' => 'sabato', 'sunday' => 'domenica',
            ];

            $reminderDay = $settings['checkin_reminder_day'] ?? 'monday';
            $italianDay  = $dayMap[$reminderDay] ?? $reminderDay;

            if ($today !== $italianDay) {
                continue;
            }

            $nutritionist = $profile->user;
            $clients = $nutritionist->clients()
                ->where('status', 'active')
                ->with('user')
                ->get();

            foreach ($clients as $client) {
                $clientEmail = $client->user?->email;
                if (! $clientEmail) {
                    continue;
                }

                // Skip if client already has a check-in this week
                $hasRecentCheckIn = $client->checkIns()
                    ->where('date', '>=', now()->startOfWeek())
                    ->exists();

                if ($hasRecentCheckIn) {
                    continue;
                }

                Mail::to($clientEmail)->queue(new CheckInReminder($client, $nutritionist));
                $this->info("Check-in reminder sent to {$clientEmail}");
            }
        }
    }
}
