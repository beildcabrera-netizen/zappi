<?php

namespace App\Helpers;

class NITValidator
{
    const FACTOR = [2, 3, 4, 5, 6, 7, 2, 3, 4, 5, 6, 7, 2, 3, 4];

    public static function validar(string $nit): bool
    {
        $nit = preg_replace('/[^0-9]/', '', $nit);
        $length = strlen($nit);

        if ($length < 7 || $length > 15) {
            return false;
        }

        $digitoCalculado = self::calcularDigito($nit);
        $digitoIngresado = (int) substr($nit, -1);

        return $digitoCalculado === $digitoIngresado;
    }

    public static function calcularDigito(string $nit): int
    {
        $nit = substr($nit, 0, -1);
        $sum = 0;
        $length = strlen($nit);

        for ($i = 0; $i < $length; $i++) {
            $sum += (int) $nit[$i] * self::FACTOR[$i];
        }

        $residuo = $sum % 11;
        $digito = 11 - $residuo;

        if ($digito === 11) {
            $digito = 0;
        } elseif ($digito === 10) {
            $digito = 1;
        }

        return $digito;
    }
}
