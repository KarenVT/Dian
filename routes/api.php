<?php

use App\Http\Controllers\API\MerchantController;
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
