<?php

use App\Console\Commands\AlertasStockCommand;
use App\Console\Commands\BackupDatabaseCommand;
use App\Console\Commands\ExpirarVentasCommand;
use App\Jobs\ExpirarVentasEnCaja;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(ExpirarVentasCommand::class)->everyMinute();
Schedule::command(AlertasStockCommand::class)->hourly();
Schedule::command(BackupDatabaseCommand::class)->dailyAt('23:00');
