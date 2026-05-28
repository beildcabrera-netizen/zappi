<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualInvoice extends Model
{
    protected $fillable = [
        'talonario_id',
        'numero_factura',
        'numero_completo',
        'codigo_autorizacion',
        'codigo_control',
        'fecha_emision',
        'nit_cliente',
        'complemento',
        'razon_social_cliente',
        'importe_total',
        'importe_ice',
        'importe_iehd',
        'importe_ipj',
        'importe_tasas',
        'importe_otros_no_sujeto_iva',
        'exportaciones',
        'ventas_tasa_cero',
        'subtotal',
        'descuentos',
        'importe_gift_card',
        'base_debito_fiscal',
        'debito_fiscal',
        'estado',
        'vendedor_id',
        'caja_id',
        'sale_id',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
    ];

    public function talonario(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Talonario::class, 'talonario_id');
    }

    public function sale(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function vendedor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function caja(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CashRegister::class, 'caja_id');
    }
}
