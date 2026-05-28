<?php

namespace App\Services\Impresion;

use App\Models\Sale;

class TicketService
{
    public function generar(Sale $venta): array
    {
        $venta->loadMissing(['items.product', 'vendedor', 'cajero', 'caja', 'facturaManual.talonario']);

        $configuracion = \App\Models\Configuracion::first();

        $encabezado = [
            'farmacia' => $configuracion?->nombre_farmacia ?? 'Farmacia',
            'direccion' => $configuracion?->direccion ?? '',
            'telefono' => $configuracion?->telefono ?? '',
            'nit' => $configuracion?->nit_farmacia ?? '',
            'ciudad' => $configuracion?->ciudad ?? '',
            'departamento' => $configuracion?->departamento ?? '',
            'actividad_economica' => $configuracion?->actividad_economica ?? '',
            'moneda' => $configuracion?->moneda_simbolo ?? 'Bs',
            'numero_venta' => $venta->numero_venta,
            'fecha' => $venta->created_at->format('d/m/Y H:i:s'),
            'vendedor' => $venta->vendedor?->nombre ?? '',
            'cajero' => $venta->cajero?->nombre ?? '',
            'caja' => $venta->caja?->nombre ?? '',
        ];

        $items = $venta->items->map(function ($item) {
            return [
                'codigo' => $item->product?->codigo_interno ?? '',
                'producto' => $item->product?->nombre_comercial ?? '',
                'presentacion' => $item->presentacion_vendida,
                'cantidad' => (float) $item->cantidad,
                'precio_unitario' => (float) $item->precio_unitario,
                'subtotal' => (float) $item->subtotal,
                'descuento' => (float) $item->descuento_item,
                'total' => (float) $item->total_item,
            ];
        })->toArray();

        $totales = [
            'subtotal' => (float) $venta->subtotal,
            'descuento' => (float) $venta->descuento_total,
            'total_venta' => (float) $venta->total_venta,
            'total_final' => (float) $venta->total_final,
            'recibido_efectivo' => (float) ($venta->recibido_efectivo ?? 0),
            'cambio' => (float) ($venta->cambio ?? 0),
        ];

        $metodoPago = match ($venta->metodo_pago) {
            'efectivo' => 'Efectivo',
            'qr_bancario' => 'QR Bancario',
            'tarjeta_debito' => 'Tarjeta de Débito',
            'tarjeta_credito' => 'Tarjeta de Crédito',
            'transferencia' => 'Transferencia',
            default => $venta->metodo_pago,
        };

        $pie = [
            'gracias' => '¡Gracias por su compra!',
            'linea_separadora' => str_repeat('-', 40),
        ];

        $facturaData = null;
        if ($venta->facturaManual) {
            $factura = $venta->facturaManual;
            $qrData = implode('|', [
                $factura->codigo_autorizacion,
                $factura->numero_factura,
                $factura->nit_cliente,
                $factura->fecha_emision->format('d/m/Y'),
                number_format($factura->importe_total, 2, '.', ''),
                $factura->codigo_control,
                $factura->numero_completo,
            ]);

            $facturaData = [
                'numero_factura' => $factura->numero_completo,
                'codigo_autorizacion' => $factura->codigo_autorizacion,
                'codigo_control' => $factura->codigo_control,
                'nit_cliente' => $factura->nit_cliente,
                'complemento' => $factura->complemento,
                'razon_social' => $factura->razon_social_cliente,
                'fecha_emision' => $factura->fecha_emision->format('d/m/Y'),
                'importe_total' => (float) $factura->importe_total,
                'base_debito_fiscal' => (float) $factura->base_debito_fiscal,
                'debito_fiscal' => (float) $factura->debito_fiscal,
                'qr_data' => $qrData,
            ];
        }

        $cliente = [
            'tipo' => $venta->cliente_tipo,
            'nit' => $venta->cliente_nit,
            'complemento' => $venta->cliente_complemento,
            'razon_social' => $venta->cliente_razon_social,
        ];

        return [
            'encabezado' => $encabezado,
            'cliente' => $cliente,
            'items' => $items,
            'totales' => $totales,
            'metodo_pago' => $metodoPago,
            'pie' => $pie,
            'factura' => $facturaData,
        ];
    }
}
