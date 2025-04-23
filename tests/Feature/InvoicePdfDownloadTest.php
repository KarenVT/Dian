<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Merchant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InvoicePdfDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected $merchant;
    protected $invoiceWithPdf;
    protected $ticketPosInvoice;
    protected $contadorUser;
    protected $clienteUser;
    protected $pdfContent;

    public function setUp(): void
    {
        parent::setUp();
        
        // Configurar almacenamiento simulado
        Storage::fake('local');
        
        // Crear un contenido de prueba para el PDF
        $this->pdfContent = 'Contenido simulado del PDF';
        
        // Crear un comercio
        $this->merchant = Merchant::factory()->create([
            'nit' => '900123456',
            'business_name' => 'Empresa PDF Test',
        ]);
        
        // Crear una factura con PDF (formal)
        $this->invoiceWithPdf = Invoice::create([
            'merchant_id' => $this->merchant->id,
            'invoice_number' => 'TEST001',
            'type' => 'income',
            'document_type' => 'invoice', // Factura formal
            'cufe' => 'test-cufe-123',
            'customer_id' => '12345',
            'customer_name' => 'Cliente Test',
            'customer_email' => 'cliente@test.com',
            'subtotal' => 200000,
            'tax' => 38000,
            'total' => 238000, // > 212000, por lo que es factura formal
            'issued_at' => Carbon::now(),
        ]);
        
        // Crear el archivo PDF y actualizar la ruta
        $pdfPath = 'fev/' . $this->merchant->nit . '/' . date('Y/m') . '/FV_' . $this->invoiceWithPdf->invoice_number . '_' . $this->invoiceWithPdf->cufe . '.pdf';
        Storage::put($pdfPath, $this->pdfContent);
        
        $this->invoiceWithPdf->update([
            'pdf_path' => $pdfPath
        ]);
        
        // Crear una factura tipo ticket POS (sin PDF formal)
        $this->ticketPosInvoice = Invoice::create([
            'merchant_id' => $this->merchant->id,
            'invoice_number' => 'TEST002',
            'type' => 'income',
            'document_type' => 'ticket_pos', // Ticket POS
            'customer_id' => '12345',
            'customer_name' => 'Cliente Test',
            'subtotal' => 100000,
            'tax' => 19000,
            'total' => 119000, // < 212000, por lo que es ticket POS
            'issued_at' => Carbon::now(),
        ]);
        
        // Crear roles
        Role::create(['name' => 'contador']);
        
        // Crear usuario contador
        $this->contadorUser = User::factory()->create([
            'merchant_id' => $this->merchant->id,
        ]);
        $this->contadorUser->assignRole('contador');
        
        // Crear usuario cliente (sin rol especial)
        $this->clienteUser = User::factory()->create();
    }

    /**
     * El dueño de la factura (Merchant) puede descargar el PDF.
     */
    public function test_merchant_can_download_invoice_pdf(): void
    {
        // Autenticar como el comercio
        Sanctum::actingAs($this->merchant, [], 'merchant');
        
        // Verificar acceso al PDF
        $response = $this->get("/api/invoices/{$this->invoiceWithPdf->id}/pdf");
        
        $response->assertStatus(200);
        $this->assertEquals($this->pdfContent, $response->getContent());
    }

    /**
     * Los usuarios con rol contador pueden descargar PDFs.
     */
    public function test_contador_can_download_invoice_pdf(): void
    {
        // Autenticar como contador
        Sanctum::actingAs($this->contadorUser);
        
        // Verificar acceso al PDF
        $response = $this->get("/api/invoices/{$this->invoiceWithPdf->id}/pdf");
        
        $response->assertStatus(200);
        $this->assertEquals($this->pdfContent, $response->getContent());
    }

    /**
     * Los clientes con token que tenga la habilidad view_invoice pueden descargar PDFs.
     */
    public function test_client_with_view_invoice_ability_can_download_pdf(): void
    {
        // Autenticar con la habilidad correcta
        Sanctum::actingAs($this->clienteUser, ['view_invoice']);
        
        // Verificar acceso al PDF
        $response = $this->get("/api/invoices/{$this->invoiceWithPdf->id}/pdf");
        
        $response->assertStatus(200);
        $this->assertEquals($this->pdfContent, $response->getContent());
    }

    /**
     * Los clientes sin la habilidad view_invoice no pueden descargar PDFs.
     */
    public function test_client_without_view_invoice_ability_cannot_download_pdf(): void
    {
        // Autenticar sin la habilidad correcta
        Sanctum::actingAs($this->clienteUser, ['otro_permiso']);
        
        // Verificar rechazo
        $response = $this->get("/api/invoices/{$this->invoiceWithPdf->id}/pdf");
        
        $response->assertForbidden();
    }

    /**
     * No se pueden descargar PDFs de tickets POS.
     */
    public function test_ticket_pos_pdf_returns_404(): void
    {
        // Autenticar como dueño
        Sanctum::actingAs($this->merchant, [], 'merchant');
        
        // Verificar que los tickets POS retornan 404
        $response = $this->get("/api/invoices/{$this->ticketPosInvoice->id}/pdf");
        
        $response->assertNotFound();
    }

    /**
     * No se puede acceder sin autenticación.
     */
    public function test_unauthenticated_access_is_denied(): void
    {
        // Sin autenticación
        $response = $this->get("/api/invoices/{$this->invoiceWithPdf->id}/pdf");
        
        $response->assertUnauthorized();
    }

    /**
     * Un merchant no puede acceder a facturas de otro merchant.
     */
    public function test_merchant_cannot_download_other_merchants_invoice_pdf(): void
    {
        // Crear otro merchant
        $otherMerchant = Merchant::factory()->create();
        
        // Autenticar como este otro merchant
        Sanctum::actingAs($otherMerchant, [], 'merchant');
        
        // Verificar rechazo
        $response = $this->get("/api/invoices/{$this->invoiceWithPdf->id}/pdf");
        
        $response->assertForbidden();
    }
} 