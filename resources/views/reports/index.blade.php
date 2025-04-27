<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Reportes de Ventas') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div x-data="reportsData()" x-init="setupDatepickers(); fetchData()">
                <!-- Filtros -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-800">{{ __('Filtros') }}</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <x-input-label for="date_from" :value="__('Fecha Desde')" />
                                <input 
                                    type="date" 
                                    id="date_from"
                                    x-model="filters.from"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                />
                            </div>
                            <div>
                                <x-input-label for="date_to" :value="__('Fecha Hasta')" />
                                <input 
                                    type="date" 
                                    id="date_to"
                                    x-model="filters.to"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                />
                            </div>
                            <div>
                                <x-input-label for="group_by" :value="__('Agrupar por')" />
                                <select 
                                    id="group_by"
                                    x-model="filters.group"
                                    class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                >
                                    <option value="day">{{ __('Día') }}</option>
                                    <option value="hour">{{ __('Hora') }}</option>
                                </select>
                            </div>
                            <div class="flex items-end">
                                <x-primary-button @click="fetchData()">
                                    {{ __('Filtrar') }}
                                </x-primary-button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjetas principales -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <!-- Tarjeta 1: Ventas (total en $) -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-800">{{ __('Ventas') }}</h3>
                        </div>
                        <div class="p-6">
                            <p class="text-3xl font-bold text-green-600" x-text="formatCurrency(data.total_sales)">$0</p>
                            <p class="text-sm text-gray-500 mt-1">{{ __('Total en pesos') }}</p>
                        </div>
                    </div>

                    <!-- Tarjeta 2: Facturas (#) -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-800">{{ __('Facturas') }}</h3>
                        </div>
                        <div class="p-6">
                            <p class="text-3xl font-bold text-blue-600" x-text="data.invoice_count">0</p>
                            <p class="text-sm text-gray-500 mt-1">{{ __('Número total') }}</p>
                        </div>
                    </div>

                    <!-- Tarjeta 3: Total IVA -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-800">{{ __('Total IVA') }}</h3>
                        </div>
                        <div class="p-6">
                            <p class="text-3xl font-bold text-purple-600" x-text="formatCurrency(data.total_tax)">$0</p>
                            <p class="text-sm text-gray-500 mt-1">{{ __('IVA generado') }}</p>
                        </div>
                    </div>

                    <!-- Tarjeta 4: Estado DIAN -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-4 border-b border-gray-200">
                            <h3 class="text-lg font-medium text-gray-800">{{ __('Estado DIAN') }}</h3>
                        </div>
                        <div class="p-6">
                            <p class="text-3xl font-bold text-yellow-600" x-text="data.pending_dian + '/' + data.rejected_dian">0/0</p>
                            <p class="text-sm text-gray-500 mt-1">{{ __('Pendientes / Rechazadas') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Gráfico de ventas -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-800">{{ __('Gráfico de Ventas') }}</h3>
                    </div>
                    <div class="p-6">
                        <canvas id="salesChart" height="100"></canvas>
                    </div>
                </div>

                <!-- Tabla de datos -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-4 border-b border-gray-200 flex justify-between items-center">
                        <h3 class="text-lg font-medium text-gray-800">{{ __('Datos Detallados') }}</h3>
                        <x-secondary-button @click="exportData()" class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            {{ __('Exportar CSV') }}
                        </x-secondary-button>
                    </div>
                    <div class="p-6 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Fecha/Hora') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Ventas') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('Facturas') }}
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ __('IVA') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="item in data.graph" :key="item.label">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="item.label"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatCurrency(item.value)"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="item.invoice_count"></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatCurrency(item.tax_sum)"></td>
                                    </tr>
                                </template>
                                <!-- Fila de totales -->
                                <tr class="bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ __('TOTAL') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="formatCurrency(data.total_sales)"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="data.invoice_count"></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="formatCurrency(data.total_tax)"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function reportsData() {
            return {
                filters: {
                    from: '',
                    to: '',
                    group: 'day'
                },
                data: {
                    total_sales: 0,
                    invoice_count: 0,
                    total_tax: 0,
                    pending_dian: 0,
                    rejected_dian: 0,
                    sales_by_hour: [],
                    graph: []
                },
                chart: null,

                setupDatepickers() {
                    // Inicializar con fecha de hoy y hace 7 días
                    const today = new Date();
                    const lastWeek = new Date();
                    lastWeek.setDate(lastWeek.getDate() - 7);
                    
                    this.filters.to = this.formatDateForInput(today);
                    this.filters.from = this.formatDateForInput(lastWeek);
                },

                formatDateForInput(date) {
                    return date.toISOString().split('T')[0];
                },

                formatCurrency(value) {
                    return new Intl.NumberFormat('es-CO', {
                        style: 'currency',
                        currency: 'COP', 
                        minimumFractionDigits: 0
                    }).format(value || 0);
                },

                fetchData() {
                    axios.get('/api/reports/sales', {
                        params: this.filters
                    })
                    .then(response => {
                        this.data = response.data;
                        this.updateChart();
                    })
                    .catch(error => {
                        console.error('Error al obtener datos del reporte:', error);
                        alert('Error al cargar los datos del reporte.');
                    });
                },

                updateChart() {
                    const ctx = document.getElementById('salesChart');
                    
                    // Destruir gráfico existente si hay uno
                    if (this.chart) {
                        this.chart.destroy();
                    }
                    
                    if (!this.data.graph || this.data.graph.length === 0) {
                        return;
                    }
                    
                    // Preparar datos para el gráfico
                    const labels = this.data.graph.map(item => item.label);
                    const values = this.data.graph.map(item => item.value);
                    
                    // Crear nuevo gráfico
                    this.chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Ventas',
                                data: values,
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                                tension: 0.1,
                                fill: true
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '$' + value.toLocaleString('es-CO');
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return '$' + context.raw.toLocaleString('es-CO');
                                        }
                                    }
                                }
                            }
                        }
                    });
                },

                exportData() {
                    // Construir URL con los parámetros de filtro
                    const params = new URLSearchParams(this.filters);
                    window.location = `/api/reports/export?${params.toString()}`;
                }
            };
        }
    </script>
    @endpush
</x-app-layout> 