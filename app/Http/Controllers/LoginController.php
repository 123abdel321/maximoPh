<?php

namespace App\Http\Controllers;

use DB;
use Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Controllers\Traits\BegReCaptchaValidateTrait AS validateReCaptcha;
//MODELS
use App\Models\User;
use App\Models\Empresa\Empresa;
use App\Models\Portafolio\Nits;
use App\Models\Empresa\Visitantes;
use App\Models\Portafolio\UserERP;
use App\Models\Portafolio\EmpresaERP;
use App\Models\Portafolio\UsuarioEmpresaERP;
use App\Models\Empresa\RolesGenerales;
use App\Models\Empresa\UsuarioEmpresa;
use App\Models\Empresa\UsuarioPermisos;
use Spatie\Permission\Models\Permission;

class LoginController extends Controller
{
    use validateReCaptcha;
    use AuthenticatesUsers;
    protected $maxAttempts = 3; // Amount of bad attempts user can make
	protected $decayMinutes = 1; // Time for which user is going to be blocked in seconds

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

    public function welcome(Request $request)
    {
        $rules = [
            'code' => 'required',
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
            $codigo = $request->get('code');
            $codigo = base64_decode($codigo);
            $id = explode('$', $codigo)[0];
            $code_general = explode('$', $codigo)[1];

            $usuario = User::where('id', $id)
                ->where('code_general', $code_general)
                ->first();

            if (!$usuario) abort(404);

            $data = [
                'id_usuario' => $id,
                'code_general' => $code_general
            ];

            return view('auth.welcome', $data);

        } catch (Exception $e) {

            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }

        return view('auth.login');
    }

    public function validateSession (Request $request)
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        // if ($this->hasTooManyLoginAttempts($request)) {
		// 	$this->fireLockoutEvent($request);
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Numero maximo de intentos permitidos, vuelva a intentarlo en 1 minuto'
        //     ], 401);
		// }

        // $captcha_token = $request->get("g-recaptcha-response");
        $credenciales1 = ['email' => $request->email, 'password' => $request->password];
        $credenciales2 = ['username' => $request->email, 'password' => $request->password];

        // if($captcha_token){
		// 	$captcha_response = $this->validateReCaptcha($captcha_token);
		// 	if ($captcha_response->success == false || $captcha_response->score < 0.5||$captcha_response->action != 'login') {
		// 		return response()->json([
		// 			'success' => false,
		// 			'message' => 'Falló la validación de reCAPTCHA'
		// 		], 401);
		// 	}
		// }else{
		// 	return response()->json([
        //         'success' => false,
		// 		'message' => 'Falló la validación de reCAPTCHA'
		// 	], 401);
		// }

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $browser        = "Desconocido";
        $browser_array  = array(
            '/msie/i'       =>  'Internet Explorer',
            '/firefox/i'    =>  'Firefox',
            '/safari/i'     =>  'Safari',
            '/chrome/i'     =>  'Google Chrome',
            '/edge/i'       =>  'Edge',
            '/opera/i'      =>  'Opera',
            '/netscape/i'   =>  'Netscape',
            '/maxthon/i'    =>  'Maxthon',
            '/konqueror/i'  =>  'Konqueror',
            '/mobile/i'     =>  'Handheld Browser'
        );
        foreach ( $browser_array as $regex => $value ) {
            if ( preg_match( $regex, $user_agent ) ) {
                $browser = $value;
            }
        }

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $os_platform =   "Desconocido";
        $os_array =   array(
            '/windows nt 10/i'      =>  'Windows 10',
            '/windows nt 6.3/i'     =>  'Windows 8.1',
            '/windows nt 6.2/i'     =>  'Windows 8',
            '/windows nt 6.1/i'     =>  'Windows 7',
            '/windows nt 6.0/i'     =>  'Windows Vista',
            '/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
            '/windows nt 5.1/i'     =>  'Windows XP',
            '/windows xp/i'         =>  'Windows XP',
            '/windows nt 5.0/i'     =>  'Windows 2000',
            '/windows me/i'         =>  'Windows ME',
            '/win98/i'              =>  'Windows 98',
            '/win95/i'              =>  'Windows 95',
            '/win16/i'              =>  'Windows 3.11',
            '/macintosh|mac os x/i' =>  'Mac OS X',
            '/mac_powerpc/i'        =>  'Mac OS 9',
            '/linux/i'              =>  'Linux',
            '/ubuntu/i'             =>  'Ubuntu',
            '/iphone/i'             =>  'iPhone',
            '/ipod/i'               =>  'iPod',
            '/ipad/i'               =>  'iPad',
            '/android/i'            =>  'Android',
            '/blackberry/i'         =>  'BlackBerry',
            '/webos/i'              =>  'Mobile'
        );
        foreach ( $os_array as $regex => $value ) {
            if ( preg_match($regex, $user_agent ) ) {
                $os_platform = $value;
            }
        }

