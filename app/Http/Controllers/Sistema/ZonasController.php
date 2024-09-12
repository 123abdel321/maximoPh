<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Zonas;
use App\Models\Sistema\InmuebleNit;

class ZonasController extends Controller
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
        return view('pages.tablas.zonas.zonas-view');
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

            $searchValue = $search_arr['value']; // Search value

            $zonas = Zonas::orderBy('id', 'DESC')
                ->with('cecos')
                ->where('nombre', 'like', '%' .$searchValue . '%')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                );

            $zonasTotals = $zonas->get();

            $zonasPaginate = $zonas->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $zonasTotals->count(),
                'iTotalDisplayRecords' => $zonasTotals->count(),
                'data' => $zonasPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Zonas generados con exito!'
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
            'nombre' => 'required|min:1|max:200|unique:max.zonas,nombre',
            'id_centro_costos' => 'nullable|exists:sam.centro_costos,id',
            'tipo' => 'nullable'
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

            $zona = Zonas::create([
                'nombre' => $request->get('nombre'),
                'id_centro_costos' => $request->get('id_centro_costos'),
                'tipo' => $request->get('tipo'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $zona,
                'message'=> 'Zona creada con exito!'
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
            'id' => 'required|exists:max.zonas,id',
            'nombre' => ['required','min:1','max:200',
                function($attribute, $value, $fail) use ($request) {
                    $zonaOld = Zonas::find($request->get('id'));
                    if ($zonaOld->nombre != $request->get('nombre')) {
                        $zonaNew = Zonas::where('nombre', $request->get('nombre'));
                        if ($zonaNew->count()) {
                            $fail("La nombre de la zona ".$value." ya existe.");
                        }
                    }
                }],
            'id_centro_costos' => 'nullable|exists:sam.centro_costos,id',
            'tipo' => 'nullable'
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

            $zona = Zonas::where('id', $request->get('id'))
                ->update([
                    'nombre' => $request->get('nombre'),
                    'id_centro_costos' => $request->get('id_centro_costos'),
                    'tipo' => $request->get('tipo'),
                    'updated_by' => request()->user()->id
                ]);

            //ACTUAIZAR DATOS EN NITS
            $nitsInmuebles = InmuebleNit::with('inmueble.zona')
                ->whereHas('inmueble', function ($query) use ($request) {
                    $query->whereHas('zona', function ($q) use ($request) {
                        $q->where('id_zona', $request->get('id'));
                    });
                })
                ->groupBy('id_nit')
                ->get();
    
            foreach ($nitsInmuebles as $nit) {
    
                $inmueblesNits = InmuebleNit::with('inmueble.zona')
                    ->whereHas('inmueble', function ($query) use ($request) {
                        $query->whereHas('zona', function ($q) use ($request) {
                            $q->where('id_zona', $request->get('id'));
                        });
                    })
                    ->where('id_nit', $nit->id_nit)
                    ->get();

                $apartamentos = '';
    
                if (count($inmueblesNits)) {
                    foreach ($inmueblesNits as $key => $inmuebleNit) {
                        $apartamentos.= $inmuebleNit->inmueble->nombre.' - '.$inmuebleNit->inmueble->zona->nombre.', ';
                    }
                }
                $nit->nit->apartamentos = rtrim($apartamentos, ", ");
                $nit->nit->save();
            }

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $zona,
                'message'=> 'Zona actualizada con exito!'
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
            'id' => 'required|exists:max.zonas,id',
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

            Zonas::where('id', $request->get('id'))->delete();

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Zona eliminada con exito!'
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
        $zonas = Zonas::select(
            \DB::raw('*'),
            \DB::raw("nombre as text")
        );

        if ($request->get("q")) {
            $zonas->where('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $zonas->orderBy('nombre', 'ASC')->paginate(40);
    }
}