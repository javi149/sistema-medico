<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SpecialtyController;
use App\Http\Controllers\ProfessionalProfileController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DoctorDashboardController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

Route::get('/', function () {
    $specialties = \App\Models\Specialty::limit(8)->get();
    
    // Obtenemos 12 médicos aleatorios para no sobrecargar la página (80 son muchos)
    $doctors = \App\Models\User::has('professionalProfile')
        ->with('professionalProfile.specialties')
        ->inRandomOrder()
        ->limit(12)
        ->get();
    
    return view('welcome', compact('specialties', 'doctors'));
});

Route::get('/dashboard', function (Request $request) {
    $user = $request->user();
Route::get('/dashboard', function () {
    $user = Auth::user();

    if (!$user) {
        return redirect()->route('login');
    }

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
});

require __DIR__.'/auth.php';

// ==========================================================
// 2. RUTAS DEL ADMINISTRADOR
// ==========================================================
Route::middleware(['auth', 'role:admin'])->group(function () {

    Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/admin/usuarios/medico/crear', [AdminUserController::class, 'createMedico'])->name('admin.users.create-medico');
    Route::post('/admin/usuarios/medico', [AdminUserController::class, 'storeMedico'])->name('admin.users.store-medico');

    Route::resource('especialidades', SpecialtyController::class);
    Route::resource('perfiles', ProfessionalProfileController::class);
    Route::resource('pacientes', PacienteController::class);

    Route::get('/admin/reportes', [ReportController::class, 'index'])->name('reportes.index');

    // Rutas de gestión de citas desde panel admin (con notificación por email)
    Route::post('/appointments/{id}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');
    Route::patch('/appointments/{id}', [AppointmentController::class, 'update'])->name('appointments.update');
    // Gestión de Citas Médicas por el Administrador
    Route::get('/admin/appointments/create', [AppointmentController::class, 'adminCreate'])->name('admin.appointments.create');
    Route::post('/admin/appointments', [AppointmentController::class, 'adminStore'])->name('admin.appointments.store');
    Route::get('/admin/appointments/{appointment}/edit', [AppointmentController::class, 'adminEdit'])->name('admin.appointments.edit');
    Route::patch('/admin/appointments/{appointment}', [AppointmentController::class, 'adminUpdate'])->name('admin.appointments.update');
    Route::delete('/admin/appointments/{appointment}', [AppointmentController::class, 'adminDestroy'])->name('admin.appointments.destroy');

    // Gestión de Lista de Espera por el Administrador
    Route::get('/admin/waitlist', [\App\Http\Controllers\AdminWaitlistController::class, 'index'])->name('admin.waitlist.index');
    Route::post('/admin/waitlist/{waitlist}/approve', [\App\Http\Controllers\AdminWaitlistController::class, 'approve'])->name('admin.waitlist.approve');

});

// ==========================================================
// RUTAS PÚBLICAS DE RESERVAS (WIZARD)
// ==========================================================
Route::get('/citas/create', [AppointmentController::class, 'create'])->name('citas.create');
Route::post('/citas', [AppointmentController::class, 'store'])->name('citas.store');
Route::get('/citas/{cita}/success', [AppointmentController::class, 'success'])->name('citas.success');
Route::post('/citas/wizard/check-rut', [AppointmentController::class, 'checkRut'])->name('wizard.check-rut');
Route::get('/citas/wizard/availability', [AppointmentController::class, 'getAvailability'])->name('wizard.availability');
Route::post('/waitlist', [\App\Http\Controllers\WaitlistController::class, 'store'])->name('waitlist.store');

// ==========================================================
// 3. RUTAS DEL PACIENTE
// ==========================================================
Route::middleware(['auth', 'role:paciente'])->group(function () {

    Route::resource('citas', AppointmentController::class)->only(['create', 'store', 'index']);

    Route::patch('/citas/{cita}/cancelar', [AppointmentController::class, 'cancelar'])->name('citas.cancelar');
    
    Route::get('/citas', [AppointmentController::class, 'index'])->name('citas.index');
    
    // LA NUEVA RUTA PARA CANCELAR
    Route::patch('/citas/{cita}/cancelar', [AppointmentController::class, 'cancelar'])->name('citas.cancelar');

    // RUTAS PARA MODIFICAR
    Route::get('/citas/{cita}/edit', [AppointmentController::class, 'edit'])->name('citas.edit');
    Route::patch('/citas/{cita}', [AppointmentController::class, 'updatePaciente'])->name('citas.update');

    // RUTAS DE LISTA DE ESPERA (ACEPTAR / RECHAZAR)
    Route::post('/waitlist/{waitlist}/accept', [\App\Http\Controllers\WaitlistController::class, 'accept'])->name('waitlist.accept');
    Route::post('/waitlist/{waitlist}/decline', [\App\Http\Controllers\WaitlistController::class, 'decline'])->name('waitlist.decline');

});

// ==========================================================
// 4. RUTAS DEL MÉDICO
// ==========================================================
Route::middleware(['auth', 'role:medico'])->group(function () {

    Route::get('/medico/dashboard', [DoctorDashboardController::class, 'index'])->name('medico.dashboard');
});