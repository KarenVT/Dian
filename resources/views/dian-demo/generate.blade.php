<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generar Factura de Demostración DIAN') }}
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form method="POST" action="{{ route('dian-demo.store') }}" id="invoice-form">
                        @csrf
                        
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Datos del Cliente</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="customer_id" :value="__('Identificación')" />
                                    <x-text-input id="customer_id" class="block mt-1 w-full" type="text" name="customer_id" :value="old('customer_id')" required />
                                    <x-input-error :messages="$errors->get('customer_id')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="customer_name" :value="__('Nombre')" />
                                    <x-text-input id="customer_name" class="block mt-1 w-full" type="text" name="customer_name" :value="old('customer_name')" required />
                                    <x-input-error :messages="$errors->get('customer_name')" class="mt-2" />
                                </div>
                                
                                <div>
                                    <x-input-label for="customer_email" :value="__('Email')" />
                                    <x-text-input id="customer_email" class="block mt-1 w-full" type="email" name="customer_email" :value="old('customer_email')" required />
                                    <x-input-error :messages="$errors->get('customer_email')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-8">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg font-medium text-gray-900">Ítems de la Factura</h3>
                                <button type="button" id="add-item" class="inline-flex items-center px-3 py-1 bg-blue-600 border border-transparent rounded-md text-xs text-white tracking-widest hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                    Agregar ítem
                                </button>
                            </div>
                            
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200" id="items-table">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unitario</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IVA %</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200" id="items-container">
                                        <tr class="item-row">
                                            <td class="px-4 py-2">
                                                <input type="text" name="item_description[]" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required>
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" name="item_quantity[]" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 item-quantity" min="1" step="0.01" value="1" required>
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" name="item_price[]" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 item-price" min="0" step="0.01" value="100" required>
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" name="item_tax[]" class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 item-tax" min="0" max="19" step="1" value="19" required>
                                            </td>
                                            <td class="px-4 py-2">
                                                <span class="item-subtotal">$119.00</span>
                                            </td>
                                            <td class="px-4 py-2">
                                                <button type="button" class="text-red-600 hover:text-red-900 remove-item">Eliminar</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="3" class="px-4 py-3 text-right text-sm font-medium text-gray-900">Totales:</td>
                                            <td class="px-4 py-3 text-left text-sm text-gray-900" id="total-tax">$19.00</td>
                                            <td class="px-4 py-3 text-left text-sm font-bold text-gray-900" id="total-amount">$119.00</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <div class="mb-6">
                            <x-input-label for="notes" :value="__('Notas')" />
                            <textarea id="notes" name="notes" rows="3" class="block mt-1 w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('notes', 'Factura de demostración para validación con DIAN') }}</textarea>
                        </div>
                        
                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('dian-demo.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:bg-gray-300 active:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-2">
                                Cancelar
                            </a>
                            
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Generar Factura
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Función para actualizar cálculos
            function updateCalculations() {
                let totalAmount = 0;
                let totalTax = 0;

                document.querySelectorAll('.item-row').forEach(row => {
                    const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
                    const price = parseFloat(row.querySelector('.item-price').value) || 0;
                    const taxRate = parseFloat(row.querySelector('.item-tax').value) || 0;
                    
                    const subtotal = quantity * price;
                    const tax = subtotal * (taxRate / 100);
                    const total = subtotal + tax;
                    
                    row.querySelector('.item-subtotal').textContent = '$' + total.toFixed(2);
                    
                    totalAmount += total;
                    totalTax += tax;
                });
                
                document.getElementById('total-amount').textContent = '$' + totalAmount.toFixed(2);
                document.getElementById('total-tax').textContent = '$' + totalTax.toFixed(2);
            }
            
            // Agregar ítem
            document.getElementById('add-item').addEventListener('click', function() {
                const itemsContainer = document.getElementById('items-container');
                const newRow = document.querySelector('.item-row').cloneNode(true);
                
                // Limpiar valores
                newRow.querySelectorAll('input').forEach(input => {
                    if (input.name.includes('description')) {
                        input.value = '';
                    } else if (input.name.includes('price')) {
                        input.value = '100';
                    } else if (input.name.includes('quantity')) {
                        input.value = '1';
                    } else if (input.name.includes('tax')) {
                        input.value = '19';
                    }
                });
                
                // Actualizar subtotal
                newRow.querySelector('.item-subtotal').textContent = '$119.00';
                
                // Agregar evento al botón eliminar
                newRow.querySelector('.remove-item').addEventListener('click', function() {
                    if (document.querySelectorAll('.item-row').length > 1) {
                        this.closest('.item-row').remove();
                        updateCalculations();
                    }
                });
                
                // Agregar eventos para actualizar cálculos
                newRow.querySelectorAll('input').forEach(input => {
                    input.addEventListener('input', updateCalculations);
                });
                
                itemsContainer.appendChild(newRow);
                updateCalculations();
            });
            
            // Configurar eventos iniciales
            document.querySelectorAll('.remove-item').forEach(button => {
                button.addEventListener('click', function() {
                    if (document.querySelectorAll('.item-row').length > 1) {
                        this.closest('.item-row').remove();
                        updateCalculations();
                    }
                });
            });
            
            document.querySelectorAll('.item-quantity, .item-price, .item-tax').forEach(input => {
                input.addEventListener('input', updateCalculations);
            });
            
            // Cálculo inicial
            updateCalculations();
        });
    </script>
</x-app-layout> 