<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    use HasFactory;

    protected $table = 'configuraciones';

    protected $fillable = [
        'nombre_farmacia',
        'nit_farmacia',
        'razon_social_farmacia',
        'direccion',
        'telefono',
        'ciudad',
        'departamento',
        'actividad_economica',
        'logo_url',
        'impresora_default',
        'tipo_impresora',
        'direccion_impresora',
        'iva_porcentaje',
        'moneda_simbolo',
        'tasa_cero_habilitada',
        'tiempo_expiracion_venta_caja',
        'alerta_stock_dias',
        'llave_dosificacion',
    ];

    protected $casts = [
        'iva_porcentaje' => 'decimal:2',
        'tasa_cero_habilitada' => 'boolean',
    ];
}
