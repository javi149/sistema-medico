@extends('layouts.app')

@section('content')

    <div class="top-bar">
        <div>
            <h1 class="page-title">Editar Especialidad</h1>
            <p class="page-subtitle">Modifica los datos de la especialidad médica.</p>
        </div>
    </div>

    <div class="card" style="max-width: 600px;">
        <form action="{{ route('especialidades.update', $especialidad) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Nombre</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $especialidad->name) }}" required>
                @error('name')
                    <small style="color: var(--danger, #e74c3c); margin-top: 4px; display: block;">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label class="form-label">Descripción</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description', $especialidad->description) }}</textarea>
                @error('description')
                    <small style="color: var(--danger, #e74c3c); margin-top: 4px; display: block;">{{ $message }}</small>
                @enderror
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Actualizar Especialidad</button>
                <a href="{{ route('especialidades.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>

@endsection
