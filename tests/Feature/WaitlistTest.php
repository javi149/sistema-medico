<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Waitlist;
use App\Models\Specialty;
use App\Mail\WaitlistSlotFreed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WaitlistTest extends TestCase
{
    use RefreshDatabase;

    private $patient1;
    private $patient2;
    private $doctor;
    private $profileId;
    private $specialty;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        // Create patients
        $this->patient1 = User::factory()->create(['email' => 'patient1@example.com']);
        $this->patient1->assignRole('paciente');

        $this->patient2 = User::factory()->create(['email' => 'patient2@example.com']);
        $this->patient2->assignRole('paciente');

        // Create doctor and profile
        $this->doctor = User::factory()->create(['email' => 'doctor@example.com']);
        $this->doctor->assignRole('medico');

        $this->profileId = DB::table('professional_profiles')->insertGetId([
            'user_id' => $this->doctor->id,
            'box_number' => 'Box 102',
            'consultation_duration_minutes' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create specialty and link to doctor
        $this->specialty = Specialty::create(['name' => 'Cardiología']);
        DB::table('professional_profile_specialty')->insert([
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $this->specialty->id
        ]);
    }

    /**
     * Test patient can join the waitlist.
     */
    public function test_patient_can_join_waitlist(): void
    {
        $response = $this->actingAs($this->patient1)
            ->post('/waitlist', [
                'patient_id' => $this->patient1->id,
                'specialty_id' => $this->specialty->id,
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success', 'Te has inscrito exitosamente en la lista de espera. Te enviaremos un correo apenas se libere un cupo.');

        $this->assertDatabaseHas('waitlists', [
            'patient_id' => $this->patient1->id,
            'specialty_id' => $this->specialty->id,
            'status' => 'Pendiente',
        ]);
    }

    /**
     * Test duplicate waitlist entries are prevented.
     */
    public function test_duplicate_waitlist_entry_is_prevented(): void
    {
        // Join once
        Waitlist::create([
            'patient_id' => $this->patient1->id,
            'specialty_id' => $this->specialty->id,
            'status' => 'Pendiente',
        ]);

        // Try to join again
        $response = $this->actingAs($this->patient1)
            ->from('/citas/create')
            ->post('/waitlist', [
                'patient_id' => $this->patient1->id,
                'specialty_id' => $this->specialty->id,
            ]);

        $response->assertRedirect('/citas/create');
        $response->assertSessionHasErrors(['error']);
    }

    /**
     * Test cancelling an appointment notifies the first waitlisted patient.
     */
    public function test_cancelling_appointment_notifies_waitlisted_patient(): void
    {
        Mail::fake();

        // Put patient 2 in the waitlist (first in queue)
        $waitlist = Waitlist::create([
            'patient_id' => $this->patient2->id,
            'specialty_id' => $this->specialty->id,
            'status' => 'Pendiente',
        ]);

        // Create an appointment for patient 1
        $appointment = Appointment::create([
            'patient_id' => $this->patient1->id,
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $this->specialty->id,
            'start_datetime' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'status' => 'reservada',
        ]);

        // Patient 1 cancels their appointment
        $response = $this->actingAs($this->patient1)
            ->patch("/citas/{$appointment->id}/cancelar");

        $response->assertRedirect();

        // Verify appointment is cancelled
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelada',
        ]);

        // Verify waitlist status changed to Notificado
        $this->assertDatabaseHas('waitlists', [
            'id' => $waitlist->id,
            'status' => 'Notificado',
        ]);

        // Verify email was sent to patient 2
        Mail::assertSent(WaitlistSlotFreed::class, function (WaitlistSlotFreed $mail) {
            return $mail->hasTo($this->patient2->email) &&
                   $mail->waitlist->patient_id === $this->patient2->id &&
                   $mail->specialtyName === 'Cardiología';
        });
    }

    /**
     * Test rescheduling an appointment notifies waitlisted patient.
     */
    public function test_rescheduling_appointment_notifies_waitlisted_patient(): void
    {
        Mail::fake();

        // Put patient 2 in the waitlist
        $waitlist = Waitlist::create([
            'patient_id' => $this->patient2->id,
            'specialty_id' => $this->specialty->id,
            'status' => 'Pendiente',
        ]);

        // Create appointment for patient 1
        $oldTime = now()->addDays(2)->format('Y-m-d H:i:s');
        $appointment = Appointment::create([
            'patient_id' => $this->patient1->id,
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $this->specialty->id,
            'start_datetime' => $oldTime,
            'status' => 'reservada',
        ]);

        // Patient 1 reschedules to a new time
        $newTime = now()->addDays(5)->format('Y-m-d H:i:s');
        $response = $this->actingAs($this->patient1)
            ->patch("/citas/{$appointment->id}", [
                'start_datetime' => $newTime
            ]);

        $response->assertRedirect('/citas');

        // Verify waitlist status changed to Notificado
        $this->assertDatabaseHas('waitlists', [
            'id' => $waitlist->id,
            'status' => 'Notificado',
        ]);

        // Verify email was sent with the old (now free) time slot
        Mail::assertSent(WaitlistSlotFreed::class, function (WaitlistSlotFreed $mail) use ($oldTime) {
            return $mail->hasTo($this->patient2->email) &&
                   $mail->slotTime === $oldTime;
        });
    }

    /**
     * Test admin deleting an appointment notifies waitlisted patient.
     */
    public function test_admin_deleting_appointment_notifies_waitlisted_patient(): void
    {
        Mail::fake();

        // Get or create admin
        $admin = User::where('email', 'admin@clinica.cl')->first();
        if (!$admin) {
            $admin = User::factory()->create();
            $admin->assignRole('admin');
        }

        // Put patient 2 in waitlist
        $waitlist = Waitlist::create([
            'patient_id' => $this->patient2->id,
            'specialty_id' => $this->specialty->id,
            'status' => 'Pendiente',
        ]);

        // Create appointment for patient 1
        $appointmentTime = now()->addDays(2)->format('Y-m-d H:i:s');
        $appointment = Appointment::create([
            'patient_id' => $this->patient1->id,
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $this->specialty->id,
            'start_datetime' => $appointmentTime,
            'status' => 'reservada',
        ]);

        // Admin deletes the appointment
        $response = $this->actingAs($admin)
            ->delete("/admin/appointments/{$appointment->id}");

        $response->assertRedirect();

        // Verify appointment was deleted
        $this->assertDatabaseMissing('appointments', [
            'id' => $appointment->id
        ]);

        // Verify waitlist status is Notificado
        $this->assertDatabaseHas('waitlists', [
            'id' => $waitlist->id,
            'status' => 'Notificado',
        ]);

        // Verify email was sent
        Mail::assertSent(WaitlistSlotFreed::class, function (WaitlistSlotFreed $mail) use ($appointmentTime) {
            return $mail->hasTo($this->patient2->email) &&
                   $mail->slotTime === $appointmentTime;
        });
    }

    /**
     * Test patient can view their waitlist offer.
     */
    public function test_patient_can_view_waitlist_offer(): void
    {
        Waitlist::create([
            'patient_id' => $this->patient1->id,
            'specialty_id' => $this->specialty->id,
            'professional_profile_id' => $this->profileId,
            'offered_datetime' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'status' => 'Notificado',
        ]);

        $response = $this->actingAs($this->patient1)->get('/citas');
        $response->assertStatus(200);
        $response->assertSee('¡Cupo Disponible en Lista de Espera!');
        $response->assertSee($this->doctor->name);
    }

    /**
     * Test patient can accept a waitlist offer.
     */
    public function test_patient_can_accept_waitlist_offer(): void
    {
        Mail::fake();

        $offeredTime = now()->addDays(2)->format('Y-m-d H:i:s');
        $waitlist = Waitlist::create([
            'patient_id' => $this->patient1->id,
            'specialty_id' => $this->specialty->id,
            'professional_profile_id' => $this->profileId,
            'offered_datetime' => $offeredTime,
            'status' => 'Notificado',
        ]);

        $response = $this->actingAs($this->patient1)
            ->post("/waitlist/{$waitlist->id}/accept");

        $response->assertRedirect('/citas');
        $response->assertSessionHas('success', '¡Cupo aceptado con éxito! Tu cita médica ha sido agendada.');

        // Verify appointment was created
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $this->patient1->id,
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $this->specialty->id,
            'start_datetime' => $offeredTime,
            'status' => 'reservada',
        ]);

        // Verify waitlist is Reasignado
        $this->assertDatabaseHas('waitlists', [
            'id' => $waitlist->id,
            'status' => 'Reasignado',
        ]);
    }

    /**
     * Test patient can decline waitlist offer, which notifies the next patient in line.
     */
    public function test_patient_can_decline_waitlist_offer_and_notifies_next_in_line(): void
    {
        Mail::fake();

        $offeredTime = now()->addDays(2)->format('Y-m-d H:i:s');
        
        // Patient 1 has an offer
        $waitlist1 = Waitlist::create([
            'patient_id' => $this->patient1->id,
            'specialty_id' => $this->specialty->id,
            'professional_profile_id' => $this->profileId,
            'offered_datetime' => $offeredTime,
            'status' => 'Notificado',
        ]);

        // Patient 2 is next in line (pending)
        $waitlist2 = Waitlist::create([
            'patient_id' => $this->patient2->id,
            'specialty_id' => $this->specialty->id,
            'status' => 'Pendiente',
        ]);

        // Patient 1 declines the offer
        $response = $this->actingAs($this->patient1)
            ->post("/waitlist/{$waitlist1->id}/decline");

        $response->assertRedirect('/citas');
        $response->assertSessionHas('success');

        // Verify waitlist 1 is Cancelado
        $this->assertDatabaseHas('waitlists', [
            'id' => $waitlist1->id,
            'status' => 'Cancelado',
        ]);

        // Verify waitlist 2 was notified automatically because the slot was declined
        $this->assertDatabaseHas('waitlists', [
            'id' => $waitlist2->id,
            'status' => 'Notificado',
            'offered_datetime' => $offeredTime,
        ]);

        // Verify email was sent to patient 2
        Mail::assertSent(WaitlistSlotFreed::class, function (WaitlistSlotFreed $mail) {
            return $mail->hasTo($this->patient2->email);
        });
    }
}
