<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'product_id',
        'nombre_producto_temp',
        'presentacion_comprada',
        'cantidad',
        'costo_unitario',
        'costo_unidad_base',
        'lote',
        'fecha_vencimiento',
        'estante_destino',
        'seccion_destino',
        'recibido',
        'cantidad_recibida',
    ];

    protected $casts = [
        'fecha_vencimiento' => 'date',
        'recibido' => 'boolean',
    ];

    public function purchase(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
