<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div x-data="dashboardData()" x-init="fetchData(); setupPolling()">
                <!-- Tarjetas principales con diseño optimizado -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-4 mb-4">
                    <!-- Tarjeta: Facturas hoy -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg transition-all duration-300 hover:shadow-md border border-gray-100">
                        <div class="p-3 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-white">
                            <h3 class="text-sm font-medium text-gray-700 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                </svg>
                                Facturas hoy
                            </h3>
                        </div>
                        <div class="p-3">
                            <p class="text-2xl font-bold text-blue-600" x-text="invoiceCount">0</p>
                            <p class="text-xs text-gray-500 mt-1">Número total</p>
                        </div>
                    </div>

                    <!-- Tarjeta: Estado DIAN -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg transition-all duration-300 hover:shadow-md border border-gray-100">
                        <div class="p-3 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-white">
                            <h3 class="text-sm font-medium text-gray-700 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-yellow-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                Estado DIAN
                            </h3>
                        </div>
                        <div class="p-3">
                            <p class="text-2xl font-bold text-yellow-600" x-text="dianStatus">0/0</p>
                            <p class="text-xs text-gray-500 mt-1">Pendientes / Rechazadas</p>
                        </div>
                        </div>
                    </div>

                <!-- Tarjetas de navegación -->
                <h3 class="text-sm font-medium text-gray-700 mb-2">Accesos rápidos</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                    <!-- Tarjeta: Facturas -->
                    <a href="{{ route('invoices.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg transition-all duration-300 hover:shadow-md border border-gray-100 hover:border-blue-200 hover:bg-blue-50">
                        <div class="p-3 flex items-center">
                            <div class="bg-blue-100 p-2 rounded-lg mr-3">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800">Facturas</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Gestionar facturas</p>
                            </div>
                        </div>
                    </a>

                    <!-- Tarjeta: Mis Datos -->
                    <a href="{{ route('companies.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg transition-all duration-300 hover:shadow-md border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50">
                        <div class="p-3 flex items-center">
                            <div class="bg-indigo-100 p-2 rounded-lg mr-3">
                                <svg class="w-5 h-5 text-indigo-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm3 1h6v4H7V5zm8 8v2h1v1H4v-1h1v-2H4v-1h16v1h-1zm-2 0H7v2h6v-2z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800">Mis Datos</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Información de empresa</p>
                            </div>
                        </div>
                    </a>

                    <!-- Tarjeta: Reportes -->
                    <a href="{{ route('reports.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg transition-all duration-300 hover:shadow-md border border-gray-100 hover:border-green-200 hover:bg-green-50">
                        <div class="p-3 flex items-center">
                            <div class="bg-green-100 p-2 rounded-lg mr-3">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800">Reportes</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Informes y estadísticas</p>
                    </div>
                        </div>
                    </a>

                    <!-- Tarjeta: Mis Clientes -->
                    <a href="{{ route('customers.index') }}" class="bg-white overflow-hidden shadow-sm sm:rounded-lg transition-all duration-300 hover:shadow-md border border-gray-100 hover:border-purple-200 hover:bg-purple-50">
                        <div class="p-3 flex items-center">
                            <div class="bg-purple-100 p-2 rounded-lg mr-3">
                                <svg class="w-5 h-5 text-purple-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-800">Mis Clientes</h3>
                                <p class="text-xs text-gray-500 mt-0.5">Gestión de clientes</p>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Información adicional -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border border-gray-100">
                    <div class="p-3 border-b border-gray-200 bg-gradient-to-r from-indigo-50 to-white">
                        <h3 class="text-sm font-medium text-gray-700 flex items-center">
                            <svg class="w-4 h-4 mr-2 text-indigo-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                            Información de usuario
                        </h3>
                    </div>
                    <div class="p-3 text-gray-900">
                        <div class="mb-2 text-sm">
                            {{ __("Has iniciado sesión correctamente") }}
                        </div>

                        <div class="border-t pt-2">
                            <h3 class="text-xs font-medium mb-2 text-gray-600">Información de tu perfil:</h3>
                            <div class="bg-gray-50 p-3 rounded border border-gray-100">
                                <div class="text-sm mb-1"><span class="font-medium text-gray-700">Nombre:</span> {{ auth()->user()->name }}</div>
                                <div class="text-sm mb-1"><span class="font-medium text-gray-700">Email:</span> {{ auth()->user()->email }}</div>
                                <div class="text-sm"><span class="font-medium text-gray-700">Rol:</span> 
                                    @foreach(auth()->user()->getRoleNames() as $role)
                                        <span class="inline-block bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded-full text-xs">{{ ucfirst($role) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function dashboardData() {
            return {
                invoiceCount: 0,
                dianStatus: '0/0',

                fetchData() {
                    axios.get('/api/reports/sales', {
                        params: {
                            from: 'today',
                            to: 'today',
                            group: 'day'
                        }
                    })
                    .then(response => {
                        const data = response.data;
                        
                        // Actualizar datos de las tarjetas
                        this.invoiceCount = data.invoice_count || 0;
                        this.dianStatus = (data.pending_dian || 0) + '/' + (data.rejected_dian || 0);
                    })
                    .catch(error => {
                        console.error('Error al obtener datos del dashboard:', error);
                    });
                },

                setupPolling() {
                    // Configurar polling cada 60 segundos
                    setInterval(() => {
                        this.fetchData();
                    }, 60000);
                }
            };
        }
    </script>
    @endpush
</x-app-layout>
