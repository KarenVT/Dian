<?php

namespace Tests\Feature;

use App\DTOs\CartDTO;
use App\DTOs\CustomerDTO;
use App\Models\company;
use App\Services\DianStorageService;
use App\Services\InvoiceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DianStorageTest extends TestCase
{
    use RefreshDatabase;
    
    /**
     * @var company
     */
    protected $company;
    
    /**
     * @var DianStorageService
     */
    protected $dianStorageService;
    
    /**
     * @var InvoiceService
     */
    protected $invoiceService;
    
    /**
     * Configuración inicial para las pruebas
     */
    public function setUp(): void
    {
        parent::setUp();
        
        // Configurar simulación de almacenamiento
        Storage::fake('local');
        
        // Crear un comercio de prueba
        $this->company = company::factory()->create([
            'nit' => '900987654',
            'business_name' => 'Empresa Prueba DIAN',
            'email' => 'dian@prueba.com',
            'password' => bcrypt('password'),
            'tax_regime' => 'COMÚN',
        ]);
        
        // Instanciar servicios
        $this->dianStorageService = new DianStorageService();
        $this->invoiceService = new InvoiceService($this->company, $this->dianStorageService);
    }
    
    /**
     * Probar la generación del path base según normativa DIAN
     */
    public function test_generates_correct_base_path_according_to_dian_standard(): void
    {
        // Generar una fecha fija para pruebas
        $testDate = Carbon::create(2023, 5, 15);
        
        // Verificar la estructura de carpetas
        $basePath = $this->dianStorageService->getBasePath(
            $this->company->nit,
            $testDate
        );
        
        $this->assertEquals('fev/900987654/2023/05', $basePath);
    }
    
    /**
     * Probar la generación del nombre de archivo según normativa DIAN
     */
    public function test_generates_correct_file_name_according_to_dian_standard(): void
    {
        $invoiceNumber = 'SEFT00000001';
        $cufe = hash('sha384', 'test_string');
        
        // Verificar nombre para XML
        $xmlFileName = $this->dianStorageService->getFileName(
            $invoiceNumber,
            $cufe,
            'xml'
        );
        
        $this->assertEquals("FV_{$invoiceNumber}_{$cufe}.xml", $xmlFileName);
        
        // Verificar nombre para PDF
        $pdfFileName = $this->dianStorageService->getFileName(
            $invoiceNumber,
            $cufe,
            'pdf'
        );
        
        $this->assertEquals("FV_{$invoiceNumber}_{$cufe}.pdf", $pdfFileName);
        
        // Verificar nombre sin CUFE (para ticket_pos)
        $posFileName = $this->dianStorageService->getFileName(
            $invoiceNumber,
            null,
            'pdf'
        );
        
        $this->assertEquals("FV_{$invoiceNumber}.pdf", $posFileName);
    }
    
    /**
     * Probar el almacenamiento de documento según normativa DIAN
     */
    public function test_stores_document_in_correct_location(): void
    {
        $testDate = Carbon::create(2023, 5, 15);
        $invoiceNumber = 'SEFT00000001';
        $cufe = hash('sha384', 'test_string');
        $content = 'Test content';
        
        // Almacenar un documento XML
        $path = $this->dianStorageService->storeDocument(
            $content,
            $this->company->nit,
            $invoiceNumber,
            $cufe,
            'xml',
            $testDate
        );
        
        // Verificar que el archivo existe en la ubicación correcta
        $expectedPath = "fev/{$this->company->nit}/2023/05/FV_{$invoiceNumber}_{$cufe}.xml";
        $this->assertEquals($expectedPath, $path);
        Storage::assertExists($path);
        
        // Verificar el contenido
        $this->assertEquals($content, Storage::get($path));
    }
    
    /**
     * Probar la generación y almacenamiento completo de una factura
     */
    public function test_invoice_service_properly_stores_documents_in_dian_format(): void
    {
        // Definir una fecha constante para pruebas
        $fixedDate = Carbon::create(2023, 6, 15, 10, 30, 0);
        Carbon::setTestNow($fixedDate);
        
        // Preparar datos de prueba
        $items = [
            [
                'code' => 'PROD001',
                'description' => 'Producto DIAN 1',
                'price' => 100000,
                'quantity' => 3,
                'tax_percentage' => 19
            ]
        ];

        $subtotal = 300000;
        $tax = 57000;
        $total = 357000;

        $cart = new CartDTO(
            $items,
            $subtotal,
            $tax,
            $total,
            'income',
            'Prueba normativa DIAN'
        );

        $customer = new CustomerDTO(
            '123456789',
            'Cliente DIAN',
            'cliente@dian.test'
        );

        // Ejecutar generación de factura
        $invoice = $this->invoiceService->generateInvoice($cart, $customer);
        
        // Verificar que existe la factura en la base de datos
        $this->assertNotNull($invoice);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'company_id' => $this->company->id
        ]);
        
        // Verificar que los archivos se guardaron según la normativa DIAN
        $expectedPath = "fev/{$this->company->nit}/2023/06/";
        
        // Verificar el XML
        $this->assertStringStartsWith($expectedPath, $invoice->xml_path);
        $this->assertStringContainsString("FV_{$invoice->invoice_number}", $invoice->xml_path);
        $this->assertStringEndsWith('.xml', $invoice->xml_path);
        Storage::assertExists($invoice->xml_path);
        
        // Verificar el PDF
        $this->assertStringStartsWith($expectedPath, $invoice->pdf_path);
        $this->assertStringContainsString("FV_{$invoice->invoice_number}", $invoice->pdf_path);
        $this->assertStringEndsWith('.pdf', $invoice->pdf_path);
        Storage::assertExists($invoice->pdf_path);
        
        // Como es una factura formal (total > 212000), debe tener CUFE
        $this->assertNotNull($invoice->cufe);
        $this->assertStringContainsString($invoice->cufe, $invoice->xml_path);
        $this->assertStringContainsString($invoice->cufe, $invoice->pdf_path);
        
        // Limpiar
        Carbon::setTestNow();
    }
    
    /**
     * Probar el comando de limpieza
     */
    public function test_dian_cleanup_command_finds_old_documents(): void
    {
        // Definir una fecha actual fija
        $currentDate = Carbon::create(2028, 6, 15);
        Carbon::setTestNow($currentDate);
        
        // Crear directorios y archivos de prueba con más de 5 años
        $oldDate = Carbon::create(2023, 1, 15);
        $invoiceNumber = 'SEFT00000099';
        $cufe = hash('sha384', 'old_test');
        $content = 'Old test content';
        
        // Almacenar un documento viejo
        $oldPath = $this->dianStorageService->storeDocument(
            $content,
            $this->company->nit,
            $invoiceNumber,
            $cufe,
            'xml',
            $oldDate
        );
        
        // Crear un documento reciente
        $recentDate = Carbon::create(2028, 1, 15); // menos de 5 años
        $recentPath = $this->dianStorageService->storeDocument(
            'Recent content',
            $this->company->nit,
            'SEFT00000100',
            hash('sha384', 'recent_test'),
            'xml',
            $recentDate
        );
        
        // Verificar que ambos existen
        Storage::assertExists($oldPath);
        Storage::assertExists($recentPath);
        
        // Obtener documentos expirados
        $expiredDocs = $this->dianStorageService->getExpiredDocuments($currentDate);
        
        // Verificar que solo se encuentra el documento antiguo
        $this->assertCount(1, $expiredDocs);
        $this->assertEquals($oldPath, $expiredDocs[0]['path']);
        
        // Ejecutar el comando (en modo dry-run)
        $this->artisan('dian:cleanup --dry-run')
            ->expectsOutput('Verificando documentos electrónicos con antigüedad mayor a 5 años...')
            ->expectsOutput('Se encontraron 1 documentos con antigüedad mayor a 5 años.')
            ->assertExitCode(0);
        
        // Verificar que los archivos siguen existiendo (porque usamos dry-run)
        Storage::assertExists($oldPath);
        Storage::assertExists($recentPath);
        
        // Limpiar
        Carbon::setTestNow();
    }
} 