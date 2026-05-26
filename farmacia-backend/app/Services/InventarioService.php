<?php

namespace App\Services;

use App\Models\Lote;
use App\Models\Producto;
use Illuminate\Support\Collection;

class InventarioService
{
    public function descontarStock(int $productoId, int $cantidad): Collection
    {
        $lotes = Lote::where('producto_id', $productoId)
            ->where('stock_actual', '>', 0)
            ->where('fecha_vencimiento', '>=', now())
            ->orderBy('fecha_vencimiento')
            ->get();

        $descontados = collect();
        $pendiente = $cantidad;

        foreach ($lotes as $lote) {
            if ($pendiente <= 0) break;

            $aDescontar = min($lote->stock_actual, $pendiente);
            $lote->decrement('stock_actual', $aDescontar);
            $pendiente -= $aDescontar;

            $descontados->push([
                'lote_id' => $lote->id,
                'cantidad' => $aDescontar,
            ]);
        }

        if ($pendiente > 0) {
            throw new \RuntimeException("Stock insuficiente para el producto ID {$productoId}. Faltan {$pendiente} unidades.");
        }

        return $descontados;
    }

    public function agregarStock(int $productoId, string $numeroLote, string $fechaVencimiento, int $cantidad, float $precioCompra): Lote
    {
        $lote = Lote::where('producto_id', $productoId)
            ->where('numero_lote', $numeroLote)
            ->first();

        if ($lote) {
            $lote->increment('stock_actual', $cantidad);
            $lote->increment('stock_inicial', $cantidad);
            return $lote;
        }

        return Lote::create([
            'producto_id' => $productoId,
            'numero_lote' => $numeroLote,
            'fecha_vencimiento' => $fechaVencimiento,
            'stock_actual' => $cantidad,
            'stock_inicial' => $cantidad,
            'precio_compra' => $precioCompra,
            'fecha_recepcion' => now(),
        ]);
    }

    public function alertasStockBajo(): Collection
    {
        return Producto::where('activo', true)
            ->get()
            ->filter(fn(Producto $p) => $p->stock_total <= $p->stock_minimo)
            ->values();
    }

    public function alertasLotesPorVencer(int $dias = 30): Collection
    {
        return Lote::where('activo', true)
            ->where('stock_actual', '>', 0)
            ->whereBetween('fecha_vencimiento', [now(), now()->addDays($dias)])
            ->with('producto')
            ->orderBy('fecha_vencimiento')
            ->get();
    }

    public function alertasLotesVencidos(): Collection
    {
        return Lote::where('activo', true)
            ->where('stock_actual', '>', 0)
            ->where('fecha_vencimiento', '<', now())
            ->with('producto')
            ->orderBy('fecha_vencimiento')
            ->get();
    }

    public function ajustarStock(int $loteId, int $nuevoStock): Lote
    {
        $lote = Lote::findOrFail($loteId);
        $lote->update(['stock_actual' => $nuevoStock]);
        return $lote;
    }

    public function registrarMerma(int $loteId, int $cantidad, string $motivo): Lote
    {
        $lote = Lote::findOrFail($loteId);

        if ($lote->stock_actual < $cantidad) {
            throw new \RuntimeException("Stock insuficiente en el lote para realizar la merma.");
        }

        $lote->decrement('stock_actual', $cantidad);
        return $lote;
    }
}
