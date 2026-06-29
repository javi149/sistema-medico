<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\ProfessionalProfileController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DoctorDashboardController; // <-- Agregado aquí arriba
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function (Request $request) {
    $user = $request->user();

    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    }

    if ($user->hasRole('medico')) {
        return redirect()->route('medico.dashboard');
    }

    if ($user->hasRole('paciente')) {
        return redirect()->route('citas.index');
    }

    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de Agendamiento de Citas (Notificación Email)
    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::patch('/appointments/{id}', [AppointmentController::class, 'update'])->name('appointments.update');
});

require __DIR__.'/auth.php';

// ==========================================================
// 2. UNIFICAMOS TODAS LAS RUTAS DEL ADMINISTRADOR AQUÍ
// ==========================================================
Route::middleware(['auth', 'role:admin'])->group(function () {
    
    // Panel de control diario (Dashboard)
    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/admin/usuarios/medico/crear', [AdminUserController::class, 'createMedico'])->name('admin.users.create-medico');
    Route::post('/admin/usuarios/medico', [AdminUserController::class, 'storeMedico'])->name('admin.users.store-medico');
    
    // CRUD de Especialidades Médicas
    Route::resource('especialidades', SpecialtyController::class);
    
    // CRUD de Perfiles Médicos
    Route::resource('perfiles', ProfessionalProfileController::class);

    // CRUD de Pacientes
    Route::resource('pacientes', PacienteController::class);

    // Reportes de Gestión (Tu módulo)
    Route::get('/admin/reportes', [ReportController::class, 'index'])->name('reportes.index');

});

// ==========================================================
// 3. RUTAS DEL PACIENTE
// ==========================================================
Route::middleware(['auth', 'role:paciente'])->group(function () {
    
    Route::resource('citas', AppointmentController::class)->only(['create', 'store', 'index']);
    
    // LA NUEVA RUTA PARA CANCELAR
    Route::patch('/citas/{cita}/cancelar', [AppointmentController::class, 'cancelar'])->name('citas.cancelar');

});

// ==========================================================
// 4. RUTAS DEL MÉDICO
// ==========================================================
Route::middleware(['auth', 'role:medico'])->group(function () {
    
    // Panel de control diario del Doctor
    Route::get('/medico/dashboard', [DoctorDashboardController::class, 'index'])->name('medico.dashboard');

});