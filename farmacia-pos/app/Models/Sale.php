<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'numero_venta',
        'estado_venta',
        'caja_id',
        'turno_vendedor_id',
        'turno_cajero_id',
        'vendedor_id',
        'cajero_id',
        'cliente_tipo',
        'cliente_nit',
        'cliente_complemento',
        'cliente_razon_social',
        'subtotal',
        'descuento_total',
        'total_venta',
        'total_final',
        'metodo_pago',
        'tipo_documento',
        'factura_manual_id',
        'recibido_efectivo',
        'cambio',
        'codigo_transaccion_qr',
        'referencia_transferencia',
        'motivo_anulacion',
        'anulado_por',
        'anulado_at',
        'enviada_a_caja_at',
        'expira_at',
    ];

    protected $casts = [
        'estado_venta' => 'string',
        'total_venta' => 'decimal:2',
        'total_final' => 'decimal:2',
        'expira_at' => 'datetime',
        'enviada_a_caja_at' => 'datetime',
    ];

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function vendedor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'vendedor_id');
    }

    public function cajero(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'cajero_id');
    }

    public function caja(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CashRegister::class, 'caja_id');
    }

    public function turnoVendedor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CashShift::class, 'turno_vendedor_id');
    }

    public function turnoCajero(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CashShift::class, 'turno_cajero_id');
    }

    public function facturaManual(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ManualInvoice::class, 'factura_manual_id');
    }

    public function scopePendientes($query)
    {
        return $query->where('estado_venta', 'pendiente');
    }

    public function scopeEnCaja($query)
    {
        return $query->where('estado_venta', 'en_caja');
    }

    public function scopeCompletadas($query)
    {
        return $query->where('estado_venta', 'completada');
    }
}
