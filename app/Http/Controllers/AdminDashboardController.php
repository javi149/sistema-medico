<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Capturamos la fecha seleccionada o la actual del servidor
        $dateParam = $request->query('date');
        try {
            $hoy = $dateParam ? Carbon::parse($dateParam)->startOfDay() : Carbon::today();
        } catch (\Exception $e) {
            $hoy = Carbon::today();
        }

        // 2. Agregaciones estadísticas optimizadas para los cuadros superiores del Mockup
        $totalCitas = Appointment::whereDate('start_datetime', $hoy)->count();
        
        $confirmadas = Appointment::whereDate('start_datetime', $hoy)
            ->whereIn('status', ['reservada', 'confirmada', 'Modificada', 'modificada'])->count();
            
        $atendidas = Appointment::whereDate('start_datetime', $hoy)
            ->whereIn('status', ['atendida', 'Atendida'])->count();
            
        $ausentes = Appointment::whereDate('start_datetime', $hoy)
            ->whereIn('status', ['ausente', 'Ausente'])->count();

        // 3. Agenda del día con Carga Ansiosa (Eager Loading) de relaciones triples
        $agenda = Appointment::with(['patient', 'professionalProfile.user', 'specialty'])
            ->whereDate('start_datetime', $hoy)
            ->orderBy('start_datetime', 'asc')
            ->get();

        // 4. Despachamos el set de datos consolidado a la vista administrativa
        return view('admin.dashboard', compact(
            'totalCitas', 'confirmadas', 'atendidas', 'ausentes', 'agenda', 'hoy'
        ));
    }
}