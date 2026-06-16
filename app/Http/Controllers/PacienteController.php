<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class PacienteController extends Controller
{
    /**
     * Mostrar una lista de pacientes.
     */
    public function index()
    {
        // Obtenemos solo los usuarios que tienen el rol de "paciente"
        $pacientes = User::role('paciente')->orderBy('created_at', 'desc')->get();
        return view('pacientes.index', compact('pacientes'));
    }

    /**
     * Mostrar el formulario para crear un nuevo paciente.
     */
    public function create()
    {
        return view('pacientes.create');
    }

    /**
     * Guardar un paciente recién creado en la base de datos.
     */
    public function store(Request $peticion)
    {
        $datosValidados = $peticion->validate([
            'name' => ['required', 'string', 'max:255'],
            'rut' => ['required', 'string', 'max:12', 'unique:users,rut'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $paciente = User::create([
            'name' => $datosValidados['name'],
            'rut' => $datosValidados['rut'],
            'email' => $datosValidados['email'],
            'password' => Hash::make($datosValidados['password']),
        ]);

        // Asignar el rol de paciente
        $rolPaciente = Role::where('name', 'paciente')->first();
        if ($rolPaciente) {
            $paciente->assignRole($rolPaciente);
        }

        return redirect()->route('pacientes.index')->with('success', 'Paciente creado exitosamente.');
    }

    /**
     * Mostrar el formulario para editar el paciente especificado.
     */
    public function edit(User $paciente)
    {
        return view('pacientes.edit', compact('paciente'));
    }

    /**
     * Actualizar el paciente especificado en la base de datos.
     */
    public function update(Request $peticion, User $paciente)
    {
        $reglas = [
            'name' => ['required', 'string', 'max:255'],
            'rut' => ['required', 'string', 'max:12', Rule::unique('users')->ignore($paciente->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($paciente->id)],
        ];

        // Solo validar la contraseña si el usuario escribió algo
        if ($peticion->filled('password')) {
            $reglas['password'] = ['string', 'min:8', 'confirmed'];
        }

        $datosValidados = $peticion->validate($reglas);

        $paciente->name = $datosValidados['name'];
        $paciente->rut = $datosValidados['rut'];
        $paciente->email = $datosValidados['email'];

        // Si se envió una contraseña nueva, la encriptamos y guardamos
        if ($peticion->filled('password')) {
            $paciente->password = Hash::make($datosValidados['password']);
        }

        $paciente->save();

        return redirect()->route('pacientes.index')->with('success', 'Paciente actualizado exitosamente.');
    }

    /**
     * Eliminar el paciente especificado de la base de datos.
     */
    public function destroy(User $paciente)
    {
        $paciente->delete();
        return redirect()->route('pacientes.index')->with('success', 'Paciente eliminado exitosamente.');
    }
}
