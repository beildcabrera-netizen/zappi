<?php

namespace Database\Seeders;

use App\Models\Talonario;
use Illuminate\Database\Seeder;

class TalonarioDemoSeeder extends Seeder
{
    public function run(): void
    {
        Talonario::create([
            'numero_autorizacion' => '482040102073187',
            'numero_tramite' => '1234567890',
            'sucursal' => 'Casa Matriz',
            'actividad_economica' => '471100 - Venta al por menor en farmacias',
            'fecha_autorizacion' => '2026-01-01',
            'fecha_limite_emision' => '2027-12-31',
            'rango_inicio' => 1,
            'rango_fin' => 50000,
            'siguiente_numero' => 15001,
            'cantidad_solicitada' => 50000,
            'pin_entrega' => '123456',
            'fecha_activacion' => '2026-01-15',
            'estado' => 'activado',
        ]);
    }
}
