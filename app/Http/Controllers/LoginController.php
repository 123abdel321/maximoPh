<?php

namespace App\Http\Controllers;

use DB;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
//MODELS
use App\Models\User;
use App\Models\Empresa\Empresa;
use App\Models\Portafolio\Nits;
use App\Models\Empresa\RolesGenerales;
use App\Models\Empresa\UsuarioEmpresa;
use App\Models\Empresa\UsuarioPermisos;
use Spatie\Permission\Models\Permission;

class LoginController extends Controller
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

    public function show()
    {
        return view('auth.login');
    }

    public function validateSession (Request $request)
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credenciales1 = ['email' => $request->email, 'password' => $request->password];
        $credenciales2 = ['username' => $request->email, 'password' => $request->password];

        if (Auth::attempt($credenciales1) || Auth::attempt($credenciales2)) {
            $request->session()->regenerate();
            $user =  User::find(Auth::user()->id);

            if($user->tokens()->where('tokenable_id', $user->id)
                ->where('name', 'web_token')
                ->exists()) {
                // $user->tokens()->delete();
            }

            $plainTextToken = '';
            if ($user->remember_token) {
                $token = $user->createToken("web_token");
                $plainTextToken = $token->plainTextToken;
                $user->remember_token = $plainTextToken;
            } else {
                $token = $user->createToken("web_token");
                $plainTextToken = $token->plainTextToken;
                $user->remember_token = $plainTextToken;
            }

            $idEmpresa = $user->id_empresa;

            if (!$idEmpresa) {
                $empresa = UsuarioEmpresa::where('id_usuario', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (!$empresa){
                    return response()->json([
                        'success'=>	true,
                        'access_token' => $plainTextToken,
                        'token_type' => 'Bearer',
                        'empresa' => '',
                        'notificacion_code' => '',
                        'message'=> 'Usuario logeado con exito!'
                    ], 200);
                }

                $idEmpresa = $empresa->id;
            }

            $usuarioEmpresa = UsuarioEmpresa::where('id_empresa', $request->user()['id_empresa'])
                ->where('id_usuario', $request->user()['id'])
                ->first();

            $empresaSelect = Empresa::where('id', $idEmpresa)->first();

            if (!$usuarioEmpresa->id_nit) {
                Config::set('database.connections.sam.database', $empresaSelect->token_db_portafolio);
                $findNit = Nits::where('email', $request->email)->first();
                if ($findNit) {
                    $usuarioEmpresa->id_nit = $findNit->id;
                    $usuarioEmpresa->save();
                }
            }
                
            $notificacionCode =  null;
            $notificacionCode = $empresaSelect->token_db_maximo.'_'.$user->id;
            $user->id_empresa = $empresaSelect->id;
            $user->has_empresa = $empresaSelect->token_db_maximo;
            $user->save();

            $usuarioPermisosEmpresa = UsuarioPermisos::where('id_user', $user->id)
                ->where('id_empresa', $empresaSelect->id)
                ->with('rol')
                ->first();

            $permisosNombre = Permission::whereIn('id', explode(',', $usuarioPermisosEmpresa->rol->ids_permission))->get();
            $nombrePermisos = [];
            foreach ($permisosNombre as $permisoNombre) {
                $nombrePermisos[] = $permisoNombre->name;
            }

            $user->syncPermissions($nombrePermisos);

            return response()->json([
                'success'=>	true,
                'access_token' => $plainTextToken,
                'token_api_portafolio' => $empresaSelect->token_api_portafolio,
                'token_type' => 'Bearer',
                'empresa' => $empresaSelect,
                'token_db_portafolio' => base64_encode($empresaSelect->token_db_portafolio),
                'notificacion_code' => $notificacionCode,
                'fondo_sistema' => $user->fondo_sistema,
                'message'=> 'Usuario logeado con exito!'
            ], 200);
        }

        return response()->json([
    		'success'=>	false,
    		'data' => '',
    		'message'=> 'The provided credentials do not match our records.'
    	], 422);
    }

    public function logout(Request $request)
    {
        $user =  User::find(Auth::user()->id);
        $user->tokens()->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
		return response()->json([
			'message' => 'Successfully logged out',
			"success"=>true
		]);

        return response()->json([
    		'success'=>	false,
    		'data' => '',
    		'message'=> 'logout true'
    	], 200);
    }

    public function logoutApi (Request $request)
    {
        $user =  User::find(Auth::user()->id);
        $user->tokens()->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
    		'success'=>	false,
    		'data' => '',
    		'message'=> 'logout true'
    	], 200);
    }

    public function selectEmpresa (Request $request)
    {

        $rules = [
            'id_empresa' => 'required|exists:clientes.empresas,id',
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

            $user =  User::find($request->user()['id']);
            $empresaSelect = Empresa::where('id', $request->get('id_empresa'))->first();

            $usuarioEmpresa = UsuarioEmpresa::where('id_empresa', $request->get('id_empresa'))
                ->where('id_usuario', $request->user()['id'])
                ->first();

            if (!$usuarioEmpresa && request()->user()->rol_maximo == 1) {

                $rolDios = RolesGenerales::where('nombre', 'DIOS')->first();
                
                $usuarioEmpresa = UsuarioEmpresa::create([
                    'id_usuario' => $request->user()['id'],
                    'id_empresa' => $request->get('id_empresa'),
                    'id_rol' => 1,
                    'id_nit' => '',
                    'estado' => 1,
                ]);

                $usuarioPermisosEmpresa = UsuarioPermisos::create([
                    'id_user' => $request->user()['id'],
                    'id_empresa' => $request->get('id_empresa'),
                    'id_rol' => 1,
                    'ids_permission' => $rolDios->ids_permission
                ]);
                $permisosNombre = Permission::whereIn('id', explode(',', $usuarioPermisosEmpresa->rol->ids_permission))->get();
                $nombrePermisos = [];
                foreach ($permisosNombre as $permisoNombre) {
                    $nombrePermisos[] = $permisoNombre->name;
                }

                $user->syncPermissions($nombrePermisos);
            } else {
                $usuarioPermisosEmpresa = UsuarioPermisos::where('id_user', $user->id)
                    ->where('id_empresa', $empresaSelect->id)
                    ->with('rol')
                    ->first();

                $permisosNombre = Permission::whereIn('id', explode(',', $usuarioPermisosEmpresa->rol->ids_permission))->get();
                $nombrePermisos = [];
                foreach ($permisosNombre as $permisoNombre) {
                    $nombrePermisos[] = $permisoNombre->name;
                }

                $user->syncPermissions($nombrePermisos);
            }

            $notificacionCode =  null;
            $notificacionCode = $empresaSelect->token_db_maximo.'_'.$user->id;

            DB::connection('clientes')->commit();

            return response()->json([
                'success'=>	true,
                'token_api_portafolio' => $empresaSelect->token_api_portafolio,
                'token_type' => 'Bearer',
                'empresa' => $empresaSelect,
                'token_db_portafolio' => base64_encode($empresaSelect->token_db_portafolio),
                'notificacion_code' => $notificacionCode,
                'fondo_sistema' => $user->fondo_sistema,
                'message'=> 'Usuario logeado con exito!'
            ], 200);
            
        } catch (Exception $e) {
            DB::connection('clientes')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }

        

        //     $empresaSelect = Empresa::where('id', $idEmpresa)->first();
    }

    private function encryptData($data)
    {
        $method = "AES-256-CBC";
        $key = "encryptionKey123";
        $options = 0;
        $iv = '1234567891011121';

        return openssl_encrypt($data, $method, $key, $options, $iv);
    }

    private function decryptData($data)
    {
        $method = "AES-256-CBC";
        $key = "encryptionKey123";
        $options = 0;
        $iv = '1234567891011121';

        return openssl_decrypt($data, $method, $key, $options, $iv);
    }
}
