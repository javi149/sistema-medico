<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

        /* Bento Grid Hero */
        .bento-hero {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .bento-card {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            position: relative;
            overflow: hidden;
        }

        .bento-card.main-hero {
            background: #f1f5f9;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .badge-tag {
            display: inline-block;
            background: var(--secondary);
            color: #854d0e;
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 700;
            margin-bottom: 20px;
            width: fit-content;
        }

        h1 {
            font-size: 3rem;
            line-height: 1.1;
            margin: 0 0 20px 0;
            color: var(--text-main);
            letter-spacing: -0.02em;
        }

        .hero-desc {
            color: var(--text-muted);
            font-size: 1.1rem;
            margin-bottom: 30px;
            max-width: 90%;
        }

        .hero-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .hero-image-card {
            padding: 0;
            background: #e2e8f0;
        }

        .hero-image-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 24px;
        }

        /* Mini Bento Cards */
        .bento-mini-grid {
            display: grid;
            grid-template-columns: 1fr 1.5fr 1fr;
            gap: 20px;
            margin-bottom: 60px;
        }

        .mini-card {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .mini-card.highlight {
            background: #e0f2fe;
        }

        .mini-card h3 {
            margin: 0 0 10px 0;
            font-size: 1.2rem;
        }

        .mini-card p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin: 0;
        }

        .pill-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 15px;
        }

        .pill {
            background: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid var(--border-color);
        }

        /* Specialties Section */
        .section-title {
            font-size: 2rem;
            margin-bottom: 30px;
            text-align: center;
        }

        .specialties-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 60px;
        }

        .specialty-card {
            background: var(--bg-card);
            padding: 25px 20px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .specialty-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);
        }

        .specialty-icon {
            width: 40px;
            height: 40px;
            background: #ccfbf1;
            color: var(--primary);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .specialty-name {
            font-weight: 600;
            font-size: 0.95rem;
        }

        /* Doctors Section */
        .doctors-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 80px;
        }

        .doctor-card {
            background: var(--bg-card);
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .doctor-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: #e2e8f0;
            margin: 0 auto 15px auto;
            overflow: hidden;
            border: 4px solid white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }

        .doctor-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .doctor-name {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 5px;
        }

        .doctor-spec {
            color: var(--primary);
            font-size: 0.85rem;
            font-weight: 600;
            background: #ccfbf1;
            padding: 4px 10px;
            border-radius: 20px;
            display: inline-block;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .bento-hero, .bento-mini-grid {
                grid-template-columns: 1fr;
            }
            .specialties-grid, .doctors-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            h1 { font-size: 2.5rem; }
            .hero-image-card { min-height: 300px; }
        }

        @media (max-width: 768px) {
            .nav-links { display: none; }
            .specialties-grid, .doctors-grid {
                grid-template-columns: 1fr;
            }
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
            <a href="#especialidades">Especialidades</a>
            <a href="#doctores">Nuestros Médicos</a>
            <a href="#contacto">Contacto</a>
            <span style="color: var(--border-color)">|</span>
            <span style="font-weight: 600; font-size: 0.95rem;">📞 +56 9 1234 5678</span>
        </div>
        <div>
            @if (Route::has('login'))
                @auth
                    <a href="{{ url('/dashboard') }}" class="btn-primary">Ir a mi Panel</a>
                @else
                    <a href="{{ route('login') }}" style="margin-right: 15px; color: var(--primary); font-weight: 600; text-decoration: none;">Portal Paciente</a>
                    <a href="{{ route('citas.create') }}" class="btn-primary">Agendar Cita</a>
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-outline" style="margin-left: 10px; border: none; padding: 10px 5px; display: none;">Registro</a>
                    @endif
                @endauth
            @endif
        </div>
    </nav>

    <!-- Bento Hero -->
    <div class="bento-hero">
        <div class="bento-card main-hero">
            <span class="badge-tag">✦ Medicina para todos</span>
            <h1>Programas integrales para la salud de toda la familia</h1>
            <p class="hero-desc">Centro médico "MediCore". Profesionales de primer nivel, métodos modernos y programas individuales para el cuidado de tu salud y la de los tuyos.</p>
            <div class="hero-actions">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-primary">Agendar Ahora</a>
                    @else
                        <a href="{{ route('citas.create') }}" class="btn-primary">Agendar Ahora</a>
                    @endauth
                @endif
                <a href="#especialidades" class="btn-outline">Catálogo de Servicios &rarr;</a>
            </div>
            
            <div style="margin-top: 40px; display: flex; align-items: center; gap: 15px;">
                <div style="display: flex; margin-left: 10px;">
                    <!-- Avatares falsos apilados -->
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #cbd5e1; border: 2px solid white; margin-left: -10px; z-index: 3;"></div>
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #94a3b8; border: 2px solid white; margin-left: -10px; z-index: 2;"></div>
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: #64748b; border: 2px solid white; margin-left: -10px; z-index: 1;"></div>
                </div>
                <div>
                    <span style="font-weight: 800; font-size: 1.2rem;">> 25</span>
                    <p style="margin: 0; font-size: 0.8rem; color: var(--text-muted); line-height: 1.2;">especialistas de la salud<br>a tu disposición</p>
                </div>
            </div>
        </div>

        <div class="bento-card hero-image-card">
            <!-- Imagen generada dinámicamente -->
            <img src="{{ asset('images/hero_doctors.png') }}" alt="Equipo médico" loading="lazy">
            
            <div style="position: absolute; bottom: 20px; right: 20px; background: white; padding: 10px 20px; border-radius: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 10px; font-weight: 700;">
                <span style="color: #ef4444;">📍</span> 4.8 
                <span style="color: #eab308;">★★★★★</span>
            </div>
        </div>
    </div>

    <!-- Mini Bento Cards -->
    <div class="bento-mini-grid">
        <div class="mini-card">
            <h3>Enfoque personal con atención y confianza</h3>
            <p>Consultas y apoyo continuo de primer nivel después de cada atención médica.</p>
        </div>
        <div class="mini-card highlight">
            <h3>Nuestras áreas principales:</h3>
            <div class="pill-list">
                @foreach(isset($specialties) ? $specialties->take(6) : [] as $spec)
                    <div class="pill">+ {{ $spec->name }}</div>
                @endforeach
            </div>
        </div>
        <div class="mini-card">
            <h3>Atención médica altamente calificada</h3>
            <p>Perfeccionamiento constante de nuestros especialistas y tecnología médica de punta.</p>
        </div>
    </div>

    <!-- Specialties Catalog -->
    <div id="especialidades">
        <div style="text-align: center; margin-bottom: 10px;">
            <span class="badge-tag">✦ Servicios</span>
        </div>
        <h2 class="section-title">Amplio espectro de servicios médicos</h2>
        
        <div class="specialties-grid" id="specialties-grid">
            @if(isset($specialties))
                <div class="specialty-card" style="cursor: pointer;" onclick="filterDoctors('')">
                    <div class="specialty-icon" style="background: var(--primary); color: white;">
                        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    </div>
                    <div class="specialty-name">Ver Todos</div>
                </div>
                @foreach($specialties as $specialty)
                    <div class="specialty-card" style="cursor: pointer;" onclick="filterDoctors('{{ $specialty->id }}')">
                        <div class="specialty-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2" fill="none"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                        </div>
                        <div class="specialty-name">{{ $specialty->name }}</div>
                        <div style="margin-left: auto; color: var(--border-color);">↗</div>
                    </div>
                @endforeach
            @else
                <p>No hay especialidades cargadas.</p>
            @endif
        </div>
    </div>



    <!-- Doctors Section -->
    <div id="doctores">
        <div style="text-align: center; margin-bottom: 10px;">
            <span class="badge-tag">✦ Profesionales</span>
        </div>
        <h2 class="section-title" style="margin-bottom: 10px;">Doctores del Centro Médico</h2>
        <p style="text-align: center; color: var(--text-muted); margin-bottom: 40px;">Especialistas altamente calificados con años de experiencia clínica.</p>
        
        <div class="doctors-grid" id="doctors-grid">
            @if(isset($doctors))
                @foreach($doctors as $doctor)
                    @php 
                        $docSpecId = ($doctor->professionalProfile && $doctor->professionalProfile->specialties->count() > 0) ? $doctor->professionalProfile->specialties->first()->id : ''; 
                        $isFemale = str_contains($doctor->name, 'Dra.');
                        
                        // Volveremos a usar las fotos IA de altísima calidad que sí parecen doctores.
                        // Usaremos 12 fotos IA distintas (6 mujeres, 6 hombres) para minimizar la repetición.
                        $photoId = ($doctor->id % 6) + 1;
                        $genderPrefix = $isFemale ? 'ai_female_' : 'ai_male_';
                        $photoUrl = asset('images/doctors/' . $genderPrefix . $photoId . '.png');
                    @endphp
                    <div class="doctor-card doctor-item" data-specialty="{{ $docSpecId }}">
                        <div class="doctor-avatar">
                            <img src="{{ $photoUrl }}" alt="Doctor" style="object-fit: cover;" loading="lazy">
                        </div>
                        <div class="doctor-spec">
                            @if($doctor->professionalProfile && $doctor->professionalProfile->specialties->count() > 0)
                                {{ $doctor->professionalProfile->specialties->first()->name }}
                            @else
                                Médico General
                            @endif
                        </div>
                        <div class="doctor-name">{{ $doctor->name }}</div>
                    </div>
                @endforeach
            @else
                <p>No hay doctores cargados.</p>
            @endif
        </div>
    </div>

    <!-- Footer -->
    <footer id="contacto" style="border-top: 1px solid var(--border-color); padding: 40px 0; margin-top: 40px; display: flex; justify-content: space-between; align-items: center; color: var(--text-muted);">
        <div>
            <div class="logo" style="margin-bottom: 10px; font-size: 1.2rem;">
                <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" stroke-width="2.5" fill="none"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                MediCore
            </div>
            <p style="font-size: 0.9rem;">&copy; 2026 Clínica Médica MediCore. Todos los derechos reservados.</p>
        </div>
        <div style="text-align: right;">
            <p style="margin: 0; font-weight: 600; color: var(--text-main);">Contacto: +56 9 1234 5678</p>
            <p style="margin: 5px 0 10px 0; font-size: 0.9rem;">contacto@medicore.cl</p>
            <a href="{{ route('login') }}" style="font-size: 0.85rem; color: var(--primary); text-decoration: none; font-weight: 600;">Acceso Doctores &rarr;</a>
        </div>
    </footer>

</div>

<script>
    function filterDoctors(specialtyId) {
        // Desplazarse suavemente hacia los doctores
        document.getElementById('doctores').scrollIntoView({ behavior: 'smooth' });
        
        const doctors = document.querySelectorAll('.doctor-item');
        let visibleCount = 0;
        
        doctors.forEach(doc => {
            if (specialtyId === '' || doc.getAttribute('data-specialty') === specialtyId) {
                doc.style.display = 'block';
                visibleCount++;
            } else {
                doc.style.display = 'none';
            }
        });

        // Si no hay resultados, podríamos mostrar un mensaje, pero para el prototipo está bien.
    }
</script>
</body>
</html>