<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Sistema\Inmueble;
use App\Models\Sistema\InmuebleNit;
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
            $search_arr = $request->get('search');

            $columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc
            $searchValue = $search_arr['value']; // Search value

            $inmueble = Inmueble::orderBy($columnName,$columnSortOrder)
                ->with('zona', 'concepto', 'personas')
                ->where('nombre', 'like', '%' .$searchValue . '%')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                );

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
        $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $inicioMes = date('Y-m', strtotime($periodo_facturacion));
        $finMes = date('Y-m-t', strtotime($periodo_facturacion));

        $cuotasMultasFacturar = CuotasMultas::with('inmueble.zona', 'concepto')//CUOTAS Y MULTAS DEL NIT
            ->whereDate('fecha_inicio', '>=', $inicioMes.'-01')
            ->whereDate('fecha_fin', '<=', $finMes)
            ->get();

        $data = [
            'numero_total_unidades' => Entorno::where('nombre', 'numero_total_unidades')->first()->valor,
            'numero_registro_unidades' => $totalInmuebles,
            'area_total_m2' => Entorno::where('nombre', 'area_total_m2')->first()->valor,
            'area_registro_m2' => $areaM2Total,
            'valor_total_presupuesto' => Entorno::where('nombre', 'valor_total_presupuesto_year_actual')->first()->valor,
            'valor_registro_presupuesto' => $valorRegistroPresupuesto,
            'valor_registro_coeficiente' => intval($coeficienteTotal * 100),
            'periodo_facturacion' => Entorno::where('nombre', 'periodo_facturacion')->first()->valor,
            'total_multas' => $cuotasMultasFacturar->sum('valor_total')
        ];

        return response()->json([
            'success'=>	true,
            'data' => $data
        ]);
    }
}