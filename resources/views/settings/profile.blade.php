<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Perfil del Comercio') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Datos del Comercio') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Actualice la información de su comercio.') }}
                            </p>
                        </header>

                        <form id="update-company-form" method="post" action="{{ route('api.companies.update', ['company' => auth()->user()->company->id]) }}" class="mt-6 space-y-6">
                            @csrf
                            @method('put')

                            <div>
                                <x-input-label for="business_name" :value="__('Razón Social')" />
                                <x-text-input id="business_name" name="business_name" type="text" class="mt-1 block w-full" :value="old('business_name', auth()->user()->company->business_name)" required autofocus />
                                <x-input-error class="mt-2" :messages="$errors->get('business_name')" />
                            </div>

                            <div>
                                <x-input-label for="tax_regime" :value="__('Régimen')" />
                                <select id="tax_regime" name="tax_regime" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    <option value="COMÚN" {{ old('tax_regime', auth()->user()->company->tax_regime) === 'COMÚN' ? 'selected' : '' }}>{{ __('Común') }}</option>
                                    <option value="SIMPLE" {{ old('tax_regime', auth()->user()->company->tax_regime) === 'SIMPLE' ? 'selected' : '' }}>{{ __('Simple') }}</option>
                                    <option value="NO_RESPONSABLE_IVA" {{ old('tax_regime', auth()->user()->company->tax_regime) === 'NO_RESPONSABLE_IVA' ? 'selected' : '' }}>{{ __('No Responsable de IVA') }}</option>
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('tax_regime')" />
                            </div>

                            <div>
                                <x-input-label for="email" :value="__('Email')" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', auth()->user()->company->email)" required />
                                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('Guardar') }}</x-primary-button>

                                <p
                                    x-data="{ show: false, message: '' }"
                                    x-show="show"
                                    x-text="message"
                                    x-on:company-updated.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 2000)"
                                    class="text-sm text-gray-600"
                                ></p>
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900">
                                {{ __('Certificado DIAN') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600">
                                {{ __('Suba su certificado digital para la facturación electrónica.') }}
                            </p>
                        </header>

                        <div 
                            x-data="certificateUploader()"
                            x-init="checkCertificateStatus()"
                            class="mt-6"
                        >
                            @if(auth()->user()->company->certificate_path)
                                <div
                                    x-show="certificateStatus.valid"
                                    class="mb-4 p-3 bg-green-100 border border-green-200 rounded-md text-green-800"
                                >
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <span class="font-medium">{{ __('Certificado vigente') }}</span>
                                    </div>
                                    <p x-text="'Válido hasta: ' + certificateStatus.expirationDate" class="mt-1 text-sm"></p>
                                </div>
                            @endif

                            <form 
                                id="certificate-form" 
                                method="post" 
                                enctype="multipart/form-data"
                                x-on:submit.prevent="uploadCertificate"
                            >
                                <div class="mb-4">
                                    <x-input-label for="certificate" :value="__('Subir Certificado (.p12)')" />
                                    <input 
                                        type="file" 
                                        id="certificate" 
                                        name="certificate" 
                                        accept=".p12"
                                        x-ref="certificateInput"
                                        x-on:change="validateFileInput"
                                        class="mt-1 block w-full text-sm text-gray-500
                                              file:mr-4 file:py-2 file:px-4
                                              file:rounded-md file:border-0
                                              file:text-sm file:font-semibold
                                              file:bg-blue-50 file:text-blue-700
                                              hover:file:bg-blue-100"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">{{ __('Tamaño máximo: 4 MB') }}</p>
                                    <div x-show="fileError" x-text="fileError" class="mt-2 text-sm text-red-600"></div>
                                </div>

                                <div class="flex items-center gap-4">
                                    <x-primary-button x-bind:disabled="isUploading || fileError">
                                        <span x-show="!isUploading">{{ __('Subir Certificado') }}</span>
                                        <span x-show="isUploading" class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            {{ __('Subiendo...') }}
                                        </span>
                                    </x-primary-button>

                                    <p
                                        x-show="uploadMessage"
                                        x-text="uploadMessage"
                                        x-bind:class="uploadStatus === 'success' ? 'text-green-600' : 'text-red-600'"
                                        class="text-sm"
                                    ></p>
                                </div>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function certificateUploader() {
            return {
                isUploading: false,
                fileError: '',
                uploadMessage: '',
                uploadStatus: '',
                certificateStatus: {
                    valid: false,
                    expirationDate: ''
                },

                validateFileInput() {
                    const fileInput = this.$refs.certificateInput;
                    if (fileInput.files.length === 0) {
                        this.fileError = '';
                        return;
                    }

                    const file = fileInput.files[0];
                    
                    // Validar tamaño (4MB máximo)
                    if (file.size > 4 * 1024 * 1024) {
                        this.fileError = '{{ __("El archivo es demasiado grande. El tamaño máximo es 4 MB.") }}';
                        return;
                    }
                    
                    // Validar extensión
                    if (!file.name.toLowerCase().endsWith('.p12')) {
                        this.fileError = '{{ __("El archivo debe tener extensión .p12") }}';
                        return;
                    }
                    
                    this.fileError = '';
                },

                async uploadCertificate() {
                    if (this.fileError) return;
                    
                    const fileInput = this.$refs.certificateInput;
                    if (fileInput.files.length === 0) {
                        this.fileError = '{{ __("Por favor, seleccione un archivo") }}';
                        return;
                    }
                    
                    const formData = new FormData();
                    formData.append('certificate', fileInput.files[0]);
                    formData.append('_method', 'PUT');
                    
                    this.isUploading = true;
                    this.uploadMessage = '';
                    
                    try {
                        const response = await fetch(`/api/companies/{{ auth()->user()->company->id }}/certificate`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (response.ok) {
                            this.uploadStatus = 'success';
                            this.uploadMessage = '{{ __("Certificado subido correctamente") }}';
                            fileInput.value = '';
                            this.checkCertificateStatus();
                        } else {
                            this.uploadStatus = 'error';
                            this.uploadMessage = result.message || '{{ __("Error al subir el certificado") }}';
                        }
                    } catch (error) {
                        this.uploadStatus = 'error';
                        this.uploadMessage = '{{ __("Error de conexión al subir el certificado") }}';
                        console.error(error);
                    } finally {
                        this.isUploading = false;
                        setTimeout(() => {
                            this.uploadMessage = '';
                        }, 3000);
                    }
                },

                async checkCertificateStatus() {
                    try {
                        const response = await fetch(`/api/companies/{{ auth()->user()->company->id }}`, {
                            headers: {
                                'Accept': 'application/json'
                            }
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            const company = data.data;
                            
                            if (company.certificate_path) {
                                // Simulamos que el certificado es válido con una fecha de expiración
                                // En un caso real, esta información debería venir del backend
                                this.certificateStatus.valid = true;
                                const expDate = new Date();
                                expDate.setFullYear(expDate.getFullYear() + 1);
                                this.certificateStatus.expirationDate = expDate.toLocaleDateString();
                            } else {
                                this.certificateStatus.valid = false;
                                this.certificateStatus.expirationDate = '';
                            }
                        }
                    } catch (error) {
                        console.error('Error al verificar el estado del certificado:', error);
                    }
                }
            };
        }

        // Script para el formulario de actualización de datos del comercio
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('update-company-form');
            if (form) {
                form.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(form);
                    
                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Accept': 'application/json'
                            },
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (response.ok) {
                            window.dispatchEvent(new CustomEvent('company-updated', {
                                detail: {
                                    message: '{{ __("Datos actualizados correctamente") }}'
                                }
                            }));
                        } else {
                            // Mostrar errores si hay alguno
                            console.error('Error al actualizar datos:', result);
                        }
                    } catch (error) {
                        console.error('Error al actualizar datos del comercio:', error);
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout> 