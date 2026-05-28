<?php

namespace App\Services\Facturacion;

class CodigoControlService
{
    private array $factores = [2, 3, 4, 5, 6, 7];

    public function generar(
        string $numeroAutorizacion,
        string $numeroFactura,
        string $nitCliente,
        string $fechaEmision,
        float  $montoTotal,
        string $llaveDosificacion
    ): string {
        $digitoVerificador = $this->obtenerDigitoVerificador($numeroFactura);

        $rc4 = new AllegedRC4((string)$digitoVerificador);
        $hexEncrypted = $rc4->encriptar($numeroAutorizacion);

        $sumaNit = $this->sumatoriaPonderada(preg_replace('/[^0-9]/', '', $nitCliente));
        $sumaFecha = $this->sumatoriaPonderada(preg_replace('/[^0-9]/', '', $fechaEmision));
        $sumaMonto = $this->sumatoriaPonderada($this->formatearMonto($montoTotal));

        $reducidoNit = $this->reducirADigito($sumaNit);
        $reducidoFecha = $this->reducirADigito($sumaFecha);
        $reducidoMonto = $this->reducirADigito($sumaMonto);

        $baseVerhoeff = $digitoVerificador . $reducidoNit . $reducidoFecha . $reducidoMonto;
        $digitoVerhoeff = Verhoeff::calcular($baseVerhoeff);

        $codigoParcial = $this->extraerDigitos($hexEncrypted, 7);
        $codigoSinLlave = $codigoParcial . $digitoVerificador . $digitoVerhoeff;

        $llaveNumeros = $this->extraerDigitos($llaveDosificacion, 4);
        $sumaConLlave = $this->sumatoriaPonderada($codigoSinLlave . $llaveNumeros);
        $digitoLlave = $sumaConLlave % 10;

        return $this->formatearCodigo($codigoParcial, $digitoVerificador, $reducidoMonto, $digitoVerhoeff, $digitoLlave);
    }

    public function sumatoriaPonderada(string $digitos): int
    {
        $total = 0;
        $len = strlen($digitos);
        $numFactores = count($this->factores);

        for ($i = 0; $i < $len; $i++) {
            $factor = $this->factores[($len - 1 - $i) % $numFactores];
            $total += (int)$digitos[$i] * $factor;
        }

        return $total;
    }

    private function obtenerDigitoVerificador(string $numeroFactura): int
    {
        $suma = $this->sumatoriaPonderada($numeroFactura);
        return $suma % 11;
    }

    private function formatearMonto(float $monto): string
    {
        return number_format($monto, 2, '', '');
    }

    private function reducirADigito(int $numero): int
    {
        while ($numero > 9) {
            $numero = array_sum(str_split((string)$numero));
        }
        return $numero;
    }

    private function extraerDigitos(string $texto, int $cantidad): string
    {
        $digitos = preg_replace('/[^0-9]/', '', $texto);
        return substr($digitos, 0, $cantidad);
    }

    private function formatearCodigo(
        string $codigoParcial,
        int    $digitoVerificador,
        int    $reducidoMonto,
        int    $digitoVerhoeff,
        int    $digitoLlave
    ): string {
        $codigo = $codigoParcial . $digitoVerificador . $reducidoMonto . $digitoVerhoeff . $digitoLlave;
        return strtoupper(substr($codigo, 0, 14));
    }
}
