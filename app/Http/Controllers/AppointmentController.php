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
        // Traemos todos los perfiles profesionales, junto con los datos de su usuario (nombre) 
        // y sus especialidades para mostrarlos en el menú desplegable.
        $profiles = ProfessionalProfile::with(['user', 'specialties'])->get();

        return view('citas.create', compact('profiles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // 1. Validación inicial: Aseguramos que los datos sean correctos y coherentes
        $request->validate([
            'professional_profile_id' => 'required|exists:professional_profiles,id',
            'start_datetime' => 'required|date|after:now', // Regla de negocio: No agendar en el pasado
        ]);

        try {
            // 2. Iniciamos la Transacción de Base de Datos
            DB::transaction(function () use ($request) {
                
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
                Appointment::create([
                    'patient_id' => Auth::id(), // Capturamos automáticamente el ID del paciente logueado
                    'professional_profile_id' => $request->professional_profile_id,
                    'specialty_id' => $especialidad_id,
                    'start_datetime' => $request->start_datetime,
                    'status' => 'reservada',
                ]);
            });

            // 5. Si la transacción termina con éxito, devolvemos al usuario con un mensaje verde
            return redirect()->back()->with('success', '¡Tu cita médica ha sido agendada con éxito!');

        } catch (\Exception $e) {
            // Si algo falla (como el error del bloque ocupado), lo atrapamos y mostramos el mensaje rojo
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
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

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
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
}
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
        $userEmail = $appointment->user->email; 

        // 4. Enviar el correo usando Mailtrap
        Mail::to($userEmail)->send(new AppointmentNotification($appointment, 'Cancelada'));

        return redirect()->back()->with('success', 'Cita cancelada con éxito y notificación enviada.');
    }

    /**
     * Modificar una cita médica y notificar al paciente.
     */
    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        // Actualizar los campos que vengan en la petición (fecha, hora de inicio, hora de término)
        $appointment->fill($request->only(['date', 'start_time', 'end_time']));
        
        $appointment->status = 'Modificada';
        $appointment->save();

        $userEmail = $appointment->user->email; 

        // Enviar notificación de modificación
        Mail::to($userEmail)->send(new AppointmentNotification($appointment, 'Modificada'));

        return redirect()->back()->with('success', 'Cita modificada con éxito y notificación enviada.');
    }
}
