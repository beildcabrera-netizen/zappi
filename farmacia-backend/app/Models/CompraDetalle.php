<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{
    protected $fillable = [
        'compra_id', 'producto_id', 'numero_lote',
        'fecha_vencimiento', 'cantidad', 'precio_unitario', 'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
            'cantidad' => 'integer',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
