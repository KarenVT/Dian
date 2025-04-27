<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;

class AuthController extends Controller
{
    /**
     * Crear un token de autenticación con las habilidades basadas en el rol del usuario
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function token(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);
        
        $user = User::where('email', $request->email)->first();
        
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas son incorrectas.'],
            ]);
        }
        
        // Determinar las habilidades basadas en el rol
        $abilities = [];
        
        // Si es administrador, dar todas las habilidades
        if ($user->hasRole('admin')) {
            $abilities = ['*']; // Wildcard para todas las habilidades
        } else {
            // Obtener las habilidades basadas en los permisos del usuario
            $abilities = $user->getAllPermissions()->pluck('name')->toArray();
        }
        
        // Crear token de API con las habilidades
        $token = $user->createToken($request->device_name, $abilities);
        
        return response()->json([
            'token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
                'abilities' => $abilities
            ]
        ]);
    }
    
    /**
     * Revocar todos los tokens del usuario actual
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        
        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }
} 