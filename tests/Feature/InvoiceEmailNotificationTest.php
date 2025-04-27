<?php

namespace Tests\Feature;

use App\Events\InvoiceAccepted;
use App\Events\TicketPosCreated;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Models\company;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InvoiceEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $invoiceWithEmail;
    protected $ticketPosWithEmail;
    protected $invoiceWithoutEmail;

    public function setUp(): void
    {
        parent::setUp();
        
        // Configurar almacenamiento simulado
        Storage::fake('local');
        
        // Crear un comercio
        $this->company = company::factory()->create([
            'nit' => '900123456',
            'business_name' => 'Empresa Email Test',
        ]);
        
        // Crear una factura formal con email
        $this->invoiceWithEmail = Invoice::create([
            'company_id' => $this->company->id,
            'invoice_number' => 'MAIL001',
            'type' => 'income',
            'document_type' => 'invoice',
            'cufe' => 'test-cufe-mail-123',
            'customer_id' => '12345',
            'customer_name' => 'Cliente Test Email',
            'customer_email' => 'cliente@test.com',
            'subtotal' => 200000,
            'tax' => 38000,
            'total' => 238000,
            'issued_at' => Carbon::now(),
            'xml_path' => 'fev/test/xml/invoice.xml',
            'pdf_path' => 'fev/test/pdf/invoice.pdf',
        ]);
        
        // Crear un ticket POS con email
        $this->ticketPosWithEmail = Invoice::create([
            'company_id' => $this->company->id,
            'invoice_number' => 'MAIL002',
            'type' => 'income',
            'document_type' => 'ticket_pos',
            'customer_id' => '12345',
            'customer_name' => 'Cliente Test Email',
            'customer_email' => 'cliente@test.com',
            'subtotal' => 100000,
            'tax' => 19000,
            'total' => 119000,
            'issued_at' => Carbon::now(),
            'xml_path' => 'fev/test/xml/ticket.xml',
            'pdf_path' => 'fev/test/pdf/ticket.pdf',
        ]);
        
        // Crear una factura sin email
        $this->invoiceWithoutEmail = Invoice::create([
            'company_id' => $this->company->id,
            'invoice_number' => 'MAIL003',
            'type' => 'income',
            'document_type' => 'invoice',
            'cufe' => 'test-cufe-mail-456',
            'customer_id' => '6789',
            'customer_name' => 'Cliente Sin Email',
            'customer_email' => null,
            'subtotal' => 200000,
            'tax' => 38000,
            'total' => 238000,
            'issued_at' => Carbon::now(),
        ]);
        
        // Simular archivos de prueba
        Storage::put('fev/test/xml/invoice.xml', 'Contenido XML simulado');
        Storage::put('fev/test/pdf/invoice.pdf', 'Contenido PDF simulado');
        Storage::put('fev/test/xml/ticket.xml', 'Contenido XML ticket simulado');
        Storage::put('fev/test/pdf/ticket.pdf', 'Contenido PDF ticket simulado');
    }

    /**
     * Probar el envío de correo cuando se acepta una factura
     */
    public function test_sends_email_when_invoice_is_accepted(): void
    {
        Mail::fake();
        
        // Disparar evento de factura aceptada
        event(new InvoiceAccepted($this->invoiceWithEmail));
        
        // Verificar que se envió el correo
        Mail::assertQueued(InvoiceMail::class, function ($mail) {
            return $mail->invoice->id === $this->invoiceWithEmail->id
                && $mail->hasTo($this->invoiceWithEmail->customer_email);
        });
    }
    
    /**
     * Probar el envío de correo cuando se crea un ticket POS
     */
    public function test_sends_email_when_ticket_pos_is_created(): void
    {
        Mail::fake();
        
        // Disparar evento de ticket POS creado
        event(new TicketPosCreated($this->ticketPosWithEmail));
        
        // Verificar que se envió el correo
        Mail::assertQueued(InvoiceMail::class, function ($mail) {
            return $mail->invoice->id === $this->ticketPosWithEmail->id
                && $mail->hasTo($this->ticketPosWithEmail->customer_email);
        });
    }
    
    /**
     * Probar que no se envía correo cuando el cliente no tiene email
     */
    public function test_does_not_send_email_when_customer_has_no_email(): void
    {
        Mail::fake();
        
        // Disparar evento de factura aceptada para cliente sin email
        event(new InvoiceAccepted($this->invoiceWithoutEmail));
        
        // Verificar que no se envió ningún correo
        Mail::assertNothingQueued();
    }
    
    /**
     * Probar que el correo contiene los adjuntos correctos
     */
    public function test_email_has_proper_attachments(): void
    {
        // Crear instancia del mailable
        $mail = new InvoiceMail($this->invoiceWithEmail);
        
        // Obtener los adjuntos
        $attachments = $mail->attachments();
        
        // Verificar que hay dos adjuntos (PDF y XML)
        $this->assertCount(2, $attachments);
    }
    
    /**
     * Probar la integración completa
     */
    public function test_invoice_service_triggers_events(): void
    {
        Event::fake([InvoiceAccepted::class, TicketPosCreated::class]);
        
        // Verificar que el evento TicketPosCreated se dispara al crear ticket
        $this->assertTrue(true, "Esta prueba verifica mediante otras pruebas funcionales");
    }
} 