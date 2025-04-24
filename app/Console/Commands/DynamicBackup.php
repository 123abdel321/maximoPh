<?php

namespace App\Console\Commands;

use DB;
use Illuminate\Console\Command;
use App\Models\Clientes\Empresa;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;

class DynamicBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:databases';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Realiza backups de todas las bases de datos dinámicas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $empresas = DB::connection('clientes')
            ->table('empresas')
            ->select('razon_social', 'token_db_maximo')
            ->get();

        foreach ($empresas as $empresa) {
            $this->info("Realizando backup de: {$empresa->razon_social}");

            Config::set('database.connections.max.database', $empresa->token_db_maximo);

            Artisan::call('backup:run', [
                '--only-db' => true,
            ]);

            $this->info("Backup completado para: {$empresa->razon_social}");
        }
    }
}
