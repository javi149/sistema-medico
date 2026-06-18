@extends('layouts.app')

@section('content')
<div style="display: flex; justify-content: center; align-items: center; min-height: 70vh;">
    <div class="card" style="max-width: 600px; width: 100%; text-align: center; padding: 40px;">
        <div style="display: flex; justify-content: center; margin-bottom: 20px;">
            <div style="background-color: #d1fae5; color: #059669; width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
        </div>
        
        <h1 style="color: var(--text-main); margin-bottom: 10px;">¡Reserva Exitosa!</h1>
        <p style="color: var(--text-muted); font-size: 1.1rem; margin-bottom: 30px;">
            Tu cita médica ha sido confirmada y registrada en nuestro sistema.
        </p>

        <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 25px; text-align: left; margin-bottom: 30px;">
            <h3 style="margin-top: 0; color: var(--primary); border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; margin-bottom: 15px;">Detalles de la Cita</h3>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <span style="display: block; font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Paciente</span>
                    <span style="font-size: 1.05rem; color: var(--text-main); font-weight: 500;">{{ $cita->patient->name }}</span>
                </div>
                <div>
                    <span style="display: block; font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">RUT</span>
                    <span style="font-size: 1.05rem; color: var(--text-main); font-weight: 500;">{{ $cita->patient->rut }}</span>
                </div>
                <div>
                    <span style="display: block; font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Profesional</span>
                    <span style="font-size: 1.05rem; color: var(--text-main); font-weight: 500;">{{ $cita->professionalProfile->user->name }}</span>
                </div>
                <div>
                    <span style="display: block; font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Especialidad</span>
                    <span style="font-size: 1.05rem; color: var(--text-main); font-weight: 500;">{{ $cita->specialty->name }}</span>
                </div>
                <div style="grid-column: span 2; background-color: white; padding: 10px; border-radius: 6px; border-left: 4px solid var(--primary); margin-top: 5px;">
                    <span style="display: block; font-size: 0.85rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;">Fecha y Hora</span>
                    <span style="font-size: 1.2rem; color: var(--primary); font-weight: 700;">{{ \Carbon\Carbon::parse($cita->start_datetime)->format('d \d\e F, Y - H:i') }} hrs</span>
                </div>
            </div>
        </div>

        <div style="background-color: #eff6ff; color: #1e40af; padding: 15px; border-radius: 6px; margin-bottom: 30px; display: flex; align-items: center; gap: 10px; text-align: left;">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
            <span style="font-size: 0.95rem;">Hemos enviado un comprobante a tu correo electrónico <strong>{{ $cita->patient->email }}</strong> con estos detalles.</span>
        </div>

        <div style="display: flex; gap: 15px; justify-content: center;">
            <a href="{{ url('/') }}" class="btn btn-secondary" style="padding: 10px 20px;">Volver al Inicio</a>
            @auth
                @if(auth()->user()->hasRole('paciente'))
                    <a href="{{ route('citas.index') }}" class="btn btn-primary" style="padding: 10px 20px;">Ver Mis Citas</a>
                @endif
            @endauth
        </div>
    </div>
</div>
@endsection
