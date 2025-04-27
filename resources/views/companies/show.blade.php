<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Datos de mi Compañía') }}
            </h2>
            <div>
                <a href="{{ route('companies.edit') }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                    Editar Datos
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Información de la Compañía</h3>
                            <div class="bg-gray-50 p-4 rounded">
                                <p class="mb-2"><span class="font-semibold">NIT:</span> {{ $company->nit }}</p>
                                <p class="mb-2"><span class="font-semibold">Nombre Comercial:</span> {{ $company->business_name }}</p>
                                <p class="mb-2"><span class="font-semibold">Teléfono:</span> {{ $company->phone ?? 'No especificado' }}</p>
                                <p><span class="font-semibold">Dirección:</span> {{ $company->address ?? 'No especificada' }}</p>
                            </div>
                        </div>
                        
                        <div>
                            <h3 class="text-lg font-semibold mb-2">Estadísticas</h3>
                            <div class="bg-gray-50 p-4 rounded">
                                <p class="mb-2"><span class="font-semibold">Usuarios:</span> {{ $company->users->count() }}</p>
                                <p class="mb-2"><span class="font-semibold">Productos:</span> {{ $company->products->count() }}</p>
                                <p><span class="font-semibold">Facturas:</span> {{ $company->invoices->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 