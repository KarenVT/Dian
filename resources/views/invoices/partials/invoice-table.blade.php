<!-- Tabla de facturas -->
<div class="overflow-x-auto relative">
    <table class="w-full text-sm text-left text-gray-500" id="invoices-table">
        <thead class="text-xs text-gray-700 uppercase bg-gray-100">
            <tr>
                <th scope="col" class="p-2">
                    <div class="flex items-center">
                        <input type="checkbox" id="select-all" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="select-all" class="sr-only">Seleccionar todo</label>
                    </div>
                </th>
                <th scope="col" class="py-3 px-2">Tipo</th>
                <th scope="col" class="py-3 px-2">Pref.</th>
                <th scope="col" class="py-3 px-2">Número</th>
                <th scope="col" class="py-3 px-2">Type code</th>
                <th scope="col" class="py-3 px-2">Fecha</th>
                <th scope="col" class="py-3 px-2">V Total</th>
                <th scope="col" class="py-3 px-2">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($invoices as $invoice)
                <tr class="bg-white border-b hover:bg-gray-50" data-invoice-id="{{ $invoice->id }}">
                    <td class="p-2">
                        <div class="flex items-center">
                            <input type="checkbox" class="invoice-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" data-invoice-id="{{ $invoice->id }}">
                            <label class="sr-only">Seleccionar factura</label>
                        </div>
                    </td>
                    <td class="py-3 px-2 font-medium text-gray-900">FS1</td>
                    <td class="py-3 px-2">FECI</td>
                    <td class="py-3 px-2 font-medium">
                        {{ $invoice->invoice_number }}
                    </td>
                    <td class="py-3 px-2">01</td>
                    <td class="py-3 px-2">
                        {{ $invoice->issued_at->format('d-m-Y') }}
                    </td>
                    <td class="py-3 px-2 font-medium text-left whitespace-nowrap pr-4">
                        ${{ number_format($invoice->total, 2, '.', ',') }}
                    </td>
                    <td class="py-3 px-2 flex items-center space-x-3">
                        <a href="/api/invoices/{{ $invoice->id }}/pdf" target="_blank" title="Descargar PDF" class="text-blue-600 hover:text-blue-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                        </a>
                        <a href="{{ route('invoices.show', $invoice->id) }}" class="font-medium text-indigo-600 hover:text-indigo-900">
                            Ver
                        </a>
                    </td>
                </tr>
            @empty
                <tr class="bg-white border-b">
                    <td colspan="8" class="py-10 px-6 text-center text-gray-500">
                        No se encontraron facturas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Paginación -->
<div class="mt-4">
    {{ $invoices->withQueryString()->links() }}
</div> 