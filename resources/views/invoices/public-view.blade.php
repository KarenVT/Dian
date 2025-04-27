<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #{{ $invoice->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
            <div class="bg-blue-600 py-4 px-6">
                <h1 class="text-white text-2xl font-bold">Factura Electrónica</h1>
            </div>
            
            <div class="p-6">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h2 class="text-xl font-semibold">Factura #{{ $invoice->invoice_number }}</h2>
                        <p class="text-gray-600">Fecha: {{ $invoice->issued_at->format('d/m/Y') }}</p>
                        @if($invoice->due_date)
                            <p class="text-gray-600">Vencimiento: {{ $invoice->due_date->format('d/m/Y') }}</p>
                        @endif
                    </div>
                    <div>
                        <a href="{{ $downloadUrl }}" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Descargar PDF
                        </a>
                    </div>
                </div>
                
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-2">Información del Cliente</h3>
                    <div class="bg-gray-50 p-4 rounded">
                        <p><strong>ID/NIT:</strong> {{ $invoice->customer_id }}</p>
                        <p><strong>Nombre:</strong> {{ $invoice->customer_name }}</p>
                        @if($invoice->customer_email)
                            <p><strong>Email:</strong> {{ $invoice->customer_email }}</p>
                        @endif
                    </div>
                </div>
                
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-2">Valores</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <tbody>
                                <tr class="border-b">
                                    <td class="py-3 px-4 text-right font-medium">Subtotal:</td>
                                    <td class="py-3 px-4 text-right">{{ number_format($invoice->subtotal, 2, ',', '.') }}</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-3 px-4 text-right font-medium">IVA:</td>
                                    <td class="py-3 px-4 text-right">{{ number_format($invoice->tax, 2, ',', '.') }}</td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td class="py-3 px-4 text-right font-bold">Total:</td>
                                    <td class="py-3 px-4 text-right font-bold">{{ number_format($invoice->total, 2, ',', '.') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if($invoice->notes)
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-2">Notas</h3>
                    <div class="bg-gray-50 p-4 rounded">
                        <p>{{ $invoice->notes }}</p>
                    </div>
                </div>
                @endif
                
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="flex items-center">
                        <div class="bg-{{ $invoice->isAcceptedByDian() ? 'green' : ($invoice->isRejectedByDian() ? 'red' : 'yellow') }}-100 rounded-full px-3 py-1 text-sm font-semibold text-{{ $invoice->isAcceptedByDian() ? 'green' : ($invoice->isRejectedByDian() ? 'red' : 'yellow') }}-800 mr-3">
                            Estado DIAN: {{ $invoice->dianResolution ? $invoice->dianResolution->dian_status : 'PENDING' }}
                        </div>
                        
                        @if($invoice->cufe)
                            <div class="text-xs text-gray-500">
                                CUFE: {{ $invoice->cufe }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
 