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
                <div class="flex flex-wrap gap-2 mb-6 bg-gray-100 p-4 rounded-lg border border-gray-200">
                    <div class="w-full mb-4">
                        <h3 class="text-sm text-gray-700 font-medium mb-2">Acciones disponibles:</h3>
                        <div class="bg-white p-3 rounded border border-gray-200 text-sm mb-3">
                            <ul class="space-y-1 text-gray-600 list-disc pl-5">
                                <li><span class="font-medium text-blue-600">Consultar Estado:</span> Verifica el estado actual de las facturas seleccionadas en el sistema de la DIAN.</li>
                                <li><span class="font-medium text-indigo-600">Enviar:</span> Transmite las facturas seleccionadas a la DIAN para su validación oficial.</li>
                                <li><span class="font-medium text-purple-600">Almacenar:</span> Guarda una copia local de las facturas seleccionadas para su posterior consulta.</li>
                                <li><span class="font-medium text-green-600">Enviar por Correo:</span> Distribuye las facturas seleccionadas a los clientes vía correo electrónico.</li>
                            </ul>
                        </div>
                    </div>
                    <button type="button" id="btn-consultar" title="Consulta el estado actual de las facturas seleccionadas en el sistema de la DIAN" class="inline-flex items-center px-3 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Consultar Estado
                    </button>
                    <button type="button" id="btn-enviar" title="Envía las facturas seleccionadas para su validación por la DIAN" class="inline-flex items-center px-3 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                        Enviar
                    </button>
                    <button type="button" id="btn-almacenar" title="Guarda una copia local de las facturas seleccionadas" class="inline-flex items-center px-3 py-2 bg-purple-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-purple-500 active:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Almacenar
                    </button>
                    <button type="button" id="btn-email" title="Envía las facturas seleccionadas por correo electrónico a los clientes" class="inline-flex items-center px-3 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500 active:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        Enviar por Correo
                    </button>
                </div>

                <!-- Indicador de selección -->
                <div id="selection-info" class="mb-4 hidden p-3 bg-blue-50 border border-blue-100 rounded text-blue-700">
                    <span id="selected-count">0</span> facturas seleccionadas
                </div>
                
                <!-- Filtros avanzados -->
                <div class="mb-6">
                    <form id="filter-form" action="{{ route('invoices.index') }}" method="GET" class="w-full space-y-4 md:space-y-0 md:flex md:flex-wrap md:items-end">
                        <div class="w-full md:w-1/4 md:pr-4">
                            <x-input-label for="invoice_number" value="Número de Factura" />
                            <x-text-input 
                                id="invoice_number" 
                                type="text" 
                                name="invoice_number" 
                                value="{{ request('invoice_number') }}" 
                                class="mt-1 block w-full filterable-field" 
                                placeholder="Buscar por número de factura..." 
                                autofocus
                            />
                        </div>
                        <div class="w-full md:w-1/5 md:pr-4">
                            <x-input-label for="start_date" value="Fecha inicio" />
                            <x-text-input id="start_date" type="date" name="start_date" value="{{ request('start_date') }}" class="mt-1 block w-full filterable-field" />
                        </div>
                        <div class="w-full md:w-1/5 md:pr-4">
                            <x-input-label for="end_date" value="Fecha fin" />
                            <x-text-input id="end_date" type="date" name="end_date" value="{{ request('end_date') }}" class="mt-1 block w-full filterable-field" />
                        </div>
                        <div class="w-full md:w-1/5 md:pr-4">
                            <x-input-label for="status" value="Estado" />
                            <select id="status" name="status" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm filterable-field">
                                <option value="">Todos los estados</option>
                                <option value="ACCEPTED" {{ request('status') === 'ACCEPTED' ? 'selected' : '' }}>Aceptadas</option>
                                <option value="SENT" {{ request('status') === 'SENT' ? 'selected' : '' }}>Enviadas</option>
                                <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Rechazadas</option>
                                <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Pendientes</option>
                            </select>
                        </div>
                        <div class="md:self-end mt-4 md:mt-0">
                            <x-primary-button type="submit" class="w-full md:w-auto h-10 justify-center px-6 py-2 bg-blue-600 hover:bg-blue-700">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                                </svg>
                                Filtrar
                            </x-primary-button>
                        </div>
                    </form>
                </div>

                <!-- Tabla de facturas -->
                <div id="invoices-table-container">
                    @include('invoices.partials.invoice-table')
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
                document.getElementById('btn-email')
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
                    if (button) {
                        if (hasSelection) {
                            button.classList.remove('opacity-50', 'cursor-not-allowed');
                        } else {
                            button.classList.add('opacity-50', 'cursor-not-allowed');
                        }
                    }
                });
            }
            
            // Event listener para el checkbox "seleccionar todo"
            if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', function() {
                invoiceCheckboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                updateButtonStates();
            });
            }
            
            // Event listeners para cada checkbox individual
            invoiceCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    // Si deseleccionamos uno, deseleccionar también "seleccionar todo"
                    if (!this.checked && selectAllCheckbox && selectAllCheckbox.checked) {
                        selectAllCheckbox.checked = false;
                    }
                    
                    // Si todos están seleccionados, seleccionar también "seleccionar todo"
                    if (selectAllCheckbox && document.querySelectorAll('.invoice-checkbox:checked').length === invoiceCheckboxes.length) {
                        selectAllCheckbox.checked = true;
                    }
                    
                    updateButtonStates();
                });
            });
            
            // Mostrar modal para las acciones
            function showActionModal(action, title) {
                // Verificar si hay facturas seleccionadas
                const selectedInvoices = Array.from(document.querySelectorAll('.invoice-checkbox:checked'))
                    .map(checkbox => {
                        const invoiceId = checkbox.getAttribute('data-invoice-id');
                        const row = document.querySelector(`tr[data-invoice-id="${invoiceId}"]`);
                        const invoiceNumber = row ? row.querySelector('td:nth-child(4)').textContent.trim() : '';
                        return { id: invoiceId, number: invoiceNumber };
                    });
                
                // Si no hay facturas seleccionadas, mostrar mensaje y salir
                if (selectedInvoices.length === 0) {
                    alert('Por favor, seleccione al menos una factura para realizar esta acción.');
                    return;
                }
                
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
                    // Verificar si hay facturas seleccionadas
                    if (document.querySelectorAll('.invoice-checkbox:checked').length === 0) {
                        alert('Por favor, seleccione al menos una factura para realizar esta acción.');
                        return;
                    }
                    
                    const action = this.id.replace('btn-', '');
                    let title = 'Procesar facturas en DIAN';
                    
                    switch(action) {
                        case 'consultar': title = 'Consultar facturas en DIAN'; break;
                        case 'enviar': title = 'Enviar facturas a DIAN'; break;
                        case 'almacenar': title = 'Almacenar facturas'; break;
                        case 'email': title = 'Enviar facturas por correo'; break;
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
                
                // Verificar que haya facturas seleccionadas
                if (selectedInvoiceIds.length === 0) {
                    alert('Por favor, seleccione al menos una factura para realizar esta acción.');
                    return;
                }
                
                // Mostrar indicador de carga
                this.disabled = true;
                this.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Procesando...
                `;
                
                // Simulación de respuesta
                setTimeout(() => {
                    // Datos de simulación según la acción
                    let simulatedData = {
                        success: selectedInvoiceIds.length,
                        failed: 0,
                        messages: [],
                        processed: []
                    };
                    
                    // Personalizar la simulación según el tipo de acción
                    switch(action) {
                        case 'enviar':
                            simulatedData.messages = ["Facturas enviadas correctamente a DIAN."];
                            selectedInvoiceIds.forEach(id => {
                                const row = document.querySelector(`tr[data-invoice-id="${id}"]`);
                                const number = row ? row.querySelector('td:nth-child(4)').textContent.trim() : 'Desconocido';
                                simulatedData.processed.push({
                                    id: id,
                                    number: number,
                                    status: 'success',
                                    message: 'Enviada correctamente. Factura autorizada por la DIAN. ID de seguimiento: CUFE-' + Math.random().toString(36).substring(2, 10)
                                });
                            });
                            break;
                        case 'consultar':
                            simulatedData.messages = ["Estado de facturas consultado correctamente."];
                            selectedInvoiceIds.forEach(id => {
                                const row = document.querySelector(`tr[data-invoice-id="${id}"]`);
                                const number = row ? row.querySelector('td:nth-child(4)').textContent.trim() : 'Desconocido';
                                simulatedData.processed.push({
                                    id: id,
                                    number: number,
                                    status: 'success',
                                    message: 'Estado en DIAN: AUTHORIZED - La factura ha sido autorizada correctamente'
                                });
                            });
                            break;
                        case 'almacenar':
                            simulatedData.messages = ["Facturas almacenadas exitosamente."];
                            selectedInvoiceIds.forEach(id => {
                                const row = document.querySelector(`tr[data-invoice-id="${id}"]`);
                                const number = row ? row.querySelector('td:nth-child(4)').textContent.trim() : 'Desconocido';
                                simulatedData.processed.push({
                                    id: id,
                                    number: number,
                                    status: 'success',
                                    message: 'Factura almacenada exitosamente'
                                });
                            });
                            break;
                        case 'email':
                            simulatedData.messages = ["Facturas enviadas por correo exitosamente."];
                            selectedInvoiceIds.forEach(id => {
                                const row = document.querySelector(`tr[data-invoice-id="${id}"]`);
                                const number = row ? row.querySelector('td:nth-child(4)').textContent.trim() : 'Desconocido';
                                simulatedData.processed.push({
                                    id: id,
                                    number: number,
                                    status: 'success',
                                    message: 'Factura enviada por correo al cliente exitosamente'
                                });
                            });
                            break;
                    }
                    
                    // Cerrar modal
                    modal.classList.add('hidden');
                    
                    // Restaurar botón
                    this.disabled = false;
                    this.innerHTML = 'Confirmar';
                    
                    // Personalizar título según la acción
                    let resultTitle = 'Resultado del procesamiento';
                    if (action === 'enviar') {
                        resultTitle = 'Resultado del envío a DIAN';
                    } else if (action === 'consultar') {
                        resultTitle = 'Estado de las facturas en DIAN';
                    } else if (action === 'almacenar') {
                        resultTitle = 'Resultado del almacenamiento local';
                    } else if (action === 'email') {
                        resultTitle = 'Resultado del envío por correo';
                    }
                    
                    // Mostrar resultados en una notificación
                    const resultModal = document.createElement('div');
                    resultModal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50';
                    
                    let statusHtml = '';
                    if (simulatedData.processed && simulatedData.processed.length > 0) {
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
                        
                        simulatedData.processed.forEach(item => {
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
                                    <h3 class="text-lg font-medium text-gray-900">${resultTitle}</h3>
                                    <button type="button" class="text-gray-400 hover:text-gray-500" id="close-result">
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                        </svg>
                                    </button>
                                </div>
                                <div class="mb-4">
                                    <div class="flex items-center mb-2">
                                        <div class="flex-1 h-2 bg-gray-200 rounded-full">
                                            <div class="h-2 bg-green-500 rounded-full" style="width: ${simulatedData.success > 0 ? (simulatedData.success / (simulatedData.success + simulatedData.failed) * 100) : 0}%;"></div>
                                        </div>
                                        <span class="ml-2 text-sm font-medium text-gray-700">${simulatedData.success}/${simulatedData.success + simulatedData.failed}</span>
                                    </div>
                                    <p class="text-sm text-gray-600">${simulatedData.messages.join(' ')}</p>
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
                }, 1000); // Simular un tiempo de procesamiento de 1 segundo
            });
            
            // Permitir cerrar el modal haciendo clic fuera de él
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                }
            });
            
            // Inicializar estado de botones
            updateButtonStates();

            // Filtrado en tiempo real
            const filterableFields = document.querySelectorAll('.filterable-field');
            const filterForm = document.getElementById('filter-form');
            let filterTimer;

            filterableFields.forEach(field => {
                field.addEventListener('input', function() {
                    clearTimeout(filterTimer);
                    filterTimer = setTimeout(applyFilters, 500); // Esperar 500ms después de que el usuario deje de escribir
                });
                
                field.addEventListener('change', function() {
                    applyFilters();
                });
            });

            function applyFilters() {
                const formData = new FormData(filterForm);
                const queryParams = new URLSearchParams(formData).toString();
                const loadingIndicator = document.createElement('div');
                
                loadingIndicator.id = 'filter-loading';
                loadingIndicator.className = 'fixed top-0 left-0 right-0 bg-blue-600 h-1 z-50';
                loadingIndicator.innerHTML = '<div class="animate-pulse bg-blue-300 h-full w-full"></div>';
                
                if (!document.getElementById('filter-loading')) {
                    document.body.appendChild(loadingIndicator);
                }
                
                fetch(`${filterForm.action}?${queryParams}&ajax=true`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    // Actualizar solo el contenedor de la tabla con la respuesta
                    document.getElementById('invoices-table-container').innerHTML = html;
                    
                    // Volver a adjuntar event listeners a los nuevos elementos
                    initCheckboxEvents();
                    
                    // Actualizar la URL del navegador sin recargar la página
                    window.history.pushState({}, '', `${filterForm.action}?${queryParams}`);
                    
                    // Eliminar el indicador de carga
                    if (document.getElementById('filter-loading')) {
                        document.body.removeChild(loadingIndicator);
                    }
                })
                .catch(error => {
                    console.error('Error al aplicar filtros:', error);
                    if (document.getElementById('filter-loading')) {
                        document.body.removeChild(loadingIndicator);
                    }
                });
            }
            
            function initCheckboxEvents() {
                const newCheckboxes = document.querySelectorAll('.invoice-checkbox');
                const newSelectAll = document.getElementById('select-all');
                
                if (newSelectAll) {
                    newSelectAll.addEventListener('change', function() {
                        newCheckboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });
                        updateButtonStates();
                    });
                }
                
                newCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        if (!this.checked && newSelectAll && newSelectAll.checked) {
                            newSelectAll.checked = false;
                        }
                        
                        if (newSelectAll && document.querySelectorAll('.invoice-checkbox:checked').length === newCheckboxes.length) {
                            newSelectAll.checked = true;
                        }
                        
                        updateButtonStates();
                    });
                });
            }
            
            // Ejecutar la función de inicialización al cargar la página
            initCheckboxEvents();
        });
    </script>
</x-app-layout> 