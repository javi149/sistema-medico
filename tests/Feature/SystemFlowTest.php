<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Specialty;
use App\Models\ProfessionalProfile;
use App\Models\Waitlist;
use App\Models\Availability;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SystemFlowTest extends TestCase
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

        // Crear pacientes
        $this->patientA = User::factory()->create(['name' => 'Paciente A', 'email' => 'pacientea@example.com', 'rut' => '33333333-3']);
        $this->patientA->assignRole('paciente');

        $this->patientB = User::factory()->create(['name' => 'Paciente B', 'email' => 'pacienteb@example.com', 'rut' => '22222222-2']);
        $this->patientB->assignRole('paciente');

        // Crear médico y especialidad
        $this->doctor = User::factory()->create(['name' => 'Dr. House']);
        $this->doctor->assignRole('medico');

        $this->profileId = DB::table('professional_profiles')->insertGetId([
            'user_id' => $this->doctor->id,
            'box_number' => 'Box 505',
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
     * System Test 10: Flujo de extremo a extremo:
     * La cancelación de una cita libera el cupo y automáticamente promueve y notifica al paciente en lista de espera.
     */
    public function test_system_appointment_cancellation_triggers_waitlist_promotion(): void
    {
        Mail::fake();

        $slotTime = now()->addDays(3)->format('Y-m-d H:i:s');

        // 1. Paciente A tiene una cita agendada
        $appointment = Appointment::create([
            'patient_id' => $this->patientA->id,
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $this->specialty->id,
            'start_datetime' => $slotTime,
            'status' => 'reservada',
        ]);

        // 2. Paciente B se inscribe en la lista de espera para esa especialidad
        $waitlist = Waitlist::create([
            'patient_id' => $this->patientB->id,
            'specialty_id' => $this->specialty->id,
            'status' => 'Pendiente',
        ]);

        // 3. Paciente A cancela su cita médica mediante el controlador
        $response = $this->actingAs($this->patientA)
            ->patch("/citas/{$appointment->id}/cancelar");

        $response->assertRedirect();

        // 4. Verificar que la cita está cancelada
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelada',
        ]);

        // 5. Verificar que el Paciente B fue promovido automáticamente a 'Notificado'
        $this->assertDatabaseHas('waitlists', [
            'id' => $waitlist->id,
            'status' => 'Notificado',
            'offered_datetime' => $slotTime,
            'professional_profile_id' => $this->profileId,
        ]);

        // 6. Verificar el envío de correo de aviso a Paciente B
        Mail::assertSent(\App\Mail\WaitlistSlotFreed::class, function ($mail) {
            return $mail->hasTo('pacienteb@example.com');
        });
    }

    /**
     * System Test 11: Flujo del Wizard de Reserva Completo.
     * Consulta RUT -> Consulta disponibilidad -> Confirma reserva -> Redirección a éxito.
     */
    public function test_system_booking_wizard_flow(): void
    {
        Mail::fake();

        // Crear disponibilidad para el doctor
        $availabilityDate = now()->addDays(2);
        $dayOfWeek = $availabilityDate->dayOfWeekIso; // 1 = Lunes, 7 = Domingo

        Availability::create([
            'professional_profile_id' => $this->profileId,
            'day_of_week' => $dayOfWeek,
            'start_time' => '10:00:00',
            'end_time' => '11:00:00',
        ]);

        // 1. Validar el RUT del paciente
        $responseRut = $this->postJson('/citas/wizard/check-rut', [
            'rut' => '33333333-3',
        ]);

        $responseRut->assertStatus(200);
        $responseRut->assertJson([
            'success' => true,
            'name' => $this->patientA->name,
            'id' => $this->patientA->id,
        ]);

        // 2. Obtener disponibilidad del médico
        $responseAvailability = $this->getJson(route('wizard.availability', [
            'specialty_id' => $this->specialty->id,
            'date' => $availabilityDate->format('Y-m-d'),
        ]));

        $responseAvailability->assertStatus(200);

        // 3. Confirmar la reserva del cupo a las 10:00
        $appointmentTime = $availabilityDate->format('Y-m-d') . ' 10:00:00';
        $responseBook = $this->actingAs($this->patientA)
            ->post('/citas', [
                'patient_id' => $this->patientA->id,
                'professional_profile_id' => $this->profileId,
                'start_datetime' => $appointmentTime,
            ]);

        // Redirigir a vista de éxito
        $responseBook->assertRedirect();

        // Cita guardada en base de datos
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $this->patientA->id,
            'professional_profile_id' => $this->profileId,
            'start_datetime' => $appointmentTime,
            'status' => 'reservada',
        ]);
    }

    /**
     * System Test 12: Flujo del Administrador: Acceso a panel de control y aprobación manual de lista de espera.
     */
    public function test_system_admin_dashboard_and_waitlist_approval(): void
    {
        Mail::fake();

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        // Sembrar cita para hoy
        $appointment = Appointment::create([
            'patient_id' => $this->patientA->id,
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $this->specialty->id,
            'start_datetime' => now()->format('Y-m-d 10:00:00'),
            'status' => 'reservada',
        ]);

        // Sembrar paciente en lista de espera
        $waitlist = Waitlist::create([
            'patient_id' => $this->patientB->id,
            'specialty_id' => $this->specialty->id,
            'status' => 'Pendiente',
        ]);

        // 1. Acceder al Dashboard diario del Admin
        $responseDashboard = $this->actingAs($admin)
            ->get('/admin/dashboard?date=' . now()->format('Y-m-d'));

        $responseDashboard->assertStatus(200);
        $responseDashboard->assertViewHas('totalCitas', 1);
        $responseDashboard->assertViewHas('confirmadas', 1);

        // 2. Administrador aprueba cupo de lista de espera de forma manual
        $futureTime = now()->addDays(2)->format('Y-m-d H:i:s');
        $responseApprove = $this->actingAs($admin)
            ->post("/admin/waitlist/{$waitlist->id}/approve", [
                'start_datetime' => $futureTime,
                'professional_profile_id' => $this->profileId,
            ]);

        $responseApprove->assertRedirect(route('admin.waitlist.index'));

        // Registro actualizado
        $this->assertDatabaseHas('waitlists', [
            'id' => $waitlist->id,
            'status' => 'Notificado',
            'offered_datetime' => $futureTime,
            'professional_profile_id' => $this->profileId,
            'managed_by' => $admin->id,
        ]);

        // Notificación de correo enviada
        Mail::assertSent(\App\Mail\WaitlistSlotFreed::class);
    }
}
