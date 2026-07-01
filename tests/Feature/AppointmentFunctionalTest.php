<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Specialty;
use App\Models\ProfessionalProfile;
use App\Models\Waitlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AppointmentFunctionalTest extends TestCase
{
    use RefreshDatabase;

    private $patient;
    private $doctor;
    private $profileId;
    private $specialty;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        // Crear entidades para las pruebas funcionales
        $this->patient = User::factory()->create(['email' => 'func_patient@example.com']);
        $this->patient->assignRole('paciente');

        $this->doctor = User::factory()->create();
        $this->doctor->assignRole('medico');

        $this->profileId = DB::table('professional_profiles')->insertGetId([
            'user_id' => $this->doctor->id,
            'box_number' => 'Box 405',
            'consultation_duration_minutes' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->specialty = Specialty::create(['name' => 'Neurología']);
        DB::table('professional_profile_specialty')->insert([
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $this->specialty->id
        ]);
    }

    /**
     * Functional Test 1: Crear una cita médica a través del endpoint de reservas POST /citas.
     */
    public function test_patient_can_book_appointment_via_post_route(): void
    {
        Mail::fake();

        $appointmentTime = now()->addDays(2)->roundMinutes(30)->format('Y-m-d H:i:s');

        $response = $this->actingAs($this->patient)
            ->post('/citas', [
                'patient_id' => $this->patient->id,
                'professional_profile_id' => $this->profileId,
                'start_datetime' => $appointmentTime,
            ]);

        // Debe redirigir a la vista de éxito
        $response->assertRedirect();
        
        // Verificar registro en base de datos
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $this->patient->id,
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $this->specialty->id,
            'start_datetime' => $appointmentTime,
            'status' => 'reservada',
        ]);

        // Verificar que se haya enviado el correo de confirmación
        Mail::assertSent(\App\Mail\AppointmentBooked::class, function ($mail) {
            return $mail->hasTo('func_patient@example.com');
        });
    }

    /**
     * Functional Test 2: Cancelación de una cita mediante el endpoint PATCH /citas/{cita}/cancelar.
     */
    public function test_patient_can_cancel_their_own_appointment(): void
    {
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $this->specialty->id,
            'start_datetime' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'status' => 'reservada',
        ]);

        $response = $this->actingAs($this->patient)
            ->from('/citas')
            ->patch("/citas/{$appointment->id}/cancelar");

        $response->assertRedirect('/citas');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelada',
        ]);
    }

    /**
     * Functional Test 3: Registro de un paciente en lista de espera a través de POST /waitlist.
     */
    public function test_patient_can_register_in_waitlist_via_post_route(): void
    {
        $response = $this->actingAs($this->patient)
            ->post('/waitlist', [
                'patient_id' => $this->patient->id,
                'specialty_id' => $this->specialty->id,
                'professional_profile_id' => $this->profileId,
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('waitlists', [
            'patient_id' => $this->patient->id,
            'specialty_id' => $this->specialty->id,
            'professional_profile_id' => $this->profileId,
            'status' => 'Pendiente',
        ]);
    }
}
