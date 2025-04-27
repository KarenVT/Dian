<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Services\DianApiService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class DianController extends Controller
{
    /**
     * Servicio de API de DIAN
     * 
     * @var DianApiService
     */
    protected $dianApiService;
    
    /**
     * Constructor del controlador
     * 
     * @param DianApiService $dianApiService
     */
    public function __construct(DianApiService $dianApiService)
    {
        $this->dianApiService = $dianApiService;
    }
    
    /**
     * Envía una factura a DIAN para validación
     * 
     * @param Invoice $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendInvoice(Invoice $invoice)
    {
        // Verificar que la factura pueda ser enviada a DIAN
        if (!$invoice->canBeSentToDian()) {
            return response()->json([
                'success' => false,
                'message' => 'La factura no puede ser enviada a DIAN. Verifique que sea una factura formal y que no haya sido procesada anteriormente.'
            ], HttpResponse::HTTP_BAD_REQUEST);
        }
        
        try {
            // Enviar la factura a DIAN
            $result = $this->dianApiService->sendInvoice($invoice);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Factura enviada correctamente a DIAN',
                    'trackId' => $result['trackId'] ?? null,
                    'invoice' => $invoice
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Error al enviar la factura a DIAN',
                'code' => $result['code'] ?? null,
                'invoice' => $invoice
            ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Error al enviar factura a DIAN: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Consulta el estado de una factura en DIAN
     * 
     * @param Invoice $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkStatus(Invoice $invoice)
    {
        // Verificar que la factura tenga un trackId
        if ($invoice->dian_response_code !== 'trackId' || empty($invoice->dian_response_message)) {
            return response()->json([
                'success' => false,
                'message' => 'La factura no tiene un trackId válido. Debe ser enviada primero a DIAN.'
            ], HttpResponse::HTTP_BAD_REQUEST);
        }
        
        try {
            // Consultar el estado en DIAN
            $result = $this->dianApiService->checkInvoiceStatus($invoice);
            
            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Estado consultado correctamente',
                    'status' => $result['status'] ?? 'pending',
                    'statusCode' => $result['statusCode'] ?? null,
                    'statusDescription' => $result['statusDescription'] ?? null,
                    'invoice' => $invoice
                ]);
            }
            
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Error al consultar estado en DIAN',
                'invoice' => $invoice
            ], HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::error('Error al consultar estado en DIAN: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la solicitud: ' . $e->getMessage()
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
    /**
     * Procesa las facturas pendientes (envío y consulta de estado)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function processPendingInvoices()
    {
        try {
            // Procesar facturas pendientes
            $results = $this->dianApiService->processPendingInvoices();
            
            return response()->json([
                'success' => true,
                'message' => 'Procesamiento completado',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Error al procesar facturas pendientes: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar facturas pendientes: ' . $e->getMessage()
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
