<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Services\Inventario\StockService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    #[Test]
    public function convierte_unidades_a_base(): void
    {
        $service = new StockService;

        $producto = Product::factory()->make([
            'unidades_por_blister' => 10,
            'blisters_por_caja' => 5,
        ]);

        $this->assertEquals(5, $service->aUnidadesBase('unidad', 5, $producto));
        $this->assertEquals(50, $service->aUnidadesBase('blister', 5, $producto));
        $this->assertEquals(250, $service->aUnidadesBase('caja', 5, $producto));
    }

    #[Test]
    public function verifica_stock_suficiente(): void
    {
        $service = new StockService;

        $producto = Product::factory()->make([
            'stock_unidades' => 100,
            'unidades_por_blister' => 10,
            'blisters_por_caja' => 5,
        ]);

        $this->assertTrue($service->hayStock($producto, 'unidad', 50));
        $this->assertTrue($service->hayStock($producto, 'blister', 5));
        $this->assertFalse($service->hayStock($producto, 'unidad', 200));
    }

    #[Test]
    public function descompone_stock_correctamente(): void
    {
        $service = new StockService;

        $producto = Product::factory()->make([
            'stock_unidades' => 127,
            'unidades_por_blister' => 10,
            'blisters_por_caja' => 5,
        ]);

        $descompuesto = $service->descomponerStock($producto);

        $this->assertEquals(2, $descompuesto['cajas']);
        $this->assertEquals(2, $descompuesto['blisters']);
        $this->assertEquals(7, $descompuesto['unidades']);
        $this->assertEquals(127, $descompuesto['total']);
    }
}
