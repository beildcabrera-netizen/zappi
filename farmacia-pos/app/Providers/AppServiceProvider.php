<?php

namespace App\Providers;

use App\Contracts\Repositories\ProductoRepositoryInterface;
use App\Contracts\Repositories\TurnoRepositoryInterface;
use App\Contracts\Repositories\VentaRepositoryInterface;
use App\Events\StockBajoEvent;
use App\Listeners\NotificarStockBajoListener;
use App\Repositories\ProductoRepository;
use App\Repositories\TurnoRepository;
use App\Repositories\VentaRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductoRepositoryInterface::class, ProductoRepository::class);
        $this->app->bind(VentaRepositoryInterface::class, VentaRepository::class);
        $this->app->bind(TurnoRepositoryInterface::class, TurnoRepository::class);
    }

    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        Event::listen(
            StockBajoEvent::class,
            NotificarStockBajoListener::class,
        );
    }
}
