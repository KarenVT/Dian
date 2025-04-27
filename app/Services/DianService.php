<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\DianResolution;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class DianService
{
    /**
     * URL base para el entorno de pruebas de DIAN
     */
    const TEST_API_URL = 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc';
    
    /**
     * URL base para el entorno de producción de DIAN
     */
    const PROD_API_URL = 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc';
    
    /**
     * Número máximo de reintentos para envío a DIAN
     */
    const MAX_RETRY_COUNT = 3;
    
    /**
     * Envía una factura a DIAN para su validación.
     *
     * @param Invoice $invoice Factura a enviar
     * @param bool $testMode Indica si se debe usar el entorno de pruebas
     * @return bool Verdadero si el envío fue exitoso
     */
    public function sendInvoice(Invoice $invoice, bool $testMode = false): bool
    {
        try {
            // Verificar si la factura ya tiene una resolución DIAN
            $resolution = $invoice->dianResolution;
            
            // Si no tiene resolución, crear una
            if (!$resolution) {
                $resolution = $invoice->dianResolution()->create([
                    'company_id' => $invoice->company_id,
                    'dian_status' => 'PENDING',
                    'dian_retry_count' => 0
                ]);
            }
            
            // Verificar límite de reintentos
            if ($resolution->dian_retry_count >= self::MAX_RETRY_COUNT) {
                Log::warning("Factura #{$invoice->id} ha alcanzado el límite máximo de reintentos ({$resolution->dian_retry_count})");
                return false;
            }
            
            // Incrementar contador de reintentos
            $resolution->incrementRetryCount();
            
            // Preparar los datos para enviar a DIAN
            $xmlPath = $invoice->signed_xml_path;
            
            if (!$xmlPath || !file_exists(storage_path($xmlPath))) {
                Log::error("XML firmado no encontrado para la factura #{$invoice->id}");
                return false;
            }
            
            $xmlContents = file_get_contents(storage_path($xmlPath));
            
            // URL de la API según el modo
            $apiUrl = $testMode ? self::TEST_API_URL : self::PROD_API_URL;
            
            // Enviar a DIAN
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($apiUrl . '/SendTestSetAsync', [
                'fileName' => basename($xmlPath),
                'contentFile' => base64_encode($xmlContents),
                'testSetId' => $testMode ? 'TEST-' . $invoice->company->nit : null
            ]);
            
            // Procesar respuesta
            if ($response->successful()) {
                $responseData = $response->json();
                
                // Registrar envío exitoso
                $resolution->update([
                    'dian_sent_at' => now(),
                    'dian_status' => 'PENDING'
                ]);
                
                Log::info("Factura #{$invoice->id} enviada exitosamente a DIAN", [
                    'response' => $responseData
                ]);
                
                return true;
            } else {
                // Registrar error
                $resolution->update([
                    'dian_response_code' => (string) $response->status(),
                    'dian_response_message' => $response->body(),
                    'dian_status' => 'REJECTED'
                ]);
                
                Log::error("Error al enviar factura #{$invoice->id} a DIAN", [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                
                return false;
            }
        } catch (Exception $e) {
            Log::error("Excepción al enviar factura #{$invoice->id} a DIAN: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            // Actualizar estado con el error
            if (isset($resolution)) {
                $resolution->update([
                    'dian_response_message' => "Error: " . $e->getMessage(),
                    'dian_status' => 'REJECTED'
                ]);
            }
            
            return false;
        }
    }
    
    /**
     * Consulta el estado de una factura en DIAN.
     *
     * @param Invoice $invoice Factura a consultar
     * @param bool $testMode Indica si se debe usar el entorno de pruebas
     * @return bool Verdadero si la consulta fue exitosa
     */
    public function checkInvoiceStatus(Invoice $invoice, bool $testMode = false): bool
    {
        try {
            // Verificar si la factura ya tiene una resolución DIAN
            $resolution = $invoice->dianResolution;
            
            if (!$resolution || !$resolution->isSent()) {
                Log::warning("Factura #{$invoice->id} no ha sido enviada a DIAN aún");
                return false;
            }
            
            // URL de la API según el modo
            $apiUrl = $testMode ? self::TEST_API_URL : self::PROD_API_URL;
            
            // Consultar estado en DIAN
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($apiUrl . '/GetStatus', [
                'trackId' => $invoice->cufe,
            ]);
            
            // Procesar respuesta
            if ($response->successful()) {
                $responseData = $response->json();
                
                // Determinar el estado según la respuesta
                $status = 'PENDING';
                $responseCode = $responseData['statusCode'] ?? null;
                $responseMessage = $responseData['statusDescription'] ?? null;
                
                if (isset($responseData['statusCode'])) {
                    // Códigos de estado exitosos (estos varían según la documentación de DIAN)
                    $successCodes = ['00', '0', 'APPROVED', 'ACCEPTED'];
                    
                    if (in_array($responseData['statusCode'], $successCodes)) {
                        $status = 'ACCEPTED';
                    } else {
                        $status = 'REJECTED';
                    }
                }
                
                // Actualizar estado
                $resolution->update([
                    'dian_processed_at' => now(),
                    'dian_status' => $status,
                    'dian_response_code' => $responseCode,
                    'dian_response_message' => $responseMessage
                ]);
                
                Log::info("Estado de factura #{$invoice->id} consultado en DIAN", [
                    'status' => $status,
                    'response' => $responseData
                ]);
                
                return true;
            } else {
                // Registrar error de consulta
                Log::error("Error al consultar estado de factura #{$invoice->id} en DIAN", [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                
                return false;
            }
        } catch (Exception $e) {
            Log::error("Excepción al consultar estado de factura #{$invoice->id} en DIAN: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return false;
        }
    }
    
    /**
     * Envía facturas pendientes a DIAN.
     *
     * @param bool $testMode Indica si se debe usar el entorno de pruebas
     * @return array Resultados del procesamiento
     */
    public function processPendingInvoices(bool $testMode = false): array
    {
        $results = [
            'total' => 0,
            'success' => 0,
            'failed' => 0
        ];
        
        // Obtener facturas que deben enviarse a DIAN
        $invoices = Invoice::whereDoesntHave('dianResolution')
            ->orWhereHas('dianResolution', function ($query) {
                $query->where('dian_status', 'PENDING')
                      ->where('dian_retry_count', '<', self::MAX_RETRY_COUNT)
                      ->whereNull('dian_processed_at');
            })
            ->get();
        
        $results['total'] = $invoices->count();
        
        foreach ($invoices as $invoice) {
            $success = $this->sendInvoice($invoice, $testMode);
            
            if ($success) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }
        
        return $results;
    }
    
    /**
     * Actualiza el estado de facturas pendientes en DIAN.
     *
     * @param bool $testMode Indica si se debe usar el entorno de pruebas
     * @return array Resultados del procesamiento
     */
    public function updatePendingInvoicesStatus(bool $testMode = false): array
    {
        $results = [
            'total' => 0,
            'success' => 0,
            'failed' => 0
        ];
        
        // Obtener facturas con estado pendiente
        $invoices = Invoice::whereHas('dianResolution', function ($query) {
            $query->where('dian_status', 'PENDING')
                  ->whereNotNull('dian_sent_at');
        })->get();
        
        $results['total'] = $invoices->count();
        
        foreach ($invoices as $invoice) {
            $success = $this->checkInvoiceStatus($invoice, $testMode);
            
            if ($success) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }
        
        return $results;
    }
} 