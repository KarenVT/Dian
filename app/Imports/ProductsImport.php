<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class ProductsImport implements ToCollection, WithHeadingRow, WithValidation
{
    protected $merchantId;
    protected $duplicates = [];
    protected $inserted = 0;

    public function __construct($merchantId)
    {
        $this->merchantId = $merchantId;
    }

    /**
     * @param Collection $rows
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Verificar si ya existe un producto con el mismo SKU para este merchant
            $exists = Product::where('merchant_id', $this->merchantId)
                ->where('sku', $row['sku'])
                ->exists();

            if ($exists) {
                // Si existe, agregarlo a la lista de duplicados
                $this->duplicates[] = $row['sku'];
                continue;
            }

            // Crear el producto
            Product::create([
                'merchant_id' => $this->merchantId,
                'sku' => $row['sku'],
                'name' => $row['name'],
                'price' => $row['price'],
                'tax_rate' => $row['tax_rate'],
                'dian_code' => $row['dian_code'] ?? null,
            ]);

            $this->inserted++;
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
     * Obtiene los SKUs duplicados
     */
    public function getDuplicates(): array
    {
        return $this->duplicates;
    }

    /**
     * Reglas de validación
     */
    public function rules(): array
    {
        return [
            '*.sku' => ['required', 'string', 'max:255'],
            '*.name' => ['required', 'string', 'max:255'],
            '*.price' => ['required', 'numeric', 'min:0'],
            '*.tax_rate' => ['required', 'numeric', 'min:0'],
            '*.dian_code' => ['nullable', 'string', 'max:255'],
        ];
    }
} 