<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test para verificar que un cliente no puede acceder a /reports/sales
     *
     * @return void
     */
    public function test_cliente_cannot_access_sales_reports(): void
    {
        // Ejecutar el seeder para crear roles y permisos
        $this->seed();
        
        // Obtener el usuario cliente creado en el seeder
        $cliente = User::where('email', 'cliente@ejemplo.com')->first();
        
        // Crear un token de API para el cliente sin habilidades adicionales
        $token = $cliente->createToken('test-token')->plainTextToken;
        
        // Intentar acceder a la ruta de reportes de ventas
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/reports/sales');
        
        // Verificar que se recibe un error 403 (Forbidden)
        $response->assertStatus(403);
    }
    
    /**
     * Test para verificar que un contador sí puede acceder a /reports/sales
     *
     * @return void
     */
    public function test_contador_can_access_sales_reports(): void
    {
        // Ejecutar el seeder para crear roles y permisos
        $this->seed();
        
        // Obtener el usuario contador creado en el seeder
        $contador = User::where('email', 'contador@ejemplo.com')->first();
        
        // Crear un token de API para el contador sin habilidades adicionales
        $token = $contador->createToken('test-token')->plainTextToken;
        
        // Intentar acceder a la ruta de reportes de ventas
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/reports/sales');
        
        // Verificar que se recibe un estado 200 (OK)
        $response->assertStatus(200);
    }
} 