<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // 1. Capturamos la fecha actual del servidor
        $hoy = Carbon::today();

        // 2. Agregaciones estadísticas optimizadas para los cuadros superiores del Mockup
        $totalCitas = Appointment::whereDate('start_datetime', $hoy)->count();
        
        $confirmadas = Appointment::whereDate('start_datetime', $hoy)
            ->whereIn('status', ['reservada', 'confirmada'])->count();
            
        $atendidas = Appointment::whereDate('start_datetime', $hoy)
            ->where('status', 'atendida')->count();
            
        $ausentes = Appointment::whereDate('start_datetime', $hoy)
            ->where('status', 'ausente')->count();

        // 3. Agenda del día con Carga Ansiosa (Eager Loading) de relaciones triples
        $agenda = Appointment::with(['patient', 'professionalProfile.user', 'specialty'])
            ->whereDate('start_datetime', $hoy)
            ->orderBy('start_datetime', 'asc')
            ->get();

        // 4. Despachamos el set de datos consolidado a la vista administrativa
        return view('admin.dashboard', compact(
            'totalCitas', 'confirmadas', 'atendidas', 'ausentes', 'agenda'
        ));
    }
}