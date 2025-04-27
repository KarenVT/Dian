<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @php use Illuminate\Support\Facades\Route; @endphp
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-50" x-data="{ sidebarOpen: false }">
            <!-- Navbar optimizado -->
            <nav class="bg-white border-b border-gray-100 shadow-sm fixed top-0 inset-x-0 z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-12">
                        <div class="flex items-center">
                            <!-- Botón Mobile (Hamburguesa) -->
                            <div class="flex items-center sm:hidden">
                                <button @click="sidebarOpen = ! sidebarOpen" class="inline-flex items-center justify-center p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-700 transition duration-150 ease-in-out">
                                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                        <path :class="{'hidden': sidebarOpen, 'inline-flex': ! sidebarOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                        <path :class="{'hidden': ! sidebarOpen, 'inline-flex': sidebarOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Logo -->
                            <div class="flex-shrink-0 flex items-center">
                                <a href="{{ route('dashboard') }}">
                                    <x-application-logo class="block h-7 w-auto fill-current text-gray-800" />
                                </a>
                            </div>

                            <!-- Nombre del comercio -->
                            <div class="hidden sm:flex sm:items-center sm:ml-4">
                                <div class="text-sm font-semibold text-gray-800">
                                    @if(Auth::user() && Auth::user()->company)
                                        {{ Auth::user()->company->business_name }}
                                    @else
                                        Sistema de Facturación
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Settings Dropdown -->
                        <div class="hidden sm:flex sm:items-center">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300 focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150 ease-in-out">
                                        <div>{{ Auth::user()->name }}</div>

                                        <div class="ml-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('Perfil') }}
                                    </x-dropdown-link>

                                    <!-- Authentication -->
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault();
                                                            this.closest('form').submit();">
                                            {{ __('Cerrar Sesión') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Sidebar (Móvil) -->
            <div x-show="sidebarOpen" class="sm:hidden fixed inset-0 z-40 flex" x-cloak>
                <!-- Overlay -->
                <div @click="sidebarOpen = false" x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0">
                    <div class="absolute inset-0 bg-gray-600 opacity-75"></div>
                </div>
                
                <!-- Contenido Sidebar -->
                <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative flex-1 flex flex-col max-w-xs w-full bg-white">
                    <!-- Cabecera Sidebar -->
                    <div class="p-3 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex-shrink-0">
                            <x-application-logo class="block h-7 w-auto fill-current text-gray-800" />
                        </div>
                        <button @click="sidebarOpen = false" class="ml-1 flex items-center justify-center h-7 w-7 rounded-full focus:outline-none focus:bg-gray-200">
                            <svg class="h-5 w-5 text-gray-500" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Elementos del Sidebar -->
                    <div class="flex-1 h-0 overflow-y-auto pt-2 pb-4">
                        <div class="px-2 space-y-1">
                            <a href="{{ route('dashboard') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:bg-gray-100 {{ request()->routeIs('dashboard') ? 'bg-gray-100' : '' }}">
                                Dashboard
                            </a>
                            
                            @can('sell')
                                <a href="{{ route('companies.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:bg-gray-100 {{ request()->routeIs('companies.*') ? 'bg-gray-100' : '' }}">
                                    Mi Compañía
                                </a>
                                
                                @if(Route::has('products.index'))
                                <a href="{{ route('products.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:bg-gray-100 {{ request()->routeIs('products.index') ? 'bg-gray-100' : '' }}">
                                    Productos
                                </a>
                                @else
                                <span class="block px-3 py-2 rounded-md text-base font-medium text-gray-400">
                                    Productos (No disponible)
                                </span>
                                @endif
                            @endcan
                            
                            @can('view_invoice')
                                @if(Route::has('invoices.index'))
                                <a href="{{ route('invoices.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:bg-gray-100 {{ request()->routeIs('invoices.index') ? 'bg-gray-100' : '' }}">
                                    Facturas
                                </a>
                                @else
                                <span class="block px-3 py-2 rounded-md text-base font-medium text-gray-400">
                                    Facturas (No disponible)
                                </span>
                                @endif
                            @endcan
                            
                            @can('report')
                                @if(Route::has('reports.index'))
                                <a href="{{ route('reports.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:bg-gray-100 {{ request()->routeIs('reports.index') ? 'bg-gray-100' : '' }}">
                                    Reportes
                                </a>
                                @else
                                <span class="block px-3 py-2 rounded-md text-base font-medium text-gray-400">
                                    Reportes (No disponible)
                                </span>
                                @endif
                            @endcan
                            
                            @role('admin')
                                @if(Route::has('users.index'))
                                <a href="{{ route('users.index') }}" class="block px-3 py-2 rounded-md text-base font-medium text-gray-900 hover:bg-gray-100 {{ request()->routeIs('users.index') ? 'bg-gray-100' : '' }}">
                                    Usuarios/Roles
                                </a>
                                @else
                                <span class="block px-3 py-2 rounded-md text-base font-medium text-gray-400">
                                    Usuarios/Roles (No disponible)
                                </span>
                                @endif
                            @endrole
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar (Desktop) -->
            <div class="hidden sm:flex sm:flex-col sm:fixed sm:top-0 sm:left-0 sm:bottom-0 sm:w-56 sm:pt-12 sm:border-r sm:border-gray-200 sm:bg-white">
                <div class="flex-1 flex flex-col pt-2 pb-4 overflow-y-auto">
                    <div class="flex-1 px-2">
                        <div class="space-y-0.5">
                            <a href="{{ route('dashboard') }}" class="group flex items-center px-2 py-2 rounded-md text-sm font-medium text-gray-900 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                                <svg class="mr-3 h-5 w-5 text-gray-500 group-hover:text-indigo-600 {{ request()->routeIs('dashboard') ? 'text-indigo-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                </svg>
                                Dashboard
                            </a>
                            
                            @can('sell')
                                <a href="{{ route('companies.index') }}" class="group flex items-center px-2 py-2 rounded-md text-sm font-medium text-gray-900 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('companies.*') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                                    <svg class="mr-3 h-5 w-5 text-gray-500 group-hover:text-indigo-600 {{ request()->routeIs('companies.*') ? 'text-indigo-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                    Mi Compañía
                                </a>
                                
                                @if(Route::has('products.index'))
                                <a href="{{ route('products.index') }}" class="group flex items-center px-2 py-2 rounded-md text-sm font-medium text-gray-900 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('products.index') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                                    <svg class="mr-3 h-5 w-5 text-gray-500 group-hover:text-indigo-600 {{ request()->routeIs('products.index') ? 'text-indigo-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    Productos
                                </a>
                                @else
                                <span class="flex items-center px-2 py-2 rounded-md text-sm font-medium text-gray-400">
                                    <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    Productos (No disponible)
                                </span>
                                @endif
                            @endcan
                            
                            @can('view_invoice')
                                @if(Route::has('invoices.index'))
                                <a href="{{ route('invoices.index') }}" class="group flex items-center px-2 py-2 rounded-md text-sm font-medium text-gray-900 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('invoices.index') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                                    <svg class="mr-3 h-5 w-5 text-gray-500 group-hover:text-indigo-600 {{ request()->routeIs('invoices.index') ? 'text-indigo-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Facturas
                                </a>
                                @else
                                <span class="flex items-center px-2 py-2 rounded-md text-sm font-medium text-gray-400">
                                    <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Facturas (No disponible)
                                </span>
                                @endif
                            @endcan
                            
                            @can('report')
                                @if(Route::has('reports.index'))
                                <a href="{{ route('reports.index') }}" class="group flex items-center px-2 py-2 rounded-md text-sm font-medium text-gray-900 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('reports.index') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                                    <svg class="mr-3 h-5 w-5 text-gray-500 group-hover:text-indigo-600 {{ request()->routeIs('reports.index') ? 'text-indigo-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    Reportes
                                </a>
                                @else
                                <span class="flex items-center px-2 py-2 rounded-md text-sm font-medium text-gray-400">
                                    <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    Reportes (No disponible)
                                </span>
                                @endif
                            @endcan
                            
                            @role('admin')
                                @if(Route::has('users.index'))
                                <a href="{{ route('users.index') }}" class="group flex items-center px-2 py-2 rounded-md text-sm font-medium text-gray-900 hover:bg-indigo-50 hover:text-indigo-700 {{ request()->routeIs('users.index') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                                    <svg class="mr-3 h-5 w-5 text-gray-500 group-hover:text-indigo-600 {{ request()->routeIs('users.index') ? 'text-indigo-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    Usuarios/Roles
                                </a>
                                @else
                                <span class="flex items-center px-2 py-2 rounded-md text-sm font-medium text-gray-400">
                                    <svg class="mr-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                    </svg>
                                    Usuarios/Roles (No disponible)
                                </span>
                                @endif
                            @endrole
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenido Principal -->
            <div class="sm:pl-56">
                <div class="pt-12">
                    <!-- Alertas Flash -->
                    {{-- <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-2">
                        @if (session('success'))
                            <x-alert type="success">
                                {{ session('success') }}
                            </x-alert>
                        @endif

                        @if (session('error'))
                            <x-alert type="error">
                                {{ session('error') }}
                            </x-alert>
                        @endif
                    </div> --}}

                    <!-- Header -->
                    @if (isset($header))
                        <header class="bg-white shadow-sm">
                            <div class="max-w-7xl mx-auto py-3 px-4 sm:px-6 lg:px-8">
                                {{ $header }}
                            </div>
                        </header>
                    @endif

                    <!-- Contenido de la Página -->
                    <main>
                        {{ $slot }}
                    </main>
                </div>
            </div>
        </div>

        @stack('scripts')
    </body>
</html>
