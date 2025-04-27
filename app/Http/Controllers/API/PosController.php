<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PosController extends Controller
{
    /**
     * Crear una nueva venta rápida (ticket)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Simulación de creación de ticket para ventas rápidas
        return response()->json([
            'message' => 'Venta rápida creada correctamente',
            'ticket_id' => rand(1000, 9999),
            'created_at' => now()->toDateTimeString()
        ], 201);
    }
} 