<?php

namespace App\Http\Controllers;

use App\Models\Waitlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaitlistController extends Controller
{
    /**
     * Store a new waitlist entry.
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:users,id',
            'specialty_id' => 'required|exists:specialties,id',
            'professional_profile_id' => 'nullable|exists:professional_profiles,id',
        ]);

        // Check if the patient is already in the waitlist for this specialty with a 'Pendiente' status
        $exists = Waitlist::where('patient_id', $request->patient_id)
            ->where('specialty_id', $request->specialty_id)
            ->where('status', 'Pendiente')
            ->exists();

        if ($exists) {
            return redirect()->back()->withErrors([
                'error' => 'Ya te encuentras registrado en la lista de espera para esta especialidad.'
            ]);
        }

        Waitlist::create([
            'patient_id' => $request->patient_id,
            'specialty_id' => $request->specialty_id,
            'professional_profile_id' => $request->professional_profile_id,
            'status' => 'Pendiente',
        ]);

        return redirect()->route('dashboard')->with('success', 'Te has inscrito exitosamente en la lista de espera. Te enviaremos un correo apenas se libere un cupo.');
    }

    /**
     * Accept the offered slot.
     */
    public function accept(Waitlist $waitlist)
    {
        if ($waitlist->patient_id !== Auth::id()) {
            abort(403, 'No estás autorizado para realizar esta acción.');
        }

        if ($waitlist->status !== 'Notificado') {
            return redirect()->back()->withErrors(['error' => 'Esta oferta no está activa o ya fue procesada.']);
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($waitlist) {
                // Check if slot is still available (to prevent double booking)
                $conflicto = \App\Models\Appointment::where('professional_profile_id', $waitlist->professional_profile_id)
                    ->where('start_datetime', $waitlist->offered_datetime)
                    ->whereIn('status', ['reservada', 'confirmada', 'Modificada', 'modificada'])
                    ->lockForUpdate()
                    ->first();

                if ($conflicto) {
                    throw new \Exception('Lo sentimos, este bloque horario ya fue reservado por otro paciente.');
                }

                // Create the appointment
                $appointment = \App\Models\Appointment::create([
                    'patient_id' => $waitlist->patient_id,
                    'professional_profile_id' => $waitlist->professional_profile_id,
                    'specialty_id' => $waitlist->specialty_id,
                    'start_datetime' => $waitlist->offered_datetime,
                    'status' => 'reservada',
                ]);

                // Update waitlist status to Reasignado
                $waitlist->update([
                    'status' => 'Reasignado',
                ]);

                // Send confirmation email
                try {
                    \Illuminate\Support\Facades\Mail::to($waitlist->patient->email)->send(new \App\Mail\AppointmentBooked($appointment));
                } catch (\Exception $e) {
                    // Ignore
                }
            });

            return redirect()->route('citas.index')->with('success', '¡Cupo aceptado con éxito! Tu cita médica ha sido agendada.');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Decline the offered slot.
     */
    public function decline(Waitlist $waitlist)
    {
        if ($waitlist->patient_id !== Auth::id()) {
            abort(403, 'No estás autorizado para realizar esta acción.');
        }

        if ($waitlist->status !== 'Notificado') {
            return redirect()->back()->withErrors(['error' => 'Esta oferta no está activa o ya fue procesada.']);
        }

        // Update waitlist status to Cancelado
        $waitlist->update([
            'status' => 'Cancelado',
        ]);

        // Since the patient declined, this slot is now free again! 
        // We notify the next patient in line.
        if ($waitlist->specialty_id && $waitlist->professional_profile_id && $waitlist->offered_datetime) {
            Waitlist::notifyNextInWaitlist(
                $waitlist->specialty_id,
                $waitlist->professional_profile_id,
                $waitlist->offered_datetime
            );
        }

        return redirect()->route('citas.index')->with('success', 'Has rechazado la hora sugerida. Tu solicitud en lista de espera ha sido cancelada.');
    }
}
