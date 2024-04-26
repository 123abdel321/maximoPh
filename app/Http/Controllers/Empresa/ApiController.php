<?php

namespace App\Http\Controllers\Empresa;

use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Jobs\ProcessProvisionedDatabase;
use Illuminate\Support\Facades\Validator;
use App\Helpers\PortafolioERP\InstaladorEmpresa;
//MODELS
use App\Models\User;
use App\Models\Empresa\Empresa;
use App\Models\Empresa\UsuarioEmpresa;

class ApiController extends Controller
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

            info('Creando empresa: '. $request->razon_social. '...');

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
            $empresa->token_api_portafolio = '';
            // $empresa->token_api_portafolio = $response['response']->api_key_token;
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
}
