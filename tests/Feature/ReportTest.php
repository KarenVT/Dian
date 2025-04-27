<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\company;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private company $company;

    protected function setUp(): void
    {
        parent::setUp();

        // Limpiar el caché de reportes antes de iniciar
        Cache::flush();

        // Crear un comercio y un usuario
        $this->company = company::factory()->create();
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
        ]);
    }

    /** @test */
    public function it_returns_sales_report_filtered_by_date_range()
    {
        // Crear facturas para el comercio
        // 1. Factura dentro del rango de fechas (día 1)
        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'issued_at' => Carbon::yesterday()->setHour(10),
            'subtotal' => 1000,
            'tax' => 190,
            'total' => 1190,
        ]);

        // 2. Factura dentro del rango de fechas (día 1)
        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'issued_at' => Carbon::yesterday()->setHour(15),
            'subtotal' => 2000,
            'tax' => 380,
            'total' => 2380,
        ]);

        // 3. Factura dentro del rango de fechas (día 2)
        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'issued_at' => Carbon::today(),
            'subtotal' => 3000,
            'tax' => 570,
            'total' => 3570,
        ]);

        // 4. Factura fuera del rango (anterior al rango)
        Invoice::factory()->create([
            'company_id' => $this->company->id,
            'issued_at' => Carbon::now()->subDays(10),
            'subtotal' => 5000,
            'tax' => 950,
            'total' => 5950,
        ]);

        // 5. Factura de otro comercio (dentro del rango)
        $othercompany = company::factory()->create();
        Invoice::factory()->create([
            'company_id' => $othercompany->id,
            'issued_at' => Carbon::yesterday(),
            'subtotal' => 4000,
            'tax' => 760,
            'total' => 4760,
        ]);

        // Definir fechas para el reporte
        $from = Carbon::yesterday()->format('Y-m-d');
        $to = Carbon::today()->format('Y-m-d');

        // Realizar la solicitud autenticada
        $response = $this->actingAs($this->user)
            ->getJson("/api/reports/sales?from={$from}&to={$to}&group=day");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_sales',
                'total_invoices',
                'total_iva',
                'graph',
            ]);

        // Verificar que los totales son correctos (solo para las facturas dentro del rango)
        $response->assertJson([
            'total_sales' => 7140.0, // 1190 + 2380 + 3570
            'total_invoices' => 3,
            'total_iva' => 1140.0, // 190 + 380 + 570
        ]);

        // Verificar la agrupación por día
        $responseData = $response->json();
        $this->assertCount(2, $responseData['graph']);  // Debe tener 2 días

        // Verificar agrupación por hora
        $responseByHour = $this->actingAs($this->user)
            ->getJson("/api/reports/sales?from={$from}&to={$to}&group=hour");

        $responseByHour->assertStatus(200);
        
        $hourData = $responseByHour->json();
        $this->assertCount(3, $hourData['graph']); // 3 horas distintas (10:00, 15:00 y hora de hoy)
    }

    /** @test */
    public function it_validates_required_parameters()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/reports/sales');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['from', 'to', 'group']);
    }

    /** @test */
    public function it_validates_date_range()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/reports/sales?from=2023-05-15&to=2023-05-10&group=day');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['to']);
    }

    /** @test */
    public function it_validates_group_parameter()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/reports/sales?from=2023-05-10&to=2023-05-15&group=invalid');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['group']);
    }
} 