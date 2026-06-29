<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MediCore') }} - Acceso</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary:          #0f766e;
            --primary-light:    #14b8a6;
            --primary-gradient: linear-gradient(135deg, #14b8a6 0%, #0f766e 100%);
            --text-main:        #0f172a;
            --text-muted:       #64748b;
            --border-color:     #e2e8f0;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            min-height: 100vh;
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
            padding: 40px 20px;
        }

        .auth-card {
            width: 100%;
            max-width: 28rem;
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            border-radius: 30px;
            border: 1px solid rgba(255, 255, 255, 0.6);
            position: relative;
            z-index: 10;
            margin-top: 2rem;
        }

        @media (max-width: 640px) {
            .auth-card {
                border-radius: 0px;
                border: none;
                box-shadow: none;
                background: white;
            }
        }

        /* Blobs decorativos */
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

        /* Título contextual */
        .panel-form-header { margin-bottom: 24px; }
        .panel-form-header h2 {
            font-size: 1.5rem; font-weight: 800;
            color: var(--text-main); margin-bottom: 6px;
        }
        .panel-form-header p { color: var(--text-muted); font-size: 0.93rem; }

        /* Override Laravel / Breeze defaults */
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
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 0.93rem;
            transition: all .2s;
        }
        input[type="email"]:focus, input[type="password"]:focus, input[type="text"]:focus {
            border-color: #0f766e !important;
            outline: none !important;
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.2) !important;
        }

        input[type="checkbox"] {
            accent-color: var(--primary-light);
            width: 15px; height: 15px; cursor: pointer;
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

        /* Mensajes de error */
        .text-red-600, [class*="text-red"] {
            color: #dc2626; font-size: 0.82rem; margin-top: 4px; display: block;
        }

        /* Alert de sesión */
        .mb-4 {
            padding: 12px 16px; border-radius: 10px;
            background: #d1fae5; color: #065f46;
            border: 1px solid #a7f3d0; font-size: 0.88rem;
            font-weight: 500; margin-bottom: 20px;
        }

        /* Espaciado */
        .mt-4 { margin-top: 16px; }
        .mt-6 { margin-top: 22px; }
        .block { display: block; }

        .footer-text {
            position: relative;
            z-index: 10;
            margin-top: 2rem;
            color: #64748b;
            font-weight: 500;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <div class="auth-bg">

        <!-- Elementos decorativos de fondo (Blobs) -->
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>

        <!-- Logo -->
        <div style="text-align: center; position: relative; z-index: 10;">
            <a href="/" style="display: flex; align-items: center; justify-content: center; gap: 10px; font-size: 2.5rem; font-weight: 800; color: #0f766e; text-decoration: none;">
                <svg viewBox="0 0 24 24" width="45" height="45" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                MediCore
            </a>
            <p style="text-align: center; font-size: 0.95rem; font-weight: 600; margin-top: 5px; color: #0f766e; opacity: 0.8;">Plataforma Médica Integral</p>
        </div>

        <!-- Tarjeta Glassmorphism -->
        <div class="auth-card">

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

        <div class="footer-text">
            &copy; {{ date('Y') }} Clínica MediCore. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>