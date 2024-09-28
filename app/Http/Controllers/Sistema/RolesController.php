<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Roles;
use App\Models\Sistema\InmuebleNit;
use App\Models\Empresa\RolesGenerales;
use App\Models\Empresa\ComponentesMenu;

class RolesController extends Controller
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
        $componentes = ComponentesMenu::whereNull("id_padre")
            ->with('hijos.permisos')
            ->orderBy('orden_menu', 'ASC')
            ->get();

        $data = [
            'componentes' => $componentes
        ];

        return view('pages.configuracion.roles.roles-view', $data);
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

            $roles = RolesGenerales::orderBy('id', 'DESC')
                // ->where('nombre', 'like', '%' .$searchValue . '%')
                ->whereIn('id_empresa', [0, request()->user()->id_empresa])
                ->where('estado', 1)
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                );

            $rolesTotals = $roles->get();

            $rolesPaginate = $roles->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $rolesTotals->count(),
                'iTotalDisplayRecords' => $rolesTotals->count(),
                'data' => $rolesPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Roles generados con exito!'
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
            'nombre' => 'required',
            'permisos' => 'required'
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
            DB::connection('clientes')->beginTransaction();

            $zona = RolesGenerales::create([
                'nombre' => $request->get('nombre'),
                'estado' => 1,
                'id_empresa' => $request->user()['id_empresa'],
                'ids_permission' => $request->get('permisos'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            DB::connection('clientes')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $zona,
                'message'=> 'Rol creado con exito!'
            ]);
            
        } catch (Exception $e) {
            DB::connection('clientes')->rollback();
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
            'id' => 'required|exists:App\Models\Empresa\RolesGenerales,id',
            'nombre' => ['required','min:1','max:200',
                function($attribute, $value, $fail) use ($request) {
                    $zonaOld = RolesGenerales::find($request->get('id'));
                    if ($zonaOld->nombre != $request->get('nombre')) {
                        $zonaNew = RolesGenerales::where('nombre', $request->get('nombre'));
                        if ($zonaNew->count()) {
                            $fail("La nombre de la zona ".$value." ya existe.");
                        }
                    }
                }],
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
            DB::connection('clientes')->beginTransaction();

            $rol = RolesGenerales::where('id', $request->get('id'))
                ->update([
                    'nombre' => $request->get('nombre'),
                    'ids_permission' => $request->get('permisos'),
                    'updated_by' => request()->user()->id
                ]);

            DB::connection('clientes')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $rol,
                'message'=> 'Rol actualizado con exito!'
            ]);
                
        } catch (Exception $e) {
            DB::connection('clientes')->rollback();
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
            'id' => 'required|exists:App\Models\Empresa\RolesGenerales,id',
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
            DB::connection('clientes')->beginTransaction();

            RolesGenerales::where('id', $request->get('id'))->delete();

            DB::connection('clientes')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Zona eliminada con exito!'
            ]);

        } catch (Exception $e) {
            DB::connection('clientes')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function combo (Request $request)
    {
        $roles = Roles::select(
            \DB::raw('*'),
            \DB::raw("nombre as text")
        );

        if ($request->get("q")) {
            $roles->where('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $roles->orderBy('nombre', 'ASC')->paginate(40);
    }
}