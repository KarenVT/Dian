<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\companyRequest;
use App\Models\company;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CompanyController extends Controller
{
    /**
     * Almacena un nuevo comercio en la base de datos.
     *
     * @param companyRequest $request
     * @return JsonResponse
     */
    public function store(companyRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                // Procesar el certificado
                $certificatePath = $this->storeCertificate($request);
                
                // Crear el comercio
                $company = company::create([
                    'nit' => $request->nit,
                    'business_name' => $request->business_name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'tax_regime' => $request->tax_regime,
                    'certificate_path' => $certificatePath,
                ]);
                
                // Retornar respuesta exitosa
                return response()->json([
                    'message' => 'Comercio registrado exitosamente',
                    'data' => $company,
                ], 201);
            });
        } catch (\Exception $e) {
            // Si hubo un error, eliminar el certificado si se cargó
            if (isset($certificatePath) && Storage::exists($certificatePath)) {
                Storage::delete($certificatePath);
            }
            
            return response()->json([
                'message' => 'Error al registrar el comercio',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muestra la información de un comercio específico.
     *
     * @param company $company
     * @return JsonResponse
     */
    public function show(company $company): JsonResponse
    {
        return response()->json([
            'data' => $company
        ]);
    }

    /**
     * Actualiza la información de un comercio existente.
     *
     * @param companyRequest $request
     * @param company $company
     * @return JsonResponse
     */
    public function update(companyRequest $request, company $company): JsonResponse
    {
        try {
            // Datos a actualizar
            $dataToUpdate = [
                'business_name' => $request->business_name ?? $company->business_name,
                'email' => $request->email ?? $company->email,
                'tax_regime' => $request->tax_regime ?? $company->tax_regime,
            ];
            
            // Actualizar contraseña si se proporcionó
            if ($request->has('password')) {
                $dataToUpdate['password'] = Hash::make($request->password);
            }
            
            // Procesar el certificado si se proporcionó uno nuevo
            if ($request->hasFile('certificate')) {
                // Eliminar el certificado anterior si existe
                if ($company->certificate_path && Storage::exists($company->certificate_path)) {
                    Storage::delete($company->certificate_path);
                }
                
                // Almacenar el nuevo certificado
                $dataToUpdate['certificate_path'] = $this->storeCertificate($request);
            }
            
            // Actualizar el comercio
            $company->update($dataToUpdate);
            
            return response()->json([
                'message' => 'Comercio actualizado exitosamente',
                'data' => $company
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el comercio',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Almacena el certificado p12 en el almacenamiento.
     *
     * @param companyRequest $request
     * @return string|null La ruta del certificado almacenado o null si no hay certificado
     */
    private function storeCertificate(companyRequest $request): ?string
    {
        if (!$request->hasFile('certificate')) {
            return null;
        }
        
        // Generar un UUID para el nombre del archivo
        $fileName = Str::uuid() . '.p12';
        
        // Crear directorio para los certificados del comercio si no existe
        $companyDir = 'certs/' . Str::slug($request->business_name);
        if (!Storage::exists($companyDir)) {
            Storage::makeDirectory($companyDir);
        }
        
        // Almacenar el certificado
        $path = $request->file('certificate')->storeAs($companyDir, $fileName);
        
        return $path;
    }

    /**
     * Actualiza solo el certificado de un comercio existente.
     *
     * @param \Illuminate\Http\Request $request
     * @param company $company
     * @return JsonResponse
     */
    public function updateCertificate(\Illuminate\Http\Request $request, company $company): JsonResponse
    {
        try {
            // Validar el certificado
            $request->validate([
                'certificate' => ['required', 'file', 'mimes:p12', 'max:4096'], // 4MB máximo
            ], [
                'certificate.required' => 'El certificado digital es obligatorio.',
                'certificate.file' => 'El certificado debe ser un archivo.',
                'certificate.mimes' => 'El certificado debe tener extensión .p12.',
                'certificate.max' => 'El tamaño del certificado no debe exceder 4 MB.',
            ]);

            // Eliminar el certificado anterior si existe
            if ($company->certificate_path && Storage::exists($company->certificate_path)) {
                Storage::delete($company->certificate_path);
            }
            
            // Generar un UUID para el nombre del archivo
            $fileName = Str::uuid() . '.p12';
            
            // Crear directorio para los certificados del comercio si no existe
            $companyDir = 'certs/' . Str::slug($company->business_name);
            if (!Storage::exists($companyDir)) {
                Storage::makeDirectory($companyDir);
            }
            
            // Almacenar el certificado
            $path = $request->file('certificate')->storeAs($companyDir, $fileName);
            
            // Actualizar la ruta del certificado en el comercio
            $company->update([
                'certificate_path' => $path,
            ]);
            
            // Simular información de expiración para la vista (esto debería venir de un análisis real del certificado)
            $expirationDate = new \DateTime();
            $expirationDate->modify('+1 year');
            
            return response()->json([
                'message' => 'Certificado actualizado exitosamente',
                'data' => [
                    'certificate_path' => $company->certificate_path,
                    'expiration_date' => $expirationDate->format('Y-m-d'),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el certificado',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 