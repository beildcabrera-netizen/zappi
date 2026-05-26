<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductoRequest;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;

class ProductoController extends Controller
{
    public function index(): JsonResponse
    {
        $productos = Producto::with('categoria', 'lotes')
            ->withSum('lotes as stock_total', 'stock_actual')
            ->orderBy('nombre')
            ->get();

        return response()->json(['success' => true, 'data' => $productos]);
    }

    public function store(ProductoRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (empty($data['ganancia_porcentaje']) && $data['precio_compra'] > 0) {
            $data['ganancia_porcentaje'] = round(
                (($data['precio_venta'] - $data['precio_compra']) / $data['precio_compra']) * 100,
                2
            );
        }

        $producto = Producto::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Producto creado exitosamente.',
            'data' => $producto->load('categoria'),
        ], 201);
    }

    public function show(Producto $producto): JsonResponse
    {
        $producto->load('categoria', 'lotes');

        return response()->json([
            'success' => true,
            'data' => $producto,
        ]);
    }

    public function update(ProductoRequest $request, Producto $producto): JsonResponse
    {
        $data = $request->validated();

        if (empty($data['ganancia_porcentaje']) && ($data['precio_compra'] ?? 0) > 0) {
            $data['ganancia_porcentaje'] = round(
                (($data['precio_venta'] - $data['precio_compra']) / $data['precio_compra']) * 100,
                2
            );
        }

        $producto->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Producto actualizado exitosamente.',
            'data' => $producto->fresh()->load('categoria', 'lotes'),
        ]);
    }

    public function destroy(Producto $producto): JsonResponse
    {
        if ($producto->stock_total > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un producto con stock disponible. Desactívelo en su lugar.',
            ], 409);
        }

        $producto->update(['activo' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Producto desactivado exitosamente.',
        ]);
    }

    public function buscar(string $query): JsonResponse
    {
        $productos = Producto::with('categoria', 'lotes')
            ->where('activo', true)
            ->where(function ($q) use ($query) {
                $q->where('codigo', 'like', "%{$query}%")
                    ->orWhere('nombre', 'like', "%{$query}%")
                    ->orWhere('principio_activo', 'like', "%{$query}%")
                    ->orWhere('registro_sanitario', 'like', "%{$query}%");
            })
            ->orderBy('nombre')
            ->limit(20)
            ->get();

        return response()->json(['success' => true, 'data' => $productos]);
    }
}
