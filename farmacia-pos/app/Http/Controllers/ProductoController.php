<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_comercial', 'like', "%{$search}%")
                    ->orWhere('nombre_generico', 'like', "%{$search}%")
                    ->orWhere('codigo_barras', 'like', "%{$search}%")
                    ->orWhere('codigo_interno', 'like', "%{$search}%")
                    ->orWhere('principio_activo', 'like', "%{$search}%");
            });
        }

        if ($seccion = $request->get('seccion')) {
            $query->where('seccion', $seccion);
        }

        if ($request->boolean('controlado')) {
            $query->where('controlado', true);
        }

        if ($request->boolean('stock_bajo')) {
            $query->whereRaw('stock_unidades <= stock_minimo_alertas');
        }

        $productos = $query->orderBy('nombre_comercial')
            ->paginate(20)
            ->withQueryString();

        $secciones = Product::whereNotNull('seccion')
            ->distinct()
            ->pluck('seccion')
            ->sort()
            ->values();

        return Inertia::render('Productos/Index', [
            'productos' => $productos,
            'secciones' => $secciones,
            'filters' => $request->only(['search', 'seccion', 'controlado', 'stock_bajo']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Productos/Create');
    }

    public function store(StoreProductoRequest $request): RedirectResponse
    {
        Product::create($request->validated());

        return Redirect::route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    public function show(Product $producto)
    {
        return Inertia::render('Productos/Show', [
            'producto' => $producto,
        ]);
    }

    public function edit(Product $producto)
    {
        return Inertia::render('Productos/Edit', [
            'producto' => $producto,
        ]);
    }

    public function update(UpdateProductoRequest $request, Product $producto): RedirectResponse
    {
        $producto->update($request->validated());

        return Redirect::route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Product $producto): RedirectResponse
    {
        $producto->delete();

        return Redirect::route('productos.index')->with('success', 'Producto eliminado correctamente.');
    }

    public function buscar(Request $request)
    {
        $search = $request->get('q', '');

        if (strlen($search) < 2) {
            return response()->json([]);
        }

        $productos = Product::where('activo', true)
            ->where('stock_unidades', '>', 0)
            ->where(function ($query) use ($search) {
                $query->where('codigo_barras', 'like', "%{$search}%")
                    ->orWhere('codigo_interno', 'like', "%{$search}%")
                    ->orWhere('nombre_comercial', 'like', "%{$search}%")
                    ->orWhere('nombre_generico', 'like', "%{$search}%");
            })
            ->select(
                'id', 'codigo_barras', 'codigo_interno', 'nombre_comercial',
                'nombre_generico', 'precio_venta_unidad', 'precio_venta_blister',
                'precio_venta_caja', 'stock_unidades', 'unidades_por_blister',
                'blisters_por_caja', 'fraccionamiento_habilitado', 'controlado',
                'refrigerado', 'presentacion_entrada', 'laboratorio'
            )
            ->orderBy('nombre_comercial')
            ->limit(20)
            ->get();

        return response()->json($productos);
    }
}
