<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediCore - Portal Clínico</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            /* Colores de la paleta médica (Teal / Cyan) */
            --primary: #0f766e;
            --primary-light: #14b8a6;
            --primary-gradient: linear-gradient(135deg, #14b8a6 0%, #0f766e 100%);
            
            --bg-body: #f1f5f9; /* Color crema claro de la imagen 2 */
            --bg-card: #ffffff;
            --bg-sidebar: #0f172a;
            
            --text-main: #0f172a;
            --text-muted: #64748b;
            --text-light: #f8fafc;
            
            --border-color: #e2e8f0;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            
            /* Status Colors */
            --status-success: #10b981;
            --status-warning: #f59e0b;
            --status-danger: #ef4444;
            --status-info: #3b82f6;
        }

        body { 
            margin: 0; 
            padding: 0; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-body); 
            color: var(--text-main); 
            display: flex; 
            height: 100vh;
            overflow: hidden;
        }

        /* --- SIDEBAR --- */
        .sidebar { 
            width: 260px; 
            background-color: var(--bg-sidebar); 
            padding: 30px 20px; 
            display: flex; 
            flex-direction: column; 
            color: var(--text-light);
            border-top-right-radius: 24px;
            border-bottom-right-radius: 24px;
            margin: 10px 0 10px 10px;
            box-shadow: var(--shadow-lg);
        }
        .sidebar h2 { 
            font-size: 1.75rem; 
            font-weight: 800;
            margin: 0 0 30px 0; 
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar h2 svg { width: 28px; height: 28px; color: var(--primary-light); }
        
        .sidebar .user-info { 
            font-size: 0.9rem; 
            margin-bottom: 30px; 
            padding-bottom: 20px; 
            border-bottom: 1px solid rgba(255,255,255,0.1); 
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .sidebar .user-avatar {
            width: 40px; height: 40px; border-radius: 50%; background: var(--primary-gradient);
            display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.1rem;
        }

        .sidebar a { 
            display: flex; 
            align-items: center;
            gap: 12px;
            color: #94a3b8; 
            text-decoration: none; 
            padding: 12px 15px; 
            margin-bottom: 5px; 
            border-radius: 10px; 
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.2s ease; 
        }
        .sidebar a svg { width: 18px; height: 18px; opacity: 0.7; transition: all 0.2s; }
        .sidebar a:hover { background-color: rgba(255,255,255,0.05); color: white; }
        .sidebar a:hover svg { opacity: 1; color: var(--primary-light); }
        .sidebar .section-label { color: #475569; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin: 20px 0 10px 15px; letter-spacing: 1px; }

        /* --- MAIN CONTENT --- */
        .main-content { 
            flex: 1; 
            padding: 40px; 
            overflow-y: auto; 
            position: relative;
        }

        /* --- GLOBAL PROFILE BANNER --- */
        .profile-header {
            background: linear-gradient(135deg, #0f766e 0%, #0f172a 100%);
            color: white;
            border-radius: 24px;
            padding: 25px 35px;
            box-shadow: var(--shadow-lg);
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 25px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 30px;
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
        .profile-header-info { display: flex; align-items: center; gap: 20px; z-index: 1; }
        .profile-avatar-large {
            width: 65px; height: 65px; border-radius: 50%; background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px); border: 2px solid rgba(255, 255, 255, 0.4);
            display: flex; align-items: center; justify-content: center; font-size: 1.8rem;
            font-weight: 800; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .profile-meta { display: flex; flex-direction: column; gap: 4px; }
        .profile-name { font-size: 1.5rem; font-weight: 800; margin: 0; letter-spacing: -0.5px; color: #ffffff; }
        .profile-role {
            font-size: 0.8rem; background: rgba(255, 255, 255, 0.2); padding: 4px 12px;
            border-radius: 100px; width: fit-content; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .profile-details-list { display: flex; gap: 20px; flex-wrap: wrap; z-index: 1; }
        .profile-detail-item {
            display: flex; align-items: center; gap: 10px; background: rgba(255, 255, 255, 0.1);
            padding: 10px 18px; border-radius: 12px; backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.1); font-size: 0.9rem;
        }
        .profile-detail-item svg { width: 18px; height: 18px; opacity: 0.9; }

        /* Ajustes para textos dentro del banner */
        .profile-header .page-title { color: white; font-size: 1.3rem; margin: 0; }
        .profile-header .page-subtitle { color: rgba(255, 255, 255, 0.7); font-size: 0.9rem; margin-top: 3px; font-weight: 500; }
        .profile-header .text-muted { color: rgba(255, 255, 255, 0.7) !important; }

        /* Top Bar (mantener para vistas antiguas hasta que se migren todas) */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            padding: 15px 30px;
            border-radius: 100px;
            box-shadow: var(--shadow-sm);
        }
        
        .page-title { font-size: 1.5rem; font-weight: 700; margin: 0; color: var(--text-main); }
        .page-subtitle { color: var(--text-muted); font-size: 0.9rem; margin-top: 5px; }

        /* --- CARDS & GLASSMORPHISM --- */
        .card {
            background-color: var(--bg-card);
            border-radius: 20px;
            padding: 25px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card:hover { box-shadow: var(--shadow-lg); }
        
        /* --- TABLES --- */
        .table-container {
            background-color: var(--bg-card);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            margin-top: 20px;
        }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { 
            background-color: #f8fafc; 
            color: var(--text-muted); 
            text-transform: uppercase; 
            font-size: 0.75rem; 
            font-weight: 700; 
            padding: 15px 20px; 
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
        }
        td { padding: 15px 20px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background-color: #f8fafc; }

        /* --- BUTTONS --- */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 20px; border-radius: 100px; font-weight: 600; font-size: 0.9rem;
            cursor: pointer; transition: all 0.2s ease; text-decoration: none; border: none;
        }
        .btn-primary { background: var(--primary-gradient); color: white; box-shadow: 0 4px 10px rgba(20, 184, 166, 0.3); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(20, 184, 166, 0.4); }
        .btn-outline { background: transparent; color: var(--text-main); border: 1px solid var(--border-color); }
        .btn-outline:hover { background: #f1f5f9; border-color: #cbd5e1; }

        /* --- BADGES --- */
        .badge {
            padding: 5px 12px; border-radius: 100px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
        }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-info { background-color: #dbeafe; color: #1e40af; }
        .badge-neutral { background-color: #f1f5f9; color: #475569; }

        /* --- INPUTS --- */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-weight: 600; font-size: 0.9rem; margin-bottom: 8px; color: var(--text-main); }
        .form-control {
            width: 100%; padding: 12px 15px; border-radius: 10px; border: 1px solid var(--border-color);
            background-color: #f8fafc; color: var(--text-main); font-size: 0.95rem; font-family: inherit;
            transition: all 0.2s; box-sizing: border-box;
        }
        .form-control:focus { outline: none; border-color: var(--primary-light); background-color: white; box-shadow: 0 0 0 3px rgba(20, 184, 166, 0.1); }

        /* --- ALERTS --- */
        .alert { padding: 15px 20px; border-radius: 12px; margin-bottom: 25px; font-weight: 500; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; }
        .alert-success { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* Icon Utility */
        .icon-sm { width: 16px; height: 16px; }
        .icon-md { width: 20px; height: 20px; }
        .icon-lg { width: 24px; height: 24px; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
            MediCore
        </h2>
        
        @auth
            <div class="user-info">
                <div class="user-avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
                <div>
                    <div style="font-weight: 600; color: white;">{{ Auth::user()->name }}</div>
                    <div style="font-size: 0.8rem; color: var(--primary-light);">
                        @foreach(Auth::user()->getRoleNames() as $role)
                            {{ ucfirst($role) }}
                        @endforeach
                    </div>
                </div>
            </div>
        @endauth

        <div style="flex: 1; overflow-y: auto;">
            @role('admin')
                <p class="section-label">General</p>
                <a href="{{ route('admin.dashboard') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    Dashboard
                </a>
                
                <p class="section-label">Gestión Médica</p>
                <a href="{{ route('pacientes.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    Pacientes
                </a>
                <a href="{{ route('perfiles.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    Profesionales
                </a>
                <a href="{{ route('especialidades.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                    Especialidades
                </a>
                <a href="{{ route('admin.waitlist.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                    Lista de Espera
                </a>
                <a href="{{ route('reportes.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                    Reportes
                </a>
                
                <p class="section-label">Ajustes</p>
                <a href="{{ route('admin.users.create-medico') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    Nuevo Médico
                </a>
            @endrole

            @role('paciente')
                <p class="section-label">Mi Portal</p>
                <a href="{{ route('citas.index') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    Mis Citas
                </a>
            @endrole

            @role('medico')
                <p class="section-label">Panel Clínico</p>
                <a href="{{ route('medico.dashboard') }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    Mi Agenda
                </a>
            @endrole
        </div>

        <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.1);">
            <a href="{{ route('profile.edit') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                Mi Perfil
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 12px 15px; font-size: 0.95rem; font-weight: 500; width: 100%; text-align: left; font-family: inherit; display: flex; align-items: center; gap: 12px; border-radius: 10px; transition: 0.2s;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-md"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                    Cerrar Sesión
                </button>
            </form>
        </div>
    </div>

    <!-- MAIN AREA -->
    <div class="main-content">
        @auth
            <!-- GLOBAL PROFILE BANNER -->
            <div class="profile-header">
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 20px;">
                    <div class="profile-header-info">
                        <div class="profile-avatar-large">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div class="profile-meta">
                            <h2 class="profile-name">{{ Auth::user()->name }}</h2>
                            <span class="profile-role">
                                @if(Auth::user()->hasRole('paciente')) Paciente @elseif(Auth::user()->hasRole('medico')) Médico @else Administrador @endif
                            </span>
                        </div>
                    </div>
                    <div class="profile-details-list">
                        <div class="profile-detail-item">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            <span style="font-weight: 600;">{{ Auth::user()->email }}</span>
                        </div>
                        @if(Auth::user()->rut)
                            <div class="profile-detail-item">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                                <span>RUT: <strong style="font-weight: 700;">{{ Auth::user()->rut }}</strong></span>
                            </div>
                        @endif
                    </div>
                </div>

                @hasSection('header_context')
                    <div style="width: 100%; border-top: 1px solid rgba(255, 255, 255, 0.15); margin-top: 10px; padding-top: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        @yield('header_context')
                    </div>
                @endif
            </div>
        @endauth

        @if(session('success'))
            <div class="alert alert-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-md"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-md" style="min-width: 20px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <div style="display: flex; flex-direction: column;">
                    @foreach($errors->all() as $error)
                        <span>{{ $error }}</span>
                    @endforeach
                </div>
            </div>
        @endif

        @yield('content')
    </div>

</body>
</html>
