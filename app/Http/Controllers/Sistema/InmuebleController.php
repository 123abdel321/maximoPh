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
        $data = [
            "editar_valor_admon_inmueble" => Entorno::where('nombre', 'editar_valor_admon_inmueble')->first()->valor,
            "valor_total_presupuesto_year_actual" => Entorno::where('nombre', 'valor_total_presupuesto_year_actual')->first()->valor,
            "numero_total_unidades" => Entorno::where('nombre', 'numero_total_unidades')->first()->valor,
            "area_total_m2" => Entorno::where('nombre', 'area_total_m2')->first()->valor,
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
                    ->orWhereHas('personas',  function ($query) use($nitSsearch) {
                        $query->whereIn('id_nit', $nitSsearch);
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

            $valor_total_presupuesto_year_actual = Entorno::where('nombre', 'valor_total_presupuesto_year_actual')->first()->valor;
            $valor_total_presupuesto_mes_actual = $valor_total_presupuesto_year_actual / 12;
            $area_total_m2 = Entorno::where('nombre', 'area_total_m2')->first()->valor;
            $valor_total_administracion = 0;

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
        $inmuebles = Inmueble::select(
            \DB::raw('*'),
            \DB::raw("nombre as text")
        )->with('personas');

        if ($request->get("search")) {
            $inmuebles->where('nombre', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('area', 'LIKE', '%' . $request->get("q") . '%')
                ->orWhere('coeficiente', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $inmuebles->paginate(40);
    }

    public function totales ()
    {
        $totalInmuebles = Inmueble::count();
        $areaM2Total = Inmueble::sum('area');
        $coeficienteTotal = Inmueble::sum('coeficiente');
        $valorRegistroPresupuesto = InmuebleNit::sum('valor_total');
        $total_concepto_facturacion = $this->totalesConceptoFacturacion();
        $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $saldo_anterior = 0;
        $count_saldo_anterior = 0;

        $inicioMes = date('Y-m', strtotime($periodo_facturacion));
        $finMes = date('Y-m-t', strtotime($periodo_facturacion));

        $total_extras_multas = $this->totalesExtrasMultas($inicioMes, $finMes);

        $nitsFacturacion = InmuebleNit::groupBy('id_nit')
            ->get();

        $response = (new Extracto(//TRAER CUENTAS POR COBRAR
            null,
            [3,7],
            null,
            $periodo_facturacion
        ))->send(request()->user()->id_empresa);

        if ($response['status'] > 299) {//VALIDAR ERRORES PORTAFOLIO
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> $response['message']
            ], 422);
        }

        $extractos = $response['response']->data;
        $extractosNits = [];

        foreach ($extractos as $extracto) {
            $extracto = (object)$extracto;
            $extractosNits[$extracto->id_nit][] =$extracto;
        }

        $total_intereses = 0;
        $count_intereses = 0;

        $response = (new Extracto(//TRAER ANTICIPOS
            null,
            [4,8]
        ))->send(request()->user()->id_empresa);

        if ($response['status'] > 299) {//VALIDAR ERRORES PORTAFOLIO
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> $response['message']
            ], 422);
        }

        $anticipos = $response['response']->data;
        $anticiposNits = [];

        foreach ($anticipos as $anticipo) {
            $anticipo = (object)$anticipo;
            $anticiposNits[$anticipo->id_nit][] = $anticipo;
        }

        $total_anticipos = 0;
        $count_anticipos = 0;

        foreach ($nitsFacturacion as $inmuebleNit) {
            $cobrarInteses = [];

            $inmueblesFacturar = InmuebleNit::with('inmueble.concepto', 'inmueble.zona')//INMUEBLES DEL NIT
                ->where('id_nit', $inmuebleNit->id_nit)
                ->get();

            //RECORREMOS INMUEBLES DEL NIT
            foreach ($inmueblesFacturar as $inmuebleFactura) {
                $cxcIntereses = $inmuebleFactura->inmueble->concepto->id_cuenta_cobrar;
                if ($inmuebleFactura->inmueble->concepto->intereses && !in_array($cxcIntereses, $cobrarInteses)) {
                    array_push($cobrarInteses, $cxcIntereses);
                }
            }
            
            $id_cuenta_intereses = Entorno::where('nombre', 'id_cuenta_intereses')->first()->valor;
            $porcentaje_intereses_mora = Entorno::where('nombre', 'porcentaje_intereses_mora')->first()->valor;

            if (array_key_exists($inmuebleNit->id_nit, $extractosNits)) {
                foreach ($extractosNits[$inmuebleNit->id_nit] as $extracto) {
                    $saldo = floatval($extracto->saldo);
                    $saldo_anterior+= $saldo;
                    $count_saldo_anterior++;
                    if (!in_array($extracto->id_cuenta, $cobrarInteses)) continue;
                    
                    $total_intereses+= $saldo * ($porcentaje_intereses_mora / 100);
                    $count_intereses++;
                }
            }

            if (array_key_exists($inmuebleNit->id_nit, $anticiposNits)) {
                foreach ($anticiposNits[$inmuebleNit->id_nit] as $anticipos) {
                    $anticipo = floatval($anticipos->saldo);
                    $total_anticipos+= $anticipo;
                    $count_anticipos++;
                }
            }
        }

        $existe_facturacion = Facturacion::where('fecha_manual', $finMes)->count();

        $data = [
            'numero_total_unidades' => Entorno::where('nombre', 'numero_total_unidades')->first()->valor,
            'numero_registro_unidades' => $totalInmuebles,
            'area_total_m2' => Entorno::where('nombre', 'area_total_m2')->first()->valor,
            'area_registro_m2' => $areaM2Total,
            'valor_total_presupuesto' => Entorno::where('nombre', 'valor_total_presupuesto_year_actual')->first()->valor,
            'valor_registro_presupuesto' => $valorRegistroPresupuesto,
            'valor_registro_coeficiente' => $coeficienteTotal * 100,
            'periodo_facturacion' => Entorno::where('nombre', 'periodo_facturacion')->first()->valor,
            'total_intereses' => $total_intereses,
            'count_intereses' => $count_intereses,
            'totales_extras_multas' => $total_extras_multas,
            'saldo_anterior' => $saldo_anterior,
            'count_saldo_anterior' => $count_saldo_anterior,
            'total_anticipos' => $total_anticipos,
            'count_anticipos' => $count_anticipos,
            'totales_concepto_facturacion' => $total_concepto_facturacion,
            'existe_facturacion' => $existe_facturacion
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
            ->whereDate('fecha_inicio', '<=', $inicioMes.'-01')
            ->whereDate('fecha_fin', '>=', $finMes)
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
}