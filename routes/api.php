<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\InvoiceController;
use App\Http\Controllers\API\MerchantController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ReportController;
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
        return $request->user();
    });
    
    Route::post('/auth/logout', [AuthController::class, 'logout']);
});

// Rutas públicas
Route::post('/merchants', [MerchantController::class, 'store']);

// Rutas protegidas por Sanctum y habilidades
Route::middleware('auth:sanctum')->group(function () {
    // Rutas de Merchants
    Route::get('/merchants/{merchant}', [MerchantController::class, 'show'])
        ->middleware('ability:view_invoice,manage_merchants');
    Route::put('/merchants/{merchant}', [MerchantController::class, 'update'])
        ->middleware('ability:manage_merchants');
    
    // Rutas para facturas
    Route::get('/invoices', [InvoiceController::class, 'index'])
        ->middleware('ability:view_invoice,view_invoice_own');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])
        ->middleware('ability:view_invoice,view_invoice_own');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])
        ->middleware('ability:view_invoice,view_invoice_own');
    
    // Rutas para productos
    Route::apiResource('products', ProductController::class)
        ->middleware('ability:sell,manage_products');
    Route::post('/products/import', [ProductController::class, 'import'])
        ->middleware('ability:manage_products');
    
    // Rutas para reportes
    Route::get('/reports/sales', [ReportController::class, 'sales'])
        ->middleware('ability:report');
    
    // Ejemplo comentado de cómo aplicar el middleware en otra ruta
    // Route::get('/example', [ExampleController::class, 'index'])
    //    ->middleware('ability:sell');
});
