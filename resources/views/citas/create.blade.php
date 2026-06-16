@extends('layouts.app')

@section('content')

    <div class="top-bar">
        <div>
            <h1 class="page-title">Agendar Hora Médica</h1>
            <p class="page-subtitle">Complete el formulario para solicitar una consulta médica.</p>
        </div>
    </div>

    <div class="card" style="max-width: 600px;">
        <form action="{{ route('citas.store') }}" method="POST">
            @csrf

            @if($errors->any())
                <div class="alert alert-error">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-md"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-md"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="icon-md"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    {{ session('success') }}
                </div>
            @endif

            <div class="form-group">
                <label class="form-label">Especialidad y Médico</label>
                <select name="professional_profile_id" class="form-control" required>
                    <option value="">Seleccione a su doctor...</option>
                    @foreach($perfiles as $perfil)
                        <option value="{{ $perfil->id }}" {{ old('professional_profile_id') == $perfil->id ? 'selected' : '' }}>
                            Dr(a). {{ $perfil->user->name }} - 
                            @foreach($perfil->specialties as $esp)
                                {{ $esp->name }}@if(!$loop->last), @endif
                            @endforeach
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label class="form-label">Fecha y Hora Preferida</label>
                <input type="datetime-local" name="start_datetime" class="form-control" required value="{{ old('start_datetime') }}">
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="submit" class="btn btn-primary">Confirmar Agendamiento</button>
                <a href="{{ route('citas.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>

@endsection