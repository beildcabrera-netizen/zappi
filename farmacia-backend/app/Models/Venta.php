<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $fillable = [
        'codigo_factura', 'cliente_id', 'nombre_cliente',
        'documento_cliente', 'subtotal', 'descuento', 'total',
        'iva', 'ice', 'iehd', 'ipj', 'metodo_pago',
        'estado', 'notas', 'user_id',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'descuento' => 'decimal:2',
            'total' => 'decimal:2',
            'iva' => 'decimal:2',
            'ice' => 'decimal:2',
            'iehd' => 'decimal:2',
            'ipj' => 'decimal:2',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function registroSin()
    {
        return $this->hasOne(RegistroVenta::class);
    }
}
