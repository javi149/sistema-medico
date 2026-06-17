@extends('layouts.app')

@section('content')

    <div class="top-bar">
        <div>
            <h1 class="page-title">Mantenedor de Pacientes</h1>
            <p class="page-subtitle">Catálogo de pacientes registrados en el sistema de la clínica.</p>
        </div>
        <a href="{{ route('pacientes.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Nuevo Paciente
        </a>
    </div>

    <div class="table-container">
        @if($pacientes->isEmpty())
            <div style="padding: 40px; text-align: center;">
                <p style="color: var(--text-muted);">No hay pacientes registrados en el sistema.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Nombre Completo</th>
                        <th>RUT</th>
                        <th>Correo Electrónico</th>
                        <th>Fecha de Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pacientes as $paciente)
                        <tr>
                            <td style="font-weight: 600; color: var(--text-main);">{{ $paciente->name }}</td>
                            <td style="color: var(--text-muted);">{{ $paciente->rut }}</td>
                            <td style="color: var(--text-muted);">{{ $paciente->email }}</td>
                            <td style="color: var(--text-muted);">{{ $paciente->created_at->format('d/m/Y') }}</td>
                            <td>
                                <div style="display: flex; gap: 15px;">
                                    <a href="{{ route('pacientes.edit', $paciente->id) }}" style="color: var(--status-info); text-decoration: none; display: flex; align-items: center; gap: 5px; font-weight: 500;">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        Editar
                                    </a>
                                    
                                    <form action="{{ route('pacientes.destroy', $paciente->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar a este paciente?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background: none; border: none; color: var(--status-danger); cursor: pointer; font-size: 1rem; padding: 0; display: flex; align-items: center; gap: 5px; font-weight: 500; font-family: inherit;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

@endsection
