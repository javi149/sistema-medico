@extends('layouts.app')

@section('content')

    <div class="top-bar">
        <div>
            <h1 class="page-title">Registrar Nuevo Médico</h1>
            <p class="page-subtitle">Crea un nuevo usuario en el sistema y asígnale el rol de médico.</p>
        </div>
    </div>

    <div class="card" style="max-width: 600px;">
        <form action="{{ route('admin.users.store-medico') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Nombre Completo</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">RUT</label>
                <input type="text" name="rut" class="form-control" value="{{ old('rut') }}" required placeholder="Ej: 12345678-9">
            </div>

            <div class="form-group">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control" required minlength="8">
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label class="form-label">Confirmar Contraseña</label>
                <input type="password" name="password_confirmation" class="form-control" required minlength="8">
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Registrar Usuario Médico</button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>

@endsection
