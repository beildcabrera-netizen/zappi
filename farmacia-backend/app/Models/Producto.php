<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $fillable = [
        'codigo', 'nombre', 'principio_activo', 'concentracion',
        'forma_farmaceutica', 'presentacion', 'registro_sanitario',
        'categoria_id', 'precio_compra', 'precio_venta',
        'ganancia_porcentaje', 'stock_minimo', 'descripcion', 'activo',
    ];

    protected function casts(): array
    {
        return [
            'precio_compra' => 'decimal:2',
            'precio_venta' => 'decimal:2',
            'ganancia_porcentaje' => 'decimal:2',
            'activo' => 'boolean',
        ];
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function lotes()
    {
        return $this->hasMany(Lote::class);
    }

    public function compraDetalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }

    public function ventaDetalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function getStockTotalAttribute()
    {
        return $this->lotes()->sum('stock_actual');
    }

    public function getPrecioGananciaAttribute()
    {
        return $this->precio_venta - $this->precio_compra;
    }
}
