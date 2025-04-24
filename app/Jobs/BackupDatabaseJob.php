<?php

namespace App\Jobs;

use Illuminate\Http\File;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BackupDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $tokenDatabase;

    public function __construct($tokenDatabase)
    {
        $this->tokenDatabase = $tokenDatabase;
    }

    public function handle(): void
    {
        copyDBConnection('max', $this->tokenDatabase);
        setDBInConnection('max', $this->tokenDatabase);

        $tables = DB::connection('max')->select('SHOW TABLES');
        $tables = array_map(fn($table) => reset($table), $tables);

        // Nombre del archivo con fecha y hora
        $filename = "{$this->tokenDatabase}_" . date('Y_m_d_H') . ".sql.gz";
        $filePath = storage_path("app/tmp/{$filename}");

        info('backup: ' . $this->tokenDatabase);

        // Configuración de la base de datos
        $dbConfig = config("database.connections.max");

        $command = "mysqldump --host={$dbConfig['host']} --user={$dbConfig['username']} --password={$dbConfig['password']} {$this->tokenDatabase} | gzip > {$filePath}";

        exec($command, $output, $resultCode);

        if ($resultCode !== 0) {
            \Log::error("Error al generar el backup para {$this->tokenDatabase}");
            return;
        }
        
        Storage::disk('do_spaces')->putFileAs("backups-maximoph", new File($filePath), $filename, 'public');

        unlink($filePath);

        info('backup: Finalizado con exito!');
    }
}
