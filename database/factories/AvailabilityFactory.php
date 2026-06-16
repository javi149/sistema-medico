<?php

namespace Database\Factories;

use App\Models\Availability;
use App\Models\ProfessionalProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

class AvailabilityFactory extends Factory
{
    protected $model = Availability::class;

    public function definition(): array
    {
        // Genera bloques de horario realistas para consultorios médicos
        $startHour = $this->faker->randomElement([8, 9, 14, 15]);
        $endHour = $startHour + $this->faker->randomElement([3, 4, 5]);

        return [
            'professional_profile_id' => ProfessionalProfile::inRandomOrder()->first()?->id ?? 1,
            'day_of_week' => $this->faker->numberBetween(1, 5), // Lunes a Viernes
            'start_time' => sprintf('%02d:00:00', $startHour),
            'end_time' => sprintf('%02d:00:00', min($endHour, 18)),
        ];
    }

    /**
     * Turno de mañana (08:00 - 13:00).
     */
    public function manana(): static
    {
        return $this->state(fn () => [
            'start_time' => '08:00:00',
            'end_time' => '13:00:00',
        ]);
    }

    /**
     * Turno de tarde (14:00 - 18:00).
     */
    public function tarde(): static
    {
        return $this->state(fn () => [
            'start_time' => '14:00:00',
            'end_time' => '18:00:00',
        ]);
    }
}
