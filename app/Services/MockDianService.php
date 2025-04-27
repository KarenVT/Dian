<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Servicio de simulación de DIAN para propósitos de demostración y pruebas
 * Este servicio simula la conexión con la DIAN sin requerir acceso a la API real
 */
class MockDianService
{
    /**
     * Envía una factura a la DIAN simulada
     *
     * @param Invoice $invoice La factura a enviar
     * @return array Respuesta simulada de la DIAN
     */
    public function sendInvoice(Invoice $invoice): array
    {
        // Simulamos un pequeño retraso para imitar una API real
        sleep(1);
        
        // Registrar en el log
        Log::info("MockDianService: Recibida factura #{$invoice->id} para envío simulado a DIAN");
        
        // Actualizar la factura
        $invoice->update([
            'dian_sent_at' => now(),
            'dian_status' => 'PENDING',
            'dian_response_code' => 'trackId',
            'dian_response_message' => $this->generateTrackId($invoice)
        ]);
        
        // Simular respuesta exitosa
        return [
            'success' => true,
            'message' => 'Factura enviada correctamente a DIAN (simulación)',
            'trackId' => $invoice->dian_response_message,
            'status' => 'PENDING'
        ];
    }
    
    /**
     * Consulta el estado de una factura en la DIAN simulada
     *
     * @param Invoice $invoice La factura a consultar
     * @return array Respuesta simulada de la DIAN
     */
    public function checkStatus(Invoice $invoice): array
    {
        // Simulamos un pequeño retraso para imitar una API real
        sleep(1);
        
        // Registrar en el log
        Log::info("MockDianService: Consultando estado de factura #{$invoice->id} en DIAN simulada");
        
        // Determinamos el estado basado en tiempos
        $status = $this->determineStatus($invoice);
        
        // Actualizamos la factura con el estado
        $invoice->update([
            'dian_processed_at' => $status['dian_processed_at'],
            'dian_status' => $status['status'],
            'dian_response_code' => $status['status_code'],
            'dian_response_message' => $status['message']
        ]);
        
        // Simular respuesta exitosa
        return [
            'success' => true,
            'message' => 'Estado consultado correctamente (simulación)',
            'status' => $status['status'],
            'statusCode' => $status['status_code'],
            'statusDescription' => $status['message']
        ];
    }
    
    /**
     * Procesa todas las facturas pendientes en la DIAN simulada
     *
     * @return array Estadísticas de procesamiento
     */
    public function processPendingInvoices(): array
    {
        $stats = [
            'total' => 0,
            'processed' => 0,
            'accepted' => 0,
            'rejected' => 0
        ];
        
        // Obtener facturas pendientes
        $pendingInvoices = Invoice::where('dian_status', 'PENDING')
            ->whereNotNull('dian_sent_at')
            ->get();
            
        $stats['total'] = $pendingInvoices->count();
        
        foreach ($pendingInvoices as $invoice) {
            $result = $this->checkStatus($invoice);
            $stats['processed']++;
            
            if ($invoice->dian_status === 'ACCEPTED') {
                $stats['accepted']++;
            } elseif ($invoice->dian_status === 'REJECTED') {
                $stats['rejected']++;
            }
        }
        
        return $stats;
    }
    
    /**
     * Generar un ID de seguimiento (trackId) simulado
     *
     * @param Invoice $invoice La factura
     * @return string ID de seguimiento simulado
     */
    private function generateTrackId(Invoice $invoice): string
    {
        return 'SIMUL-' . str_pad($invoice->id, 10, '0', STR_PAD_LEFT) . '-' . time();
    }
    
    /**
     * Determina el estado de una factura basado en el tiempo transcurrido
     *
     * @param Invoice $invoice La factura a procesar
     * @return array Estado determinado
     */
    private function determineStatus(Invoice $invoice): array
    {
        $sentAt = $invoice->dian_sent_at ?? now()->subMinutes(5);
        $elapsedMinutes = $sentAt->diffInMinutes(now());
        
        // Por defecto, procesada ahora
        $processedAt = now();
        
        // Si ha pasado más de 5 minutos, marca como aceptada (90% probabilidad) o rechazada
        if ($elapsedMinutes >= 5) {
            $isAccepted = (rand(1, 100) <= 90);
            
            if ($isAccepted) {
                return [
                    'status' => 'ACCEPTED',
                    'status_code' => '00',
                    'message' => 'Documento validado exitosamente por DIAN',
                    'dian_processed_at' => $processedAt
                ];
            } else {
                return [
                    'status' => 'REJECTED',
                    'status_code' => 'ERROR',
                    'message' => 'El documento presenta inconsistencias en la estructura',
                    'dian_processed_at' => $processedAt
                ];
            }
        }
        
        // Si ha pasado menos tiempo, sigue en pendiente
        return [
            'status' => 'PENDING',
            'status_code' => 'PROCESSING',
            'message' => 'Documento en proceso de validación',
            'dian_processed_at' => null
        ];
    }
} 