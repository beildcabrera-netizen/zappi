<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Services\RCVService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReporteController extends Controller
{
    public function __construct(
        private RCVService $rcvService
    ) {}

    public function ventas(Request $request): JsonResponse
    {
        $ventas = $this->rcvService->generarDesdeVentas($request->only(['desde', 'hasta', 'cliente_id']));

        return response()->json(['success' => true, 'data' => $ventas]);
    }

    public function exportarCSV(Request $request): JsonResponse
    {
        $ventas = $this->rcvService->generarDesdeVentas($request->only(['desde', 'hasta']));
        $csv = $this->rcvService->exportarCSV($ventas);

        return response()->json([
            'success' => true,
            'data' => [
                'csv' => $csv,
                'filename' => 'rcv_export_' . now()->format('Ymd_His') . '.csv',
            ],
        ]);
    }

    public function resumenDiario(Request $request): JsonResponse
    {
        $fecha = $request->get('fecha', today()->format('Y-m-d'));

        $ventas = Venta::whereDate('created_at', $fecha)
            ->with('detalles.producto', 'registroSin')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'fecha' => $fecha,
                'total_ventas' => $ventas->count(),
                'total_ingresos' => $ventas->where('estado', 'completada')->sum('total'),
                'ventas' => $ventas,
            ],
        ]);
    }
}
