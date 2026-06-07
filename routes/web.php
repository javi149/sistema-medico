<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SpecialtyController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

use App\Models\User;

// Ruta temporal de desarrollo para verificar el árbol de relaciones de Eloquent
Route::get('/test-relaciones', function () {
    // 1. Buscamos al usuario administrador que ya tiene perfil
    $medico = User::where('email', 'admin@clinica.cl')->first();
    
    // 2. Retornamos una respuesta estructurada con sus especialidades vinculadas
    return response()->json([
        'medico' => $medico->name,
        'perfil' => $medico->professionalProfile,
        'especialidades' => $medico->professionalProfile->specialties
    ]);
});

// Rutas de Administración Médica (Protegidas)
Route::middleware(['auth', 'role:admin'])->group(function () {
    
    // El método 'resource' crea automáticamente las 7 rutas del CRUD
    Route::resource('especialidades', SpecialtyController::class);

});