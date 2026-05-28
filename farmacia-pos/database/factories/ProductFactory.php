<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'codigo_barras' => $this->faker->unique()->ean13(),
            'codigo_interno' => 'PROD-' . $this->faker->unique()->randomNumber(5),
            'nombre_comercial' => $this->faker->word() . ' ' . $this->faker->randomNumber(3) . 'mg',
            'nombre_generico' => $this->faker->word(),
            'principio_activo' => $this->faker->word(),
            'concentracion' => $this->faker->randomNumber(3) . 'mg',
            'forma_farmaceutica' => 'tableta',
            'laboratorio' => $this->faker->company(),
            'presentacion_entrada' => 'unidad',
            'unidades_por_blister' => 10,
            'blisters_por_caja' => 10,
            'fraccionamiento_habilitado' => true,
            'precio_venta_unidad' => $this->faker->randomFloat(2, 1, 100),
            'precio_venta_blister' => $this->faker->randomFloat(2, 10, 500),
            'precio_venta_caja' => $this->faker->randomFloat(2, 100, 5000),
            'costo_compra_unidad' => $this->faker->randomFloat(2, 0.5, 50),
            'stock_unidades' => $this->faker->numberBetween(50, 500),
            'stock_minimo_alertas' => 10,
            'estante' => 'A',
            'seccion' => 'General',
            'activo' => true,
        ];
    }
}
