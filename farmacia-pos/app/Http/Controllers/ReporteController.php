<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    public function ventas(Request $request)
    {
        $validated = $request->validate([
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
            'vendedor_id' => 'nullable|exists:users,id',
            'metodo_pago' => 'nullable|string',
        ]);

        $query = Sale::with('vendedor', 'cajero', 'caja', 'items.product')
            ->where('estado_venta', 'completada');

        if ($fechaDesde = $validated['fecha_desde'] ?? null) {
            $query->whereDate('created_at', '>=', $fechaDesde);
        }

        if ($fechaHasta = $validated['fecha_hasta'] ?? null) {
            $query->whereDate('created_at', '<=', $fechaHasta);
        }

        if ($vendedorId = $validated['vendedor_id'] ?? null) {
            $query->where('vendedor_id', $vendedorId);
        }

        if ($metodoPago = $validated['metodo_pago'] ?? null) {
            $query->where('metodo_pago', $metodoPago);
        }

        $ventas = $query->orderBy('created_at', 'desc')
            ->paginate(30)
            ->withQueryString();

        $resumen = Sale::where('estado_venta', 'completada')
            ->when($fechaDesde ?? null, fn($q) => $q->whereDate('created_at', '>=', $fechaDesde))
            ->when($fechaHasta ?? null, fn($q) => $q->whereDate('created_at', '<=', $fechaHasta))
            ->select(
                DB::raw('COUNT(*) as total_ventas'),
                DB::raw('COALESCE(SUM(total_final), 0) as monto_total'),
                DB::raw('COALESCE(AVG(total_final), 0) as promedio_venta'),
                DB::raw('COALESCE(SUM(descuento_total), 0) as descuentos_total'),
            )
            ->first();

        $metodosPago = Sale::where('estado_venta', 'completada')
            ->when($fechaDesde ?? null, fn($q) => $q->whereDate('created_at', '>=', $fechaDesde))
            ->when($fechaHasta ?? null, fn($q) => $q->whereDate('created_at', '<=', $fechaHasta))
            ->select('metodo_pago', DB::raw('COUNT(*) as total'), DB::raw('SUM(total_final) as monto'))
            ->groupBy('metodo_pago')
            ->get();

        $vendedores = User::where('rol', '!=', 'administrador')->orderBy('nombre')->get(['id', 'nombre']);

        return Inertia::render('Reportes/Ventas', [
            'ventas' => $ventas,
            'resumen' => $resumen,
            'metodosPago' => $metodosPago,
            'vendedores' => $vendedores,
            'filters' => $request->only(['fecha_desde', 'fecha_hasta', 'vendedor_id', 'metodo_pago']),
        ]);
    }

    public function inventario(Request $request)
    {
        $validated = $request->validate([
            'seccion' => 'nullable|string',
            'controlado' => 'nullable|boolean',
            'bajo_stock' => 'nullable|boolean',
            'proximo_vencer' => 'nullable|integer|min:1',
        ]);

        $productos = Product::where('activo', true);

        if ($seccion = $validated['seccion'] ?? null) {
            $productos->where('seccion', $seccion);
        }

        if ($request->boolean('controlado')) {
            $productos->where('controlado', true);
        }

        if ($request->boolean('bajo_stock')) {
            $productos->whereRaw('stock_unidades <= stock_minimo_alertas');
        }

        $listaProductos = $productos->orderBy('nombre_comercial')
            ->paginate(30)
            ->withQueryString();

        $totales = Product::where('activo', true)
            ->select(
                DB::raw('COUNT(*) as total_productos'),
                DB::raw('COALESCE(SUM(stock_unidades), 0) as total_unidades'),
                DB::raw('COALESCE(SUM(stock_unidades * costo_compra_unidad), 0) as valor_inventario'),
                DB::raw('COUNT(CASE WHEN stock_unidades <= stock_minimo_alertas THEN 1 END) as productos_bajo_stock'),
            )
            ->first();

        $secciones = Product::where('activo', true)
            ->whereNotNull('seccion')
            ->distinct()
            ->pluck('seccion')
            ->sort()
            ->values();

        return Inertia::render('Reportes/Inventario', [
            'productos' => $listaProductos,
            'totales' => $totales,
            'secciones' => $secciones,
            'filters' => $request->only(['seccion', 'controlado', 'bajo_stock', 'proximo_vencer']),
        ]);
    }

    public function finanzas(Request $request)
    {
        $validated = $request->validate([
            'fecha_desde' => 'nullable|date',
            'fecha_hasta' => 'nullable|date|after_or_equal:fecha_desde',
        ]);

        $fechaDesde = $validated['fecha_desde'] ?? now()->startOfMonth()->toDateString();
        $fechaHasta = $validated['fecha_hasta'] ?? now()->toDateString();

        $diario = Sale::where('estado_venta', 'completada')
            ->whereDate('created_at', '>=', $fechaDesde)
            ->whereDate('created_at', '<=', $fechaHasta)
            ->select(
                DB::raw('DATE(created_at) as fecha'),
                DB::raw('COUNT(*) as total_ventas'),
                DB::raw('COALESCE(SUM(total_final), 0) as monto_total'),
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('fecha')
            ->get();

        $metodosPago = Sale::where('estado_venta', 'completada')
            ->whereDate('created_at', '>=', $fechaDesde)
            ->whereDate('created_at', '<=', $fechaHasta)
            ->select(
                'metodo_pago',
                DB::raw('COUNT(*) as total'),
                DB::raw('COALESCE(SUM(total_final), 0) as monto'),
            )
            ->groupBy('metodo_pago')
            ->get();

        $resumen = Sale::where('estado_venta', 'completada')
            ->whereDate('created_at', '>=', $fechaDesde)
            ->whereDate('created_at', '<=', $fechaHasta)
            ->select(
                DB::raw('COUNT(*) as total_ventas'),
                DB::raw('COALESCE(SUM(total_final), 0) as monto_total'),
                DB::raw('COALESCE(AVG(total_final), 0) as promedio_venta'),
                DB::raw('COALESCE(SUM(descuento_total), 0) as descuentos_total'),
            )
            ->first();

        $productosMasVendidos = SaleItem::select(
            'product_id',
            DB::raw('SUM(cantidad) as total_vendido'),
            DB::raw('SUM(total_item) as monto_total'),
        )
            ->whereHas('sale', function ($q) use ($fechaDesde, $fechaHasta) {
                $q->where('estado_venta', 'completada')
                    ->whereDate('created_at', '>=', $fechaDesde)
                    ->whereDate('created_at', '<=', $fechaHasta);
            })
            ->groupBy('product_id')
            ->orderByDesc('total_vendido')
            ->limit(10)
            ->with('product:id,nombre_comercial,codigo_interno')
            ->get();

        return Inertia::render('Reportes/Finanzas', [
            'diario' => $diario,
            'metodosPago' => $metodosPago,
            'resumen' => $resumen,
            'productosMasVendidos' => $productosMasVendidos,
            'filters' => compact('fechaDesde', 'fechaHasta'),
        ]);
    }
}
