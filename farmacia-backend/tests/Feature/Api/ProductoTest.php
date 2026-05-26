<?php

namespace Tests\Feature\Api;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductoTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private string $token;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'activo' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);

        $this->token = $response->json('data.token');
    }

    public function test_listar_productos()
    {
        $response = $this->withToken($this->token)
            ->getJson('/api/v1/productos');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_crear_producto()
    {
        $categoria = Categoria::factory()->create();

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/productos', [
                'codigo' => 'PROD-001',
                'nombre' => 'Paracetamol 500mg',
                'principio_activo' => 'Paracetamol',
                'concentracion' => '500mg',
                'forma_farmaceutica' => 'Tableta',
                'presentacion' => 'Caja x 100',
                'categoria_id' => $categoria->id,
                'precio_compra' => 5.00,
                'precio_venta' => 10.00,
                'stock_minimo' => 10,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => ['codigo' => 'PROD-001'],
            ]);
    }

    public function test_crear_producto_sin_codigo_duplicado()
    {
        $categoria = Categoria::factory()->create();

        $this->withToken($this->token)->postJson('/api/v1/productos', [
            'codigo' => 'PROD-001',
            'nombre' => 'Producto A',
            'precio_compra' => 5,
            'precio_venta' => 10,
            'categoria_id' => $categoria->id,
        ]);

        $response = $this->withToken($this->token)
            ->postJson('/api/v1/productos', [
                'codigo' => 'PROD-001',
                'nombre' => 'Producto B',
                'precio_compra' => 5,
                'precio_venta' => 10,
                'categoria_id' => $categoria->id,
            ]);

        $response->assertStatus(422);
    }

    public function test_buscar_producto()
    {
        $categoria = Categoria::factory()->create();

        $this->withToken($this->token)->postJson('/api/v1/productos', [
            'codigo' => 'PROD-002',
            'nombre' => 'Ibuprofeno 400mg',
            'precio_compra' => 8,
            'precio_venta' => 15,
            'categoria_id' => $categoria->id,
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/v1/productos/buscar/Ibuprofeno');

        $response->assertStatus(200)
            ->assertJsonFragment(['nombre' => 'Ibuprofeno 400mg']);
    }
}
