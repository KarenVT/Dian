<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\Company;
use App\Services\MockDianService;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\DTOs\CartDTO;
use App\DTOs\CustomerDTO;
use Carbon\Carbon;

/**
 * Controlador para demostración de integración con DIAN
 */
class DianDemoController extends Controller
{
    /**
     * Servicio de simulación de DIAN
     * 
     * @var MockDianService
     */
    protected $mockDianService;

    /**
     * Constructor del controlador
     * 
     * @param MockDianService $mockDianService
     */
    public function __construct(MockDianService $mockDianService)
    {
        $this->mockDianService = $mockDianService;
    }

    /**
     * Muestra la vista de demostración DIAN
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Obtener las facturas del usuario actual
        $invoices = Invoice::where('company_id', Auth::user()->company_id)
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('dian-demo.index', compact('invoices'));
    }

    /**
     * Mostrar el formulario para generar una factura de demostración
     * 
     * @return \Illuminate\View\View
     */
    public function showGenerateForm()
    {
        return view('dian-demo.generate');
    }

    /**
     * Genera una factura de demostración
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generateInvoice(Request $request)
    {
        // Validar datos de entrada
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|string|max:20',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'item_description' => 'required|array',
            'item_quantity' => 'required|array',
            'item_price' => 'required|array',
            'item_tax' => 'required|array',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Preparar los items del carrito
        $items = [];
        $subtotal = 0;
        $tax = 0;

        for ($i = 0; $i < count($request->item_description); $i++) {
            $quantity = floatval($request->item_quantity[$i]);
            $price = floatval($request->item_price[$i]);
            $taxRate = floatval($request->item_tax[$i]);
            
            $itemSubtotal = $quantity * $price;
            $itemTax = $itemSubtotal * ($taxRate / 100);
            
            $items[] = [
                'code' => 'DEMO-' . ($i + 1),
                'description' => $request->item_description[$i],
                'price' => $price,
                'quantity' => $quantity,
                'tax_percentage' => $taxRate
            ];
            
            $subtotal += $itemSubtotal;
            $tax += $itemTax;
        }

        $total = $subtotal + $tax;

        // Crear objetos DTO
        $cart = new CartDTO(
            $items,
            $subtotal,
            $tax,
            $total,
            'income',
            $request->notes ?? 'Factura de demostración'
        );

        $customer = new CustomerDTO(
            $request->customer_id,
            $request->customer_name,
            $request->customer_email
        );

        try {
            // Obtener la compañía del usuario actual
            $company = Company::findOrFail(Auth::user()->company_id);
            
            // Crear el servicio de facturas
            $invoiceService = new InvoiceService($company);
            
            // Generar la factura
            $invoice = $invoiceService->generateInvoice($cart, $customer);
            
            return redirect()->route('dian-demo.show', $invoice->id)
                ->with('success', 'Factura generada correctamente. Ahora puede enviarla a la DIAN simulada.');
                
        } catch (\Exception $e) {
            Log::error('Error al generar factura de demostración: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Error al generar la factura: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Muestra una factura específica
     * 
     * @param Invoice $invoice
     * @return \Illuminate\View\View
     */
    public function show(Invoice $invoice)
    {
        // Verificar que la factura pertenezca a la compañía del usuario
        if ($invoice->company_id !== Auth::user()->company_id) {
            abort(403, 'No tiene permiso para ver esta factura');
        }

        return view('dian-demo.show', compact('invoice'));
    }

    /**
     * Envía una factura a la DIAN simulada
     * 
     * @param Invoice $invoice
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendToDian(Invoice $invoice)
    {
        // Verificar que la factura pertenezca a la compañía del usuario
        if ($invoice->company_id !== Auth::user()->company_id) {
            abort(403, 'No tiene permiso para enviar esta factura');
        }

        try {
            // Enviar a DIAN simulada
            $result = $this->mockDianService->sendInvoice($invoice);
            
            if ($result['success']) {
                return redirect()->route('dian-demo.show', $invoice->id)
                    ->with('success', 'Factura enviada correctamente a DIAN (simulada). ID de seguimiento: ' . $result['trackId']);
            } else {
                return redirect()->route('dian-demo.show', $invoice->id)
                    ->with('error', 'Error al enviar la factura a DIAN: ' . ($result['message'] ?? 'Error desconocido'));
            }
        } catch (\Exception $e) {
            Log::error('Error al enviar factura a DIAN simulada: ' . $e->getMessage());
            
            return redirect()->route('dian-demo.show', $invoice->id)
                ->with('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }

    /**
     * Consulta el estado de una factura en la DIAN simulada
     * 
     * @param Invoice $invoice
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkStatus(Invoice $invoice)
    {
        // Verificar que la factura pertenezca a la compañía del usuario
        if ($invoice->company_id !== Auth::user()->company_id) {
            abort(403, 'No tiene permiso para consultar esta factura');
        }

        try {
            // Consultar estado en DIAN simulada
            $result = $this->mockDianService->checkStatus($invoice);
            
            if ($result['success']) {
                $statusMessage = "Estado DIAN: {$result['status']} - {$result['statusDescription']}";
                return redirect()->route('dian-demo.show', $invoice->id)
                    ->with('success', 'Estado consultado correctamente. ' . $statusMessage);
            } else {
                return redirect()->route('dian-demo.show', $invoice->id)
                    ->with('error', 'Error al consultar estado en DIAN: ' . ($result['message'] ?? 'Error desconocido'));
            }
        } catch (\Exception $e) {
            Log::error('Error al consultar estado en DIAN simulada: ' . $e->getMessage());
            
            return redirect()->route('dian-demo.show', $invoice->id)
                ->with('error', 'Error al procesar la solicitud: ' . $e->getMessage());
        }
    }

    /**
     * Procesa todas las facturas pendientes
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processPending()
    {
        try {
            // Procesar facturas pendientes
            $stats = $this->mockDianService->processPendingInvoices();
            
            $message = "Procesamiento completado. Total: {$stats['total']}, Procesadas: {$stats['processed']}, " .
                      "Aceptadas: {$stats['accepted']}, Rechazadas: {$stats['rejected']}";
            
            return redirect()->route('dian-demo.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Error al procesar facturas pendientes: ' . $e->getMessage());
            
            return redirect()->route('dian-demo.index')
                ->with('error', 'Error al procesar facturas pendientes: ' . $e->getMessage());
        }
    }
} 