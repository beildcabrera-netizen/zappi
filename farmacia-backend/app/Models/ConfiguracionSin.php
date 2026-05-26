<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionSin extends Model
{
    protected $table = 'configuracion_sin';

    protected $fillable = [
        'nit', 'razon_social', 'nombre_comercial', 'codigo_sucursal',
        'direccion', 'telefono', 'ciudad', 'pais',
        'cuis', 'cufd', 'cuis_fecha', 'cufd_fecha',
        'tipo_modalidad', 'tipo_emision', 'tipo_documento_sector',
        'leyenda_1', 'leyenda_2', 'codigo_control', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'cuis_fecha' => 'datetime',
            'cufd_fecha' => 'datetime',
            'activo' => 'boolean',
        ];
    }
}
