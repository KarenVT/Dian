<?php

namespace App\Services;

use App\DTOs\CartDTO;
use App\DTOs\CustomerDTO;
use App\Models\Invoice;
use App\Models\Merchant;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use Spatie\ArrayToXml\ArrayToXml;

class InvoiceService
{
    /**
     * @var string El prefijo para la numeración de facturas
     */
    private string $invoicePrefix = 'SEFT';
    
    /**
     * @var Merchant Instancia del comercio que emite la factura
     */
    private Merchant $merchant;
    
    /**
     * @var DianStorageService Servicio para almacenamiento normativo DIAN
     */
    private DianStorageService $dianStorageService;
    
    /**
     * Constructor del servicio
     *
     * @param Merchant $merchant El comercio que emite la factura
     * @param DianStorageService|null $dianStorageService Servicio de almacenamiento DIAN
     */
    public function __construct(Merchant $merchant, ?DianStorageService $dianStorageService = null)
    {
        $this->merchant = $merchant;
        $this->dianStorageService = $dianStorageService ?? new DianStorageService();
    }
    
    /**
     * Genera una factura electrónica a partir de un carrito y datos del cliente
     *
     * @param CartDTO $cart Los datos del carrito
     * @param CustomerDTO $customer Los datos del cliente
     * @return Invoice La factura generada
     * @throws Exception Si hay errores en el proceso
     */
    public function generateInvoice(CartDTO $cart, CustomerDTO $customer): Invoice
    {
        try {
            // Generar número de factura (autoincremental)
            $invoiceNumber = $this->generateInvoiceNumber();
            
            // Verificar si ya existe una factura con este número para este comercio (idempotencia)
            $existingInvoice = Invoice::where('merchant_id', $this->merchant->id)
                ->where('invoice_number', $invoiceNumber)
                ->first();
                
            if ($existingInvoice) {
                return $existingInvoice;
            }
            
            // Determinar el tipo de documento según el total
            $documentType = Invoice::determineDocumentType($cart->total);
            
            // Generar el XML de la factura
            $xmlArray = $this->buildInvoiceXml($cart, $customer, $invoiceNumber);
            $xml = ArrayToXml::convert($xmlArray, 'Invoice');
            
            // Generar el CUFE
            $cufe = null;
            $signedXmlPath = null;
            
            // Si es una factura formal, necesitamos CUFE y firma
            if ($documentType === 'invoice') {
                $cufe = $this->calculateCufe($cart, $customer, $invoiceNumber);
                $signedXmlPath = $this->signXml($xml, $cufe);
            }
            
            // Generar la fecha de emisión
            $issuedAt = Carbon::now();
            
            // Guardar el XML original y PDF según normativa DIAN
            $xmlContent = $xml;
            $xmlPath = $this->dianStorageService->storeDocument(
                $xmlContent,
                $this->merchant->nit,
                $invoiceNumber,
                $cufe,
                'xml',
                $issuedAt
            );
            
            // Generar PDF (simulado)
            $pdfContent = $this->generatePdfContent($xmlArray, $invoiceNumber);
            $pdfPath = $this->dianStorageService->storeDocument(
                $pdfContent,
                $this->merchant->nit,
                $invoiceNumber,
                $cufe,
                'pdf',
                $issuedAt
            );
            
            // Guardar la factura en la base de datos usando transacción
            return DB::transaction(function () use ($cart, $customer, $invoiceNumber, $documentType, $cufe, $xmlPath, $pdfPath, $signedXmlPath, $issuedAt) {
                return Invoice::create([
                    'merchant_id' => $this->merchant->id,
                    'invoice_number' => $invoiceNumber,
                    'type' => $cart->type,
                    'document_type' => $documentType,
                    'cufe' => $cufe,
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_email' => $customer->email,
                    'subtotal' => $cart->subtotal,
                    'tax' => $cart->tax,
                    'total' => $cart->total,
                    'xml_path' => $xmlPath,
                    'pdf_path' => $pdfPath,
                    'signed_xml_path' => $signedXmlPath,
                    'issued_at' => $issuedAt,
                    'due_date' => $cart->dueDate ? Carbon::parse($cart->dueDate) : null,
                    'notes' => $cart->notes,
                ]);
            });
        } catch (Exception $e) {
            // Limpiar archivos temporales en caso de error
            $this->cleanupTempFiles();
            throw $e;
        }
    }
    
