<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear los roles del sistema
        $roleAdmin = Role::create(['name' => 'Administrador']);
        $roleMedico = Role::create(['name' => 'Medico']);
        $rolePaciente = Role::create(['name' => 'Paciente']);

        // 2. Crear al usuario Administrador raíz (SuperAdmin)
        $admin = User::create([
            'name' => 'Admin Sistema',
            'rut' => '11111111-1',
            'email' => 'admin@clinica.cl',
            'password' => Hash::make('password123'), // Contraseña segura por defecto
        ]);

        // 3. Asignarle el rol de Administrador al usuario recién creado
        $admin->assignRole($roleAdmin);
    }
}