<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Talonario extends Model
{
    protected $fillable = [
        'numero_autorizacion',
        'numero_tramite',
        'sucursal',
        'actividad_economica',
        'fecha_autorizacion',
        'fecha_limite_emision',
        'rango_inicio',
        'rango_fin',
        'siguiente_numero',
        'cantidad_solicitada',
        'pin_entrega',
        'fecha_activacion',
        'estado',
    ];

    public function facturas(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ManualInvoice::class, 'talonario_id');
    }
}
