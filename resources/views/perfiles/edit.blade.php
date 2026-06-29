@extends('layouts.app')

@section('content')

    <div class="top-bar">
        <div>
            <h1 class="page-title">Editar Perfil Médico</h1>
            <p class="page-subtitle">Modifique los datos del perfil profesional del médico.</p>
        </div>
    </div>

    <div class="card" style="max-width: 700px;">
        <form action="{{ route('perfiles.update', $perfil) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Doctor(a)</label>
                <input type="text" class="form-control" value="{{ $perfil->user->name }} ({{ $perfil->user->email }})" disabled>
            </div>

            <div class="form-group">
                <label class="form-label">Duración estándar de consulta (minutos)</label>
                <input type="number" name="consultation_duration_minutes" class="form-control" value="{{ old('consultation_duration_minutes', $perfil->consultation_duration_minutes) }}" min="15" max="60" step="5" required>
                @error('consultation_duration_minutes')
                    <p style="color: var(--status-danger); font-size: 0.85rem; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Biografía o Detalles</label>
                <textarea name="bio" class="form-control" rows="4">{{ old('bio', $perfil->bio) }}</textarea>
                @error('bio')
                    <p style="color: var(--status-danger); font-size: 0.85rem; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label class="form-label">Especialidades</label>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0; margin-bottom: 10px;">Mantenga presionado Ctrl (Windows) o Command (Mac) para seleccionar múltiples opciones.</p>
                <select name="specialties[]" class="form-control" multiple required style="height: 150px;">
                    @foreach($especialidades as $esp)
                        <option value="{{ $esp->id }}" {{ (collect(old('specialties', $perfil->specialties->pluck('id')->toArray()))->contains($esp->id)) ? 'selected' : '' }}>
                            {{ $esp->name }}
                        </option>
                    @endforeach
                </select>
                @error('specialties')
                    <p style="color: var(--status-danger); font-size: 0.85rem; margin-top: 5px;">{{ $message }}</p>
                @enderror
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Actualizar Perfil</button>
                <a href="{{ route('perfiles.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>

@endsection
