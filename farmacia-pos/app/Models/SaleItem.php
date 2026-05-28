<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'presentacion_vendida',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'descuento_item',
        'total_item',
        'unidades_descontadas',
        'receta_numero',
        'receta_medico',
        'receta_foto_url',
    ];

    public function sale(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
