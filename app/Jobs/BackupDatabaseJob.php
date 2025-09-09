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
        try {
            // 1. Configurar conexión a la BD
            copyDBConnection('max', $this->empresa->token_db_maximo);
            setDBInConnection('max', $this->empresa->token_db_maximo);

            // 2. Crear directorio temporal si no existe
            $tempDir = storage_path('app/temp');
            if (!File::exists($tempDir)) {
                File::makeDirectory($tempDir, 0755, true);
            }

            // 3. Generar nombre de archivo
            $filename = "{$this->empresa->token_db_maximo}_" . date('Y_m_d_H_i_s') . ".sql.gz";
            $filePath = $tempDir . '/' . $filename;
            
            // 4. Ejecutar mysqldump
            $dbConfig = config("database.connections.max");
            $command = sprintf(
                'mysqldump --host=%s --port=25060 --user=%s --password=%s %s | gzip > %s',
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

            // 5. Subir a Digital Ocean Spaces
            $fileToUpload = new SymfonyFile($filePath);
            
            Storage::disk('do_spaces')->putFileAs(
                'backups-maximoph',
                $fileToUpload, // Pasar la instancia de archivo, no la facade
                $filename,
                ['visibility' => 'public']
            );

            $fileUrl = Storage::disk('do_spaces')->url("backups-portafolioerp/{$filename}");

            // 6. Registrar en base de datos con manejo de error 1615
            $this->registerBackupWithRetry($filename, $fileUrl);

            // 7. Limpiar backups antiguos
            $this->cleanOldBackups();

            // 8. Eliminar archivo temporal
            File::delete($filePath);

            \Log::info("Backup generado de {$this->empresa->razon_social}");

        } catch (\Exception $e) {
            \Log::error("Error generando backup: " . $e->getMessage());
        }
    }

    protected function registerBackupWithRetry($filename, $url, $maxAttempts = 3)
    {
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            try {
                $attempt++;
                
                $now = now();
                
                DB::connection('clientes')->table('backup_empresas')->insert([
                    'id_empresa' => $this->empresa->id,
                    'url_file' => $url,
                    'file_name' => $filename,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
                
                // Si llega aquí, tuvo éxito
                return;
                
            } catch (\Exception $e) {
                if ($attempt >= $maxAttempts) {
                    \Log::error("Error después de {$maxAttempts} intentos al registrar backup: " . $e->getMessage());
                    return;
                }
                
                // Verificar si es el error 1615 específico
                if (strpos($e->getMessage(), 'Prepared statement needs to be re-prepared') !== false) {
                    \Log::warning("Error 1615 detectado, reintentando registro (intento {$attempt})");
                    
                    // Forzar reconexión a la base de datos
                    DB::connection('clientes')->reconnect();
                    
                    // Esperar un momento antes de reintentar
                    sleep(1);
                    continue;
                }
                
                // Si es otro error, no reintentar
                \Log::error("Error diferente al 1615 al registrar backup: " . $e->getMessage());
                return;
            }
        }
    }

    protected function cleanOldBackups()
    {
        try {
            $backups = DB::connection('clientes')
                ->table('backup_empresas')
                ->where('id_empresa', $this->empresa->id)
                ->orderBy('created_at', 'desc')
                ->get();
                
            if ($backups->count() > $this->maxBackups) {
                // Obtener los IDs de los backups que deben ser eliminados (los más antiguos)
                $backupsToDelete = $backups->slice($this->maxBackups)->pluck('id');
                
                // Eliminar los backups más antiguos
                foreach ($backupsToDelete as $backupId) {
                    $backup = $backups->firstWhere('id', $backupId);
                    if ($backup) {
                        $this->deleteBackupWithRetry($backup);
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error("Error obteniendo backups antiguos: " . $e->getMessage());
        }
    }

    protected function deleteBackupWithRetry($backup, $maxAttempts = 3)
    {
        $attempt = 0;
        
        while ($attempt < $maxAttempts) {
            try {
                $attempt++;
                
                $path = 'backups-portafolioerp/' . $backup->file_name;

                if (empty(trim($path))) {
                    \Log::error("Path vacío para backup ID: {$backup->id}");
                    return;
                }

                // Eliminar archivo de backup
                if (Storage::disk('do_spaces')->exists($path)) {
                    Storage::disk('do_spaces')->delete($path);
                }

                // Eliminar registro de la base de datos
                DB::connection('clientes')
                    ->table('backup_empresas')
                    ->where('id', $backup->id)
                    ->delete();
                
                // Si llega aquí, tuvo éxito
                return;
                
            } catch (\Exception $e) {
                if ($attempt >= $maxAttempts) {
                    \Log::error("Error después de {$maxAttempts} intentos al eliminar backup {$backup->id}: " . $e->getMessage());
                    return;
                }
                
                // Verificar si es el error 1615 específico
                if (strpos($e->getMessage(), 'Prepared statement needs to be re-prepared') !== false) {
                    \Log::warning("Error 1615 detectado, reintentando eliminación (intento {$attempt}) para backup {$backup->id}");
                    
                    // Forzar reconexión a la base de datos
                    DB::connection('clientes')->reconnect();
                    
                    // Esperar un momento antes de reintentar
                    sleep(1);
                    continue;
                }
                
                // Si es otro error, no reintentar
                \Log::error("Error diferente al 1615 al eliminar backup {$backup->id}: " . $e->getMessage());
                return;
            }
        }
    }
}