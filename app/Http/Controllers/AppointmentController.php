<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\ProfessionalProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. Buscamos solo las citas del paciente logueado.
        // Usamos 'with' para traer los datos del médico y la especialidad en una sola consulta.
        $misCitas = Appointment::with(['professionalProfile.user', 'specialty'])
            ->where('patient_id', Auth::id())
            ->orderBy('start_datetime', 'asc') // Ordenamos desde la cita más próxima a la más lejana
            ->get();

        // 1.1 Buscamos ofertas de lista de espera notificadas para el paciente.
        $waitlistOffers = \App\Models\Waitlist::with(['specialty', 'professionalProfile.user'])
            ->where('patient_id', Auth::id())
            ->where('status', 'Notificado')
            ->get();

        // 2. Enviamos la variable a una nueva vista
        return view('citas.index', compact('misCitas', 'waitlistOffers'));
    }

    public function create()
    {
        $specialties = \App\Models\Specialty::all();
        return view('citas.create', compact('specialties'));
    }

    /**
     * Store a newly created resource in storage.
     * 
     * [LÓGICA DE NEGOCIO PROFUNDA - INGENIERÍA DE SOFTWARE]
     * Este método implementa el caso de uso central del sistema: Agendamiento de Citas.
     * Se aplican principios ACID y control de concurrencia para mantener la consistencia de datos.
     */
    public function store(Request $request)
    {
        // 1. VALIDACIÓN (Capa de Seguridad y Reglas de Negocio Básicas)
        // Se valida integridad referencial (exists) y reglas temporales (after:now)
        $request->validate([
            'patient_id' => 'required|exists:users,id',
            'professional_profile_id' => 'required|exists:professional_profiles,id',
            'start_datetime' => 'required|date|after:now', // Regla: No agendar en el pasado
        ]);

        try {
            // 2. TRANSACCIÓN DE BASE DE DATOS (Atomicidad)
            // Asegura que todas las operaciones dentro del closure se ejecuten con éxito (Commit)
            // o ninguna lo haga (Rollback), previniendo inconsistencias estructurales.
            $appointment = DB::transaction(function () use ($request) {
                
                // 3. CONTROL DE CONCURRENCIA (Bloqueo Pesimista)
                // lockForUpdate() aplica un "FOR UPDATE" a nivel de SQL. Si dos pacientes intentan
                // reservar el mismo bloque simultáneamente (condición de carrera), el motor
                // de BD pondrá en cola la segunda petición hasta que la primera libere el bloqueo.
                $conflicto = Appointment::where('professional_profile_id', $request->professional_profile_id)
                    ->where('start_datetime', $request->start_datetime)
                    ->whereIn('status', ['reservada', 'confirmada', 'Modificada', 'modificada']) // Excluye canceladas
                    ->lockForUpdate()
                    ->first();

                if ($conflicto) {
                    // Rompemos la transacción lanzando una excepción si el bloque ya fue tomado
                    throw new \Exception('Lo sentimos, este bloque horario acaba de ser reservado por otro paciente.');
                }

                // 4. INTEGRIDAD REFERENCIAL COMPLEJA
                // Obtenemos la especialidad del médico para asegurar consistencia en reportes futuros
                $perfil = ProfessionalProfile::with('specialties')->findOrFail($request->professional_profile_id);
                
                // Validamos que el médico tenga al menos una especialidad asignada
                if ($perfil->specialties->isEmpty()) {
                    throw new \Exception('El médico seleccionado no tiene especialidades registradas.');
                }
                
                $especialidad_id = $perfil->specialties->first()->id;

                
                // 4. Guardamos la cita definitiva en la base de datos
                $createdAppointment = Appointment::create([
                    'patient_id' => $request->patient_id, // Usamos el paciente validado del wizard
                    'professional_profile_id' => $request->professional_profile_id,
                    'specialty_id' => $especialidad_id,
                    'start_datetime' => $request->start_datetime,
                    'status' => 'reservada',
                ]);
                
                return $createdAppointment;
            });

            // 5. Enviar el correo electrónico
            \Illuminate\Support\Facades\Mail::to($appointment->patient->email)->send(new \App\Mail\AppointmentBooked($appointment));

            // 6. Si la transacción termina con éxito, redirigimos a la pantalla de éxito
            return redirect()->route('citas.success', $appointment->id);

        } catch (\Exception $e) {
            // Si algo falla (como el error del bloque ocupado), lo atrapamos y mostramos el mensaje rojo
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    
    public function success(Appointment $cita)
    {
        return view('appointments.success', compact('cita'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cita = Appointment::with(['professionalProfile.user', 'specialty'])->findOrFail($id);
        
        // Capa de seguridad: Aseguramos que la cita le pertenezca al paciente logueado
        if ($cita->patient_id !== Auth::id()) {
            abort(403, 'Acción no autorizada. No puedes modificar citas de otros pacientes.');
        }

        // Solo podemos modificar citas reservadas o confirmadas o modificadas
        if (!in_array(strtolower($cita->status), ['reservada', 'confirmada', 'modificada'])) {
            return redirect()->route('citas.index')->withErrors(['error' => 'Esta cita no se puede modificar en su estado actual.']);
        }

        return view('citas.edit', compact('cita'));
    }

    /**
     * Update the patient's appointment details (rescheduling).
     */
    public function updatePaciente(Request $request, string $id)
    {
        $cita = Appointment::findOrFail($id);

        // Capa de seguridad: Aseguramos que la cita le pertenezca al paciente logueado
        if ($cita->patient_id !== Auth::id()) {
            abort(403, 'Acción no autorizada.');
        }

        $request->validate([
            'start_datetime' => 'required|date|after:now',
        ]);

        try {
            // Validamos disponibilidad y realizamos el cambio mediante transacción
            DB::transaction(function () use ($request, $cita) {
                
                // Buscar si ya existe otra cita reservada para ese médico en ese horario
                $conflicto = Appointment::where('professional_profile_id', $cita->professional_profile_id)
                    ->where('start_datetime', $request->start_datetime)
                    ->where('id', '!=', $cita->id) // No contar la misma cita que estamos modificando
                    ->whereIn('status', ['reservada', 'confirmada', 'Modificada', 'modificada'])
                    ->lockForUpdate()
                    ->first();

                if ($conflicto) {
                    throw new \Exception('Lo sentimos, este bloque horario ya no está disponible.');
                }

                $cita->update([
                    'start_datetime' => $request->start_datetime,
                    'status' => 'Modificada', // El test espera 'Modificada' con M mayúscula
                ]);
            });

            // Cargar relaciones antes de enviar el correo
            $cita->load('patient');

            // Enviar correo de notificación
            try {
                \Illuminate\Support\Facades\Mail::to($cita->patient->email)
                    ->send(new \App\Mail\AppointmentNotification($cita, 'Modificada'));
            } catch (\Exception $e) {
                // Ignorar error de envío de correo en local
            }

            return redirect()->route('citas.index')->with('success', 'Su cita ha sido reprogramada con éxito y se ha enviado la notificación por correo.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }


    public function cancelar(Appointment $cita)
    {
        // 1. Capa de Seguridad: Validamos que la cita pertenezca al usuario logueado
        if ($cita->patient_id !== Auth::id()) {
            abort(403, 'Acción no autorizada. No puedes cancelar citas de otros pacientes.');
        }

        // 2. Regla de Negocio: Solo podemos cancelar citas que estén "reservadas"
        if ($cita->status === 'reservada') {
            
            // 3. La actualización en la base de datos
            $cita->update(['status' => 'cancelada']);
            
            return redirect()->back()->with('success', 'Tu cita ha sido anulada exitosamente. El bloque ha sido liberado.');
        }

        // Si intentan cancelar una cita que ya fue atendida o cancelada previamente
        return redirect()->back()->withErrors(['error' => 'Esta cita no se puede cancelar en su estado actual.']);
    }
    /**
     * Cancelar una cita médica y notificar al paciente.
     */
    public function cancel($id)
    {
        // 1. Buscar la cita en PostgreSQL
        $appointment = Appointment::findOrFail($id);

        // 2. Cambiar el estado a Cancelada
        $appointment->status = 'cancelada';
        $appointment->save();

        // 3. Enviar correo de notificación
        \Illuminate\Support\Facades\Mail::to($appointment->patient->email)->send(new \App\Mail\AppointmentNotification($appointment, 'Cancelada'));
        // 3. Obtener el correo del usuario asociado a la cita
        $userEmail = $appointment->patient->email;

        // 4. Enviar el correo usando Mailtrap
        try {
            \Illuminate\Support\Facades\Mail::to($userEmail)->send(new \App\Mail\AppointmentNotification($appointment, 'Cancelada'));
        } catch (\Exception $e) {
            // Ignorar errores de correo local
        }

        return redirect()->back()->with('success', 'Cita cancelada con éxito y notificación enviada.');
    }

    /**
     * Modificar una cita médica y notificar al paciente.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'start_datetime' => 'sometimes|date|after:now',
            'specialty_id' => 'sometimes|exists:specialties,id',
        ]);

        $appointment = Appointment::findOrFail($id);
        
        // Actualizar los campos que vengan en la petición (fecha, hora de inicio, hora de término)
        $appointment->fill($request->only(['start_datetime', 'specialty_id']));
        
        $appointment->status = 'Modificada';
        $appointment->save();

        // Enviar correo de notificación de modificación
        \Illuminate\Support\Facades\Mail::to($appointment->patient->email)->send(new \App\Mail\AppointmentNotification($appointment, 'Modificada'));
        $userEmail = $appointment->patient->email;

        // Enviar notificación de modificación
        try {
            \Illuminate\Support\Facades\Mail::to($userEmail)->send(new \App\Mail\AppointmentNotification($appointment, 'Modificada'));
        } catch (\Exception $e) {
            // Ignorar errores de correo local
        }

        return redirect()->back()->with('success', 'Cita modificada con éxito y notificación enviada.');
    }

    public function checkRut(Request $request)
    {
        $request->validate(['rut' => ['required', new \App\Rules\ValidRut]]);
        
        // Buscar el RUT tal como se ingresó (con puntos y guión)
        $user = \App\Models\User::where('rut', $request->rut)->role('paciente')->first();
        
        // Si no lo encuentra, intentar buscar agregando puntos si es que el usuario no los puso
        if (!$user) {
            $rutSinPuntos = str_replace('.', '', $request->rut);
            // Intentar encontrar donde el RUT de la BD sin puntos coincida con el ingresado sin puntos
            $user = \App\Models\User::whereRaw("REPLACE(rut, '.', '') = ?", [$rutSinPuntos])->role('paciente')->first();
        }

        if ($user) {
            return response()->json(['success' => true, 'name' => $user->name, 'id' => $user->id]);
        }
        return response()->json(['success' => false, 'message' => 'Paciente no encontrado en nuestros registros.']);
    }

    public function getAvailability(Request $request)
    {
        $specialty_id = $request->query('specialty_id');
        $date = $request->query('date'); // YYYY-MM-DD
        
        $doctors = ProfessionalProfile::whereHas('specialties', function($q) use ($specialty_id) {
            $q->where('specialties.id', $specialty_id);
        })->with('user')->get();

        $dayOfWeek = \Carbon\Carbon::parse($date)->dayOfWeekIso; // 1 = Lunes, 7 = Domingo
        $now = \Carbon\Carbon::now();
        $isToday = \Carbon\Carbon::parse($date)->isToday();

        // Optimización N+1: Obtener disponibilidades y citas de una sola vez
        $doctorIds = $doctors->pluck('id');
        
        $allAvailabilities = \App\Models\Availability::whereIn('professional_profile_id', $doctorIds)
            ->where('day_of_week', $dayOfWeek)
            ->get()
            ->groupBy('professional_profile_id');

        $allAppointments = Appointment::whereIn('professional_profile_id', $doctorIds)
            ->whereDate('start_datetime', $date)
            ->whereIn('status', ['reservada', 'confirmada', 'Modificada', 'modificada'])
            ->get()
            ->groupBy('professional_profile_id');

        $results = [];
        foreach ($doctors as $doctor) {
            $availabilities = $allAvailabilities->get($doctor->id, collect());
            $appointments = $allAppointments->get($doctor->id, collect());

            $slots = [];
            foreach ($availabilities as $avail) {
                $start = \Carbon\Carbon::parse($avail->start_time);
                $end = \Carbon\Carbon::parse($avail->end_time);
                
                while ($start->copy()->addMinutes(30) <= $end) {
                    $slotTime = $start->format('H:i');
                    $slotDatetime = $date . ' ' . $slotTime . ':00';
                    $slotCarbon = \Carbon\Carbon::parse($slotDatetime);
                    
                    // Filtrar horas en el pasado si es hoy para evitar error 'after:now'
                    if ($isToday && $slotCarbon->isPast()) {
                        $start->addMinutes(30);
                        continue;
                    }
                    
                    $hasAppt = $appointments->contains(function($appt) use ($slotDatetime) {
                        return \Carbon\Carbon::parse($appt->start_datetime)->format('Y-m-d H:i:s') === $slotDatetime;
                    });

                    $slots[] = [
                        'time' => $slotTime,
                        'available' => !$hasAppt,
                        'datetime' => $slotDatetime
                    ];
                    $start->addMinutes(30);
                }
            }

            if (count($slots) > 0) {
                $results[] = [
                    'doctor_id' => $doctor->id,
                    'doctor_name' => $doctor->user->name,
                    'slots' => $slots
                ];
            }
        }

        return response()->json($results);
    }

    /**
     * Admin view to schedule a new appointment.
     */
    public function adminCreate()
    {
        $pacientes = \App\Models\User::role('paciente')->orderBy('name')->get();
        $specialties = \App\Models\Specialty::all();
        return view('admin.appointments.create', compact('pacientes', 'specialties'));
    }

    /**
     * Admin logic to store a scheduled appointment.
     */
    public function adminStore(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:users,id',
            'professional_profile_id' => 'required|exists:professional_profiles,id',
            'start_datetime' => 'required|date|after:now',
        ]);

        try {
            $appointment = DB::transaction(function () use ($request) {
                
                // Buscar si existe un conflicto
                $conflicto = Appointment::where('professional_profile_id', $request->professional_profile_id)
                    ->where('start_datetime', $request->start_datetime)
                    ->whereIn('status', ['reservada', 'confirmada', 'Modificada', 'modificada'])
                    ->lockForUpdate()
                    ->first();

                if ($conflicto) {
                    throw new \Exception('Lo sentimos, este bloque horario ya está reservado.');
                }

                $perfil = ProfessionalProfile::with('specialties')->findOrFail($request->professional_profile_id);
                if ($perfil->specialties->isEmpty()) {
                    throw new \Exception('El médico seleccionado no tiene especialidades registradas.');
                }
                $especialidad_id = $perfil->specialties->first()->id;

                return Appointment::create([
                    'patient_id' => $request->patient_id,
                    'professional_profile_id' => $request->professional_profile_id,
                    'specialty_id' => $especialidad_id,
                    'start_datetime' => $request->start_datetime,
                    'status' => 'reservada',
                ]);
            });

            // Enviar correo al paciente
            try {
                \Illuminate\Support\Facades\Mail::to($appointment->patient->email)
                    ->send(new \App\Mail\AppointmentBooked($appointment));
            } catch (\Exception $e) {
                // Ignorar
            }

            // Redirigir al dashboard administrativo de esa fecha
            $fechaUrl = \Carbon\Carbon::parse($appointment->start_datetime)->format('Y-m-d');
            return redirect()->route('admin.dashboard', ['date' => $fechaUrl])->with('success', 'Cita médica agendada con éxito para el paciente.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Admin view to reschedule an appointment.
     */
    public function adminEdit(Appointment $appointment)
    {
        $appointment->load(['patient', 'professionalProfile.user', 'specialty']);
        return view('admin.appointments.edit', compact('appointment'));
    }

    /**
     * Admin logic to reschedule an appointment.
     */
    public function adminUpdate(Request $request, Appointment $appointment)
    {
        $request->validate([
            'start_datetime' => 'required|date|after:now',
        ]);

        try {
            DB::transaction(function () use ($request, $appointment) {
                
                // Buscar si existe conflicto
                $conflicto = Appointment::where('professional_profile_id', $appointment->professional_profile_id)
                    ->where('start_datetime', $request->start_datetime)
                    ->where('id', '!=', $appointment->id)
                    ->whereIn('status', ['reservada', 'confirmada', 'Modificada', 'modificada'])
                    ->lockForUpdate()
                    ->first();

                if ($conflicto) {
                    throw new \Exception('Lo sentimos, este bloque horario ya está reservado.');
                }

                $appointment->update([
                    'start_datetime' => $request->start_datetime,
                    'status' => 'Modificada', // Mantener 'Modificada' con M mayúscula para compatibilidad
                ]);
            });

            // Enviar correo
            try {
                \Illuminate\Support\Facades\Mail::to($appointment->patient->email)
                    ->send(new \App\Mail\AppointmentNotification($appointment, 'Modificada'));
            } catch (\Exception $e) {
                // Ignorar
            }

            $fechaUrl = \Carbon\Carbon::parse($appointment->start_datetime)->format('Y-m-d');
            return redirect()->route('admin.dashboard', ['date' => $fechaUrl])->with('success', 'Cita reprogramada con éxito.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Admin logic to delete an appointment permanently.
     */
    public function adminDestroy(Appointment $appointment)
    {
        try {
            // Enviar correo informando de la eliminación/cancelación permanente
            try {
                \Illuminate\Support\Facades\Mail::to($appointment->patient->email)
                    ->send(new \App\Mail\AppointmentNotification($appointment, 'Eliminada'));
            } catch (\Exception $e) {
                // Ignorar
            }

            $appointment->delete();
            return redirect()->back()->with('success', 'Cita médica eliminada permanentemente del sistema.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Error al eliminar la cita: ' . $e->getMessage()]);
        }
    }
}
