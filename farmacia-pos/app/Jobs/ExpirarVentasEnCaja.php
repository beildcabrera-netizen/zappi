<?php

namespace App\Jobs;

use App\Models\Sale;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ExpirarVentasEnCaja implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $expiradas = Sale::where('estado_venta', 'en_caja')
            ->where('expira_at', '<=', now())
            ->with('items.product')
            ->get();

        foreach ($expiradas as $venta) {
            DB::transaction(function () use ($venta) {
                foreach ($venta->items as $item) {
                    $item->product->increment('stock_unidades', $item->unidades_descontadas);
                }
                $venta->update(['estado_venta' => 'expirada']);
            });
        }
    }
}
