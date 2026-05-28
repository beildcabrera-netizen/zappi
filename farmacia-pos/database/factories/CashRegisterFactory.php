<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CashRegisterFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre' => 'Caja ' . $this->faker->randomLetter(),
            'ubicacion' => $this->faker->word(),
            'activa' => true,
        ];
    }
}
