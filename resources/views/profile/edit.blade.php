@extends('layouts.app')

@section('content')
<style>
    /* Estilos del Perfil y Dashboard */
    .profile-container {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .profile-header {
        background: linear-gradient(135deg, #0f766e 0%, #0d9488 100%);
        color: white;
        border-radius: 24px;
        padding: 35px;
        box-shadow: var(--shadow-lg);
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 25px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .profile-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        pointer-events: none;
    }

    .profile-header-info {
        display: flex;
        align-items: center;
        gap: 20px;
        z-index: 1;
    }

    .profile-avatar-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
        border: 3px solid rgba(255, 255, 255, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 800;
        color: white;
        text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .profile-meta {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .profile-name {
        font-size: 1.75rem;
        font-weight: 800;
        margin: 0;
        letter-spacing: -0.5px;
    }

    .profile-role {
        font-size: 0.9rem;
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 12px;
        border-radius: 100px;
        width: fit-content;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .profile-details-list {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
        z-index: 1;
    }

    .profile-detail-item {
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.1);
        padding: 12px 20px;
        border-radius: 14px;
        backdrop-filter: blur(5px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .profile-detail-item svg {
        width: 20px;
        height: 20px;
        opacity: 0.9;
    }

    /* Pestañas (Tabs) */
    .tabs-nav {
        display: flex;
        gap: 10px;
        border-bottom: 2px solid var(--border-color);
        padding-bottom: 2px;
        margin-bottom: 10px;
    }

    .tab-link {
        background: none;
        border: none;
        padding: 14px 24px;
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-muted);
        cursor: pointer;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .tab-link svg {
        width: 18px;
        height: 18px;
        transition: transform 0.2s;
    }

    .tab-link:hover {
        color: var(--primary);
    }

    .tab-link:hover svg {
        transform: translateY(-1px);
    }

    .tab-link.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }

    .tab-panel {
        display: none;
        animation: fadeIn 0.3s ease-in-out;
    }

    .tab-panel.active {
        display: block;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Dashboard Widgets */
    .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .widget-card {
        background: white;
        border-radius: 20px;
        padding: 24px;
        border: 1px solid var(--border-color);
        box-shadow: var(--shadow-sm);
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.3s ease;
    }

    .widget-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
        border-color: var(--primary-light);
    }

    .widget-info h4 {
        margin: 0;
        color: var(--text-muted);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
    }

    .widget-value {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-main);
        margin: 8px 0 0 0;
        line-height: 1.1;
    }

    .widget-icon {
        width: 50px;
        height: 50px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .widget-icon.primary { background: rgba(15, 118, 110, 0.1); color: var(--primary); }
    .widget-icon.success { background: rgba(16, 185, 129, 0.1); color: var(--status-success); }
    .widget-icon.danger { background: rgba(239, 68, 68, 0.1); color: var(--status-danger); }
    .widget-icon.info { background: rgba(59, 130, 246, 0.1); color: var(--status-info); }

    /* Layout de Dos Columnas */
    .dashboard-two-col {
        display: grid;
        grid-template-columns: 3fr 2fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    @media (max-width: 1024px) {
        .dashboard-two-col {
            grid-template-columns: 1fr;
        }
    }

    .section-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-main);
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title svg {
        width: 20px;
        height: 20px;
        color: var(--primary);
    }

    /* Gráfico Circular de Asistencia */
    .attendance-radial-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 20px;
        background: #f8fafc;
        border-radius: 16px;
        border: 1px solid var(--border-color);
    }

    .radial-chart {
        position: relative;
        width: 130px;
        height: 130px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
    }

    .radial-chart svg {
        transform: rotate(-90deg);
    }

    .radial-chart .circle-bg {
        fill: none;
        stroke: #e2e8f0;
        stroke-width: 10;
    }

    .radial-chart .circle-val {
        fill: none;
        stroke: var(--primary);
        stroke-width: 10;
        stroke-linecap: round;
        transition: stroke-dashoffset 1s ease-out;
    }

    .radial-percentage {
        position: absolute;
        font-size: 1.6rem;
        font-weight: 800;
        color: var(--text-main);
    }

    /* Médicos Frecuentes */
    .doctors-list {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .doctor-card {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        border-radius: 16px;
        border: 1px solid var(--border-color);
        background: #f8fafc;
        transition: all 0.2s ease;
    }

    .doctor-card:hover {
        background: white;
        border-color: var(--primary-light);
        box-shadow: var(--shadow-sm);
        transform: translateX(3px);
    }

    .doctor-avatar-circle {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: var(--primary-gradient);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
    }

    .doctor-info-text {
        flex: 1;
        margin-left: 15px;
    }

    .doctor-name-text {
        font-weight: 700;
        color: var(--text-main);
        margin: 0;
        font-size: 0.95rem;
    }

    .doctor-spec-text {
        color: var(--text-muted);
        font-size: 0.85rem;
        margin: 2px 0 0 0;
    }

    .doctor-visits-badge {
        background: rgba(15, 118, 110, 0.1);
        color: var(--primary);
        padding: 6px 12px;
        border-radius: 100px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    /* Barras de Progreso de Especialidades */
    .specialty-item {
        margin-bottom: 15px;
    }

    .specialty-header-info {
        display: flex;
        justify-content: space-between;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 6px;
    }

    .specialty-bar-track {
        height: 8px;
        background: #e2e8f0;
        border-radius: 100px;
        overflow: hidden;
    }

    .specialty-bar-fill {
        height: 100%;
        background: var(--primary-gradient);
        border-radius: 100px;
        transition: width 1s ease-out;
    }

    /* Tarjeta de Próxima Cita */
    .upcoming-appointment-card {
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        border: 1px solid #bbf7d0;
        border-radius: 20px;
        padding: 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        flex-wrap: wrap;
    }

    .upcoming-info-wrapper {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .upcoming-calendar-icon {
        width: 55px;
        height: 55px;
        background: white;
        border-radius: 14px;
        border: 1px solid #bbf7d0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: var(--shadow-sm);
    }

    .upcoming-calendar-month {
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--status-success);
        background: #f0fdf4;
        width: 100%;
        text-align: center;
        padding: 2px 0;
        border-top-left-radius: 13px;
        border-top-right-radius: 13px;
    }

    .upcoming-calendar-day {
        font-size: 1.3rem;
        font-weight: 800;
        color: var(--text-main);
        line-height: 1.1;
    }

    /* Formularios de Configuración */
    .settings-card {
        background: white;
        border-radius: 20px;
        border: 1px solid var(--border-color);
        padding: 30px;
        margin-bottom: 25px;
        box-shadow: var(--shadow-sm);
    }

    .settings-card-header {
        border-bottom: 1px solid var(--border-color);
        padding-bottom: 15px;
        margin-bottom: 25px;
    }

    .settings-card-title {
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-main);
        margin: 0;
    }

    .settings-card-desc {
        color: var(--text-muted);
        font-size: 0.85rem;
        margin: 5px 0 0 0;
    }
</style>

<div class="profile-container">
    
    <!-- HEADER DEL PERFIL -->
    <div class="profile-header">
        <div class="profile-header-info">
            <div class="profile-avatar-large">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div class="profile-meta">
                <h2 class="profile-name">{{ $user->name }}</h2>
                <span class="profile-role">
                    @if($user->hasRole('paciente')) Paciente @elseif($user->hasRole('medico')) Médico @else Administrador @endif
                </span>
            </div>
        </div>
        <div class="profile-details-list">
            <div class="profile-detail-item">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                <span style="font-weight: 600;">{{ $user->email }}</span>
            </div>
            @if($user->rut)
                <div class="profile-detail-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <span>RUT: <strong style="font-weight: 700;">{{ $user->rut }}</strong></span>
                </div>
            @endif
        </div>
    </div>

    @if($user->hasRole('paciente'))
        <!-- NAVEGACIÓN DE PESTAÑAS (TABS) -->
        <div class="tabs-nav">
            <button class="tab-link active" onclick="switchTab(event, 'tab-dashboard')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                Mi Dashboard
            </button>
            <button class="tab-link" onclick="switchTab(event, 'tab-historial')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                Historial Clínico
            </button>
            <button class="tab-link" onclick="switchTab(event, 'tab-configuracion')">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                Configurar Cuenta
            </button>
        </div>

        <!-- 1. PESTAÑA: DASHBOARD -->
        <div id="tab-dashboard" class="tab-panel active">
            
            <!-- TARJETAS DE MÉTRICAS -->
            <div class="dashboard-grid">
                <div class="widget-card">
                    <div class="widget-info">
                        <h4>Citas Totales</h4>
                        <p class="widget-value">{{ $totalCitas }}</p>
                    </div>
                    <div class="widget-icon primary">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="icon-lg"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    </div>
                </div>

                <div class="widget-card">
                    <div class="widget-info">
                        <h4>Asistidas</h4>
                        <p class="widget-value" style="color: var(--status-success);">{{ $atendidas }}</p>
                    </div>
                    <div class="widget-icon success">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="icon-lg"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                </div>

                <div class="widget-card">
                    <div class="widget-info">
                        <h4>Inasistencias</h4>
                        <p class="widget-value" style="color: var(--status-danger);">{{ $ausentes }}</p>
                    </div>
                    <div class="widget-icon danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="icon-lg"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    </div>
                </div>

                <div class="widget-card">
                    <div class="widget-info">
                        <h4>Pendientes</h4>
                        <p class="widget-value" style="color: var(--status-info);">{{ $programadas }}</p>
                    </div>
                    <div class="widget-icon info">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="icon-lg"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    </div>
                </div>
            </div>

            <!-- PRÓXIMA CITA DESTACADA -->
            @if($proximaCita)
                <div style="margin-bottom: 30px;">
                    <h3 class="section-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                        Próxima Atención Médica
                    </h3>
                    <div class="upcoming-appointment-card">
                        <div class="upcoming-info-wrapper">
                            <div class="upcoming-calendar-icon">
                                <span class="upcoming-calendar-month">{{ \Carbon\Carbon::parse($proximaCita->start_datetime)->translatedFormat('M') }}</span>
                                <span class="upcoming-calendar-day">{{ \Carbon\Carbon::parse($proximaCita->start_datetime)->format('d') }}</span>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 1.15rem; color: var(--text-main); font-weight: 800;">
                                    Dr(a). {{ $proximaCita->professionalProfile->user->name }}
                                </h4>
                                <p style="margin: 3px 0 0 0; color: var(--text-muted); font-size: 0.9rem; font-weight: 600;">
                                    Especialidad: <span style="color: var(--primary);">{{ $proximaCita->specialty->name }}</span>
                                </p>
                                <p style="margin: 5px 0 0 0; color: var(--status-success); font-size: 0.9rem; font-weight: 700; display: flex; align-items: center; gap: 5px;">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                                    {{ \Carbon\Carbon::parse($proximaCita->start_datetime)->translatedFormat('l, d \d\e F \a\ \l\a\s H:i') }} hrs
                                </p>
                            </div>
                        </div>
                        <div style="display: flex; gap: 10px;">
                            <a href="{{ route('citas.index') }}" class="btn btn-primary" style="padding: 12px 24px;">
                                Gestionar mis Citas
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- SECCIONES DE ANÁLISIS DOS COLUMNAS -->
            <div class="dashboard-two-col">
                
                <!-- Columna Izquierda: Especialidades & Análisis -->
                <div class="card">
                    <h3 class="section-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                        Especialidades Médicas Consultadas
                    </h3>
                    
                    @if($especialidadesFrecuentes->isEmpty())
                        <div style="text-align: center; padding: 40px 20px; color: var(--text-muted);">
                            <p>Aún no tienes atenciones registradas para desglosar por especialidades.</p>
                        </div>
                    @else
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            @php
                                $maxCount = $especialidadesFrecuentes->max('count') ?: 1;
                            @endphp
                            @foreach($especialidadesFrecuentes as $esp)
                                @php
                                    $pct = round(($esp['count'] / $maxCount) * 100);
                                @endphp
                                <div class="specialty-item">
                                    <div class="specialty-header-info">
                                        <span style="color: var(--text-main);">{{ $esp['name'] }}</span>
                                        <span style="color: var(--primary);">{{ $esp['count'] }} {{ $esp['count'] === 1 ? 'visita' : 'visitas' }}</span>
                                    </div>
                                    <div class="specialty-bar-track">
                                        <div class="specialty-bar-fill" style="width: {{ $pct }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Columna Derecha: Tasa de Asistencia & Médicos Frecuentes -->
                <div style="display: flex; flex-direction: column; gap: 30px;">
                    
                    <!-- Tasa de Asistencia -->
                    <div class="card" style="padding: 24px;">
                        <h3 class="section-title" style="margin-bottom: 15px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                            Tasa de Asistencia
                        </h3>
                        <div class="attendance-radial-container">
                            <div class="radial-chart">
                                <svg width="120" height="120" viewBox="0 0 120 120">
                                    <circle class="circle-bg" cx="60" cy="60" r="50" />
                                    <circle class="circle-val" cx="60" cy="60" r="50" 
                                            stroke-dasharray="314" 
                                            stroke-dashoffset="{{ 314 - (314 * $tasaAsistencia) / 100 }}" />
                                </svg>
                                <span class="radial-percentage">{{ $tasaAsistencia }}%</span>
                            </div>
                            <p style="margin: 0; font-size: 0.9rem; color: var(--text-muted); font-weight: 500;">
                                Has asistido a <strong>{{ $atendidas }}</strong> de tus últimas <strong>{{ $totalCitas - $canceladas }}</strong> citas programadas.
                            </p>
                        </div>
                    </div>

                    <!-- Médicos Frecuentes -->
                    <div class="card" style="padding: 24px;">
                        <h3 class="section-title" style="margin-bottom: 15px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            Tus Médicos Frecuentes
                        </h3>
                        @if($medicosFrecuentes->isEmpty())
                            <div style="text-align: center; padding: 20px; color: var(--text-muted); font-size: 0.9rem;">
                                No hay profesionales registrados en tu historial todavía.
                            </div>
                        @else
                            <div class="doctors-list">
                                @foreach($medicosFrecuentes as $medico)
                                    <div class="doctor-card">
                                        <div style="display: flex; align-items: center;">
                                            <div class="doctor-avatar-circle">
                                                {{ substr($medico['name'], 0, 1) }}
                                            </div>
                                            <div class="doctor-info-text">
                                                <h5 class="doctor-name-text">Dr(a). {{ $medico['name'] }}</h5>
                                                <p class="doctor-spec-text">{{ $medico['specialty'] }}</p>
                                            </div>
                                        </div>
                                        <span class="doctor-visits-badge">
                                            {{ $medico['count'] }} {{ $medico['count'] === 1 ? 'Cita' : 'Citas' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                </div>
            </div>

        </div>

        <!-- 2. PESTAÑA: HISTORIAL CLÍNICO -->
        <div id="tab-historial" class="tab-panel">
            <div class="card" style="padding: 25px;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; margin-bottom: 20px;">
                    <h3 class="section-title" style="margin: 0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        Historial de Atenciones Clínicas
                    </h3>
                    
                    <!-- Buscador/Filtro Rápido en Tabla -->
                    <input type="text" id="tableSearch" onkeyup="filterHistoryTable()" placeholder="Buscar por médico o especialidad..." class="form-control" style="max-width: 300px; padding: 8px 15px; font-size: 0.9rem; border-radius: 100px;">
                </div>

                <div class="table-container" style="margin-top: 0; border-radius: 14px; overflow: hidden;">
                    @if($citas->isEmpty())
                        <div style="padding: 40px; text-align: center; color: var(--text-muted);">
                            <p>No se encontraron registros de citas en tu historial médico.</p>
                        </div>
                    @else
                        <table id="historyTable">
                            <thead>
                                <tr>
                                    <th>Fecha y Hora</th>
                                    <th>Profesional Médico</th>
                                    <th>Área Especialidad</th>
                                    <th>Estado de Cita</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($citas as $cita)
                                    <tr>
                                        <td style="font-weight: 600; color: var(--text-main);">
                                            {{ \Carbon\Carbon::parse($cita->start_datetime)->translatedFormat('d M Y - H:i') }} hrs
                                        </td>
                                        <td style="font-weight: 500;">Dr(a). {{ $cita->professionalProfile->user->name ?? 'Asignado' }}</td>
                                        <td style="color: var(--text-muted);">{{ $cita->specialty->name ?? 'General' }}</td>
                                        <td>
                                            @php
                                                $badgeClass = 'badge-neutral';
                                                $statusName = strtolower($cita->status);
                                                if(in_array($statusName, ['reservada', 'confirmada', 'modificada'])) $badgeClass = 'badge-success';
                                                if($statusName == 'cancelada') $badgeClass = 'badge-danger';
                                                if($statusName == 'ausente') $badgeClass = 'badge-warning';
                                                if($statusName == 'atendida') $badgeClass = 'badge-info';
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">
                                                {{ ucfirst($cita->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- 3. PESTAÑA: CONFIGURACIÓN DE CUENTA (O VISTA POR DEFECTO PARA NO-PACIENTES) -->
    <div id="tab-configuracion" class="tab-panel @if(!$user->hasRole('paciente')) active @endif">
        
        <!-- Formulario: Información de Perfil -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h3 class="settings-card-title">Información Personal</h3>
                <p class="settings-card-desc">Actualiza tu nombre y correo electrónico vinculados a tu cuenta.</p>
            </div>
            
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('patch')
                
                <div class="form-group">
                    <label class="form-label" for="name">Nombre Completo</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" required autocomplete="name">
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Dirección de Correo Electrónico</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" required autocomplete="email">
                </div>

                <div style="display: flex; align-items: center; gap: 15px; margin-top: 25px;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 25px;">
                        Guardar Cambios
                    </button>
                    @if (session('status') === 'profile-updated')
                        <span style="color: var(--status-success); font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 5px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            ¡Información actualizada con éxito!
                        </span>
                    @endif
                </div>
            </form>
        </div>

        <!-- Formulario: Cambiar Contraseña -->
        <div class="settings-card">
            <div class="settings-card-header">
                <h3 class="settings-card-title">Cambiar Contraseña</h3>
                <p class="settings-card-desc">Asegúrate de utilizar una contraseña larga y aleatoria para mantener tu cuenta segura.</p>
            </div>

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                @method('put')

                <div class="form-group">
                    <label class="form-label" for="update_password_current_password">Contraseña Actual</label>
                    <input type="password" name="current_password" id="update_password_current_password" class="form-control" required autocomplete="current-password">
                </div>

                <div class="form-group">
                    <label class="form-label" for="update_password_password">Nueva Contraseña</label>
                    <input type="password" name="password" id="update_password_password" class="form-control" required autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label class="form-label" for="update_password_password_confirmation">Confirmar Nueva Contraseña</label>
                    <input type="password" name="password_confirmation" id="update_password_password_confirmation" class="form-control" required autocomplete="new-password">
                </div>

                <div style="display: flex; align-items: center; gap: 15px; margin-top: 25px;">
                    <button type="submit" class="btn btn-primary" style="padding: 12px 25px;">
                        Actualizar Contraseña
                    </button>
                    @if (session('status') === 'password-updated')
                        <span style="color: var(--status-success); font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 5px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            ¡Contraseña cambiada con éxito!
                        </span>
                    @endif
                </div>
            </form>
        </div>

        <!-- Formulario: Eliminar Cuenta -->
        <div class="settings-card" style="border-color: #fecaca;">
            <div class="settings-card-header" style="border-color: #fee2e2;">
                <h3 class="settings-card-title" style="color: var(--status-danger);">Eliminar Cuenta</h3>
                <p class="settings-card-desc">Una vez que tu cuenta sea eliminada, todos sus recursos y datos se borrarán de forma permanente.</p>
            </div>

            <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('¿Estás seguro de que deseas eliminar permanentemente tu cuenta? Esta acción no se puede deshacer.');">
                @csrf
                @method('delete')

                <div class="form-group">
                    <label class="form-label" for="delete_account_password">Ingresa tu contraseña para confirmar la eliminación</label>
                    <input type="password" name="password" id="delete_account_password" class="form-control" placeholder="Contraseña de seguridad" required style="max-width: 400px;">
                </div>

                <button type="submit" class="btn" style="background-color: var(--status-danger); color: white; padding: 12px 25px; margin-top: 15px; box-shadow: 0 4px 10px rgba(239, 68, 68, 0.25);">
                    Eliminar Cuenta de Forma Permanente
                </button>
            </form>
        </div>

    </div>

</div>

<script>
    /**
     * Alternar entre pestañas en el perfil
     */
    function switchTab(event, tabId) {
        // Ocultar todos los paneles
        const panels = document.querySelectorAll('.tab-panel');
        panels.forEach(panel => {
            panel.classList.remove('active');
        });

        // Desactivar todos los botones de pestaña
        const tabLinks = document.querySelectorAll('.tab-link');
        tabLinks.forEach(link => {
            link.classList.remove('active');
        });

        // Activar el panel seleccionado
        document.getElementById(tabId).classList.add('active');
        
        // Activar el botón que disparó el evento
        if (event) {
            event.currentTarget.classList.add('active');
        }
    }

    /**
     * Filtrar dinámicamente la tabla de historial de citas
     */
    function filterHistoryTable() {
        const input = document.getElementById('tableSearch');
        const filter = input.value.toLowerCase();
        const table = document.getElementById('historyTable');
        if (!table) return;
        
        const tr = table.getElementsByTagName('tr');

        for (let i = 1; i < tr.length; i++) {
            let matchFound = false;
            const tds = tr[i].getElementsByTagName('td');
            
            // Buscar coincidencia en la columna de Profesional (índice 1) o Especialidad (índice 2)
            if (tds[1] || tds[2]) {
                const doctorText = (tds[1].textContent || tds[1].innerText).toLowerCase();
                const specialtyText = (tds[2].textContent || tds[2].innerText).toLowerCase();
                
                if (doctorText.indexOf(filter) > -1 || specialtyText.indexOf(filter) > -1) {
                    matchFound = true;
                }
            }

            if (matchFound) {
                tr[i].style.display = '';
            } else {
                tr[i].style.display = 'none';
            }
        }
    }
</script>
@endsection
