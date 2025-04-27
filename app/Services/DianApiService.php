<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class DianApiService
{
    /**
     * URL base del API de DIAN (se puede configurar por ambiente)
     * 
     * @var string
     */
    protected string $apiBaseUrl;
    
    /**
     * Token de API para autenticación con DIAN
     * 
     * @var string|null
     */
    protected ?string $apiToken;
    
    /**
     * ID del software registrado ante DIAN
     * 
     * @var string|null
     */
    protected ?string $softwareId;
    
    /**
     * Constructor del servicio
     */
    public function __construct()
    {
        // Inicializar configuración desde variables de entorno
        $this->apiBaseUrl = config('dian.api_base_url', 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc');
        $this->apiToken = config('dian.api_token');
        $this->softwareId = config('dian.software_id');
    }
    
    /**
     * Envía una factura a DIAN para validación
     *
     * @param Invoice $invoice
     * @return array Respuesta del API de DIAN
     * @throws Exception
     */
    public function sendInvoice(Invoice $invoice): array
    {
        try {
            // Verificar que la factura pueda ser enviada
            if (!$invoice->canBeSentToDian()) {
                throw new Exception("La factura {$invoice->invoice_number} no puede ser enviada a DIAN");
            }
            
            // Verificar que exista el XML firmado
            if (empty($invoice->signed_xml_path) || !Storage::exists($invoice->signed_xml_path)) {
                throw new Exception("El XML firmado no existe para la factura {$invoice->invoice_number}");
            }
            
            // Leer el XML firmado
            $xmlContent = Storage::get($invoice->signed_xml_path);
            $xmlBase64 = base64_encode($xmlContent);
            
            // Construir payload para DIAN
            $payload = [
                'xmlBase64' => $xmlBase64,
                'softwareId' => $this->softwareId,
                'documentNumber' => $invoice->invoice_number,
                'documentType' => 'INVOICE'
            ];
            
            // Marcar como enviada antes de hacer la solicitud
            $invoice->markAsSentToDian();
            
            // Hacer la petición a DIAN
            $response = Http::withToken($this->apiToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->timeout(30) // 30 segundos de timeout
                ->post("{$this->apiBaseUrl}/SendBillAsync", $payload);
            
            // Procesar la respuesta
            $responseData = $response->json();
            
            // Si es exitosa, registrar el trackId
            if ($response->successful() && isset($responseData['trackId'])) {
                $trackId = $responseData['trackId'];
                
                // Guardar el trackId en los metadatos de respuesta
                $invoice->update([
                    'dian_response_code' => 'trackId',
                    'dian_response_message' => $trackId
                ]);
                
                Log::info("Factura {$invoice->invoice_number} enviada a DIAN con éxito. TrackId: {$trackId}");
                
                return [
                    'success' => true,
                    'trackId' => $trackId,
                    'message' => 'Factura enviada correctamente a DIAN'
                ];
            }
            
            // Si hay error, registrarlo
            $errorMessage = $responseData['message'] ?? 'Error desconocido al enviar a DIAN';
            $errorCode = $responseData['code'] ?? 'UNKNOWN';
            
            $invoice->markAsProcessedByDian('rejected', $errorCode, $errorMessage);
            
            Log::error("Error al enviar factura {$invoice->invoice_number} a DIAN: {$errorMessage}");
            
            return [
                'success' => false,
                'message' => $errorMessage,
                'code' => $errorCode
            ];
        } catch (Exception $e) {
            // Incrementar el contador de reintentos
            $invoice->incrementDianRetryCount();
            
            // Registrar el error
            Log::error("Excepción al enviar factura {$invoice->invoice_number} a DIAN: " . $e->getMessage());
            
            throw $e;
        }
    }
    
    /**
     * Consulta el estado de una factura en DIAN usando el trackId
     *
     * @param Invoice $invoice
     * @return array Respuesta del API de DIAN
     * @throws Exception
     */
    public function checkInvoiceStatus(Invoice $invoice): array
    {
        try {
            // Verificar que la factura tenga un trackId
            if ($invoice->dian_response_code !== 'trackId' || empty($invoice->dian_response_message)) {
                throw new Exception("La factura {$invoice->invoice_number} no tiene un trackId válido");
            }
            
            $trackId = $invoice->dian_response_message;
            
            // Hacer la petición a DIAN
            $response = Http::withToken($this->apiToken)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ])
                ->timeout(30)
                ->get("{$this->apiBaseUrl}/GetStatus/{$trackId}");
            
            // Procesar la respuesta
            $responseData = $response->json();
            
            // Si es exitosa, actualizar el estado
            if ($response->successful()) {
                $statusCode = $responseData['statusCode'] ?? null;
                $statusDescription = $responseData['statusDescription'] ?? 'Sin descripción';
                
                // Determinar el estado basado en el código de estado
                $status = 'pending';
                if ($statusCode == '00') {
                    $status = 'accepted';
                } elseif (in_array($statusCode, ['01', '02', '04', '99'])) {
                    $status = 'rejected';
                }
                
                // Actualizar la factura con el resultado
                $invoice->markAsProcessedByDian($status, $statusCode, $statusDescription);
                
                Log::info("Estado de factura {$invoice->invoice_number} en DIAN: {$status} ({$statusCode}: {$statusDescription})");
                
                return [
                    'success' => true,
                    'status' => $status,
                    'statusCode' => $statusCode,
                    'statusDescription' => $statusDescription
                ];
            }
            
            // Si hay error, registrarlo
            $errorMessage = $responseData['message'] ?? 'Error desconocido al consultar estado en DIAN';
            
            Log::error("Error al consultar estado de factura {$invoice->invoice_number} en DIAN: {$errorMessage}");
            
            return [
                'success' => false,
                'message' => $errorMessage
            ];
        } catch (Exception $e) {
            // Registrar el error
            Log::error("Excepción al consultar estado de factura {$invoice->invoice_number} en DIAN: " . $e->getMessage());
            
            throw $e;
        }
    }
    
    /**
     * Envía y consulta el estado de todas las facturas pendientes
     *
     * @return array Resultados del procesamiento
     */
    public function processPendingInvoices(): array
    {
        $results = [
            'sent' => 0,
            'checked' => 0,
            'accepted' => 0,
            'rejected' => 0,
            'errors' => 0,
            'pending' => 0
        ];
        
        // Procesar facturas que no han sido enviadas aún
        $pendingSendInvoices = Invoice::where('dian_sent_at', null)
            ->where('document_type', 'invoice')
            ->where('dian_retry_count', '<', 3)
            ->take(50)
            ->get();
        
        foreach ($pendingSendInvoices as $invoice) {
            try {
                $sendResult = $this->sendInvoice($invoice);
                if ($sendResult['success']) {
                    $results['sent']++;
                } else {
                    $results['errors']++;
                }
            } catch (Exception $e) {
                $results['errors']++;
                continue;
            }
        }
        
        // Procesar facturas que ya fueron enviadas pero están pendientes de respuesta
        $pendingStatusInvoices = Invoice::whereNotNull('dian_sent_at')
            ->where('dian_status', 'pending')
            ->whereNull('dian_processed_at')
            ->where('dian_response_code', 'trackId')
            ->take(50)
            ->get();
        
        foreach ($pendingStatusInvoices as $invoice) {
            try {
                $statusResult = $this->checkInvoiceStatus($invoice);
                $results['checked']++;
                
                if ($statusResult['success']) {
                    switch ($invoice->dian_status) {
                        case 'accepted':
                            $results['accepted']++;
                            break;
                        case 'rejected':
                            $results['rejected']++;
                            break;
                        case 'pending':
                            $results['pending']++;
                            break;
                    }
                }
            } catch (Exception $e) {
                $results['errors']++;
                continue;
            }
        }
        
        return $results;
    }
} 
 