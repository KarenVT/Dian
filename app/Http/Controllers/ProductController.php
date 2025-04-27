<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductPriceHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ProductsImport;
use App\Models\company;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Muestra el listado de productos.
     */
    public function index(Request $request)
    {
        $search = $request->input('search', '');
        $companyId = Auth::user()->company_id;
        
        $products = Product::where('company_id', $companyId)
            ->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return view('products.index', compact('products', 'search'));
    }

    /**
     * Muestra el formulario para crear un nuevo producto.
     */
    public function create()
    {
        return view('products.create');
    }

    /**
     * Crea un nuevo comercio y lo asigna al usuario actual.
     * 
     * @param \App\Models\User $user
     * @return \App\Models\User
     */
    private function ensurecompanyExists($user)
    {
        if (!$user->company_id) {
            try {
                // Si el usuario no tiene un comercio asignado, crear uno nuevo
                $company = company::create([
                    'nit' => substr(time(), -6) . rand(100, 999), // NIT más corto basado en parte del timestamp + número aleatorio
                    'business_name' => $user->name . ' Comercio',
                    'email' => $user->email,
                    'tax_regime' => 'SIMPLE', // Régimen por defecto
                    'password' => Hash::make(Str::random(10)), // Contraseña aleatoria
                ]);
                
                // Verificar que la empresa se haya creado correctamente
                if (!$company || !$company->id) {
                    Log::error('Error: No se pudo crear la empresa');
                    return $user;
                }
                
                // Asignar el nuevo comercio al usuario usando el modelo User
                User::where('id', $user->id)->update(['company_id' => $company->id]);
                
                // Recargar el usuario para obtener el company_id actualizado
                $user = User::find($user->id);
                
                // Para depuración
                Log::info('Usuario actualizado con company_id: ' . $user->company_id);
            } catch (\Exception $e) {
                Log::error('Error al crear empresa: ' . $e->getMessage());
                throw $e;
            }
        }
        
        return $user;
    }

    /**
     * Almacena un nuevo producto en la base de datos.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
        ]);

        $user = Auth::user();
        $user = $this->ensurecompanyExists($user);
        
        $product = new Product($validated);
        $product->company_id = $user->company_id;
        $product->save();
        
        return redirect()->route('products.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Muestra el formulario para editar un producto.
     */
    public function edit(Product $product)
    {
        // Verificar que pertenezca al comercio actual
        if ($product->company_id !== Auth::user()->company_id) {
            abort(403, 'No está autorizado para editar este producto.');
        }
        
        return view('products.edit', compact('product'));
    }

    /**
     * Actualiza un producto en la base de datos.
     */
    public function update(Request $request, Product $product)
    {
        // Verificar que pertenezca al comercio actual
        if ($product->company_id !== Auth::user()->company_id) {
            abort(403, 'No está autorizado para actualizar este producto.');
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
        ]);
        
        // Verificar si el precio ha cambiado para registrar el historial
        $oldPrice = $product->price;
        $newPrice = $validated['price'];
        
        if ($oldPrice != $newPrice) {
            // Registrar el cambio de precio en el historial
            ProductPriceHistory::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'notes' => $request->input('price_change_notes')
            ]);
        }
        
        $product->update($validated);
        
        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Elimina un producto de la base de datos.
     */
    public function destroy(Product $product)
    {
        // Verificar que pertenezca al comercio actual
        if ($product->company_id !== Auth::user()->company_id) {
            abort(403, 'No está autorizado para eliminar este producto.');
        }
        
        $product->delete();
        
        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado exitosamente.');
    }
    
    /**
     * Importa productos desde un archivo CSV.
     */
    public function import(Request $request)
    {
        // Verificar si existe un archivo temporal en la sesión
        $tempFile = session('temp_file');
        if (!$tempFile || !Storage::exists($tempFile)) {
            return redirect()->route('products.index')
                ->with('error', 'No se encontró el archivo a importar.');
        }

        $user = Auth::user();
        
        // Asegurar que el usuario tenga una empresa asignada
        try {
            $user = $this->ensurecompanyExists($user);
            
            if (!$user->company_id) {
                return redirect()->route('products.index')
                    ->with('error', 'No se pudo asignar una empresa al usuario. Por favor, contacte al administrador.');
            }
            
            $companyId = $user->company_id;
            
            // Registrar el ID de la empresa para depuración
            Log::info('Importando productos para la empresa ID: ' . $companyId . ' y usuario ID: ' . $user->id);
            
            $import = new ProductsImport($companyId);
            
            // Leer el archivo CSV
            $path = Storage::path($tempFile);
            $data = array_map('str_getcsv', file($path));
            
            // Procesar los datos
            $import->process($data);
            
            // Eliminar el archivo temporal
            Storage::delete($tempFile);
            
            // Limpiar la variable de sesión
            session()->forget('temp_file');
            
            // Verificar si hay errores
            $errors = $import->getErrors();
            if (!empty($errors)) {
                return redirect()->route('products.index')
                    ->with('error', 'Hubo errores al importar algunos productos: ' . implode(', ', $errors));
            }
            
            return redirect()->route('products.index')
                ->with('success', 'Se importaron ' . $import->getInsertedCount() . ' productos correctamente.');
        } catch (\Exception $e) {
            Log::error('Error en la importación: ' . $e->getMessage());
            return redirect()->route('products.index')
                ->with('error', 'Error al importar productos: ' . $e->getMessage());
        }
    }
    
    /**
     * Vista previa de los productos a importar.
     */
    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);
        
        // Leer el archivo y mostrar una vista previa
        $file = $request->file('file');
        $path = $file->getRealPath();
        $records = array_map('str_getcsv', file($path));
        
        // Eliminar la primera fila (encabezados)
        $headers = array_shift($records);
        
        // Convertir a lowercase para comparación
        $headersLowercase = array_map('strtolower', $headers);
        
        // Verificar encabezados requeridos
        $requiredHeaders = ['name', 'price', 'tax_rate'];
        $missingHeaders = array_diff($requiredHeaders, $headersLowercase);
        
        if (!empty($missingHeaders)) {
            return redirect()->route('products.index')
                ->with('error', 'El archivo no contiene todos los encabezados requeridos: ' . implode(', ', $missingHeaders));
        }
        
        // Guardar el archivo temporalmente
        $tempPath = $file->store('temp');
        
        // Crear array asociativo con los datos
        $data = [];
        foreach ($records as $record) {
            if (count($record) === count($headers)) {
                $row = array_combine($headersLowercase, $record);
                $data[] = $row;
            }
        }
        
        // Guardar la ruta temporal en sesión
        session(['temp_file' => $tempPath]);
        
        // Usar los headers originales para mostrar en la vista
        return view('products.preview-import', compact('data', 'headers'));
    }
    
    /**
     * Muestra el historial de precios de un producto.
     */
    public function priceHistory(Product $product)
    {
        // Verificar que pertenezca al comercio actual o tenga el rol de administrador
        if ($product->company_id !== Auth::user()->company_id && !Gate::allows('role:admin')) {
            abort(403, 'No está autorizado para ver este historial de precios.');
        }
        
        // Cargar el historial de precios con los usuarios
        $priceHistory = $product->priceHistory()->with('user')->orderBy('created_at', 'desc')->paginate(10);
        
        return view('products.price-history', compact('product', 'priceHistory'));
    }

    /**
     * Descarga una plantilla CSV de ejemplo.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="productos_plantilla.csv"',
        ];
        
        $callback = function() {
            $file = fopen('php://output', 'w');
            
            // Escribir encabezados
            fputcsv($file, ['name', 'price', 'tax_rate']);
            
            // Escribir ejemplos
            fputcsv($file, ['Producto Ejemplo 1', '10000', '19']);
            fputcsv($file, ['Producto Ejemplo 2', '15000', '5']);
            fputcsv($file, ['Producto Ejemplo 3', '20000', '0']);
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Exporta los productos a un archivo Excel.
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        $user = $this->ensurecompanyExists($user);
        $companyId = $user->company_id;
        
        $format = $request->input('format', 'xlsx');
        $validFormats = ['xlsx', 'csv'];
        
        if (!in_array($format, $validFormats)) {
            $format = 'xlsx';
        }
        
        $fileName = 'productos_' . date('Y-m-d');
        
        $exporter = new \App\Exports\ProductsExport($companyId);
        
        if ($format === 'csv') {
            return Excel::create($fileName, function($excel) use ($exporter) {
                $excel->sheet('Productos', function($sheet) use ($exporter) {
                    $exporter->handle($sheet);
                });
            })->download('csv');
        } else {
            return Excel::create($fileName, function($excel) use ($exporter) {
                $excel->sheet('Productos', function($sheet) use ($exporter) {
                    $exporter->handle($sheet);
                });
            })->download('xlsx');
        }
    }

    /**
     * Obtiene productos para el formulario de facturas.
     * Esta ruta es accesible para usuarios con el permiso 'sell'.
     */
    public function getProductsForInvoice(Request $request)
    {
        $companyId = Auth::user()->company_id;
        
        if (!$companyId) {
            return response()->json(['error' => 'Usuario sin compañía asignada'], 400);
        }
        
        $products = Product::where('company_id', $companyId)
            ->select(['id', 'name', 'price', 'tax_rate'])
            ->orderBy('name')
            ->get();
            
        return response()->json($products);
    }
} 