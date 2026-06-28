<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Waitlist;
use App\Models\Specialty;
use App\Mail\WaitlistSlotFreed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminWaitlistTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $patient;
    private $doctor;
    private $profileId;
    private $specialty;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);

        // Create admin
        $this->admin = User::factory()->create(['email' => 'admin@example.com']);
        $this->admin->assignRole('admin');

        // Create patient
        $this->patient = User::factory()->create(['email' => 'patient@example.com']);
        $this->patient->assignRole('paciente');

        // Create doctor and profile
        $this->doctor = User::factory()->create(['email' => 'doctor@example.com']);
        $this->doctor->assignRole('medico');

        $this->profileId = DB::table('professional_profiles')->insertGetId([
            'user_id' => $this->doctor->id,
            'box_number' => 'Box 103',
            'consultation_duration_minutes' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create specialty and link to doctor
        $this->specialty = Specialty::create(['name' => 'Dermatología']);
        DB::table('professional_profile_specialty')->insert([
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $this->specialty->id
        ]);
    }

    /**
     * Test non-admin cannot access admin waitlist routes.
     */
    public function test_non_admin_cannot_access_waitlist_routes(): void
    {
        $waitlist = Waitlist::create([
            'patient_id' => $this->patient->id,
            'specialty_id' => $this->specialty->id,
            'status' => 'Pendiente',
        ]);

        // Try as patient
        $responseGet = $this->actingAs($this->patient)->get('/admin/waitlist');
        $responseGet->assertStatus(403);

        $responsePost = $this->actingAs($this->patient)->post("/admin/waitlist/{$waitlist->id}/approve", [
            'start_datetime' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'professional_profile_id' => $this->profileId,
        ]);
        $responsePost->assertStatus(403);
    }

    /**
     * Test admin can view pending waitlist entries.
     */
    public function test_admin_can_view_waitlist(): void
    {
        Waitlist::create([
            'patient_id' => $this->patient->id,
            'specialty_id' => $this->specialty->id,
            'status' => 'Pendiente',
        ]);

        $response = $this->actingAs($this->admin)->get('/admin/waitlist');
        $response->assertStatus(200);
        $response->assertSee($this->patient->name);
        $response->assertSee('Dermatología');
    }

    /**
     * Test admin can approve availability and notify patient.
     */
    public function test_admin_can_approve_availability_and_notify(): void
    {
        Mail::fake();

        $waitlist = Waitlist::create([
            'patient_id' => $this->patient->id,
            'specialty_id' => $this->specialty->id,
            'status' => 'Pendiente',
        ]);

        $freedTime = now()->addDays(2)->format('Y-m-d H:i:s');

        $response = $this->actingAs($this->admin)
            ->post("/admin/waitlist/{$waitlist->id}/approve", [
                'start_datetime' => $freedTime,
                'professional_profile_id' => $this->profileId,
            ]);

        $response->assertRedirect('/admin/waitlist');
        $response->assertSessionHas('success', 'Disponibilidad aprobada y paciente notificado con éxito.');

        // Verify status and managed_by in database
        $this->assertDatabaseHas('waitlists', [
            'id' => $waitlist->id,
            'status' => 'Notificado',
            'managed_by' => $this->admin->id,
        ]);

        // Verify email was sent
        Mail::assertSent(WaitlistSlotFreed::class, function (WaitlistSlotFreed $mail) use ($freedTime) {
            return $mail->hasTo($this->patient->email) &&
                   $mail->doctorName === $this->doctor->name &&
                   $mail->specialtyName === 'Dermatología' &&
                   $mail->slotTime === $freedTime;
        });
    }
}
