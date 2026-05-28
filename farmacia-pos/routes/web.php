<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UsuarioController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Caja / Punto de Venta
    Route::get('/caja/venta', [CajaController::class, 'puntoVenta'])->name('caja.venta')->middleware('turno.abierto');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::post('/ventas/enviar-a-caja', [VentaController::class, 'enviarACaja'])->name('ventas.enviar-caja');
    Route::post('/ventas/{venta}/cobrar', [VentaController::class, 'cobrar'])->name('ventas.cobrar')->middleware('permiso.cobro');
    Route::post('/ventas/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular');

    // Turnos
    Route::get('/turno/apertura', [TurnoController::class, 'apertura'])->name('turno.apertura');
    Route::post('/turnos/abrir', [TurnoController::class, 'abrir'])->name('turnos.abrir');
    Route::post('/turnos/cerrar', [TurnoController::class, 'cerrar'])->name('turnos.cerrar');

    // Productos
    Route::resource('productos', ProductoController::class);
    Route::get('/productos/buscar', [ProductoController::class, 'buscar'])->name('productos.buscar');

    // Facturación
    Route::get('/facturas/talonarios', [FacturaController::class, 'talonarios'])->name('facturas.talonarios');
    Route::post('/facturas/talonarios', [FacturaController::class, 'storeTalonario'])->name('facturas.talonarios.store');
    Route::get('/facturas/registro-ventas', [FacturaController::class, 'registroVentas'])->name('facturas.registro-ventas');

    // Compras
    Route::resource('compras', CompraController::class);
    Route::post('/compras/{compra}/recepcionar', [CompraController::class, 'recepcionar'])->name('compras.recepcionar');

    // Reportes
    Route::get('/reportes/ventas', [ReporteController::class, 'ventas'])->name('reportes.ventas');
    Route::get('/reportes/inventario', [ReporteController::class, 'inventario'])->name('reportes.inventario');
    Route::get('/reportes/finanzas', [ReporteController::class, 'finanzas'])->name('reportes.finanzas');

    // Configuración
    Route::get('/configuracion', [ConfiguracionController::class, 'edit'])->name('configuracion.edit');
    Route::put('/configuracion', [ConfiguracionController::class, 'update'])->name('configuracion.update');

    // Usuarios
    Route::resource('usuarios', UsuarioController::class)->except(['show']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
