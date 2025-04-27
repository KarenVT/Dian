<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Configuración de Integración con DIAN
    |--------------------------------------------------------------------------
    |
    | Esta configuración es utilizada para la integración con la DIAN (Dirección
    | de Impuestos y Aduanas Nacionales) para la facturación electrónica.
    |
    */

    // URL base del API de DIAN (cambia entre ambientes)
    'api_base_url' => env('DIAN_API_URL', 'https://vpfe-hab.dian.gov.co/WcfDianCustomerServices.svc'),

    // Token de autenticación para el API de DIAN
    'api_token' => env('DIAN_API_TOKEN'),

    // ID del software registrado ante DIAN
    'software_id' => env('DIAN_SOFTWARE_ID'),

    // Pin del software
    'software_pin' => env('DIAN_SOFTWARE_PIN'),

    // Certificado para firma electrónica
    'certificate_path' => env('DIAN_CERTIFICATE_PATH', storage_path('app/certs/dian/certificate.p12')),
    'certificate_password' => env('DIAN_CERTIFICATE_PASSWORD'),

    // Ambiente de DIAN
    // 1: Producción, 2: Pruebas/Habilitación
    'environment' => env('DIAN_ENVIRONMENT', 2),

    // Prefijo para facturas
    'invoice_prefix' => env('DIAN_INVOICE_PREFIX', 'SEFT'),

    // Resolución de facturación vigente
    'resolution_number' => env('DIAN_RESOLUTION_NUMBER'),
    'resolution_date' => env('DIAN_RESOLUTION_DATE'),
    'resolution_start_number' => env('DIAN_RESOLUTION_START_NUMBER', 1),
    'resolution_end_number' => env('DIAN_RESOLUTION_END_NUMBER', 100000),

    // Código de política de firma
    'signature_policy_url' => env('DIAN_SIGNATURE_POLICY_URL', 'https://facturaelectronica.dian.gov.co/politicadefirma/v2/politicadefirmav2.pdf'),
    'signature_policy_hash' => env('DIAN_SIGNATURE_POLICY_HASH', 'dMoMvtcG5aIzgYo0tIsSQeVJBDnUnfSOfBpxXrmor0Y='),
]; 
 