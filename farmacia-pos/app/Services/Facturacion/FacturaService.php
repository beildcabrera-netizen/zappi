<?php

namespace App\Services\Facturacion;

use App\Exceptions\TalonarioAgotadoException;
use App\Exceptions\TalonarioExpiradoException;
use App\Models\ManualInvoice;
use App\Models\Sale;
use App\Models\Talonario;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FacturaService
{
    public function __construct(
        protected CodigoControlService $codigoControlService,
    ) {}

    public function asignarFactura(Sale $venta): ManualInvoice
    {
        return DB::transaction(function () use ($venta) {
            $talonario = Talonario::where('estado', 'activado')
                ->where('fecha_limite_emision', '>=', now()->toDateString())
                ->where('siguiente_numero', '<=', DB::raw('rango_fin'))
                ->orderBy('siguiente_numero')
                ->lockForUpdate()
                ->first();

            if (!$talonario) {
                $talonarioAgotado = Talonario::where('estado', 'activado')
                    ->where('siguiente_numero', '>', DB::raw('rango_fin'))
                    ->first();
                if ($talonarioAgotado) {
                    throw new TalonarioAgotadoException($talonarioAgotado->numero_autorizacion);
                }
                throw new TalonarioExpiradoException('Sin talonario disponible');
            }

            $numeroFactura = (string) $talonario->siguiente_numero;
            $numeroCompleto = $talonario->numero_autorizacion . '-' . str_pad($numeroFactura, 10, '0', STR_PAD_LEFT);
            $ivaPorcentaje = (float) config('farmacia.iva_porcentaje', 13);
            $ivaFactor = $ivaPorcentaje / 100;
            $subtotal = $venta->subtotal - ($venta->descuento_total ?? 0);
            $baseDebitoFiscal = round($subtotal / (1 + $ivaFactor), 2);
            $debitoFiscal = round($subtotal - $baseDebitoFiscal, 2);

            $configuracion = Cache::remember('configuracion', 3600, fn() => \App\Models\Configuracion::first());
            $llaveDosificacion = $configuracion?->llave_dosificacion ?? '';

            $codigoControl = $this->codigoControlService->generar(
                numeroAutorizacion: $talonario->numero_autorizacion,
                numeroFactura: $numeroFactura,
                nitCliente: $venta->cliente_nit,
                fechaEmision: now()->format('Ymd'),
                montoTotal: $venta->total_final,
                llaveDosificacion: $llaveDosificacion,
            );

            $factura = ManualInvoice::create([
                'talonario_id' => $talonario->id,
                'numero_factura' => $numeroFactura,
                'numero_completo' => $numeroCompleto,
                'codigo_autorizacion' => $talonario->numero_autorizacion,
                'codigo_control' => $codigoControl,
                'fecha_emision' => now()->toDateString(),
                'nit_cliente' => $venta->cliente_nit,
                'complemento' => $venta->cliente_complemento,
                'razon_social_cliente' => $venta->cliente_razon_social,
                'importe_total' => $venta->total_final,
                'subtotal' => $subtotal,
                'descuentos' => $venta->descuento_total ?? 0,
                'base_debito_fiscal' => $baseDebitoFiscal,
                'debito_fiscal' => $debitoFiscal,
                'estado' => 'V',
                'vendedor_id' => $venta->vendedor_id,
                'caja_id' => $venta->caja_id,
                'sale_id' => $venta->id,
            ]);

            $talonario->increment('siguiente_numero');

            if ($talonario->siguiente_numero > $talonario->rango_fin) {
                $talonario->update(['estado' => 'agotado']);
            }

            $venta->update([
                'factura_manual_id' => $factura->id,
                'tipo_documento' => 'factura_manual',
            ]);

            return $factura;
        });
    }
}
