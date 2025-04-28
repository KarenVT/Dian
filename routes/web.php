<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PublicInvoiceController;
use App\Http\Controllers\CompanyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Rutas de perfil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Compañía (datos del comerciante)
    Route::get('/company', [CompanyController::class, 'index'])->name('companies.index');
    Route::get('/company/edit', [CompanyController::class, 'edit'])->name('companies.edit');
    Route::put('/company', [CompanyController::class, 'update'])->name('companies.update');
    
    // Ruta para el perfil del comercio
    Route::get('/settings/profile', function () {
        return view('settings.profile');
    })->name('settings.profile');
    
    // Rutas temporales para las funcionalidades mencionadas en el layout
    // Estas rutas serán reemplazadas por controladores reales en el futuro
    
    // Productos
    Route::middleware('can:manage_products')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::post('/products/import', [ProductController::class, 'import'])->name('products.import');
        Route::post('/products/preview-import', [ProductController::class, 'previewImport'])->name('products.preview-import');
        Route::get('/products/download-template', [ProductController::class, 'downloadTemplate'])->name('products.download-template');
        Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
        Route::get('/products/{product}/price-history', [ProductController::class, 'priceHistory'])->name('products.price-history');
    });
    
    // Ruta especial para obtener productos en el formulario de facturas
    Route::get('/obtener-productos-para-factura', [ProductController::class, 'getProductsForInvoice'])->name('products.for-invoice');
    
    // Ruta para generar facturas desde el formulario web
    Route::post('/generar-factura', [InvoiceController::class, 'generateInvoice'])->name('invoices.generate');
    
    // Clientes
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');
    
    // Facturas
    Route::middleware('can:view_invoice')->group(function () {
        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('/invoices/search', [InvoiceController::class, 'search'])->name('invoices.search');
        Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::put('/invoices/{invoice}/resend', [InvoiceController::class, 'resend'])->name('invoices.resend');
    });
    
    // Reportes
    Route::get('/reports', function () {
        return view('reports.index');
    })->middleware('can:report')->name('reports.index');
    
    // Usuarios
    Route::middleware('role:admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/{user}/update-role', [UserController::class, 'updateRole'])->name('users.update-role');
        Route::post('/users/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
    });
});

// Rutas públicas para consulta de facturas (accesibles sin autenticación)
Route::get('/facturas/{token}', [PublicInvoiceController::class, 'show'])->name('public.invoices.show');
Route::get('/facturas/{token}/pdf', [PublicInvoiceController::class, 'downloadPdf'])->name('public.invoices.pdf');

// Rutas para DIAN
Route::middleware(['auth'])->prefix('dian')->name('dian.')->group(function () {
    Route::post('/invoices/{invoice}/send', [App\Http\Controllers\DianController::class, 'sendInvoice'])->name('send');
    Route::get('/invoices/{invoice}/status', [App\Http\Controllers\DianController::class, 'checkStatus'])->name('check-status');
    Route::post('/invoices/batch', [App\Http\Controllers\DianController::class, 'batchProcess'])->name('batch-process');
});

// Rutas para la demostración de DIAN
Route::middleware(['auth'])->prefix('dian-demo')->name('dian-demo.')->group(function () {
    Route::get('/', [App\Http\Controllers\DianDemoController::class, 'index'])->name('index');
    Route::get('/generate', [App\Http\Controllers\DianDemoController::class, 'showGenerateForm'])->name('generate');
    Route::post('/generate', [App\Http\Controllers\DianDemoController::class, 'generateInvoice'])->name('store');
    Route::get('/invoices/{invoice}', [App\Http\Controllers\DianDemoController::class, 'show'])->name('show');
    Route::post('/invoices/{invoice}/send', [App\Http\Controllers\DianDemoController::class, 'sendToDian'])->name('send');
    Route::get('/invoices/{invoice}/status', [App\Http\Controllers\DianDemoController::class, 'checkStatus'])->name('check-status');
    Route::post('/process-pending', [App\Http\Controllers\DianDemoController::class, 'processPending'])->name('process-pending');
});

require __DIR__.'/auth.php';
