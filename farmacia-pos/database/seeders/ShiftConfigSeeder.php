<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftConfigSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('shift_configs')->insert([
            [
                'nombre' => 'Turno Mañana',
                'hora_inicio' => '08:00:00',
                'hora_fin' => '14:00:00',
                'dias_semana' => json_encode([1, 2, 3, 4, 5]),
                'modo_operacion' => 'vendedor_cobra',
                'cajas_activas' => json_encode([1]),
                'min_personal' => 1,
                'prioridad' => 1,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Turno Tarde',
                'hora_inicio' => '14:00:00',
                'hora_fin' => '20:00:00',
                'dias_semana' => json_encode([1, 2, 3, 4, 5]),
                'modo_operacion' => 'mixto',
                'cajas_activas' => json_encode([1]),
                'min_personal' => 1,
                'prioridad' => 2,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
