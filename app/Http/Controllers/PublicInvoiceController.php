<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PublicInvoiceController extends Controller
{
    /**
     * Muestra la factura pública usando el token de acceso
     *
     * @param string $token
     * @return \Illuminate\Http\Response
     */
    public function show(string $token)
    {
        $invoice = Invoice::where('access_token', $token)->firstOrFail();
        
        return view('invoices.public-view', [
            'invoice' => $invoice,
            'downloadUrl' => route('public.invoices.pdf', $token)
        ]);
    }
    
    /**
     * Descarga el PDF de la factura usando el token de acceso
     *
     * @param string $token
     * @return \Illuminate\Http\Response
     */
    public function downloadPdf(string $token)
    {
        $invoice = Invoice::where('access_token', $token)->firstOrFail();
        
        if (!$invoice->pdf_path || !Storage::exists($invoice->pdf_path)) {
            abort(404, 'El archivo PDF de la factura no está disponible.');
        }
        
        return response()->download(
            storage_path('app/' . $invoice->pdf_path),
            "factura_{$invoice->invoice_number}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }
}
