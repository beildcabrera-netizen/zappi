<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'backup:run-farmacia
                            {--only-db : Only backup the database, not files}';
    protected $description = 'Ejecuta una copia de seguridad de la base de datos y archivos';

    public function handle(): int
    {
        $this->info('Iniciando copia de seguridad...');

        if ($this->option('only-db')) {
            $exitCode = Artisan::call('backup:run', ['--only-db' => true, '--no-notification' => true]);
        } else {
            $exitCode = Artisan::call('backup:run', ['--no-notification' => true]);
        }

        if ($exitCode === 0) {
            $this->info('Copia de seguridad completada exitosamente.');
            return Command::SUCCESS;
        }

        $this->error('Error al realizar la copia de seguridad.');
        return Command::FAILURE;
    }
}
