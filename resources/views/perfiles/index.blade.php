@extends('layouts.app')

@section('content')

    <div class="top-bar">
        <div>
            <h1 class="page-title">Perfiles Profesionales</h1>
            <p class="page-subtitle">Administre el personal médico y sus especialidades.</p>
        </div>
        <a href="{{ route('perfiles.create') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-sm"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
            Asignar Perfil a Médico
        </a>
    </div>

    <div class="table-container">
        @if($perfiles->isEmpty())
            <div style="padding: 40px; text-align: center;">
                <p style="color: var(--text-muted);">No hay perfiles médicos registrados.</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Doctor(a)</th>
                        <th>Email</th>
                        <th>Duración Consulta</th>
                        <th>Especialidades</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($perfiles as $perfil)
                        <tr>
                            <td style="font-weight: 600; color: var(--text-main);">{{ $perfil->user->name }}</td>
                            <td style="color: var(--text-muted);">{{ $perfil->user->email }}</td>
                            <td style="color: var(--text-muted);">{{ $perfil->consultation_duration_minutes }} min</td>
                            <td>
                                @foreach($perfil->specialties as $especialidad)
                                    <span class="badge badge-info" style="margin-right: 5px; margin-bottom: 5px; display: inline-block;">{{ $especialidad->name }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

@endsection
