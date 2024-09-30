<?php

namespace App\Jobs;

use DB;
use Exception;
use App\Helpers\helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Helpers\DocumentoGeneralController;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Facturacion;
use App\Models\Portafolio\FacDocumentos;
use App\Models\Sistema\FacturacionDetalle;
use App\Models\Portafolio\DocumentosGeneral;

class ProcessFacturacionGeneralDelete implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $empresa = null;
    public $inicioMes = null;
	public $id_usuario = null;
    public $id_empresa = null;
    public $periodo_facturacion = null;

    /**
     * Create a new job instance.
	 * 
	 * @return void
     */
    public function __construct($id_usuario, $id_empresa)
    {
        $this->id_usuario = $id_usuario;
        $this->id_empresa = $id_empresa;
        $this->empresa = Empresa::find($id_empresa);
        $this->periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $this->inicioMes = date('Y-m', strtotime($this->periodo_facturacion));
    }

    /**
     * Execute the job.
	 * 
	 * @return string
     */
    public function handle()
    {
        try {            
            copyDBConnection('max', 'max');
            setDBInConnection('max', $this->empresa->token_db_maximo);

            copyDBConnection('sam', 'sam');
            setDBInConnection('sam', $this->empresa->token_db_portafolio);
           
            $query = $this->getInmueblesNitsQuery();
            $query->unionAll($this->getCuotasMultasNitsQuery(date('Y-m', strtotime($this->periodo_facturacion))));

            DB::connection('max')
                ->table(DB::raw("({$query->toSql()}) AS nits"))
                ->mergeBindings($query)
                ->select(
                    'id_nit'
                )
                ->groupByRaw('id_nit')
                ->orderByRaw('id_nit')
                ->chunk(233, function ($nits) {
                    $nits->each(function ($nit) {

                        $facturaEliminar = Facturacion::where('id_nit', $nit->id_nit)
                            ->where('fecha_manual', $this->inicioMes.'-01')
                            ->first();

                        if ($facturaEliminar) {
                            $facturaPortafolio = FacDocumentos::where('token_factura', $facturaEliminar->token_factura)->first();
                            if ($facturaPortafolio) {
                                $documento = DocumentosGeneral::where('relation_id', $facturaPortafolio->id)
                                    ->where('relation_type', 2)
                                    ->delete();
                                $facturaPortafolio->delete();
                            }
                            FacturacionDetalle::where('id_factura', $facturaEliminar->id)->delete();
                            $facturaEliminar->delete();
                        }
                    });
                });

            $urlEventoNotificacion = $this->empresa->token_db_maximo.'_'.$this->id_usuario;
            event(new PrivateMessageEvent('facturacion-rapida-'.$urlEventoNotificacion, [
                'tipo' => 'exito',
                'success' =>  true,
                'action' => 2
            ]));

		} catch (Exception $exception) {
			Log::error('ProcessFacturacionGeneralDelete al enviar facturación a PortafolioERP', [
                'message' => $exception->getMessage(),
                'line' => $exception->getLine()
            ]);
		}
    }

    private function getInmueblesNitsQuery()
    {
        return DB::connection('max')->table('inmueble_nits AS IN')
            ->select(
                'IN.id_nit'
            );
    }

    private function getCuotasMultasNitsQuery($fecha_facturar)
    {
        return DB::connection('max')->table('cuotas_multas AS CM')
            ->select(
                'CM.id_nit'
            )
            ->where("CM.fecha_inicio", '<=', $fecha_facturar)
            ->where("CM.fecha_fin", '>=', $fecha_facturar);
    }

	public function failed($exception)
	{
		Log::error('ProcessFacturacionGeneralDelete al enviar facturación a PortafolioERP', [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine()
        ]);
	}
}
