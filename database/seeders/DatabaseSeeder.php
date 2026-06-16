<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\ProfessionalProfile;
use App\Models\Specialty;
use App\Models\User;
use App\Models\Waitlist;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================
        // 0. ROLES BASE
        // =====================================================
        $this->call(RoleSeeder::class);

        $roleMedico = Role::where('name', 'medico')->first();
        $rolePaciente = Role::where('name', 'paciente')->first();
        $admin = User::where('email', 'admin@clinica.cl')->first();

        // =====================================================
        // 1. TABLA MANTENEDORA: ESPECIALIDADES (20 registros)
        // =====================================================
        $this->command->info('Creando 20 especialidades médicas...');
        $specialties = Specialty::factory(20)->create();

        // =====================================================
        // 2. TABLA MANTENEDORA: PACIENTES (60 registros)
        // =====================================================
        $this->command->info('Creando 60 pacientes...');
        $pacientes = User::factory(60)->create();
        // El UserFactory ya asigna rol 'paciente' automáticamente en afterCreating

        // =====================================================
        // 3. TABLA MANTENEDORA: MÉDICOS + PERFILES (10 registros)
        // =====================================================
        $this->command->info('Creando 10 médicos con perfiles profesionales...');

        $medicosData = [
            ['name' => 'Dr. Carlos Méndez',    'rut' => '12345678-9', 'email' => 'carlos.mendez@clinica.cl'],
            ['name' => 'Dra. Ana Torres',       'rut' => '98765432-1', 'email' => 'ana.torres@clinica.cl'],
            ['name' => 'Dr. Luis Rojas',        'rut' => '11223344-5', 'email' => 'luis.rojas@clinica.cl'],
            ['name' => 'Dra. María González',   'rut' => '15678234-0', 'email' => 'maria.gonzalez@clinica.cl'],
            ['name' => 'Dr. Roberto Soto',      'rut' => '16789345-1', 'email' => 'roberto.soto@clinica.cl'],
            ['name' => 'Dra. Camila Vargas',    'rut' => '17890456-2', 'email' => 'camila.vargas@clinica.cl'],
            ['name' => 'Dr. Felipe Muñoz',      'rut' => '18901567-3', 'email' => 'felipe.munoz@clinica.cl'],
            ['name' => 'Dra. Valentina Díaz',   'rut' => '19012678-4', 'email' => 'valentina.diaz@clinica.cl'],
            ['name' => 'Dr. Andrés Cifuentes',  'rut' => '20123789-5', 'email' => 'andres.cifuentes@clinica.cl'],
            ['name' => 'Dra. Francisca Reyes',  'rut' => '21234890-6', 'email' => 'francisca.reyes@clinica.cl'],
        ];

        $biografias = [
            'Médico con más de 15 años de experiencia clínica en atención hospitalaria y ambulatoria.',
            'Especialista formada en la Universidad de Chile con subespecialidad en el área pediátrica.',
            'Profesional con vasta trayectoria en medicina de urgencia y cuidados intensivos.',
            'Cirujana con experiencia internacional y múltiples publicaciones en revistas indexadas.',
            'Especialista con enfoque en medicina preventiva y educación en salud comunitaria.',
            'Investigadora activa con más de 20 publicaciones en el área de enfermedades crónicas.',
            'Médico deportivo con experiencia en selecciones nacionales y clubes profesionales.',
            'Especialista en salud mental con formación en terapia cognitivo-conductual.',
            'Cirujano con subespecialidad en técnicas mínimamente invasivas y laparoscópicas.',
            'Médico integral con amplia experiencia en zonas rurales y atención primaria.',
        ];

        $perfiles = [];

        foreach ($medicosData as $i => $data) {
            $medico = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'rut' => $data['rut'],
                    'password' => Hash::make('password123'),
                    'email_verified_at' => now(),
                ]
            );

            if (! $medico->hasRole('medico')) {
                $medico->assignRole($roleMedico);
            }

            if (! $medico->professionalProfile) {
                $profile = ProfessionalProfile::create([
                    'user_id' => $medico->id,
                    'bio' => $biografias[$i],
                    'consultation_duration_minutes' => fake()->randomElement([15, 20, 30, 30, 30, 45]),
                ]);

                // Cada médico tiene 1 a 3 especialidades
                $profile->specialties()->attach(
                    $specialties->random(rand(1, 3))->pluck('id')
                );

                $perfiles[] = $profile;
            } else {
                $perfiles[] = $medico->professionalProfile;
            }
        }

        // =====================================================
        // 4. TABLA MANTENEDORA: DISPONIBILIDADES (50 registros)
        //    Horarios de atención para cada médico
        // =====================================================
        $this->command->info('Creando disponibilidades horarias para médicos...');

        foreach ($perfiles as $perfil) {
            // Cada médico atiende entre 3 y 5 días a la semana
            $diasAtencion = fake()->randomElements([1, 2, 3, 4, 5], rand(3, 5));

            foreach ($diasAtencion as $dia) {
                // Turno de mañana
                Availability::create([
                    'professional_profile_id' => $perfil->id,
                    'day_of_week' => $dia,
                    'start_time' => '08:00:00',
                    'end_time' => '13:00:00',
                ]);

                // Algunos médicos también tienen turno de tarde (60% de probabilidad)
                if (fake()->boolean(60)) {
                    Availability::create([
                        'professional_profile_id' => $perfil->id,
                        'day_of_week' => $dia,
                        'start_time' => '14:00:00',
                        'end_time' => '18:00:00',
                    ]);
                }
            }
        }

        $totalDisponibilidades = Availability::count();
        $this->command->info("  → {$totalDisponibilidades} bloques de disponibilidad creados.");

        // =====================================================
        // 5. TABLA TRANSACCIONAL: CITAS MÉDICAS (100 registros)
        //    Mix de citas pasadas, de hoy y futuras
        // =====================================================
        $this->command->info('Creando 100 citas médicas...');

        $pacienteIds = $pacientes->pluck('id')->toArray();
        $perfilIds = collect($perfiles)->pluck('id')->toArray();

        // --- 60 citas pasadas (ya atendidas, canceladas o ausentes) ---
        for ($i = 0; $i < 60; $i++) {
            $perfilId = fake()->randomElement($perfilIds);
            $perfil = ProfessionalProfile::with('specialties')->find($perfilId);
            $especialidadId = $perfil->specialties->isNotEmpty()
                ? $perfil->specialties->random()->id
                : $specialties->random()->id;

            // Distribuir estados: 60% atendidas, 25% canceladas, 15% ausentes
            $rand = fake()->numberBetween(1, 100);
            if ($rand <= 60) {
                $status = 'atendida';
            } elseif ($rand <= 85) {
                $status = 'cancelada';
            } else {
                $status = 'ausente';
            }

            Appointment::create([
                'patient_id' => fake()->randomElement($pacienteIds),
                'professional_profile_id' => $perfilId,
                'specialty_id' => $especialidadId,
                'start_datetime' => fake()->dateTimeBetween('-3 months', '-1 day'),
                'status' => $status,
            ]);
        }

        // --- 15 citas para hoy (para que el dashboard tenga datos) ---
        for ($i = 0; $i < 15; $i++) {
            $perfilId = fake()->randomElement($perfilIds);
            $perfil = ProfessionalProfile::with('specialties')->find($perfilId);
            $especialidadId = $perfil->specialties->isNotEmpty()
                ? $perfil->specialties->random()->id
                : $specialties->random()->id;

            $hora = fake()->numberBetween(8, 17);
            $minuto = fake()->randomElement([0, 15, 30, 45]);

            Appointment::create([
                'patient_id' => fake()->randomElement($pacienteIds),
                'professional_profile_id' => $perfilId,
                'specialty_id' => $especialidadId,
                'start_datetime' => Carbon::today()->setTime($hora, $minuto),
                'status' => fake()->randomElement(['reservada', 'confirmada']),
            ]);
        }

        // --- 25 citas futuras (reservadas o confirmadas) ---
        for ($i = 0; $i < 25; $i++) {
            $perfilId = fake()->randomElement($perfilIds);
            $perfil = ProfessionalProfile::with('specialties')->find($perfilId);
            $especialidadId = $perfil->specialties->isNotEmpty()
                ? $perfil->specialties->random()->id
                : $specialties->random()->id;

            Appointment::create([
                'patient_id' => fake()->randomElement($pacienteIds),
                'professional_profile_id' => $perfilId,
                'specialty_id' => $especialidadId,
                'start_datetime' => fake()->dateTimeBetween('+1 day', '+2 months'),
                'status' => fake()->randomElement(['reservada', 'confirmada']),
            ]);
        }

        $this->command->info('  → 100 citas creadas (60 pasadas + 15 hoy + 25 futuras).');

        // =====================================================
        // 6. TABLA TRANSACCIONAL: LISTA DE ESPERA (50 registros)
        // =====================================================
        $this->command->info('Creando 50 registros en lista de espera...');

        for ($i = 0; $i < 50; $i++) {
            // 70% tienen médico preferido, 30% aceptan cualquier médico
            $tienePreferencia = fake()->boolean(70);

            // Distribuir estados: 50% pendiente, 20% notificado, 15% reasignado, 15% cancelado
            $rand = fake()->numberBetween(1, 100);
            if ($rand <= 50) {
                $status = 'Pendiente';
                $managedBy = null;
            } elseif ($rand <= 70) {
                $status = 'Notificado';
                $managedBy = $admin?->id;
            } elseif ($rand <= 85) {
                $status = 'Reasignado';
                $managedBy = $admin?->id;
            } else {
                $status = 'Cancelado';
                $managedBy = $admin?->id;
            }

            Waitlist::create([
                'patient_id' => fake()->randomElement($pacienteIds),
                'professional_profile_id' => $tienePreferencia ? fake()->randomElement($perfilIds) : null,
                'specialty_id' => $specialties->random()->id,
                'status' => $status,
                'managed_by' => $managedBy,
                'created_at' => fake()->dateTimeBetween('-2 months', 'now'),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('  → 50 registros en lista de espera creados.');

        // =====================================================
        // RESUMEN FINAL
        // =====================================================
        $this->command->newLine();
        $this->command->info('========================================');
        $this->command->info('  POBLAMIENTO COMPLETADO CON ÉXITO');
        $this->command->info('========================================');
        $this->command->table(
            ['Tabla', 'Registros'],
            [
                ['users (admin)', '1'],
                ['users (médicos)', count($medicosData)],
                ['users (pacientes)', $pacientes->count()],
                ['specialties', $specialties->count()],
                ['professional_profiles', count($perfiles)],
                ['professional_profile_specialty', '~' . count($perfiles) * 2],
                ['availabilities', $totalDisponibilidades],
                ['appointments', '100'],
                ['waitlists', '50'],
            ]
        );
    }
}
