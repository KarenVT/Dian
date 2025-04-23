<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
        // Lógica para listar facturas según el usuario autenticado
        // Este método se implementaría en futuras iteraciones
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
} 