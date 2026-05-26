<?php

namespace App\Services;

use App\Helpers\SINHelper;
use App\Models\ConfiguracionSin;
use App\Models\RegistroVenta;
use App\Models\Venta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SINService
{
    private ?ConfiguracionSin $config;

    public function __construct()
    {
        $this->config = ConfiguracionSin::where('activo', true)->first();
    }

    public function solicitarCUFD(): bool
    {
        if (!$this->config) {
            throw new \RuntimeException('Configuración SIN no encontrada.');
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'apiKey' => $this->config->cuis ?? '',
            ])->post('https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionCodigos/solicitarCufd', [
                'nit' => $this->config->nit,
                'codigoSucursal' => (int) $this->config->codigo_sucursal,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->config->update([
                    'cufd' => $data['codigo'],
                    'cufd_fecha' => now(),
                ]);
                return true;
            }

            Log::error('Error solicitando CUFD', ['response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error('Error solicitando CUFD: ' . $e->getMessage());
            return false;
        }
    }

    public function solicitarCUIS(): bool
    {
        if (!$this->config) {
            throw new \RuntimeException('Configuración SIN no encontrada.');
        }

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://pilotosiatservicios.impuestos.gob.bo/v2/FacturacionCodigos/solicitarCuis', [
                'nit' => $this->config->nit,
                'codigoSucursal' => (int) $this->config->codigo_sucursal,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $this->config->update([
                    'cuis' => $data['codigo'],
                    'cuis_fecha' => now(),
                ]);
                return true;
            }

            Log::error('Error solicitando CUIS', ['response' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error('Error solicitando CUIS: ' . $e->getMessage());
            return false;
        }
    }

    public function enviarFactura(Venta $venta): RegistroVenta
    {
        $registro = $venta->registroSin ?? new RegistroVenta(['venta_id' => $venta->id]);

        $numeroFactura = $this->siguienteNumeroFactura();
        $cuf = SINHelper::generarCUF($this->config, $numeroFactura);

        $xml = $this->generarXMLFactura($venta, $cuf, $numeroFactura);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'apiKey' => $this->config->cuis ?? '',
            ])->post('https://pilotosiatservicios.impuestos.gob.bo/v2/Facturacion/emision', [
                'nit' => $this->config->nit,
                'cuf' => $cuf,
                'codigoSucursal' => (int) $this->config->codigo_sucursal,
                'codigoPuntoVenta' => 0,
                'datos' => $xml,
            ]);

            $registro->fill([
                'cuf' => $cuf,
                'numero_factura' => $numeroFactura,
                'xml_envio' => $xml,
                'xml_respuesta' => $response->body(),
                'fecha_envio' => now(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $registro->codigo_autorizacion = $data['codigoAutorizacion'] ?? null;
                $registro->estado_sin = 'emitida';
            } else {
                $registro->estado_sin = 'rechazada';
                $registro->mensaje_error = $response->body();
            }

            $registro->save();
            return $registro;
        } catch (\Exception $e) {
            $registro->fill([
                'cuf' => $cuf,
                'numero_factura' => $numeroFactura,
                'xml_envio' => $xml,
                'estado_sin' => 'error',
                'mensaje_error' => $e->getMessage(),
                'fecha_envio' => now(),
            ]);
            $registro->save();

            throw $e;
        }
    }

    public function verificarEstado(): array
    {
        if (!$this->config) {
            return ['online' => false, 'message' => 'Configuración SIN no encontrada.'];
        }

        $cuisValido = $this->config->cuis && $this->config->cuis_fecha &&
            now()->diffInDays($this->config->cuis_fecha) < 30;

        $cufdValido = SINHelper::CUFDValido($this->config);

        return [
            'online' => $cuisValido && $cufdValido,
            'cuis_valido' => (bool) $cuisValido,
            'cufd_valido' => (bool) $cufdValido,
            'cuis' => $this->config->cuis,
            'cufd' => $this->config->cufd,
        ];
    }

    private function siguienteNumeroFactura(): int
    {
        $ultimo = RegistroVenta::whereNotNull('numero_factura')
            ->orderBy('numero_factura', 'desc')
            ->first();

        return $ultimo ? $ultimo->numero_factura + 1 : 1;
    }

    private function generarXMLFactura(Venta $venta, string $cuf, int $numeroFactura): string
    {
        $items = '';
        foreach ($venta->detalles as $detalle) {
            $items .= "<detalle>
                <actividadEconomica>471000</actividadEconomica>
                <codigoProductoSin>0</codigoProductoSin>
                <codigoProducto>{$detalle->producto->codigo}</codigoProducto>
                <descripcion>{$detalle->producto->nombre}</descripcion>
                <cantidad>{$detalle->cantidad}</cantidad>
                <unidadMedida>58</unidadMedida>
                <precioUnitario>" . SINHelper::formatearImporteSIN($detalle->precio_unitario) . "</precioUnitario>
                <montoDescuento>0.00</montoDescuento>
                <subTotal>" . SINHelper::formatearImporteSIN($detalle->subtotal) . "</subTotal>
                <numeroSerie>0</numeroSerie>
                <numeroImei>0</numeroImei>
            </detalle>";
        }

        return "<factura>
            <cabecera>
                <nitEmisor>{$this->config->nit}</nitEmisor>
                <razonSocialEmisor>{$this->config->razon_social}</razonSocialEmisor>
                <municipio>{$this->config->ciudad}</municipio>
                <telefono>{$this->config->telefono}</telefono>
                <direccion>{$this->config->direccion}</direccion>
                <codigoSucursal>{$this->config->codigo_sucursal}</codigoSucursal>
                <codigoPuntoVenta>0</codigoPuntoVenta>
                <fechaEmision>" . $venta->created_at->format('Y-m-d\TH:i:s') . "</fechaEmision>
                <nombreRazonSocial>{$venta->nombre_cliente}</nombreRazonSocial>
                <numeroDocumento>{$venta->documento_cliente}</numeroDocumento>
                <complemento></complemento>
                <codigoCliente>{$venta->cliente_id}</codigoCliente>
                <tipoDocumentoFiscal>1</tipoDocumentoFiscal>
                <codigoMetodoPago>1</codigoMetodoPago>
                <numeroFactura>{$numeroFactura}</numeroFactura>
                <cuf>{$cuf}</cuf>
                <montoTotal>" . SINHelper::formatearImporteSIN($venta->total) . "</montoTotal>
                <montoTotalSujetoIva>" . SINHelper::formatearImporteSIN($venta->subtotal) . "</montoTotalSujetoIva>
                <descuento>0.00</descuento>
                <totalPagar>" . SINHelper::formatearImporteSIN($venta->total) . "</totalPagar>
                <montoGiftCard>0.00</montoGiftCard>
                <transporte>0.00</transporte>
                <tipoCambio>1</tipoCambio>
                <codigoMoneda>1</codigoMoneda>
                <montoTotalMoneda>" . SINHelper::formatearImporteSIN($venta->total) . "</montoTotalMoneda>
                <leyenda>{$this->config->leyenda_1}</leyenda>
                <usuario>{$venta->user->name}</usuario>
                <codigoDocumentoSector>{$this->config->tipo_documento_sector}</codigoDocumentoSector>
                <codigoEmision>{$this->config->tipo_emision}</codigoEmision>
                <codigoModalidad>{$this->config->tipo_modalidad}</codigoModalidad>
                <numeroMedioPago>0</numeroMedioPago>
                <montoIce>0.00</montoIce>
                <montoIeha>0.00</montoIeha>
                <montoIpj>0.00</montoIpj>
            </cabecera>
            {$items}
        </factura>";
    }
}
