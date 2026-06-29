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

    @if(isset($waitlistOffers) && !$waitlistOffers->isEmpty())
        <div style="margin-bottom: 30px; background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); border: 1px solid #7dd3fc; border-radius: 20px; padding: 25px; box-shadow: var(--shadow-md);">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 15px;">
                <div style="background: var(--primary); color: white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="icon-sm" style="color: white;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <h3 style="margin: 0; color: #0369a1; font-weight: 800; font-size: 1.2rem;">¡Cupo Disponible en Lista de Espera!</h3>
            </div>
            <p style="color: #0c4a6e; margin: 0 0 20px 0; font-size: 0.95rem; font-weight: 500;">
                Se ha liberado una hora médica que coincide con tu solicitud. Por favor, confirma si deseas tomarla:
            </p>
            
            <div style="display: flex; flex-direction: column; gap: 15px;">
                @foreach($waitlistOffers as $offer)
                    <div style="background: white; border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; border: 1px solid #cbd5e1;">
                        <div>
                            <div style="font-weight: 700; color: var(--text-main); font-size: 1.05rem;">
                                Dr(a). {{ $offer->professionalProfile->user->name ?? 'Médico Asignado' }}
                            </div>
                            <div style="color: var(--text-muted); font-size: 0.9rem; margin-top: 4px;">
                                Especialidad: <strong style="color: var(--primary);">{{ $offer->specialty->name }}</strong>
                            </div>
                            <div style="color: #0369a1; font-weight: 700; font-size: 0.95rem; margin-top: 6px; display: flex; align-items: center; gap: 6px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                {{ \Carbon\Carbon::parse($offer->offered_datetime)->format('d/m/Y - H:i') }} hrs
                            </div>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <form action="{{ route('waitlist.decline', $offer->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas rechazar este cupo? Se cancelará tu solicitud en lista de espera.');" style="margin: 0;">
                                @csrf
                                <button type="submit" class="btn btn-outline" style="padding: 10px 20px; border-color: var(--status-danger); color: var(--status-danger); background: white;">
                                    Rechazar Hora
                                </button>
                            </form>
                            <form action="{{ route('waitlist.accept', $offer->id) }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="btn btn-primary" style="padding: 10px 24px;">
                                    Aceptar Hora
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

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
                                @if(in_array(strtolower($cita->status), ['reservada', 'confirmada', 'modificada']))
                                    <div style="display: flex; gap: 8px;">
                                        <a href="{{ route('citas.edit', $cita->id) }}" class="btn btn-outline" style="padding: 5px 10px; font-size: 0.8rem; color: var(--primary); border-color: var(--primary);">
                                            Modificar
                                        </a>
                                        <form action="{{ route('citas.cancelar', $cita->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas cancelar esta cita?');" style="margin: 0;">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline" style="padding: 5px 10px; font-size: 0.8rem; color: var(--status-danger); border-color: var(--status-danger);">
                                                Cancelar
                                            </button>
                                        </form>
                                    </div>
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
