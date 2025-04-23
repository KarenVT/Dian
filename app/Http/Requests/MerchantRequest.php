<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MerchantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'business_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'tax_regime' => ['required', Rule::in(['COMÚN', 'SIMPLE', 'NO_RESPONSABLE_IVA'])],
            'certificate' => ['sometimes', 'file', 'mimes:p12', 'max:2048'],
        ];

        // Reglas para la creación (store)
        if ($this->isMethod('post')) {
            $rules['nit'] = ['required', 'string', 'regex:/^[0-9]{9,10}$/', 'unique:merchants,nit'];
            $rules['password'] = ['required', 'string', 'min:8'];
            $rules['certificate'] = ['required', 'file', 'mimes:p12', 'max:2048'];
        } 
        // Reglas para la actualización (update)
        else if ($this->isMethod('put') || $this->isMethod('patch')) {
            $merchantId = $this->route('merchant');
            $rules['nit'] = ['sometimes', 'required', 'string', 'regex:/^[0-9]{9,10}$/', Rule::unique('merchants')->ignore($merchantId)];
            $rules['password'] = ['sometimes', 'string', 'min:8'];
            $rules['email'] = ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('merchants')->ignore($merchantId)];
        }

        return $rules;
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nit.required' => 'El NIT es obligatorio.',
            'nit.string' => 'El NIT debe ser un texto.',
            'nit.regex' => 'El NIT debe tener entre 9 y 10 dígitos numéricos.',
            'nit.unique' => 'Este NIT ya está registrado.',
            'business_name.required' => 'La razón social es obligatoria.',
            'business_name.string' => 'La razón social debe ser un texto.',
            'business_name.max' => 'La razón social no debe exceder los 255 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.string' => 'El correo electrónico debe ser un texto.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'email.max' => 'El correo electrónico no debe exceder los 255 caracteres.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.string' => 'La contraseña debe ser un texto.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'tax_regime.required' => 'El régimen tributario es obligatorio.',
            'tax_regime.in' => 'Seleccione un régimen tributario válido: COMÚN, SIMPLE o NO_RESPONSABLE_IVA.',
            'certificate.required' => 'El certificado digital es obligatorio.',
            'certificate.file' => 'El certificado debe ser un archivo.',
            'certificate.mimes' => 'El certificado debe tener extensión .p12.',
            'certificate.max' => 'El tamaño del certificado no debe exceder 2 MB.',
        ];
    }
} 