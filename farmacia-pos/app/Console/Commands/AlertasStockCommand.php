<?php

namespace App\Console\Commands;

use App\Events\StockBajoEvent;
use App\Models\Product;
use Illuminate\Console\Command;

class AlertasStockCommand extends Command
{
    protected $signature = 'stock:alertas';
    protected $description = 'Verifica productos con stock por debajo del mínimo y envía alertas';

    public function handle(): int
    {
        $productosBajoStock = Product::where('activo', true)
            ->whereColumn('stock_unidades', '<=', 'stock_minimo_alertas')
            ->get();

        if ($productosBajoStock->isEmpty()) {
            $this->info('No hay productos con stock bajo.');
            return Command::SUCCESS;
        }

        $this->warn("Se encontraron {$productosBajoStock->count()} productos con stock bajo:");

        foreach ($productosBajoStock as $producto) {
            $this->line(" - {$producto->codigo_interno}: {$producto->nombre_comercial} (stock: {$producto->stock_unidades}, mínimo: {$producto->stock_minimo_alertas})");
            StockBajoEvent::dispatch($producto, $producto->stock_unidades);
        }

        return Command::SUCCESS;
    }
}
