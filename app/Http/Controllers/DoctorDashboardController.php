<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorDashboardController extends Controller
{
    public function index()
    {
        // 1. Obtenemos al usuario que acaba de iniciar sesión (El Médico)
        $usuario = Auth::user();

        // 2. Seguridad: Verificamos que este usuario realmente tenga un Perfil Profesional creado.
        // Si el administrador le creó la cuenta pero olvidó hacerle el perfil, esto evita que el sistema explote (Error 500).
        if (!$usuario->professionalProfile) {
            abort(403, 'Aún no tienes un perfil médico asignado en el sistema.');
        }

        $miPerfilId = $usuario->professionalProfile->id;
        $hoy = Carbon::today();

        // 3. Buscamos SOLO las citas de este médico para el día de hoy
        $misPacientesHoy = Appointment::with(['patient', 'specialty'])
            ->where('professional_profile_id', $miPerfilId)
            ->whereDate('start_datetime', $hoy)
            ->orderBy('start_datetime', 'asc')
            ->get();

        // 4. Enviamos los datos a la vista del doctor
        return view('medico.dashboard', compact('misPacientesHoy'));
    }
}