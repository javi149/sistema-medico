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

        // 2. Enviamos la variable a una nueva vista
        return view('citas.index', compact('misCitas'));
    }

    public function create()
    {
        $specialties = \App\Models\Specialty::all();
        return view('citas.create', compact('specialties'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validación inicial: Aseguramos que los datos sean correctos y coherentes
        $request->validate([
            'patient_id' => 'required|exists:users,id',
            'professional_profile_id' => 'required|exists:professional_profiles,id',
            'start_datetime' => 'required|date|after:now', // Regla de negocio: No agendar en el pasado
        ]);

        try {
            // 2. Iniciamos la Transacción de Base de Datos
            $appointment = DB::transaction(function () use ($request) {
                
                // Buscamos si ya existe una cita en esa misma fecha/hora para ese médico específico.
                // lockForUpdate() bloquea las filas que coincidan para que nadie más las lea mientras guardamos.
                $conflicto = Appointment::where('professional_profile_id', $request->professional_profile_id)
                    ->where('start_datetime', $request->start_datetime)
                    ->whereIn('status', ['reservada', 'confirmada']) // Si está cancelada, no es conflicto
                    ->lockForUpdate()
                    ->first();

                if ($conflicto) {
                    // Si existe un conflicto, rompemos la transacción lanzando una Excepción
                    throw new \Exception('Lo sentimos, este bloque horario acaba de ser reservado por otro paciente.');
                }

                // 3. Obtenemos la especialidad principal del médico para adjuntarla a la cita
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
        //
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
        $appointment->status = 'Cancelada';
        $appointment->save();

        // 3. Obtener el correo del usuario asociado a la cita
        // $userEmail = $appointment->user->email; // Nota: si existe relacion user

        // 4. Enviar el correo usando Mailtrap
        // Mail::to($userEmail)->send(new AppointmentNotification($appointment, 'Cancelada'));

        return redirect()->back()->with('success', 'Cita cancelada con éxito y notificación enviada.');
    }

    /**
     * Modificar una cita médica y notificar al paciente.
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        // Actualizar los campos que vengan en la petición (fecha, hora de inicio, hora de término)
        $appointment->fill($request->only(['start_datetime', 'specialty_id']));
        
        $appointment->status = 'Modificada';
        $appointment->save();

        // $userEmail = $appointment->user->email; 

        // Enviar notificación de modificación
        // Mail::to($userEmail)->send(new AppointmentNotification($appointment, 'Modificada'));

        return redirect()->back()->with('success', 'Cita modificada con éxito y notificación enviada.');
    }

    public function checkRut(Request $request)
    {
        $request->validate(['rut' => 'required']);
        
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
            ->whereIn('status', ['reservada', 'confirmada'])
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
}
