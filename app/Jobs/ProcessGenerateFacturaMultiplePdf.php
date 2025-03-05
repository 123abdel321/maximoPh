<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use App\Models\Sistema\ArchivosCache;
use App\Events\PrivateMessageEvent;
use App\Helpers\Printers\FacturacionPdfMultiple;

class ProcessGenerateFacturaMultiplePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $timeout = 1200;

    protected $empresa;
    protected $nits;
    protected $periodo;
    protected $idZona;
    protected $idUser;

    /**
     * Create a new job instance.
     */
    public function __construct($empresa, $nits = null, $periodo = null, $idZona = null, $idUser = null)
    {
        $this->empresa = $empresa;
        $this->nits = $nits;
        $this->periodo = $periodo;
        $this->idZona = $idZona;
        $this->idUser = $idUser;
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
        
        try {
            $urlEventoNotificacion = $this->empresa->token_db_maximo.'_'.$this->idUser;

            $facturasPdf = (new FacturacionPdfMultiple($this->empresa, $this->nits, $this->periodo, $this->idZona))
                ->buildPdf()
                ->saveStorage();

            $archivo = ArchivosCache::create([
                'tipo_archivo' => '.pdf',
                'name_file' => 'facturacion.pdf',
                'relative_path' => '',
                'url_archivo' => $facturasPdf,
                'created_by' => $this->idUser,
                'updated_by' => $this->idUser
            ]);

            $urlEventoNotificacion = $this->empresa->token_db_maximo.'_'.$this->idUser;
            event(new PrivateMessageEvent('facturacion-factura-'.$urlEventoNotificacion, [
                'tipo' => 'exito',
                'urf_factura' => $facturasPdf,
                'success' =>  true,
                'action' => 3
            ]));
        } catch (Exception $exception) {
			Log::error('Error al generar PDF de facturación', [
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'file' => $exception->getFile(),
                'user' => $this->idUser,
                'empresa' => $this->empresa->id,
            ]);

            throw $exception;
		}
    }
}
