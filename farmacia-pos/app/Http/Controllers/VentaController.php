<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Services\Facturacion\FacturaService;
use App\Services\Impresion\TicketService;
use App\Services\Inventario\StockService;
use App\Services\Venta\VentaService;
use App\Exceptions\VentaYaAnuladaException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class VentaController extends Controller
{
    public function __construct(
        protected VentaService $ventaService,
        protected FacturaService $facturaService,
        protected TicketService $ticketService,
        protected StockService $stockService,
    ) {}

    public function store(StoreVentaRequest $request): RedirectResponse
    {
        file_put_contents('/tmp/venta_debug.log', "DEBUG: controller store start\n", FILE_APPEND);
        $user = auth()->user();
        file_put_contents('/tmp/venta_debug.log', "DEBUG: controller after auth\n", FILE_APPEND);

        file_put_contents('/tmp/venta_debug.log', "DEBUG: after validated\n", FILE_APPEND);
        $datos = array_merge($request->validated(), [
            'vendedor_id' => $user->id,
            'cajero_id' => $user->id,
            'turno_vendedor_id' => $user->turnoActivo?->id,
            'turno_cajero_id' => $user->puede_cobrar ? $user->turnoActivo?->id : null,
        ]);

        $venta = $this->ventaService->crear($datos);

        if ($request->boolean('generar_factura')) {
            $this->facturaService->asignarFactura($venta);
        }

        $ticket = $this->ticketService->generar($venta);

        return Redirect::route('caja.venta')->with([
            'success' => 'Venta realizada correctamente.',
            'ticket' => $ticket,
        ]);
    }

    public function enviarACaja(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'caja_id' => 'required|exists:cash_registers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.presentacion_vendida' => 'required|string',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
            'items.*.total_item' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'total_venta' => 'required|numeric|min:0',
            'total_final' => 'required|numeric|min:0',
            'metodo_pago' => 'required|string',
            'cliente_tipo' => 'nullable|string|in:consumidor_final,con_nit',
            'cliente_nit' => 'nullable|string|max:20',
            'cliente_complemento' => 'nullable|string|max:10',
            'cliente_razon_social' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        $datos = array_merge($validated, [
            'vendedor_id' => $user->id,
            'turno_vendedor_id' => $user->turnoActivo?->id,
        ]);

        $venta = $this->ventaService->crearPendiente($datos);

        return Redirect::route('caja.venta')->with('success', 'Venta enviada a caja correctamente.');
    }

    public function cobrar(Sale $venta, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'metodo_pago' => 'required|string|in:efectivo,qr_bancario,tarjeta_debito,tarjeta_credito,transferencia',
            'recibido_efectivo' => 'nullable|numeric|min:0',
            'cambio' => 'nullable|numeric|min:0',
            'codigo_transaccion_qr' => 'nullable|string|max:255',
            'referencia_transferencia' => 'nullable|string|max:255',
            'generar_factura' => 'boolean',
        ]);

        $user = auth()->user();

        if (!$user->puede_cobrar) {
            return Redirect::back()->with('error', 'No tienes permiso para cobrar ventas.');
        }

        if ($venta->estado_venta !== 'en_caja') {
            return Redirect::back()->with('error', 'La venta no está en estado de caja.');
        }

        $venta->load('items.product');

        foreach ($venta->items as $item) {
            if (!$this->stockService->hayStock($item->product, $item->presentacion_vendida, $item->cantidad)) {
                return Redirect::back()->with('error', "Stock insuficiente para {$item->product?->nombre_comercial}. La venta puede haber expirado.");
            }
        }

        $datos = array_merge($validated, [
            'turno_cajero_id' => $user->turnoActivo?->id,
        ]);

        $this->ventaService->cobrar($venta, $datos, $user->id);

        if (($validated['generar_factura'] ?? false) && $venta->cliente_tipo === 'con_nit') {
            $this->facturaService->asignarFactura($venta);
        }

        $venta->refresh();
        $ticket = $this->ticketService->generar($venta);

        return Redirect::route('caja.venta')->with([
            'success' => 'Venta cobrada correctamente.',
            'ticket' => $ticket,
        ]);
    }

    public function anular(Sale $venta, Request $request): RedirectResponse
    {
        $user = auth()->user();

        if (!$user->hasRole('administrador')) {
            return Redirect::back()->with('error', 'Solo el administrador puede anular ventas.');
        }

        if ($venta->estado_venta === 'anulada') {
            return Redirect::back()->with('error', 'La venta ya está anulada.');
        }

        $validated = $request->validate([
            'motivo_anulacion' => 'required|string|max:500',
        ]);

        $this->ventaService->anular($venta, $validated['motivo_anulacion'], $user->id);

        return Redirect::back()->with('success', 'Venta anulada correctamente. Stock restaurado.');
    }
}
