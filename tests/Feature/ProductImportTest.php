<?php

namespace Tests\Feature;

use App\Models\company;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear un company para las pruebas
        $this->company = company::factory()->create();

        // Crear un usuario asociado al company
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
        ]);

        // Autenticar al usuario
        Sanctum::actingAs($this->user);
    }

    /**
     * Test de importación de productos con duplicados.
     */
    public function test_import_products_with_duplicates(): void
    {
        Storage::fake('local');

        // Crear un producto existente para simular duplicidad
        Product::create([
            'company_id' => $this->company->id,
            'sku' => 'PROD001',
            'name' => 'Producto Test 1',
            'price' => 100.00,
            'tax_rate' => 19.00,
            'dian_code' => '12345',
        ]);

        // Crear un archivo CSV temporal con 2 productos (uno duplicado)
        $csvContent = "sku,name,price,tax_rate,dian_code\n";
        $csvContent .= "PROD001,Producto Test 1,100.00,19.00,12345\n"; // Este está duplicado
        $csvContent .= "PROD002,Producto Test 2,200.00,19.00,67890\n"; // Este es nuevo

        $file = UploadedFile::fake()->createWithContent(
            'products.csv',
            $csvContent
        );

        // Realizar la solicitud de importación
        $response = $this->postJson('/api/products/import', [
            'file' => $file,
        ]);

        // Verificar respuesta
        $response->assertStatus(200)
            ->assertJsonStructure([
                'inserted',
                'duplicates'
            ])
            ->assertJson([
                'inserted' => 1, // Solo debería insertarse un producto
                'duplicates' => ['PROD001'], // El producto duplicado
            ]);

        // Verificar que solo hay 2 productos en total en la base de datos
        $this->assertEquals(2, Product::count());
    }
}
