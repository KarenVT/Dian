<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\InvoiceDetail;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::query();
        
        // Aplicar filtros si existen
        if ($request->filled('invoice_number')) {
            $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
        }
        
        if ($request->filled('start_date')) {
            $query->whereDate('issued_at', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('issued_at', '<=', $request->end_date);
        }
        
        if ($request->filled('status') && $request->status !== '') {
            $query->where('dian_status', $request->status);
        }
        
        $invoices = $query->orderBy('created_at', 'desc')->paginate(10);
        
        // Si es una solicitud AJAX, devolver solo la vista parcial
        if ($request->ajax() || $request->has('ajax')) {
            return view('invoices.partials.invoice-table', compact('invoices'));
        }
        
        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create()
    {
        return view('invoices.create');
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice)
    {
        // Cargar relaciones necesarias
        $invoice->load('company');
        
        return view('invoices.show', compact('invoice'));
    }
    
    /**
     * Resend the invoice to the customer.
     */
    public function resend(Invoice $invoice)
    {
        try {
            // Llamar a la API interna directamente sin token
            $response = Http::put("/api/invoices/{$invoice->id}/resend");
            
            if ($response->successful()) {
                return redirect()->back()->with('success', 'Factura reenviada correctamente al cliente.');
            }
            
            return redirect()->back()->with('error', 'No se pudo reenviar la factura: ' . $response->json('message', 'Error desconocido'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al reenviar la factura: ' . $e->getMessage());
        }
    }
    
    /**
     * Genera una factura desde el formulario web.
     * Esta ruta es accesible para usuarios con el permiso 'sell'.
     */
    public function generateInvoice(Request $request)
    {
        try {
            // Obtener los datos del comerciante automáticamente del usuario autenticado
            $user = Auth::user();
            $companyId = $user->company_id;
            
            if (!$companyId) {
                return response()->json(['error' => 'Usuario sin compañía asignada'], 400);
            }
            
            // Cargar los datos de la compañía para la factura
            $company = \App\Models\Company::findOrFail($companyId);
            
            // Validar datos de entrada (solo datos del cliente y productos)
            $validator = Validator::make($request->all(), [
                'document_number' => 'required|string',
                'name' => 'nullable|string',
                'email' => 'nullable|email',
                'phone' => 'required|string',
                'address' => 'required|string',
                'document_type' => 'required|string',
                'payment_method' => 'required|string',
                'payment_means' => 'required|string',
                'operation_type' => 'required|string',
                'due_date' => 'required|date',
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|integer|exists:products,id',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'items.*.price' => 'required|numeric|min:0',
            ]);
            
            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }
            
            // Crear la factura
            DB::beginTransaction();
            
            try {
                // Crear la factura con campos DIAN
                $invoice = new Invoice();
                
                // Calcular subtotal y tax primero
                $subtotal = 0;
                $tax = 0;
                
                foreach ($request->items as $item) {
                    $itemSubtotal = $item['price'] * $item['quantity'];
                    $itemTax = $itemSubtotal * ($item['tax_percent'] / 100);
                    
                    $subtotal += $itemSubtotal;
                    $tax += $itemTax;
                }
                
                // Datos del comerciante (obtenidos automáticamente)
                $invoice->company_id = $companyId;
                
                // Datos de la compañía - no guardar campos que no existen en la tabla
                // Estos datos deben ser manejados por relaciones
                
                // Datos del cliente (recibidos del formulario)
                $invoice->customer_id = $request->document_number;
                $invoice->customer_name = $request->name ?? 'Cliente General'; // Valor predeterminado si name es null
                $invoice->customer_email = $request->email; // Este campo es nullable en la tabla
                
                // Datos adicionales del cliente - guardar en la tabla customers
                if (!empty($request->document_number)) {
                    // Buscar si el cliente ya existe para esta compañía
                    $customer = \App\Models\Customer::where('company_id', $companyId)
                        ->where('document_number', $request->document_number)
                        ->first();
                    
                    if (!$customer) {
                        // Crear nuevo cliente si no existe
                        $customer = new \App\Models\Customer();
                        $customer->company_id = $companyId;
                        $customer->document_type = $request->document_type;
                        $customer->document_number = $request->document_number;
                    }
                    
                    // Actualizar los datos del cliente
                    $customer->name = $request->name ?? 'Cliente General';
                    $customer->email = $request->email;
                    $customer->phone = $request->phone;
                    $customer->address = $request->address;
                    $customer->save();
                }
                
                // Datos de la factura
                $invoice->type = $request->type ?? 'income';
                $invoice->document_type = Invoice::determineDocumentType($subtotal + $tax); // Usar el método estático de la clase Invoice
                $invoice->notes = $request->notes ?? '';
                $invoice->due_date = $request->due_date;
                $invoice->issued_at = now(); // Fecha de emisión es ahora
                
                // Generar número de factura
                $invoice->invoice_number = $this->generateInvoiceNumber($companyId);
                
                // Actualizar totales de la factura
                $invoice->subtotal = $subtotal;
                $invoice->tax = $tax;
                $invoice->total = $subtotal + $tax;
                
                // Generar token de acceso único
                $invoice->access_token = Str::random(64);
                
                $invoice->save();
                
                // Agregar detalles
                foreach ($request->items as $item) {
                    $product = Product::find($item['product_id']);
                    
                    if (!$product || $product->company_id != $companyId) {
                        throw new \Exception('Producto no válido o no pertenece a su empresa');
                    }
                    
                    $itemSubtotal = $item['price'] * $item['quantity'];
                    $itemTax = $itemSubtotal * ($item['tax_percent'] / 100);
                    
                    $detail = new InvoiceDetail();
                    $detail->invoice_id = $invoice->id;
                    $detail->product_id = $item['product_id'];
                    $detail->quantity = $item['quantity'];
                    $detail->unit_price = $item['price'];
                    $detail->tax_rate = $item['tax_percent'];
                    $detail->tax_amount = $itemTax;
                    $detail->subtotal = $itemSubtotal;
                    $detail->total = $itemSubtotal + $itemTax;
                    $detail->software_description = $product->name ?? 'Producto';
                    $detail->discount = 0;
                    $detail->save();
                }
                
                // Actualizar totales de la factura
                $invoice->subtotal = $subtotal;
                $invoice->tax = $tax;
                $invoice->total = $subtotal + $tax;
                $invoice->save();
                
                DB::commit();
                
                return response()->json([
                    'success' => true, 
                    'message' => 'Factura generada exitosamente',
                    'invoice' => $invoice
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al generar la factura: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Genera un número de factura único para la compañía
     * 
     * @param int $companyId ID de la compañía
     * @return string Número de factura generado
     */
    private function generateInvoiceNumber(int $companyId): string
    {
        // Obtener el último número de factura para esta compañía
        $lastInvoice = Invoice::where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();
        
        $prefix = 'FACT';
        $number = 1;
        
        if ($lastInvoice) {
            // Extraer el número de la última factura si existe
            $lastNumber = preg_replace('/[^0-9]/', '', $lastInvoice->invoice_number);
            $number = intval($lastNumber) + 1;
        }
        
        // Formatear el número con ceros a la izquierda
        return $prefix . '-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }
} 