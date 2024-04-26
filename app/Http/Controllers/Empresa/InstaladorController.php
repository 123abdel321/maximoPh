<?php

namespace App\Http\Controllers\Empresa;

use DB;
use Exception;
use Smalot\PdfParser\Parser;
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

class InstaladorController extends Controller
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
        return view('pages.administrativo.instalador.instalador-view');
    }

    public function generate (Request $request)
    {
        try {
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

            $empresas = Empresa::orderBy($columnName,$columnSortOrder)
                ->with('usuario');

            $empresasTotals = $empresas->get();

            $empresasPaginate = $empresas->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $empresasTotals->count(),
                'iTotalDisplayRecords' => $empresasTotals->count(),
                'data' => $empresasPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Empresas generadas con exito!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function rut (Request $request)
    {
        $rules = [
            'file_rut_empresa' => 'required|mimes:pdf|max:1024'
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

            $parser = new Parser;
            $pdf = $parser->parseFile($request->file('file_rut_empresa'));
            $pages = $pdf->getPages();

            $data = [
                'dv' => null,
                'nit' => null,
                'email' => null,
                'telefono' => null,
                'direccion' => null,
                'razon_social' => null,
                'nombre_completo' => null,
            ];

            foreach ($pages as $page) {
                $text = nl2br($page->getText());
                $text = str_replace(["\n","\t"], " ", $text);
                $dataPage = explode('<br />', $text);
                $nitCompleto = $this->getNitCompleto($dataPage);
                $data['dv'] = substr($nitCompleto, -1);
                $data['nit'] = substr($nitCompleto, 0, -1);
                $data['email'] = $this->getEmail($dataPage);
                $data['telefono'] = $this->getTelefono($dataPage);
                $data['direccion'] = $this->getDireccion($dataPage);
                $data['razon_social'] = $this->getRazonSocial($dataPage);
                $data['nombre_completo'] = $this->getNombreCompleto($dataPage);
            }

            return response()->json([
                "success" => true,
                "data" => $data,
            ]);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function instalacionEmpresa (Request $request)
    {
        $rules = [
            'imagen_empresa_nueva' => 'nullable|max:1024',
            'razon_social_empresa_nueva' => 'required',
            'nombre_completo_empresa_nueva' => 'required',
            'nit_empresa_nueva' => 'required',
            'email_empresa_nueva' => 'required',
            'numero_unidades' => 'required',
            'valor_unidades' => 'required',
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
            DB::connection('max')->beginTransaction();

            $existEmpresa = Empresa::where('nit',$request->get('nit_empresa_nueva'))->first();

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

            info('Creando empresa: '. $request->razon_social_empresa_nueva. '...');
            $usuarioOwner = User::create([
                'firstname' => $request->nombre_completo_empresa_nueva,
                'username' => $request->email_empresa_nueva,
                'email' => $request->email_empresa_nueva,
                'password' => $request->nit_empresa_nueva,
				'address' => $request->direccion_empresa_nueva,
            ]);

            $empresa = Empresa::create([
				'servidor' => 'max',
				'nombre' => $request->razon_social_empresa_nueva,
				'tipo_contribuyente' => 1,
				'razon_social' => $request->razon_social_empresa_nueva,
				'nit' => $request->nit_empresa_nueva,
				'dv' => '',
				'telefono' => $request->telefono_empresa_nueva,
                'id_usuario_owner' => $usuarioOwner->id,
                'valor_suscripcion_mensual' => intval(str_replace("", ",", $request->numero_unidades)) * intval(str_replace("", ",", $request->valor_unidades)),
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
            $empresa->estado = 1;
            $empresa->hash = Hash::make($empresa->id);
			$empresa->save();

            $this->associateUserToCompany($usuarioOwner, $empresa);

            $estado = ProcessProvisionedDatabase::dispatch($empresa);
            info('Empresa'. $request->razon_social.' creada con exito!');

            DB::connection('clientes')->commit();
            DB::connection('max')->commit();

            return response()->json([
                "success" => true,
                'data' => '',
                "message" => 'La instalación se está procesando, verifique en 1 minuto.'
            ], 200);
            
        } catch (Exception $e) {
            DB::connection('clientes')->rollback();
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    private function getNitCompleto($dataPage)
    {
        if (array_key_exists(85, $dataPage)) {
            return str_replace(" ","",$dataPage[85]);
        }
        if (array_key_exists(86, $dataPage)) {
            return str_replace(" ","",$dataPage[86]);
        }
        return null;
    }

    private function getDireccion($dataPage)
    {
        if (array_key_exists(94, $dataPage)) {
            return str_replace("  ","",substr($dataPage[94], 1));
        }
        return null;
    }

    private function getEmail($dataPage)
    {
        if (array_key_exists(95, $dataPage)) {
            return str_replace(" ","",$dataPage[95]);
        }
        return null;
    }

    private function getTelefono($dataPage)
    {
        if (array_key_exists(96, $dataPage)) {
            return str_replace(" ","",$dataPage[96]);
        }
        return null;
    }

    private function getRazonSocial($dataPage)
    {
        if (array_key_exists(92, $dataPage)) {
            return str_replace("  ","",substr($dataPage[92], 1));
        }
        return null;
    }

    private function getNombreCompleto($dataPage)
    {
        if (array_key_exists(90, $dataPage)) {
            return str_replace("  ","",substr($dataPage[90], 1));
        }
        return null;
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

}