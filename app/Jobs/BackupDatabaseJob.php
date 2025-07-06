<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
//MODEL
use App\Models\Empresas\BackupEmpresa;


class BackupDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $empresa;
    protected $maxBackups = 10;

    public function __construct($empresa)
    {
        $this->empresa = $empresa;
    }

    public function handle()
    {
        // 1. Configurar conexión a la BD
        copyDBConnection('max', $this->empresa->token_db);
        setDBInConnection('max', $this->empresa->token_db);

        // 2. Crear directorio temporal si no existe
        $tempDir = storage_path('app/temp');
        if (!File::exists($tempDir)) {
            File::makeDirectory($tempDir, 0755, true);
        }

        // 3. Generar nombre de archivo
        $filename = "{$this->empresa->token_db}_" . date('Y_m_d_H_i_s') . ".sql.gz";
        $filePath = $tempDir . '/' . $filename;
        
        // 4. Ejecutar mysqldump
        $dbConfig = config("database.connections.max");
        $command = sprintf(
            'mysqldump --host=%s --user=%s --password=%s %s | gzip > %s',
            escapeshellarg($dbConfig['host']),
            escapeshellarg($dbConfig['username']),
            escapeshellarg($dbConfig['password']),
            escapeshellarg($this->empresa->token_db),
            escapeshellarg($filePath)
        );
        
        exec($command, $output, $resultCode);

        if ($resultCode !== 0) {
            throw new \RuntimeException("Falló mysqldump: " . implode("\n", $output));
        }

        // 5. Subir a Digital Ocean Spaces (CORRECCIÓN CLAVE)
        $fileToUpload = new SymfonyFile($filePath); // Usar SymfonyFile aquí
        
        Storage::disk('do_spaces')->putFileAs(
            'backups-maximoph',
            $fileToUpload, // Pasar la instancia de archivo, no la facade
            $filename,
            ['visibility' => 'public']
        );

        // 6. Registrar en base de datos
        $this->registerBackup(
            $filename,
            Storage::disk('do_spaces')->url("backups-maximoph/{$filename}")
        );

        // 7. Limpiar backups antiguos
        $this->cleanOldBackups();

        // 8. Eliminar archivo temporal
        File::delete($filePath);
    }

    protected function registerBackup($filename, $url)
    {
        BackupEmpresa::create([
            'id_empresa' => $this->empresa->id,
            'url_file' => $url,
            'file_name' => $filename
        ]);
    }

    protected function cleanOldBackups()
    {
        $backups = BackupEmpresa::where('id_empresa', $this->empresa->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        if ($backups->count() > $this->maxBackups) {
            $oldestBackups = $backups->slice($this->maxBackups);
            
            foreach ($oldestBackups as $backup) {
                try {
                    $path = str_replace(
                        Storage::disk('do_spaces')->url(''),
                        '',
                        $backup->url_file
                    );
                    Storage::disk('do_spaces')->delete($path);
                    $backup->delete();
                } catch (\Exception $e) {
                    \Log::error("Error eliminando backup antiguo: " . $e->getMessage());
                }
            }
        }
    }
}
