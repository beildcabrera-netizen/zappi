<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Services\Inventario\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class CompraController extends Controller
{
    public function __construct(
        protected StockService $stockService
    ) {
        $this->middleware('role:administrador')->only(['destroy', 'recepcionar']);
    }

    public function index(Request $request)
    {
        $query = Purchase::with('supplier', 'user');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_orden', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn($s) => $s->where('nombre', 'like', "%{$search}%"));
            });
        }

        if ($estado = $request->get('estado')) {
            $query->where('estado', $estado);
        }

        $compras = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Compras/Index', [
            'compras' => $compras,
            'filters' => $request->only(['search', 'estado']),
        ]);
    }

    public function create()
    {
        $proveedores = Supplier::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $productos = Product::where('activo', true)
            ->orderBy('nombre_comercial')
            ->select(
                'id', 'codigo_interno', 'nombre_comercial', 'presentacion_entrada',
                'unidades_por_blister', 'blisters_por_caja',
                'costo_compra_unidad', 'seccion', 'estante'
            )
            ->get();

        return Inertia::render('Compras/Create', [
            'proveedores' => $proveedores,
            'productos' => $productos,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'fecha_orden' => 'required|date',
            'observaciones' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.nombre_producto_temp' => 'nullable|string|max:255',
            'items.*.presentacion_comprada' => 'required|string',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.costo_unitario' => 'required|numeric|min:0',
            'items.*.costo_unidad_base' => 'nullable|numeric|min:0',
            'items.*.lote' => 'nullable|string|max:100',
            'items.*.fecha_vencimiento' => 'nullable|date',
            'items.*.estante_destino' => 'nullable|string|max:50',
            'items.*.seccion_destino' => 'nullable|string|max:50',
        ]);

        $compra = DB::transaction(function () use ($validated) {
            $montoTotal = collect($validated['items'])->sum(fn($item) => $item['cantidad'] * $item['costo_unitario']);

            $compra = Purchase::create([
                'supplier_id' => $validated['supplier_id'],
                'fecha_orden' => $validated['fecha_orden'],
                'estado' => 'pendiente',
                'monto_total' => $montoTotal,
                'observaciones' => $validated['observaciones'] ?? null,
                'user_id' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                PurchaseItem::create([
                    'purchase_id' => $compra->id,
                    'product_id' => $item['product_id'] ?? null,
                    'nombre_producto_temp' => $item['nombre_producto_temp'] ?? null,
                    'presentacion_comprada' => $item['presentacion_comprada'],
                    'cantidad' => $item['cantidad'],
                    'costo_unitario' => $item['costo_unitario'],
                    'costo_unidad_base' => $item['costo_unidad_base'] ?? null,
                    'lote' => $item['lote'] ?? null,
                    'fecha_vencimiento' => $item['fecha_vencimiento'] ?? null,
                    'estante_destino' => $item['estante_destino'] ?? null,
                    'seccion_destino' => $item['seccion_destino'] ?? null,
                    'recibido' => false,
                    'cantidad_recibida' => 0,
                ]);
            }

            return $compra;
        });

        return Redirect::route('compras.show', $compra)->with('success', 'Compra registrada correctamente.');
    }

    public function show(Purchase $compra)
    {
        $compra->loadMissing(['supplier', 'user', 'items.product']);

        return Inertia::render('Compras/Show', [
            'compra' => $compra,
        ]);
    }

    public function edit(Purchase $compra)
    {
        if ($compra->estado !== 'pendiente') {
            return Redirect::route('compras.show', $compra)->with('error', 'No se puede editar una compra que no está pendiente.');
        }

        $compra->loadMissing('items');
        $proveedores = Supplier::where('activo', true)->orderBy('nombre')->get();
        $productos = Product::where('activo', true)
            ->orderBy('nombre_comercial')
            ->select(
                'id', 'codigo_interno', 'nombre_comercial', 'presentacion_entrada',
                'unidades_por_blister', 'blisters_por_caja',
                'costo_compra_unidad', 'seccion', 'estante'
            )
            ->get();

        return Inertia::render('Compras/Edit', [
            'compra' => $compra,
            'proveedores' => $proveedores,
            'productos' => $productos,
        ]);
    }

    public function update(Request $request, Purchase $compra): RedirectResponse
    {
        if ($compra->estado !== 'pendiente') {
            return Redirect::route('compras.show', $compra)->with('error', 'No se puede modificar una compra que no está pendiente.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'fecha_orden' => 'required|date',
            'observaciones' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:purchase_items,id',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.nombre_producto_temp' => 'nullable|string|max:255',
            'items.*.presentacion_comprada' => 'required|string',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.costo_unitario' => 'required|numeric|min:0',
            'items.*.costo_unidad_base' => 'nullable|numeric|min:0',
            'items.*.lote' => 'nullable|string|max:100',
            'items.*.fecha_vencimiento' => 'nullable|date',
            'items.*.estante_destino' => 'nullable|string|max:50',
            'items.*.seccion_destino' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($compra, $validated) {
            $montoTotal = collect($validated['items'])->sum(fn($item) => $item['cantidad'] * $item['costo_unitario']);

            $compra->update([
                'supplier_id' => $validated['supplier_id'],
                'fecha_orden' => $validated['fecha_orden'],
                'monto_total' => $montoTotal,
                'observaciones' => $validated['observaciones'] ?? null,
            ]);

            $existingIds = $compra->items()->pluck('id')->toArray();
            $incomingIds = collect($validated['items'])->pluck('id')->filter()->toArray();
            $toDelete = array_diff($existingIds, $incomingIds);
            if (!empty($toDelete)) {
                PurchaseItem::whereIn('id', $toDelete)->delete();
            }

            foreach ($validated['items'] as $item) {
                $itemData = [
                    'product_id' => $item['product_id'] ?? null,
                    'nombre_producto_temp' => $item['nombre_producto_temp'] ?? null,
                    'presentacion_comprada' => $item['presentacion_comprada'],
                    'cantidad' => $item['cantidad'],
                    'costo_unitario' => $item['costo_unitario'],
                    'costo_unidad_base' => $item['costo_unidad_base'] ?? null,
                    'lote' => $item['lote'] ?? null,
                    'fecha_vencimiento' => $item['fecha_vencimiento'] ?? null,
                    'estante_destino' => $item['estante_destino'] ?? null,
                    'seccion_destino' => $item['seccion_destino'] ?? null,
                ];

                if (isset($item['id']) && in_array($item['id'], $existingIds)) {
                    PurchaseItem::where('id', $item['id'])->update($itemData);
                } else {
                    $compra->items()->create(array_merge($itemData, [
                        'recibido' => false,
                        'cantidad_recibida' => 0,
                    ]));
                }
            }
        });

        return Redirect::route('compras.show', $compra)->with('success', 'Compra actualizada correctamente.');
    }

    public function destroy(Purchase $compra): RedirectResponse
    {
        if ($compra->estado === 'recibida') {
            return Redirect::back()->with('error', 'No se puede eliminar una compra ya recibida.');
        }

        $compra->items()->delete();
        $compra->delete();

        return Redirect::route('compras.index')->with('success', 'Compra eliminada correctamente.');
    }

    public function recepcionar(Purchase $compra, Request $request): RedirectResponse
    {
        if ($compra->estado === 'recibida') {
            return Redirect::back()->with('error', 'Esta compra ya fue recepcionada.');
        }

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:purchase_items,id',
            'items.*.cantidad_recibida' => 'required|numeric|min:0',
            'fecha_recepcion' => 'nullable|date',
        ]);

        DB::transaction(function () use ($compra, $validated) {
            foreach ($validated['items'] as $itemData) {
                $purchaseItem = PurchaseItem::where('id', $itemData['id'])
                    ->where('purchase_id', $compra->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $purchaseItem->update([
                    'recibido' => true,
                    'cantidad_recibida' => $itemData['cantidad_recibida'],
                ]);

                if ($purchaseItem->product_id) {
                    $producto = Product::lockForUpdate()->find($purchaseItem->product_id);

                    if ($producto) {
                        $unidadesBase = $this->stockService->aUnidadesBase(
                            $purchaseItem->presentacion_comprada,
                            $itemData['cantidad_recibida'],
                            $producto
                        );

                        $producto->increment('stock_unidades', $unidadesBase);

                        if ($purchaseItem->costo_unitario > 0 && $purchaseItem->cantidad_recibida > 0) {
                            $stockAnterior = $producto->stock_unidades - $unidadesBase;
                            $costoAnterior = $producto->costo_compra_unidad;
                            $nuevoCosto = $stockAnterior > 0
                                ? (($stockAnterior * $costoAnterior) + ($unidadesBase * $purchaseItem->costo_unitario)) / $producto->stock_unidades
                                : $purchaseItem->costo_unitario;
                            $producto->update(['costo_compra_unidad' => round($nuevoCosto, 2)]);
                        }
                    }
                }
            }

            $compra->update([
                'estado' => 'recibida',
                'fecha_recepcion' => $validated['fecha_recepcion'] ?? now()->toDateString(),
            ]);
        });

        return Redirect::route('compras.show', $compra)->with('success', 'Compra recepcionada y stock actualizado.');
    }
}
