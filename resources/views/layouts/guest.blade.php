<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
    </body>
</html>
