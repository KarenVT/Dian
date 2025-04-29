<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Generar Factura Electrónica') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div x-data="invoiceForm()" x-init="initialize()">
                    <form @submit.prevent="submitForm">
                        <!-- Sección de la compañía (datos del comerciante) -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Información de mi Compañía</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <x-input-label value="NIT" />
                                    <p class="mt-1 p-2 block w-full border border-gray-200 rounded-md bg-gray-50">{{ Auth::user()->company->nit }}</p>
                                </div>
                                <div>
                                    <x-input-label value="Nombre Comercial" />
                                    <p class="mt-1 p-2 block w-full border border-gray-200 rounded-md bg-gray-50">{{ Auth::user()->company->business_name }}</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <x-input-label value="Teléfono" />
                                    <p class="mt-1 p-2 block w-full border border-gray-200 rounded-md bg-gray-50">{{ Auth::user()->company->phone ?? 'No especificado' }}</p>
                                </div>
                                <div>
                                    <x-input-label value="Dirección" />
                                    <p class="mt-1 p-2 block w-full border border-gray-200 rounded-md bg-gray-50">{{ Auth::user()->company->address ?? 'No especificada' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Sección del cliente -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Información del Cliente</h3>
                            
                            <!-- Selector de cliente -->
                            <div class="mb-4">
                                <x-input-label for="customer_select" value="Seleccione un Cliente *" />
                                <div class="relative">
                                    <select 
                                        id="customer_select" 
                                        x-model="selectedCustomerId"
                                        @change="selectCustomer()"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                        <option value="">Seleccione un cliente</option>
                                        <template x-for="customer in customers" :key="customer.id">
                                            <option :value="customer.id" x-text="customer.name + ' - ' + customer.document_type + ': ' + customer.document_number"></option>
                                        </template>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                        <div x-show="customersLoading" class="animate-spin h-5 w-5 border-2 border-indigo-500 rounded-full border-t-transparent"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Detalles del cliente -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <x-input-label for="document_type" value="Tipo de Documento *" />
                                    <select 
                                        id="document_type" 
                                        x-model="formData.document_type"
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        required
                                    >
                                        <option value="CC">Cédula de Ciudadanía</option>
                                        <option value="NIT">NIT</option>
                                        <option value="CE">Cédula de Extranjería</option>
                                        <option value="TI">Tarjeta de Identidad</option>
                                        <option value="PP">Pasaporte</option>
                                        <option value="NIP">Número de Identificación Personal</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="document_number" value="Número de Documento *" />
                                    <x-text-input id="document_number" type="text" x-model="formData.document_number" class="mt-1 block w-full" required />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <x-input-label for="name" value="Nombre *" />
                                    <x-text-input id="name" type="text" x-model="formData.name" class="mt-1 block w-full" required />
                                </div>
                                <div>
                                    <x-input-label for="phone" value="Teléfono *" />
                                    <x-text-input id="phone" type="text" x-model="formData.phone" class="mt-1 block w-full" required />
                                </div>
                            </div>
                            <div>
                                <x-input-label for="address" value="Dirección *" />
                                <x-text-input id="address" type="text" x-model="formData.address" class="mt-1 block w-full" required />
                            </div>
                        </div>

                        <!-- Sección de productos -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Productos</h3>
                            
                            <!-- Agregar producto -->
                            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 items-end">
                                <div class="md:col-span-2">
                                    <x-input-label for="product_select" value="Producto" />
                                    <div class="relative">
                                        <select 
                                            id="product_select" 
                                            x-model="selectedProductId"
                                            @change="selectProduct()"
                                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        >
                                            <option value="">Seleccione un producto</option>
                                            <template x-for="product in products" :key="product.id">
                                                <option :value="product.id" x-text="product.name + ' - $' + new Intl.NumberFormat('es-CO').format(product.price)"></option>
                                            </template>
                                        </select>
                                        <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none">
                                            <div x-show="productsLoading" class="animate-spin h-5 w-5 border-2 border-indigo-500 rounded-full border-t-transparent"></div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <x-input-label for="product_quantity" value="Cantidad" />
                                    <x-text-input id="product_quantity" type="number" step="0.01" min="1" x-model="newItem.quantity" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label for="product_price" value="Precio" />
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <span class="text-gray-500">$</span>
                                        </div>
                                        <x-text-input id="product_price" type="number" step="0.01" min="0" x-model="newItem.price" class="pl-7 mt-1 block w-full" />
                                    </div>
                                </div>
                                <div>
                                    <x-primary-button type="button" @click="addItemToCart()" class="w-full justify-center">
                                        Agregar
                                    </x-primary-button>
                                </div>
                            </div>
                            
                            <!-- Tabla de productos seleccionados -->
                            <div class="overflow-x-auto relative">
                                <table class="w-full text-sm text-left text-gray-500">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-100">
                                        <tr>
                                            <th scope="col" class="py-3 px-6">Producto</th>
                                            <th scope="col" class="py-3 px-6">Cantidad</th>
                                            <th scope="col" class="py-3 px-6">Precio</th>
                                            <th scope="col" class="py-3 px-6">IVA</th>
                                            <th scope="col" class="py-3 px-6">Subtotal</th>
                                            <th scope="col" class="py-3 px-6">Total</th>
                                            <th scope="col" class="py-3 px-6">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, index) in formData.items" :key="index">
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="py-4 px-6 font-medium text-gray-900" x-text="item.product_name"></td>
                                                <td class="py-4 px-6" x-text="item.quantity"></td>
                                                <td class="py-4 px-6" x-text="formatCurrency(item.price)"></td>
                                                <td class="py-4 px-6" x-text="item.tax_percent + '%'"></td>
                                                <td class="py-4 px-6" x-text="formatCurrency(item.price * item.quantity)"></td>
                                                <td class="py-4 px-6" x-text="formatCurrency((item.price * item.quantity) + ((item.price * item.quantity) * (item.tax_percent/100)))"></td>
                                                <td class="py-4 px-6">
                                                    <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-900">
                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-bind:class="{ 'hidden': formData.items.length > 0 }">
                                            <td colspan="7" class="py-4 px-6 text-center text-gray-500">
                                                No hay productos agregados.
                                            </td>
                                        </tr>
                                        <tr class="bg-gray-50 font-semibold">
                                            <td colspan="4" class="py-4 px-6 text-right">Totales:</td>
                                            <td class="py-4 px-6" x-text="formatCurrency(calculateSubtotal())"></td>
                                            <td class="py-4 px-6" x-text="formatCurrency(calculateTotal())"></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Sección de información adicional -->
                        <div class="mb-8">
                            <h3 class="text-lg font-medium text-gray-800 mb-4">Información Adicional</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <x-input-label for="payment_means" value="Medio de Pago" />
                                    <select id="payment_means" x-model="formData.payment_means" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="10">Efectivo</option>
                                        <option value="20">Cheque</option>
                                        <option value="41">Transferencia bancaria</option>
                                        <option value="42">Consignación bancaria</option>
                                        <option value="47">Tarjeta débito</option>
                                        <option value="48">Tarjeta crédito</option>
                                        <option value="49">Tarjeta prepago</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="due_date" value="Fecha de Vencimiento" />
                                    <x-text-input id="due_date" type="date" x-model="formData.due_date" class="mt-1 block w-full" required />
                                </div>
                            </div>
                            <div>
                                <x-input-label for="notes" value="Notas / Observaciones" />
                                <textarea id="notes" x-model="formData.notes" rows="3" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"></textarea>
                            </div>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex justify-end space-x-3">
                            <x-secondary-button type="button" @click="window.location.href = '{{ route('invoices.index') }}'">
                                Cancelar
                            </x-secondary-button>
                            <x-primary-button type="submit" x-bind:disabled="loading || formData.items.length === 0">
                                <span x-bind:class="{ 'hidden': loading }">Generar Factura</span>
                                <span x-bind:class="{ 'hidden': !loading }" class="flex items-center">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Procesando...
                                </span>
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function invoiceForm() {
            return {
                loading: false,
                productsLoading: false,
                customersLoading: false,
                products: [],
                customers: [],
                selectedProductId: '',
                selectedCustomerId: '',
                formData: {
                    document_type: 'CC',
                    document_number: '',
                    name: '',
                    email: '',
                    phone: '',
                    address: '',
                    customer_country: 'CO',
                    payment_method: '1', // Efectivo por defecto
                    payment_means: '10', // Efectivo por defecto
                    operation_type: '10', // Estándar por defecto
                    type: 'income',
                    notes: '',
                    due_date: '',
                    items: [],
                    company_id: '',
                    company_nit: '',
                    company_name: '',
                    company_phone: '',
                    company_address: ''
                },
                newItem: {
                    product_id: '',
                    product_name: '',
                    description: '',
                    quantity: 1,
                    price: 0,
                    tax_percent: 19
                },
                
                initialize() {
                    this.fetchProducts();
                    this.fetchCustomers();
                    
                    // Asignar fecha de vencimiento predeterminada (hoy + 30 días)
                    const defaultDueDate = new Date();
                    defaultDueDate.setDate(defaultDueDate.getDate() + 30);
                    this.formData.due_date = defaultDueDate.toISOString().split('T')[0];
                    
                    // Cargar los datos de la compañía para la factura
                    this.formData.company_id = {{ Auth::user()->company_id }};
                    this.formData.company_nit = "{{ Auth::user()->company->nit }}";
                    this.formData.company_name = "{{ Auth::user()->company->business_name }}";
                    this.formData.company_phone = "{{ Auth::user()->company->phone ?? '' }}";
                    this.formData.company_address = "{{ Auth::user()->company->address ?? '' }}";
                },
                
                async fetchProducts() {
                    this.productsLoading = true;
                    
                    try {
                        console.log('Iniciando carga de productos...');
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        console.log('CSRF Token obtenido:', csrfToken ? 'Sí' : 'No');
                        
                        // Usamos una ruta web regular en vez de la API
                        const response = await fetch('/obtener-productos-para-factura', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            credentials: 'include' // Importante para mantener la sesión
                        });
                        
                        console.log('Respuesta recibida:', response.status, response.statusText);
                        
                        if (!response.ok) {
                            const errorText = await response.text();
                            console.error('Error en respuesta:', errorText);
                            throw new Error(`Error al cargar productos: ${response.status} ${response.statusText}`);
                        }
                        
                        const data = await response.json();
                        console.log('Datos recibidos:', data);
                        
                        if (!Array.isArray(data)) {
                            console.error('Formato incorrecto:', data);
                            throw new Error('Formato de respuesta inválido');
                        }
                        
                        if (data.length === 0) {
                            console.log('No se encontraron productos');
                            alert('No hay productos disponibles. Por favor, cree algunos productos primero.');
                            this.products = [];
                            return;
                        }
                        
                        this.products = data.map(product => ({
                            ...product,
                            displayName: `${product.name} - $${new Intl.NumberFormat('es-CO').format(product.price)}`
                        }));
                        
                        console.log('Productos procesados:', this.products.length);
                    } catch (error) {
                        console.error('Error al cargar productos:', error);
                        alert(`Error al cargar productos: ${error.message}. Por favor, recargue la página o contacte a soporte.`);
                    } finally {
                        this.productsLoading = false;
                    }
                },
                
                async fetchCustomers() {
                    this.customersLoading = true;
                    
                    try {
                        console.log('Iniciando carga de clientes...');
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        console.log('CSRF Token obtenido:', csrfToken ? 'Sí' : 'No');
                        
                        // Usamos una ruta web regular en vez de la API
                        const response = await fetch('/obtener-clientes-para-factura', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken
                            },
                            credentials: 'include' // Importante para mantener la sesión
                        });
                        
                        console.log('Respuesta recibida:', response.status, response.statusText);
                        
                        if (!response.ok) {
                            const errorText = await response.text();
                            console.error('Error en respuesta:', errorText);
                            throw new Error(`Error al cargar clientes: ${response.status} ${response.statusText}`);
                        }
                        
                        const data = await response.json();
                        console.log('Datos recibidos:', data);
                        
                        if (!Array.isArray(data)) {
                            console.error('Formato incorrecto:', data);
                            throw new Error('Formato de respuesta inválido');
                        }
                        
                        if (data.length === 0) {
                            console.log('No se encontraron clientes');
                            alert('No hay clientes disponibles. Por favor, cree algunos clientes primero.');
                            this.customers = [];
                            return;
                        }
                        
                        this.customers = data.map(customer => ({
                            ...customer,
                            displayName: `${customer.name} - ${customer.document_type}: ${customer.document_number}`
                        }));
                        
                        console.log('Clientes procesados:', this.customers.length);
                    } catch (error) {
                        console.error('Error al cargar clientes:', error);
                        alert(`Error al cargar clientes: ${error.message}. Por favor, recargue la página o contacte a soporte.`);
                    } finally {
                        this.customersLoading = false;
                    }
                },
                
                selectProduct() {
                    if (this.selectedProductId) {
                        const product = this.products.find(p => p.id === parseInt(this.selectedProductId));
                        if (product) {
                            this.newItem.product_id = product.id;
                            this.newItem.product_name = product.name;
                            this.newItem.description = product.description || '';
                            this.newItem.price = parseFloat(product.price) || 0;
                            this.newItem.tax_percent = parseFloat(product.tax_rate) || 19;
                        } else {
                            console.error('Producto no encontrado en la lista');
                            this.resetProductFields();
                        }
                    } else {
                        this.resetProductFields();
                    }
                },
                
                resetProductFields() {
                    this.newItem.product_id = '';
                    this.newItem.product_name = '';
                    this.newItem.description = '';
                    this.newItem.quantity = 1;
                    this.newItem.price = 0;
                    this.newItem.tax_percent = 19;
                },
                
                addItemToCart() {
                    if (!this.newItem.product_id || !this.newItem.product_name || this.newItem.quantity <= 0 || this.newItem.price <= 0) {
                        alert('Por favor complete todos los campos del producto correctamente.');
                        return;
                    }
                    
                    this.formData.items.push({...this.newItem});
                    this.resetProductFields();
                    this.selectedProductId = '';
                    
                    // Enfocar el selector de productos para facilitar la adición rápida
                    document.getElementById('product_select').focus();
                },
                
                removeItem(index) {
                    this.formData.items.splice(index, 1);
                },
                
                calculateSubtotal() {
                    return this.formData.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                },
                
                calculateTax() {
                    return this.formData.items.reduce((sum, item) => {
                        return sum + (item.price * item.quantity * (item.tax_percent / 100));
                    }, 0);
                },
                
                calculateTotal() {
                    return this.calculateSubtotal() + this.calculateTax();
                },
                
                formatCurrency(value) {
                    return new Intl.NumberFormat('es-CO', {
                        style: 'currency',
                        currency: 'COP',
                        minimumFractionDigits: 0
                    }).format(value);
                },
                
                validateForm() {
                    // Validación de cliente
                    if (!this.formData.document_number) {
                        alert('Debe ingresar el número de documento del cliente.');
                        return false;
                    }
                    
                    if (!this.formData.phone) {
                        alert('El teléfono del cliente es obligatorio para la facturación electrónica DIAN.');
                        return false;
                    }
                    
                    if (!this.formData.address) {
                        alert('La dirección del cliente es obligatoria para la facturación electrónica DIAN.');
                        return false;
                    }
                    
                    // Validación de productos
                    if (this.formData.items.length === 0) {
                        alert('Debe agregar al menos un producto a la factura.');
                        return false;
                    }
                    
                    // Validación de fecha de vencimiento
                    if (!this.formData.due_date) {
                        alert('La fecha de vencimiento es obligatoria.');
                        return false;
                    }
                    
                    return true;
                },
                
                async submitForm() {
                    if (!this.validateForm()) {
                        return;
                    }
                    
                    this.loading = true;
                    
                    try {
                        const response = await fetch('/generar-factura', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(this.formData),
                            credentials: 'include'
                        });
                        
                        const data = await response.json();
                        
                        if (!response.ok) {
                            throw new Error(data.message || data.error || 'Error al generar la factura');
                        }
                        
                        alert('Factura generada correctamente');
                        window.location.href = `/invoices/${data.invoice.id}`;
                    } catch (error) {
                        console.error('Error al generar la factura:', error);
                        alert(error.message || 'Error al generar la factura. Intente nuevamente.');
                    } finally {
                        this.loading = false;
                    }
                },
                
                selectCustomer() {
                    if (this.selectedCustomerId) {
                        const customer = this.customers.find(c => c.id === parseInt(this.selectedCustomerId));
                        if (customer) {
                            this.formData.document_type = customer.document_type;
                            this.formData.document_number = customer.document_number;
                            this.formData.name = customer.name;
                            this.formData.phone = customer.phone;
                            this.formData.address = customer.address;
                        } else {
                            console.error('Cliente no encontrado en la lista');
                            this.resetCustomerFields();
                        }
                    } else {
                        this.resetCustomerFields();
                    }
                },
                
                resetCustomerFields() {
                    this.formData.document_type = 'CC';
                    this.formData.document_number = '';
                    this.formData.name = '';
                    this.formData.phone = '';
                    this.formData.address = '';
                }
            };
        }
    </script>
    @endpush
</x-app-layout> 
