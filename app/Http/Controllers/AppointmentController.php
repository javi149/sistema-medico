<?php

namespace App\Http\Controllers;

use App\Mail\AppointmentNotification;
use App\Models\Appointment;
use App\Models\ProfessionalProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AppointmentController extends Controller
{
    /**
     * Listar las citas del paciente logueado.
     */
    public function index()
    {
        $misCitas = Appointment::with(['professionalProfile.user', 'specialty'])
            ->where('patient_id', Auth::id())
            ->orderBy('start_datetime', 'asc')
            ->get();

        return view('citas.index', compact('misCitas'));
    }

    /**
     * Formulario para crear una nueva cita.
     */
    public function create()
    {
        // Traemos todos los perfiles profesionales, junto con los datos de su usuario (nombre) 
        // y sus especialidades para mostrarlos en el menú desplegable.
        $perfiles = ProfessionalProfile::with(['user', 'specialties'])->get();

        return view('citas.create', compact('perfiles'));
    }

    /**
     * Guardar una nueva cita con protección contra doble reserva.
     */
    public function store(Request $request)
    {
        $request->validate([
            'professional_profile_id' => 'required|exists:professional_profiles,id',
            'start_datetime'          => 'required|date|after:now',
        ]);

        try {
            DB::transaction(function () use ($request) {

                // Bloqueo pesimista para evitar doble reserva concurrente
                $conflicto = Appointment::where('professional_profile_id', $request->professional_profile_id)
                    ->where('start_datetime', $request->start_datetime)
                    ->whereIn('status', ['reservada', 'confirmada'])
                    ->lockForUpdate()
                    ->first();

                if ($conflicto) {
                    throw new \Exception('Lo sentimos, este bloque horario acaba de ser reservado por otro paciente.');
                }

                $perfil = ProfessionalProfile::with('specialties')->findOrFail($request->professional_profile_id);

                if ($perfil->specialties->isEmpty()) {
                    throw new \Exception('El médico seleccionado no tiene especialidades registradas.');
                }

                Appointment::create([
                    'patient_id'              => Auth::id(),
                    'professional_profile_id' => $request->professional_profile_id,
                    'specialty_id'            => $perfil->specialties->first()->id,
                    'start_datetime'          => $request->start_datetime,
                    'status'                  => 'reservada',
                ]);
            });

            return redirect()->back()->with('success', '¡Tu cita médica ha sido agendada con éxito!');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Cancelar una cita (acción del paciente desde su portal).
     * Solo puede cancelar sus propias citas y solo si están en estado "reservada".
     */
    public function cancelar(Appointment $cita)
    {
        if ($cita->patient_id !== Auth::id()) {
            abort(403, 'Acción no autorizada. No puedes cancelar citas de otros pacientes.');
        }

        if ($cita->status !== 'reservada') {
            return redirect()->back()->withErrors(['error' => 'Esta cita no se puede cancelar en su estado actual.']);
        }

        $cita->update(['status' => 'cancelada']);

        return redirect()->back()->with('success', 'Tu cita ha sido anulada exitosamente. El bloque ha sido liberado.');
    }

    /**
     * Cancelar una cita desde el panel de administración y notificar al paciente por email.
     */
    public function cancel(string $id)
    {
        $appointment = Appointment::with('patient')->findOrFail($id);

        $appointment->status = 'cancelada';
        $appointment->save();

        Mail::to($appointment->patient->email)->send(
            new AppointmentNotification($appointment, 'Cancelada')
        );

        return redirect()->back()->with('success', 'Cita cancelada con éxito y notificación enviada.');
    }

    /**
     * Modificar una cita desde el panel de administración y notificar al paciente por email.
     */
    public function update(Request $request,string $id)
    {
        $appointment = Appointment::with('patient')->findOrFail($id);

        $appointment->fill($request->only(['start_datetime']));
        $appointment->status = 'modificada';
        $appointment->save();

        Mail::to($appointment->patient->email)->send(
            new AppointmentNotification($appointment, 'Modificada')
        );

        return redirect()->back()->with('success', 'Cita modificada con éxito y notificación enviada.');
    }

    // ---------------------------------------------------------------
    // Métodos del CRUD estándar (pendientes de implementar)
    // ---------------------------------------------------------------

    public function show(string $id)
    {
        //
    }

    public function edit(string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}