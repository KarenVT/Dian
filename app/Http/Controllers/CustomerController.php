<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    /**
     * Muestra el listado de clientes.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $companyId = Auth::user()->company_id;
        
        $customers = Customer::where('company_id', $companyId)
            ->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('document_number', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->orderBy('name', 'asc')
            ->paginate(10);
        
        // Lista de tipos de documento para mostrar en la interfaz
        $documentTypes = [
            'CC' => 'Cédula de Ciudadanía',
            'CE' => 'Cédula de Extranjería',
            'NIT' => 'NIT',
            'PP' => 'Pasaporte',
            'TI' => 'Tarjeta de Identidad'
        ];
        
        return view('customers.index', compact('customers', 'search', 'documentTypes'));
    }

    /**
     * Muestra el formulario para crear un nuevo cliente.
     */
    public function create()
    {
        // Lista de tipos de documento para el formulario
        $documentTypes = [
            'CC' => 'Cédula de Ciudadanía',
            'CE' => 'Cédula de Extranjería',
            'NIT' => 'NIT',
            'PP' => 'Pasaporte',
            'TI' => 'Tarjeta de Identidad'
        ];
        
        return view('customers.create', compact('documentTypes'));
    }

    /**
     * Almacena un nuevo cliente en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document_number' => 'required|string|max:20',
            'document_type' => 'required|string|max:10',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        // Verificar que no exista un cliente con la misma identificación y tipo de documento
        $exists = Customer::where('company_id', Auth::user()->company_id)
            ->where('document_number', $validated['document_number'])
            ->where('document_type', $validated['document_type'])
            ->exists();
            
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['document_number' => 'Ya existe un cliente con esta identificación y tipo de documento.']);
        }

        // Crear el cliente
        $customer = new Customer($validated);
        $customer->company_id = Auth::user()->company_id;
        $customer->save();
        
        return redirect()->route('customers.index')
            ->with('success', 'Cliente creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un cliente.
     */
    public function edit(Customer $customer)
    {
        // Verificar que pertenezca al comercio actual
        if ($customer->company_id !== Auth::user()->company_id) {
            abort(403, 'No está autorizado para editar este cliente.');
        }
        
        // Lista de tipos de documento para el formulario
        $documentTypes = [
            'CC' => 'Cédula de Ciudadanía',
            'CE' => 'Cédula de Extranjería',
            'NIT' => 'NIT',
            'PP' => 'Pasaporte',
            'TI' => 'Tarjeta de Identidad'
        ];
        
        return view('customers.edit', compact('customer', 'documentTypes'));
    }

    /**
     * Actualiza un cliente en la base de datos.
     */
    public function update(Request $request, Customer $customer)
    {
        // Verificar que pertenezca al comercio actual
        if ($customer->company_id !== Auth::user()->company_id) {
            abort(403, 'No está autorizado para actualizar este cliente.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'document_number' => 'required|string|max:20',
            'document_type' => 'required|string|max:10',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);
        
        // Verificar que no exista otro cliente con la misma identificación y tipo de documento
        $exists = Customer::where('company_id', Auth::user()->company_id)
            ->where('document_number', $validated['document_number'])
            ->where('document_type', $validated['document_type'])
            ->where('id', '!=', $customer->id)
            ->exists();
            
        if ($exists) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['document_number' => 'Ya existe otro cliente con esta identificación y tipo de documento.']);
        }
        
        $customer->update($validated);
        
        return redirect()->route('customers.index')
            ->with('success', 'Cliente actualizado exitosamente.');
    }

    /**
     * Elimina un cliente de la base de datos.
     */
    public function destroy(Customer $customer)
    {
        // Verificar que pertenezca al comercio actual
        if ($customer->company_id !== Auth::user()->company_id) {
            abort(403, 'No está autorizado para eliminar este cliente.');
        }
        
        $customer->delete();
        
        return redirect()->route('customers.index')
            ->with('success', 'Cliente eliminado exitosamente.');
    }
}
