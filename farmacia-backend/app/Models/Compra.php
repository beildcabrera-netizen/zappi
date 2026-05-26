<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    protected $fillable = [
        'numero_factura', 'proveedor_id', 'nit_proveedor',
        'nombre_proveedor', 'subtotal', 'descuento', 'total',
        'credito_fiscal', 'fecha_compra', 'notas', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'descuento' => 'decimal:2',
            'total' => 'decimal:2',
            'credito_fiscal' => 'decimal:2',
            'fecha_compra' => 'date',
        ];
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }
}
