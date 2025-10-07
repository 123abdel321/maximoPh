<?php

namespace App\Jobs;


use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use App\Models\Sistema\ArchivosCache;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Helpers\Printers\FacturacionPdfMultiple;

class ProcessGenerateFacturaMultiplePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;
    public $tries = 5;
    public $maxExceptions = 3;
    protected $empresa;
    protected $nits;
    protected $periodo;
    protected $idZona;
    protected $idUser;
    protected $jobIndex;
    protected $totalChunks;

    /**
     * Create a new job instance.
     */
    public function __construct($empresa, $nits = null, $periodo = null, $idZona = null, $idUser = null, $jobIndex = null, $totalChunks = null)
    {
        $this->empresa = $empresa;
        $this->nits = $nits;
        $this->periodo = $periodo;
        $this->idZona = $idZona;
        $this->idUser = $idUser;
        $this->jobIndex = $jobIndex;
        $this->totalChunks = $totalChunks;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        copyDBConnection('max', 'max');
        setDBInConnection('max', $this->empresa->token_db_maximo);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $this->empresa->token_db_portafolio);
        
        $urlEventoNotificacion = $this->empresa->token_db_maximo.'_'.$this->idUser;
        $facturasPdf = null;

        try {

            $printer = (new FacturacionPdfMultiple($this->empresa, $this->nits, $this->periodo, $this->idZona))
                ->buildPdf();

            // 1. Generar nombre de archivo dinámico basado en la partición
            $baseName = 'facturacion_' . $this->periodo;
            $fileSuffix = $this->jobIndex && $this->totalChunks 
                ? "_Parte_{$this->jobIndex}_de_{$this->totalChunks}" 
                : "";

            $newFileName = $baseName . $fileSuffix;
            $printer->name = $newFileName . '_' . uniqid();
            $facturasPdf = $printer->saveStorage();

            $archivo = ArchivosCache::create([
                'tipo_archivo' => '.pdf',
                'name_file' => $printer->name . '.pdf', 
                'relative_path' => '',
                'url_archivo' => $facturasPdf,
                'created_by' => $this->idUser,
                'updated_by' => $this->idUser
            ]);

            // 3. Generar mensaje de notificación claro para el usuario (ej: Parte 1 de 9)
            $messageSuffix = $this->jobIndex && $this->totalChunks 
                ? " (Parte {$this->jobIndex} de {$this->totalChunks})"
                : "";

            event(new PrivateMessageEvent('facturacion-factura-'.$urlEventoNotificacion, [
                'tipo' => 'exito',
                'urf_factura' => $facturasPdf,
                'success' => true,
                'action' => 3,
                'message' => 'Factura generada exitosamente' . $messageSuffix
            ]));
        } catch (Exception $exception) {
			Log::error('Error al generar PDF de facturación', [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
                'user' => $this->idUser,
                'empresa' => $this->empresa->id,
            ]);
		}
    }

    public function failed($exception)
    {
        $urlEventoNotificacion = $this->empresa->token_db_maximo.'_'.$this->idUser;
        $messageSuffix = $this->jobIndex && $this->totalChunks 
            ? " (Parte {$this->jobIndex} de {$this->totalChunks})"
            : "";
            
        Log::error('Fallo permanente al generar PDF de facturación' . $messageSuffix, [
            'error' => $exception->getMessage(),
            'line' => $exception->getLine(),
            'file' => $exception->getFile(),
            'user' => $this->idUser,
            'empresa' => $this->empresa->id,
        ]);

        event(new PrivateMessageEvent('facturacion-factura-'.$urlEventoNotificacion, [
            'tipo' => 'error',
            'success' => false,
            'action' => 4,
            'message' => 'Fallo al generar la factura' . $messageSuffix . '. Contacte soporte.'
        ]));
    }
}
