<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    /**
     * Mostrar el perfil de la compañía del usuario actual
     */
    public function index()
    {
        // Obtener la compañía del usuario autenticado
        $company = Auth::user()->company;
        
        if (!$company) {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes una compañía asignada');
        }
        
        return view('companies.show', compact('company'));
    }

    /**
     * Mostrar el formulario para editar los datos de la compañía
     */
    public function edit()
    {
        // Obtener la compañía del usuario autenticado
        $company = Auth::user()->company;
        
        if (!$company) {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes una compañía asignada');
        }
        
        return view('companies.edit', compact('company'));
    }

    /**
     * Actualizar los datos de la compañía
     */
    public function update(Request $request)
    {
        // Obtener la compañía del usuario autenticado
        $company = Auth::user()->company;
        
        if (!$company) {
            return redirect()->route('dashboard')
                ->with('error', 'No tienes una compañía asignada');
        }
        
        $validated = $request->validate([
            'nit' => ['required', 'string', 'max:20', Rule::unique('companies')->ignore($company->id)],
            'business_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);

        $company->update($validated);

        return redirect()->route('companies.index')
            ->with('success', 'Datos de la compañía actualizados exitosamente');
    }
} 