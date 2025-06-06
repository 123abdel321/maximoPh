<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Config;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Zonas;
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Inmueble;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\CuotasMultas;
use App\Models\Sistema\CuotasMultasTemporal;

class CuotasMultasController extends Controller
{
    protected $messages = null;
    protected $dataCuotasMultas = [];

    public function __construct()
	{
		$this->messages = [
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute es inválido.',
            'numeric' => 'El campo :attribute debe ser un valor numérico.',
            'string' => 'El campo :attribute debe ser texto',
            'array' => 'El campo :attribute debe ser un arreglo.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
        ];
	}

    public function index ()
    {
        return view('pages.operaciones.cuotas_multas.cuotas_multas-view');
    }

    public function read (Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search');

            $columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc

            $this->generarTemporarCuotasMultas($request);

            $cuotasMultas = CuotasMultasTemporal::orderBy($columnName,$columnSortOrder)
                ->with('concepto', 'nit', 'inmueble', 'inmueble.concepto', 'inmueble.personas', 'inmueble.zona')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                );

            $cuotasMultasTotals = $cuotasMultas->get();

            $cuotasMultasPaginate = $cuotasMultas->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $cuotasMultasTotals->count(),
                'iTotalDisplayRecords' => $cuotasMultasTotals->count(),
                'data' => $cuotasMultasPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Cuotas extra/multas generados con exito!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    private function generarTemporarCuotasMultas ($request)
    {
        CuotasMultasTemporal::truncate();
        
        $totalesCuotas = DB::connection('max')->table('cuotas_multas AS CM')
            ->select(
                "*",
                DB::raw('1 AS totales'),
                DB::raw('SUM(valor_total) AS valor_total'),
                DB::raw('COUNT(id) AS total_count'),
            )
            ->where("fecha_inicio", "<=", $request->get('fecha_periodo'))
            ->where("fecha_fin", ">=", $request->get('fecha_periodo'))
            ->when($request->get('id_concepto'), function ($query) use($request) {
                $query->where('id_concepto_facturacion', $request->get('id_concepto'));
            })
            ->when($request->get('id_nit'), function ($query) use($request) {
                $query->where('id_nit', $request->get('id_nit'));
            })
            ->groupByRaw('id_concepto_facturacion')
            ->get();
        
        if (count($totalesCuotas)) {
            $totalesCuotas->each(function ($documento) use ($request) {
                $this->dataCuotasMultas[$documento->id_concepto_facturacion] = [
                    'id_nit' => '',
                    'id_inmueble' => '',
                    'id_cuotas_multas' => '',
                    'id_concepto_facturacion' => $documento->id_concepto_facturacion,
                    'tipo_concepto' => '',
                    'fecha_inicio' => '',
                    'fecha_fin' => '',
                    'valor_total' => $documento->valor_total,
                    'valor_coeficiente' => '',
                    'observacion' => $documento->total_count,
                    'totales' => $request->get('nivel') == 2 ? '1' : '0',
                    'created_by' => '',
                    'updated_by' => '',
                    'created_at' => '',
                    'updated_at' => '',
                ];
            });
        }

        if ($request->get('nivel') == 2) {
            $detalleCuotas = DB::connection('max')->table('cuotas_multas AS CM')
                ->select(
                    "*"
                )
                ->where("fecha_inicio", "<=", $request->get('fecha_periodo'))
                ->where("fecha_fin", ">=", $request->get('fecha_periodo'))
                ->when($request->get('id_concepto'), function ($query) use($request) {
                    $query->where('id_concepto_facturacion', $request->get('id_concepto'));
                })
                ->when($request->get('id_nit'), function ($query) use($request) {
                    $query->where('id_nit', $request->get('id_nit'));
                })
                ->groupByRaw('id')
                ->get();

            if (count($detalleCuotas)) {
                $detalleCuotas->each(function ($documento) {
                    $this->dataCuotasMultas[$documento->id_concepto_facturacion.'-A'.$documento->id] = [
                        'id_nit' => $documento->id_nit,
                        'id_inmueble' => $documento->id_inmueble,
                        'id_cuotas_multas' => $documento->id,
                        'id_concepto_facturacion' => $documento->id_concepto_facturacion,
                        'tipo_concepto' => $documento->tipo_concepto,
                        'fecha_inicio' => $documento->fecha_inicio,
                        'fecha_fin' => $documento->fecha_fin,
                        'valor_total' => $documento->valor_total,
                        'valor_coeficiente' => $documento->valor_coeficiente,
                        'observacion' => $documento->observacion,
                        'totales' => '0',
                        'created_at' => $documento->created_at,
                        'updated_at' => $documento->updated_at,
                        'created_by' => $documento->created_by,
                        'updated_by' => $documento->updated_by,
                    ];
                });
            }
        }

        if (count($this->dataCuotasMultas)) {
            ksort($this->dataCuotasMultas, SORT_STRING | SORT_FLAG_CASE);
        }

        
        foreach (array_chunk($this->dataCuotasMultas,233) as $dataCuotasMultas){
            DB::connection('max')
                ->table('cuotas_multas_temporals')
                ->insert(array_values($dataCuotasMultas));
        }

    }

