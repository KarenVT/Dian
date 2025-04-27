<?php

namespace App\Exports;

use App\Models\Product;
use PHPExcel_Worksheet;

class ProductsExport {
    
    protected $companyId;

    /**
     * Constructor que recibe el ID del comercio
     * 
     * @param int $companyId
     */
    public function __construct($companyId)
    {
        $this->companyId = $companyId;
    }

    /**
     * Método para generar el contenido del Excel
     * 
     * @param PHPExcel_Worksheet $sheet
     */
    public function handle($sheet)
    {
        // Encabezados
        $headers = [
            'ID',
            'Nombre',
            'Precio',
            'Tasa de Impuesto',
            'Fecha de Creación',
            'Fecha de Actualización'
        ];
        
        // Escribir encabezados en la primera fila
        foreach ($headers as $index => $header) {
            $sheet->setCellValueByColumnAndRow($index, 1, $header);
        }
        
        // Obtener los productos
        $products = Product::where('company_id', $this->companyId)->get();
        
        // Escribir datos
        $row = 2;
        foreach ($products as $product) {
            $sheet->setCellValueByColumnAndRow(0, $row, $product->id);
            $sheet->setCellValueByColumnAndRow(1, $row, $product->name);
            $sheet->setCellValueByColumnAndRow(2, $row, $product->price);
            $sheet->setCellValueByColumnAndRow(3, $row, $product->tax_rate);
            $sheet->setCellValueByColumnAndRow(4, $row, $product->created_at->format('d/m/Y H:i:s'));
            $sheet->setCellValueByColumnAndRow(5, $row, $product->updated_at->format('d/m/Y H:i:s'));
            $row++;
        }
    }
} 