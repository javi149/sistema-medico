@extends('layouts.app')

@section('content')

    <div class="top-bar">
        <div>
            <h1 class="page-title">Historial de Citas Médicas</h1>
            <p class="page-subtitle">Revisa tus citas programadas y tu historial de atenciones.</p>
        </div>
        <a href="{{ route('citas.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Agendar Nueva Hora
        </a>
    </div>

    <div class="table-container">
        @if($misCitas->isEmpty())
            <div style="padding: 40px; text-align: center;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 48px; height: 48px; color: var(--border-color); margin-bottom: 15px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <p style="color: var(--text-muted); font-weight: 500;">No tienes citas médicas registradas en tu historial.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Médico</th>
                        <th>Especialidad</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($misCitas as $cita)
                        <tr>
                            <td style="font-weight: 600; color: var(--text-main);">
                                {{ \Carbon\Carbon::parse($cita->start_datetime)->format('d M Y - H:i A') }}
                            </td>
                            <td>Dr(a). {{ $cita->professionalProfile->user->name }}</td>
                            <td style="color: var(--text-muted);">{{ $cita->specialty->name }}</td>
                            <td>
                                @php
                                    $badgeClass = 'badge-neutral';
                                    if(in_array($cita->status, ['reservada', 'confirmada'])) $badgeClass = 'badge-success';
                                    if($cita->status == 'cancelada') $badgeClass = 'badge-danger';
                                    if($cita->status == 'ausente') $badgeClass = 'badge-warning';
                                    if($cita->status == 'atendida') $badgeClass = 'badge-info';
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ ucfirst($cita->status) }}
                                </span>
                            </td>
                            <td>
                                @if(in_array($cita->status, ['reservada', 'confirmada']))
                                    <form action="{{ route('citas.cancelar', $cita->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas cancelar esta cita?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline" style="padding: 5px 10px; font-size: 0.8rem; color: var(--status-danger); border-color: var(--status-danger);">
                                            Cancelar Cita
                                        </button>
                                    </form>
                                @else
                                    <span style="color: var(--text-muted); font-size: 0.85rem;">No disponible</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

@endsection
