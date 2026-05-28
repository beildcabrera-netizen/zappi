<?php

namespace Tests\Feature;

use App\Models\CashRegister;
use App\Models\CashShift;
use App\Models\Configuracion;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VentaTest extends TestCase
{
    use RefreshDatabase;

    protected User $vendedor;
    protected CashShift $turno;
    protected Product $producto;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolSeeder::class);

        Configuracion::factory()->create();

        $caja = CashRegister::factory()->create(['activa' => true]);

        $this->vendedor = User::factory()->create();
        $this->vendedor->assignRole('vendedor');

        $this->turno = CashShift::factory()->create([
            'cash_register_id' => $caja->id,
            'user_id' => $this->vendedor->id,
        ]);

        $this->producto = Product::factory()->create([
            'stock_unidades' => 100,
            'precio_venta_unidad' => 10.00,
        ]);

        $this->actingAs($this->vendedor);
    }

    #[Test]
    public function vendedor_puede_crear_venta_directa(): void
    {
        file_put_contents('/tmp/venta_debug.log', "DEBUG: test method start\n", FILE_APPEND);
        $payload = [
            'items' => [
                [
                    'product_id' => $this->producto->id,
                    'presentacion_vendida' => 'unidad',
                    'cantidad' => 2,
                    'precio_unitario' => 10.00,
                    'subtotal' => 20.00,
                    'total_item' => 20.00,
                ],
            ],
            'subtotal' => 20.00,
            'total_venta' => 20.00,
            'total_final' => 20.00,
            'metodo_pago' => 'efectivo',
            'recibido_efectivo' => 20.00,
            'cambio' => 0,
        ];

        $response = $this->post(route('ventas.store'), $payload);

        $response->assertSessionHas('success');
    }
}
