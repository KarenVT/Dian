<?php

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas para merchants
Route::post('/merchants', [MerchantController::class, 'store']);
Route::get('/merchants/{merchant}', [MerchantController::class, 'show'])->middleware('auth:sanctum');
Route::put('/merchants/{merchant}', [MerchantController::class, 'update'])->middleware('auth:sanctum');

// Rutas para facturas
Route::middleware('auth:sanctum')->group(function () {
    // Ruta para descargar el PDF de una factura
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf']);
    
    // Otras rutas de facturas (implementación futura)
    Route::get('/invoices', [InvoiceController::class, 'index']);
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
    
    // Rutas para productos
    Route::apiResource('products', ProductController::class);
    Route::post('/products/import', [ProductController::class, 'import']);
    
    // Rutas para reportes
    Route::get('/reports/sales', [ReportController::class, 'sales']);
});
