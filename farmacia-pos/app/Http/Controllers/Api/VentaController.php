<?php

namespace App\Http\Controllers\Api;

use App\Contracts\Repositories\VentaRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Services\Venta\VentaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function __construct(
        protected VentaRepositoryInterface $ventaRepository,
        protected VentaService $ventaService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['fecha_desde', 'fecha_hasta', 'vendedor_id', 'metodo_pago']);

        return response()->json(
            $this->ventaRepository->reporteVentas($filters)
        );
    }

    public function show(int $id): JsonResponse
    {
        $venta = $this->ventaRepository->findById($id);

        if (!$venta) {
            return response()->json(['error' => 'Venta no encontrada'], 404);
        }

        $venta->load('items.product', 'vendedor', 'cajero', 'facturaManual');

        return response()->json($venta);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.presentacion_vendida' => 'required|string',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
            'items.*.total_item' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'total_venta' => 'required|numeric|min:0',
            'total_final' => 'required|numeric|min:0',
            'metodo_pago' => 'required|string',
            'generar_factura' => 'boolean',
        ]);

        $user = $request->user();

        $datos = array_merge($validated, [
            'vendedor_id' => $user->id,
            'cajero_id' => $user->id,
        ]);

        $venta = $this->ventaService->crear($datos);

        return response()->json($venta->load('items.product'), 201);
    }
}
