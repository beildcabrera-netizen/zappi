<?php

namespace App\Services\Inventario;

use App\Exceptions\StockInsuficienteException;
use App\Models\Product;

class StockService
{
    public function aUnidadesBase(string $presentacion, float $cantidad, Product $producto): int
    {
        return match($presentacion) {
            'unidad' => (int) $cantidad,
            'blister' => (int) ($cantidad * $producto->unidades_por_blister),
            'caja' => (int) ($cantidad * $producto->unidades_por_blister * $producto->blisters_por_caja),
            'frasco', 'tubo' => (int) $cantidad,
            default => throw new \InvalidArgumentException("Presentación inválida: $presentacion")
        };
    }

    public function hayStock(Product $producto, string $presentacion, float $cantidad): bool
    {
        return $producto->stock_unidades >= $this->aUnidadesBase($presentacion, $cantidad, $producto);
    }

    public function verificarStock(Product $producto, string $presentacion, float $cantidad): void
    {
        if (!$this->hayStock($producto, $presentacion, $cantidad)) {
            throw new StockInsuficienteException(
                $producto->nombre_comercial,
                $producto->stock_unidades,
                $this->aUnidadesBase($presentacion, $cantidad, $producto)
            );
        }
    }

    public function descontar(Product $producto, int $unidades): void
    {
        $producto->decrement('stock_unidades', $unidades);
    }

    public function reponer(Product $producto, int $unidades): void
    {
        $producto->increment('stock_unidades', $unidades);
    }

    public function descomponerStock(Product $producto): array
    {
        $total = $producto->stock_unidades;
        $upb = $producto->unidades_por_blister ?: 1;
        $bpc = $producto->blisters_por_caja ?: 1;
        $upc = $upb * $bpc;

        $cajas = (int) floor($total / $upc);
        $resto = $total % $upc;
        $blisters = (int) floor($resto / $upb);
        $unidades = $resto % $upb;

        return compact('cajas', 'blisters', 'unidades', 'total');
    }
}
