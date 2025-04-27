<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Facturas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <!-- Botón para crear nueva factura -->
                <div class="flex justify-end mb-6">
                    @can('sell')
                    <a href="{{ route('invoices.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 3a1 1 0 00-1 1v5H4a1 1 0 100 2h5v5a1 1 0 102 0v-5h5a1 1 0 100-2h-5V4a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Generar Nueva Factura
                    </a>
                    @endcan
                </div>
                
                <!-- Botones de acción para DIAN -->
                <div class="flex flex-wrap gap-2 mb-6 bg-gray-100 p-3 rounded-lg border border-gray-200">
                    <button type="button" id="btn-consultar" disabled class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                        Consultar Estado
                    </button>
                    <button type="button" id="btn-enviar" disabled class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                        Enviar
                    </button>
                    <button type="button" id="btn-almacenar" disabled class="inline-flex items-center px-3 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500 active:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                        Almacenar
                    </button>
                    <button type="button" id="btn-notificar" disabled class="inline-flex items-center px-3 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-500 active:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                        Notificar
                    </button>
                    <button type="button" id="btn-consultar-estado" disabled class="inline-flex items-center px-3 py-2 bg-teal-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-teal-500 active:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                        Consultar Estado
                    </button>
                    <button type="button" id="btn-generar-eventos" disabled class="inline-flex items-center px-3 py-2 bg-cyan-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-cyan-500 active:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                        Generar Eventos
                    </button>
                    <button type="button" id="btn-consultar-eventos" disabled class="inline-flex items-center px-3 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 active:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                        Consultar Eventos
                    </button>
                    <button type="button" id="btn-xml" disabled class="inline-flex items-center px-3 py-2 bg-sky-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-sky-500 active:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                        XML
                    </button>
                </div>

                <!-- Indicador de selección -->
                <div id="selection-info" class="mb-4 hidden p-3 bg-blue-50 border border-blue-100 rounded text-blue-700">
                    <span id="selected-count">0</span> facturas seleccionadas
                </div>
                
                <!-- Buscador rápido por número de factura -->
                <div class="mb-4">
                    <form action="{{ route('invoices.search') }}" method="GET" class="flex gap-2">
                        <div class="flex-grow">
                            <x-text-input 
                                id="quick_search" 
                                name="invoice_number" 
                                type="text" 
                                value="{{ request('invoice_number') }}" 
                                class="w-full" 
                                placeholder="Buscar por número de factura..." 
                                autofocus
                            />
                        </div>
                        <x-primary-button type="submit">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                            </svg>
                        </x-primary-button>
                    </form>
                </div>
                
                <!-- Filtros avanzados -->
                <div class="mb-6">
                    <form action="{{ route('invoices.index') }}" method="GET" class="space-y-4 md:space-y-0 md:flex md:flex-wrap md:space-x-4 items-end">
                        <div class="w-full md:w-1/4">
                            <x-input-label for="invoice_number" value="Número de Factura" />
                            <x-text-input id="invoice_number" type="text" name="invoice_number" value="{{ request('invoice_number') }}" class="mt-1 block w-full" placeholder="Buscar por número..." />
                        </div>
                        <div class="w-full md:w-1/5">
                            <x-input-label for="start_date" value="Fecha inicio" />
                            <x-text-input id="start_date" type="date" name="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full" />
                        </div>
                        <div class="w-full md:w-1/5">
                            <x-input-label for="end_date" value="Fecha fin" />
                            <x-text-input id="end_date" type="date" name="end_date" value="{{ request('end_date') }}" class="mt-1 block w-full" />
                        </div>
                        <div class="w-full md:w-1/5">
                            <x-input-label for="status" value="Estado" />
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="">Todos los estados</option>
                                <option value="ACCEPTED" {{ request('status') === 'ACCEPTED' ? 'selected' : '' }}>Aceptadas</option>
                                <option value="SENT" {{ request('status') === 'SENT' ? 'selected' : '' }}>Enviadas</option>
                                <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Rechazadas</option>
                                <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Pendientes</option>
                            </select>
                        </div>
                        <div>
                            <x-primary-button type="submit">
                                Filtrar
                            </x-primary-button>
                        </div>
                    </form>
                </div>

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
                                <th scope="col" class="p-2 whitespace-nowrap">D S N X M V Z C</th>
                                <th scope="col" class="py-3 px-2">Tipo</th>
                                <th scope="col" class="py-3 px-2">Pref.</th>
                                <th scope="col" class="py-3 px-2">Número</th>
                                <th scope="col" class="py-3 px-2">Type code</th>
                                <th scope="col" class="py-3 px-2">Fecha</th>
                                <th scope="col" class="py-3 px-2">V Total</th>
                                <th scope="col" class="py-3 px-2">A R E X T</th>
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
                                    <td class="p-2">
                                        <div class="flex space-x-1">
                                            <span class="w-4 h-4 inline-block bg-blue-400 rounded-full"></span>
                                            <span class="w-4 h-4 inline-block bg-gray-200 rounded-full"></span>
                                            <span class="w-4 h-4 inline-block bg-gray-200 rounded-full"></span>
                                            <span class="w-4 h-4 inline-block bg-gray-200 rounded-full"></span>
                                            <span class="w-4 h-4 inline-block bg-gray-200 rounded-full"></span>
                                            <span class="w-4 h-4 inline-block bg-gray-200 rounded-full"></span>
                                            <span class="w-4 h-4 inline-block bg-gray-200 rounded-full"></span>
                                            <span class="w-4 h-4 inline-block bg-gray-200 rounded-full"></span>
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
                                    <td class="py-3 px-2 font-medium text-right">
                                        ${{ number_format($invoice->total, 2) }}
                                    </td>
                                    <td class="py-3 px-2">
                                        <div class="flex space-x-1">
                                            <span class="w-4 h-4 inline-block bg-gray-200 rounded-full"></span>
                                            <span class="w-4 h-4 inline-block bg-gray-200 rounded-full"></span>
                                            <span class="w-4 h-4 inline-block bg-gray-200 rounded-full"></span>
                                            <span class="w-4 h-4 inline-block bg-gray-200 rounded-full"></span>
                                        </div>
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
                                    <td colspan="10" class="py-10 px-6 text-center text-gray-500">
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
            </div>
        </div>
    </div>
    
    <!-- Modal para acciones DIAN -->
    <div id="dian-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-lg max-w-lg w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4" id="modal-title">Procesar facturas en DIAN</h3>
                <div class="mb-4" id="modal-content">
                    <p>¿Está seguro que desea realizar esta acción para las facturas seleccionadas?</p>
                    <ul id="selected-invoices-list" class="mt-2 text-sm text-gray-600 max-h-40 overflow-y-auto"></ul>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" id="modal-cancel" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">
                        Cancelar
                    </button>
                    <button type="button" id="modal-confirm" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Confirmar
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const invoiceCheckboxes = document.querySelectorAll('.invoice-checkbox');
            const selectionInfo = document.getElementById('selection-info');
            const selectedCount = document.getElementById('selected-count');
            const actionButtons = [
                document.getElementById('btn-consultar'),
                document.getElementById('btn-enviar'),
                document.getElementById('btn-almacenar'),
                document.getElementById('btn-notificar'),
                document.getElementById('btn-consultar-estado'),
                document.getElementById('btn-generar-eventos'),
                document.getElementById('btn-consultar-eventos'),
                document.getElementById('btn-xml')
            ];
            
            const modal = document.getElementById('dian-modal');
            const modalTitle = document.getElementById('modal-title');
            const modalContent = document.getElementById('modal-content');
            const selectedInvoicesList = document.getElementById('selected-invoices-list');
            const modalCancel = document.getElementById('modal-cancel');
            const modalConfirm = document.getElementById('modal-confirm');
            
            // Función para actualizar el estado de los botones
            function updateButtonStates() {
                const checkedBoxes = document.querySelectorAll('.invoice-checkbox:checked');
                const hasSelection = checkedBoxes.length > 0;
                
                // Actualizar contador
                selectedCount.textContent = checkedBoxes.length;
                selectionInfo.classList.toggle('hidden', !hasSelection);
                
                // Habilitar/deshabilitar botones según selección
                actionButtons.forEach(button => {
                    if (button) button.disabled = !hasSelection;
                });
            }
            
            // Event listener para el checkbox "seleccionar todo"
            selectAllCheckbox.addEventListener('change', function() {
                invoiceCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateButtonStates();
            });
            
            // Event listeners para cada checkbox individual
            invoiceCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Si deseleccionamos uno, deseleccionar también "seleccionar todo"
                    if (!this.checked && selectAllCheckbox.checked) {
                        selectAllCheckbox.checked = false;
                    }
                    
                    // Si todos están seleccionados, seleccionar también "seleccionar todo"
                    if (document.querySelectorAll('.invoice-checkbox:checked').length === invoiceCheckboxes.length) {
                        selectAllCheckbox.checked = true;
                    }
                    
                    updateButtonStates();
                });
            });
            
            // Mostrar modal para las acciones
            function showActionModal(action, title) {
                // Obtener facturas seleccionadas
                const selectedInvoices = Array.from(document.querySelectorAll('.invoice-checkbox:checked'))
                    .map(checkbox => {
                        const invoiceId = checkbox.getAttribute('data-invoice-id');
                        const row = document.querySelector(`tr[data-invoice-id="${invoiceId}"]`);
                        const invoiceNumber = row.querySelector('td:nth-child(5)').textContent.trim();
                        return { id: invoiceId, number: invoiceNumber };
                    });
                
                // Actualizar modal
                modalTitle.textContent = title;
                selectedInvoicesList.innerHTML = '';
                selectedInvoices.forEach(invoice => {
                    const li = document.createElement('li');
                    li.textContent = `Factura #${invoice.number}`;
                    li.setAttribute('data-invoice-id', invoice.id);
                    selectedInvoicesList.appendChild(li);
                });
                
                // Configurar acción del botón confirmar
                modalConfirm.setAttribute('data-action', action);
                
                // Mostrar modal
                modal.classList.remove('hidden');
            }
            
            // Configurar botones de acción
            actionButtons.forEach(button => {
                if (!button) return;
                
                button.addEventListener('click', function() {
                    const action = this.id.replace('btn-', '');
                    let title = 'Procesar facturas en DIAN';
                    
                    switch(action) {
                        case 'consultar': title = 'Consultar facturas en DIAN'; break;
                        case 'enviar': title = 'Enviar facturas a DIAN'; break;
                        case 'almacenar': title = 'Almacenar facturas'; break;
                        case 'notificar': title = 'Notificar facturas'; break;
                        case 'consultar-estado': title = 'Consultar estado de facturas'; break;
                        case 'generar-eventos': title = 'Generar eventos para facturas'; break;
                        case 'consultar-eventos': title = 'Consultar eventos de facturas'; break;
                        case 'xml': title = 'Generar XML de facturas'; break;
                    }
                    
                    showActionModal(action, title);
                });
            });
            
            // Cerrar modal
            modalCancel.addEventListener('click', function() {
                modal.classList.add('hidden');
            });
            
            // Ejecutar acción cuando se confirma
            modalConfirm.addEventListener('click', function() {
                const action = this.getAttribute('data-action');
                const selectedInvoiceIds = Array.from(document.querySelectorAll('.invoice-checkbox:checked'))
                    .map(checkbox => checkbox.getAttribute('data-invoice-id'));
                
                // Mostrar indicador de carga
                this.disabled = true;
                this.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Procesando...
                `;
                
                // Enviar solicitud al servidor
                fetch('{{ route('dian.batch-process') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        action: action,
                        invoices: selectedInvoiceIds
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Cerrar modal
                    modal.classList.add('hidden');
                    
                    // Restaurar botón
                    this.disabled = false;
                    this.innerHTML = 'Confirmar';
                    
                    // Mostrar resultados en una notificación más elaborada
                    const resultModal = document.createElement('div');
                    resultModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50';
                    
                    let statusHtml = '';
                    if (data.processed && data.processed.length > 0) {
                        statusHtml = `
                            <div class="mt-4 max-h-60 overflow-y-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b">
                                            <th class="text-left py-1 px-2">Factura</th>
                                            <th class="text-left py-1 px-2">Estado</th>
                                            <th class="text-left py-1 px-2">Mensaje</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                        `;
                        
                        data.processed.forEach(item => {
                            statusHtml += `
                                <tr class="border-b">
                                    <td class="py-1 px-2">${item.number}</td>
                                    <td class="py-1 px-2">
                                        <span class="inline-block rounded-full h-2 w-2 mr-1 ${item.status === 'success' ? 'bg-green-500' : 'bg-red-500'}"></span>
                                        ${item.status === 'success' ? 'Éxito' : 'Error'}
                                    </td>
                                    <td class="py-1 px-2">${item.message}</td>
                                </tr>
                            `;
                        });
                        
                        statusHtml += `
                                    </tbody>
                                </table>
                            </div>
                        `;
                    }
                    
                    resultModal.innerHTML = `
                        <div class="bg-white rounded-lg max-w-lg w-full">
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <h3 class="text-lg font-medium text-gray-900">Resultado del procesamiento</h3>
                                    <button type="button" class="text-gray-400 hover:text-gray-500" id="close-result">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="mb-4">
                                    <div class="flex items-center mb-2">
                                        <div class="flex-1 h-2 bg-gray-200 rounded-full">
                                            <div class="h-2 bg-green-500 rounded-full" style="width: ${data.success > 0 ? (data.success / (data.success + data.failed) * 100) : 0}%;"></div>
                                        </div>
                                        <span class="ml-2 text-sm font-medium text-gray-700">${data.success}/${data.success + data.failed}</span>
                                    </div>
                                    <p class="text-sm text-gray-600">${data.messages.join(' ')}</p>
                                </div>
                                ${statusHtml}
                                <div class="mt-4 flex justify-end">
                                    <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700" id="ok-result">
                                        Aceptar
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    document.body.appendChild(resultModal);
                    
                    // Eventos para cerrar el modal de resultados
                    document.getElementById('close-result').addEventListener('click', () => {
                        document.body.removeChild(resultModal);
                    });
                    
                    document.getElementById('ok-result').addEventListener('click', () => {
                        document.body.removeChild(resultModal);
                    });
                    
                    resultModal.addEventListener('click', (e) => {
                        if (e.target === resultModal) {
                            document.body.removeChild(resultModal);
                        }
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Restaurar botón
                    this.disabled = false;
                    this.innerHTML = 'Confirmar';
                    
                    // Cerrar modal y mostrar error
                    modal.classList.add('hidden');
                    alert('Error al procesar la solicitud: ' + error.message);
                });
            });
            
            // Permitir cerrar el modal haciendo clic fuera de él
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });
            
            // Inicializar estado de botones
            updateButtonStates();
        });
    </script>
</x-app-layout> 