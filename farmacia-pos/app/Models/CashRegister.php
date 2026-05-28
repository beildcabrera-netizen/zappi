<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'ubicacion', 'impresora_tickets', 'modo_override', 'activa'];

    protected $casts = [
        'activa' => 'boolean',
    ];

    public function turnos(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CashShift::class, 'cash_register_id');
    }

    public function ventas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sale::class, 'caja_id');
    }
}
