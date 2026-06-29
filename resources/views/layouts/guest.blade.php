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

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary:          #0f766e;
            --primary-light:    #14b8a6;
            --primary-gradient: linear-gradient(135deg, #14b8a6 0%, #0f766e 100%);
            --bg-body:          #f1f5f9;
            --bg-sidebar:       #0f172a;
            --text-main:        #0f172a;
            --text-muted:       #64748b;
            --border-color:     #e2e8f0;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
        }

        /* ── Panel izquierdo decorativo ── */
        .panel-left {
            width: 50%;
            background: var(--bg-sidebar);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 50px;
            position: relative;
            overflow: hidden;
        }
        .panel-left::before {
            content: '';
            position: absolute;
            width: 520px; height: 520px;
            background: radial-gradient(circle, rgba(20,184,166,.18) 0%, transparent 70%);
            top: -120px; left: -120px;
            border-radius: 50%;
            pointer-events: none;
        }
        .panel-left::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(20,184,166,.12) 0%, transparent 70%);
            bottom: -100px; right: -80px;
            border-radius: 50%;
            pointer-events: none;
        }

        .logo {
            display: flex; align-items: center; gap: 12px;
            color: #f8fafc; font-size: 1.6rem; font-weight: 800;
            position: relative; z-index: 1; text-decoration: none;
        }
        .logo svg { width: 32px; height: 32px; color: var(--primary-light); }

        .panel-content { position: relative; z-index: 1; }

        .panel-headline {
            font-size: 2.3rem; font-weight: 800;
            color: #f8fafc; line-height: 1.2; margin-bottom: 16px;
        }
        .panel-headline span {
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .panel-sub {
            color: #94a3b8; font-size: 0.95rem;
            line-height: 1.7; margin-bottom: 40px; max-width: 380px;
        }

        .stats-grid {
            display: grid; grid-template-columns: repeat(3, 1fr);
            gap: 14px; margin-bottom: 40px;
        }
        .stat-card {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 14px; padding: 18px;
        }
        .stat-number { font-size: 1.7rem; font-weight: 800; color: var(--primary-light); }
        .stat-label  { font-size: 0.75rem; color: #94a3b8; font-weight: 500; margin-top: 3px; }

        .doctors-row { display: flex; align-items: center; }
        .doctor-avatar {
            width: 42px; height: 42px; border-radius: 50%;
            border: 3px solid var(--bg-sidebar);
            background: var(--primary-gradient);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.82rem; color: white;
            margin-left: -10px; flex-shrink: 0;
        }
        .doctor-avatar:first-child { margin-left: 0; }
        .doctors-label {
            margin-left: 14px; font-size: 0.83rem; color: #94a3b8;
        }
        .doctors-label strong { color: #f8fafc; }

        /* ── Panel derecho ── */
        .panel-right {
            width: 50%;
            display: flex; align-items: center; justify-content: center;
            padding: 50px 60px; overflow-y: auto;
        }

        .slot-wrapper { width: 100%; max-width: 420px; }

        /* Estilos para los componentes Blade de Breeze dentro del slot */
        .slot-wrapper label {
            display: block; font-weight: 600;
            font-size: 0.88rem; margin-bottom: 7px; color: var(--text-main);
        }
        .slot-wrapper input[type="text"],
        .slot-wrapper input[type="email"],
        .slot-wrapper input[type="password"] {
            width: 100%; padding: 12px 15px;
            border-radius: 12px;
            border: 1.5px solid var(--border-color);
            background-color: #f8fafc;
            color: var(--text-main);
            font-size: 0.93rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            transition: all .2s;
            margin-bottom: 4px;
        }
        .slot-wrapper input[type="text"]:focus,
        .slot-wrapper input[type="email"]:focus,
        .slot-wrapper input[type="password"]:focus {
            outline: none;
            border-color: var(--primary-light);
            background-color: white;
            box-shadow: 0 0 0 3px rgba(20,184,166,.12);
        }
        .slot-wrapper input[type="checkbox"] {
            accent-color: var(--primary-light);
            width: 15px; height: 15px; cursor: pointer;
        }
        /* Botón primario de Breeze */
        .slot-wrapper button[type="submit"],
        .slot-wrapper .btn-submit {
            padding: 12px 28px;
            border-radius: 100px;
            background: var(--primary-gradient);
            color: white; font-weight: 700;
            font-size: 0.95rem; border: none; cursor: pointer;
            box-shadow: 0 4px 14px rgba(20,184,166,.35);
            transition: all .2s;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .slot-wrapper button[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(20,184,166,.45);
        }
        /* Links dentro del slot */
        .slot-wrapper a {
            color: var(--primary); font-weight: 600;
            font-size: 0.88rem; text-decoration: none;
        }
        .slot-wrapper a:hover { text-decoration: underline; }
        /* Mensajes de error de Breeze */
        .slot-wrapper .text-red-600,
        .slot-wrapper [class*="text-red"] {
            color: #dc2626; font-size: 0.82rem; margin-top: 4px; display: block;
        }
        /* Alert de sesión de Breeze */
        .slot-wrapper .mb-4 {
            padding: 12px 16px; border-radius: 10px;
            background: #d1fae5; color: #065f46;
            border: 1px solid #a7f3d0; font-size: 0.88rem;
            font-weight: 500; margin-bottom: 20px;
        }
        /* Espaciado entre campos */
        .slot-wrapper .mt-4 { margin-top: 16px; }
        .slot-wrapper .mt-6 { margin-top: 22px; }
        .slot-wrapper .block { display: block; }

        /* Encabezado del panel derecho (título contextual) */
        .panel-form-header { margin-bottom: 28px; }
        .panel-form-header h2 {
            font-size: 1.9rem; font-weight: 800;
            color: var(--text-main); margin-bottom: 6px;
        }
        .panel-form-header p { color: var(--text-muted); font-size: 0.93rem; }

        /* Responsive */
        @media (max-width: 900px) {
            .panel-left  { display: none; }
            .panel-right { width: 100%; padding: 40px 24px; }
        }
    </style>
</head>
<body>

    <!-- Panel izquierdo decorativo -->
    <div class="panel-left">
        <a href="/" class="logo">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
            </svg>
            MediCore
        </a>

        <div class="panel-content">
            <h1 class="panel-headline">
                La clínica del<br>futuro, <span>hoy.</span>
            </h1>
            <p class="panel-sub">
                Gestiona citas, profesionales y reportes desde un solo lugar. Diseñado para equipos médicos que priorizan la eficiencia y la experiencia del paciente.
            </p>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">+80</div>
                    <div class="stat-label">Médicos activos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">99%</div>
                    <div class="stat-label">Uptime del sistema</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">3</div>
                    <div class="stat-label">Portales de acceso</div>
                </div>
            </div>

            <div class="doctors-row">
                <div class="doctor-avatar">KR</div>
                <div class="doctor-avatar">MS</div>
                <div class="doctor-avatar">AS</div>
                <div class="doctor-avatar">HK</div>
                <div class="doctor-avatar" style="font-size:.7rem;">+76</div>
                <span class="doctors-label">Únete a <strong>+80 profesionales</strong> registrados</span>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'MediCore') }} - Acceso</title>

        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif !important;
            }
            .auth-bg {
                background: linear-gradient(to bottom right, #f0fdfa, #ccfbf1);
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                position: relative;
                overflow: hidden;
                padding-top: 20px;
            }
            .auth-card {
                width: 100%;
                max-width: 28rem;
                padding: 2.5rem;
                background: rgba(255, 255, 255, 0.8);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
                border-radius: 30px !important;
                border: 1px solid rgba(255, 255, 255, 0.6) !important;
                position: relative;
                z-index: 10;
                margin-top: 2rem;
            }
            @media (max-width: 640px) {
                .auth-card {
                    border-radius: 0px !important;
                    border: none !important;
                    box-shadow: none !important;
                    background: white;
                }
            }
            .blob {
                position: absolute;
                border-radius: 50%;
                mix-blend-mode: multiply;
                opacity: 0.2;
                z-index: 1;
            }
            .blob-1 { top: -10%; left: -10%; width: 24rem; height: 24rem; background: #0f766e; filter: blur(80px); animation: blob 10s infinite alternate; }
            .blob-2 { top: 20%; right: -10%; width: 30rem; height: 30rem; background: #14b8a6; filter: blur(100px); animation: blob 10s infinite alternate 2s; }
            .blob-3 { bottom: -20%; left: 20%; width: 25rem; height: 25rem; background: #2dd4bf; filter: blur(80px); animation: blob 10s infinite alternate 4s; }
            
            @keyframes blob {
                0% { transform: translate(0px, 0px) scale(1); }
                33% { transform: translate(40px, -60px) scale(1.1); }
                66% { transform: translate(-30px, 30px) scale(0.9); }
                100% { transform: translate(0px, 0px) scale(1); }
            }
            /* Override Laravel defaults */
            button[type="submit"], .bg-gray-800 {
                background-color: #0f766e !important;
                border-radius: 50px !important;
                padding: 12px 24px !important;
                font-weight: 700 !important;
                letter-spacing: 0.5px;
                transition: background 0.3s !important;
                color: white !important;
                border: none !important;
                width: auto !important;
            }
            button[type="submit"]:hover, .bg-gray-800:hover {
                background-color: #115e59 !important;
            }
            input[type="email"], input[type="password"], input[type="text"] {
                border-radius: 12px !important;
                border: 1px solid #cbd5e1 !important;
                background: white !important;
                padding: 12px 16px !important;
                width: 100% !important;
                box-shadow: none !important;
            }
            input[type="email"]:focus, input[type="password"]:focus, input[type="text"]:focus {
                border-color: #0f766e !important;
                outline: none !important;
                box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.2) !important;
            }
            a {
                color: #0f766e !important;
                font-weight: 600;
                text-decoration: none !important;
            }
            a:hover {
                color: #115e59 !important;
            }
            label {
                font-weight: 600 !important;
                color: #334155 !important;
            }
        </style>
    </head>
    <body>
        <div class="auth-bg">
            
            <!-- Elementos decorativos de fondo (Blobs) -->
            <div class="blob blob-1"></div>
            <div class="blob blob-2"></div>
            <div class="blob blob-3"></div>

            <div class="z-10" style="text-align: center; position: relative;">
                <a href="/" style="display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 2.5rem; font-weight: 800; color: #0f766e; text-decoration: none;">
                    <svg viewBox="0 0 24 24" width="45" height="45" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                    MediCore
                </a>
                <p style="text-align: center; font-size: 0.95rem; font-weight: 600; margin-top: 5px; color: #0f766e; opacity: 0.8;">Plataforma Médica Integral</p>
            </div>

            <!-- Tarjeta Glassmorphism -->
            <div class="auth-card">
                {{ $slot }}
            </div>
            
            <div class="z-10 mt-8 text-sm" style="color: #64748b; font-weight: 500;">
                &copy; {{ date('Y') }} Clínica MediCore. Todos los derechos reservados.
            </div>
        </div>
    </div>

    <!-- Panel derecho: slot de Breeze -->
    <div class="panel-right">
        <div class="slot-wrapper">

            {{-- Título contextual según la ruta actual --}}
            <div class="panel-form-header">
                @if(request()->routeIs('login'))
                    <h2>Bienvenido de nuevo</h2>
                    <p>Ingresa tus credenciales para acceder al sistema.</p>
                @elseif(request()->routeIs('register'))
                    <h2>Crear cuenta</h2>
                    <p>Completa el formulario para registrarte como paciente.</p>
                @elseif(request()->routeIs('password.request'))
                    <h2>Recuperar contraseña</h2>
                    <p>Te enviaremos un enlace para restablecer tu acceso.</p>
                @elseif(request()->routeIs('password.reset'))
                    <h2>Nueva contraseña</h2>
                    <p>Elige una contraseña segura para tu cuenta.</p>
                @else
                    <h2>MediCore</h2>
                @endif
            </div>

            {{ $slot }}

        </div>
    </div>

</body>
</html>