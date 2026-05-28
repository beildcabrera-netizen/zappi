<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftConfig extends Model
{
    protected $fillable = [
        'nombre',
        'hora_inicio',
        'hora_fin',
        'dias_semana',
        'modo_operacion',
        'cajas_activas',
        'min_personal',
        'prioridad',
        'activo',
    ];

    protected $casts = [
        'dias_semana' => 'array',
        'cajas_activas' => 'array',
        'activo' => 'boolean',
    ];
}
