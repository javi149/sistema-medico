@extends('layouts.app')

@section('content')

    <div class="top-bar">
        <div>
            <h1 class="page-title">Mantente al día, Dr(a). {{ explode(' ', Auth::user()->name)[0] }}</h1>
            <p class="page-subtitle">Gestión de pacientes y rondas diarias: <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F, Y') }}</strong></p>
        </div>
        <div style="display: flex; gap: 10px;">
            <button class="btn btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Agregar Evento
            </button>
        </div>
    </div>

    <!-- CALENDAR LIKE VIEW -->
    <div style="background: var(--bg-card); border-radius: 20px; border: 1px solid var(--border-color); padding: 30px; box-shadow: var(--shadow-sm);">
        
        <h3 style="font-weight: 700; font-size: 1.25rem; margin-top: 0; margin-bottom: 25px; color: var(--text-main);">Fila de Pacientes (Hoy)</h3>
        
        @if($misPacientesHoy->isEmpty())
            <div style="text-align: center; padding: 50px 20px;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 64px; height: 64px; color: var(--border-color); margin-bottom: 15px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <p style="color: var(--text-muted); font-weight: 500; font-size: 1.1rem;">No tienes pacientes agendados para la jornada de hoy.</p>
                <p style="color: #94a3b8; font-size: 0.9rem;">Disfruta tu tiempo libre o revisa historiales clínicos pendientes.</p>
            </div>
        @else
            <!-- Contenedor estilo Grid/Cards en vez de tabla tradicional -->
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                @foreach($misPacientesHoy as $cita)
                    @php
                        // Distribuir colores aleatorios suaves (como en la imagen 2) basados en el id de la cita para mantener consistencia
                        $colors = [
                            ['bg' => '#fef08a', 'border' => '#fde047', 'text' => '#854d0e', 'icon' => '#ca8a04'], // Amarillo
                            ['bg' => '#bfdbfe', 'border' => '#93c5fd', 'text' => '#1e40af', 'icon' => '#3b82f6'], // Azul
                            ['bg' => '#fbcfe8', 'border' => '#f9a8d4', 'text' => '#9d174d', 'icon' => '#ec4899'], // Rosa
                            ['bg' => '#e9d5ff', 'border' => '#d8b4fe', 'text' => '#581c87', 'icon' => '#a855f7'], // Morado
                            ['bg' => '#dcfce7', 'border' => '#bbf7d0', 'text' => '#166534', 'icon' => '#22c55e'], // Verde
                        ];
                        $color = $colors[$cita->id % count($colors)];
                    @endphp
                    
                    <div style="background-color: {{ $color['bg'] }}; border: 1px solid {{ $color['border'] }}; border-radius: 16px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: transform 0.2s; cursor: pointer;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                        
                        <!-- Icono Superior -->
                        <div style="width: 32px; height: 32px; border-radius: 50%; background: white; display: flex; align-items: center; justify-content: center; color: {{ $color['icon'] }}; margin-bottom: 15px; box-shadow: var(--shadow-sm);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                        </div>
                        
                        <!-- Info Principal -->
                        <div>
                            <h4 style="margin: 0 0 5px 0; color: {{ $color['text'] }}; font-size: 1.1rem; font-weight: 700;">{{ $cita->specialty->name }}</h4>
                            <div style="color: {{ $color['text'] }}; opacity: 0.8; font-size: 0.85rem; margin-bottom: 15px; display: flex; align-items: center; gap: 5px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                {{ \Carbon\Carbon::parse($cita->start_datetime)->format('H:i') }} - {{ \Carbon\Carbon::parse($cita->start_datetime)->addMinutes($cita->professionalProfile->consultation_duration_minutes ?? 30)->format('H:i A') }}
                            </div>
                        </div>
                        
                        <!-- Paciente Info -->
                        <div style="background: rgba(255,255,255,0.4); padding: 12px; border-radius: 10px; margin-bottom: 15px;">
                            <div style="font-weight: 600; color: {{ $color['text'] }}; font-size: 0.95rem;">{{ $cita->patient->name }}</div>
                            <div style="font-size: 0.75rem; color: {{ $color['text'] }}; opacity: 0.7;">RUT: {{ $cita->patient->rut ?? 'N/A' }}</div>
                        </div>

                        <!-- Botón Acción -->
                        <button style="background-color: {{ $color['text'] }}; color: white; border: none; padding: 10px; border-radius: 100px; font-weight: 600; font-size: 0.85rem; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 5px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                            Iniciar Consulta
                        </button>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

@endsection