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

        // Sembrar los roles definidos en la aplicación
        $this->seed(\Database\Seeders\RoleSeeder::class);

        // 1. Crear usuario paciente
        $this->patient = User::factory()->create([
            'email' => 'paciente@example.com',
        ]);
        $this->patient->assignRole('paciente');

        // 2. Crear usuario médico
        $this->doctor = User::factory()->create([
            'email' => 'medico@example.com',
        ]);
        $this->doctor->assignRole('medico');

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

        // Crear una especialidad para asociarla a la cita
        $specialty = \App\Models\Specialty::create(['name' => 'Pediatría']);

        // Crear una cita médica
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $specialty->id,
            'start_datetime' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'status' => 'Agendada',
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
                   $mail->appointment->status === 'Cancelada';
        });
    }

    /**
     * Test para verificar que modificar una cita actualiza su fecha/hora y envía notificación por correo.
     */
    public function test_updating_an_appointment_updates_details_and_sends_email(): void
    {
        Mail::fake();

        // Crear una especialidad para asociarla a la cita
        $specialty = \App\Models\Specialty::create(['name' => 'Pediatría']);

        // Crear una cita médica
        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $specialty->id,
            'start_datetime' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'status' => 'Agendada',
        ]);

        // Nuevos datos para la cita
        $newDetails = [
            'start_datetime' => now()->addDays(5)->format('Y-m-d H:i:s'),
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
            'start_datetime' => $newDetails['start_datetime'],
            'status' => 'Modificada',
        ]);

        // Verificar envío de correo por Mailtrap/Mail
        Mail::assertSent(AppointmentNotification::class, function (AppointmentNotification $mail) use ($newDetails) {
            return $mail->hasTo('paciente@example.com') &&
                   $mail->actionType === 'Modificada' &&
                   $mail->appointment->status === 'Modificada' &&
                   $mail->appointment->start_datetime === $newDetails['start_datetime'];
        });
    }

    /**
     * Test que verifica que el paciente puede ver el formulario de reprogramación y reprogramar.
     */
    public function test_patient_can_view_reschedule_page_and_update_appointment(): void
    {
        Mail::fake();
        $specialty = \App\Models\Specialty::create(['name' => 'Kinesiología']);

        $appointment = Appointment::create([
            'patient_id' => $this->patient->id,
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $specialty->id,
            'start_datetime' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'status' => 'reservada',
        ]);

        // 1. Verificar acceso a la vista edit
        $responseEdit = $this->actingAs($this->patient)
            ->get("/citas/{$appointment->id}/edit");
        $responseEdit->assertStatus(200);

        // 2. Reprogramar la cita
        $newTime = now()->addDays(5)->format('Y-m-d H:i:s');
        $responseUpdate = $this->actingAs($this->patient)
            ->patch("/citas/{$appointment->id}", [
                'start_datetime' => $newTime
            ]);

        $responseUpdate->assertRedirect('/citas');
        $responseUpdate->assertSessionHas('success');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'start_datetime' => $newTime,
            'status' => 'Modificada',
        ]);

        Mail::assertSent(AppointmentNotification::class);
    }

    /**
     * Test que verifica que el administrador puede agendar, reprogramar y eliminar citas.
     */
    public function test_admin_can_schedule_reschedule_and_delete_appointment(): void
    {
        Mail::fake();
        
        $admin = User::where('email', 'admin@clinica.cl')->first(); // Creado por el RoleSeeder
        if (!$admin) {
            $admin = User::factory()->create();
            $admin->assignRole('admin');
        }

        $specialty = \App\Models\Specialty::create(['name' => 'Cardiología']);
        // Vincular especialidad al médico
        DB::table('professional_profile_specialty')->insert([
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $specialty->id
        ]);

        // 1. Agendar cita por Admin
        $date1 = now()->addDays(2)->format('Y-m-d') . ' 09:00:00';
        $responseStore = $this->actingAs($admin)
            ->post('/admin/appointments', [
                'patient_id' => $this->patient->id,
                'professional_profile_id' => $this->profileId,
                'start_datetime' => $date1
            ]);

        $responseStore->assertRedirect('/admin/dashboard?date=' . now()->addDays(2)->format('Y-m-d'));
        
        $appointment = Appointment::where('patient_id', $this->patient->id)
            ->where('start_datetime', $date1)
            ->first();
        
        $this->assertNotNull($appointment);

        // 2. Reprogramar cita por Admin
        $date2 = now()->addDays(2)->format('Y-m-d') . ' 11:30:00';
        $responseUpdate = $this->actingAs($admin)
            ->patch("/admin/appointments/{$appointment->id}", [
                'start_datetime' => $date2
            ]);

        $responseUpdate->assertRedirect('/admin/dashboard?date=' . now()->addDays(2)->format('Y-m-d'));
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'start_datetime' => $date2,
            'status' => 'Modificada',
        ]);

        // 3. Eliminar cita por Admin
        $responseDelete = $this->actingAs($admin)
            ->delete("/admin/appointments/{$appointment->id}");

        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('appointments', [
            'id' => $appointment->id
        ]);
    }
}
