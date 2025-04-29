<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            body {
                font-family: 'Poppins', sans-serif;
            }
            .pricing-card {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .pricing-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            }
            .btn-primary {
                @apply bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-5 rounded-lg transition-all shadow-md hover:shadow-lg;
            }
            .btn-secondary {
                @apply bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-5 rounded-lg transition-all shadow-md hover:shadow-lg;
            }
            .btn-outline {
                @apply border border-blue-600 text-blue-600 hover:bg-blue-50 font-semibold py-2.5 px-5 rounded-lg transition-all;
            }
            .plan-tab {
                @apply py-2 px-4 font-medium rounded-md cursor-pointer;
            }
            .plan-tab-active {
                @apply bg-blue-600 text-white;
            }
            .plan-tab-inactive {
                @apply bg-gray-200 text-gray-700 hover:bg-gray-300;
            }
            .feature-icon {
                @apply h-6 w-6 text-blue-500 mb-1;
            }
            .feature-item {
                @apply flex flex-col items-center text-center;
            }
            .footer-link {
                @apply text-gray-600 hover:text-blue-600 transition-colors;
            }
        </style>
    </head>
    <body class="antialiased bg-gray-50">
        <!-- Encabezado -->
        <header class="bg-white shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <img src="{{ asset('img/Logo Paychex.png') }}" alt="Paychex" class="h-10">
                    </div>
                    <!-- Navegación -->
                    <div class="hidden md:flex space-x-1">
            @if (Route::has('login'))
                            <div class="flex items-center space-x-3">
                    @auth
                                    <a href="{{ url('/dashboard') }}" class="btn-outline">Panel de Control</a>
                    @else
                                    <a href="{{ route('login') }}" class="btn-outline">Iniciar Sesión</a>

                        @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="btn-primary">Registrarse</a>
                        @endif
                    @endauth
                </div>
            @endif
                    </div>
                    <!-- Menú móvil -->
                    <div class="md:hidden">
                        <button type="button" class="text-gray-500 hover:text-gray-700 focus:outline-none">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero -->
        <section class="py-10 lg:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="lg:flex lg:items-center lg:justify-between">
                    <div class="lg:w-1/2 mb-10 lg:mb-0">
                        <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 mb-4 leading-tight">
                            <span class="text-blue-600">Controla tus finanzas</span> con nuestro Software de facturación electrónica
                        </h1>
                        <p class="text-lg md:text-xl text-gray-600 mb-8">
                            Con un <span class="font-semibold text-blue-600">acceso rápido</span> y de <span class="font-semibold text-blue-600">confianza</span> con <span class="font-semibold text-blue-600">Paychex</span>
                        </p>
                    </div>
                    <div class="lg:w-1/2 flex justify-center">
                        <img src="{{ asset('img/Logo de Dian.png') }}" alt="DIAN" class="w-3/4 max-w-md">
                    </div>
                </div>
            </div>
        </section>

        <!-- Planes -->
        <section class="py-12 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Planes de acuerdo a tu capacidad operativa y presupuesto</h2>
                    <p class="text-lg text-gray-600 max-w-2xl mx-auto">Elige el plan que mejor se adapte a tus necesidades y comienza a disfrutar de todos los beneficios de nuestra plataforma.</p>
                </div>
                
                <!-- Tabs para cambiar entre planes mensuales y anuales -->
                <div class="flex justify-center space-x-4 mb-8">
                    <button class="plan-tab plan-tab-active" id="mensual-tab">Mensual</button>
                    <button class="plan-tab plan-tab-inactive" id="anual-tab">Anual</button>
                </div>

                <!-- Planes mensuales -->
                <div id="planes-mensuales" class="grid md:grid-cols-2 gap-8">
                    <!-- Plan Persona Mensual -->
                    <div class="pricing-card bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                        <div class="bg-blue-600 text-white py-4 px-6">
                            <h3 class="text-xl font-bold">Persona</h3>
                        </div>
                        <div class="p-6">
                            <div class="text-center mb-6">
                                <p class="text-gray-500 mb-2">Características:</p>
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="feature-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="feature-icon" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6z" clip-rule="evenodd" />
                                        </svg>
                                        <span>40 Documentos</span>
                                    </div>
                                    <div class="feature-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="feature-icon" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                                        </svg>
                                        <span>4 Usuarios</span>
                                    </div>
                                </div>
                                <div class="flex justify-center mb-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <p class="text-gray-500 mb-1">Certificado Digital</p>
                            </div>
                            <div class="text-center">
                                <span class="text-3xl font-bold text-gray-900">$70.000</span>
                                <span class="text-gray-500">/mes</span>
                            </div>
                            <div class="mt-6">
                                <a href="{{ route('register') }}" class="block w-full py-3 px-4 text-center bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">Comenzar ahora</a>
                            </div>
                        </div>
                            </div>

                    <!-- Plan Empresarial Mensual -->
                    <div class="pricing-card bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                        <div class="bg-green-600 text-white py-4 px-6">
                            <h3 class="text-xl font-bold">Empresarial</h3>
                        </div>
                        <div class="p-6">
                            <div class="text-center mb-6">
                                <p class="text-gray-500 mb-2">Características:</p>
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="feature-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="feature-icon" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6z" clip-rule="evenodd" />
                                        </svg>
                                        <span>6.000 Documentos</span>
                                    </div>
                                    <div class="feature-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="feature-icon" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                            </svg>
                                        <span>Usuarios ilimitados</span>
                                    </div>
                                </div>
                                <div class="flex justify-center mb-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <p class="text-gray-500 mb-1">Certificado Digital</p>
                            </div>
                            <div class="text-center">
                                <span class="text-3xl font-bold text-gray-900">$150.000</span>
                                <span class="text-gray-500">/mes</span>
                            </div>
                            <div class="mt-6">
                                <a href="{{ route('register') }}" class="block w-full py-3 px-4 text-center bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">Comenzar ahora</a>
                            </div>
                        </div>
                    </div>
                            </div>

                <!-- Planes anuales (ocultos inicialmente) -->
                <div id="planes-anuales" class="grid md:grid-cols-2 gap-8 hidden">
                    <!-- Plan Persona Anual -->
                    <div class="pricing-card bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                        <div class="bg-blue-600 text-white py-4 px-6">
                            <h3 class="text-xl font-bold">Persona</h3>
                        </div>
                        <div class="p-6">
                            <div class="text-center mb-6">
                                <p class="text-gray-500 mb-2">Características:</p>
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="feature-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="feature-icon" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6z" clip-rule="evenodd" />
                                        </svg>
                                        <span>700 Documentos</span>
                                    </div>
                                    <div class="feature-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="feature-icon" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                            </svg>
                                        <span>16 Usuarios</span>
                                    </div>
                                </div>
                                <div class="flex justify-center mb-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <p class="text-gray-500 mb-1">Certificado Digital</p>
                            </div>
                            <div class="text-center">
                                <span class="text-3xl font-bold text-gray-900">$700.000</span>
                                <span class="text-gray-500">/año</span>
                            </div>
                            <div class="mt-6">
                                <a href="{{ route('register') }}" class="block w-full py-3 px-4 text-center bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">Comenzar ahora</a>
                            </div>
                        </div>
                            </div>

                    <!-- Plan Empresarial Anual -->
                    <div class="pricing-card bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
                        <div class="bg-green-600 text-white py-4 px-6">
                            <h3 class="text-xl font-bold">Empresarial</h3>
                        </div>
                        <div class="p-6">
                            <div class="text-center mb-6">
                                <p class="text-gray-500 mb-2">Características:</p>
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="feature-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="feature-icon" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 0v12h8V4H6z" clip-rule="evenodd" />
                                        </svg>
                                        <span>10.000 Documentos</span>
                                    </div>
                                    <div class="feature-item">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="feature-icon" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z" />
                            </svg>
                                        <span>Usuarios ilimitados</span>
                                    </div>
                                </div>
                                <div class="flex justify-center mb-6">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <p class="text-gray-500 mb-1">Certificado Digital</p>
                            </div>
                            <div class="text-center">
                                <span class="text-3xl font-bold text-gray-900">$1.500.000</span>
                                <span class="text-gray-500">/año</span>
                            </div>
                            <div class="mt-6">
                                <a href="{{ route('register') }}" class="block w-full py-3 px-4 text-center bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">Comenzar ahora</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Certificaciones -->
        <section class="py-10 bg-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900">Proveedor Tecnológico Autorizado por la DIAN</h2>
                    <p class="text-lg text-gray-600 mt-2">Resolución 5536</p>
                </div>
                <div class="flex flex-wrap justify-center items-center gap-8">
                    <img src="{{ asset('img/Logo de Dian.png') }}" alt="DIAN" class="h-16">
                    <img src="{{ asset('img/Logo de Certificado.png') }}" alt="Certificado" class="h-16">
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-gray-800 text-white pt-12 pb-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Contacto</h3>
                        <p class="text-gray-400 mb-2">Email: FacturaElectronica@Paychex.com</p>
                        <p class="text-gray-400">Medellín - Antioquia</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Certificaciones</h3>
                        <p class="text-gray-400 mb-2">Certificación ISO 27001</p>
                        <p class="text-gray-400">Proveedor Tecnológico Autorizado por la DIAN Resolución 5536</p>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold mb-4">Enlaces</h3>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="#" class="hover:text-blue-400 transition-colors">Términos y Condiciones</a></li>
                            <li><a href="#" class="hover:text-blue-400 transition-colors">Política de Cookies</a></li>
                            <li><a href="#" class="hover:text-blue-400 transition-colors">Política de Tratamiento de datos personales</a></li>
                            <li><a href="#" class="hover:text-blue-400 transition-colors">Política de Seguridad de la información</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-700 pt-6 text-center text-gray-400 text-sm">
                    <p>&copy; {{ date('Y') }} Paychex. Todos los derechos reservados.</p>
                </div>
            </div>
        </footer>

        <script>
            // Cambiar entre planes mensuales y anuales
            document.addEventListener('DOMContentLoaded', function() {
                const mensualTab = document.getElementById('mensual-tab');
                const anualTab = document.getElementById('anual-tab');
                const planesMensuales = document.getElementById('planes-mensuales');
                const planesAnuales = document.getElementById('planes-anuales');
                
                mensualTab.addEventListener('click', function() {
                    planesMensuales.classList.remove('hidden');
                    planesAnuales.classList.add('hidden');
                    mensualTab.classList.remove('plan-tab-inactive');
                    mensualTab.classList.add('plan-tab-active');
                    anualTab.classList.remove('plan-tab-active');
                    anualTab.classList.add('plan-tab-inactive');
                });
                
                anualTab.addEventListener('click', function() {
                    planesMensuales.classList.add('hidden');
                    planesAnuales.classList.remove('hidden');
                    mensualTab.classList.remove('plan-tab-active');
                    mensualTab.classList.add('plan-tab-inactive');
                    anualTab.classList.remove('plan-tab-inactive');
                    anualTab.classList.add('plan-tab-active');
                });
            });
        </script>
    </body>
</html>
