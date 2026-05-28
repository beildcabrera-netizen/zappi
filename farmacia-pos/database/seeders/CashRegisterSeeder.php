<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashRegisterSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('cash_registers')->insert([
            [
                'nombre' => 'Caja 1',
                'ubicacion' => 'Principal',
                'impresora_tickets' => 'EPSON TM-T20',
                'modo_override' => 'vendedor_cobra',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nombre' => 'Caja 2',
                'ubicacion' => 'Secundaria',
                'impresora_tickets' => 'EPSON TM-T20',
                'modo_override' => 'vendedor_cobra',
                'activa' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
