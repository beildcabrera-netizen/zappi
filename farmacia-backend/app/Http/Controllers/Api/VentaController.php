<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\VentaRequest;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Services\FacturaService;
use App\Services\InventarioService;
use Illuminate\Http\JsonResponse;

class VentaController extends Controller
{
    public function __construct(
        private InventarioService $inventarioService,
        private FacturaService $facturaService
    ) {}

    public function index(): JsonResponse
    {
        $ventas = Venta::with('cliente', 'user', 'detalles.producto', 'registroSin')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['success' => true, 'data' => $ventas]);
    }

    public function store(VentaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $detalles = $data['detalles'];
        unset($data['detalles']);

        $subtotal = 0;
        $itemsVenta = [];

        foreach ($detalles as $detalle) {
            $lotesDescontados = $this->inventarioService->descontarStock(
                $detalle['producto_id'],
                $detalle['cantidad']
            );

            foreach ($lotesDescontados as $ld) {
                $totalItem = $ld['cantidad'] * $detalle['precio_unitario'];
                $subtotal += $totalItem;

                $itemsVenta[] = [
                    'producto_id' => $detalle['producto_id'],
                    'lote_id' => $ld['lote_id'],
                    'cantidad' => $ld['cantidad'],
                    'precio_unitario' => $detalle['precio_unitario'],
                    'subtotal' => $totalItem,
                ];
            }
        }

        $data['subtotal'] = $subtotal;
        $data['descuento'] = $data['descuento'] ?? 0;
        $iva = round(($subtotal - $data['descuento']) * 0.13, 2);
        $data['iva'] = $iva;
        $data['ice'] = 0;
        $data['iehd'] = 0;
        $data['ipj'] = 0;
        $data['total'] = round($subtotal - $data['descuento'] + $iva, 2);
        $data['user_id'] = auth()->id();
        $data['codigo_factura'] = 'FAC-' . now()->format('YmdHis') . '-' . random_int(100, 999);

        if (!empty($data['cliente_id'])) {
            $cliente = \App\Models\Cliente::find($data['cliente_id']);
            $data['nombre_cliente'] = $data['nombre_cliente'] ?? $cliente?->nombre;
            $data['documento_cliente'] = $data['documento_cliente'] ?? $cliente?->documento;
        }

        $venta = Venta::create($data);

        foreach ($itemsVenta as $item) {
            $item['venta_id'] = $venta->id;
            VentaDetalle::create($item);
        }

        $venta->load('detalles.producto', 'cliente');

        return response()->json([
            'success' => true,
            'message' => 'Venta registrada exitosamente.',
            'data' => $venta,
        ], 201);
    }

    public function show(Venta $venta): JsonResponse
    {
        $venta->load('detalles.producto', 'cliente', 'user', 'registroSin');

        return response()->json(['success' => true, 'data' => $venta]);
    }

    public function anular(Venta $venta): JsonResponse
    {
        if ($venta->estado === 'anulada') {
            return response()->json([
                'success' => false,
                'message' => 'La venta ya se encuentra anulada.',
            ], 400);
        }

        $venta->update(['estado' => 'anulada']);

        foreach ($venta->detalles as $detalle) {
            $lote = \App\Models\Lote::find($detalle->lote_id);
            if ($lote) {
                $lote->increment('stock_actual', $detalle->cantidad);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Venta anulada exitosamente. Stock restaurado.',
            'data' => $venta->fresh()->load('detalles.producto'),
        ]);
    }

    public function imprimir(Venta $venta): JsonResponse
    {
        try {
            $pdf = $this->facturaService->generarTicket($venta);

            $base64 = base64_encode($pdf);

            return response()->json([
                'success' => true,
                'data' => [
                    'pdf_base64' => $base64,
                    'nombre' => "ticket_{$venta->codigo_factura}.pdf",
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el ticket: ' . $e->getMessage(),
            ], 500);
        }
    }
}
