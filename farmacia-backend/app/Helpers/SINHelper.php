<?php

namespace App\Helpers;

use App\Models\ConfiguracionSin;

class SINHelper
{
    public static function generarCUF(ConfiguracionSin $config, int $numeroFactura): string
    {
        $nit = str_pad(preg_replace('/[^0-9]/', '', $config->nit), 13, '0', STR_PAD_LEFT);
        $fecha = now()->format('YmdHis') . '000';
        $sucursal = str_pad($config->codigo_sucursal, 4, '0', STR_PAD_LEFT);
        $modalidad = str_pad($config->tipo_modalidad, 1, '1');
        $emision = str_pad($config->tipo_emision, 1, '1');
        $tipoFactura = '1';
        $tipoSector = str_pad($config->tipo_documento_sector, 2, '0', STR_PAD_LEFT);
        $nroFactura = str_pad($numeroFactura, 10, '0', STR_PAD_LEFT);

        $base = $nit . $fecha . $sucursal . $modalidad . $emision . $tipoFactura . $tipoSector . $nroFactura;

        $digito = self::calcularDigitoVerificador($base);

        return $base . $digito;
    }

    private static function calcularDigitoVerificador(string $base): string
    {
        $sum = 0;
        $length = strlen($base);

        for ($i = 0; $i < $length; $i++) {
            $sum += (int) $base[$i] * (2 - ($i % 2));
        }

        $residuo = $sum % 11;
        $digito = 11 - $residuo;

        if ($digito === 11) {
            $digito = 0;
        } elseif ($digito === 10) {
            $digito = 1;
        }

        return (string) $digito;
    }

    public static function formatearImporteSIN(float $monto): string
    {
        return number_format($monto, 2, '.', '');
    }

    public static function CUFDValido(?ConfiguracionSin $config): bool
    {
        if (!$config || !$config->cufd || !$config->cufd_fecha) {
            return false;
        }

        return now()->lessThan($config->cufd_fecha->copy()->addHours(24));
    }
}
