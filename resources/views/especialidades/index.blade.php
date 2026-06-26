@extends('layouts.app')

@section('content')

    <div class="top-bar">
        <div>
            <h1 class="page-title">Especialidades Médicas</h1>
            <p class="page-subtitle">Catálogo de especialidades disponibles en la clínica.</p>
        </div>
        <a href="{{ route('especialidades.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Nueva Especialidad
        </a>
    </div>

    <div class="table-container">
        @if($especialidades->isEmpty())
            <div style="padding: 40px; text-align: center;">
                <p style="color: var(--text-muted);">No hay especialidades registradas. Crea la primera.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($especialidades as $especialidad)
                        <tr>
                            <td style="font-weight: 600; color: var(--primary);">{{ $especialidad->name }}</td>
                            <td style="color: var(--text-muted);">{{ $especialidad->description ?? 'Sin descripción' }}</td>
                            <td>
                                <a href="{{ route('especialidades.edit', $especialidad) }}" class="btn btn-outline" style="padding: 6px 12px; font-size: 0.85rem;">Editar</a>
                                <form action="{{ route('especialidades.destroy', $especialidad) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar esta especialidad?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 0.85rem;">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

@endsection
