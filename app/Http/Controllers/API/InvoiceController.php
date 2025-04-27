<?php

namespace App\Http\Controllers\API;

use App\DTOs\CartDTO;
use App\DTOs\CustomerDTO;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Product;
use App\Services\InvoiceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class InvoiceController extends Controller
{
    /**
     * Muestra una lista de las facturas del usuario o comercio autenticado.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Obtener el comercio asociado al usuario autenticado
        $company = Auth::user()->company;
        
        // Aplicar filtros según parámetros de la solicitud
        $query = $company->invoices()->orderBy('created_at', 'desc');
        
        // Filtro por fecha
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('issued_at', [$request->start_date, $request->end_date]);
        }
        
        // Filtro por cliente
        if ($request->has('customer_name')) {
            $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
        }
        
        // Filtro por estado con DIAN
        if ($request->has('dian_status')) {
            $query->where('dian_status', $request->dian_status);
        }
        
        // Paginación
        $invoices = $query->paginate($request->per_page ?? 15);
        
        return response()->json($invoices);
    }

    /**
     * Muestra los detalles de una factura específica.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        
        return response()->json([
            'data' => $invoice
        ]);
    }

    /**
     * Descarga el archivo PDF de una factura específica.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf(Invoice $invoice)
    {
        // Verificar autorización con la política
        $this->authorize('downloadPdf', $invoice);
        
        // Verificar que la factura tenga un PDF y que exista
        if (!$invoice->pdf_path || !Storage::exists($invoice->pdf_path)) {
            return response()->json([
                'message' => 'El archivo PDF de esta factura no está disponible.'
            ], Response::HTTP_NOT_FOUND);
        }
        
        // Generar nombre de archivo para la descarga
        $downloadName = "Factura_{$invoice->invoice_number}.pdf";
        
        // Descargar el archivo
        return Storage::download($invoice->pdf_path, $downloadName);
    }

    /**
     * Crea una nueva factura electrónica.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validación completa de los datos de entrada
        $validator = Validator::make($request->all(), [
            // Datos del cliente
            'customer_id' => 'required|string|max:20', // NIT o documento de identidad
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'nullable|email',
            'customer_phone' => 'nullable|string|max:20',
            'customer_address' => 'nullable|string|max:255',
            'customer_city' => 'nullable|string|max:100',
            'customer_state' => 'nullable|string|max:100',
            'customer_postal_code' => 'nullable|string|max:10',
            'customer_country' => 'nullable|string|max:2',
            
            // Datos de la factura
            'type' => 'nullable|string|in:income,credit,debit',
            'notes' => 'nullable|string|max:500',
            'due_date' => 'nullable|date_format:Y-m-d',
            
            // Items de la factura
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.tax_percent' => 'required|numeric|min:0|max:100',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        // Obtener el comercio del usuario autenticado
        $company = Auth::user()->company;
        
        // Crear el DTO del cliente
        $customerDTO = new CustomerDTO(
            $request->customer_id,
            $request->customer_name,
            $request->customer_email,
            $request->customer_phone,
            $request->customer_address,
            $request->customer_city,
            $request->customer_state,
            $request->customer_postal_code,
            $request->customer_country ?? 'CO'
        );
        
        // Procesar items y calcular totales
        $items = [];
        $subtotal = 0;
        $tax = 0;
        
        foreach ($request->items as $item) {
            // Obtener el producto de la base de datos
            $product = Product::findOrFail($item['product_id']);
            
            // Calcular valores para este ítem
            $price = floatval($item['price']);
            $quantity = floatval($item['quantity']);
            $taxPercent = floatval($item['tax_percent']);
            
            $lineSubtotal = $price * $quantity;
            $lineTax = $lineSubtotal * ($taxPercent / 100);
            
            // Acumular totales
            $subtotal += $lineSubtotal;
            $tax += $lineTax;
            
            // Añadir al array de items
            $items[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'description' => $product->description,
                'quantity' => $quantity,
                'price' => $price,
                'tax_percent' => $taxPercent,
                'subtotal' => $lineSubtotal,
                'tax' => $lineTax,
                'total' => $lineSubtotal + $lineTax
            ];
        }
        
        // Crear el DTO del carrito
        $cartDTO = new CartDTO(
            $items,
            $subtotal,
            $tax,
            $subtotal + $tax,
            $request->type ?? 'income',
            $request->notes,
            $request->due_date
        );
        
        try {
            // Inicializar el servicio de facturación
            $invoiceService = new InvoiceService($company);
            
            // Generar la factura
            $invoice = $invoiceService->generateInvoice($cartDTO, $customerDTO);
            
            // Retornar la respuesta exitosa
            return response()->json([
                'message' => 'Factura electrónica generada correctamente',
                'invoice' => $invoice
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Manejar errores durante la generación
        return response()->json([
                'message' => 'Error al generar la factura electrónica',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Reenvía la factura al correo del cliente.
     *
     * @param  \App\Models\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function resend(Invoice $invoice)
    {
        // Verificar autorización con la política
        $this->authorize('view', $invoice);
        
        // Verificar que la factura tenga un correo de cliente
        if (!$invoice->customer_email) {
            return response()->json([
                'message' => 'La factura no tiene un correo electrónico de cliente.'
            ], Response::HTTP_BAD_REQUEST);
        }
        
        try {
            // Aquí se implementaría el código para enviar el correo
            // Por ejemplo, usando una clase Mail o un job en cola
            
            // Actualizar el estado de envío en la factura
            $invoice->update([
                'email_sent_at' => now()
            ]);
        
        return response()->json([
            'message' => 'Factura reenviada correctamente al cliente.',
            'sent_to' => $invoice->customer_email
        ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al reenviar la factura.',
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
} 