<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'MediCore') }}</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
        <style>
            *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
                display: flex;
                min-height: 100vh;
                background: #f8fafc;
            }

            /* ── PANEL IZQUIERDO OSCURO ── */
            .auth-left {
                width: 50%;
                background: linear-gradient(160deg, #0f172a 0%, #0f2d2b 60%, #134e4a 100%);
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                padding: 3rem;
                position: relative;
                overflow: hidden;
            }
            .auth-left::before {
                content: '';
                position: absolute;
                top: -100px; left: -100px;
                width: 500px; height: 500px;
                background: radial-gradient(circle, rgba(20,184,166,0.15) 0%, transparent 70%);
                pointer-events: none;
            }
            .auth-left::after {
                content: '';
                position: absolute;
                bottom: -80px; right: -80px;
                width: 400px; height: 400px;
                background: radial-gradient(circle, rgba(15,118,110,0.2) 0%, transparent 70%);
                pointer-events: none;
            }
            .auth-logo {
                display: flex;
                align-items: center;
                gap: 10px;
                color: #14b8a6;
                font-size: 1.5rem;
                font-weight: 800;
                text-decoration: none;
                position: relative;
                z-index: 2;
            }
            .auth-logo svg { stroke: #14b8a6; }

            .auth-hero {
                position: relative;
                z-index: 2;
            }
            .auth-hero h1 {
                font-size: 2.8rem;
                font-weight: 800;
                color: #f8fafc;
                line-height: 1.15;
                margin-bottom: 1rem;
            }
            .auth-hero h1 span { color: #14b8a6; }
            .auth-hero p {
                color: #94a3b8;
                font-size: 1rem;
                line-height: 1.6;
                max-width: 380px;
            }

            .auth-stats {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 12px;
                position: relative;
                z-index: 2;
            }
            .stat-card {
                background: rgba(255,255,255,0.05);
                border: 1px solid rgba(255,255,255,0.08);
                border-radius: 14px;
                padding: 1rem;
            }
            .stat-card .stat-num {
                font-size: 1.6rem;
                font-weight: 800;
                color: #14b8a6;
            }
            .stat-card .stat-label {
                font-size: 0.75rem;
                color: #64748b;
                margin-top: 2px;
            }

            .auth-avatars {
                display: flex;
                align-items: center;
                gap: 8px;
                position: relative;
                z-index: 2;
            }
            .avatar-stack { display: flex; }
            .avatar-stack .av {
                width: 34px; height: 34px;
                border-radius: 50%;
                border: 2px solid #0f172a;
                margin-left: -8px;
                font-size: 0.7rem;
                font-weight: 700;
                display: flex; align-items: center; justify-content: center;
                color: white;
            }
            .avatar-stack .av:first-child { margin-left: 0; }
            .av-teal   { background: #0f766e; }
            .av-cyan   { background: #0891b2; }
            .av-slate  { background: #475569; }
            .av-purple { background: #7c3aed; }
            .av-more   { background: rgba(255,255,255,0.15); font-size: 0.65rem; }
            .auth-avatars span {
                font-size: 0.82rem;
                color: #94a3b8;
            }
            .auth-avatars strong { color: #e2e8f0; }

            /* ── PANEL DERECHO FORMULARIO ── */
            .auth-right {
                width: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 3rem;
                background: #f8fafc;
            }
            .auth-form-wrap {
                width: 100%;
                max-width: 420px;
            }
            .auth-form-wrap h2 {
                font-size: 2rem;
                font-weight: 800;
                color: #0f172a;
                margin-bottom: 6px;
            }
            .auth-form-wrap .auth-subtitle {
                font-size: 0.9rem;
                color: #64748b;
                margin-bottom: 2rem;
            }

            /* Inputs */
            .form-group { margin-bottom: 1.2rem; }
            .form-group label {
                display: block;
                font-weight: 600;
                font-size: 0.9rem;
                color: #334155;
                margin-bottom: 6px;
            }
            .form-group input[type="email"],
            .form-group input[type="password"],
            .form-group input[type="text"] {
                width: 100%;
                padding: 13px 16px;
                border: 1.5px solid #e2e8f0;
                border-radius: 12px;
                font-size: 0.95rem;
                font-family: inherit;
                color: #0f172a;
                background: white;
                transition: border-color 0.2s, box-shadow 0.2s;
                outline: none;
            }
            .form-group input:focus {
                border-color: #0f766e;
                box-shadow: 0 0 0 3px rgba(15,118,110,0.12);
            }

            /* Botón submit */
            .btn-submit {
                background: #0f766e;
                color: white;
                border: none;
                border-radius: 50px;
                padding: 13px 28px;
                font-size: 0.9rem;
                font-weight: 700;
                font-family: inherit;
                letter-spacing: 0.5px;
                cursor: pointer;
                transition: background 0.2s;
            }
            .btn-submit:hover { background: #115e59; }

            /* Fila inferior del form */
            .auth-form-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-top: 1.5rem;
            }
            .auth-form-footer a, .auth-form-footer-center a {
                font-size: 0.88rem;
                color: #0f766e;
                font-weight: 600;
                text-decoration: none;
            }
            .auth-form-footer a:hover { color: #115e59; }
            .auth-form-footer-center {
                text-align: center;
                margin-top: 1.5rem;
            }

            /* Checkbox */
            .remember-row {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-top: 0.8rem;
            }
            .remember-row input[type="checkbox"] {
                width: 16px; height: 16px;
                accent-color: #0f766e;
                cursor: pointer;
            }
            .remember-row label {
                font-size: 0.88rem;
                color: #64748b;
                cursor: pointer;
            }

            /* Error messages */
            .field-error {
                font-size: 0.8rem;
                color: #ef4444;
                margin-top: 4px;
            }

            /* Responsive */
            @@media (max-width: 768px) {
                .auth-left { display: none; }
                .auth-right { width: 100%; padding: 2rem 1.5rem; }
            }

        </style>
    </head>
    <body>
        <!-- Panel Izquierdo -->
        <div class="auth-left">
            <a href="/" class="auth-logo">
                <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                </svg>
                MediCore
            </a>

            <div class="auth-hero">
                <h1>La clínica del<br>futuro, <span>hoy.</span></h1>
                <p>Gestiona citas, profesionales y reportes desde un solo lugar. Diseñado para equipos médicos que priorizan la eficiencia y la experiencia del paciente.</p>
            </div>

            <div class="auth-stats">
                <div class="stat-card">
                    <div class="stat-num">+80</div>
                    <div class="stat-label">Médicos activos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num">99%</div>
                    <div class="stat-label">Uptime del sistema</div>
                </div>
                <div class="stat-card">
                    <div class="stat-num">3</div>
                    <div class="stat-label">Portales de acceso</div>
                </div>
            </div>

            <div class="auth-avatars">
                <div class="avatar-stack">
                    <div class="av av-teal">KR</div>
                    <div class="av av-cyan">MS</div>
                    <div class="av av-slate">AS</div>
                    <div class="av av-purple">HK</div>
                    <div class="av av-more">+76</div>
                </div>
                <span>Únete a <strong>+80 profesionales</strong> registrados</span>
            </div>
        </div>

        <!-- Panel Derecho -->
        <div class="auth-right">
            <div class="auth-form-wrap">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>