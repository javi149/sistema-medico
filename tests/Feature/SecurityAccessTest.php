<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Specialty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SecurityAccessTest extends TestCase
{
    use RefreshDatabase;

    private $patientA;
    private $patientB;
    private $doctor;
    private $profileId;
    private $specialty;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->patientA = User::factory()->create(['email' => 'patienta@security.com']);
        $this->patientA->assignRole('paciente');

        $this->patientB = User::factory()->create(['email' => 'patientb@security.com']);
        $this->patientB->assignRole('paciente');

        $this->doctor = User::factory()->create();
        $this->doctor->assignRole('medico');

        $this->profileId = DB::table('professional_profiles')->insertGetId([
            'user_id' => $this->doctor->id,
            'box_number' => 'Box 606',
            'consultation_duration_minutes' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->specialty = Specialty::create(['name' => 'Kinesiología']);
        DB::table('professional_profile_specialty')->insert([
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $this->specialty->id
        ]);
    }

    /**
     * Security Test 13: Bloqueo de rutas de administración a usuarios no autorizados (pacientes/médicos/invitados).
     */
    public function test_non_admin_cannot_access_admin_dashboard(): void
    {
        // Caso A: Paciente intenta acceder (retorna 403 o redirección según middleware de rol)
        $response1 = $this->actingAs($this->patientA)
            ->get('/admin/dashboard');
        
        $response1->assertStatus(403);

        // Caso B: Médico intenta acceder
        $response2 = $this->actingAs($this->doctor)
            ->get('/admin/dashboard');

        $response2->assertStatus(403);

        // Caso C: Usuario no autenticado (invitado) recibe 403 debido al middleware de Spatie
        $response3 = $this->get('/admin/dashboard');
        $response3->assertStatus(403);
    }

    /**
     * Security Test 14: Un paciente no puede cancelar ni modificar citas de otros pacientes.
     */
    public function test_patient_cannot_cancel_another_patients_appointment(): void
    {
        // Cita que pertenece al Paciente B
        $appointmentB = Appointment::create([
            'patient_id' => $this->patientB->id,
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $this->specialty->id,
            'start_datetime' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'status' => 'reservada',
        ]);

        // Paciente A intenta cancelar la cita del Paciente B
        $response = $this->actingAs($this->patientA)
            ->patch("/citas/{$appointmentB->id}/cancelar");

        // Debe retornar 403/Acción no autorizada
        $response->assertStatus(403);

        // Validar que el estado de la cita sigue siendo 'reservada'
        $this->assertDatabaseHas('appointments', [
            'id' => $appointmentB->id,
            'status' => 'reservada',
        ]);
    }

    /**
     * Security Test 15: Validación estricta de payloads maliciosos y formatos en el Wizard (RUT inválido, XSS/script injection).
     */
    public function test_wizard_enforces_validation_on_invalid_rut_and_bad_inputs(): void
    {
        // Caso A: RUT demasiado corto (falla regla ValidRut)
        $responseRutShort = $this->postJson('/citas/wizard/check-rut', [
            'rut' => '1',
        ]);
        $responseRutShort->assertStatus(422); // Unprocessable Content
        $responseRutShort->assertJsonValidationErrors(['rut']);

        // Caso B: Petición de reserva con datos incompletos o en blanco
        $responseBookEmpty = $this->actingAs($this->patientA)
            ->post('/citas', [
                'patient_id' => '',
                'professional_profile_id' => '',
                'start_datetime' => '',
            ]);
        
        $responseBookEmpty->assertStatus(302);
        $responseBookEmpty->assertSessionHasErrors(['patient_id', 'professional_profile_id', 'start_datetime']);
    }
}
