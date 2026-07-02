@extends('layouts.app')

@section('content')

@section('header_context')
    <div>
        <h1 class="page-title">Control Diario de Citas</h1>
        <p class="page-subtitle">Gestión y supervisión de la agenda clínica.</p>
    </div>
    <div style="display: flex; align-items: center; gap: 15px;">
        <form action="{{ route('admin.dashboard') }}" method="GET" style="display: flex; align-items: center; gap: 10px; margin: 0;">
            <label for="date-select" style="font-size: 0.85rem; font-weight: 700; color: rgba(255,255,255,0.7); text-transform: uppercase;">Agenda del día:</label>
            <input type="date" id="date-select" name="date" value="{{ $hoy->format('Y-m-d') }}" onchange="this.form.submit()" style="padding: 8px 12px; border-radius: 50px; border: 1px solid rgba(255,255,255,0.2); font-family: inherit; font-size: 0.9rem; outline: none; background: rgba(255,255,255,0.1); color: white; font-weight: 600; cursor: pointer; transition: all 0.2s;" onfocus="this.style.borderColor='white'" onblur="this.style.borderColor='rgba(255,255,255,0.2)'">
        </form>
        <a href="{{ route('admin.appointments.create') }}" class="btn btn-primary" style="padding: 8px 18px; font-size: 0.85rem; background: rgba(255,255,255,0.2); box-shadow: none;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Agendar Hora
        </a>
    </div>
@endsection

    <!-- STATS CARDS -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px;">
        
        <div class="card" style="border-top: 4px solid var(--primary-light);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">Total Citas</span>
                    <h2 style="margin: 10px 0 0 0; font-size: 2.5rem; font-weight: 800; color: var(--text-main);">{{ $totalCitas }}</h2>
                </div>
                <div style="background: rgba(20, 184, 166, 0.1); padding: 10px; border-radius: 12px; color: var(--primary-light);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-lg"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                </div>
            </div>
        </div>

        <div class="card" style="border-top: 4px solid var(--status-success);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">Confirmadas</span>
                    <h2 style="margin: 10px 0 0 0; font-size: 2.5rem; font-weight: 800; color: var(--text-main);">{{ $confirmadas }}</h2>
                </div>
                <div style="background: rgba(16, 185, 129, 0.1); padding: 10px; border-radius: 12px; color: var(--status-success);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-lg"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
            </div>
        </div>

        <div class="card" style="border-top: 4px solid var(--status-info);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">Atendidas</span>
                    <h2 style="margin: 10px 0 0 0; font-size: 2.5rem; font-weight: 800; color: var(--text-main);">{{ $atendidas }}</h2>
                </div>
                <div style="background: rgba(59, 130, 246, 0.1); padding: 10px; border-radius: 12px; color: var(--status-info);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-lg"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
            </div>
        </div>

        <div class="card" style="border-top: 4px solid var(--status-danger);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <span style="color: var(--text-muted); font-size: 0.85rem; font-weight: 700; text-transform: uppercase;">Ausentes</span>
                    <h2 style="margin: 10px 0 0 0; font-size: 2.5rem; font-weight: 800; color: var(--text-main);">{{ $ausentes }}</h2>
                </div>
                <div style="background: rgba(239, 68, 68, 0.1); padding: 10px; border-radius: 12px; color: var(--status-danger);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-lg"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                </div>
            </div>
        </div>

    </div>

    <!-- TABLE -->
    <h3 style="font-weight: 700; font-size: 1.25rem; margin-bottom: 15px; color: var(--text-main);">Agenda del Día</h3>
    
    <div class="table-container">
        @if($agenda->isEmpty())
            <div style="padding: 40px; text-align: center;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 48px; height: 48px; color: var(--border-color); margin-bottom: 15px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <p style="color: var(--text-muted); font-weight: 500;">No se registran movimientos ni citas médicas para la jornada de hoy.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Hora</th>
                        <th>Paciente</th>
                        <th>Médico Asignado</th>
                        <th>Especialidad</th>
                        <th>Estado</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($agenda as $cita)
                        <tr>
                            <td style="font-weight: 700; color: var(--primary);">{{ \Carbon\Carbon::parse($cita->start_datetime)->format('H:i A') }}</td>
                            <td style="font-weight: 500;">{{ $cita->patient->name }}</td>
                            <td>Dr(a). {{ $cita->professionalProfile->user->name }}</td>
                            <td style="color: var(--text-muted);">{{ $cita->specialty->name }}</td>
                            <td>
                                @php
                                    $badgeClass = 'badge-neutral';
                                    $statusLower = strtolower($cita->status);
                                    if(in_array($statusLower, ['reservada', 'confirmada', 'modificada'])) $badgeClass = 'badge-success';
                                    if($statusLower == 'cancelada') $badgeClass = 'badge-danger';
                                    if($statusLower == 'ausente') $badgeClass = 'badge-warning';
                                    if($statusLower == 'atendida') $badgeClass = 'badge-info';
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ ucfirst($cita->status) }}
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px; justify-content: flex-end; align-items: center;">
                                    @if(in_array(strtolower($cita->status), ['reservada', 'confirmada', 'modificada']))
                                        <a href="{{ route('admin.appointments.edit', $cita->id) }}" class="btn btn-outline" style="padding: 5px 10px; font-size: 0.8rem; color: var(--primary); border-color: var(--primary);">
                                            Reagendar
                                        </a>
                                        <form action="{{ route('appointments.cancel', $cita->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas cancelar esta cita?');" style="margin: 0;">
                                            @csrf
                                            <button type="submit" class="btn btn-outline" style="padding: 5px 10px; font-size: 0.8rem; color: var(--status-warning); border-color: var(--status-warning);">
                                                Cancelar
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.appointments.destroy', $cita->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar permanentemente esta cita? Esta acción no se puede deshacer.');" style="margin: 0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline" style="padding: 5px 10px; font-size: 0.8rem; color: var(--status-danger); border-color: var(--status-danger);">
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