<?php

namespace App\Http\Controllers\Empresa;

use DB;
use Config;
use Exception;
use App\Mail\GeneralEmail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Jobs\ProcessProvisionedDatabase;
use Illuminate\Support\Facades\Validator;
use App\Helpers\PortafolioERP\InstaladorEmpresa;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Http\Controllers\Traits\BegReCaptchaValidateTrait AS validateReCaptcha;
//MODELS
use App\Models\User;
use App\Models\Portafolio\Nits;
use App\Models\Empresa\Empresa;
use App\Models\Empresa\EnvioEmail;
use App\Models\Empresa\UsuarioEmpresa;
use App\Models\Empresa\UsuarioPermisos;
use App\Models\Sistema\TerminosCondiciones;
use App\Models\Sistema\TerminosCondicionesUser;

use Spatie\Permission\Models\Permission;

class ApiController extends Controller
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

    public function login(Request $request)
    {
        $credenciales1 = ['email' => $request->email, 'password' => $request->password];
        $credenciales2 = ['username' => $request->email, 'password' => $request->password];

        if (Auth::attempt($credenciales1) || Auth::attempt($credenciales2)) {

            $user = User::where('email', $request->email)->first();

            $token = $user->createToken("api_token")->plainTextToken;
            $user->remember_token = $token;
            $user->save();

            return response()->json([
                'success'=>	true,
                'access_token' => $token,
                'empresa' => '',
                'token_type' => 'Bearer',
                'message'=> 'Usuario logeado con exito!'
            ], 200);
        }

        return response()->json([
    		'success'=>	false,
    		'data' => '',
    		'message'=> 'Credenciales incorrectas.'
    	], 422);
    }

    public function register(Request $request)
    {
        $rules = [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'email' => 'required|string|email|max:255|unique:users',
            'telefono' => 'required|string',
            'documento' => 'required|string',
            'tipo_documento' => 'required|string',
            'password' => 'required|string'
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

            User::create([
                'username' => $request->get('username'),
                'firstname' => $request->get('firstname'),
                'lastname' => $request->get('lastname'),
                'email' => $request->get('email'),
                'telefono' => $request->get('telefono'),
                'password' => $request->get('password')
            ]);

            DB::connection('max')->commit();

            return response()->json([
                "success" => true,
                "data" => [],
                "message" => 'Usuario registrado con exito!'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function createEmpresa(Request $request)
    {
        $rules = [
			'nit' => 'required|max:200',
			'dv' => "nullable",
			'tipo_contribuyente' => 'required|in:1,2',
			'primer_apellido' => 'nullable',
			'segundo_apellido' => 'nullable|string|max:60|',
			'primer_nombre' => 'nullable',
			'otros_nombres' => 'nullable|string|max:60',
			'razon_social' => 'required',
            'username' => 'required|string|max:255|unique:clientes.users',
			'direccion' => 'nullable|min:3|max:100',
			'telefono' => 'nullable|numeric|digits_between:1,30',
            'email' => 'required|string|email|max:255|unique:clientes.users',
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

            $existEmpresa = Empresa::where('nit',$request->get('nit'))->first();

            if ($existEmpresa) {
                if ($existEmpresa->estado == 5) {
                    return response()->json([
                        "success"=>false,
                        "errors"=>["La empresa ".$existEmpresa->nombre." con nit ".$existEmpresa->nit." tiene un proceso de pago pendiente. Por favor intenta más tarde"]
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                } else {
                    return response()->json([
                        "success"=>false,
                        "errors"=>["La empresa ".$existEmpresa->nombre." con nit ".$existEmpresa->nit." ya está registrada."]
                    ], Response::HTTP_UNPROCESSABLE_ENTITY);
                }
            }

            info("Creando empresa: {$request->razon_social} ...");

            $usuarioOwner = User::create([
                'firstname' => $request->razon_social,
                'username' => $request->username,
                'email' => $request->email,
                'telefono' => $request->telefono,
                'password' => $request->nit,
				'address' => $request->direccion,
            ]);

            $empresa = Empresa::create([
				'servidor' => 'max',
				'nombre' => $request->razon_social ?? $request->primer_nombre .' '. $request->primer_apellido,
				'primer_apellido' => $request->primer_apellido,
				'segundo_apellido' => $request->segundo_apellido,
				'primer_nombre' => $request->primer_nombre,
				'otros_nombres' => $request->otros_nombres,
				'tipo_contribuyente' => $request->tipo_contribuyente,
				'razon_social' => $request->razon_social,
				'nit' => $request->nit,
				'dv' => $request->dv,
				'telefono' => $request->telefono,
                'id_usuario_owner' => $usuarioOwner->id,
				'estado' => 0
			]);

            $response = (new InstaladorEmpresa($empresa, $usuarioOwner))->send();

            if ($response['status'] > 299) {
                DB::connection('clientes')->rollback();
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>$response['response']->message
                ], 422);
            }

            $nameDb = $this->generateUniqueNameDb($empresa);

            $empresa->token_db_maximo = 'maximo_'.$nameDb;
            $empresa->token_db_portafolio = 'portafolio_'.$nameDb;
            $empresa->token_api_portafolio = $response['response']->api_key_token;
            $empresa->hash = Hash::make($empresa->id);
			$empresa->save();

            $this->associateUserToCompany($usuarioOwner, $empresa);

            info('Empresa'. $request->razon_social.' creada con exito!');

			ProcessProvisionedDatabase::dispatch($empresa);

            DB::connection('clientes')->commit();

            return response()->json([
                "success" => true,
                'data' => '',
                "message" => 'La instalación se está procesando, verifique en 5 minutos.'
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

    public function validateEmail(Request $request)
    {
        if ($this->hasTooManyLoginAttempts($request)) {
			$this->fireLockoutEvent($request);
            return response()->json([
                'success' => false,
                'message' => 'Numero maximo de intentos permitidos, vuelva a intentarlo en 1 minuto'
            ], 401);
		}

        // $captcha_token = $request->get("g-recaptcha-response");

        // if($captcha_token){
		// 	$captcha_response = $this->validateReCaptcha($captcha_token);
		// 	if ($captcha_response->success == false || $captcha_response->score < 0.5||$captcha_response->action != 'validateEmail') {
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

        $rules = [
            'email' => 'required|email'
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

        if ($validator->fails()){
            $this->incrementLoginAttempts($request);
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        try {

            $usuario = User::where('email', $request->get('email'))
                ->first();

            if (!$usuario) {
                $this->incrementLoginAttempts($request);
                return response()->json([
                    "success"=>false,
                    'data' => '',
                    "message"=>'El email no fue encontrado en nuestra base de datos!'
                ], 422);
            }

            if ($usuario->code_general) {
                $start = new Carbon($usuario->limit_general);
                $end = Carbon::now();
                $diff = (int)$start->diff($end)->format('%H%I%S');
                if ($diff <= 59) {
                    $this->incrementLoginAttempts($request);
                    return response()->json([
                        "success"=>false,
                        'data' => '',
                        "message"=>'El tiempo esperado es menor a 60 segundos!'
                    ], 422);
                }
            }

            $usuario->code_general = $this->generateRandomString(5);
            $limit_general = Carbon::now()->format('Y-m-d H:i:s');
            $limit_general = Carbon::parse($limit_general);
            $limit_general = $limit_general->addHours(1);
            $usuario->limit_general = $limit_general;
            $usuario->save();

            $nombreUsuario = $usuario->firstname;
            $nombreUsuario.= $usuario->lastname ? ' '.$usuario->lastname : '';

            $envioEmail = EnvioEmail::create([
                'id_empresa' => $usuario->id_empresa,
                'id_nit' => $nit->id_nit,
                'email' => $email,
                'contexto' => 'emails.recover',
                'status' => 'en_cola'
            ]);

            $response = Mail::to($usuario->email)
                ->send(new GeneralEmail('MAXIMOPH', 'emails.recover', [
                    'nombre' => $nombreUsuario,
                    'code_general' => $usuario->code_general
                ]));

            $sgMessageId = $response->getSymfonySentMessage()->getMessageId();

            $envioEmail->sg_message_id = $sgMessageId;
            $envioEmail->save();

            return response()->json([
                "success"=>true,
                "data" => '',
                "message" => '',
            ], 200);

        } catch (Exception $e) {
            $this->incrementLoginAttempts($request);
            return response()->json([
                "success"=>false,
                'data' => $e->getLine(),
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function confirmPass(Request $request)
    {
        $rules = [
            'codigo' => 'required',
            'password' => 'required',
            'id_usuario' => 'required|exists:App\Models\User,id',
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

            $user = User::where('id', $request->get('id_usuario'))
                ->where('code_general', $request->get('codigo'))
                ->first();

            if ($user) {
                $user->update([
                    'email_verified_at' => Carbon::now(),
                    'password' => $request->get('password'),
                    'code_general' => ''
                ]);
            }
            
            return response()->json([
                'success'=>	true,
                'data' => '',
                'message'=> ''
            ], 200);
            
        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function validateCode(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'code_general' => 'required'
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

            $usuario = User::where('email', $request->get('email'))
                ->first();

            if (!$usuario) {
                return response()->json([
                    "success"=>false,
                    'data' => '',
                    "message"=>'El email no fue encontrado en nuestra base de datos!'
                ], 422);
            }

            if (!$usuario->code_general) {
                return response()->json([
                    "success"=>false,
                    'data' => '',
                    "message"=>'El usuario no tiene codigo asignado, volver a enviar el email!'
                ], 422);
            }

            $start = new Carbon($usuario->limit_general);
            $end = Carbon::now();

            if ($start->gte($end) && $usuario->code_general == $request->get('code_general')) {
                return response()->json([
                    "success"=>true,
                    'data' => [],
                ], 200);
            }

            return response()->json([
                "success"=>false,
                'data' => '',
                "message"=>'Error al validar código de seguridad!'
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => $e->getLine(),
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function changePassword(Request $request)
    {
        $rules = [
            'email' => 'required|email',
            'new_password' => 'required'
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

            $usuario = User::where('email', $request->get('email'))
                ->first();

            if (!$usuario) {
                return response()->json([
                    "success"=>false,
                    'data' => '',
                    "message"=>'El email no fue encontrado en nuestra base de datos!'
                ], 422);
            }

            if (!$usuario->code_general) {
                return response()->json([
                    "success"=>false,
                    'data' => '',
                    "message"=>'El usuario no tiene codigo asignado, volver a enviar el email!'
                ], 422);
            }

            $start = new Carbon($usuario->limit_general);
            $end = Carbon::now();
            
            if ($start->gte($end)) {
                $usuario->update([
                    'password' => $request->get('new_password')
                ]);

                return response()->json([
                    "success" => 200,
                    'data' => '',
                    "message" => 'Contraseña actualizada con exito'
                ], 200);
            }

            return response()->json([
                "success"=>false,
                'data' => '',
                "message"=>'Error el código de seguridad ah caducado!'
            ], 422);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => $e->getLine(),
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function terminosCondiciones(Request $request)
    {
        try {
            DB::connection('max')->beginTransaction();

            $terminosCondiciones = TerminosCondiciones::orderBy('id', 'DESC')->first();
            
            TerminosCondicionesUser::create([
                'user_id' => request()->user()->id,
                'terminos_condiciones_id' => $terminosCondiciones->id
            ]);

            DB::connection('max')->commit();

            return response()->json([
                "success" => 200,
                'data' => '',
                "message" => 'Terminos y condiciones aceptados con exito'
            ], 200);
        } catch (Exception $e) {
            Log::error('Fallo actualizando terminos y condiciones');
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    private function generateUniqueNameDb($empresa)
	{
		$razonSocial = str_replace(" ", "_", strtolower($empresa->razon_social));
		return $razonSocial.'_'.$empresa->nit;
	}

    private function associateUserToCompany($user, $empresa)
	{
        User::where('id', $user->id)->update([
            'has_empresa' => $empresa->token_db_maximo,
        ]);

		$usuarioEmpresa = UsuarioEmpresa::where('id_usuario', $user->id)
			->where('id_empresa', $empresa->id)
			->first();

		if(!$usuarioEmpresa){
			UsuarioEmpresa::create([
				'id_usuario' => $user->id,
				'id_empresa' => $empresa->id,
				'id_rol' => 2, // default: 2
				'estado' => 1, // default: 1 activo
			]);
		}
		return;
	}

    public function getUsuario (Request $request)
    {
        $usuario = User::where('id', $request->get('id'))->first();

        return response()->json([
            "success"=>true,
            "data"=>$usuario
        ], 200);
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
