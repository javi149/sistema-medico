<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\ProfessionalProfile;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'patient_id' => User::factory(),
            'professional_profile_id' => ProfessionalProfile::inRandomOrder()->first()?->id ?? 1,
            'specialty_id' => Specialty::inRandomOrder()->first()?->id ?? 1,
            'start_datetime' => $this->faker->dateTimeBetween('-3 months', '+1 month'),
            'status' => $this->faker->randomElement(['reservada', 'confirmada', 'cancelada', 'atendida', 'ausente']),
        ];
    }

    /**
     * Cita en el pasado (ya atendida, cancelada o ausente).
     */
    public function pasada(): static
    {
        return $this->state(fn () => [
            'start_datetime' => $this->faker->dateTimeBetween('-3 months', '-1 day'),
            'status' => $this->faker->randomElement(['atendida', 'atendida', 'atendida', 'cancelada', 'ausente']),
        ]);
    }

    /**
     * Cita futura (reservada o confirmada).
     */
    public function futura(): static
    {
        return $this->state(fn () => [
            'start_datetime' => $this->faker->dateTimeBetween('+1 day', '+1 month'),
            'status' => $this->faker->randomElement(['reservada', 'confirmada']),
        ]);
    }

    /**
     * Cita para hoy (útil para el dashboard).
     */
    public function hoy(): static
    {
        $hora = $this->faker->numberBetween(8, 17);
        $minuto = $this->faker->randomElement([0, 15, 30, 45]);

        return $this->state(fn () => [
            'start_datetime' => now()->setTime($hora, $minuto, 0),
            'status' => $this->faker->randomElement(['reservada', 'confirmada']),
        ]);
    }
}
