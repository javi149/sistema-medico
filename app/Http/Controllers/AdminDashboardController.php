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

        // 2. Agenda del día con Carga Ansiosa (Eager Loading)
        $agenda = Appointment::with(['patient', 'professionalProfile.user', 'specialty'])
            ->whereDate('start_datetime', $hoy)
            ->orderBy('start_datetime', 'asc')
            ->get();

        // 3. Agregaciones estadísticas optimizadas en memoria (evita 4 queries a la BD)
        $totalCitas = $agenda->count();
        
        $confirmadas = $agenda->filter(fn($c) => in_array(strtolower($c->status), ['reservada', 'confirmada', 'modificada']))->count();
            
        $atendidas = $agenda->filter(fn($c) => strtolower($c->status) === 'atendida')->count();
            
        $ausentes = $agenda->filter(fn($c) => strtolower($c->status) === 'ausente')->count();

        // 4. Despachamos el set de datos consolidado a la vista administrativa
        return view('admin.dashboard', compact(
            'totalCitas', 'confirmadas', 'atendidas', 'ausentes', 'agenda', 'hoy'
        ));
    }
}