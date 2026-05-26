<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@farmacia.com',
            'password' => bcrypt('admin123'),
            'role' => 'admin',
            'telefono' => '77777777',
            'activo' => true,
        ]);

        User::create([
            'name' => 'Vendedor 1',
            'email' => 'vendedor@farmacia.com',
            'password' => bcrypt('vendedor123'),
            'role' => 'vendedor',
            'telefono' => '77777778',
            'activo' => true,
        ]);

        $categorias = [
            'Analgésicos',
            'Antibióticos',
            'Antiinflamatorios',
            'Antihistamínicos',
            'Cardiovasculares',
            'Digestivos',
            'Respiratorios',
            'Vitaminas y Suplementos',
            'Cuidado Personal',
            'Otros',
        ];

        foreach ($categorias as $nombre) {
            Categoria::create(['nombre' => $nombre]);
        }
    }
}
