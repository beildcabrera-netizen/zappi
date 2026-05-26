<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    protected $fillable = [
        'producto_id', 'numero_lote', 'fecha_vencimiento',
        'stock_actual', 'stock_inicial', 'precio_compra',
        'fecha_recepcion', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_vencimiento' => 'date',
            'fecha_recepcion' => 'date',
            'precio_compra' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function ventaDetalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function getDiasPorVencerAttribute()
    {
        return now()->diffInDays($this->fecha_vencimiento, false);
    }
}
