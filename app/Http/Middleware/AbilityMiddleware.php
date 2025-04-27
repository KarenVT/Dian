<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        // Para entorno de testing, loguear información adicional
        if (app()->environment('testing')) {
            Log::info('AbilityMiddleware - User Roles:', ['roles' => $user->getRoleNames()]);
            Log::info('AbilityMiddleware - User Permissions:', ['permissions' => $user->getAllPermissions()->pluck('name')]);
            Log::info('AbilityMiddleware - Required Abilities:', ['abilities' => $abilities]);
        }

        // Verificar si el usuario es administrador (tiene acceso total)
        if ($user->hasRole('admin')) {
            return $next($request);
        }

        // Verificar si el usuario tiene alguna de las habilidades requeridas
        $hasPermission = false;

        foreach ($abilities as $ability) {
            // Verificar permisos de Spatie
            if ($user->hasPermissionTo($ability)) {
                $hasPermission = true;
                break;
            }

            // Verificar habilidades del token de Sanctum
            if ($user->tokenCan($ability)) {
                $hasPermission = true;
                break;
            }
        }

        if (!$hasPermission) {
            if (app()->environment('testing')) {
                Log::warning('AbilityMiddleware - Access Denied', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                    'url' => $request->url(),
                    'abilities' => $abilities
                ]);
            }

            return response()->json([
                'message' => 'Forbidden: Acceso no autorizado',
                'required_abilities' => $abilities
            ], 403);
        }

        return $next($request);
    }
} 