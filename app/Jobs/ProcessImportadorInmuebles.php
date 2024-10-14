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
use App\Models\Sistema\Inmueble;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\InmueblesImport;

class ProcessImportadorInmuebles implements ShouldQueue
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

            $inmueblesImport = InmueblesImport::with('inmueble.personas')
                ->where('estado', 0)
                ->get();
                
            //RECORREMOS CUOTAS EXTRAS & MULTAS
            foreach ($inmueblesImport as $inmuebleIm) {
                $inmueble = (object)['id' => null];
                //CREATE OR UPDATE INMUEBLE
                if ($inmuebleIm->id_inmueble) {
                    Inmueble::where('id', $inmuebleIm->id_inmueble)
                        ->update([
                            'area' => $inmuebleIm->area,
                            'coeficiente' => $inmuebleIm->coheficiente,
                            'id_zona' => $inmuebleIm->id_zona,
                            'id_concepto_facturacion' => $inmuebleIm->id_concepto_facturacion,
                            'valor_total_administracion' => $inmuebleIm->valor_administracion,
                        ]);
                    $inmueble = Inmueble::find($inmuebleIm->id_inmueble);
                } else {
                    
                    $inmueble = Inmueble::create([
                        'nombre' => $inmuebleIm->nombre_inmueble,
                        'area' => $inmuebleIm->area,
                        'coeficiente' => $inmuebleIm->coheficiente,
                        'id_zona' => $inmuebleIm->id_zona,
                        'id_concepto_facturacion' => $inmuebleIm->id_concepto_facturacion,
                        'valor_total_administracion' => $inmuebleIm->valor_administracion,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);
                }
                $inmueblesNitsExistentes = InmuebleNit::where('id_inmueble', $inmuebleIm->id_inmueble)->get();
                //CREATE OR UPDATE PROPIETARIO
                $porcentajeAdmin = $inmuebleIm->porcentaje_administracion ? $inmuebleIm->porcentaje_administracion : 100;
                $valorAdmin = $inmuebleIm->valor_administracion;
                if ($inmuebleIm->id_nit) {

                    if ($inmueble && is_object($inmueble) && $inmueble->id) {

                        InmuebleNit::where('id_inmueble', $inmueble->id)
                            ->where('id_nit', $inmuebleIm->id_nit)
                            ->updateOrCreate([
                                'id_nit' => $inmuebleIm->id_nit,
                                'id_inmueble' => $inmueble->id,
                                'porcentaje_administracion' => $porcentajeAdmin,
                                'valor_total' => $valorAdmin * ($porcentajeAdmin / 100),
                                'tipo' => $inmuebleIm->tipo,
                                'created_by' => request()->user()->id,
                                'updated_by' => request()->user()->id
                            ]);
                    } else {
                        return response()->json([
                            "success"=>false,
                            'data' => [],
                            "message"=>["Inmueble" => ["El inmueble no existe, registro No. ".$inmuebleIm->id]]
                        ], 422);
                    }
                } else if (count($inmueblesNitsExistentes)) {

                    foreach ($inmueblesNitsExistentes as $key => $inmuebleNit) {
                        InmuebleNit::where('id', $inmuebleNit->id)
                            ->update([
                                'porcentaje_administracion' => $porcentajeAdmin,
                                'valor_total' => $valorAdmin * ($porcentajeAdmin / 100),
                                'updated_by' => request()->user()->id
                            ]);
                    }
                }
                $inmuebleIm->estado = 5;
                $inmuebleIm->save();
            }

            InmueblesImport::truncate();

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