<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\ProfessionalProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserUnitTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 7: Verifica que los campos del modelo User sean asignables en masa (fillable).
     */
    public function test_user_attributes_are_mass_assignable(): void
    {
        $user = new User([
            'name' => 'Juan Pérez',
            'rut' => '12.345.678-9',
            'email' => 'juan.perez@example.com',
            'password' => 'secreto123',
        ]);

        $this->assertEquals('Juan Pérez', $user->name);
        $this->assertEquals('12.345.678-9', $user->rut);
        $this->assertEquals('juan.perez@example.com', $user->email);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('secreto123', $user->password));
    }

    /**
     * Test 8: Verifica que la asignación de roles mediante Spatie funcione adecuadamente.
     */
    public function test_user_roles_can_be_assigned_and_checked(): void
    {
        $paciente = User::factory()->create();
        $paciente->assignRole('paciente');

        $medico = User::factory()->create();
        $medico->assignRole('medico');

        $this->assertTrue($paciente->hasRole('paciente'));
        $this->assertFalse($paciente->hasRole('medico'));

        $this->assertTrue($medico->hasRole('medico'));
        $this->assertFalse($medico->hasRole('admin'));
    }

    /**
     * Test 9: Verifica la relación uno a uno entre el User y su ProfessionalProfile.
     */
    public function test_user_has_one_professional_profile(): void
    {
        $doctor = User::factory()->create();
        $doctor->assignRole('medico');

        $profile = ProfessionalProfile::create([
            'user_id' => $doctor->id,
            'box_number' => 'Box 301',
            'consultation_duration_minutes' => 30,
        ]);

        $this->assertInstanceOf(ProfessionalProfile::class, $doctor->professionalProfile);
        $this->assertEquals($profile->id, $doctor->professionalProfile->id);
    }
}
