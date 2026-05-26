<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\InventarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private InventarioService $inventarioService
    ) {}

    public function resumen(): JsonResponse
    {
        $ventasHoy = Venta::whereDate('created_at', today())
            ->where('estado', 'completada')
            ->sum('total');

        $ventasMes = Venta::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('estado', 'completada')
            ->sum('total');

        $totalProductos = Producto::where('activo', true)->count();

        $stockBajo = $this->inventarioService->alertasStockBajo()->count();

        $lotesPorVencer = $this->inventarioService->alertasLotesPorVencer(30)->count();

        $ventas7d = Venta::where('created_at', '>=', now()->subDays(7))
            ->where('estado', 'completada')
            ->selectRaw('DATE(created_at) as fecha, SUM(total) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $topProductos = Venta::where('ventas.estado', 'completada')
            ->join('venta_detalles', 'ventas.id', '=', 'venta_detalles.venta_id')
            ->join('productos', 'venta_detalles.producto_id', '=', 'productos.id')
            ->selectRaw('productos.nombre, SUM(venta_detalles.cantidad) as total_vendido')
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'ventas_hoy' => $ventasHoy,
                'ventas_mes' => $ventasMes,
                'total_productos' => $totalProductos,
                'stock_bajo' => $stockBajo,
                'lotes_por_vencer' => $lotesPorVencer,
                'ventas_7d' => $ventas7d,
                'top_productos' => $topProductos,
                'alertas_stock_bajo' => $this->inventarioService->alertasStockBajo(),
                'alertas_lotes_por_vencer' => $this->inventarioService->alertasLotesPorVencer(30),
            ],
        ]);
    }
}
