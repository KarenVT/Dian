<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Detalles de Factura') }} #{{ $invoice->invoice_number }}
            </h2>
            <button id="shareButton" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                    </svg>
                    {{ __('Compartir') }}
                </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Navegación por pestañas -->
                <div class="mb-4 border-b border-gray-200">
                <ul class="flex flex-wrap -mb-px text-sm font-medium text-center" role="tablist">
                        <li class="mr-2" role="presentation">
                        <button id="general-tab" class="inline-block p-4 border-b-2 border-blue-600 rounded-t-lg" type="button" role="tab" aria-controls="general" aria-selected="true">Información General</button>
                        </li>
                        <li class="mr-2" role="presentation">
                        <button id="details-tab" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" type="button" role="tab" aria-controls="details" aria-selected="false">Detalles</button>
                        </li>
                        <li class="mr-2" role="presentation">
                        <button id="summary-tab" class="inline-block p-4 border-b-2 border-transparent rounded-t-lg hover:text-gray-600 hover:border-gray-300" type="button" role="tab" aria-controls="summary" aria-selected="false">Resumen</button>
                        </li>
                    </ul>
                </div>
                
            <!-- Contenidos de las pestañas -->
            <div class="tab-content" id="tabContent">
                <!-- Tab 1: Información General -->
                    <div class="block" id="general" role="tabpanel" aria-labelledby="general-tab">
                    <!-- Contenido de la pestaña información general aquí -->
                    <!-- Sección 1: Información de la Factura -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Información de la Factura</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Información Básica</h4>
                                    <ul class="space-y-2">
                                        <li class="flex justify-between">
                                            <span class="text-gray-600">Número:</span>
                                            <span class="font-medium">{{ $invoice->invoice_number }}</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-gray-600">Fecha de emisión:</span>
                                            <span class="font-medium">{{ $invoice->issued_at ? $invoice->issued_at->format('d/m/Y') : 'N/A' }}</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-gray-600">Fecha de vencimiento:</span>
                                            <span class="font-medium">{{ $invoice->due_date ? date('d/m/Y', strtotime($invoice->due_date)) : 'N/A' }}</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-gray-600">Estado:</span>
                                            <span class="font-medium">
                                                @if($invoice->status == 'paid')
                                                    <span class="px-2 py-1 text-xs text-green-800 bg-green-100 rounded-full">Pagada</span>
                                                @elseif($invoice->status == 'partial')
                                                    <span class="px-2 py-1 text-xs text-yellow-800 bg-yellow-100 rounded-full">Pago Parcial</span>
                                                @elseif($invoice->status == 'overdue')
                                                    <span class="px-2 py-1 text-xs text-red-800 bg-red-100 rounded-full">Vencida</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs text-blue-800 bg-blue-100 rounded-full">Pendiente</span>
                                                @endif
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                                
                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Valores</h4>
                                    <ul class="space-y-2">
                                        <li class="flex justify-between">
                                            <span class="text-gray-600">Subtotal:</span>
                                            <span class="font-medium">${{ number_format($invoice->subtotal, 2) }}</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-gray-600">IVA:</span>
                                            <span class="font-medium">${{ number_format($invoice->tax, 2) }}</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-gray-600">Total:</span>
                                            <span class="font-medium text-lg">${{ number_format($invoice->total, 2) }}</span>
                                        </li>
                                    </ul>
                            </div>

                                <div>
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Información DIAN</h4>
                                    <ul class="space-y-2">
                                        <li class="flex justify-between">
                                            <span class="text-gray-600">CUFE:</span>
                                            <span class="font-medium" id="cufe-display">{{ $invoice->cufe ?? 'Sin CUFE' }}</span>
                                        </li>
                                        <li class="flex justify-between">
                                            <span class="text-gray-600">Estado DIAN:</span>
                                            <span class="font-medium" id="dian-status-display">
                                                @if($invoice->dian_status == 'approved')
                                                    <span class="px-2 py-1 text-xs text-green-800 bg-green-100 rounded-full">Aprobada</span>
                                                @elseif($invoice->dian_status == 'rejected')
                                                    <span class="px-2 py-1 text-xs text-red-800 bg-red-100 rounded-full">Rechazada</span>
                                                @elseif($invoice->dian_status == 'processing')
                                                    <span class="px-2 py-1 text-xs text-yellow-800 bg-yellow-100 rounded-full">En Proceso</span>
                                                @else
                                                    <span class="px-2 py-1 text-xs text-gray-800 bg-gray-100 rounded-full">Pendiente</span>
                                                @endif
                                            </span>
                                        </li>
                                        <li class="flex justify-between" id="dian-sent-at" style="{{ $invoice->dian_sent_at ? '' : 'display: none;' }}">
                                            <span class="text-gray-600">Fecha de envío:</span>
                                            <span class="font-medium">{{ $invoice->dian_sent_at ? $invoice->dian_sent_at->format('d/m/Y H:i') : '' }}</span>
                                        </li>
                                        <li class="flex justify-between" id="dian-response-code" style="{{ $invoice->dian_response_code ? '' : 'display: none;' }}">
                                            <span class="text-gray-600">Código DIAN:</span>
                                            <span class="font-medium">{{ $invoice->dian_response_code ?? '' }}</span>
                                        </li>
                                    </ul>
                                    
                                    <!-- Botones de acción DIAN -->
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <button type="button" id="btn-consultar-dian" title="Consulta el estado actual de la factura en el sistema de la DIAN" class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 text-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Consultar
                                        </button>
                                        <button type="button" id="btn-enviar-dian" title="Envía la factura para su validación por la DIAN" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 text-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                            </svg>
                                            Enviar
                                        </button>
                                        <button type="button" id="btn-almacenar-dian" title="Guarda una copia local de la factura" class="inline-flex items-center px-3 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500 active:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150 text-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                                            </svg>
                                            Almacenar
                                        </button>
                                    </div>
                                </div>
                            </div>

                            @if($invoice->notes)
                                <div class="mt-6 pt-4 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-gray-500 mb-2">Notas</h4>
                                    <p class="text-gray-700">{{ $invoice->notes }}</p>
                                                </div>
            @endif
                                        </div>
                                    </div>
                    
                    <!-- Sección 2: Información del Cliente -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Información del Cliente</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <ul class="space-y-3">
                                        <li>
                                            <span class="text-gray-600">Nombre:</span>
                                            <span class="font-medium">{{ $invoice->customer_name }}</span>
                                        </li>
                                        <li>
                                            <span class="text-gray-600">{{ $invoice->document_type ?? 'Documento' }}:</span>
                                            <span class="font-medium">{{ $invoice->customer_id }}</span>
                                        </li>
                                        <li>
                                            <span class="text-gray-600">Email:</span>
                                            <span class="font-medium">{{ $invoice->customer_email ?? 'No especificado' }}</span>
                                        </li>
                                    </ul>
                                    </div>
                                
                                <div>
                                    <ul class="space-y-3">
                                        <li>
                                            <span class="text-gray-600">Teléfono:</span>
                                            <span class="font-medium">{{ $invoice->customer_phone ?? 'No especificado' }}</span>
                                        </li>
                                        <li>
                                            <span class="text-gray-600">Dirección:</span>
                                            <span class="font-medium">{{ $invoice->customer_address ?? 'No especificada' }}</span>
                                        </li>
                                        <li>
                                            <span class="text-gray-600">Ciudad/País:</span>
                                            <span class="font-medium">
                                                {{ $invoice->customer_city ?? 'No especificada' }}
                                                {{ $invoice->customer_state ? ', ' . $invoice->customer_state : '' }}
                                                {{ $invoice->customer_country ? ', ' . $invoice->customer_country : '' }}
                                            </span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                                        </div>
                                    </div>
                    
                    <!-- Sección 3: Información del Comerciante -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">Información del Comerciante</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <ul class="space-y-3">
                                        <li>
                                            <span class="text-gray-600">Nombre:</span>
                                            <span class="font-medium">{{ $invoice->company->name ?? 'No especificado' }}</span>
                                        </li>
                                        <li>
                                            <span class="text-gray-600">NIT:</span>
                                            <span class="font-medium">{{ $invoice->company->nit ?? 'No especificado' }}</span>
                                        </li>
                                        <li>
                                            <span class="text-gray-600">Email:</span>
                                            <span class="font-medium">{{ $invoice->company->email ?? 'No especificado' }}</span>
                                        </li>
                                    </ul>
                                        </div>
                                
                                <div>
                                    <ul class="space-y-3">
                                        <li>
                                            <span class="text-gray-600">Teléfono:</span>
                                            <span class="font-medium">{{ $invoice->company->phone ?? 'No especificado' }}</span>
                                        </li>
                                        <li>
                                            <span class="text-gray-600">Dirección:</span>
                                            <span class="font-medium">{{ $invoice->company->address ?? 'No especificada' }}</span>
                                        </li>
                                    </ul>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="hidden" id="details" role="tabpanel" aria-labelledby="details-tab">
                        <!-- Sección 5: Detalle de Productos/Servicios -->
                        <div class="bg-white p-4 rounded-lg">
                            <h3 class="text-lg font-semibold mb-3 text-gray-800 border-b pb-2">Detalles de Productos/Servicios</h3>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full bg-white border border-gray-200">
                                    <thead class="bg-gray-100">
                                        <tr>
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">ID</th>
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Código Producto</th>
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Descripción</th>
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Cantidad</th>
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Valor Unitario</th>
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Descuento</th>
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Recargo</th>
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Base Imponible</th>
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">% IVA</th>
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Valor IVA</th>
                                            <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($invoice->details as $index => $detail)
                                            <tr class="{{ $index % 2 === 0 ? 'bg-gray-50' : 'bg-white' }}">
                                                <td class="py-2 px-4 border-b border-gray-200">{{ $detail->id }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200">{{ $detail->product_id ?? 'N/A' }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200">
                                                    {{ $detail->product ? $detail->product->name : 'Producto no disponible' }}
                                                </td>
                                                <td class="py-2 px-4 border-b border-gray-200">{{ number_format($detail->quantity, 2) }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200">${{ number_format($detail->unit_value, 2) }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200">${{ number_format($detail->discounts, 2) }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200">${{ number_format($detail->surcharges, 2) }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200">${{ number_format($detail->total_base, 2) }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200">{{ number_format($detail->tax_rate, 2) }}%</td>
                                                <td class="py-2 px-4 border-b border-gray-200">${{ number_format($detail->tax, 2) }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200 font-semibold">${{ number_format($detail->total, 2) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="11" class="py-4 px-4 border-b border-gray-200 text-center text-gray-500">No hay detalles disponibles para esta factura</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                        </div>
                    </div>
                </div>
                
                <div class="hidden" id="summary" role="tabpanel" aria-labelledby="summary-tab">
                    <!-- Sección 6: Resumen de Impuestos -->
                    <div class="bg-white p-4 rounded-lg">
                        <h3 class="text-lg font-semibold mb-3 text-gray-800 border-b pb-2">Resumen de Impuestos</h3>
                        
                                <div class="overflow-x-auto">
                                    <table class="min-w-full bg-white border border-gray-200">
                                        <thead class="bg-gray-100">
                                            <tr>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Impuesto</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Base Imponible</th>
                                        <th class="py-2 px-4 border-b border-gray-200 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                            @php
                                        // Agrupar los impuestos por tasa
                                        $taxGroups = [];
                                        $items = collect([]);
                                        
                                        if ($invoice->details) {
                                            foreach ($invoice->details as $detail) {
                                                $taxKey = number_format($detail->tax_rate, 2);
                                                if (!isset($taxGroups[$taxKey])) {
                                                    $taxGroups[$taxKey] = [
                                                        'rate' => $detail->tax_rate,
                                                        'base' => 0,
                                                        'tax' => 0
                                                    ];
                                                }
                                                
                                                $taxGroups[$taxKey]['base'] += $detail->total_base;
                                                $taxGroups[$taxKey]['tax'] += $detail->tax;
                                            }
                                            
                                            $items = collect($taxGroups)->sortBy('rate');
                                        }
                                            @endphp
                                            
                                    @forelse($items as $rate => $group)
                                        <tr class="bg-white">
                                            <td class="py-2 px-4 border-b border-gray-200">IVA {{ $rate }}%</td>
                                            <td class="py-2 px-4 border-b border-gray-200">${{ number_format($group['base'], 2) }}</td>
                                            <td class="py-2 px-4 border-b border-gray-200">${{ number_format($group['tax'], 2) }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" class="py-4 px-4 border-b border-gray-200 text-center text-gray-500">No hay datos de IVA disponibles</td>
                                </tr>
                                            @endforelse
                                            <tr class="bg-gray-100 font-semibold">
                                                <td class="py-2 px-4 border-b border-gray-200">TOTAL</td>
                                                <td class="py-2 px-4 border-b border-gray-200">${{ number_format($invoice->details->sum('total_base'), 2) }}</td>
                                                <td class="py-2 px-4 border-b border-gray-200">${{ number_format($invoice->tax, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                                </div>

                                <!-- Sección 7: Pie de Factura -->
                                <div class="mt-6 pt-4 border-t border-gray-200">
                                    <h3 class="text-md font-semibold mb-3 text-gray-800">Información de Generación</h3>
                                    <div class="space-y-2">
                                        <div class="flex flex-wrap">
                                            <label class="w-full sm:w-1/3 text-gray-600">Elaboró:</label>
                                            <div class="w-full sm:w-2/3 font-medium">
                                                {{ auth()->user()->name ?? 'Sistema de Facturación Electrónica' }}
                                            </div>
                                        </div>
                                        <div class="flex flex-wrap">
                                            <label class="w-full sm:w-1/3 text-gray-600">Fecha de generación:</label>
                                            <div class="w-full sm:w-2/3 font-medium">{{ $invoice->created_at->format('d/m/Y H:i:s') }}</div>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            
            <!-- Código QR (Simulado) -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-center">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Código QR</h3>
                    @if($invoice->cufe)
                        <!-- Simulación: en producción, el QR sería generado con el CUFE -->
                        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAKAAAACgCAYAAACLz2ctAAAEGklEQVR4Ae3BQW7kQAzAwK79/5fH3iIHAUV0Sx7s+vy5XI7L5XhcLsfjcjleFcdvheNWcdwqjrfiuFUct4rjVnHcKo63imOqOKaKY6o4porjVnE8Ko6p4pgqjqniuFUcj4rjUXFMFcet4pgqjqniuFUct4pjqjieKo5bcUwVx1RxTBXHreJ4VBxTxfGqOKaKY6o4porjUXFMFcdUcUwVx61+eDmOX4rjVnHcKo6p4pgqjlvFcas4HhXHVHFMFcdUcTwqjqnimCqOW8UxVRxTxTFVHI+KY6o4porjreKYKo6p4nhUHFPFMVUcU8UxVRy34rgVx1RxTBXHreJ4VBxTxfGqOKaKY6o4porjUXFMFcdUcbxVHL8UxyvimCqOqeK4VRyPimOqOKaK41ZxTBXHo+KYKo5bxTFVHLeK41ZxTBXHo+KYKo5bxTFVHFPFMVUcU8UxVRxTxTFVHLeK41YcU8XxqnhcLsfjcjleFcdvheNWcdwqjrfiuFUct4rjVnHcKo63imOqOKaKY6o4porjVnE8Ko6p4pgqjqniuFUcj4rjUXFMFcet4pgqjqniuFUct4pjqjieKo5bcUwVx1RxTBXHVHFMFcdUcUwVx1Rx3CqOqeKYKo6p4rgVx1RxPE6ZfwAM4n7yLlh+0AAAAABJRU5ErkJggg==" alt="Código QR de la factura" class="mx-auto" style="max-width: 200px;">
                    @else
                        <div class="text-gray-500">
                            No hay código QR disponible para esta factura
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="mt-6 flex justify-between">
                <a href="{{ route('invoices.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Volver al listado
                    </a>
                <a href="/api/invoices/{{ $invoice->id }}/pdf" target="_blank" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                    Descargar PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Link al script de compartir y funcionalidad de tabs -->
    <script>
        // Función para compartir
        document.getElementById('shareButton').addEventListener('click', function() {
            if (navigator.share) {
                navigator.share({
                    title: 'Factura #{{ $invoice->invoice_number }}',
                    text: 'Comparto la factura #{{ $invoice->invoice_number }} por un total de ${{ number_format($invoice->total, 2) }}',
                    url: '{{ $invoice->getPublicUrl() }}',
                }).catch(console.error);
            } else {
                // Copiar al portapapeles como alternativa
                const url = '{{ $invoice->getPublicUrl() }}';
                navigator.clipboard.writeText(url).then(function() {
                    alert('URL copiada al portapapeles: ' + url);
                }, function() {
                    alert('No se pudo copiar la URL');
                });
            }
        });

        // Manejo de las pestañas
        const tabElements = [
            {
                id: 'general-tab',
                triggerEl: document.getElementById('general-tab'),
                targetEl: document.getElementById('general')
            },
            {
                id: 'details-tab',
                triggerEl: document.getElementById('details-tab'),
                targetEl: document.getElementById('details')
            },
            {
                id: 'summary-tab',
                triggerEl: document.getElementById('summary-tab'),
                targetEl: document.getElementById('summary')
            }
        ];

        // Configurar los eventos de las pestañas
        tabElements.forEach(tab => {
            tab.triggerEl.addEventListener('click', function() {
                // Ocultar todas las pestañas
                tabElements.forEach(t => {
                    t.targetEl.classList.add('hidden');
                    t.triggerEl.classList.remove('border-blue-600');
                    t.triggerEl.classList.add('border-transparent');
                    t.triggerEl.setAttribute('aria-selected', false);
                });
                
                // Mostrar la pestaña seleccionada
                tab.targetEl.classList.remove('hidden');
                tab.triggerEl.classList.remove('border-transparent');
                tab.triggerEl.classList.add('border-blue-600');
                tab.triggerEl.setAttribute('aria-selected', true);
            });
        });
    </script>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // References to tabs
            const generalTab = document.getElementById('general-tab');
            const detailsTab = document.getElementById('details-tab');
            const summaryTab = document.getElementById('summary-tab');
            
            // References to content
            const generalContent = document.getElementById('general');
            const detailsContent = document.getElementById('details');
            const summaryContent = document.getElementById('summary');
            
            // Tab switching logic
            generalTab.addEventListener('click', function() {
                // Show general, hide others
                generalContent.classList.remove('hidden');
                generalContent.classList.add('block');
                detailsContent.classList.add('hidden');
                summaryContent.classList.add('hidden');
                
                // Update active tab styles
                generalTab.classList.add('border-blue-600');
                detailsTab.classList.remove('border-blue-600');
                summaryTab.classList.remove('border-blue-600');
                
                detailsTab.classList.add('border-transparent');
                summaryTab.classList.add('border-transparent');
            });
            
            detailsTab.addEventListener('click', function() {
                // Show details, hide others
                generalContent.classList.add('hidden');
                detailsContent.classList.remove('hidden');
                detailsContent.classList.add('block');
                summaryContent.classList.add('hidden');
                
                // Update active tab styles
                generalTab.classList.remove('border-blue-600');
                detailsTab.classList.add('border-blue-600');
                summaryTab.classList.remove('border-blue-600');
                
                generalTab.classList.add('border-transparent');
                summaryTab.classList.add('border-transparent');
            });
            
            summaryTab.addEventListener('click', function() {
                // Show summary, hide others
                generalContent.classList.add('hidden');
                detailsContent.classList.add('hidden');
                summaryContent.classList.remove('hidden');
                summaryContent.classList.add('block');
                
                // Update active tab styles
                generalTab.classList.remove('border-blue-600');
                detailsTab.classList.remove('border-blue-600');
                summaryTab.classList.add('border-blue-600');
                
                generalTab.classList.add('border-transparent');
                detailsTab.classList.add('border-transparent');
            });
            
            // DIAN action buttons functionality
            const btnConsultarDian = document.getElementById('btn-consultar-dian');
            const btnEnviarDian = document.getElementById('btn-enviar-dian');
            const btnAlmacenarDian = document.getElementById('btn-almacenar-dian');
            
            // DIAN status display elements
            const cufeDisplay = document.getElementById('cufe-display');
            const dianStatusDisplay = document.getElementById('dian-status-display');
            const dianSentAt = document.getElementById('dian-sent-at');
            const dianResponseCode = document.getElementById('dian-response-code');
            
            // Helper function to show confirmation message
            function showConfirmation(action, callback) {
                if (confirm(`¿Está seguro que desea ${action} esta factura?`)) {
                    callback();
                }
            }
            
            // Helper to update DIAN status display
            function updateDianStatusDisplay(status) {
                let html = '';
                switch(status) {
                    case 'approved':
                        html = '<span class="px-2 py-1 text-xs text-green-800 bg-green-100 rounded-full">Aprobada</span>';
                        break;
                    case 'rejected':
                        html = '<span class="px-2 py-1 text-xs text-red-800 bg-red-100 rounded-full">Rechazada</span>';
                        break;
                    case 'processing':
                        html = '<span class="px-2 py-1 text-xs text-yellow-800 bg-yellow-100 rounded-full">En Proceso</span>';
                        break;
                    default:
                        html = '<span class="px-2 py-1 text-xs text-gray-800 bg-gray-100 rounded-full">Pendiente</span>';
                }
                dianStatusDisplay.innerHTML = html;
            }
            
            // Button: Consultar estado en DIAN
            btnConsultarDian.addEventListener('click', function() {
                showConfirmation('consultar el estado de', function() {
                    // Simulate API call
                    btnConsultarDian.disabled = true;
                    btnConsultarDian.innerHTML = '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                    
                    setTimeout(function() {
                        // Simulate response
                        const status = 'processing';
                        updateDianStatusDisplay(status);
                        
                        // Update response code
                        dianResponseCode.style.display = 'flex';
                        dianResponseCode.querySelector('span:last-child').textContent = 'DOC-' + Math.floor(Math.random() * 1000000);
                        
                        // Restore button
                        btnConsultarDian.disabled = false;
                        btnConsultarDian.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Consultar';
                        
                        // Show confirmation message
                        alert('Estado consultado correctamente. La factura está siendo procesada por DIAN.');
                    }, 1500);
                });
            });
            
            // Button: Enviar a DIAN
            btnEnviarDian.addEventListener('click', function() {
                showConfirmation('enviar', function() {
                    // Simulate API call
                    btnEnviarDian.disabled = true;
                    btnEnviarDian.innerHTML = '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                    
                    setTimeout(function() {
                        // Generate CUFE if not present
                        if (cufeDisplay.textContent === 'Sin CUFE') {
                            const newCufe = 'CUFE-' + Math.random().toString(36).substring(2, 15);
                            cufeDisplay.textContent = newCufe;
                        }
                        
                        // Update status
                        updateDianStatusDisplay('approved');
                        
                        // Update sent date
                        const now = new Date();
                        const formattedDate = now.toLocaleDateString('es-CO') + ' ' + now.toLocaleTimeString('es-CO', {hour: '2-digit', minute:'2-digit'});
                        dianSentAt.style.display = 'flex';
                        dianSentAt.querySelector('span:last-child').textContent = formattedDate;
                        
                        // Update response code
                        dianResponseCode.style.display = 'flex';
                        dianResponseCode.querySelector('span:last-child').textContent = 'AUTH-' + Math.floor(Math.random() * 1000000);
                        
                        // Restore button
                        btnEnviarDian.disabled = false;
                        btnEnviarDian.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg> Enviar';
                        
                        // Show confirmation message
                        alert('Factura enviada correctamente. Factura autorizada por la DIAN.');
                    }, 2000);
                });
            });
            
            // Button: Almacenar localmente
            btnAlmacenarDian.addEventListener('click', function() {
                showConfirmation('almacenar', function() {
                    // Simulate API call
                    btnAlmacenarDian.disabled = true;
                    btnAlmacenarDian.innerHTML = '<svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
                    
                    setTimeout(function() {
                        // Restore button
                        btnAlmacenarDian.disabled = false;
                        btnAlmacenarDian.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg> Almacenar';
                        
                        // Show confirmation message
                        alert('Factura almacenada exitosamente.');
                    }, 1000);
                });
            });
        });
    </script>
    @endpush
</x-app-layout>