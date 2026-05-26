<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    protected $fillable = [
        'venta_id', 'producto_id', 'lote_id',
        'cantidad', 'precio_unitario', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function lote()
    {
        return $this->belongsTo(Lote::class);
    }
}
