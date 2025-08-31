<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Proyecto;

class ProyectosController extends Controller
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
        return view('pages.tareas.proyectos.proyectos-view');
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

            $proyectos = Proyecto::orderBy('id', 'DESC')
                ->with('responsable')
                ->where('nombre', 'like', '%' .$searchValue . '%')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d') AS fecha_inicio"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d') AS fecha_fin"),
                    'created_by',
                    'updated_by'
                );

            $proyectosTotals = $proyectos->get();

            $proyectosPaginate = $proyectos->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $proyectosTotals->count(),
                'iTotalDisplayRecords' => $proyectosTotals->count(),
                'data' => $proyectosPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Proyectos generados con exito!'
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
            'nombre' => 'required|min:1|max:200|unique:max.proyectos,nombre',
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'valor_total' => 'required',
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

            $proyecto = Proyecto::create([
                'nombre' => $request->get('nombre'),
                'id_usuario' => $request->get('id_usuario'),
                'fecha_inicio' => $request->get('fecha_inicio'),
                'fecha_fin' => $request->get('fecha_fin'),
                'valor_total' => $request->get('valor_total'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $proyecto,
                'message'=> 'Proyecto creadO con exito!'
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
            'id' => 'required|exists:max.proyectos,id',
            'nombre' => ['required','min:1','max:200',
                function($attribute, $value, $fail) use ($request) {
                    $proyectoOld = Proyecto::find($request->get('id'));
                    if ($proyectoOld->nombre != $request->get('nombre')) {
                        $proyectoNew = Proyecto::where('nombre', $request->get('nombre'));
                        if ($proyectoNew->count()) {
                            $fail("La nombre de la proyecto ".$value." ya existe.");
                        }
                    }
                }],
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'valor_total' => 'required',
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

            $proyecto = Proyecto::where('id', $request->get('id'))
                ->update([
                    'nombre' => $request->get('nombre'),
                    'id_usuario' => $request->get('id_usuario'),
                    'fecha_inicio' => $request->get('fecha_inicio'),
                    'fecha_fin' => $request->get('fecha_fin'),
                    'valor_total' => $request->get('valor_total'),
                    'updated_by' => request()->user()->id
                ]);

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $proyecto,
                'message'=> 'Proyecto actualizada con exito!'
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
            'id' => 'required|exists:max.proyectos,id',
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

            Proyecto::where('id', $request->get('id'))->delete();

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Proyecto eliminada con exito!'
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
        $proyectos = Proyecto::select(
            \DB::raw('*'),
            \DB::raw("nombre as text")
        );

        if ($request->get("q")) {
            $proyectos->where('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $proyectos->orderBy('nombre', 'ASC')->paginate(40);
    }
}