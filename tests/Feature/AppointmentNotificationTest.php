<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use App\Mail\AppointmentNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AppointmentNotificationTest extends TestCase
{
    use RefreshDatabase;

    private $patient;
    private $doctor;
    private $profileId;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Crear usuario paciente
        $this->patient = User::factory()->create([
            'email' => 'paciente@example.com',
        ]);

        // 2. Crear usuario médico
        $this->doctor = User::factory()->create([
            'email' => 'medico@example.com',
        ]);

        // 3. Crear perfil profesional para el médico
        $this->profileId = DB::table('professional_profiles')->insertGetId([
            'user_id' => $this->doctor->id,
            'box_number' => 'Box 101',
            'consultation_duration_minutes' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test para verificar que cancelar una cita actualiza su estado y envía notificación por correo.
     */
    public function test_cancelling_an_appointment_updates_status_and_sends_email(): void
    {
        Mail::fake();

        // Crear una especialidad para la prueba
        $specialtyId = DB::table('specialties')->insertGetId([
            'name' => 'Cardiología',
            'description' => 'Especialidad en el corazón',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear una cita médica
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $specialtyId,
            'start_datetime' => '2026-06-20 10:00:00',
            'status' => 'reservada',
        ]);

        // Realizar petición como paciente autenticado
        $response = $this->actingAs($this->patient)
            ->from('/dashboard') // Usar dashboard como origen para el redirect back
            ->post("/appointments/{$appointment->id}/cancel");

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success', 'Cita cancelada con éxito y notificación enviada.');

        // Verificar cambio en base de datos
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelada',
        ]);

        // Verificar envío de correo por Mailtrap/Mail
        Mail::assertSent(AppointmentNotification::class, function (AppointmentNotification $mail) {
            return $mail->hasTo('paciente@example.com') &&
                   $mail->actionType === 'Cancelada' &&
                   $mail->appointment->status === 'cancelada';
        });
    }

    /**
     * Test para verificar que modificar una cita actualiza su fecha/hora y envía notificación por correo.
     */
    public function test_updating_an_appointment_updates_details_and_sends_email(): void
    {
        Mail::fake();

        // Crear una especialidad para la prueba
        $specialtyId = DB::table('specialties')->insertGetId([
            'name' => 'Cardiología',
            'description' => 'Especialidad en el corazón',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Crear una cita médica
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $specialtyId,
            'start_datetime' => '2026-06-20 10:00:00',
            'status' => 'reservada',
        ]);

        // Nuevos datos para la cita
        $newDetails = [
            'start_datetime' => '2026-06-25 11:00:00',
        ];

        // Realizar petición como paciente autenticado
        $response = $this->actingAs($this->patient)
            ->from('/dashboard')
            ->patch("/appointments/{$appointment->id}", $newDetails);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success', 'Cita modificada con éxito y notificación enviada.');

        // Verificar cambios en base de datos
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'start_datetime' => '2026-06-25 11:00:00',
            'status' => 'modificada',
        ]);

        // Verificar envío de correo por Mailtrap/Mail
        Mail::assertSent(AppointmentNotification::class, function (AppointmentNotification $mail) {
            return $mail->hasTo('paciente@example.com') &&
                   $mail->actionType === 'Modificada' &&
                   $mail->appointment->status === 'modificada' &&
                   $mail->appointment->start_datetime === '2026-06-25 11:00:00';
        });
    }
}
