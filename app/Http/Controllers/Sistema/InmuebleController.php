<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\PortafolioERP\Extracto;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Sistema\Inmueble;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\Facturacion;
use App\Models\Sistema\CuotasMultas;

class InmuebleController extends Controller
{
    protected $messages = null;

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
        $editar_valor_admon_inmueble = Entorno::where('nombre', 'editar_valor_admon_inmueble')->first();
        $editar_coheficiente_admon_inmueble = Entorno::where('nombre', 'editar_coheficiente_admon_inmueble')->first();
        $valor_total_presupuesto_year_actual = Entorno::where('nombre', 'valor_total_presupuesto_year_actual')->first();
        $numero_total_unidades = Entorno::where('nombre', 'numero_total_unidades')->first();
        $area_total_m2 = Entorno::where('nombre', 'area_total_m2')->first();
        
        $data = [
            "editar_valor_admon_inmueble" => $editar_valor_admon_inmueble && $editar_valor_admon_inmueble->valor ? $editar_valor_admon_inmueble->valor : '0',
            "editar_coheficiente_admon_inmueble" => $editar_coheficiente_admon_inmueble && $editar_coheficiente_admon_inmueble->valor ? $editar_coheficiente_admon_inmueble->valor : '0',
            "valor_total_presupuesto_year_actual" => $valor_total_presupuesto_year_actual && $valor_total_presupuesto_year_actual->valor ? $valor_total_presupuesto_year_actual->valor : '0',
            "numero_total_unidades" => $numero_total_unidades && $numero_total_unidades->valor ? $numero_total_unidades->valor : '0',
            "area_total_m2" => $area_total_m2 && $area_total_m2->valor ? $area_total_m2->valor : '0',
        ];

        return view('pages.tablas.inmuebles.inmuebles-view', $data);
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

            $columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc

