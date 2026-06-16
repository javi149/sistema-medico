<?php

namespace Database\Factories;

use App\Models\ProfessionalProfile;
use App\Models\Specialty;
use App\Models\User;
use App\Models\Waitlist;
use Illuminate\Database\Eloquent\Factories\Factory;

class WaitlistFactory extends Factory
{
    protected $model = Waitlist::class;

    public function definition(): array
    {
        return [
            'patient_id' => User::factory(),
            'professional_profile_id' => $this->faker->optional(0.7)->randomElement(
                ProfessionalProfile::pluck('id')->toArray() ?: [null]
            ),
            'specialty_id' => Specialty::inRandomOrder()->first()?->id,
            'status' => $this->faker->randomElement(['Pendiente', 'Pendiente', 'Pendiente', 'Notificado', 'Reasignado', 'Cancelado']),
            'managed_by' => null,
            'created_at' => $this->faker->dateTimeBetween('-2 months', 'now'),
        ];
    }

    /**
     * Registro pendiente (sin gestionar).
     */
    public function pendiente(): static
    {
        return $this->state(fn () => [
            'status' => 'Pendiente',
            'managed_by' => null,
        ]);
    }

    /**
     * Registro ya gestionado por un admin.
     */
    public function gestionado(): static
    {
        return $this->state(fn () => [
            'status' => $this->faker->randomElement(['Notificado', 'Reasignado']),
        ]);
    }
}
