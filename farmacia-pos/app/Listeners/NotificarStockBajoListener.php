<?php

namespace App\Listeners;

use App\Events\StockBajoEvent;
use Illuminate\Support\Facades\Log;

class NotificarStockBajoListener
{
    public function handle(StockBajoEvent $event): void
    {
        Log::warning("Stock bajo para el producto {$event->product->nombre_comercial} ({$event->product->codigo_interno}). Stock actual: {$event->stockActual}");
    }
}
