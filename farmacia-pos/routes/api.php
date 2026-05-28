<?php

use App\Http\Controllers\Api\VentaController as ApiVentaController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::get('ventas', [ApiVentaController::class, 'index']);
    Route::get('ventas/{sale}', [ApiVentaController::class, 'show']);
    Route::post('ventas', [ApiVentaController::class, 'store']);
});
