@extends('layouts.app')

@section('content')

    <div class="top-bar">
        <div>
            <h1 class="page-title">Nueva Especialidad</h1>
            <p class="page-subtitle">Registra una especialidad médica en el sistema.</p>
        </div>
    </div>

    <div class="card" style="max-width: 600px;">
        <form action="{{ route('especialidades.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Nombre</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label class="form-label">Descripción</label>
                <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Guardar Especialidad</button>
                <a href="{{ route('especialidades.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>

@endsection
