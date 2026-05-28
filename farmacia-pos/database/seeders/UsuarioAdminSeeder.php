<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'nombre' => 'Administrador',
            'email' => 'admin@farmacia.com',
            'password' => Hash::make('admin123'),
            'rol' => 'administrador',
            'puede_cobrar' => true,
            'activo' => true,
        ]);
        $admin->assignRole('administrador');

        $vendedor = User::create([
            'nombre' => 'María López',
            'email' => 'vendedor@farmacia.com',
            'password' => Hash::make('vendedor123'),
            'rol' => 'vendedor',
            'puede_cobrar' => false,
            'activo' => true,
        ]);
        $vendedor->assignRole('vendedor');

        $cajero = User::create([
            'nombre' => 'Carlos Pérez',
            'email' => 'cajero@farmacia.com',
            'password' => Hash::make('cajero123'),
            'rol' => 'cajero',
            'puede_cobrar' => true,
            'activo' => true,
        ]);
        $cajero->assignRole('cajero');
    }
}
