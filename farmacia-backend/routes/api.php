<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoriaController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\CompraController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FinanzaController;
use App\Http\Controllers\Api\LoteController;
use App\Http\Controllers\Api\ProductoController;
use App\Http\Controllers\Api\ProveedorController;
use App\Http\Controllers\Api\ReporteController;
use App\Http\Controllers\Api\SINController;
use App\Http\Controllers\Api\VentaController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware(['auth:api', 'jwt.auth'])->group(function () {

        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::post('auth/refresh', [AuthController::class, 'refresh']);

        Route::get('dashboard', [DashboardController::class, 'resumen']);

        Route::apiResource('categorias', CategoriaController::class);

        Route::get('productos/buscar/{query}', [ProductoController::class, 'buscar']);
        Route::apiResource('productos', ProductoController::class);

        Route::get('lotes/alertas', [LoteController::class, 'alertas']);
        Route::get('lotes', [LoteController::class, 'index']);
        Route::get('lotes/{lote}', [LoteController::class, 'show']);
        Route::put('lotes/{lote}/ajustar-stock', [LoteController::class, 'ajustarStock']);
        Route::post('lotes/{lote}/merma', [LoteController::class, 'registrarMerma']);

        Route::get('clientes/buscar/{query}', [ClienteController::class, 'buscar']);
        Route::apiResource('clientes', ClienteController::class);

        Route::get('proveedores/buscar/{query}', [ProveedorController::class, 'buscar']);
        Route::apiResource('proveedores', ProveedorController::class);

        Route::apiResource('compras', CompraController::class)->only(['index', 'store', 'show']);

        Route::get('ventas/imprimir/{venta}', [VentaController::class, 'imprimir']);
        Route::post('ventas/{venta}/anular', [VentaController::class, 'anular']);
        Route::apiResource('ventas', VentaController::class)->only(['index', 'store', 'show']);

        Route::get('finanzas/resumen', [FinanzaController::class, 'resumen']);

        Route::get('sin/configuracion', [SINController::class, 'configuracion']);
        Route::post('sin/configuracion', [SINController::class, 'guardarConfiguracion']);
        Route::post('sin/solicitar-cuis', [SINController::class, 'solicitarCUIS']);
        Route::post('sin/solicitar-cufd', [SINController::class, 'solicitarCUFD']);
        Route::get('sin/estado', [SINController::class, 'estado']);

        Route::get('reportes/ventas', [ReporteController::class, 'ventas']);
        Route::get('reportes/exportar-csv', [ReporteController::class, 'exportarCSV']);
        Route::get('reportes/resumen-diario', [ReporteController::class, 'resumenDiario']);
    });
});