    public function create (Request $request)
    {
        $rules = [
            'tipo_concepto' => 'required',
            'id_concepto_tipo_facturacion' => 'nullable|exists:max.concepto_facturacions,id',
            'id_zona' => 'nullable|exists:max.zonas,id',
            'id_inmueble' => 'nullable|exists:max.inmuebles,id',
            'id_nit' => 'nullable|exists:sam.nits,id',
            'id_concepto_facturacion' => 'required|exists:max.concepto_facturacions,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'valor' => 'required',
            'observacion' => 'required',
            'masivo' => 'nullable',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }
        // return response()->json([
        //     "success"=>false,
        //     'data' => [],
        //     "message"=> 'asds'
        // ], 200);
        try {
            DB::connection('max')->beginTransaction();

            $nitsCuotasMultas = InmuebleNit::select('id_nit', 'id_inmueble')
                ->whereIn('tipo', [0, 3])
                ->when($request->get('id_concepto_tipo_facturacion'), function ($query) use($request) {
                    $query->whereHas('inmueble',  function ($q) use($request) {
                        $q->where('id_concepto_facturacion', $request->get('id_concepto_tipo_facturacion'));
                    });
                })
                ->when($request->get('id_zona'), function ($query) use($request) {
                    $query->whereHas('inmueble',  function ($q) use($request) {
                        $q->where('id_zona', $request->get('id_zona'));
                    });
                })
                ->when($request->get('id_nit'), function ($query) use($request) {
                    $query->where('id_nit', $request->get('id_nit'));
                })
                ->when($request->get('id_inmueble'), function ($query) use($request) {
                    $query->where('id_inmueble', $request->get('id_inmueble'));
                })
                ->when($request->get('masivo') != '1' ? true : false, function ($query) {
                    $query->groupBy('id_nit');
                })
                ->get();
            
            //RECORREMOS NITS CON INMUEBLES
            foreach ($nitsCuotasMultas as $nit) {
                if ($request->get('tipo_concepto')) {//POR VALOR INDIVIDUAL
                    CuotasMultas::create([
                        'id_nit' => $nit->id_nit,
                        'id_inmueble' => $nit->id_inmueble,
                        'tipo_concepto' => 1,
                        'id_concepto_facturacion' => $request->get('id_concepto_facturacion'),
                        'fecha_inicio' => $request->get('fecha_inicio'),
                        'fecha_fin' => $request->get('fecha_fin'),
                        'valor_total' => $request->get('valor'),
                        'observacion' => $request->get('observacion'),
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id,
                    ]);
                } else {// POR COEFICIENTE
                    $inmueblesNits = InmuebleNit::with('inmueble.concepto', 'inmueble.zona')//INMUEBLES DEL NIT
                        ->when($request->get('id_inmueble'), function ($query) use($request) {
                            $query->where('id_inmueble', $request->get('id_inmueble'));
                        })
                        ->where('id_nit', $nit->id_nit)
                        ->get();
    
                    //RECORRERMOS INMUEBLES DEL NIT
                    foreach ($inmueblesNits as $inmuebleNit) {
                        $porcentaje = $inmuebleNit->inmueble->coeficiente * ($inmuebleNit->porcentaje_administracion / 100);
                        $valorTotal = $request->get('valor') * $porcentaje;
                        if (!$valorTotal) continue;
    
                        CuotasMultas::create([
                            'id_nit' => $nit->id_nit,
                            'id_inmueble' => $inmuebleNit->id_inmueble,
                            'tipo_concepto' => 0,
                            'id_concepto_facturacion' => $request->get('id_concepto_facturacion'),
                            'fecha_inicio' => $request->get('fecha_inicio'),
                            'fecha_fin' => $request->get('fecha_fin'),
                            'valor_total' => $valorTotal,
                            'valor_coeficiente' => $request->get('valor'),
                            'observacion' => $request->get('observacion'),
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id,
                        ]);
                    }
                }
            }

            DB::connection('max')->commit();

            return response()->json([
                "success"=>true,
                'data' => [],
                "message"=>'Cuota extra/multa creada con exito'
            ], 200);

        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function update (Request $request)
    {
        $rules = [
            'id' => 'required|exists:max.cuotas_multas,id',
            'tipo_concepto' => 'required',
            'id_inmueble' => 'nullable|exists:max.inmuebles,id',
            'id_nit' => 'nullable|exists:sam.nits,id',
            'id_concepto_facturacion' => 'required|exists:max.concepto_facturacions,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date',
            'valor' => 'required',
            'observacion' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        try {
            DB::connection('max')->beginTransaction();
            
            $inmueble = Inmueble::find($request->get('id_inmueble'));
            $inmuebleNit = InmuebleNit::where('id_inmueble', $inmueble->id)
                ->where('id_nit', $request->get('id_nit'))
                ->first();

            $valorTotal = $request->get('valor');
            if (!$request->get('tipo_concepto')) {
                $porcentaje = $inmueble->coeficiente * ($inmuebleNit->porcentaje_administracion / 100);
                $valorTotal = $request->get('valor') * $porcentaje;
            }

            $cuotasMultas = CuotasMultas::where('id', $request->get('id'))
                ->update([
                    'id_nit' => $request->get('id_nit'),
                    'id_inmueble' => $inmueble->id,
                    'id_concepto_facturacion' => $request->get('id_concepto_facturacion'),
                    'tipo_concepto' => $request->get('tipo_concepto'),
                    'fecha_inicio' => $request->get('fecha_inicio'),
                    'fecha_fin' => $request->get('fecha_fin'),
                    'valor_total' => $valorTotal,
                    'valor_coeficiente' => $request->get('valor'),
                    'observacion' => $request->get('observacion'),
                    'updated_by' => request()->user()->id
                ]);

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $cuotasMultas,
                'message'=> 'Cuota extra/multa actualizada con exito!'
            ]);
                
        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }

    }

    public function delete (Request $request)
    {
        $rules = [
            'id' => 'required|exists:max.cuotas_multas,id',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        try {
            DB::connection('max')->beginTransaction();

            CuotasMultas::where('id', $request->get('id'))->delete();

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Cuota / multa eliminada con exito!'
            ]);

        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function deleteMasivo (Request $request)
    {
        try {
            DB::connection('max')->beginTransaction();

            CuotasMultas::when($request->get('id_concepto_facturacion'), function ($query) use ($request) {
                    $query->where('id_concepto_facturacion', $request->get('id_concepto_facturacion'));
                })
                ->when($request->get('id_zona'), function ($query) use ($request) {
                    $query->whereHas('inmueble', function ($q) use ($request) {
                        $q->where('id_zona', $request->get('id_zona'));
                    });
                })
                ->when($request->get('id_inmueble'), function ($query) use ($request) {
                    $query->where('id_inmueble', $request->get('id_inmueble'));
                })
                ->when($request->get('id_nit'), function ($query) use ($request) {
                    $query->where('id_nit', $request->get('id_nit'));
                })
                ->when($request->get('periodo'), function ($query) use ($request) {
                    $query->where("fecha_inicio", '<=', $request->get('periodo'))
                        ->where("fecha_fin", '>=', $request->get('periodo'));
                })
                ->delete();

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Cuota / multa eliminada con exito!'
            ]);

        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function totales (Request $request)
    {
        $filtro1 = $request->get('fecha_desde') && $request->get('fecha_hasta') ? true : false;
        
        $cuotasMultas = CuotasMultas::select(
                DB::raw("SUM(valor_total) AS valor_total")
            )
            ->where("fecha_inicio", "<=", $request->get('fecha_periodo'))
            ->where("fecha_fin", ">=", $request->get('fecha_periodo'));

        if ($request->get('search')) {
            $empresa = Empresa::where('token_db_maximo', $request->user()['has_empresa'])->first();
            $cuotasMultas->whereHas('nit',  function ($query) use($empresa, $request) {
                $query->from("$empresa->token_db_portafolio.nits")
                    ->where('primer_nombre', 'LIKE', '%'.$request->get('search').'%')
                    ->orWhere('razon_social', 'LIKE', '%'.$request->get('search').'%')
                    ->orWhere('otros_nombres', 'LIKE', '%'.$request->get('search').'%')
                    ->orWhere('primer_apellido', 'LIKE', '%'.$request->get('search').'%')
                    ->orWhere('segundo_apellido', 'LIKE', '%'.$request->get('search').'%')
                    ->orWhere('numero_documento', 'LIKE', '%'.$request->get('search').'%')
                    ->orWhere('email', 'LIKE', '%'.$request->get('search').'%');
            })
            ->orWhereHas('concepto',  function ($query) use($request) {
                $query->where('nombre_concepto', 'LIKE', '%'.$request->get('search').'%');
            })
            ->orWhereHas('inmueble',  function ($query) use($request) {
                $query->where('nombre', 'LIKE', '%'.$request->get('search').'%')
                    ->orWhereHas('zona',  function ($q) use($request) {
                        $q->where('nombre', 'LIKE', '%'.$request->get('search').'%');
                    });
            });
        }

        if ($request->get('id_nit')) {
            $cuotasMultas->where('id_nit', $request->get('id_nit'))
                ->orWhereBetween("fecha_inicio", [$request->get('fecha_desde'), $request->get('fecha_hasta')])
                ->orWhereBetween("fecha_fin", [$request->get('fecha_desde'), $request->get('fecha_hasta')]);
        }

        if ($request->get('id_concepto')) {
            $cuotasMultas->where('id_concepto_facturacion', $request->get('id_concepto'));
        }

        $cuotasMultas = $cuotasMultas->first();
        $data = [
            'total' => $cuotasMultas->valor_total
        ];

        return response()->json([
            'success'=>	true,
            'data' => $data
        ]);
    }

    public function comboConcepto (Request $request)
    {
        $concepto = CuotasMultas::select(
                DB::raw('id_concepto_facturacion AS id'),
                'id_nit',
                'id_inmueble',
                'id_concepto_facturacion',
                'tipo_concepto',
                'fecha_inicio',
                'fecha_fin',
                'valor_total',
                'valor_coeficiente',
                'observacion',
                'created_by',
                'updated_by'
            )
            ->with('concepto')
            ->groupBy('id_concepto_facturacion');

        if ($request->get("search")) {
            $concepto->orWhereHas('concepto',  function ($query) use($request) {
                $query->where('nombre_concepto', 'LIKE', '%'.$request->get('search').'%');
            });
        }

        return $concepto->paginate(40);
    }

    private function nitsSearch($search)
    {
        $data = [];
        $nits = DB::connection('sam')->table('nits')->select('id')
            ->where('razon_social', 'LIKE', '%'.$search.'%')
            ->orWhere('numero_documento', 'LIKE', '%'.$search.'%')
            ->orWhere(DB::raw("(CASE
                WHEN razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                WHEN (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                ELSE NULL
            END)"), 'LIKE', '%'.$search.'%')
            ->orWhere('email', 'LIKE', '%'.$search.'%')
            ->get()->toArray();

        if (count($nits)) {
            foreach ($nits as $nit) {
                $data[] = $nit->id;
            }
        }

        return $data;        
    }
}