    /**
     * Genera un número de factura único
     *
     * @return string El número de factura generado
     */
    private function generateInvoiceNumber(): string
    {
        // Obtener el último número de factura de este comercio
        $lastInvoice = Invoice::where('merchant_id', $this->merchant->id)
            ->orderBy('id', 'desc')
            ->first();
            
        $lastNumber = 0;
        
        if ($lastInvoice) {
            // Extraer el número de la última factura (eliminar prefijo)
            $lastNumberStr = str_replace($this->invoicePrefix, '', $lastInvoice->invoice_number);
            $lastNumber = (int) $lastNumberStr;
        }
        
        // Incrementar y formatear el nuevo número (8 dígitos)
        $newNumber = $lastNumber + 1;
        $formattedNumber = str_pad($newNumber, 8, '0', STR_PAD_LEFT);
        
        return $this->invoicePrefix . $formattedNumber;
    }
    
    /**
     * Construye la estructura XML de la factura según UBL 2.1
     *
     * @param CartDTO $cart Los datos del carrito
     * @param CustomerDTO $customer Los datos del cliente
     * @param string $invoiceNumber El número de factura
     * @return array La estructura XML como array asociativo
     */
    private function buildInvoiceXml(CartDTO $cart, CustomerDTO $customer, string $invoiceNumber): array
    {
        $now = Carbon::now();
        $issuedDate = $now->format('Y-m-d');
        $issuedTime = $now->format('H:i:s');
        
        return [
            '_attributes' => [
                'xmlns' => 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2',
                'xmlns:cac' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2',
                'xmlns:cbc' => 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2',
            ],
            'cbc:UBLVersionID' => '2.1',
            'cbc:CustomizationID' => 'DIAN 2.1',
            'cbc:ProfileID' => 'DIAN 2.1',
            'cbc:ProfileExecutionID' => '1',
            'cbc:ID' => $invoiceNumber,
            'cbc:UUID' => ['_attributes' => ['schemeID' => '1'], '_value' => ''],
            'cbc:IssueDate' => $issuedDate,
            'cbc:IssueTime' => $issuedTime,
            'cbc:InvoiceTypeCode' => $cart->type,
            'cbc:DocumentCurrencyCode' => 'COP',
            'cac:AccountingSupplierParty' => [
                'cbc:AdditionalAccountID' => '1',
                'cac:Party' => [
                    'cac:PartyIdentification' => [
                        'cbc:ID' => ['_attributes' => ['schemeAgencyID' => '195', 'schemeID' => '0', 'schemeName' => '31'], '_value' => $this->merchant->nit],
                    ],
                    'cac:PartyName' => [
                        'cbc:Name' => $this->merchant->business_name,
                    ],
                    'cac:PartyTaxScheme' => [
                        'cbc:RegistrationName' => $this->merchant->business_name,
                        'cbc:CompanyID' => ['_attributes' => ['schemeAgencyID' => '195', 'schemeID' => '0', 'schemeName' => '31'], '_value' => $this->merchant->nit],
                        'cbc:TaxLevelCode' => $this->merchant->tax_regime,
                        'cac:RegistrationAddress' => [
                            'cbc:ID' => '11001',
                            'cbc:CityName' => 'BOGOTA',
                            'cbc:CountrySubentity' => 'BOGOTA D.C.',
                            'cbc:CountrySubentityCode' => '11',
                            'cac:AddressLine' => [
                                'cbc:Line' => 'Dirección Ficticia 123',
                            ],
                            'cac:Country' => [
                                'cbc:IdentificationCode' => 'CO',
                                'cbc:Name' => ['_attributes' => ['languageID' => 'es'], '_value' => 'Colombia'],
                            ],
                        ],
                        'cac:TaxScheme' => [
                            'cbc:ID' => '01',
                            'cbc:Name' => 'IVA',
                        ],
                    ],
                ],
            ],
            'cac:AccountingCustomerParty' => [
                'cbc:AdditionalAccountID' => '1',
                'cac:Party' => [
                    'cac:PartyIdentification' => [
                        'cbc:ID' => ['_attributes' => ['schemeAgencyID' => '195', 'schemeID' => '0', 'schemeName' => '13'], '_value' => $customer->id],
                    ],
                    'cac:PartyName' => [
                        'cbc:Name' => $customer->name,
                    ],
                    'cac:PartyTaxScheme' => [
                        'cbc:RegistrationName' => $customer->name,
                        'cbc:CompanyID' => ['_attributes' => ['schemeAgencyID' => '195', 'schemeID' => '0', 'schemeName' => '13'], '_value' => $customer->id],
                        'cbc:TaxLevelCode' => 'R-99-PN',
                        'cac:RegistrationAddress' => [
                            'cbc:ID' => '11001',
                            'cbc:CityName' => $customer->city ?? 'BOGOTA',
                            'cbc:CountrySubentity' => $customer->state ?? 'BOGOTA D.C.',
                            'cbc:CountrySubentityCode' => '11',
                            'cac:AddressLine' => [
                                'cbc:Line' => $customer->address ?? 'Dirección Desconocida',
                            ],
                            'cac:Country' => [
                                'cbc:IdentificationCode' => $customer->country ?? 'CO',
                                'cbc:Name' => ['_attributes' => ['languageID' => 'es'], '_value' => 'Colombia'],
                            ],
                        ],
                        'cac:TaxScheme' => [
                            'cbc:ID' => '01',
                            'cbc:Name' => 'IVA',
                        ],
                    ],
                    'cac:PartyLegalEntity' => [
                        'cbc:RegistrationName' => $customer->name,
                        'cbc:CompanyID' => ['_attributes' => ['schemeAgencyID' => '195', 'schemeID' => '0', 'schemeName' => '13'], '_value' => $customer->id],
                    ],
                    'cac:Contact' => [
                        'cbc:ElectronicMail' => $customer->email ?? '',
                    ],
                ],
            ],
            'cac:PaymentMeans' => [
                'cbc:ID' => '1',
                'cbc:PaymentMeansCode' => '10',
                'cbc:PaymentDueDate' => $cart->dueDate ?? $issuedDate,
            ],
            'cac:TaxTotal' => [
                'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'COP'], '_value' => number_format($cart->tax, 2, '.', '')],
                'cac:TaxSubtotal' => [
                    'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'COP'], '_value' => number_format($cart->subtotal, 2, '.', '')],
                    'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'COP'], '_value' => number_format($cart->tax, 2, '.', '')],
                    'cac:TaxCategory' => [
                        'cbc:Percent' => '19.00',
                        'cac:TaxScheme' => [
                            'cbc:ID' => '01',
                            'cbc:Name' => 'IVA',
                        ],
                    ],
                ],
            ],
            'cac:LegalMonetaryTotal' => [
                'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'COP'], '_value' => number_format($cart->subtotal, 2, '.', '')],
                'cbc:TaxExclusiveAmount' => ['_attributes' => ['currencyID' => 'COP'], '_value' => number_format($cart->subtotal, 2, '.', '')],
                'cbc:TaxInclusiveAmount' => ['_attributes' => ['currencyID' => 'COP'], '_value' => number_format($cart->total, 2, '.', '')],
                'cbc:PayableAmount' => ['_attributes' => ['currencyID' => 'COP'], '_value' => number_format($cart->total, 2, '.', '')],
            ],
            'cac:InvoiceLine' => $this->buildInvoiceLines($cart->items),
        ];
    }
    
