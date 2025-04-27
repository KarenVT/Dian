<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Editar Cliente') }}
            </h2>
            <a href="{{ route('customers.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('customers.update', $customer) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Columna Izquierda -->
                            <div>
                                <!-- Nombre -->
                                <div class="mb-4">
                                    <x-input-label for="name" :value="__('Nombre')" />
                                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $customer->name)" required autofocus />
                                    @error('name')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Tipo de Documento -->
                                <div class="mb-4">
                                    <x-input-label for="document_type" :value="__('Tipo de Documento')" />
                                    <select id="document_type" name="document_type" x-data="{ type: '{{ old('document_type', $customer->document_type) }}' }" x-model="type" x-on:change="setupIdMask(type)" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        @foreach($documentTypes as $value => $label)
                                            <option value="{{ $value }}" {{ old('document_type', $customer->document_type) == $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('document_type')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Identificación con máscara -->
                                <div class="mb-4">
                                    <x-input-label for="identification" :value="__('Identificación')" />
                                    <div x-data="identificationMask()" class="mt-1">
                                        <x-text-input 
                                            id="identification" 
                                            name="identification" 
                                            type="text" 
                                            class="block w-full" 
                                            :value="old('identification', $customer->identification)" 
                                            x-ref="input"
                                            x-init="setupMask()"
                                            required 
                                        />
                                    </div>
                                    @error('identification')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            
                            <!-- Columna Derecha -->
                            <div>
                                <!-- Email -->
                                <div class="mb-4">
                                    <x-input-label for="email" :value="__('Email')" />
                                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $customer->email)" />
                                    @error('email')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Teléfono -->
                                <div class="mb-4">
                                    <x-input-label for="phone" :value="__('Teléfono')" />
                                    <x-text-input 
                                        id="phone" 
                                        name="phone" 
                                        type="text" 
                                        class="mt-1 block w-full" 
                                        :value="old('phone', $customer->phone)" 
                                        x-data="{}"
                                        x-mask="(999) 999-9999" 
                                    />
                                    @error('phone')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <!-- Dirección -->
                                <div class="mb-4">
                                    <x-input-label for="address" :value="__('Dirección')" />
                                    <textarea 
                                        id="address" 
                                        name="address" 
                                        rows="3" 
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >{{ old('address', $customer->address) }}</textarea>
                                    @error('address')
                                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end mt-6">
                            <a href="{{ route('customers.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150 mr-3">
                                Cancelar
                            </a>
                            
                            <x-primary-button>
                                Actualizar
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
    <script>
        function identificationMask() {
            return {
                setupMask() {
                    // Obtiene el tipo de documento seleccionado
                    const documentType = document.getElementById('document_type').value;
                    this.setupIdMask(documentType);
                },
                
                setupIdMask(type) {
                    // Define las máscaras según el tipo de documento
                    switch(type) {
                        case 'CC':
                            // Cédula: 10 dígitos
                            Alpine.bind(this.$refs.input, () => ({
                                'x-mask': '9999999999'
                            }));
                            break;
                        case 'CE':
                            // Cédula Extranjería: formato alfanumérico
                            Alpine.bind(this.$refs.input, () => ({
                                'x-mask': 'aaaaaaaaaa'
                            }));
                            break;
                        case 'NIT':
                            // NIT: 9 dígitos + 1 dígito verificador
                            Alpine.bind(this.$refs.input, () => ({
                                'x-mask': '999.999.999-9'
                            }));
                            break;
                        case 'PP':
                            // Pasaporte: alfanumérico
                            Alpine.bind(this.$refs.input, () => ({
                                'x-mask': 'aaaaaaaaa*'
                            }));
                            break;
                        case 'TI':
                            // Tarjeta de Identidad: 10 dígitos
                            Alpine.bind(this.$refs.input, () => ({
                                'x-mask': '9999999999'
                            }));
                            break;
                        default:
                            // Sin máscara para otros casos
                            Alpine.bind(this.$refs.input, () => ({
                                'x-mask': ''
                            }));
                    }
                }
            }
        }
    </script>
    @endpush
</x-app-layout> 