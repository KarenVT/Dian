<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AbilityMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$abilities): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // Verificar si el usuario tiene alguna de las habilidades requeridas
        // ya sea a través de los permisos de Spatie o las habilidades del token de Sanctum
        foreach ($abilities as $ability) {
            // Verificar permisos de Spatie
            if ($user->hasPermissionTo($ability)) {
                return $next($request);
            }

            // Verificar habilidades del token de Sanctum
            if ($user->tokenCan($ability)) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'Forbidden: Acceso no autorizado'], 403);
    }
} 