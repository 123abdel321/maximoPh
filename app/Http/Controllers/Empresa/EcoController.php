<?php

namespace App\Http\Controllers\Empresa;

use DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//JOBS
use App\Jobs\ProcessEnvioGeneralWhatsapp;
//HELPERS
use App\Helpers\Eco\RegisterEco;
//MODELS
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Entorno;

use App\Models\Empresa\UsuarioEmpresa;

class EcoController extends Controller
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
        $ecoToken = Entorno::where('nombre', 'eco_login')->first();
        $ecoToken = $ecoToken->valor ?? null;

        $data = [
            'tokenEco' => $ecoToken
        ];
        
        return view('pages.administrativo.notificaciones.notificaciones-view', $data);
    }

    public function register(Request $request)
    {

        try {
            $empresa = Empresa::where('token_db_maximo', $request->user()['has_empresa'])->first();

            if (!$empresa) {
                return response()->json([
                    "success" => false,
                    "message" => "Empresa no encontrada para el usuario autenticado."
                ], 404);
            }

            $securePassword = Str::random(16);

            $data = [
                "name" => $empresa->nombre,
                "email" => $empresa->correo,
                "password" => $securePassword,
                "password_confirmation" => $securePassword
            ];

            $register = (new RegisterEco($data))->send();

            if ($register->status == 200) {

                $token = "{$register->response->token_type} {$register->response->access_token}";
                Entorno::updateOrCreate(
                    [ 'nombre' => 'eco_login' ],
                    [ 'valor' =>  $token ]
                );

                return response()->json([
                    "success" => true,
                    'data' => $register->response,
                    'token' => $token,
                    "message" => "Registro en servicio Eco exitoso."
                ], 200);

            } elseif ($register->status == 422) {
                $externalErrors = $register->response->errors ?? [];

                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => $externalErrors,
                ], 422);

            } else {
                $errorMessage = $register->response->message ?? 'Error desconocido en el servicio Eco.';
                
                return response()->json([
                    "success" => false,
                    'data' => [],
                    "message" => $errorMessage
                ], $register->status);
            }

        } catch (Exception $e) {
            return response()->json([
                "success"=> false,
                'data' => [],
                "message"=> $e->getMessage()
            ], 422);
        } 
    }

    public function sendWhatsapp(Request $request)
    {
        try {

            $rules = [
                'id_nit' => 'nullable|numeric',
                'id_zona' => 'nullable|numeric',
                'mensaje' => 'required|string|max:1000',
                'tipo_envio' => 'required|in:con_archivo,sin_archivo',
                'numeros' => 'nullable|string',
                'archivos' => 'nullable|array'
            ];

            $validator = Validator::make($request->all(), $rules, $this->messages);

            if ($validator->fails()){
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>$validator->errors()
                ], 422);
            }

            $ecoToken = Entorno::where('nombre', 'eco_login')->first();

            if (!$ecoToken || !$ecoToken->valor) {
                return response()->json([
                    "success"=>false,
                    "message"=>'No se encuentra configurado el token de notificaciones'
                ], 422);
            }

            // validar archivo si el tipo lo requiere
            if ($request->tipo_envio === 'con_archivo' && !$request->has('archivos')) {
                return response()->json([
                    "success"=>false,
                    "message"=>'Debe adjuntar un archivo para este tipo de envío'
                ], 422);
            }

            $id_usuario = $request->user()->id;
            $id_empresa = request()->user()->id_empresa;
            
            ProcessEnvioGeneralWhatsapp::dispatch(
                $request->all(),
                $id_empresa,
                $id_usuario,
                $request->get('archivos')
            );
            
            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Whatsapps enviados con exito!'
            ]);

            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>'No se encuentra configuradas las notificaciones'
            ], 422);
            
        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                "message"=>$e->getMessage()
            ], 500);
        }
    }


}