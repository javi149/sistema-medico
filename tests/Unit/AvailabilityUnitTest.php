<?php

namespace Tests\Unit;

use App\Models\Availability;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AvailabilityUnitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: El accessor getDayNameAttribute traduce correctamente el número de día al español.
     */
    public function test_get_day_name_returns_correct_spanish_name(): void
    {
        $availability = new Availability(['day_of_week' => 1]); // Lunes
        $this->assertEquals('Lunes', $availability->day_name);

        $availability2 = new Availability(['day_of_week' => 5]); // Viernes
        $this->assertEquals('Viernes', $availability2->day_name);
    }

    /**
     * Test 2: Un número de día fuera de rango retorna 'Desconocido'.
     */
    public function test_get_day_name_returns_desconocido_for_out_of_bounds_day(): void
    {
        $availability = new Availability(['day_of_week' => 99]);
        $this->assertEquals('Desconocido', $availability->day_name);

        $availability2 = new Availability(['day_of_week' => -1]);
        $this->assertEquals('Desconocido', $availability2->day_name);
    }

    /**
     * Test 3: Verifica que la relación de disponibilidad con el perfil profesional funcione.
     */
    public function test_availability_belongs_to_professional_profile(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $profile = ProfessionalProfile::create([
            'user_id' => $doctor->id,
            'box_number' => 'Box 105',
            'consultation_duration_minutes' => 20,
        ]);

        $availability = Availability::create([
            'professional_profile_id' => $profile->id,
            'day_of_week' => 2,
            'start_time' => '09:00:00',
            'end_time' => '13:00:00',
        ]);

        $this->assertInstanceOf(ProfessionalProfile::class, $availability->professionalProfile);
        $this->assertEquals($profile->id, $availability->professionalProfile->id);
    }
}
