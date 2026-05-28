<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ConfiguracionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nombre_farmacia' => 'Farmacia Test',
            'direccion' => $this->faker->address(),
            'telefono' => $this->faker->phoneNumber(),
            'nit_farmacia' => $this->faker->numerify('##########'),
            'ciudad' => $this->faker->city(),
            'departamento' => 'Santa Cruz',
            'razon_social_farmacia' => 'Farmacia Test S.R.L.',
            'actividad_economica' => 'Venta de medicamentos',
            'moneda_simbolo' => 'Bs',
            'iva_porcentaje' => 13,
            'tiempo_expiracion_venta_caja' => 15,
            'llave_dosificacion' => 'Ff' . chr(130) . chr(132) . chr(134) . chr(136) . chr(140) . 'Gg',
        ];
    }
}