        $responseGeo = null;
        if ($request->ip) {
            $geo = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->get('ipinfo.io/'.$request->ip.'?token=ba8524c502fa55');
            $responseGeo = (object) $geo->json();
        }

        if (Auth::attempt($credenciales1) || Auth::attempt($credenciales2)) {
            $request->session()->regenerate();
            $user =  User::find(Auth::user()->id);
            $data = null;
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
            $notificacionCodeGeneral = $empresaSelect->token_db_maximo;
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

            if ($responseGeo) {
                $data = [
                    'id_usuario' => $request->user() ? $request->user()->id : null,
                    'ip' => $request->ip,
                    'device' => $os_platform,
                    'browser' => $browser,
                    'loc' => property_exists($responseGeo, 'loc') ? $responseGeo->loc : null,
                    'city' => property_exists($responseGeo, 'city') ? $responseGeo->city : null,
                    'region' => property_exists($responseGeo, 'region') ? $responseGeo->region : null,
                    'country' => property_exists($responseGeo, 'country') ? $responseGeo->country : null,
                    'hostname' => property_exists($responseGeo, 'hostname') ? $responseGeo->hostname : null,
                    'org' => property_exists($responseGeo, 'org') ? $responseGeo->org : null,
                    'timezone' => property_exists($responseGeo, 'timezone') ? $responseGeo->timezone : null,
                ];
            }
        
            // $visitante = Visitantes::create($data);
            // info('Usuario: ', $data);

            return response()->json([
                'success'=>	true,
                'access_token' => $plainTextToken,
                'token_api_portafolio' => $empresaSelect->token_api_portafolio,
                'token_type' => 'Bearer',
                'empresa' => $empresaSelect,
                'token_db_portafolio' => base64_encode($empresaSelect->token_db_portafolio),
                'notificacion_code' => $notificacionCode,
                "notificacion_code_general"=>$notificacionCodeGeneral,
                'fondo_sistema' => $user->fondo_sistema,
                'message'=> 'Usuario logeado con exito!'
            ], 200);
        }

        $this->incrementLoginAttempts($request);

        if ($responseGeo) {
            $data = [
                'id_usuario' => $request->user() ? $request->user()->id : null,
                'ip' => $request->ip,
                'device' => $os_platform,
                'browser' => $browser,
                'loc' => property_exists($responseGeo, 'loc') ? $responseGeo->loc : null,
                'city' => property_exists($responseGeo, 'city') ? $responseGeo->city : null,
                'region' => property_exists($responseGeo, 'region') ? $responseGeo->region : null,
                'country' => property_exists($responseGeo, 'country') ? $responseGeo->country : null,
                'hostname' => property_exists($responseGeo, 'hostname') ? $responseGeo->hostname : null,
                'org' => property_exists($responseGeo, 'org') ? $responseGeo->org : null,
                'timezone' => "email: ".$request->email." - pass: ".$request->password,
            ];
        }
    
        // $visitante = Visitantes::create($data);
        // Log::error('Fallido login', $data);

