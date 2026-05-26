<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompraRequest;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Services\InventarioService;
use Illuminate\Http\JsonResponse;

class CompraController extends Controller
{
    public function __construct(
        private InventarioService $inventarioService
    ) {}

    public function index(): JsonResponse
    {
        $compras = Compra::with('proveedor', 'user', 'detalles.producto')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $compras]);
    }

    public function store(CompraRequest $request): JsonResponse
    {
        $data = $request->validated();
        $detalles = $data['detalles'];
        unset($data['detalles']);

        $subtotal = collect($detalles)->sum(fn($d) => $d['cantidad'] * $d['precio_unitario']);
        $data['subtotal'] = $subtotal;
        $data['total'] = $subtotal;
        $data['credito_fiscal'] = round($subtotal * 0.13, 2);
        $data['user_id'] = auth()->id();

        $compra = Compra::create($data);

        foreach ($detalles as $detalle) {
            CompraDetalle::create([
                'compra_id' => $compra->id,
                'producto_id' => $detalle['producto_id'],
                'numero_lote' => $detalle['numero_lote'],
                'fecha_vencimiento' => $detalle['fecha_vencimiento'],
                'cantidad' => $detalle['cantidad'],
                'precio_unitario' => $detalle['precio_unitario'],
                'subtotal' => $detalle['cantidad'] * $detalle['precio_unitario'],
            ]);

            $this->inventarioService->agregarStock(
                $detalle['producto_id'],
                $detalle['numero_lote'],
                $detalle['fecha_vencimiento'],
                $detalle['cantidad'],
                $detalle['precio_unitario']
            );

            $producto = \App\Models\Producto::find($detalle['producto_id']);
            if ($producto && $detalle['precio_unitario'] > 0) {
                $producto->update([
                    'precio_compra' => $detalle['precio_unitario'],
                ]);
            }
        }

        $compra->load('detalles.producto', 'proveedor');

        return response()->json([
            'success' => true,
            'message' => 'Compra registrada exitosamente.',
            'data' => $compra,
        ], 201);
    }

    public function show(Compra $compra): JsonResponse
    {
        $compra->load('detalles.producto', 'proveedor', 'user');

        return response()->json(['success' => true, 'data' => $compra]);
    }
}
