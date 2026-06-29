@extends('layouts.app')

@section('content')

    <div class="top-bar">
        <div>
            <h1 class="page-title">Gestión de Lista de Espera</h1>
            <p class="page-subtitle">Aprobación de disponibilidad de horas y notificación automática.</p>
        </div>
    </div>

    <!-- TABLE -->
    <h3 style="font-weight: 700; font-size: 1.25rem; margin-bottom: 15px; color: var(--text-main);">Pacientes en Espera</h3>
    
    <div class="table-container">
        @if($waitlists->isEmpty())
            <div style="padding: 40px; text-align: center;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 48px; height: 48px; color: var(--border-color); margin-bottom: 15px;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
                <p style="color: var(--text-muted); font-weight: 500;">No hay pacientes registrados en la lista de espera actualmente.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Fecha Registro</th>
                        <th>Paciente</th>
                        <th>Especialidad Solicitada</th>
                        <th>Médico Preferido</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($waitlists as $waitlist)
                        <tr id="row-{{ $waitlist->id }}">
                            <td style="font-weight: 600; color: var(--text-muted);">
                                {{ \Carbon\Carbon::parse($waitlist->created_at)->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <div style="font-weight: 700; color: var(--text-main);">{{ $waitlist->patient->name }}</div>
                                <div style="font-size: 0.8rem; color: var(--text-muted);">RUT: {{ $waitlist->patient->rut ?? 'N/A' }} | {{ $waitlist->patient->email }}</div>
                            </td>
                            <td>
                                <span class="badge badge-info">
                                    {{ $waitlist->specialty->name ?? 'Cualquiera' }}
                                </span>
                            </td>
                            <td>
                                {{ $waitlist->professionalProfile->user->name ?? 'Cualquier Médico' }}
                            </td>
                            <td style="text-align: right;">
                                <button type="button" class="btn btn-primary" style="padding: 6px 14px; font-size: 0.85rem;" onclick="toggleForm({{ $waitlist->id }})">
                                    Aprobar Disponibilidad
                                </button>
                            </td>
                        </tr>
                        <tr id="form-row-{{ $waitlist->id }}" style="display: none; background-color: #f8fafc;">
                            <td colspan="5" style="padding: 20px; border-bottom: 1px solid var(--border-color);">
                                <div style="background: white; border: 1px solid var(--border-color); border-radius: 12px; padding: 20px; box-shadow: var(--shadow-sm);">
                                    <h4 style="margin: 0 0 15px 0; color: var(--primary); font-size: 1rem; font-weight: 700;">Ingresar Disponibilidad para {{ $waitlist->patient->name }}</h4>
                                    
                                    <form action="{{ route('admin.waitlist.approve', $waitlist->id) }}" method="POST">
                                        @csrf
                                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 15px;">
                                            <div class="form-group" style="margin: 0;">
                                                <label class="form-label" style="font-size: 0.8rem;">Seleccionar Médico</label>
                                                <select name="professional_profile_id" class="form-control" required style="padding: 10px 12px; font-size: 0.9rem;">
                                                    <option value="">-- Seleccionar Profesional --</option>
                                                    @if(isset($doctorsBySpecialty[$waitlist->specialty_id]))
                                                        @foreach($doctorsBySpecialty[$waitlist->specialty_id] as $doctor)
                                                            <option value="{{ $doctor->id }}" {{ ($waitlist->professional_profile_id == $doctor->id) ? 'selected' : '' }}>
                                                                Dr(a). {{ $doctor->user->name }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                            
                                            <div class="form-group" style="margin: 0;">
                                                <label class="form-label" style="font-size: 0.8rem;">Fecha y Hora Liberada</label>
                                                <input type="datetime-local" name="start_datetime" class="form-control" required style="padding: 10px 12px; font-size: 0.9rem;">
                                            </div>
                                        </div>
                                        
                                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                            <button type="button" class="btn btn-outline" style="padding: 8px 16px; font-size: 0.85rem;" onclick="toggleForm({{ $waitlist->id }})">
                                                Cancelar
                                            </button>
                                            <button type="submit" class="btn btn-primary" style="padding: 8px 18px; font-size: 0.85rem;">
                                                Aprobar y Notificar Paciente
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <script>
        function toggleForm(id) {
            const formRow = document.getElementById('form-row-' + id);
            if (formRow.style.display === 'none') {
                formRow.style.display = 'table-row';
            } else {
                formRow.style.display = 'none';
            }
        }
    </script>

@endsection
