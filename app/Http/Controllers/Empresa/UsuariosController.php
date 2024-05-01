<?php

namespace App\Http\Controllers\Empresa;

use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\User;
use App\Models\Empresa\RolesGenerales;
use App\Models\Empresa\UsuarioEmpresa;
use App\Models\Empresa\UsuarioPermisos;

class UsuariosController extends Controller
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

    public function index (Request $request)
    {
        $data = [
            'roles' => RolesGenerales::where('id', '!=', 1)->get()
        ];

        return view('pages.configuracion.usuarios.usuarios-view', $data);
    }

    public function generate (Request $request)
    {
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

        $usuarios = User::orderBy($columnName,$columnSortOrder)
            ->with('permisos.rol')
            ->where('id_empresa', $request->user()['id_empresa'])
            ->withWhereHas('permisos', function ($query) use ($request){
                $query->where('id_empresa', $request->user()['id_empresa']);
            })
            ->select(
                '*',
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                'created_by',
                'updated_by'
            );

        if ($searchValue) {
            $usuarios = $usuarios->where('username', 'like', '%' .$searchValue. '%')
                ->orWhere('firstname', 'like', '%' .$searchValue. '%')
                ->orWhere('lastname', 'like', '%' .$searchValue. '%')
                ->orWhere('email', 'like', '%' .$searchValue. '%');
        }

        $usuarios = $usuarios->where('id_empresa', $request->user()['id_empresa']);
        $usuariosTotals = $usuarios->get();

        $usuariosPaginate = $usuarios->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $usuariosTotals->count(),
            'iTotalDisplayRecords' => $usuariosTotals->count(),
            'data' => $usuariosPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Usuarios cargados con exito!'
        ]);
    }

    public function create (Request $request)
    {        
        $rules = [
            'usuario' => 'required|string|min:1|unique:App\Models\User,username',
            'email' => 'required|email|string|max:255|unique:App\Models\User,email',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255'
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

            $rol = RolesGenerales::where('id', $request->get('rol_usuario'))->first();

            $usuario = User::create([
                'username' => $request->get('usuario'),
                'id_empresa' => $request->user()['id_empresa'],
                'has_empresa' => $request->user()['has_empresa'],
                'firstname' => $request->get('firstname'),
                'lastname' => $request->get('lastname'),
                'email' => $request->get('email'),
                'address' => $request->get('address'),
                'password' => $request->get('password'),
                'telefono' => $request->get('telefono'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            UsuarioEmpresa::updateOrCreate([
                'id_usuario' => $usuario->id,
                'id_empresa' => request()->user()->id_empresa
            ],[
                'id_rol' => $rol->id, // ROL PROPIETARIO
                'estado' => 1, // default: 1 activo
            ]);

            UsuarioPermisos::updateOrCreate([
                'id_user' => $usuario->id,
                'id_empresa' => request()->user()->id_empresa
            ],[
                'id_rol' => $rol->id, // ROL PROPIETARIO
                'ids_permission' => $rol->ids_permission
            ]);

            DB::connection('clientes')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $usuario,
                'message'=> 'Usuario creado con exito!'
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
            'id' => 'required|exists:App\Models\User,id',
            'usuario' => 'required|string|min:1|unique:App\Models\User,username',
            "usuario" => [
                "required","string",
				function ($attribute, $value, $fail) use ($request) {
                    $existeUsuario = User::where('username', $request->get('usuario'))->where('id', '!=', $request->get('id'));
					if ($existeUsuario->count()) {
                        $fail("El usuario (".$value.") ya se encuentra en uso.");
                    }
				},
            ],
            "email" => [
                "required","email","string","max:255",
				function ($attribute, $value, $fail) use ($request) {
                    $existeCorreo = User::where('email', $request->get('email'))->where('id', '!=', $request->get('id'));
					if ($existeCorreo->count()) {
                        $fail("El correo (".$value.") ya se encuentra en uso.");
                    }
				},
            ],
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255'
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

            $rol = RolesGenerales::where('id', $request->get('rol_usuario'))->first();

            $usuario = User::where('id', $request->get('id'))->first();
            $usuario->username = $request->get('usuario');
            $usuario->id_empresa = $request->user()['id_empresa'];
            $usuario->has_empresa = $request->user()['has_empresa'];
            $usuario->firstname = $request->get('firstname');
            $usuario->lastname = $request->get('lastname');
            $usuario->email = $request->get('email');
            $usuario->address = $request->get('address');
            $usuario->telefono = $request->get('telefono');
            $usuario->facturacion_rapida = $request->get('facturacion_rapida');
            $usuario->updated_by = request()->user()->id;
            $usuario->save();

            if ($request->get('password')) {
                $usuario->update([
                    'password' => $request->get('password')
                ]);
            }

            UsuarioEmpresa::updateOrCreate([
                'id_usuario' => $usuario->id,
                'id_empresa' => request()->user()->id_empresa
            ],[
                'id_rol' => $rol->id, // ROL PROPIETARIO
                'estado' => 1, // default: 1 activo
            ]);

            UsuarioPermisos::updateOrCreate([
                'id_user' => $usuario->id,
                'id_empresa' => request()->user()->id_empresa
            ],[
                'id_rol' => $rol->id, // ROL PROPIETARIO
                'ids_permission' => $rol->ids_permission
            ]);

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,   
                'data' => $usuario,
                'message'=> 'Usuario actualizado con exito!'
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
        $totalRows = $request->has("totalRows") ? $request->get("totalRows") : 40;

        $user = DB::connection('clientes')->table('usuario_empresas AS UE')
            ->select(
                '*',
                DB::raw("CONCAT_WS(' ', U.firstname, U.lastname) AS text")
            )
            ->leftJoin('users AS U', 'UE.id_usuario', 'U.id')
            ->where('UE.id_empresa', $request->user()['id_empresa']);

        if ($request->get("search")) {
            $user->where('U.firstname', 'LIKE', $request->get("search") . '%')
                ->orWhere('U.lastname', 'LIKE', $request->get("search") . '%');
        }

        return $user->paginate($totalRows);
    }
}