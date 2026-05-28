<?php

namespace App\Console\Commands;

use App\Jobs\ExpirarVentasEnCaja;
use Illuminate\Console\Command;

class ExpirarVentasCommand extends Command
{
    protected $signature = 'ventas:expirar';
    protected $description = 'Expira ventas en caja cuyo tiempo límite haya pasado';

    public function handle(): int
    {
        $this->info('Expriando ventas vencidas en caja...');

        ExpirarVentasEnCaja::dispatch();

        $this->info('Ventas expiradas correctamente.');

        return Command::SUCCESS;
    }
}
