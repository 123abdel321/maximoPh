<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\BackupDatabaseJob;
//MODELS
use App\Models\Empresa\Empresa;

class BackupDatabases extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup:databases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un respaldo de todas las bases de datos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $empresasActivas = Empresa::where('estado', 1)
            ->orderBy('id', 'ASC')
            ->get();

        // Procesar en lotes más pequeños
        $empresasActivas->chunk(3)->each(function ($chunk) {
            foreach ($chunk as $empresa) {
                BackupDatabaseJob::dispatch($empresa)
                    ->delay(now()->addSeconds(rand(1, 30))); // Espaciar los jobs
            }
        });
        
        \Log::info("Se han programado backups para {$empresasActivas->count()} empresas");
    }
}
