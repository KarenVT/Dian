<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Editar Datos de mi Compañía') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('companies.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <x-input-label for="nit" :value="__('NIT')" />
                            <x-text-input id="nit" class="block mt-1 w-full" type="text" name="nit" :value="old('nit', $company->nit)" required autofocus />
                            <x-input-error :messages="$errors->get('nit')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="business_name" :value="__('Nombre Comercial')" />
                            <x-text-input id="business_name" class="block mt-1 w-full" type="text" name="business_name" :value="old('business_name', $company->business_name)" required />
                            <x-input-error :messages="$errors->get('business_name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="phone" :value="__('Teléfono')" />
                            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $company->phone)" />
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="address" :value="__('Dirección')" />
                            <x-text-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address', $company->address)" />
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('companies.index') }}" class="text-gray-600 hover:text-gray-900 mr-3">
                                {{ __('Cancelar') }}
                            </a>
                            <x-primary-button>
                                {{ __('Actualizar') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 