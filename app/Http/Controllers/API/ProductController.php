<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Usuario no autenticado'], 401);
            }
            
            $companyId = $user->company_id;
            if (!$companyId) {
                return response()->json(['error' => 'Usuario sin compañía asignada'], 400);
            }
            
            $query = Product::where('company_id', $companyId);
            
            // Filtrar por búsqueda si está presente
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }
            
            // Si es para el selector de productos en facturas, devolver sin paginar
            if ($request->input('for') === 'invoice') {
                $products = $query->select(['id', 'name', 'price', 'tax_rate'])->get();
                return response()->json($products);
            } else {
                $products = $query->paginate($request->input('per_page', 10));
                return response()->json($products);
            }
        } catch (\Exception $e) {
            Log::error('Error al obtener productos: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar la solicitud: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Product::create([
            'company_id' => Auth::user()->company_id,
            'name' => $request->name,
            'price' => $request->price,
            'tax_rate' => $request->tax_rate,
        ]);

        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->firstOrFail();
            
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->firstOrFail();
            
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product->update($request->all());

        return response()->json($product);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::where('company_id', Auth::user()->company_id)
            ->where('id', $id)
            ->firstOrFail();
            
        $product->delete();
        
        return response()->json(null, 204);
    }
    
    /**
     * Importar productos desde un archivo CSV.
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,txt',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $companyId = Auth::user()->company_id;
        
        try {
            $import = new ProductsImport($companyId);
            Excel::import($import, $request->file('file'));
            
            $result = [
                'inserted' => $import->getInsertedCount(),
                'duplicates' => $import->getDuplicates(),
            ];
            
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al importar productos: ' . $e->getMessage()], 500);
        }
    }
}
