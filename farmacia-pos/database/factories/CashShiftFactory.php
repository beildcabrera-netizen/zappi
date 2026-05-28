<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CashShiftFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tipo_turno' => 'vendedor_cobra',
            'fecha_apertura' => now(),
            'monto_inicial' => 500,
            'estado' => 'abierta',
        ];
    }
}
