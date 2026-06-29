@extends('layouts.app')

@section('content')

    <div class="top-bar">
        <div>
            <h1 class="page-title">Editar Paciente</h1>
            <p class="page-subtitle">Modifica la información personal del paciente o actualiza su contraseña.</p>
        </div>
    </div>

    <div class="card" style="max-width: 600px;">
        <form action="{{ route('pacientes.update', $paciente->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Nombre Completo</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $paciente->name) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">RUT</label>
                <input type="text" name="rut" class="form-control" value="{{ old('rut', $paciente->rut) }}" required placeholder="12345678-9" maxlength="12">
            </div>

            <div class="form-group">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $paciente->email) }}" required>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 30px 0;">
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 20px; display: flex; align-items: center; gap: 5px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                Deja la contraseña en blanco si no deseas cambiarla.
            </p>

            <div class="form-group">
                <label class="form-label">Nueva Contraseña (Opcional)</label>
                <input type="password" name="password" class="form-control" minlength="8">
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label class="form-label">Confirmar Nueva Contraseña</label>
                <input type="password" name="password_confirmation" class="form-control" minlength="8">
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Actualizar Paciente</button>
                <a href="{{ route('pacientes.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>

@endsection
