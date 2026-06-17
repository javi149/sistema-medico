<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Mail\AppointmentNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
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