<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $merchantId = Auth::user()->merchant_id;
        $products = Product::where('merchant_id', $merchantId)->get();
        
        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sku' => 'required|string|max:255|unique:products,sku,NULL,id,merchant_id,' . Auth::user()->merchant_id,
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
            'dian_code' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $product = Product::create([
            'merchant_id' => Auth::user()->merchant_id,
            'sku' => $request->sku,
            'name' => $request->name,
            'price' => $request->price,
            'tax_rate' => $request->tax_rate,
            'dian_code' => $request->dian_code,
        ]);

        return response()->json($product, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::where('merchant_id', Auth::user()->merchant_id)
            ->where('id', $id)
            ->firstOrFail();
            
        return response()->json($product);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::where('merchant_id', Auth::user()->merchant_id)
            ->where('id', $id)
            ->firstOrFail();
            
        $validator = Validator::make($request->all(), [
            'sku' => 'required|string|max:255|unique:products,sku,' . $id . ',id,merchant_id,' . Auth::user()->merchant_id,
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
            'dian_code' => 'nullable|string|max:255',
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
        $product = Product::where('merchant_id', Auth::user()->merchant_id)
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

        $merchantId = Auth::user()->merchant_id;
        
        try {
            $import = new ProductsImport($merchantId);
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
