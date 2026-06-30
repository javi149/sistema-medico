<x-guest-layout>
    <h2>Crear cuenta</h2>
    <p class="auth-subtitle">Completa el formulario para registrarte como paciente.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label for="name">Nombre</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            @error('name')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="rut">RUT</label>
            <input id="rut" type="text" name="rut" value="{{ old('rut') }}" required maxlength="12" placeholder="12.345.678-9">
            @error('rut')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmar Contraseña</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
            @error('password_confirmation')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="auth-form-footer-center">
            <a href="{{ route('login') }}">¿Ya estás registrado?</a>
            <button type="submit" class="btn-submit" style="margin-left: 12px;">REGISTRARSE</button>
        </div>
    </form>
</x-guest-layout>