@extends('layouts.app')

@section('content')

    <div class="top-bar">
        <div>
            <h1 class="page-title">Nuevo Paciente</h1>
            <p class="page-subtitle">Registra un nuevo paciente en el sistema y asígnale sus credenciales de acceso.</p>
        </div>
    </div>

    <div class="card" style="max-width: 600px;">
        @if ($errors->any())
            <div style="background-color: #fee2e2; color: #b91c1c; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('pacientes.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Nombre Completo</label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                @error('name')<span style="color: red; font-size: 0.85rem;">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">RUT</label>
                <input type="text" name="rut" class="form-control @error('rut') is-invalid @enderror" value="{{ old('rut') }}" required placeholder="12345678-9" maxlength="12">
                @error('rut')<span style="color: red; font-size: 0.85rem;">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Correo Electrónico</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                @error('email')<span style="color: red; font-size: 0.85rem;">{{ $message }}</span>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Contraseña</label>
                <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required minlength="8">
                @error('password')<span style="color: red; font-size: 0.85rem;">{{ $message }}</span>@enderror
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label class="form-label">Confirmar Contraseña</label>
                <input type="password" name="password_confirmation" class="form-control" required minlength="8">
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Guardar Paciente</button>
                <a href="{{ route('pacientes.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>

@endsection
