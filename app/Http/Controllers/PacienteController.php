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
     * 
     * [LÓGICA DE NEGOCIO PROFUNDA - INGENIERÍA DE SOFTWARE]
     * Este método implementa la creación segura de usuarios en el sistema.
     * Aborda 3 aspectos críticos de seguridad y consistencia: 
     * 1. Hashing de contraseñas (Nunca almacenar en texto plano).
     * 2. Integridad de identidad (RUT y Email únicos en el sistema).
     * 3. Autorización (Asignación automática del rol correcto vía Spatie).
     */
    public function store(Request $peticion)
    {
        // 1. VALIDACIÓN ESTRICTA DE ENTRADA
        // Se aplica la regla 'unique' directamente a la tabla users para garantizar
        // que no existan colisiones de identidad (dos pacientes con el mismo RUT o correo).
        $datosValidados = $peticion->validate([
            'name' => ['required', 'string', 'max:255'],
            'rut' => ['required', 'string', 'max:12', new \App\Rules\ValidRut, 'unique:users,rut'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // 2. CREACIÓN DEL MODELO CON CRIPTOGRAFÍA
        // El Hash::make utiliza el algoritmo Bcrypt con un factor de trabajo alto
        // para proteger las credenciales contra ataques de fuerza bruta o rainbow tables.
        $paciente = User::create([
            'name' => $datosValidados['name'],
            'rut' => $datosValidados['rut'],
            'email' => $datosValidados['email'],
            'password' => Hash::make($datosValidados['password']),
        ]);

        // 3. ASIGNACIÓN DE ROLES (RBAC - Role Based Access Control)
        // Automatizamos la asignación del rol 'paciente' para asegurar que este nuevo
        // usuario no tenga privilegios escalados (por defecto, tendrá los permisos mínimos).
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
            'rut' => ['required', 'string', 'max:12', new \App\Rules\ValidRut, Rule::unique('users')->ignore($paciente->id)],
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
