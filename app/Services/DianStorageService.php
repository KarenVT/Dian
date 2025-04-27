<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\company;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DianStorageService
{
    /**
     * Genera la ruta base para almacenamiento de documentos electrónicos según normativa DIAN
     *
     * @param string $companyNit NIT del comercio
     * @param Carbon|null $date Fecha para la estructura de carpetas (default: hoy)
     * @return string Ruta base para almacenamiento
     */
    public function getBasePath(string $companyNit, ?Carbon $date = null): string
    {
        $date = $date ?? Carbon::now();
        
        return sprintf(
            'fev/%s/%s/%s',
            $companyNit,
            $date->format('Y'),
            $date->format('m')
        );
    }
    
    /**
     * Genera el nombre del archivo según formato normativo
     * 
     * @param string $consecutive Número consecutivo de la factura
     * @param string|null $cufe CUFE de la factura (opcional para tickets POS)
     * @param string $extension Extensión del archivo (.xml o .pdf)
     * @return string Nombre del archivo según normativa
     */
    public function getFileName(string $consecutive, ?string $cufe, string $extension): string
    {
        $cufeStr = $cufe ? '_' . $cufe : '';
        return sprintf('FV_%s%s.%s', $consecutive, $cufeStr, $extension);
    }
    
    /**
     * Almacena un documento XML/PDF según la normativa DIAN
     * 
     * @param string $content Contenido del archivo
     * @param string $companyNit NIT del comercio
     * @param string $consecutive Número consecutivo de la factura
     * @param string|null $cufe CUFE de la factura (opcional para tickets POS)
     * @param string $extension Extensión del archivo (.xml o .pdf)
     * @param Carbon|null $date Fecha para la estructura de carpetas (default: hoy)
     * @return string Ruta completa donde se almacenó el archivo
     */
    public function storeDocument(
        string $content, 
        string $companyNit, 
        string $consecutive, 
        ?string $cufe, 
        string $extension,
        ?Carbon $date = null
    ): string {
        $basePath = $this->getBasePath($companyNit, $date);
        $fileName = $this->getFileName($consecutive, $cufe, $extension);
        $fullPath = $basePath . '/' . $fileName;
        
        // Asegurar que la carpeta existe
        Storage::makeDirectory($basePath);
        
        // Almacenar el archivo
        Storage::put($fullPath, $content);
        
        return $fullPath;
    }
    
    /**
     * Almacena XML y PDF de una factura según normativa DIAN
     *
     * @param Invoice $invoice La factura a procesar
     * @return array Las rutas de los archivos almacenados [xml_path, pdf_path, signed_xml_path]
     */
    public function storeInvoiceDocuments(Invoice $invoice): array
    {
        $date = $invoice->issued_at;
        $company = $invoice->company;
        $consecutive = $invoice->invoice_number;
        $cufe = $invoice->cufe;
        
        $paths = [
            'xml_path' => null,
            'pdf_path' => null,
            'signed_xml_path' => null
        ];
        
        // Obtener contenido de los archivos originales
        if ($invoice->xml_path && Storage::exists($invoice->xml_path)) {
            $xmlContent = Storage::get($invoice->xml_path);
            $paths['xml_path'] = $this->storeDocument(
                $xmlContent, 
                $company->nit, 
                $consecutive, 
                $cufe, 
                'xml',
                $date
            );
            
            // Borrar el XML original si es diferente a la nueva ubicación
            if ($invoice->xml_path !== $paths['xml_path']) {
                Storage::delete($invoice->xml_path);
            }
        }
        
        if ($invoice->pdf_path && Storage::exists($invoice->pdf_path)) {
            $pdfContent = Storage::get($invoice->pdf_path);
            $paths['pdf_path'] = $this->storeDocument(
                $pdfContent, 
                $company->nit, 
                $consecutive, 
                $cufe, 
                'pdf',
                $date
            );
            
            // Borrar el PDF original si es diferente a la nueva ubicación
            if ($invoice->pdf_path !== $paths['pdf_path']) {
                Storage::delete($invoice->pdf_path);
            }
        }
        
        if ($invoice->signed_xml_path && Storage::exists($invoice->signed_xml_path)) {
            $signedXmlContent = Storage::get($invoice->signed_xml_path);
            $paths['signed_xml_path'] = $this->storeDocument(
                $signedXmlContent, 
                $company->nit, 
                $consecutive, 
                $cufe, 
                'signed.xml',
                $date
            );
            
            // Borrar el XML firmado original si es diferente a la nueva ubicación
            if ($invoice->signed_xml_path !== $paths['signed_xml_path']) {
                Storage::delete($invoice->signed_xml_path);
            }
        }
        
        // Actualizar la factura con las nuevas rutas
        $invoice->update($paths);
        
        return $paths;
    }
    
    /**
     * Verifica archivos con antigüedad mayor a 5 años
     * 
     * @param Carbon|null $referenceDate Fecha de referencia (default: hoy)
     * @return array Array con información de los archivos a eliminar
     */
    public function getExpiredDocuments(?Carbon $referenceDate = null): array
    {
        $referenceDate = $referenceDate ?? Carbon::now();
        $expirationDate = $referenceDate->copy()->subYears(5);
        
        $expiredFiles = [];
        $companyDirectories = Storage::directories('fev');
        
        foreach ($companyDirectories as $companyDir) {
            $companyNit = basename($companyDir);
            $yearDirectories = Storage::directories($companyDir);
            
            foreach ($yearDirectories as $yearDir) {
                $year = basename($yearDir);
                
                // Si el año es anterior al año de expiración, revisar los archivos
                if ((int)$year < $expirationDate->year) {
                    $monthDirectories = Storage::directories($yearDir);
                    
                    foreach ($monthDirectories as $monthDir) {
                        $month = basename($monthDir);
                        
                        // Si estamos en el año de expiración, solo incluir meses anteriores
                        if ((int)$year === $expirationDate->year && (int)$month >= $expirationDate->month) {
                            continue;
                        }
                        
                        // Obtener todos los archivos en este directorio
                        $files = Storage::files($monthDir);
                        
                        foreach ($files as $file) {
                            $fileInfo = [
                                'path' => $file,
                                'companyNit' => $companyNit,
                                'year' => $year,
                                'month' => $month,
                                'filename' => basename($file),
                                'created_at' => Storage::lastModified($file)
                            ];
                            
                            $expiredFiles[] = $fileInfo;
                        }
                    }
                }
            }
        }
        
        return $expiredFiles;
    }
} 