<?php

namespace App\Services\Venta;

use App\Contracts\Repositories\VentaRepositoryInterface;
use App\Models\Product;
use App\Models\Sale;
use App\Services\Caja\TurnoService;
use App\Services\Inventario\StockService;
use App\Events\VentaEnEspera;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class VentaService
{
    public function __construct(
        protected StockService $stockService,
        protected TurnoService $turnoService,
        protected VentaRepositoryInterface $ventaRepository,
    ) {}

    public function crear(array $datos): Sale
    {
        return DB::transaction(function () use ($datos) {
            $numeroVenta = $this->generarNumeroVenta();

            $sale = Sale::create([
                'numero_venta' => $numeroVenta,
                'estado_venta' => 'completada',
                'caja_id' => $datos['caja_id'] ?? null,
                'turno_vendedor_id' => $datos['turno_vendedor_id'] ?? null,
                'turno_cajero_id' => $datos['turno_cajero_id'] ?? null,
                'vendedor_id' => $datos['vendedor_id'],
                'cajero_id' => $datos['cajero_id'] ?? $datos['vendedor_id'],
                'cliente_tipo' => $datos['cliente_tipo'] ?? 'consumidor_final',
                'cliente_nit' => $datos['cliente_nit'] ?? '0',
                'cliente_complemento' => $datos['cliente_complemento'] ?? null,
                'cliente_razon_social' => $datos['cliente_razon_social'] ?? null,
                'subtotal' => $datos['subtotal'],
                'descuento_total' => $datos['descuento_total'] ?? 0,
                'total_venta' => $datos['total_venta'],
                'total_final' => $datos['total_final'],
                'metodo_pago' => $datos['metodo_pago'],
                'tipo_documento' => $datos['tipo_documento'] ?? 'nota_venta',
                'recibido_efectivo' => $datos['recibido_efectivo'] ?? null,
                'cambio' => $datos['cambio'] ?? null,
                'codigo_transaccion_qr' => $datos['codigo_transaccion_qr'] ?? null,
                'referencia_transferencia' => $datos['referencia_transferencia'] ?? null,
            ]);

            foreach ($datos['items'] as $i => $item) {
                $producto = Product::where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $unidadesDescontadas = $this->stockService->aUnidadesBase(
                    $item['presentacion_vendida'],
                    $item['cantidad'],
                    $producto
                );
                $sale->items()->create([
                    'product_id' => $producto->id,
                    'presentacion_vendida' => $item['presentacion_vendida'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                    'descuento_item' => $item['descuento_item'] ?? 0,
                    'total_item' => $item['total_item'],
                    'unidades_descontadas' => $unidadesDescontadas,
                    'receta_numero' => $item['receta_numero'] ?? null,
                    'receta_medico' => $item['receta_medico'] ?? null,
                    'receta_foto_url' => $item['receta_foto_url'] ?? null,
                ]);

                $this->stockService->descontar($producto, $unidadesDescontadas);
            }

            $this->actualizarConteosTurno($sale);

            return $sale;
        });
    }

    public function crearPendiente(array $datos): Sale
    {
        return DB::transaction(function () use ($datos) {
            $numeroVenta = $this->generarNumeroVenta();

            $sale = Sale::create([
                'numero_venta' => $numeroVenta,
                'estado_venta' => 'pendiente',
                'caja_id' => $datos['caja_id'] ?? null,
                'turno_vendedor_id' => $datos['turno_vendedor_id'] ?? null,
                'vendedor_id' => $datos['vendedor_id'],
                'cliente_tipo' => $datos['cliente_tipo'] ?? 'consumidor_final',
                'cliente_nit' => $datos['cliente_nit'] ?? '0',
                'cliente_complemento' => $datos['cliente_complemento'] ?? null,
                'cliente_razon_social' => $datos['cliente_razon_social'] ?? null,
                'subtotal' => $datos['subtotal'],
                'descuento_total' => $datos['descuento_total'] ?? 0,
                'total_venta' => $datos['total_venta'],
                'total_final' => $datos['total_final'],
                'metodo_pago' => $datos['metodo_pago'],
                'tipo_documento' => $datos['tipo_documento'] ?? 'nota_venta',
                'enviada_a_caja_at' => now(),
                'expira_at' => now()->addMinutes(
                    Cache::remember('configuracion.tiempo_expiracion', 3600, fn() => \App\Models\Configuracion::first()?->tiempo_expiracion_venta_caja) ?? 15
                ),
            ]);

            foreach ($datos['items'] as $item) {
                $producto = Product::where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $unidadesDescontadas = $this->stockService->aUnidadesBase(
                    $item['presentacion_vendida'],
                    $item['cantidad'],
                    $producto
                );

                $sale->items()->create([
                    'product_id' => $producto->id,
                    'presentacion_vendida' => $item['presentacion_vendida'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['subtotal'],
                    'descuento_item' => $item['descuento_item'] ?? 0,
                    'total_item' => $item['total_item'],
                    'unidades_descontadas' => $unidadesDescontadas,
                    'receta_numero' => $item['receta_numero'] ?? null,
                    'receta_medico' => $item['receta_medico'] ?? null,
                    'receta_foto_url' => $item['receta_foto_url'] ?? null,
                ]);

                $this->stockService->descontar($producto, $unidadesDescontadas);
            }

            $sale->update(['estado_venta' => 'en_caja']);

            VentaEnEspera::dispatch($sale, $sale->caja_id);

            return $sale;
        });
    }

    public function cobrar(Sale $venta, array $datos, int $cajeroId): Sale
    {
        return DB::transaction(function () use ($venta, $datos, $cajeroId) {
            $venta->update([
                'estado_venta' => 'completada',
                'cajero_id' => $cajeroId,
                'turno_cajero_id' => $datos['turno_cajero_id'] ?? null,
                'metodo_pago' => $datos['metodo_pago'],
                'recibido_efectivo' => $datos['recibido_efectivo'] ?? null,
                'cambio' => $datos['cambio'] ?? null,
                'codigo_transaccion_qr' => $datos['codigo_transaccion_qr'] ?? null,
                'referencia_transferencia' => $datos['referencia_transferencia'] ?? null,
            ]);

            return $venta->fresh();
        });
    }

    public function anular(Sale $venta, string $motivo, int $userId): Sale
    {
        return DB::transaction(function () use ($venta, $motivo, $userId) {
            $venta->load('items');

            foreach ($venta->items as $item) {
                Product::where('id', $item->product_id)
                    ->lockForUpdate()
                    ->increment('stock_unidades', $item->unidades_descontadas);
            }

            $venta->update([
                'estado_venta' => 'anulada',
                'motivo_anulacion' => $motivo,
                'anulado_por' => $userId,
                'anulado_at' => now(),
            ]);

            return $venta;
        });
    }

    protected function generarNumeroVenta(): string
    {
        $lastSale = Sale::whereYear('created_at', now()->year)
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        $counter = $lastSale ? (int) substr($lastSale->numero_venta, -6) + 1 : 1;

        return 'V-' . now()->year . '-' . str_pad($counter, 6, '0', STR_PAD_LEFT);
    }

    protected function actualizarConteosTurno(Sale $sale): void
    {
        $modo = $this->turnoService->detectarModoActual();

        if ($sale->turno_vendedor_id && in_array($modo, ['vendedor_cobra', 'mixto'])) {
            $sale->turnoVendedor->increment('ventas_propias_count');
            $sale->turnoVendedor->increment('total_ventas_propias', $sale->total_final);
        }

        if ($sale->turno_cajero_id) {
            $sale->turnoCajero->increment('ventas_cobradas_otros_count');
            $sale->turnoCajero->increment('total_ventas_otros', $sale->total_final);
        }
    }
}