    /**
     * Construye las líneas de la factura para el XML
     *
     * @param array $items Los items del carrito
     * @return array Las líneas de factura para el XML
     */
    private function buildInvoiceLines(array $items): array
    {
        $lines = [];
        $lineNumber = 1;
        
        foreach ($items as $item) {
            $unitPrice = $item['price'] ?? 0;
            $quantity = $item['quantity'] ?? 1;
            $taxPercentage = $item['tax_percentage'] ?? 19;
            $lineTotal = $unitPrice * $quantity;
            
            $lines[] = [
                'cbc:ID' => $lineNumber,
                'cbc:InvoicedQuantity' => ['_attributes' => ['unitCode' => 'EA'], '_value' => $quantity],
                'cbc:LineExtensionAmount' => ['_attributes' => ['currencyID' => 'COP'], '_value' => number_format($lineTotal, 2, '.', '')],
                'cac:Item' => [
                    'cbc:Description' => $item['description'] ?? 'Producto',
                    'cac:SellersItemIdentification' => [
                        'cbc:ID' => $item['code'] ?? 'SKU-' . $lineNumber,
                    ],
                    'cac:StandardItemIdentification' => [
                        'cbc:ID' => ['_attributes' => ['schemeID' => '999'], '_value' => $item['code'] ?? 'SKU-' . $lineNumber],
                    ],
                ],
                'cac:Price' => [
                    'cbc:PriceAmount' => ['_attributes' => ['currencyID' => 'COP'], '_value' => number_format($unitPrice, 2, '.', '')],
                    'cbc:BaseQuantity' => ['_attributes' => ['unitCode' => 'EA'], '_value' => '1'],
                ],
                'cac:TaxTotal' => [
                    'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'COP'], '_value' => number_format($lineTotal * $taxPercentage / 100, 2, '.', '')],
                    'cac:TaxSubtotal' => [
                        'cbc:TaxableAmount' => ['_attributes' => ['currencyID' => 'COP'], '_value' => number_format($lineTotal, 2, '.', '')],
                        'cbc:TaxAmount' => ['_attributes' => ['currencyID' => 'COP'], '_value' => number_format($lineTotal * $taxPercentage / 100, 2, '.', '')],
                        'cac:TaxCategory' => [
                            'cbc:Percent' => number_format($taxPercentage, 2, '.', ''),
                            'cac:TaxScheme' => [
                                'cbc:ID' => '01',
                                'cbc:Name' => 'IVA',
                            ],
                        ],
                    ],
                ],
            ];
            
            $lineNumber++;
        }
        
        return $lines;
    }
    