            $inmueble = Inmueble::orderBy($columnName,$columnSortOrder)
                ->with('zona', 'concepto', 'personas.nit')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                );
            
            if ($request->get('search')) {
                $nitSsearch = $this->nitsSearch($request->get('search'));
                
                $inmueble->where('nombre', 'LIKE', '%'.$request->get('search').'%')
                    ->orWhere('area', 'LIKE', '%'.$request->get('search').'%')
                    ->orWhere('coeficiente', 'LIKE', '%'.$request->get('search').'%')
                    ->when(count($nitSsearch) > 0 ? true : false, function ($query) use($nitSsearch) {
                        $query->orWhereHas('personas',  function ($query) use($nitSsearch) {
                            $query->whereIn('id_nit', $nitSsearch);
                        });
                    });
                    
            }

            if ($request->get('id_nit')) {
                $inmueble->whereHas('personas',  function ($query) use($request) {
                    $query->where('id_nit', $request->get('id_nit'));
                });
            }

            if ($request->get('id_zona')) {
                $inmueble->whereHas('zona',  function ($query) use($request) {
                    $query->where('id_zona', $request->get('id_zona'));
                });
            }

            if ($request->get('id_concepto_facturacion')) {
                $inmueble->whereHas('concepto',  function ($query) use($request) {
                    $query->where('id_concepto_facturacion', $request->get('id_concepto_facturacion'));
                });
            }

            $inmuebleTotals = $inmueble->get();

            $inmueblePaginate = $inmueble->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $inmuebleTotals->count(),
                'iTotalDisplayRecords' => $inmuebleTotals->count(),
                'data' => $inmueblePaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Inmuebles generados con exito!'
            ]);


        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function create (Request $request)
    {
        $rules = [
            'nombre' => 'required|min:1|max:200|unique:max.inmuebles,nombre',
            'id_zona' => 'required|exists:max.zonas,id',
            'id_concepto_facturacion' => 'nullable|exists:max.concepto_facturacions,id',
            'area' => 'required',
            'valor_total_administracion' => 'nullable',
            'observaciones' => 'nullable'
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
            
            $editar_valor_admon_inmueble =  Entorno::where('nombre', 'editar_valor_admon_inmueble')->first()->valor;
            $valor_total_presupuesto_year_actual = Entorno::where('nombre', 'valor_total_presupuesto_year_actual')->first()->valor;
            $valor_total_presupuesto_year_actual = $valor_total_presupuesto_year_actual / 12;
            $area_total_m2 = Entorno::where('nombre', 'area_total_m2')->first()->valor;

            $coeficiente = $request->get('area') / $area_total_m2;

            if ($editar_valor_admon_inmueble) {
                if ($request->get('valor_total_administracion') <= 0) {
                    return response()->json([
                        "success"=>false,
                        'data' => [],
                        "message"=>['valor_total_administracion' => 'El valor de la administración en obligatorio']
                    ], 422);
                }
                $valor_total_administracion = $request->get('valor_total_administracion');
            } else {
                $valor_total_administracion = $coeficiente * $valor_total_presupuesto_year_actual;
            }

            $inmueble = Inmueble::create([
                'id_zona' => $request->get('id_zona'),
                'id_concepto_facturacion' => $request->get('id_concepto_facturacion'),
                'area' => $request->get('area'),
                'nombre' => $request->get('nombre'),
                'coeficiente' => $coeficiente,
                'valor_total_administracion' => round($valor_total_administracion),
                'observaciones' => $request->get('observaciones'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $inmueble,
                'message'=> 'Inmueble creado con exito!'
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

    public function update (Request $request)
    {
        $rules = [
            'id' => 'required|exists:max.inmuebles,id',
            'nombre' => ['required','min:1','max:200',
                function($attribute, $value, $fail) use ($request) {
                    $inmuebleOld = Inmueble::find($request->get('id'));
                    if ($inmuebleOld->nombre != $request->get('nombre')) {
                        $inmuebleNew = Inmueble::where('nombre', $request->get('nombre'));
                        if ($inmuebleNew->count()) {
                            $fail("La nombre del inmueble ".$value." ya existe.");
                        }
                    }
                }],
            'id_zona' => 'nullable|exists:max.zonas,id',
            'id_concepto_facturacion' => 'nullable|exists:max.concepto_facturacions,id',
            'area' => 'required',
            'valor_total_administracion' => 'nullable',
            'observaciones' => 'nullable'
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
            $editar_valor_admon_inmueble =  Entorno::where('nombre', 'editar_valor_admon_inmueble')->first()->valor;
            $editar_coheficiente_admon_inmueble =  Entorno::where('nombre', 'editar_coheficiente_admon_inmueble')->first()->valor;

            $valor_total_presupuesto_year_actual = Entorno::where('nombre', 'valor_total_presupuesto_year_actual')->first()->valor;
            $valor_total_presupuesto_mes_actual = $valor_total_presupuesto_year_actual / 12;
            $area_total_m2 = Entorno::where('nombre', 'area_total_m2')->first()->valor;
            $valor_total_administracion = 0;

            if ($editar_valor_admon_inmueble && $editar_coheficiente_admon_inmueble) {
                $coeficiente = $request->get('coeficiente');
            } else {
                $coeficiente = $request->get('area') / $area_total_m2;
            }
            
            if ($editar_valor_admon_inmueble) {
                if ($request->get('valor_total_administracion') <= 0) {
                    return response()->json([
                        "success"=>false,
                        'data' => [],
                        "message"=>['valor_total_administracion' => 'El valor de la administración en obligatorio']
                    ], 422);
                }
                $valor_total_administracion = $request->get('valor_total_administracion');
            } else {
                $valor_total_administracion = $coeficiente * $valor_total_presupuesto_mes_actual;
            }

            $inmueble = Inmueble::where('id', $request->get('id'))
                ->update ([
                    'id_zona' => $request->get('id_zona'),
                    'id_concepto_facturacion' => $request->get('id_concepto_facturacion'),
                    'area' => $request->get('area'),
                    'nombre' => $request->get('nombre'),
                    'coeficiente' => $coeficiente,
                    'valor_total_administracion' => round($valor_total_administracion),
                    'observaciones' => $request->get('observaciones'),
                    'updated_by' => request()->user()->id
                ]);

            $inmueblesNits = InmuebleNit::where('id_inmueble', $request->get('id'))
                ->with('inmueble')
                ->get();
            
            foreach ($inmueblesNits as $inmuebleNis) {
                $total = round($valor_total_administracion * ($inmuebleNis->porcentaje_administracion / 100));
                $inmuebleNis->valor_total = $total;
                $inmuebleNis->save();
            }

            //ACTUAIZAR DATOS EN NITS
            $nitsInmuebles = InmuebleNit::where('id_inmueble', $request->get('id'))
                ->with('nit')
                ->groupBy('id_nit')
                ->get();
    
            foreach ($nitsInmuebles as $nit) {
    
                $inmueblesNits = InmuebleNit::with('inmueble.zona')->where('id_nit', $nit->nit->id)->get();
                $apartamentos = '';
    
                if (count($inmueblesNits)) {
                    foreach ($inmueblesNits as $key => $inmuebleNit) {
                        $apartamentos.= $inmuebleNit->inmueble->zona->nombre.' - '.$inmuebleNit->inmueble->nombre.', ';
                    }
                }
                $nit->nit->apartamentos = rtrim($apartamentos, ", ");
                $nit->nit->save();
            }

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $inmueble,
                'message'=> 'Inmueble actualizado con exito!'
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
            'id' => 'required|exists:max.inmuebles,id',
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

            Inmueble::where('id', $request->get('id'))->delete();
            InmuebleNit::where('id_inmueble', $request->get('id'))->delete();

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Inmueble eliminada con exito!'
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

    public function combo (Request $request)
    {
        $inmuebles = Inmueble::with('personas.nit')
            ->select(
                \DB::raw('*'),
                \DB::raw("nombre as text")
            );

        if ($request->get("search")) {
            $nitSsearch = $this->nitsSearch($request->get('search'));
            
            if (count($nitSsearch)) {
                $inmuebles->whereHas('personas',  function ($query) use($nitSsearch) {
                        $query->whereIn('id_nit', $nitSsearch);
                    })
                    ->orWhere('nombre', 'LIKE', '%' . $request->get("search") . '%')
                    ->orWhere('area', 'LIKE', '%' . $request->get("search") . '%')
                    ->orWhere('coeficiente', 'LIKE', '%' . $request->get("search") . '%');
            } else {
                $inmuebles->where('nombre', 'LIKE', '%' . $request->get("search") . '%')
                    ->orWhere('area', 'LIKE', '%' . $request->get("search") . '%')
                    ->orWhere('coeficiente', 'LIKE', '%' . $request->get("search") . '%');
            }
        }

        return $inmuebles->paginate(40);
    }

    public function totales (Request $request)
    {
        $totalInmuebles = Inmueble::whereNotNull('id');
        $search = $request->get('search');
        $nitSsearch = $search ? $this->nitsSearch($search) : [];
        if ($search) {
            $totalInmuebles->where('nombre', 'LIKE', '%'.$search.'%')
                ->orWhere('area', 'LIKE', '%'.$search.'%')
                ->orWhere('coeficiente', 'LIKE', '%'.$search.'%')
                ->orWhere('observaciones', 'LIKE', '%'.$search.'%')
                ->orWhere('valor_total_administracion', 'LIKE', '%'.$search.'%')
                ->when(count($nitSsearch) > 0 ? true : false, function ($query) use($nitSsearch) {
                    $query->orWhereHas('personas',  function ($query) use($nitSsearch) {
                        $query->whereIn('id_nit', $nitSsearch);
                    });
                });
        }

        if ($request->get('id_nit')) {
            $totalInmuebles->whereHas('personas',  function ($query) use($request) {
                $query->where('id_nit', $request->get('id_nit'));
            });
        }

        if ($request->get('id_zona')) {
            $totalInmuebles->whereHas('zona',  function ($query) use($request) {
                $query->where('id_zona', $request->get('id_zona'));
            });
        }

        if ($request->get('id_concepto_facturacion')) {
            $totalInmuebles->whereHas('concepto',  function ($query) use($request) {
                $query->where('id_concepto_facturacion', $request->get('id_concepto_facturacion'));
            });
        }

        $areaM2Total = Inmueble::whereNotNull('id');
        if ($search) {
            $areaM2Total->where('nombre', 'LIKE', '%'.$search.'%')
                ->orWhere('area', 'LIKE', '%'.$search.'%')
                ->orWhere('coeficiente', 'LIKE', '%'.$search.'%')
                ->orWhere('observaciones', 'LIKE', '%'.$search.'%')
                ->orWhere('valor_total_administracion', 'LIKE', '%'.$search.'%')
                ->when(count($nitSsearch) > 0 ? true : false, function ($query) use($nitSsearch) {
                    $query->orWhereHas('personas',  function ($query) use($nitSsearch) {
                        $query->whereIn('id_nit', $nitSsearch);
                    });
                });
        }

        if ($request->get('id_nit')) {
            $areaM2Total->whereHas('personas',  function ($query) use($request) {
                $query->where('id_nit', $request->get('id_nit'));
            });
        }

        if ($request->get('id_zona')) {
            $areaM2Total->whereHas('zona',  function ($query) use($request) {
                $query->where('id_zona', $request->get('id_zona'));
            });
        }

        if ($request->get('id_concepto_facturacion')) {
            $areaM2Total->whereHas('concepto',  function ($query) use($request) {
                $query->where('id_concepto_facturacion', $request->get('id_concepto_facturacion'));
            });
        }

        $coeficienteTotal = Inmueble::whereNotNull('id');
        if ($search) {
            $coeficienteTotal->where('nombre', 'LIKE', '%'.$search.'%')
                ->orWhere('area', 'LIKE', '%'.$search.'%')
                ->orWhere('coeficiente', 'LIKE', '%'.$search.'%')
                ->orWhere('observaciones', 'LIKE', '%'.$search.'%')
                ->orWhere('valor_total_administracion', 'LIKE', '%'.$search.'%')
                ->when(count($nitSsearch) > 0 ? true : false, function ($query) use($nitSsearch) {
                    $query->orWhereHas('personas',  function ($query) use($nitSsearch) {
                        $query->whereIn('id_nit', $nitSsearch);
                    });
                });
        }

        if ($request->get('id_nit')) {
            $coeficienteTotal->whereHas('personas',  function ($query) use($request) {
                $query->where('id_nit', $request->get('id_nit'));
            });
        }

        if ($request->get('id_zona')) {
            $coeficienteTotal->whereHas('zona',  function ($query) use($request) {
                $query->where('id_zona', $request->get('id_zona'));
            });
        }

        if ($request->get('id_concepto_facturacion')) {
            $coeficienteTotal->whereHas('concepto',  function ($query) use($request) {
                $query->where('id_concepto_facturacion', $request->get('id_concepto_facturacion'));
            });
        }

        $inmueblesPresupuesto = Inmueble::whereNotNull('id');
        if ($search) {
            $inmueblesPresupuesto->where('nombre', 'LIKE', '%'.$search.'%')
                ->orWhere('area', 'LIKE', '%'.$search.'%')
                ->orWhere('coeficiente', 'LIKE', '%'.$search.'%')
                ->orWhere('observaciones', 'LIKE', '%'.$search.'%')
                ->orWhere('valor_total_administracion', 'LIKE', '%'.$search.'%')
                ->when(count($nitSsearch) > 0 ? true : false, function ($query) use($nitSsearch) {
                    $query->orWhereHas('personas',  function ($query) use($nitSsearch) {
                        $query->whereIn('id_nit', $nitSsearch);
                    });
                });
        }
        if ($request->get('id_nit')) {
            $inmueblesPresupuesto->whereHas('personas',  function ($query) use($request) {
                $query->where('id_nit', $request->get('id_nit'));
            });
        }

        if ($request->get('id_zona')) {
            $inmueblesPresupuesto->whereHas('zona',  function ($query) use($request) {
                $query->where('id_zona', $request->get('id_zona'));
            });
        }

        if ($request->get('id_concepto_facturacion')) {
            $inmueblesPresupuesto->whereHas('concepto',  function ($query) use($request) {
                $query->where('id_concepto_facturacion', $request->get('id_concepto_facturacion'));
            });
        }
        $inmueblesFilter = [];
        $inmueblesPresupuesto = $inmueblesPresupuesto->get();
        if (count($inmueblesPresupuesto)) {
            foreach ($inmueblesPresupuesto as $inmuebles) {
                $inmueblesFilter[] = $inmuebles->id;
            }
        }

        $totalPresupuesto = InmuebleNit::whereIn('id_inmueble', $inmueblesFilter)
            ->sum('valor_total');;

        $data = [
            'numero_registro_unidades' => $totalInmuebles->count(),
            'area_registro_m2' => $areaM2Total->sum('area'),
            'valor_registro_presupuesto' => $totalPresupuesto,
            'valor_registro_coeficiente' => $coeficienteTotal->sum('coeficiente') * 100
        ];

        return response()->json([
            'success'=>	true,
            'data' => $data
        ]);
    }

    private function totalesConceptoFacturacion ()
    {
        $conceptoFacturacion = DB::connection('max')->table('inmueble_nits')->select(
                'concepto_facturacions.nombre_concepto',
                DB::raw('SUM(valor_total) AS valor_total'),
                DB::raw('COUNT(inmueble_nits.id) AS count')
            )
            ->leftJoin('inmuebles', 'inmueble_nits.id_inmueble', 'inmuebles.id')
            ->leftJoin('concepto_facturacions', 'inmuebles.id_concepto_facturacion', 'concepto_facturacions.id')
            ->groupBy('inmuebles.id_concepto_facturacion')
            ->get();

        return $conceptoFacturacion;
    }

    private function totalesExtrasMultas ($inicioMes, $finMes)
    {
        $extrasMultas = DB::connection('max')->table('cuotas_multas')->select(
                'concepto_facturacions.nombre_concepto',
                DB::raw('SUM(valor_total) AS valor_total'),
                DB::raw('COUNT(cuotas_multas.id) AS count')
            )
            ->leftJoin('concepto_facturacions', 'cuotas_multas.id_concepto_facturacion', 'concepto_facturacions.id')
            ->orWhereBetween("fecha_inicio", [$inicioMes, $finMes])
            ->orWhereBetween("fecha_fin", [$inicioMes, $finMes])
            ->groupBy('cuotas_multas.id_concepto_facturacion')
            ->get();

        return $extrasMultas;
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

    private function actualizarNombreApartamentos(Nits $nit)
    {
        $inmueblesNits = InmuebleNit::with('inmueble.zona')->where('id_nit', $nit->id)->get();

        $apartamentos = '';

        if (count($inmueblesNits)) {
            foreach ($inmueblesNits as $key => $inmuebleNit) {
                $apartamentos.= $inmuebleNit->inmueble->zona->nombre.' - '.$inmuebleNit->inmueble->nombre.', ';
            }
        }
        $nit->apartamentos = rtrim($apartamentos, ", ");
        $nit->save();
    }
}