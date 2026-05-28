<?php

namespace App\Services\Facturacion;

class AllegedRC4
{
    private array $s;
    private string $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    public function encriptar(string $input): string
    {
        $this->inicializarKSA();
        $hex = '';

        for ($x = 0, $j = 0, $n = strlen($input); $x < $n; $x++) {
            $i = $x % 256;
            $j = ($j + $this->s[$i]) % 256;
            $this->intercambiar($i, $j);
            $k = $this->s[($this->s[$i] + $this->s[$j]) % 256];
            $hex .= strtoupper(dechex(ord($input[$x]) ^ $k));
        }

        return $hex;
    }

    private function inicializarKSA(): void
    {
        $this->s = range(0, 255);
        $keyLen = strlen($this->key);

        for ($i = 0, $j = 0; $i < 256; $i++) {
            $j = ($j + $this->s[$i] + ord($this->key[$i % $keyLen])) % 256;
            $this->intercambiar($i, $j);
        }
    }

    private function intercambiar(int $i, int $j): void
    {
        $tmp = $this->s[$i];
        $this->s[$i] = $this->s[$j];
        $this->s[$j] = $tmp;
    }
}