        return response()->json([
    		'success'=>	false,
    		'data' => '',
    		'message'=> 'Las credenciales proporcionadas no coinciden con nuestros registros.'
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
            $user->id_empresa = $request->get('id_empresa');
            $user->has_empresa = $empresaSelect->token_db_maximo;
            $user->save();

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
    }

    public function loginPortafolioERP (Request $request)
    {
        try {
            $userMaximo =  User::find($request->user()['id']);
            $empresaMaximo = Empresa::find($request->user()['id_empresa']);
            $userPortafolio =  UserERP::where('email', $userMaximo->email)->first();

            copyDBConnection('cliporta', 'cliporta');
            setDBInConnection('cliporta', env("CLIPORTA_DB_DATABASE", "adminclientes_test"));

            if ($userMaximo && $userPortafolio) {
                if (($userMaximo->rol_maximo == 1 && $userPortafolio->rol_portafolio != 1) || ($userMaximo->rol_maximo != 1 && $userPortafolio->rol_portafolio == 1)) {
                    logger()->critical('Error de rol Dios, el usuario: '. $userMaximo->email.' ('.$userMaximo->id.') no coincide en los servidores');
                    return response()->json([
                        "success"=>false,
                        "message"=>'Error al iniciar sesion en portafolio ERP'
                    ], 422);
                }
                $empresaERP = EmpresaERP::where('token_db', $empresaMaximo->token_db_portafolio)->first();
                $userEmpresaERP = UsuarioEmpresaERP::where('id_usuario', $userPortafolio->id)
                    ->where('id_empresa', $empresaERP->id)
                    ->first();

                if ($userMaximo->rol_maximo == 1 && $userPortafolio->rol_portafolio == 1 && !$userEmpresaERP) {
                    logger()->notice('El usuario: '. $userMaximo->email.' ('.$userMaximo->id.') Se agrego permiso de Dios. '.'En la empresa: '.$empresaERP->razon_social);
                    $userEmpresaERP = UsuarioEmpresaERP::create([
                        'id_empresa' => $empresaERP->id,
                        'id_usuario' => $userPortafolio->id,
                        'id_rol' => 1,
                        'estado' => 1,
                    ]);
                } else if ($userMaximo->rol_maximo != 1 && !$userEmpresaERP) {
                    logger()->notice('El usuario: '. $userMaximo->email.' ('.$userMaximo->id.') Se agrego permiso de Administrador. '.'En la empresa: '.$empresaERP->razon_social);
                    $userEmpresaERP = UsuarioEmpresaERP::create([
                        'id_empresa' => $empresaERP->id,
                        'id_usuario' => $userPortafolio->id,
                        'id_rol' => 2,
                        'estado' => 1,
                    ]);

                }

                $userPortafolio->id_empresa = $empresaERP->id;
                $userPortafolio->has_empresa = $empresaERP->token_db;
                $userPortafolio->about = $this->generateRandomString(10);
                $userPortafolio->save();
            }

            return response()->json([
                "success"=>false,
                'data' => 'login-direct?email='.$userPortafolio->email.'&code_login='.base64_encode($userPortafolio->about),
                "message"=>'Url generada con exito'
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

    private function generateRandomString($length = 8) {
        $characters = '0123456789abcdefghijklmnopqrs092u3tuvwxyzaskdhfhf9882323ABCDEFGHIJKLMNksadf9044OPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function getOS() {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$os_platform =   "Desconocido";
		$os_array =   array(
			'/windows nt 10/i'      =>  'Windows 10',
			'/windows nt 6.3/i'     =>  'Windows 8.1',
			'/windows nt 6.2/i'     =>  'Windows 8',
			'/windows nt 6.1/i'     =>  'Windows 7',
			'/windows nt 6.0/i'     =>  'Windows Vista',
			'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
			'/windows nt 5.1/i'     =>  'Windows XP',
			'/windows xp/i'         =>  'Windows XP',
			'/windows nt 5.0/i'     =>  'Windows 2000',
			'/windows me/i'         =>  'Windows ME',
			'/win98/i'              =>  'Windows 98',
			'/win95/i'              =>  'Windows 95',
			'/win16/i'              =>  'Windows 3.11',
			'/macintosh|mac os x/i' =>  'Mac OS X',
			'/mac_powerpc/i'        =>  'Mac OS 9',
			'/linux/i'              =>  'Linux',
			'/ubuntu/i'             =>  'Ubuntu',
			'/iphone/i'             =>  'iPhone',
			'/ipod/i'               =>  'iPod',
			'/ipad/i'               =>  'iPad',
			'/android/i'            =>  'Android',
			'/blackberry/i'         =>  'BlackBerry',
			'/webos/i'              =>  'Mobile'
		);
		foreach ( $os_array as $regex => $value ) {
			if ( preg_match($regex, $user_agent ) ) {
				$os_platform = $value;
			}
		}
		return $os_platform;
	}


	public function getBrowser() {
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		$browser        = "Desconocido";
		$browser_array  = array(
			'/msie/i'       =>  'Internet Explorer',
			'/firefox/i'    =>  'Firefox',
			'/safari/i'     =>  'Safari',
			'/chrome/i'     =>  'Google Chrome',
			'/edge/i'       =>  'Edge',
			'/opera/i'      =>  'Opera',
			'/netscape/i'   =>  'Netscape',
			'/maxthon/i'    =>  'Maxthon',
			'/konqueror/i'  =>  'Konqueror',
			'/mobile/i'     =>  'Handheld Browser'
		);
		foreach ( $browser_array as $regex => $value ) {
			if ( preg_match( $regex, $user_agent ) ) {
				$browser = $value;
			}
		}
		return $browser;
	}
}
