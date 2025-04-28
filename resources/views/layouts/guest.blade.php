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

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            .auth-container {
                background-color: #f9fafb;
                min-height: 100vh;
            }
            
            .auth-card {
                background-color: white;
                border-radius: 0.75rem;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
                width: 100%;
                max-width: 32rem;
                overflow: hidden;
            }
            
            .auth-header {
                background-color: #4f46e5;
                padding: 1.5rem;
                text-align: center;
                color: white;
            }
            
            .auth-body {
                padding: 2rem;
            }
            
            .auth-input {
                padding-left: 2.5rem !important;
                border-color: #d1d5db !important;
                border-radius: 0.375rem !important;
            }
            
            .auth-button {
                display: block;
                width: 100%;
                padding: 0.75rem 1rem;
                background-color: #4f46e5;
                color: white;
                font-weight: 500;
                border-radius: 0.375rem;
                text-align: center;
                transition: background-color 0.2s;
            }
            
            .auth-button:hover {
                background-color: #4338ca;
            }
            
            .auth-link {
                color: #4f46e5;
                font-weight: 500;
                text-decoration: none;
            }
            
            .auth-link:hover {
                text-decoration: underline;
            }
        </style>
    </head>
    <body>
        <div class="auth-container flex items-center justify-center p-4 sm:p-6 md:p-8">
            <div class="auth-card">
                <div class="auth-body">
                    {{ $slot }}
            </div>
            </div>
        </div>
    </body>
</html>
