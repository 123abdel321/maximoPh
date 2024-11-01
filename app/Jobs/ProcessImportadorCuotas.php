<?php

namespace App\Jobs;

use DB;
use Exception;
use Carbon\Carbon;
use App\Helpers\helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
//MODELS
use App\Models\Sistema\CuotasMultas;
use App\Models\Sistema\CuotasMultasImport;

class ProcessImportadorCuotas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $empresa = null;
    public $user_id = null;

    public function __construct($empresa, $user_id)
    {
        $this->empresa = $empresa;
        $this->user_id = $user_id;
    }

    public function handle()
    {
        
        try {            
            copyDBConnection('max', 'max');
            setDBInConnection('max', $this->empresa->token_db_maximo);

            copyDBConnection('sam', 'sam');
            setDBInConnection('sam', $this->empresa->token_db_portafolio);

            $cuotasMultas = CuotasMultasImport::where('estado', 0)
                ->get();
                
            //RECORREMOS CUOTAS EXTRAS & MULTAS
            foreach ($cuotasMultas as $cuota) {
                CuotasMultas::create([
                    'id_nit' => $cuota->id_nit,
                    'id_inmueble' => $cuota->id_inmueble,
                    'tipo_concepto' => 1,
                    'id_concepto_facturacion' => $cuota->id_concepto_facturacion,
                    'fecha_inicio' => $cuota->fecha_inicio,
                    'fecha_fin' => $cuota->fecha_fin,
                    'valor_total' => $cuota->valor_total,
                    'observacion' => '',
                    'created_by' => $this->user_id,
                    'updated_by' => $this->user_id,
                ]);
            }

            CuotasMultasImport::truncate();

        } catch (Exception $exception) {
			Log::error('ProcessImportarRecibos', [
                'message' => $exception->getMessage(),
                'line' => $exception->getLine()
            ]);
		}
    }

    public function failed($exception)
	{
		Log::error('ProcessImportarRecibos', [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine()
        ]);
	}

}