    /**
     * Calcula el CUFE (Código Único de Facturación Electrónica)
     *
     * @param CartDTO $cart Los datos del carrito
     * @param CustomerDTO $customer Los datos del cliente
     * @param string $invoiceNumber El número de factura
     * @return string El CUFE calculado
     */
    private function calculateCufe(CartDTO $cart, CustomerDTO $customer, string $invoiceNumber): string
    {
        // Datos para el CUFE según DIAN
        $now = Carbon::now();
        $issuedDate = $now->format('Y-m-d');
        $issuedTime = $now->format('H:i:s');
        
        // Construir la cadena para el CUFE
        $cufeString = implode('', [
            $invoiceNumber,
            $issuedDate,
            $issuedTime,
            number_format($cart->total, 2, '.', ''),
            '01', // Código de impuesto (IVA)
            number_format($cart->tax, 2, '.', ''),
            $this->merchant->nit,
            $customer->id,
            '1', // Tipo de operación
            'COP', // Moneda
        ]);
        
        // Calcular el hash SHA-384
        return hash('sha384', $cufeString);
    }
    
    /**
     * Firma el XML con el certificado digital del comercio
     *
     * @param string $xml El XML a firmar
     * @param string $cufe El CUFE calculado para esta factura
     * @return string La ruta al XML firmado
     * @throws Exception Si hay problemas con la firma
     */
    private function signXml(string $xml, string $cufe): string
    {
        // Comprobar que el comercio tenga un certificado
        if (empty($this->merchant->certificate_path) || !Storage::exists($this->merchant->certificate_path)) {
            throw new Exception("No se encontró el certificado digital del comercio");
        }
        
        // Cargar el certificado desde storage
        $certPath = Storage::path($this->merchant->certificate_path);
        
        // Actualizar el CUFE en el XML
        $xmlDoc = new \DOMDocument();
        $xmlDoc->loadXML($xml);
        
        $uuidNode = $xmlDoc->getElementsByTagNameNS('urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2', 'UUID')->item(0);
        if ($uuidNode) {
            $uuidNode->nodeValue = $cufe;
        }
        
        // Crear el objeto para firmar
        $objDSig = new XMLSecurityDSig();
        
        // Preparar el certificado
        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        
        // Configurar la clave RSA desde el certificado P12
        // En una implementación real se debe proporcionar la contraseña
        // Aquí se usa una contraseña de ejemplo, en producción debe manejarse de forma segura
        $objKey->loadKey($certPath, true, true, ['password' => 'password123']);
        
        // Firmar el documento
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);
        $objDSig->addReference(
            $xmlDoc, 
            XMLSecurityDSig::SHA256,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
            ['force_uri' => true]
        );
        
        $objDSig->sign($objKey);
        $objDSig->add509Cert(file_get_contents($certPath));
        $objDSig->appendSignature($xmlDoc->documentElement);
        
        // Guardar el XML firmado
        $signedXml = $xmlDoc->saveXML();
        
        // Almacenar según normativa DIAN
        $issuedAt = Carbon::now();
        $signedXmlPath = $this->dianStorageService->storeDocument(
            $signedXml, 
            $this->merchant->nit, 
            $this->merchant->id . '_' . Carbon::now()->timestamp,
            $cufe, 
            'signed.xml',
            $issuedAt
        );
        
        return $signedXmlPath;
    }
    
    /**
     * Genera el contenido del PDF (simulado)
     *
     * @param array $data Los datos de la factura
     * @param string $invoiceNumber El número de factura
     * @return string Contenido del PDF generado
     */
    private function generatePdfContent(array $data, string $invoiceNumber): string
    {
        // Simulamos la generación del PDF, en un caso real se usaría una librería como DOMPDF
        return "PDF simulado para la factura: $invoiceNumber";
    }
    
    /**
     * Limpia archivos temporales en caso de error
     */
    private function cleanupTempFiles(): void
    {
        // Implementar limpieza de archivos temporales si es necesario
    }
} 