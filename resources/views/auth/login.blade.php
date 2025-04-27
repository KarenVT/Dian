<x-guest-layout>
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-gray-900 mb-1">Bienvenido(a)</h1>
        <p class="text-sm text-gray-600">Inicia sesión con tus credenciales</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <!-- Validation Errors -->
    <x-auth-validation-errors class="mb-4" :errors="$errors" />

    <form method="POST" action="{{ route('login') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
            <input id="email" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" type="email" name="email" value="{{ old('email') }}" required autofocus />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
            <input id="password" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" type="password" name="password" required autocomplete="current-password" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="remember_me" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" name="remember">
                <label for="remember_me" class="ml-2 block text-sm text-gray-700">Recordarme</label>
            </div>
            
            @if (Route::has('password.request'))
                <a class="text-sm text-indigo-600 hover:text-indigo-900" href="{{ route('password.request') }}">
                    ¿Olvidaste tu contraseña?
                </a>
            @endif
        </div>

        <div>
            <button type="submit" class="auth-button">Iniciar sesión</button>
        </div>

        <div class="text-center mt-6">
            <p class="text-sm text-gray-600">¿No tienes cuenta? <a href="{{ route('register') }}" class="auth-link">Regístrate</a></p>
        </div>
    </form>
</x-guest-layout>
