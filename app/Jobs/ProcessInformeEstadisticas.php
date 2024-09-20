<?php

namespace App\Jobs;

use DB;
use Illuminate\Bus\Queueable;
use App\Events\PrivateMessageEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
//MODELS
use App\Models\Sistema\Inmueble;
use App\Models\Sistema\InmuebleNit;
use App\Models\Empresa\Empresa;
use App\Models\Informes\InfEstadisticas;


class ProcessInformeEstadisticas implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;
    public $id_usuario;
	public $id_empresa;
    public $id_estadistica = 0;
    public $estadisticaCollection = [];

    /**
     * Create a new job instance.
     */
    public function __construct($request, $id_usuario, $id_empresa)
    {
        $this->request = $request;
        $this->id_usuario = $id_usuario;
		$this->id_empresa = $id_empresa;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $empresa = Empresa::find($this->id_empresa);

        copyDBConnection('max', 'max');
        setDBInConnection('max', $empresa->token_db_maximo);

        copyDBConnection('sam', 'sam');
        setDBInConnection('sam', $empresa->token_db_portafolio);

        DB::connection('informes')->beginTransaction();

        try {
            
            $estadistica = InfEstadisticas::create([
				'id_empresa' => $this->id_empresa,
				'id_nit' => $this->request['id_nit'],
				'id_zona' => $this->request['id_zona'],
				'id_concepto_facturacion' => $this->request['id_concepto_facturacion'],
				'fecha_desde' => $this->request['fecha_desde'],
				'fecha_hasta' => $this->request['fecha_hasta'],
				'agrupar' => $this->request['agrupar'],
				'detalle' => $this->request['detalle'],
			]);
            
            $this->id_estadistica = $estadistica->id;

            $dataTotal = [
                'id_estadisticas' => $this->id_estadistica,
                'id_nit' => '',
                'id_cuenta' => '',
                'total_area' => '',
                'total_coheficiente' => '',
                'saldo_anterior' => 0,
                'total_facturas' => 0,
                'total_abono' => 0,
                'saldo' => 0,
                'total' => 2,
            ];

            $nits = $this->getInmueblesMemo();

            foreach ($nits as $id_nit => $cuentas) {
                
                $query = $this->carteraDocumentosQuery($id_nit, $cuentas);
                $query->unionAll($this->carteraAnteriorQuery($id_nit, $cuentas));

                $cabeza = DB::connection('sam')
                    ->table(DB::raw("({$query->toSql()}) AS cartera"))
                    ->mergeBindings($query)
                    ->select(
                        'id_nit',
                        'id_cuenta',
                        'documento_referencia',
                        'id_centro_costos',
                        'consecutivo',
                        'concepto',
                        'fecha_manual',
                        'created_at',
                        'fecha_creacion',
                        'fecha_edicion',
                        'created_by',
                        'updated_by',
                        'anulado',
                        DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
                        DB::raw('SUM(debito) AS debito'),
                        DB::raw('SUM(credito) AS credito'),
                        DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                        DB::raw("IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
                        DB::raw("IF(naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
                    )
                    ->groupByRaw($this->request['agrupar'])
                    ->orderByRaw('created_at')
                ->first();

                if ($cabeza) {
                    $this->estadisticaCollection[] = [
                        'id_estadisticas' => $this->id_estadistica,
                        'id_nit' => $id_nit,
                        'id_cuenta' => $cabeza->id_cuenta,
                        'total_area' => 0,
                        'total_coheficiente' => 0,
                        'saldo_anterior' => $cabeza->saldo_anterior,
                        'total_facturas' => $cabeza->total_facturas,
                        'total_abono' => $cabeza->total_abono,
                        'saldo' => $cabeza->saldo_final,
                        'total' => 1,
                    ];
    
                    $dataTotal['saldo_anterior']+=$cabeza->saldo_anterior;
                    $dataTotal['total_facturas']+=$cabeza->total_facturas;
                    $dataTotal['total_abono']+=$cabeza->total_abono;
                    $dataTotal['saldo']+=$cabeza->saldo_final;
                }
            }
            
            $this->estadisticaCollection[] = $dataTotal;

            foreach (array_chunk($this->estadisticaCollection,233) as $estadisticaCollection){
                DB::connection('informes')
                    ->table('inf_estadistica_detalles')
                    ->insert(array_values($estadisticaCollection));
            }

            DB::connection('informes')->commit();

            event(new PrivateMessageEvent('informe-estadisticas-'.$empresa->token_db_maximo.'_'.$this->id_usuario, [
                'tipo' => 'exito',
                'mensaje' => 'Informe generado con exito!',
                'titulo' => 'Estadisticas generadas',
                'id_estadistica' => $this->id_estadistica,
                'autoclose' => false
            ]));            

        } catch (Exception $exception) {
            DB::connection('informes')->rollback();
            Log::error('ProcessInformeEstadisticas', ['message' => $exception->getMessage()]);
			throw $exception;
        }
    }

    private function getInmueblesNitsQuery()
    {
        $inmueble = DB::connection('max')->table('inmueble_nits AS IN')
            ->select(
                'IN.id_nit'
            )
            ->leftJoin('inmuebles AS IMN', 'IN.id_inmueble', 'IMN.id')
            ->leftJoin('zonas AS Z', 'IMN.id_zona', 'Z.id');

        if ($this->request['id_nit']) {
            $inmueble->where('IN.id_nit', $this->request['id_nit']);
        }

        if ($this->request['id_zona']) {
            $inmueble->where('Z.id', $this->request['id_zona'])
                ->where('IMN.id_concepto_facturacion', 1);
        }

        return $inmueble;
    }

    private function getCuotasMultasNitsQuery()
    {
        $cuotas = DB::connection('max')->table('cuotas_multas AS CM')
            ->select(
                'CM.id_nit'
            )
            ->leftJoin('inmuebles AS IMN', 'CM.id_inmueble', 'IMN.id')
            ->leftJoin('zonas AS Z', 'IMN.id_zona', 'Z.id');

        if ($this->request['id_nit']) {
            $cuotas->where('CM.id_nit', $this->request['id_nit']);
        }

        if ($this->request['id_zona']) {
            $cuotas->where('Z.id', $this->request['id_zona'])
                ->where('CM.id_concepto_facturacion', 1);
        }

        return $cuotas;
    }

    private function carteraDocumentosQuery($id_nit = NULL, $id_cuentas = NULL)
    {
        $documentosQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                "DG.id_nit",
                "DG.id_cuenta",
                "DG.documento_referencia",
                "PC.naturaleza_cuenta",
                "DG.id_centro_costos",
                "DG.consecutivo",
                "DG.concepto",
                "DG.fecha_manual",
                "DG.created_at",
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                "DG.anulado",
                DB::raw("0 AS saldo_anterior"),
                DB::raw("DG.debito AS debito"),
                DB::raw("DG.credito AS credito"),
                DB::raw("DG.debito - DG.credito AS saldo_final"),
                DB::raw("1 AS total_columnas")
            )
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'PC.id', 'PCT.id_cuenta')
            ->where('anulado', 0)
            ->whereIn('PCT.id_tipo_cuenta', [3,7,4,8])
            ->when($id_nit, function ($query) use($id_nit) {
				$query->where('DG.id_nit', $id_nit);
			})
            // ->when($id_cuentas, function ($query) use($id_cuentas) {
			// 	$query->whereIn('DG.id_cuenta', $id_cuentas);
			// })
            ->when($this->request['fecha_desde'] ? true : false, function ($query) {
				$query->where('DG.fecha_manual', '>=', $this->request['fecha_desde']);
			}) 
            ->when($this->request['fecha_hasta'] ? true : false, function ($query) {
				$query->where('DG.fecha_manual', '<=', $this->request['fecha_hasta']);
			})
            ->when(array_key_exists('id_cuenta', $this->request), function ($query) {
				$query->where('DG.id_cuenta', $this->request['id_cuenta']);
			});

        return $documentosQuery;
    }

    private function carteraAnteriorQuery($id_nit = NULL, $id_cuentas = NULL)
    {

        $anterioresQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                "DG.id_nit",
                "DG.id_cuenta",
                "DG.documento_referencia",
                "PC.naturaleza_cuenta",
                "DG.id_centro_costos",
                "DG.consecutivo",
                "DG.concepto",
                "DG.fecha_manual",
                "DG.created_at",
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                "DG.anulado",
                DB::raw("debito - credito AS saldo_anterior"),
                DB::raw("0 AS debito"),
                DB::raw("0 AS credito"),
                DB::raw("0 AS saldo_final"),
                DB::raw("1 AS total_columnas")
            )
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'PC.id', 'PCT.id_cuenta')
            ->where('anulado', 0)
            ->whereIn('PCT.id_tipo_cuenta', [3,7,4,8])
            ->when($id_nit, function ($query) use($id_nit) {
				$query->where('DG.id_nit', $id_nit);
			}) 
            // ->when($id_cuentas, function ($query) use($id_cuentas) {
			// 	$query->whereIn('DG.id_cuenta', $id_cuentas);
			// })
            ->when($this->request['fecha_desde'] ? true : false, function ($query) {
				$query->where('DG.fecha_manual', '<', $this->request['fecha_desde']);
			});

        return $anterioresQuery;
    }

    private function getInmueblesMemo()
    {
        $dataInforme = [];

        $inmuebles = Inmueble::where('id_zona', $this->request['id_zona'])
            ->with('personas')
            ->whereHas('concepto', function ($query) {
                $query->where('tipo_concepto', 0);
            });

        if ($this->request['id_nit']) {
            $inmuebles->whereHas('personas', function ($q) {
                $q->where('id_nit', $this->request['id_nit']);
            });
        }

        $inmuebles = $inmuebles->get();
        
        foreach ($inmuebles as $inmueble) {

            $id_nit = $inmueble->personas ? $inmueble->personas[0]->id_nit : null;
            if (!$id_nit) continue;

            $inmueblesNit = InmuebleNit::where('id_nit', $id_nit)
                ->with('inmueble.concepto')
                ->get();

            foreach ($inmueblesNit as $inmuebleNit) {
                if ($inmuebleNit->inmueble->id_zona == $this->request['id_zona']) {
                    $cuentaFiltro = $inmuebleNit->inmueble->concepto->id_cuenta_cobrar;
                    $dataInforme[$id_nit][] = [
                        'id_cuenta' => $cuentaFiltro
                    ];
                }
            }
        }

        $inmueblesNo = Inmueble::where('id_zona', $this->request['id_zona'])
            ->with('personas')
            ->whereHas('concepto', function ($query) {
                $query->where('tipo_concepto', 1);
            })
            ->get();

        foreach ($inmueblesNo as $inmuebleNo) {
            $id_nit = $inmueble->personas ? $inmueble->personas[0]->id_nit : null;
            if (!$id_nit) continue;

            $existeEnOtraTorre = false;
            $inmueblesNit = InmuebleNit::where('id_nit', $id_nit)
                ->with('inmueble.concepto')
                ->get();

            //VALIDAR SI EXISTE
            foreach ($inmueblesNit as $inmuebleNit) {
                $zonaItem = $inmuebleNit->inmueble->id_zona;
                $concepto = $inmuebleNit->inmueble->id_concepto_facturacion;

                if ($zonaItem != $this->request['id_zona'] && $concepto == 1) {
                    $existeEnOtraTorre = true;
                }
            }

            if (!$existeEnOtraTorre) continue;
            //IF EXISTE
            foreach ($inmueblesNit as $inmuebleNit) {
                if ($inmuebleNit->inmueble->id_zona == $this->request['id_zona']) {
                    $cuentaFiltro = $inmuebleNit->inmueble->concepto->id_cuenta_cobrar;
                    $dataInforme[$id_nit][] = [
                        'id_cuenta' => $cuentaFiltro
                    ];
                }
            }
        }

        $dataReal = [];
        foreach ($dataInforme as $id_nit => $nitCuentas) {
            $cuentas = [];
            foreach ($nitCuentas as $cuenta) {
                if (!in_array($cuenta['id_cuenta'], $cuentas)) {
                    $cuentas[] = $cuenta['id_cuenta'];
                }
            }
            $dataReal[$id_nit] = $cuentas;
        }
        return $dataReal;
    }

}
