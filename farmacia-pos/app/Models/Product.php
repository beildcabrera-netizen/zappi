<?php

namespace App\Models;

use App\Traits\HasAuditableChanges;
use App\Traits\HasCodigoInterno;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, HasAuditableChanges, HasCodigoInterno;

    protected $fillable = [
        'codigo_barras',
        'codigo_interno',
        'nombre_comercial',
        'nombre_generico',
        'principio_activo',
        'concentracion',
        'forma_farmaceutica',
        'laboratorio',
        'registro_sanitario',
        'presentacion_entrada',
        'unidades_por_blister',
        'blisters_por_caja',
        'fraccionamiento_habilitado',
        'precio_venta_unidad',
        'precio_venta_blister',
        'precio_venta_caja',
        'costo_compra_unidad',
        'stock_unidades',
        'stock_blisters',
        'stock_cajas',
        'stock_minimo_alertas',
        'estante',
        'seccion',
        'ubicacion_detalle',
        'controlado',
        'tipo_controlado',
        'refrigerado',
        'foto_url',
        'activo',
    ];

    protected $casts = [
        'fraccionamiento_habilitado' => 'boolean',
        'controlado' => 'boolean',
        'refrigerado' => 'boolean',
        'activo' => 'boolean',
        'precio_venta_unidad' => 'decimal:2',
        'precio_venta_blister' => 'decimal:2',
        'precio_venta_caja' => 'decimal:2',
        'costo_compra_unidad' => 'decimal:2',
    ];

    protected $appends = ['stock_cajas_calc', 'stock_blisters_calc', 'stock_bajo'];

    public function saleItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function getStockCajasCalcAttribute(): int
    {
        if (!$this->blisters_por_caja) {
            return 0;
        }
        $unidadesPorCaja = $this->unidades_por_blister * $this->blisters_por_caja;
        return (int) floor($this->stock_unidades / $unidadesPorCaja);
    }

    public function getStockBlistersCalcAttribute(): int
    {
        if (!$this->unidades_por_blister || !$this->blisters_por_caja) {
            return 0;
        }
        $unidadesPorCaja = $this->unidades_por_blister * $this->blisters_por_caja;
        $restoCajas = $this->stock_unidades % $unidadesPorCaja;
        return (int) floor($restoCajas / $this->unidades_por_blister);
    }

    public function getStockBajoAttribute(): bool
    {
        return $this->stock_unidades <= $this->stock_minimo_alertas;
    }
}
