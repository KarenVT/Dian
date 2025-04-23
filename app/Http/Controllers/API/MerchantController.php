<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\MerchantRequest;
use App\Models\Merchant;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MerchantController extends Controller
{
    /**
     * Almacena un nuevo comercio en la base de datos.
     *
     * @param MerchantRequest $request
     * @return JsonResponse
     */
    public function store(MerchantRequest $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                // Procesar el certificado
                $certificatePath = $this->storeCertificate($request);
                
                // Crear el comercio
                $merchant = Merchant::create([
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
                    'data' => $merchant,
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
     * @param Merchant $merchant
     * @return JsonResponse
     */
    public function show(Merchant $merchant): JsonResponse
    {
        return response()->json([
            'data' => $merchant
        ]);
    }

    /**
     * Actualiza la información de un comercio existente.
     *
     * @param MerchantRequest $request
     * @param Merchant $merchant
     * @return JsonResponse
     */
    public function update(MerchantRequest $request, Merchant $merchant): JsonResponse
    {
        try {
            // Datos a actualizar
            $dataToUpdate = [
                'business_name' => $request->business_name ?? $merchant->business_name,
                'email' => $request->email ?? $merchant->email,
                'tax_regime' => $request->tax_regime ?? $merchant->tax_regime,
            ];
            
            // Actualizar contraseña si se proporcionó
            if ($request->has('password')) {
                $dataToUpdate['password'] = Hash::make($request->password);
            }
            
            // Procesar el certificado si se proporcionó uno nuevo
            if ($request->hasFile('certificate')) {
                // Eliminar el certificado anterior si existe
                if ($merchant->certificate_path && Storage::exists($merchant->certificate_path)) {
                    Storage::delete($merchant->certificate_path);
                }
                
                // Almacenar el nuevo certificado
                $dataToUpdate['certificate_path'] = $this->storeCertificate($request);
            }
            
            // Actualizar el comercio
            $merchant->update($dataToUpdate);
            
            return response()->json([
                'message' => 'Comercio actualizado exitosamente',
                'data' => $merchant
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
     * @param MerchantRequest $request
     * @return string|null La ruta del certificado almacenado o null si no hay certificado
     */
    private function storeCertificate(MerchantRequest $request): ?string
    {
        if (!$request->hasFile('certificate')) {
            return null;
        }
        
        // Generar un UUID para el nombre del archivo
        $fileName = Str::uuid() . '.p12';
        
        // Crear directorio para los certificados del comercio si no existe
        $merchantDir = 'certs/' . Str::slug($request->business_name);
        if (!Storage::exists($merchantDir)) {
            Storage::makeDirectory($merchantDir);
        }
        
        // Almacenar el certificado
        $path = $request->file('certificate')->storeAs($merchantDir, $fileName);
        
        return $path;
    }
} 