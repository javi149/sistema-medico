<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Prueba de Especialidades</title>
</head>
<body>
    <h1>Módulo de Especialidades - Vista de Prueba</h1>
    
    @if(session('success'))
        <p style="color: green;">{{ session('success') }}</p>
    @endif

    <ul>
        @foreach($especialidades as $especialidad)
            <li><strong>{{ $especialidad->name }}</strong>: {{ $especialidad->description }}</li>
        @endforeach
    </ul>
</body>
</html>