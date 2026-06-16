<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMedicoUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class AdminUserController extends Controller
{
    public function createMedico(): View
    {
        return view('admin.users.create-medico');
    }

    public function storeMedico(StoreMedicoUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $medico = User::create([
            'name' => $validated['name'],
            'rut' => $validated['rut'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $medico->assignRole(Role::firstOrCreate(['name' => 'medico']));

        return redirect()
            ->route('perfiles.create')
            ->with('success', 'Usuario médico creado. Ahora puedes asignarle su perfil profesional.');
    }
}
