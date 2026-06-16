@extends('layouts.app')

@section('content')

    <div class="top-bar">
        <div>
            <h1 class="page-title">Asignar Perfil Médico</h1>
            <p class="page-subtitle">Asigna un perfil médico a un usuario y vincula sus especialidades.</p>
        </div>
    </div>

    <div class="card" style="max-width: 700px;">
        <form action="{{ route('perfiles.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label">Seleccione el Usuario Médico</label>
                <select name="user_id" class="form-control" required>
                    <option value="">-- Seleccionar --</option>
                    @foreach($medicos as $medico)
                        <option value="{{ $medico->id }}" {{ old('user_id') == $medico->id ? 'selected' : '' }}>
                            {{ $medico->name }} ({{ $medico->email }})
                        </option>
                    @endforeach
                </select>
                @if($medicos->isEmpty())
                    <p style="color: var(--status-warning); font-size: 0.85rem; margin-top: 5px;">⚠️ No hay usuarios con rol "medico" sin perfil asignado.</p>
                @endif
            </div>

            <div class="form-group">
                <label class="form-label">Duración estándar de consulta (minutos)</label>
                <input type="number" name="consultation_duration_minutes" class="form-control" value="{{ old('consultation_duration_minutes', 30) }}" min="10" step="5" required>
            </div>

            <div class="form-group">
                <label class="form-label">Biografía o Detalles</label>
                <textarea name="bio" class="form-control" rows="4">{{ old('bio') }}</textarea>
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label class="form-label">Especialidades</label>
                <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 0; margin-bottom: 10px;">Mantenga presionado Ctrl (Windows) o Command (Mac) para seleccionar múltiples opciones.</p>
                <select name="specialties[]" class="form-control" multiple required style="height: 150px;">
                    @foreach($especialidades as $esp)
                        <option value="{{ $esp->id }}" {{ (collect(old('specialties'))->contains($esp->id)) ? 'selected':'' }}>
                            {{ $esp->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Asignar Perfil</button>
                <a href="{{ route('perfiles.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>

@endsection
