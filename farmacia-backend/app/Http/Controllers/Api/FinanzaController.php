<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Http\JsonResponse;

class FinanzaController extends Controller
{
    public function resumen(): JsonResponse
    {
        $ventasHoy = Venta::whereDate('created_at', today())
            ->where('estado', 'completada')
            ->sum('total');

        $ventasMes = Venta::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('estado', 'completada')
            ->sum('total');

        $comprasMes = Compra::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total');

        $debitoFiscal = Venta::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('estado', 'completada')
            ->sum('iva');

        $creditoFiscal = Compra::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('credito_fiscal');

        $ivaNeto = $debitoFiscal - $creditoFiscal;

        $ganancia = $ventasMes - $comprasMes;

        $inversionInventario = Producto::where('activo', true)
            ->get()
            ->sum(fn($p) => $p->stock_total * $p->precio_compra);

        $ventasPorDia = Venta::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->where('estado', 'completada')
            ->selectRaw('DATE(created_at) as fecha, SUM(total) as total')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        $inventarioPorCategoria = Producto::where('activo', true)
            ->with('categoria')
            ->get()
            ->groupBy(fn($p) => $p->categoria?->nombre ?? 'Sin categoría')
            ->map(fn($items, $cat) => [
                'categoria' => $cat,
                'total_productos' => $items->count(),
                'valor_inventario' => $items->sum(fn($p) => $p->stock_total * $p->precio_compra),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'ventas_hoy' => $ventasHoy,
                'ventas_mes' => $ventasMes,
                'compras_mes' => $comprasMes,
                'ganancia' => $ganancia,
                'inversion_inventario' => $inversionInventario,
                'debito_fiscal' => $debitoFiscal,
                'credito_fiscal' => $creditoFiscal,
                'iva_neto' => $ivaNeto,
                'ventas_por_dia' => $ventasPorDia,
                'inventario_por_categoria' => $inventarioPorCategoria,
            ],
        ]);
    }
}
