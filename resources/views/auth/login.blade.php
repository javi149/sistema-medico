<x-guest-layout>
    <h2>Bienvenido de nuevo</h2>
    <p class="auth-subtitle">Ingresa tus credenciales para acceder al sistema.</p>

    @if (session('status'))
        <div style="background:#d1fae5; color:#065f46; padding:10px 14px; border-radius:10px; font-size:0.88rem; margin-bottom:1rem;">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">Correo Electrónico</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <div class="remember-row">
            <input id="remember_me" type="checkbox" name="remember">
            <label for="remember_me">Recordarme</label>
        </div>

        <div class="auth-form-footer">
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
            @endif
            <button type="submit" class="btn-submit">INICIAR SESIÓN</button>
        </div>
    </form>

    <div class="auth-form-footer-center" style="margin-top:1.5rem;">
        <span style="font-size:0.88rem; color:#64748b;">¿No tienes cuenta?</span>
        <a href="{{ route('register') }}" style="margin-left:6px;">Regístrate aquí</a>
    </div>
</x-guest-layout>