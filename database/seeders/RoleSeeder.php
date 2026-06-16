<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        $roleMedico = Role::firstOrCreate(['name' => 'medico']);
        $rolePaciente = Role::firstOrCreate(['name' => 'paciente']);

        $admin = User::firstOrCreate(
            ['email' => 'admin@clinica.cl'],
            [
                'name' => 'Admin Sistema',
                'rut' => '11111111-1',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole($roleAdmin);
        }
    }
}
