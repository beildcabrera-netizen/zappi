<?php

namespace App\Services;

class NITValidator
{
    public function validar(string $nit): bool
    {
        $nit = preg_replace('/[^0-9]/', '', $nit);
        if (strlen($nit) < 7 || strlen($nit) > 15) return false;
        if ($nit === '0') return true;

        $digits = str_split($nit);
        $lastDigit = (int) array_pop($digits);
        $factors = [2, 3, 4, 5, 6, 7, 2, 3, 4, 5, 6, 7, 2, 3, 4];
        $sum = 0;
        $position = count($digits) - 1;

        foreach ($digits as $digit) {
            $sum += (int) $digit * $factors[$position--];
        }

        $remainder = $sum % 11;
        $check = 11 - $remainder;
        if ($check === 11) $check = 0;
        if ($check === 10) return false;

        return $check === $lastDigit;
    }

    public function formatear(string $nit): string
    {
        $nit = preg_replace('/[^0-9]/', '', $nit);
        if (strlen($nit) <= 7) return $nit;

        return substr($nit, 0, -1) . '-' . substr($nit, -1);
    }
}
