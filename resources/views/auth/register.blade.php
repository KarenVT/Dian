<x-guest-layout>
    <div class="mb-8 text-center">
        <h1 class="text-2xl font-bold text-gray-900 mb-1">Crea tu cuenta</h1>
        <p class="text-sm text-gray-600">Completa el formulario para registrarte</p>
    </div>

    <!-- Validation Errors -->
    <x-auth-validation-errors class="mb-4" :errors="$errors" />

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <!-- Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
            <input id="name" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" type="text" name="name" value="{{ old('name') }}" required autofocus />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
            <input id="email" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" type="email" name="email" value="{{ old('email') }}" required />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
            <input id="password" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" type="password" name="password" required autocomplete="new-password" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
            <input id="password_confirmation" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" type="password" name="password_confirmation" required />
        </div>

        <div class="mt-6">
            <button type="submit" class="auth-button">Registrarse</button>
        </div>

        <div class="text-center mt-6">
            <p class="text-sm text-gray-600">¿Ya tienes cuenta? <a href="{{ route('login') }}" class="auth-link">Inicia sesión</a></p>
        </div>
    </form>
</x-guest-layout>
