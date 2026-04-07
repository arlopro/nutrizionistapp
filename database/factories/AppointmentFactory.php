<?php

namespace Database\Factories;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Models\Appointment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Appointment> */
class AppointmentFactory extends Factory
{
    public function definition(): array
    {
        $isPast = fake()->boolean(65);

        if ($isPast) {
            $startsAt = fake()->dateTimeBetween('-6 months', '-1 day');
            $status   = fake()->randomElement([
                AppointmentStatus::Completed->value,
                AppointmentStatus::Completed->value,
                AppointmentStatus::Completed->value,
                AppointmentStatus::NoShow->value,
                AppointmentStatus::Cancelled->value,
            ]);
        } else {
            $startsAt = fake()->dateTimeBetween('+1 day', '+3 months');
            $status   = fake()->randomElement([
                AppointmentStatus::Scheduled->value,
                AppointmentStatus::Confirmed->value,
                AppointmentStatus::Confirmed->value,
            ]);
        }

        $type     = fake()->randomElement(AppointmentType::cases());
        $duration = fake()->randomElement([30, 45, 60, 60, 90]);
        $endsAt   = (clone $startsAt)->modify("+{$duration} minutes");

        $titles = [
            AppointmentType::FirstVisit->value => 'Prima visita',
            AppointmentType::FollowUp->value   => 'Visita di controllo',
            AppointmentType::Online->value     => 'Consulenza online',
            AppointmentType::Other->value      => 'Appuntamento',
        ];

        return [
            'nutritionist_id' => null,
            'client_id'       => null,
            'title'           => $titles[$type->value],
            'type'            => $type->value,
            'status'          => $status,
            'starts_at'       => $startsAt,
            'ends_at'         => $endsAt,
            'location'        => fake()->boolean(50) ? fake()->randomElement(['Studio via Roma 12', 'Studio via Garibaldi 5', 'Online (Zoom)', 'Studio centro']) : null,
            'notes'           => fake()->boolean(30) ? fake('it_IT')->sentence(fake()->numberBetween(6, 18)) : null,
        ];
    }
}
