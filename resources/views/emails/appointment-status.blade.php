<!DOCTYPE html>
<html>
<head>
    <title>Actualización de Cita - MediCore</title>
</head>
<body style="background-color: #0d1117; color: #ffffff; font-family: Arial, sans-serif; padding: 20px;">
    <h2 style="color: #58a6ff;">Notificación MediCore</h2>
    <p>Estimado Paciente, le informamos que su cita médica ha sido <strong>{{ $actionType }}</strong> exitosamente.</p>
    
    <div style="background-color: #161b22; padding: 15px; border-radius: 5px; margin: 20px 0; border: 1px solid #30363d;">
        <p><strong>Detalles de la Cita:</strong></p>
        <ul>
            <li><strong>Fecha y Hora:</strong> {{ \Carbon\Carbon::parse($appointment->start_datetime)->format('d-m-Y H:i') }}</li>
            <li><strong>Estado Actual:</strong> {{ $appointment->status }}</li>
        </ul>
    </div>
    
    <p>Para más detalles, ingrese a su pantalla de usuario.</p>
</body>
</html>