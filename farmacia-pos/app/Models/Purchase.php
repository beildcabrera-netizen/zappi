<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $fillable = [
        'numero_orden',
        'supplier_id',
        'fecha_orden',
        'fecha_recepcion',
        'estado',
        'monto_total',
        'observaciones',
        'user_id',
    ];

    protected $casts = [
        'fecha_orden' => 'date',
        'fecha_recepcion' => 'date',
    ];

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
