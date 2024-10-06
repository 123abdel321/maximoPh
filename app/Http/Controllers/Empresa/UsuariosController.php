<?php

namespace App\Http\Controllers\Empresa;

use DB;
use Exception;
use App\Mail\GeneralEmail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessSyncronizarUsuarios;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\User;
use App\Models\Empresa\Empresa;
use App\Models\Portafolio\Nits;
use App\Models\Sistema\Porteria;
use App\Models\Sistema\InmuebleNit;
use App\Models\Empresa\RolesGenerales;
use App\Models\Empresa\UsuarioEmpresa;
use App\Models\Empresa\UsuarioPermisos;
use App\Models\Sistema\ArchivosGenerales;

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
        $usuarioEmpresa = UsuarioEmpresa::where('id_empresa', $request->user()['id_empresa'])
            ->where('id_usuario', $request->user()['id'])
            ->first();

        $data = [
            'roles' => RolesGenerales::where('id', '!=', 1)->get(),
            'usuario_nit' => $usuarioEmpresa,
        ];

        return view('pages.configuracion.usuarios.usuarios-view', $data);
    }

    public function generate (Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");
        $id_empresa = $request->user()['id_empresa'];
        $rol_maximo = $request->user()['rol_maximo'];

        $filterSearch = '';
        $filterMaximo = '';
        $filterGeneral = '';

        $searchValue = $request->get('search');

        if ($searchValue) {
            $filterSearch = 'AND US.firstname LIKE "%'.$searchValue.'%" ';
            $filterSearch.= 'OR US.lastname LIKE "%'.$searchValue.'%" ';
            $filterSearch.= 'OR US.email LIKE "%'.$searchValue.'%" ';
            $filterSearch.= 'OR US.username LIKE "%'.$searchValue.'%" ';
        }

        if ($request->get('id_rol')) {
            $filterGeneral.= 'AND UE.id_rol = '.$request->get('id_rol').' ';
        }

        if ($request->get('id_nit')) {
            $filterGeneral.= 'AND UE.id_nit = '.$request->get('id_nit').' ';
        }
        
        if (!$rol_maximo) {
            $filterMaximo = 'AND US.rol_maximo = 0 ';
        }

        $totalUsuarios = DB::connection('clientes')->select("SELECT
                COUNT(US.id) AS total_usuarios
            FROM
                users US
                
            LEFT JOIN usuario_empresas UE ON US.id = UE.id_usuario

            WHERE UE.id_empresa = {$id_empresa}
                {$filterSearch}
            ");

        $totalUsuarios = collect($totalUsuarios);
        if (count($totalUsuarios)) $totalUsuarios = $totalUsuarios[0]->total_usuarios;
        else $totalUsuarios = 0;

        $usuarios = DB::connection('clientes')->select("SELECT
                US.id,
                UE.id_rol,
                UE.id_nit,
                RG.nombre AS nombre_rol,
                US.username,
                US.firstname,
                US.lastname,
                US.email,
                US.telefono,
                US.address,
                US.email_verified_at,
                US.created_by,
                US.updated_by,
                US.created_at,
                US.updated_at
            FROM
                users US
                
            LEFT JOIN usuario_empresas UE ON US.id = UE.id_usuario
            LEFT JOIN roles_generales RG ON UE.id_rol = RG.id

            WHERE UE.id_empresa = {$id_empresa}
                {$filterSearch}
                {$filterMaximo}
                {$filterGeneral}

            LIMIT {$rowperpage} OFFSET {$start}
        ");

        $usuarios = collect($usuarios);
        
        $dataUsuarios = [];

        if (count($usuarios)) {
            foreach ($usuarios as $usuario) {
                $nit = null;
                if ($usuario->id_nit) {
                    $nit = Nits::where('id', $usuario->id_nit)->first();
                }
                
                $dataUsuarios[] = (object)[
                    'id' => $usuario->id,
                    'id_rol' => $usuario->id_rol,
                    'id_nit' => $usuario->id_nit,
                    'nombre_completo' => $nit ? $nit->nombre_completo.' '.$nit->apartamentos : '',
                    'email_verified_at' => $usuario->email_verified_at,
                    'nombre_rol' => $usuario->nombre_rol,
                    'username' => $usuario->username,
                    'firstname' => $usuario->firstname,
                    'lastname' => $usuario->lastname,
                    'email' => $usuario->email,
                    'telefono' => $usuario->telefono,
                    'address' => $usuario->address,
                    'created_by' => $usuario->created_by,
                    'updated_by' => $usuario->updated_by,
                    'fecha_creacion' => Carbon::parse($usuario->created_at)->format('Y-m-d H:i:s'),
                    'fecha_edicion' => Carbon::parse($usuario->updated_at)->format('Y-m-d H:i:s'),
                ];
            }
        }

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $totalUsuarios,
            'iTotalDisplayRecords' => $totalUsuarios,
            'data' => $dataUsuarios,
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

        if ($request->get('rol_usuario') != 1) {
            if (!$request->get('id_nit')) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>['id_nit' => 'El nit es obligatorio']
                ], 422);
            }
            $nit = Nits::where('id', $request->get('id_nit'))->first();
            if (!$nit) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>['id_nit' => 'El nit es invalido']
                ], 422);
            }
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
                'id_nit' => $request->get('id_nit')
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

        if ($request->get('rol_usuario') != 1) {
            if (!$request->get('id_nit')) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'El nit es obligatorio'
                ], 422);
            }
            $nit = Nits::where('id', $request->get('id_nit'))->first();
            if (!$nit) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'El nit es invalido'
                ], 422);
            }
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
                'id_nit' => $request->get('id_nit')
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

    public function delete (Request $request)
    {
        $rules = [
            'id' => 'required|exists:App\Models\User,id',
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

            $totalEmpresas = UsuarioEmpresa::where('id_usuario', $request->get('id'))->count();

            if ($totalEmpresas == 1) User::where('id', $request->get('id'))->delete();
            
            UsuarioEmpresa::where('id_usuario', $request->get('id'))
                ->where('id_empresa', request()->user()->id_empresa)
                ->delete();

            UsuarioPermisos::where('id_user', $request->get('id'))
                ->where('id_empresa', request()->user()->id_empresa)
                ->delete();

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

    public function sync2 (Request $request)
    {
        try {
            DB::connection('clientes')->beginTransaction();

            
            $inmueblesNits = InmuebleNit::whereNotNull('id_nit')
                ->groupBy('id_nit');

            if ($request->get('id_zona')) {
                $inmueblesNits->whereHas('inmueble',  function ($query) use($request) {
                    $query->where('id_zona', $request->get('id_zona'));
                });
            }

            if ($request->get('id_inmueble')) {
                $inmueblesNits->where('id_inmueble', $request->get('id_inmueble'));
            }

            if ($request->get('id_nit')) {
                $inmueblesNits->where('id_nit', $request->get('id_nit'));
            }
            $dataInmuebles = $inmueblesNits->get();
            
            $empresa = Empresa::find(request()->user()->id_empresa);   
            
            foreach ($dataInmuebles as $dataInmueble) {
                
                $usuario = UsuarioEmpresa::where('id_nit', $dataInmueble->id_nit)
                    ->count();
                
                if (!$usuario) {
                    $nit = Nits::where('id', $dataInmueble->id_nit)
                        ->first();

                    $usuarioPropietario = User::create([
                        'id_empresa' => request()->user()->id_empresa,
                        'has_empresa' => $empresa->token_db_maximo,
                        'firstname' => $nit->primer_nombre,
                        'lastname' => $nit->primer_apellido,
                        'username' => '123'.$nit->primer_nombre.'321',
                        'email' => $nit->email,
                        'telefono' => $nit->telefono_1,
                        'password' => $nit->numero_documento,
                        'address' => $nit->direccion,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);
                    
                    $idRol = $dataInmueble->tipo ? 3 : 5;
                    $rolPropietario = RolesGenerales::find($idRol);
                    
                    UsuarioEmpresa::updateOrCreate([
                        'id_usuario' => $usuarioPropietario->id,
                        'id_empresa' => request()->user()->id_empresa
                    ],[
                        'id_rol' => $idRol, // 3: PROPIETARIO; 4:RESIDENTE
                        'id_nit' => $nit->id,
                        'estado' => 1, // default: 1 activo
                    ]);

                    UsuarioPermisos::updateOrCreate([
                        'id_user' => $usuarioPropietario->id,
                        'id_empresa' => request()->user()->id_empresa
                    ],[
                        'id_rol' => $idRol, // ROL PROPIETARIO
                        'ids_permission' => $rolPropietario->ids_permission
                    ]);

                    $portero = Porteria::where('id_usuario', $usuarioPropietario->id)
                        ->whereIn('tipo_porteria', [0,1])
                        ->first();

                    if ($portero) {
                        $portero->tipo_porteria = $request->get('tipo') == 1 ? 1 : 0;
                        $portero->nombre = $nit->primer_nombre.' '.$nit->primer_apellido;
                        $portero->dias = $request->get('tipo') != 0 ? '1,2,3,4,5,6,7' : null;
                        $portero->updated_by = request()->user()->id;
                        $portero->save();
                    } else {
                        $portero = Porteria::create([
                            'id_usuario' => $usuarioPropietario->id,
                            'id_nit' => $nit->id,
                            'tipo_porteria' => $dataInmueble->tipo == 1 ? 1 : 0,
                            'nombre' => $nit->primer_nombre.' '.$nit->primer_apellido,
                            'dias' => !$dataInmueble->tipo ? '1,2,3,4,5,6,7' : null,
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id,
                        ]);
                    }

                    $tieneImagen = ArchivosGenerales::where('relation_type', 1)
                        ->where('relation_id', $portero->id);
                    
                    if ($nit->logo_nit && !$tieneImagen->count()) {
                        $archivo = new ArchivosGenerales([
                            'tipo_archivo' => 'imagen',
                            'url_archivo' => $nit->logo_nit,
                            'estado' => 1,
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);
            
                        $archivo->relation()->associate($portero);
                        $portero->archivos()->save($archivo);
                    }
                }
            }

            DB::connection('clientes')->commit();
            
            return response()->json([
                "success"=>true,
                'data' => [],
                "message"=>'mensaje'
            ], 200);

        } catch (Exception $e) {
            DB::connection('clientes')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function sync (Request $request)
    {
        try {

            $data = $request->only(['id_inmueble', 'id_nit', 'id_zona']);
            ProcessSyncronizarUsuarios::dispatch(request()->user()->id, request()->user()->id_empresa, $data);

            return response()->json([
                "success"=>true,
                'data' => [],
                "message"=>'Sincronizando usuarios...'
            ], 200);

        } catch (Exception $e) {
            
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function welcome (Request $request)
    {
        $rules = [
            'id' => 'required|exists:App\Models\User,id',
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

            $usuario = User::find($request->get('id'));
            $usuario->code_general = $this->generateRandomString(5);
            $usuario->limit_general = Carbon::now()->format('Y-m-d H:i:s');
            $usuario->save();

            $code = $request->get('id').'$'.$usuario->code_general;
            $url_welcome = 'welcome/?code='.base64_encode($code);

            $nombreUsuario = $usuario->firstname;
            $nombreUsuario.= $usuario->lastname ? ' '.$usuario->lastname : '';

            Mail::to($usuario->email)
                ->cc('noreply@maximoph.com')
                ->bcc('bcc@maximoph.com')
                ->queue(new GeneralEmail('BIENVENIDO A MAXIMOPH', 'emails.welcome', [
                    'nombre' => $nombreUsuario,
                    'url' => $url_welcome,
                ]));

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Email enviado con exito!'
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

    public function welcomeMultiple (Request $request)
    {
        $usuarios = explode(",", $request->get('usuarios'));

        if (count($usuarios)) {
            foreach ($usuarios as $idUsuario) {
                $usuario = User::where('id', $idUsuario)->first();
                if ($usuario && $usuario->firstname != 'NORTEAMERICA S.A.S.' && !$usuario->email_verified_at) {
                    $usuario->code_general = $this->generateRandomString(5);
                    $usuario->limit_general = Carbon::now()->format('Y-m-d H:i:s');
                    $usuario->save();
            
                    $code = $idUsuario.'$'.$usuario->code_general;
                    $url_welcome = 'welcome/?code='.base64_encode($code);
                    
                    $nombreUsuario = $usuario->firstname;
                    $nombreUsuario.= $usuario->lastname ? ' '.$usuario->lastname : '';
                    
                    Mail::to($usuario->email)
                        ->cc('noreply@maximoph.com')
                        ->bcc('bcc@maximoph.com')
                        ->queue(new GeneralEmail('BIENVENIDO A MAXIMOPH', 'emails.welcome', [
                            'nombre' => $nombreUsuario,
                            'url' => $url_welcome,
                        ]));
                }
            }
        }
        return 'correos enviados con exito';
    }

    private function generateRandomString($length = 20) {
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}
}