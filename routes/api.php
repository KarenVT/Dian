<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\InvoiceController;
use App\Http\Controllers\API\companyController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ReportController;
use App\Http\Controllers\API\PosController;
use App\Http\Controllers\API\DianController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas de autenticación
Route::post('/auth/token', [AuthController::class, 'token']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user()->load('roles', 'permissions');
    });
    
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

// Rutas públicas
Route::post('/companies', [companyController::class, 'store']);

// Rutas protegidas por Sanctum y habilidades
Route::middleware('auth:sanctum')->group(function () {
    // Rutas de companies
    Route::get('/companies/{company}', [companyController::class, 'show'])
        ->middleware('ability:view_invoice,manage_companies');
    Route::put('/companies/{company}', [companyController::class, 'update'])
        ->middleware('ability:manage_companies');
    Route::put('/companies/{company}/certificate', [companyController::class, 'updateCertificate'])
        ->middleware('ability:manage_companies');
    
    // Rutas para facturas
    Route::get('/invoices', [InvoiceController::class, 'index'])
        ->middleware('ability:view_invoice,view_invoice_own');
    Route::get('/invoices/search', [InvoiceController::class, 'search'])
        ->middleware('ability:view_invoice,view_invoice_own');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
        ->middleware('ability:view_invoice,view_invoice_own');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])
        ->middleware('ability:view_invoice,view_invoice_own');
    Route::put('/invoices/{invoice}/resend', [InvoiceController::class, 'resend'])
        ->middleware('ability:view_invoice,view_invoice_own');
    Route::post('/invoices', [InvoiceController::class, 'store'])
        ->middleware('ability:sell');
    
    // Rutas para integración con DIAN
    Route::prefix('dian')->group(function () {
        Route::post('/invoices/{invoice}/send', [DianController::class, 'sendInvoice'])
            ->middleware('ability:manage_dian');
        Route::get('/invoices/{invoice}/status', [DianController::class, 'checkStatus'])
            ->middleware('ability:manage_dian,view_invoice');
        Route::post('/process-pending', [DianController::class, 'processPendingInvoices'])
            ->middleware('ability:manage_dian');
    });
    
    // Rutas para venta rápida (POS)
    Route::post('/tickets', [PosController::class, 'store'])
        ->middleware('ability:sell');
    
    // Rutas para productos
    Route::apiResource('products', ProductController::class)
        ->middleware('ability:manage_products,sell');
    Route::post('/products/import', [ProductController::class, 'import'])
        ->middleware('ability:manage_products');
    
    // Rutas para clientes
    Route::apiResource('customers', \App\Http\Controllers\API\CustomerController::class)
        ->middleware('ability:sell');
    
    // Rutas para reportes - IMPORTANTE: Asegurarse de que está bien protegida
    Route::get('/reports/sales', [ReportController::class, 'sales'])
        ->middleware('ability:view_reports_basic');
    Route::get('/reports/export', [ReportController::class, 'export'])
        ->middleware('ability:view_reports_basic');
});
