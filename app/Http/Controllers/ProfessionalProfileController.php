<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ProfessionalProfile;
use App\Http\Requests\StoreProfessionalProfileRequest;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Specialty;

class ProfessionalProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Traemos todos los perfiles con los datos del usuario y sus especialidades
        $perfiles = \App\Models\ProfessionalProfile::with(['user', 'specialties'])->get();
        
        return view('perfiles.index', compact('perfiles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $medicos = User::role('medico')->doesntHave('professionalProfile')->get();
        $especialidades = Specialty::all();

        return view('perfiles.create', compact('medicos', 'especialidades'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProfessionalProfileRequest $request)
    {
        // 1. Si el código llega a esta línea, significa que el FormRequest 
        // ya validó todo y los datos son 100% seguros. Obtenemos esos datos limpios.
        $validatedData = $request->validated();

        // 2. Creamos el perfil profesional en la base de datos
        $profile = ProfessionalProfile::create([
            'user_id' => $validatedData['user_id'],
            'bio' => $validatedData['bio'] ?? null,
            'consultation_duration_minutes' => $validatedData['consultation_duration_minutes'],
        ]);

        // 3. Magia de Laravel: Vinculamos las especialidades en la tabla intermedia (Pivot)
        $profile->specialties()->attach($validatedData['specialties']);

        // 4. Redirigimos al administrador con un mensaje de éxito
        return redirect()->route('perfiles.index')->with('success', 'Perfil profesional creado y especialidades asignadas con éxito.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('perfiles.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $perfil = ProfessionalProfile::with('specialties')->findOrFail($id);
        $especialidades = Specialty::all();

        return view('perfiles.edit', compact('perfil', 'especialidades'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'bio' => 'nullable|max:1000',
            'consultation_duration_minutes' => 'required|integer|min:15|max:60',
            'specialties' => 'required|array|min:1',
            'specialties.*' => 'exists:specialties,id',
        ]);

        $perfil = ProfessionalProfile::findOrFail($id);

        $perfil->update([
            'bio' => $validatedData['bio'] ?? null,
            'consultation_duration_minutes' => $validatedData['consultation_duration_minutes'],
        ]);

        $perfil->specialties()->sync($validatedData['specialties']);

        return redirect()->route('perfiles.index')->with('success', 'Perfil profesional actualizado con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $perfil = ProfessionalProfile::findOrFail($id);
        $user = $perfil->user;

        $perfil->specialties()->detach();
        $perfil->delete();
        
        if ($user) {
            $user->delete();
        }

        return redirect()->route('perfiles.index')->with('success', 'Perfil profesional y cuenta de usuario eliminados con éxito.');
    }
}
