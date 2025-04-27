<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbilityTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * Test para verificar que un Cliente no puede acceder a /reports/sales.
     *
     * @return void
     */
    public function test_cliente_cannot_access_sales_reports(): void
    {
        // Ejecutar el seeder para crear roles y permisos
        $this->seed();
        
        // Obtener el usuario cliente creado en el seeder
        $cliente = User::where('email', 'cliente@ejemplo.com')->first();
        
        // Depurar los roles y permisos
        $roles = $cliente->getRoleNames();
        $permisos = $cliente->getAllPermissions()->pluck('name');
        
        $this->assertTrue($cliente->hasRole('cliente'), 'El usuario no tiene el rol de cliente');
        $this->assertTrue($cliente->hasPermissionTo('view_invoice_own'), 'El cliente no tiene permiso view_invoice_own');
        $this->assertFalse($cliente->hasPermissionTo('view_reports_basic'), 'El cliente no debería tener permiso view_reports_basic');
        
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
     * Test para verificar que un Comerciante sí puede acceder a /reports/sales.
     *
     * @return void
     */
    public function test_comerciante_can_access_sales_reports(): void
    {
        // Ejecutar el seeder para crear roles y permisos
        $this->seed();
        
        // Obtener el usuario comerciante creado en el seeder
        $comerciante = User::where('email', 'comerciante@ejemplo.com')->first();
        
        // Depurar los roles y permisos
        $roles = $comerciante->getRoleNames();
        $permisos = $comerciante->getAllPermissions()->pluck('name');
        
        $this->assertTrue($comerciante->hasRole('comerciante'), 'El usuario no tiene el rol de comerciante');
        $this->assertTrue($comerciante->hasPermissionTo('view_reports_basic'), 'El comerciante no tiene permiso view_reports_basic');
        
        // Crear un token de API para el comerciante sin habilidades adicionales
        $token = $comerciante->createToken('test-token')->plainTextToken;
        
        // Intentar acceder a la ruta de reportes de ventas
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/reports/sales');
        
        // Verificar que se recibe un estado 200 (OK)
        $response->assertStatus(200);
    }
} 