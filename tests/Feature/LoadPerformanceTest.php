<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\User;
use App\Models\Specialty;
use App\Models\ProfessionalProfile;
use App\Models\Availability;
use App\Models\Waitlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LoadPerformanceTest extends TestCase
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

        $this->patient1 = User::factory()->create(['email' => 'p1@load.com']);
        $this->patient1->assignRole('paciente');

        $this->patient2 = User::factory()->create(['email' => 'p2@load.com']);
        $this->patient2->assignRole('paciente');

        $this->doctor = User::factory()->create();
        $this->doctor->assignRole('medico');

        $this->profileId = DB::table('professional_profiles')->insertGetId([
            'user_id' => $this->doctor->id,
            'box_number' => 'Box 707',
            'consultation_duration_minutes' => 30,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->specialty = Specialty::create(['name' => 'Psiquiatría']);
        DB::table('professional_profile_specialty')->insert([
            'professional_profile_id' => $this->profileId,
            'specialty_id' => $this->specialty->id
        ]);
    }

    /**
     * Load Test 16: Control de Concurrencia (Race Condition).
     * Simula dos intentos de reservar exactamente el mismo bloque horario. Solo uno debe tener éxito.
     */
    public function test_concurrency_only_allows_one_booking_for_same_slot(): void
    {
        Mail::fake();

        $slotTime = now()->addDays(5)->roundMinutes(30)->format('Y-m-d H:i:s');

        // Primer Intento: Reserva el slot
        $response1 = $this->actingAs($this->patient1)
            ->post('/citas', [
                'patient_id' => $this->patient1->id,
                'professional_profile_id' => $this->profileId,
                'start_datetime' => $slotTime,
            ]);
        $response1->assertRedirect();

        // Segundo Intento (Concurrente/Inmediato): Intenta reservar el mismo slot
        $response2 = $this->actingAs($this->patient2)
            ->post('/citas', [
                'patient_id' => $this->patient2->id,
                'professional_profile_id' => $this->profileId,
                'start_datetime' => $slotTime,
            ]);

        // Debe fallar y redirigir con un error en la sesión
        $response2->assertRedirect();
        $response2->assertSessionHasErrors(['error']);

        // Assert que solo hay una cita registrada en la base de datos para ese slot
        $this->assertEquals(1, Appointment::where('professional_profile_id', $this->profileId)
            ->where('start_datetime', $slotTime)
            ->count());
    }

    /**
     * Load Test 17: Benchmark de Latencia y Consulta Masiva (Base de Datos).
     * Siembra 500 disponibilidades médicas y mide que la consulta de disponibilidad tarde menos de 200 ms.
     */
    public function test_availability_query_performance_under_load(): void
    {
        // Sembrar 500 disponibilidades (para diferentes médicos/días)
        $availabilities = [];
        for ($i = 0; $i < 500; $i++) {
            $availabilities[] = [
                'professional_profile_id' => $this->profileId,
                'day_of_week' => rand(1, 7),
                'start_time' => sprintf('%02d:00:00', rand(8, 11)),
                'end_time' => sprintf('%02d:00:00', rand(13, 17)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('availabilities')->insert($availabilities);

        // Medir tiempo de ejecución
        $startTime = microtime(true);

        $response = $this->getJson(route('wizard.availability', [
            'specialty_id' => $this->specialty->id,
            'date' => now()->addDays(1)->format('Y-m-d'),
        ]));

        $endTime = microtime(true);
        $elapsedMs = ($endTime - $startTime) * 1000;

        $response->assertStatus(200);
        
        // Assert: El endpoint responde en menos de 200 ms a pesar de la carga de registros
        $this->assertLessThan(200, $elapsedMs, "La consulta de disponibilidad tardó {$elapsedMs} ms, excediendo el límite de 200 ms.");
    }

    /**
     * Load Test 18: Rendimiento y Consumo de Memoria de la Lista de Espera.
     * Siembra 100 pacientes en lista de espera y valida el procesamiento eficiente de la cola.
     */
    public function test_waitlist_processing_performance_under_load(): void
    {
        Mail::fake();

        // Sembrar 100 pacientes en lista de espera
        $waitlistEntries = [];
        $users = User::factory()->count(100)->create();
        
        foreach ($users as $idx => $user) {
            $user->assignRole('paciente');
            $waitlistEntries[] = [
                'patient_id' => $user->id,
                'specialty_id' => $this->specialty->id,
                'status' => 'Pendiente',
                'created_at' => now()->subMinutes(100 - $idx),
                'updated_at' => now()->subMinutes(100 - $idx),
            ];
        }
        DB::table('waitlists')->insert($waitlistEntries);

        $freedTime = now()->addDays(2)->format('Y-m-d H:i:s');

        // Medir recursos
        $startMemory = memory_get_usage();
        $startTime = microtime(true);

        // Notificar al siguiente
        Waitlist::notifyNextInWaitlist($this->specialty->id, $this->profileId, $freedTime);

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $elapsedMs = ($endTime - $startTime) * 1000;
        $memoryDeltaKb = ($endMemory - $startMemory) / 1024;

        // Se debió notificar al primer paciente (orden por fecha de creación)
        $notified = Waitlist::where('status', 'Notificado')->first();
        $this->assertNotNull($notified);

        // Assert de límites: Procesamiento rápido y bajo consumo
        $this->assertLessThan(100, $elapsedMs, "El procesamiento tardó {$elapsedMs} ms.");
        $this->assertLessThan(5000, $memoryDeltaKb, "El consumo de memoria aumentó por {$memoryDeltaKb} KB.");
    }
}
