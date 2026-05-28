<?php

namespace App\Services\Reportes;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ReporteService
{
    public function ventas(array $filters = []): array
    {
        $query = Sale::with('vendedor', 'cajero', 'caja', 'items.product')
            ->where('estado_venta', 'completada');

        if ($fechaDesde = $filters['fecha_desde'] ?? null) {
            $query->whereDate('created_at', '>=', $fechaDesde);
        }
        if ($fechaHasta = $filters['fecha_hasta'] ?? null) {
            $query->whereDate('created_at', '<=', $fechaHasta);
        }
        if ($vendedorId = $filters['vendedor_id'] ?? null) {
            $query->where('vendedor_id', $vendedorId);
        }
        if ($metodoPago = $filters['metodo_pago'] ?? null) {
            $query->where('metodo_pago', $metodoPago);
        }

        $ventas = $query->orderByDesc('created_at')->paginate(30)->withQueryString();

        static $resumenQuery = null;
        $resumenQuery ??= Sale::where('estado_venta', 'completada');

        if ($fechaDesde ?? null) $resumenQuery->whereDate('created_at', '>=', $fechaDesde);
        if ($fechaHasta ?? null) $resumenQuery->whereDate('created_at', '<=', $fechaHasta);

        $resumen = (clone $resumenQuery)->select(
            DB::raw('COUNT(*) as total_ventas'),
            DB::raw('COALESCE(SUM(total_final), 0) as monto_total'),
            DB::raw('COALESCE(AVG(total_final), 0) as promedio_venta'),
            DB::raw('COALESCE(SUM(descuento_total), 0) as descuentos_total'),
        )->first();

        $metodosPago = (clone $resumenQuery)
            ->select('metodo_pago', DB::raw('COUNT(*) as total'), DB::raw('SUM(total_final) as monto'))
            ->groupBy('metodo_pago')
            ->get();

        $vendedores = User::role(['vendedor', 'cajero'])->orderBy('name')->get(['id', 'name']);

        return compact('ventas', 'resumen', 'metodosPago', 'vendedores');
    }

    public function inventario(array $filters = []): array
    {
        $query = Product::where('activo', true);

        if ($seccion = $filters['seccion'] ?? null) {
            $query->where('seccion', $seccion);
        }
        if (!empty($filters['controlado'])) {
            $query->where('controlado', true);
        }
        if (!empty($filters['bajo_stock'])) {
            $query->whereRaw('stock_unidades <= stock_minimo_alertas');
        }

        $productos = $query->orderBy('nombre_comercial')->paginate(30)->withQueryString();

        $totales = Product::where('activo', true)
            ->select(
                DB::raw('COUNT(*) as total_productos'),
                DB::raw('COALESCE(SUM(stock_unidades), 0) as total_unidades'),
                DB::raw('COALESCE(SUM(stock_unidades * costo_compra_unidad), 0) as valor_inventario'),
                DB::raw('COUNT(CASE WHEN stock_unidades <= stock_minimo_alertas THEN 1 END) as productos_bajo_stock'),
            )->first();

        $secciones = Product::where('activo', true)
            ->whereNotNull('seccion')
            ->distinct()
            ->pluck('seccion')
            ->sort()
            ->values();

        return compact('productos', 'totales', 'secciones');
    }
}
