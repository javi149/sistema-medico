<?php

namespace Tests\Unit;

use App\Models\Waitlist;
use App\Models\Specialty;
use App\Models\ProfessionalProfile;
use App\Models\User;
use App\Mail\WaitlistSlotFreed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class WaitlistUnitTest extends TestCase
{
    use RefreshDatabase;

    private $patient;
    private $doctor;
    private $profileId;
    private $specialty;

    protected function setUp(): void
    {
        parent::setUp();

        // Create standard test entities
        $this->patient = User::factory()->create(['email' => 'waitlist_patient@example.com']);
        $this->patient->assignRole('paciente');

        $this->doctor = User::factory()->create();
        $this->doctor->assignRole('medico');

        $this->profileId = DB::table('professional_profiles')->insertGetId([
            'user_id' => $this->doctor->id,
            'box_number' => 'Box 204',
            'consultation_duration_minutes' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->specialty = Specialty::create(['name' => 'Pediatría']);
        DB::table('professional_profile_specialty')->insert([
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $this->specialty->id
        ]);
    }

    /**
     * Test 4: notifyNextInWaitlist() promueve al primer paciente en cola a 'Notificado',
     * asignando la fecha de oferta y enviando correo de aviso.
     */
    public function test_notify_next_in_waitlist_promotes_patient_and_sends_email(): void
    {
        Mail::fake();

        // Crear registro en lista de espera
        $waitlist = Waitlist::create([
            'patient_id' => $this->patient->id,
            'specialty_id' => $this->specialty->id,
            'status' => 'Pendiente',
        ]);

        // Simular que se libera un cupo
        $freedDatetime = now()->addDays(3)->format('Y-m-d H:i:s');
        Waitlist::notifyNextInWaitlist($this->specialty->id, $this->profileId, $freedDatetime);

        // Verificar cambio en base de datos
        $this->assertDatabaseHas('waitlists', [
            'id' => $waitlist->id,
            'status' => 'Notificado',
            'offered_datetime' => $freedDatetime,
            'professional_profile_id' => $this->profileId,
        ]);

        // Verificar envío de correo
        Mail::assertSent(WaitlistSlotFreed::class, function (WaitlistSlotFreed $mail) {
            return $mail->hasTo('waitlist_patient@example.com');
        });
    }

    /**
     * Test 5: Si no hay nadie en lista de espera, notifyNextInWaitlist() no hace nada
     * y no arroja excepciones ni envía correos.
     */
    public function test_notify_next_in_waitlist_does_nothing_if_queue_is_empty(): void
    {
        Mail::fake();

        $freedDatetime = now()->addDays(3)->format('Y-m-d H:i:s');
        
        // Ejecutar notificación sin registros previos en lista de espera
        Waitlist::notifyNextInWaitlist($this->specialty->id, $this->profileId, $freedDatetime);

        // Validar que no se envió ningún correo
        Mail::assertNothingSent();
    }

    /**
     * Test 6: Verifica las relaciones Eloquent del modelo Waitlist.
     */
    public function test_waitlist_model_relations_work_properly(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $waitlist = Waitlist::create([
            'patient_id' => $this->patient->id,
            'specialty_id' => $this->specialty->id,
            'professional_profile_id' => $this->profileId,
            'status' => 'Reasignado',
            'managed_by' => $admin->id,
        ]);

        $this->assertInstanceOf(User::class, $waitlist->patient);
        $this->assertEquals($this->patient->id, $waitlist->patient->id);

        $this->assertInstanceOf(Specialty::class, $waitlist->specialty);
        $this->assertEquals($this->specialty->id, $waitlist->specialty->id);

        $this->assertInstanceOf(ProfessionalProfile::class, $waitlist->professionalProfile);
        $this->assertEquals($this->profileId, $waitlist->professionalProfile->id);

        $this->assertInstanceOf(User::class, $waitlist->managedBy);
        $this->assertEquals($admin->id, $waitlist->managedBy->id);
    }
}
