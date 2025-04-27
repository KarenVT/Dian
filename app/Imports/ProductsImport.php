<?php

namespace App\Imports;

use App\Models\Product;
use PHPExcel_Worksheet;
use Illuminate\Support\Facades\Log;

class ProductsImport
{
    protected $companyId;
    protected $inserted = 0;
    protected $errors = [];
    protected $duplicates = [];

    public function __construct($companyId)
    {
        $this->companyId = $companyId;
        // Registrar el ID de la empresa recibido en el constructor
        Log::info('ProductsImport iniciado con company_id: ' . $this->companyId);
    }

    /**
     * Procesa los datos del archivo importado
     * 
     * @param array $data
     * @return void
     */
    public function process($data)
    {
        // Validar que companyId no sea nulo
        if (!$this->companyId) {
            $this->errors[] = 'No se ha asignado una empresa al usuario actual. Por favor, contacte al administrador.';
            return;
        }

        // Registrar que estamos procesando con un company_id válido
        Log::info('Procesando importación con company_id: ' . $this->companyId);

        // Ignora la primera fila (encabezados)
        $headers = array_shift($data);
        
        // Convertir encabezados a minúsculas para comparación
        $headers = array_map('strtolower', $headers);
        
        // Verifica que existan las columnas requeridas
        if (!in_array('name', $headers) || !in_array('price', $headers) || !in_array('tax_rate', $headers)) {
            $this->errors[] = 'El archivo debe contener las columnas: name, price, tax_rate';
            return;
        }
        
        // Procesa cada fila
        foreach ($data as $row) {
            if (count($row) !== count($headers)) {
                continue; // Salta filas incompletas
            }
            
            // Combina headers y valores
            $rowData = array_combine($headers, $row);
            
            // Validación básica
            if (empty($rowData['name']) || !is_numeric($rowData['price']) || !is_numeric($rowData['tax_rate'])) {
                continue;
            }
            
            // Crear el producto
            try {
                // Verificar si ya existe un producto con el mismo nombre
                $existingProduct = Product::where('company_id', $this->companyId)
                    ->where('name', $rowData['name'])
                    ->first();

                if ($existingProduct) {
                    $this->duplicates[] = $rowData['name'];
                    continue;
                }

                // Crear producto usando el constructor y asegurando que el company_id se establezca correctamente
                $product = new Product();
                $product->name = $rowData['name'];
                $product->price = (float) $rowData['price'];
                $product->tax_rate = (float) $rowData['tax_rate'];
                $product->company_id = $this->companyId;
                
                // Registrar el producto antes de guardar para depuración
                Log::info('Guardando producto: ' . json_encode([
                    'name' => $product->name,
                    'price' => $product->price,
                    'tax_rate' => $product->tax_rate,
                    'company_id' => $product->company_id
                ]));
                
                $product->save();
                
                $this->inserted++;
            } catch (\Exception $e) {
                $this->errors[] = "Error al importar fila: " . implode(', ', $row) . ". Error: " . $e->getMessage();
                // Registrar error detallado
                Log::error('Error guardando producto: ' . $e->getMessage());
            }
        }
    }

    /**
     * Obtiene la cantidad de registros insertados
     */
    public function getInsertedCount(): int
    {
        return $this->inserted;
    }
    
    /**
     * Obtiene los errores durante la importación
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Obtiene los productos duplicados que se encontraron durante la importación
     */
    public function getDuplicates(): array
    {
        return $this->duplicates;
    }
} 