<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        if ($user->hasRole('paciente')) {
            // 1. Buscamos todas las citas del paciente (una sola query con eager loading)
            $citas = \App\Models\Appointment::with(['professionalProfile.user', 'specialty'])
                ->where('patient_id', $user->id)
                ->orderBy('start_datetime', 'desc')
                ->get();

            // 2. Próxima cita programada: la derivamos de la colección ya cargada (sin query extra)
            $proximaCita = $citas
                ->filter(fn($c) => \Carbon\Carbon::parse($c->start_datetime)->isFuture()
                    && in_array(strtolower($c->status), ['reservada', 'confirmada', 'modificada']))
                ->sortBy('start_datetime')
                ->first();

            // 3. Estadísticas clave (todo desde la colección en memoria)
            $totalCitas = $citas->count();
            $atendidas = $citas->where('status', 'atendida')->count();
            $ausentes = $citas->where('status', 'ausente')->count();
            $canceladas = $citas->where('status', 'cancelada')->count();
            $programadas = $citas->filter(fn($c) => in_array(strtolower($c->status), ['reservada', 'confirmada', 'modificada']))->count();

            // Tasa de asistencia (porcentaje de citas atendidas sobre las citas no canceladas)
            $citasValidas = $totalCitas - $canceladas;
            $tasaAsistencia = $citasValidas > 0 ? round(($atendidas / $citasValidas) * 100) : 0;

            // 4. Profesionales frecuentes (médicos con los que más se ha atendido)
            $medicosFrecuentes = $citas->groupBy('professional_profile_id')
                ->map(function ($group) {
                    $first = $group->first();
                    return [
                        'count' => $group->count(),
                        'name' => $first->professionalProfile->user->name ?? 'Médico Asignado',
                        'specialty' => $first->specialty->name ?? 'General',
                        'email' => $first->professionalProfile->user->email ?? '',
                    ];
                })
                ->sortByDesc('count')
                ->take(3);

            // 5. Especialidades (áreas) más consultadas
            $especialidadesFrecuentes = $citas->groupBy('specialty_id')
                ->map(function ($group) {
                    return [
                        'count' => $group->count(),
                        'name' => $group->first()->specialty->name ?? 'General',
                    ];
                })
                ->sortByDesc('count')
                ->take(5);

            return view('profile.edit', compact(
                'user',
                'citas',
                'proximaCita',
                'totalCitas',
                'atendidas',
                'ausentes',
                'canceladas',
                'programadas',
                'tasaAsistencia',
                'medicosFrecuentes',
                'especialidadesFrecuentes'
            ));
        }

        return view('profile.edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
