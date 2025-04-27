<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Productos') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Barra superior con buscador y botones -->
                    <div x-data="{ search: '{{ $search }}' }" class="mb-6">
                        <div class="flex flex-col sm:flex-row justify-between gap-4">
                            <!-- Buscador -->
                            <div class="relative flex-1">
                                <form method="GET" action="{{ route('products.index') }}" id="search-form">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <input 
                                        type="text" 
                                        name="search" 
                                        id="search" 
                                        x-model="search" 
                                        @input.debounce.300ms="document.getElementById('search-form').submit()"
                                        class="form-input pl-10 block w-full sm:text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500" 
                                        placeholder="Buscar productos..."
                                    >
                                </form>
                            </div>
                            
                            <!-- Botones de acción -->
                            <div class="flex gap-3">
                                <div x-data="{ exportDropdownOpen: false }" class="relative">
                                    <button 
                                        @click="exportDropdownOpen = !exportDropdownOpen" 
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                    >
                                        Exportar
                                    </button>
                                    
                                    <!-- Dropdown -->
                                    <div 
                                        x-show="exportDropdownOpen" 
                                        @click.outside="exportDropdownOpen = false"
                                        class="absolute z-10 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none"
                                        x-transition:enter="transition ease-out duration-100"
                                        x-transition:enter-start="transform opacity-0 scale-95"
                                        x-transition:enter-end="transform opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="transform opacity-100 scale-100"
                                        x-transition:leave-end="transform opacity-0 scale-95"
                                    >
                                        <div class="py-1">
                                            <a href="{{ route('products.export', ['format' => 'xlsx']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Exportar a Excel</a>
                                            <a href="{{ route('products.export', ['format' => 'csv']) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Exportar a CSV</a>
                                        </div>
                                    </div>
                                </div>
                                
                                <button 
                                    @click="$dispatch('open-modal', 'import-modal')" 
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                >
                                    Importar CSV
                                </button>
                                <a 
                                    href="{{ route('products.create') }}" 
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    Nuevo producto
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de productos -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IVA</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse ($products as $product)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            ${{ number_format($product->price, 2, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->tax_rate }}%</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                            <div class="flex justify-end items-center space-x-2">
                                                <a href="{{ route('products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                                <a href="{{ route('products.price-history', $product) }}" class="text-blue-600 hover:text-blue-900 ml-2">Historial de precios</a>
                                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button 
                                                        type="button" 
                                                        onclick="confirmDelete(this)"
                                                        class="text-red-600 hover:text-red-900 ml-2"
                                                    >
                                                        Eliminar
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-center text-gray-500">
                                            No se encontraron productos. 
                                            @if($search)
                                                <a href="{{ route('products.index') }}" class="text-indigo-600 hover:text-indigo-900">Ver todos</a>
                                            @else
                                                <a href="{{ route('products.create') }}" class="text-indigo-600 hover:text-indigo-900">Crear producto</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="mt-4">
                        {{ $products->withQueryString()->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de importación -->
    <x-modal name="import-modal" :show="false" maxWidth="md">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">
                Importar Productos desde CSV
            </h2>

            <form 
                id="import-form" 
                action="{{ route('products.preview-import') }}" 
                method="POST" 
                enctype="multipart/form-data"
                x-data="{ file: null, importProcessing: false }"
            >
                @csrf
                
                <div class="mb-4">
                    <x-input-label for="file" value="Archivo CSV" />
                    <input 
                        type="file" 
                        id="file" 
                        name="file" 
                        accept=".csv,.txt"
                        class="mt-1 block w-full text-sm text-gray-500
                               file:mr-4 file:py-2 file:px-4
                               file:rounded-md file:border-0
                               file:text-sm file:font-semibold
                               file:bg-indigo-50 file:text-indigo-700
                               hover:file:bg-indigo-100"
                        x-on:change="file = $event.target.files[0]"
                        required
                    />
                    <div class="mt-2 text-sm text-gray-500">
                        El archivo debe tener las columnas: name, price, tax_rate
                        <a href="{{ route('products.download-template') }}" class="text-indigo-600 hover:text-indigo-900 ml-2">
                            Descargar plantilla de ejemplo
                        </a>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Cancelar
                    </x-secondary-button>
                    
                    <x-primary-button
                        x-bind:disabled="importProcessing || !file"
                        x-on:click="importProcessing = true; document.getElementById('import-form').submit()"
                    >
                        <span x-show="!importProcessing">Previsualizar</span>
                        <span x-show="importProcessing">Procesando...</span>
                    </x-primary-button>
                </div>
            </form>
        </div>
    </x-modal>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(button) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    button.closest('form').submit();
                }
            });
        }
    </script>
    @endpush
</x-app-layout> 