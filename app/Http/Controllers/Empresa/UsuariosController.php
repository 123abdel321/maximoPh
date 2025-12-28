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

use App\Models\Empresa\EnvioEmail;
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
            'unique' => 'El :attribute ya existe.',
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

    public function generate(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");
        $searchValue = $request->get('search')['value'] ?? '';
        
        $id_empresa = $request->user()['id_empresa'];
        $rol_maximo = $request->user()['rol_maximo'];

        // Consulta base usando Eloquent/Query Builder
        $usuariosQuery = DB::connection('clientes')
            ->table('users as US')
            ->select(
                'US.id',
                'UE.id_rol',
                'UE.id_nit',
                'RG.nombre as nombre_rol',
                'US.username',
                'US.firstname',
                'US.lastname',
                'US.email',
                'US.telefono',
                'US.address',
                'US.email_verified_at',
                'US.created_by',
                'US.updated_by',
                'US.created_at',
                'US.updated_at'
            )
            ->leftJoin('usuario_empresas as UE', function($join) use ($id_empresa) {
                $join->on('US.id', '=', 'UE.id_usuario')
                    ->where('UE.id_empresa', '=', $id_empresa);
            })
            ->leftJoin('roles_generales as RG', 'UE.id_rol', '=', 'RG.id')
            ->where('UE.id_empresa', $id_empresa);

        // Filtro por rol máximo del usuario
        if (!$rol_maximo) {
            $usuariosQuery->where('US.rol_maximo', 0);
        }

        // Filtro por id_rol
        if ($request->filled('id_rol')) {
            $usuariosQuery->where('UE.id_rol', $request->get('id_rol'));
        }

        // Filtro por id_nit
        if ($request->filled('id_nit')) {
            $usuariosQuery->where('UE.id_nit', $request->get('id_nit'));
        }

        // Búsqueda general (sin SQL injection)
        if (!empty($searchValue)) {
            $usuariosQuery->where(function($query) use ($searchValue) {
                $query->where('US.firstname', 'like', '%' . $searchValue . '%')
                    ->orWhere('US.lastname', 'like', '%' . $searchValue . '%')
                    ->orWhere('US.email', 'like', '%' . $searchValue . '%')
                    ->orWhere('US.username', 'like', '%' . $searchValue . '%');
            });
        }

        // Obtener total de registros sin paginación
        $totalUsuarios = $usuariosQuery->count();

        // Aplicar paginación
        if ($rowperpage > 0) {
            $usuariosQuery->skip($start)->take($rowperpage);
        }
        
        $usuarios = $usuariosQuery->get();

        // Procesar los resultados
        $dataUsuarios = [];
        
        // Precargar NITs para evitar N+1 queries
        $idsNit = $usuarios->pluck('id_nit')->filter()->unique()->values();
        $nits = Nits::whereIn('id', $idsNit)->get()->keyBy('id');
        
        foreach ($usuarios as $usuario) {
            $nombreCompletoNit = '';
            
            if ($usuario->id_nit && isset($nits[$usuario->id_nit])) {
                $nit = $nits[$usuario->id_nit];
                $nombreCompletoNit = $nit->nombre_completo . ($nit->apartamentos ? ' ' . $nit->apartamentos : '');
            }
            
            $dataUsuarios[] = [
                'id' => $usuario->id,
                'id_rol' => $usuario->id_rol,
                'id_nit' => $usuario->id_nit,
                'nombre_completo' => $nombreCompletoNit,
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
                'fecha_creacion' => $usuario->created_at ? Carbon::parse($usuario->created_at)->format('Y-m-d H:i:s') : null,
                'fecha_edicion' => $usuario->updated_at ? Carbon::parse($usuario->updated_at)->format('Y-m-d H:i:s') : null,
            ];
        }

        return response()->json([
            'success' => true,
            'draw' => intval($draw),
            'iTotalRecords' => $totalUsuarios,
            'iTotalDisplayRecords' => $totalUsuarios,
            'data' => $dataUsuarios,
            'perPage' => $rowperpage,
            'message' => 'Usuarios cargados con éxito!'
        ]);
    }

    public function create(Request $request)
    {        
        $rules = [
            'usuario' => 'required|string|min:1',
            'email' => 'required|email|string|max:255',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255'
        ];
        
        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()){
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $validator->errors()
            ], 422);
        }

        // Validación del rol y NIT
        if ($request->get('rol_usuario') != 1) {
            if (!$request->get('id_nit')) {
                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => ['id_nit' => 'El nit es obligatorio']
                ], 422);
            }
            
            $nit = Nits::where('id', $request->get('id_nit'))->first();
            if (!$nit) {
                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => ['id_nit' => 'El nit es invalido']
                ], 422);
            }
        }

        try {
            DB::connection('clientes')->beginTransaction();

            $rol = RolesGenerales::where('id', $request->get('rol_usuario'))->first();
            
            if (!$rol) {
                throw new Exception("El rol especificado no existe");
            }

            $empresaActualId = request()->user()->id_empresa;
            $empresaActualHash = request()->user()->has_empresa;
            $usuarioCreadorId = request()->user()->id;

            // Verificar si el usuario ya existe por username o email
            $usuarioExistente = User::where('username', $request->get('usuario'))
                ->orWhere('email', $request->get('email'))
                ->first();

            // Verificar si el usuario ya está asociado a esta empresa
            $yaAsociadoEmpresa = false;
            if ($usuarioExistente) {
                $yaAsociadoEmpresa = UsuarioEmpresa::where('id_usuario', $usuarioExistente->id)
                    ->where('id_empresa', $empresaActualId)
                    ->exists();
            }

            // Si el usuario existe y ya está asociado a esta empresa, retornar error
            if ($usuarioExistente && $yaAsociadoEmpresa) {
                DB::connection('clientes')->rollback();
                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => "El usuario ya existe y está asociado a esta empresa"
                ], 422);
            }

            $usuario = null;

            if ($usuarioExistente && !$yaAsociadoEmpresa) {
                // CASO 1: Usuario existe pero no está asociado a esta empresa
                $usuario = $usuarioExistente;
                
                // Actualizar datos del usuario si se proporcionaron
                $actualizacionUsuario = [
                    'updated_by' => $usuarioCreadorId,
                ];
                
                // Solo actualizar campos si se proporcionan
                if ($request->has('firstname')) {
                    $actualizacionUsuario['firstname'] = $request->get('firstname');
                }
                if ($request->has('lastname')) {
                    $actualizacionUsuario['lastname'] = $request->get('lastname');
                }
                if ($request->has('address')) {
                    $actualizacionUsuario['address'] = $request->get('address');
                }
                if ($request->has('telefono')) {
                    $actualizacionUsuario['telefono'] = $request->get('telefono');
                }
                
                $usuario->update($actualizacionUsuario);
                
            } else {
                // CASO 2: Usuario no existe, crearlo nuevo
                // Validar unicidad para nuevo usuario
                $uniqueRules = [
                    'usuario' => 'unique:App\Models\User,username',
                    'email' => 'unique:App\Models\User,email',
                ];
                
                $uniqueValidator = Validator::make($request->all(), $uniqueRules);
                
                if ($uniqueValidator->fails()) {
                    DB::connection('clientes')->rollback();
                    return response()->json([
                        "success" => false,
                        'data' => [],
                        "message" => $uniqueValidator->errors()
                    ], 422);
                }

                $usuario = User::create([
                    'username' => $request->get('usuario'),
                    'id_empresa' => $empresaActualId,
                    'has_empresa' => $empresaActualHash,
                    'firstname' => $request->get('firstname'),
                    'lastname' => $request->get('lastname'),
                    'email' => $request->get('email'),
                    'address' => $request->get('address'),
                    'password' => $request->get('password'),
                    'telefono' => $request->get('telefono'),
                    'created_by' => $usuarioCreadorId,
                    'updated_by' => $usuarioCreadorId,
                ]);
            }

            // Asociar usuario a la empresa (crear o actualizar)
            UsuarioEmpresa::updateOrCreate(
                [
                    'id_usuario' => $usuario->id,
                    'id_empresa' => $empresaActualId
                ],
                [
                    'id_rol' => $rol->id,
                    'estado' => 1,
                    'id_nit' => $request->get('id_nit')
                ]
            );

            // Asignar permisos según el rol
            UsuarioPermisos::updateOrCreate(
                [
                    'id_user' => $usuario->id,
                    'id_empresa' => $empresaActualId
                ],
                [
                    'id_rol' => $rol->id,
                    'ids_permission' => $rol->ids_permission
                ]
            );

            // Si el usuario es nuevo, asignar el rol global (si es necesario)
            if (!$usuarioExistente) {
                // Esto depende de cómo manejes los roles en este proyecto
                // Si usas Spatie Permission como en el otro proyecto:
                // $usuario->syncRoles($rol);
            }

            DB::connection('clientes')->commit();

            $mensaje = $usuarioExistente 
                ? "Usuario asociado a la empresa con éxito!" 
                : "Usuario creado con éxito!";

            return response()->json([
                'success' => true,
                'data' => $usuario,
                'message' => $mensaje,
                'nuevo_usuario' => !$usuarioExistente
            ]);

        } catch (Exception $e) {
            DB::connection('clientes')->rollback();
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
            ], 422);
        }
    }

    public function update(Request $request)
    {
        $rules = [
            'id' => 'required|exists:App\Models\User,id',
            'usuario' => [
                'required',
                'string',
                'min:1',
                function ($attribute, $value, $fail) use ($request) {
                    $existeUsuario = User::where('username', $value)
                        ->where('id', '!=', $request->get('id'))
                        ->exists();
                    
                    if ($existeUsuario) {
                        $fail("El usuario ($value) ya se encuentra en uso.");
                    }
                },
            ],
            'email' => [
                'required',
                'email',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $existeCorreo = User::where('email', $value)
                        ->where('id', '!=', $request->get('id'))
                        ->exists();
                    
                    if ($existeCorreo) {
                        $fail("El correo ($value) ya se encuentra en uso.");
                    }
                },
            ],
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255'
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()) {
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $validator->errors()
            ], 422);
        }

        if ($request->get('rol_usuario') != 1) {
            if (!$request->get('id_nit')) {
                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => ['id_nit' => 'El nit es obligatorio']
                ], 422);
            }
            
            $nit = Nits::where('id', $request->get('id_nit'))->first();
            if (!$nit) {
                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => ['id_nit' => 'El nit es invalido']
                ], 422);
            }
        }

        try {
            DB::connection('max')->beginTransaction();

            $rol = RolesGenerales::where('id', $request->get('rol_usuario'))->first();
            
            if (!$rol) {
                throw new Exception("El rol especificado no existe");
            }

            $usuario = User::where('id', $request->get('id'))->first();
            
            if (!$usuario) {
                throw new Exception("Usuario no encontrado");
            }

            // Verificar que el usuario pertenezca a la empresa del usuario logeado
            // Esto es importante para seguridad multi-tenancy
            $perteneceEmpresa = UsuarioEmpresa::where('id_usuario', $usuario->id)
                ->where('id_empresa', request()->user()->id_empresa)
                ->exists();
            
            if (!$perteneceEmpresa) {
                throw new Exception("No tienes permisos para editar este usuario");
            }

            // Actualizar datos del usuario
            $usuario->username = $request->get('usuario');
            $usuario->firstname = $request->get('firstname');
            $usuario->lastname = $request->get('lastname');
            $usuario->email = $request->get('email');
            $usuario->address = $request->get('address');
            $usuario->telefono = $request->get('telefono');
            $usuario->facturacion_rapida = $request->get('facturacion_rapida');
            $usuario->updated_by = request()->user()->id;
            $usuario->save();

            // Actualizar password si se proporciona
            if ($request->filled('password')) {
                $usuario->update([
                    'password' => $request->get('password')
                ]);
            }

            // Actualizar la relación con la empresa
            UsuarioEmpresa::updateOrCreate(
                [
                    'id_usuario' => $usuario->id,
                    'id_empresa' => request()->user()->id_empresa
                ],
                [
                    'id_rol' => $rol->id,
                    'estado' => 1,
                    'id_nit' => $request->get('id_nit')
                ]
            );

            // Actualizar permisos
            UsuarioPermisos::updateOrCreate(
                [
                    'id_user' => $usuario->id,
                    'id_empresa' => request()->user()->id_empresa
                ],
                [
                    'id_rol' => $rol->id,
                    'ids_permission' => $rol->ids_permission
                ]
            );

            DB::connection('max')->commit();

            return response()->json([
                'success' => true,
                'data' => $usuario,
                'message' => 'Usuario actualizado con éxito!'
            ]);

        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success" => false,
                'data' => [],
                "message" => $e->getMessage()
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

            if (filter_var($usuario->email, FILTER_VALIDATE_EMAIL)) {

                $response = Mail::to($usuario->email)
                    ->send(new GeneralEmail('BIENVENIDO A MAXIMOPH', 'emails.welcome', [
                        'nombre' => $nombreUsuario,
                        'url' => $url_welcome,
                    ]));

                $sgMessageId = $response->getSymfonySentMessage()->getMessageId();
    
                EnvioEmail::create([
                    'id_nit' => '',
                    'id_empresa' => request()->user()->id_empresa,
                    'email' => $usuario->email,
                    'sg_message_id' => $sgMessageId,
                    'contexto' => 'emails.welcome',
                    'status' => 'en_cola'
                ]);
            }

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
                        ->queue(new GeneralEmail('BIENVENIDO A MAXIMOPH', 'emails.welcome', [
                            'nombre' => $nombreUsuario,
                            'url' => $url_welcome,
                        ]));

                    EnvioEmail::create([
                        'id_nit' => $nit->id,
                        'email' => $nit->email_2,
                        'contexto' => 'envio_factura'
                    ]);
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