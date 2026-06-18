<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>MediCore - Salud Integral</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    
    <style>
        :root {
            --primary: #0f766e;
            --primary-light: #14b8a6;
            --primary-dark: #115e59;
            --secondary: #fef08a;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Navbar */
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            margin-bottom: 30px;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--primary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-links {
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-main);
            font-weight: 500;
            font-size: 0.95rem;
            transition: color 0.3s;
        }

        .nav-links a:hover {
            color: var(--primary);
        }

        .btn-primary {
            background-color: var(--primary);
            color: white !important;
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.3s;
            border: none;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-outline {
            border: 1px solid var(--border-color);
            background: white;
            color: var(--text-main);
            padding: 10px 24px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: border-color 0.3s;
        }

        .btn-outline:hover {
            border-color: var(--primary);
        }

        @media (max-width: 768px) {
            .navbar { flex-direction: column; gap: 15px; }
            .nav-links { display: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Navbar -->
    <nav class="navbar">
        <a href="/" class="logo">
            <svg viewBox="0 0 24 24" width="28" height="28" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round" class="css-i6dzq1"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
            MediCore
        </a>
        <div class="nav-links">
            <a href="/#especialidades">Especialidades</a>
            <a href="/#doctores">Nuestros Médicos</a>
            <a href="/#contacto">Contacto</a>
            <span style="color: var(--border-color)">|</span>
            <span style="font-weight: 600; font-size: 0.95rem;">📞 +56 9 1234 5678</span>
        </div>
        <div>
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-primary">Ir a mi Panel</a>
                @else
                    <a href="{{ route('citas.create') }}" class="btn-primary">Agendar Cita</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-outline" style="margin-left: 10px; border: none; padding: 10px 5px;">Registro</a>
                    @endif
                @endauth
            @endif
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

</div>
</body>
</html>
