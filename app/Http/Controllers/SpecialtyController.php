<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Specialty;
use App\Http\Requests\StoreSpecialtyRequest; // Inyectamos tu escudo
use Illuminate\Http\RedirectResponse;

class SpecialtyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // 1. Buscamos todas las especialidades en Supabase
        $especialidades = Specialty::all();
        
        // 2. Se las enviamos a la vista de Cristóbal (que él creará en resources/views/especialidades/index.blade.php)
        return view('especialidades.index', compact('especialidades'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Solo devuelve el HTML del formulario, no interactúa con la base de datos
        return view('especialidades.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSpecialtyRequest $request): RedirectResponse
    {
        // Si el código llega a esta línea, es porque la validación del Request pasó exitosamente.
        // Los datos ya están limpios y seguros.
        
        $datosValidados = $request->validated();
        
        // Guardamos en Supabase
        Specialty::create($datosValidados);

        // Redireccionamos a la lista con un mensaje de éxito
        return redirect()->route('especialidades.index')->with('success', 'Especialidad creada con éxito.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return redirect()->route('especialidades.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $especialidad = Specialty::findOrFail($id);

        return view('especialidades.edit', compact('especialidad'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $especialidad = Specialty::findOrFail($id);

        $datosValidados = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:specialties,name,' . $especialidad->id],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        $especialidad->update($datosValidados);

        return redirect()->route('especialidades.index')->with('success', 'Especialidad actualizada con éxito.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $especialidad = Specialty::findOrFail($id);
        $especialidad->delete();

        return redirect()->route('especialidades.index')->with('success', 'Especialidad eliminada con éxito.');
    }
}
