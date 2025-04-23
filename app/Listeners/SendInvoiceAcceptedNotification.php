<?php

namespace App\Listeners;

use App\Events\InvoiceAccepted;
use App\Mail\InvoiceMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class SendInvoiceAcceptedNotification implements ShouldQueue
{
    use InteractsWithQueue;
    
    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue;
    
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        // Asignar la cola configurada en config/mail.php
        $this->queue = Config::get('mail.queue', 'mail');
    }

    /**
     * Handle the event.
     */
    public function handle(InvoiceAccepted $event): void
    {
        $invoice = $event->invoice;
        
        if (empty($invoice->customer_email)) {
            Log::warning("No se puede enviar la notificación de factura aceptada porque el cliente no tiene correo", [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number
            ]);
            return;
        }
        
        try {
            Mail::to($invoice->customer_email)
                ->queue(new InvoiceMail($invoice));
                
            Log::info("Correo de factura aceptada enviado correctamente", [
                'invoice_id' => $invoice->id,
                'customer_email' => $invoice->customer_email
            ]);
        } catch (\Exception $e) {
            Log::error("Error al enviar el correo de factura aceptada", [
                'invoice_id' => $invoice->id,
                'customer_email' => $invoice->customer_email,
                'error' => $e->getMessage()
            ]);
            
            // Lanza la excepción para que Laravel reintente el trabajo
            throw $e;
        }
    }
}
