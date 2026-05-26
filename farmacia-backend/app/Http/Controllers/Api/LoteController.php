<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lote;
use App\Models\Producto;
use App\Services\InventarioService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoteController extends Controller
{
    public function __construct(
        private InventarioService $inventarioService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Lote::with('producto');

        if ($request->has('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }

        if ($request->has('activo')) {
            $query->where('activo', $request->boolean('activo'));
        }

        $lotes = $query->orderBy('fecha_vencimiento')->get();

        return response()->json(['success' => true, 'data' => $lotes]);
    }

    public function show(Lote $lote): JsonResponse
    {
        $lote->load('producto');

        return response()->json(['success' => true, 'data' => $lote]);
    }

    public function ajustarStock(Request $request, Lote $lote): JsonResponse
    {
        $request->validate([
            'stock_actual' => 'required|integer|min:0',
            'motivo' => 'nullable|string|max:500',
        ]);

        try {
            $lote = $this->inventarioService->ajustarStock($lote->id, $request->integer('stock_actual'));

            return response()->json([
                'success' => true,
                'message' => 'Stock ajustado exitosamente.',
                'data' => $lote->fresh()->load('producto'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function registrarMerma(Request $request, Lote $lote): JsonResponse
    {
        $request->validate([
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string|max:500',
        ]);

        try {
            $lote = $this->inventarioService->registrarMerma(
                $lote->id,
                $request->integer('cantidad'),
                $request->string('motivo')
            );

            return response()->json([
                'success' => true,
                'message' => 'Merma registrada exitosamente.',
                'data' => $lote->fresh()->load('producto'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function alertas(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'stock_bajo' => $this->inventarioService->alertasStockBajo(),
                'por_vencer' => $this->inventarioService->alertasLotesPorVencer(30),
                'vencidos' => $this->inventarioService->alertasLotesVencidos(),
            ],
        ]);
    }
}
