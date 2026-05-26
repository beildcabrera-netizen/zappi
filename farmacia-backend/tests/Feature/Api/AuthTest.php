<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_iniciar_sesion()
    {
        User::factory()->create([
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'activo' => true,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['token', 'user'],
            ]);
    }

    public function test_credenciales_invalidas_devuelven_error()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'no@existe.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['success' => false]);
    }

    public function test_usuario_inactivo_no_puede_iniciar_sesion()
    {
        User::factory()->create([
            'email' => 'inactivo@test.com',
            'password' => bcrypt('password'),
            'activo' => false,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'inactivo@test.com',
            'password' => 'password',
        ]);

        $response->assertStatus(403);
    }

    public function test_rutas_protegidas_requieren_token()
    {
        $response = $this->getJson('/api/v1/dashboard');

        $response->assertStatus(401);
    }
}
