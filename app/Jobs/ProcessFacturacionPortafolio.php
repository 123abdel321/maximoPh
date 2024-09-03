<?php

namespace App\Jobs;

use DB;
use Exception;
use App\Helpers\helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Helpers\PortafolioERP\FacturacionERP;
//MODELS
use App\Models\Empresas\Empresa;
use App\Models\Sistema\Facturacion;

class ProcessFacturacionPortafolio implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

	public $periodoFacturar;
	public $idEmpresa;

    /**
     * Create a new job instance.
	 * 
	 * @return void
     */
    public function __construct($idEmpresa, $periodoFacturar)
    {
        $this->idEmpresa = $id_empresa;
        $this->periodoFacturar = $periodoFacturar;
    }

    /**
     * Execute the job.
	 * 
	 * @return string
     */
    public function handle()
    {
        try {

			(new FacturacionERP(
                $periodo_facturacion
            ))->send();

		} catch (Exception $exception) {
			Log::error('ProcessFacturacionPortafolio al enviar facturación a PortafolioERP', ['message' => $exception->getMessage()]);
		}
    }

	public function failed($exception)
	{
		Log::error('ProcessFacturacionPortafolio al enviar facturación a PortafolioERP', ['message' => $exception->getMessage()]);

		$this->dropDb($this->empresa->token_db_maximo);
	}
}
