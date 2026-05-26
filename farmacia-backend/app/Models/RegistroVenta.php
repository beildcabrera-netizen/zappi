<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistroVenta extends Model
{
    protected $table = 'registro_ventas';

    protected $fillable = [
        'venta_id', 'cuf', 'codigo_autorizacion', 'estado_sin',
        'xml_envio', 'xml_respuesta', 'numero_factura',
        'fecha_envio', 'mensaje_error',
    ];

    protected function casts(): array
    {
        return [
            'fecha_envio' => 'datetime',
            'numero_factura' => 'integer',
        ];
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}
