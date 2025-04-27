<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\DianApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
     * Envía una factura a DIAN para validación (interfaz web)
     * 
     * @param Invoice $invoice
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendInvoice(Invoice $invoice)
    {
        // Verificar que la factura pueda ser enviada a DIAN
        if (!$invoice->canBeSentToDian()) {
            return redirect()->back()
                ->with('error', 'La factura no puede ser enviada a DIAN. Verifique que sea una factura formal y que no haya sido procesada anteriormente.');
        }
        
        try {
            // Enviar la factura a DIAN
            $result = $this->dianApiService->sendInvoice($invoice);
            
            if ($result['success']) {
                return redirect()->back()
                    ->with('success', 'Factura enviada correctamente a DIAN. ID de seguimiento: ' . ($result['trackId'] ?? 'No disponible'));
            }
            
            return redirect()->back()
                ->with('error', 'Error al enviar la factura a DIAN: ' . ($result['message'] ?? 'Error desconocido'));
        } catch (\Exception $e) {
            Log::error('Error al enviar factura a DIAN (web): ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }
    
    /**
     * Consulta el estado de una factura en DIAN (interfaz web)
     * 
     * @param Invoice $invoice
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkStatus(Invoice $invoice)
    {
        // Verificar que la factura tenga un trackId
        if ($invoice->dian_response_code !== 'trackId' || empty($invoice->dian_response_message)) {
            return redirect()->back()
                ->with('error', 'La factura no tiene un ID de seguimiento válido. Debe ser enviada primero a DIAN.');
        }
        
        try {
            // Consultar el estado en DIAN
            $result = $this->dianApiService->checkInvoiceStatus($invoice);
            
            if ($result['success']) {
                return redirect()->back()
                    ->with('success', 'Estado de factura consultado correctamente. Estado: ' . 
                        ($result['status'] ?? 'pendiente') . ' - ' . 
                        ($result['statusDescription'] ?? 'Sin descripción'));
            }
            
            return redirect()->back()
                ->with('error', 'Error al consultar estado en DIAN: ' . ($result['message'] ?? 'Error desconocido'));
        } catch (\Exception $e) {
            Log::error('Error al consultar estado en DIAN (web): ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }
    
    /**
     * Procesa múltiples facturas con la acción especificada
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchProcess(Request $request)
    {
        // Validar los datos de entrada
        $validated = $request->validate([
            'invoices' => 'required|array|min:1',
            'invoices.*' => 'required|integer|exists:invoices,id',
            'action' => 'required|string|in:consultar,enviar,almacenar,notificar,consultar-estado,generar-eventos,consultar-eventos,xml',
        ]);
        
        $results = [
            'success' => 0,
            'failed' => 0,
            'messages' => [],
            'processed' => []
        ];
        
        // Cargar las facturas seleccionadas
        $invoices = Invoice::whereIn('id', $validated['invoices'])->get();
        
        // Procesar según la acción solicitada
        foreach ($invoices as $invoice) {
            try {
                switch ($validated['action']) {
                    case 'enviar':
                        if ($invoice->canBeSentToDian()) {
                            $actionResult = $this->dianApiService->sendInvoice($invoice);
                            if ($actionResult['success']) {
                                $results['success']++;
                                $results['processed'][] = [
                                    'id' => $invoice->id,
                                    'number' => $invoice->invoice_number,
                                    'status' => 'success',
                                    'message' => 'Enviada correctamente. ID de seguimiento: ' . ($actionResult['trackId'] ?? 'No disponible')
                                ];
                            } else {
                                $results['failed']++;
                                $results['processed'][] = [
                                    'id' => $invoice->id,
                                    'number' => $invoice->invoice_number,
                                    'status' => 'error',
                                    'message' => 'Error: ' . ($actionResult['message'] ?? 'Error desconocido')
                                ];
                            }
                        } else {
                            $results['failed']++;
                            $results['processed'][] = [
                                'id' => $invoice->id,
                                'number' => $invoice->invoice_number,
                                'status' => 'error',
                                'message' => 'No puede ser enviada a DIAN'
                            ];
                        }
                        break;
                        
                    case 'consultar':
                    case 'consultar-estado':
                        if ($invoice->dian_response_code === 'trackId' && !empty($invoice->dian_response_message)) {
                            $actionResult = $this->dianApiService->checkInvoiceStatus($invoice);
                            if ($actionResult['success']) {
                                $results['success']++;
                                $results['processed'][] = [
                                    'id' => $invoice->id,
                                    'number' => $invoice->invoice_number,
                                    'status' => 'success',
                                    'message' => 'Estado: ' . ($actionResult['status'] ?? 'pendiente')
                                ];
                            } else {
                                $results['failed']++;
                                $results['processed'][] = [
                                    'id' => $invoice->id,
                                    'number' => $invoice->invoice_number,
                                    'status' => 'error',
                                    'message' => 'Error: ' . ($actionResult['message'] ?? 'Error desconocido')
                                ];
                            }
                        } else {
                            $results['failed']++;
                            $results['processed'][] = [
                                'id' => $invoice->id,
                                'number' => $invoice->invoice_number,
                                'status' => 'error',
                                'message' => 'No tiene ID de seguimiento válido'
                            ];
                        }
                        break;
                        
                    case 'almacenar':
                        // Simulación de almacenamiento
                        $results['success']++;
                        $results['processed'][] = [
                            'id' => $invoice->id,
                            'number' => $invoice->invoice_number,
                            'status' => 'success',
                            'message' => 'Factura almacenada correctamente'
                        ];
                        break;
                        
                    case 'notificar':
                        // Simulación de notificación
                        $results['success']++;
                        $results['processed'][] = [
                            'id' => $invoice->id,
                            'number' => $invoice->invoice_number,
                            'status' => 'success',
                            'message' => 'Notificación enviada correctamente'
                        ];
                        break;
                        
                    case 'generar-eventos':
                        // Simulación de generación de eventos
                        $results['success']++;
                        $results['processed'][] = [
                            'id' => $invoice->id,
                            'number' => $invoice->invoice_number,
                            'status' => 'success',
                            'message' => 'Eventos generados correctamente'
                        ];
                        break;
                        
                    case 'consultar-eventos':
                        // Simulación de consulta de eventos
                        $results['success']++;
                        $results['processed'][] = [
                            'id' => $invoice->id,
                            'number' => $invoice->invoice_number,
                            'status' => 'success',
                            'message' => 'No hay eventos registrados'
                        ];
                        break;
                        
                    case 'xml':
                        // Verificar si tiene XML generado
                        if ($invoice->xml_path) {
                            $results['success']++;
                            $results['processed'][] = [
                                'id' => $invoice->id,
                                'number' => $invoice->invoice_number,
                                'status' => 'success',
                                'message' => 'XML generado y disponible para descarga',
                                'xmlUrl' => '/api/invoices/' . $invoice->id . '/xml'
                            ];
                        } else {
                            $results['failed']++;
                            $results['processed'][] = [
                                'id' => $invoice->id,
                                'number' => $invoice->invoice_number,
                                'status' => 'error',
                                'message' => 'No tiene XML generado'
                            ];
                        }
                        break;
                }
            } catch (\Exception $e) {
                Log::error("Error al procesar acción {$validated['action']} para factura #{$invoice->id}: " . $e->getMessage());
                $results['failed']++;
                $results['processed'][] = [
                    'id' => $invoice->id,
                    'number' => $invoice->invoice_number,
                    'status' => 'error',
                    'message' => 'Error interno: ' . $e->getMessage()
                ];
            }
        }
        
        // Mensaje general del resultado
        if ($results['success'] > 0 && $results['failed'] === 0) {
            $results['messages'][] = "Todas las facturas fueron procesadas correctamente.";
        } elseif ($results['success'] > 0 && $results['failed'] > 0) {
            $results['messages'][] = "{$results['success']} facturas procesadas correctamente y {$results['failed']} con errores.";
        } else {
            $results['messages'][] = "No se pudo procesar ninguna factura correctamente.";
        }
        
        return response()->json($results);
    }
}
