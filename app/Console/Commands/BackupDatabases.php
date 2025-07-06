<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\BackupDatabaseJob;
//MODELS
use App\Models\Empresas\Empresa;

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
    
        foreach ($empresasActivas as $empresa) {
            BackupDatabaseJob::dispatch($empresa);
        }
        
        \Log::info("Se han programado backups para {$empresasActivas->count()} empresas");
    }
}
