<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConfiguracionInicialSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('configuraciones')->insert([
            'nombre_farmacia' => 'Farmacia Boliviana',
            'nit_farmacia' => '1234567890123',
            'razon_social_farmacia' => 'Farmacia Boliviana S.R.L.',
            'direccion' => 'Av. San Martín #1234, Zona Central',
            'telefono' => '591-3-3456789',
            'ciudad' => 'Santa Cruz',
            'departamento' => 'Santa Cruz',
            'actividad_economica' => 'Venta de productos farmacéuticos y cosméticos',
            'logo_url' => null,
            'impresora_default' => 'EPSON TM-T20',
            'iva_porcentaje' => 13.00,
            'moneda_simbolo' => 'Bs',
            'tasa_cero_habilitada' => true,
            'tiempo_expiracion_venta_caja' => 15,
            'alerta_stock_dias' => 30,
            'llave_dosificacion' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
