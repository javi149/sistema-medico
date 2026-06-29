<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Cupo Disponible en Lista de Espera!</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            color: #334155;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header {
            background-color: #0f766e;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            padding: 30px;
        }
        .content p {
            line-height: 1.6;
            margin-bottom: 20px;
        }
        .ticket {
            background-color: #f0fdfa;
            border-left: 4px solid #0f766e;
            padding: 20px;
            margin-bottom: 25px;
            border-radius: 4px;
        }
        .ticket div {
            margin-bottom: 10px;
        }
        .ticket div:last-child {
            margin-bottom: 0;
        }
        .label {
            font-weight: bold;
            color: #115e59;
            display: inline-block;
            width: 120px;
        }
        .value {
            font-weight: 600;
            color: #0f172a;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
        .btn {
            display: inline-block;
            background-color: #0f766e;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-weight: bold;
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #115e59;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Se ha liberado un cupo para ti!</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $waitlist->patient->name }}</strong>,</p>
            <p>Te escribimos porque estabas en nuestra lista de espera. ¡Buenas noticias! Se ha liberado un bloque de atención con los siguientes detalles:</p>
            
            <div class="ticket">
                <div>
                    <span class="label">Especialidad:</span>
                    <span class="value">{{ $specialtyName }}</span>
                </div>
                <div>
                    <span class="label">Profesional:</span>
                    <span class="value">{{ $doctorName }}</span>
                </div>
                <div>
                    <span class="label">Fecha y Hora:</span>
                    <span class="value">{{ \Carbon\Carbon::parse($slotTime)->format('d/m/Y - H:i') }} hrs</span>
                </div>
            </div>
            
            <p>Si deseas reservar esta hora, te recomendamos ingresar de inmediato a nuestro sistema, ya que los cupos liberados se asignan por orden de reserva.</p>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ route('citas.create') }}" class="btn">Reservar mi hora ahora</a>
            </div>
        </div>
        <div class="footer">
            <p>Este es un correo automático de notificación de lista de espera, por favor no respondas a este correo.</p>
            <p>&copy; {{ date('Y') }} Clínica MediCore. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
