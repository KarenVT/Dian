<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detalles de Factura DIAN') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Mensajes de alerta -->
            @if (session('success'))
                <div class="mb-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            <!-- Tarjeta de información -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Factura #{{ $invoice->invoice_number }}</h3>
                            <p class="text-sm text-gray-600">Emitida: {{ $invoice->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        
                        <div class="text-right">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                {{ $invoice->document_type === 'invoice' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ $invoice->document_type === 'invoice' ? 'Factura Electrónica' : 'Ticket POS' }}
                            </span>
                            
                            @if($invoice->dian_status === 'ACCEPTED')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 mt-1">
                                    Aceptada por DIAN
                                </span>
                            @elseif($invoice->dian_status === 'REJECTED')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800 mt-1">
                                    Rechazada por DIAN
                                </span>
                            @elseif($invoice->dian_status === 'PENDING')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 mt-1">
                                    Validación DIAN en proceso
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 mt-1">
                                    No enviada a DIAN
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Datos del cliente y totales -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Datos del Cliente</h4>
                            <p class="text-sm"><span class="font-medium">ID:</span> {{ $invoice->customer_id }}</p>
                            <p class="text-sm"><span class="font-medium">Nombre:</span> {{ $invoice->customer_name }}</p>
                            <p class="text-sm"><span class="font-medium">Email:</span> {{ $invoice->customer_email }}</p>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">Totales</h4>
                            <p class="text-sm"><span class="font-medium">Subtotal:</span> ${{ number_format($invoice->subtotal, 2, ',', '.') }}</p>
                            <p class="text-sm"><span class="font-medium">IVA:</span> ${{ number_format($invoice->tax, 2, ',', '.') }}</p>
                            <p class="text-sm font-medium text-lg"><span class="font-medium">Total:</span> ${{ number_format($invoice->total, 2, ',', '.') }}</p>
                        </div>
                    </div>
                    
                    <!-- Acciones DIAN -->
                    <div class="mt-6 flex space-x-4">
                        <a href="{{ route('dian-demo.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Volver
                        </a>
                        
                        <!-- Solo mostrar el botón si no está enviada o está rechazada -->
                        @if(!$invoice->dian_status || $invoice->dian_status === 'REJECTED')
                            <form action="{{ route('dian-demo.send', $invoice->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Enviar a DIAN
                                </button>
                            </form>
                        @endif
                        
                        <!-- Solo mostrar el botón si está pendiente -->
                        @if($invoice->dian_status === 'PENDING')
                            <a href="{{ route('dian-demo.check-status', $invoice->id) }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Consultar Estado
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            
            <!-- Tabla de detalles -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Ítems de la Factura</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unit.</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IVA</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($invoice->details as $detail)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $detail->software_description }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($detail->quantity, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            ${{ number_format($detail->unit_price, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($detail->tax_rate, 2, ',', '.') }}%
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                            ${{ number_format($detail->total, 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No hay ítems para mostrar
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Información DIAN -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Información DIAN</h3>
                    
                    @if($invoice->dian_status)
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <tbody class="divide-y divide-gray-200">
                                    <tr>
                                        <td class="px-6 py-3 text-left text-sm font-medium text-gray-900">Estado</td>
                                        <td class="px-6 py-3 text-left text-sm text-gray-500">
                                            @if($invoice->dian_status === 'ACCEPTED')
                                                <span class="text-green-600 font-medium">Aceptada</span>
                                            @elseif($invoice->dian_status === 'REJECTED')
                                                <span class="text-red-600 font-medium">Rechazada</span>
                                            @elseif($invoice->dian_status === 'PENDING')
                                                <span class="text-yellow-600 font-medium">En proceso</span>
                                            @else
                                                {{ $invoice->dian_status }}
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-3 text-left text-sm font-medium text-gray-900">Código de respuesta</td>
                                        <td class="px-6 py-3 text-left text-sm text-gray-500">{{ $invoice->dian_response_code ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-3 text-left text-sm font-medium text-gray-900">Mensaje</td>
                                        <td class="px-6 py-3 text-left text-sm text-gray-500">{{ $invoice->dian_response_message ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-3 text-left text-sm font-medium text-gray-900">Fecha de envío</td>
                                        <td class="px-6 py-3 text-left text-sm text-gray-500">{{ $invoice->dian_sent_at ? $invoice->dian_sent_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-3 text-left text-sm font-medium text-gray-900">Fecha de procesamiento</td>
                                        <td class="px-6 py-3 text-left text-sm text-gray-500">{{ $invoice->dian_processed_at ? $invoice->dian_processed_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-3 text-left text-sm font-medium text-gray-900">CUFE</td>
                                        <td class="px-6 py-3 text-left text-sm text-gray-500">{{ $invoice->cufe ?? 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-6">
                            <p class="text-gray-500">Esta factura aún no ha sido enviada a DIAN.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 