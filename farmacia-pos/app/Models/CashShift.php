<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashShift extends Model
{
    use HasFactory;
    protected $fillable = [
        'cash_register_id',
        'user_id',
        'tipo_turno',
        'fecha_apertura',
        'fecha_cierre',
        'monto_inicial',
        'monto_final_declarado',
        'monto_final_calculado',
        'diferencia',
        'estado',
        'ventas_propias_count',
        'ventas_cobradas_otros_count',
        'total_ventas_propias',
        'total_ventas_otros',
        'observaciones_cierre',
        'cerrado_por',
    ];

    protected $casts = [
        'monto_inicial' => 'decimal:2',
        'monto_final_declarado' => 'decimal:2',
        'monto_final_calculado' => 'decimal:2',
        'diferencia' => 'decimal:2',
        'total_ventas_propias' => 'decimal:2',
        'total_ventas_otros' => 'decimal:2',
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
    ];

    public function caja(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CashRegister::class, 'cash_register_id');
    }

    public function usuario(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cerrador(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'cerrado_por');
    }

    public function ventasPropias(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sale::class, 'turno_vendedor_id');
    }

    public function ventasCobradas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sale::class, 'turno_cajero_id');
    }
}
