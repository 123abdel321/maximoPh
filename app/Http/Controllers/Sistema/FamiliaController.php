<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Empresa\UsuarioEmpresa;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Portafolio\Nits;
use App\Models\Sistema\Porteria;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\PorteriaEvento;
use App\Models\Sistema\ArchivosGenerales;

class FamiliaController extends Controller
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
        $usuarioEmpresa = UsuarioEmpresa::where('id_usuario', $request->user()->id)
            ->where('id_empresa', $request->user()->id_empresa)
            ->first();

        $data = [
            'usuario_rol' => $usuarioEmpresa->id_rol
        ];

        return view('pages.administrativo.familia.familia-view', $data);
    }

    public function read (Request $request)
    {
        try {
            $usuarioEmpresa = UsuarioEmpresa::where('id_usuario', $request->user()->id)
                ->where('id_empresa', $request->user()->id_empresa)
                ->first();

            $start = $request->get("start");
            $rowperpage = 24;

            $porteria = Porteria::with('archivos', 'propietario', 'eventos', 'usuario', 'inmueble', 'nit')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                )
                ->whereIn('tipo_porteria', [1,2,3]);

            if (!$request->user()->can('familia terceros')) {
                $porteria->where('id_usuario', $request->user()->id);
            }
            
            if ($request->user()->can('familia terceros') && $request->get("search")) {
                $porteria->where(function ($query) use ($request) {
                    $query->where('nombre', 'like', '%' .$request->get("search"). '%')
                        ->orWhere('placa', 'like', '%' .$request->get("search"). '%')
                        ->orWhere('observacion', 'like', '%' .$request->get("search"). '%')
                        ->orWhere('email', 'like', '%' .$request->get("search"). '%')
                        ->orWhere('telefono', 'like', '%' .$request->get("search"). '%')
                        ->orWhere('documento', 'like', '%' .$request->get("search"). '%');
                });
                    
            } else if ($request->get("search") && $usuarioEmpresa) {
                $porteria->where(function ($query) use ($request, $usuarioEmpresa) {
                    $query->where('id_nit', $usuarioEmpresa->id_nit)
                        ->orWhere('nombre', 'like', '%' .$request->get("search"). '%')
                        ->orWhere('placa', 'like', '%' .$request->get("search"). '%')
                        ->orWhere('observacion', 'like', '%' .$request->get("search"). '%')
                        ->orWhere('email', 'like', '%' .$request->get("search"). '%')
                        ->orWhere('telefono', 'like', '%' .$request->get("search"). '%')
                        ->orWhere('documento', 'like', '%' .$request->get("search"). '%');
                });
            }
            
            if ($request->get("id_nit")) $porteria->where('id_nit', $request->get("id_nit"));
            if ($request->get("tipo") || $request->get("tipo") == '0') $porteria->where('tipo_porteria', $request->get("tipo"));
            if ($request->get("fecha") && !$request->get("search")) {
                $fechaFilter = Carbon::parse($request->get("fecha"));
                $diaFilter = $fechaFilter->dayOfWeek;
                $porteria->where('dias', 'LIKE', '%'.$diaFilter);
            }
            $totalData = $porteria->count();
            $porteria->skip($start)->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'iTotalRecords' => $totalData,
                'iTotalDisplayRecords' => $totalData,
                'data' => $porteria->orderBy('id', 'DESC')->get(),
                'perPage' => $rowperpage,
                'message'=> 'Familia generada con exito!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function find (Request $request)
    {
        try {
            $porteria = Porteria::with('archivos', 'propietario')
                ->where('id', $request->get('id'))
                ->first();

            if (!$porteria->propietario) {
                return response()->json([
                    "success"=>false,
                    'data' => null,
                    "message"=>'El propietario no tiene una Cédula/Nit asociado'
                ], 422);
            }
                
            $nit = Nits::where('email', $porteria->propietario->email)->first();
            $inmuebleNit = InmuebleNit::with('inmueble')->where('id_nit', $nit->id)->first();
            $porteria->nit = $nit;
            $porteria->inmueble_nit = $inmuebleNit;

            return response()->json([
                'success'=>	true,
                'data' => $porteria,
                'message'=> 'Datos porteria cargados con exito!'
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
            'tipo_familia_create' => 'nullable',
            'tipo_vehiculo_familia' => 'nullable',
            'tipo_mascota_familia' => 'nullable',
            'nombre_persona_familia' => 'nullable|min:1|max:200',
            'placa_persona_familia' => 'nullable',
            'observacion_persona_familia' => 'nullable',
            'imagen_familia' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
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

            $file = $request->file('photos');
            $nitData = Nits::find($request->get('id_nit_familia'));

            $usuarioEmpresa = null;
            if ($request->get('id_nit_familia')) {
                $usuarioEmpresa = UsuarioEmpresa::where('id_nit', $request->get('id_nit_familia'))
                    ->where('id_empresa', $request->user()->id_empresa)
                    ->first();
            } else {
                $usuarioEmpresa = UsuarioEmpresa::where('id_usuario', $request->user()->id)
                    ->where('id_empresa', $request->user()->id_empresa)
                    ->first();
            }

            $idInmueble = $request->get('id_inmueble_familia');

            if (!$idInmueble && $usuarioEmpresa) {
                $inmuebleNit = InmuebleNit::where('id_nit', $usuarioEmpresa->id_nit)
                    ->first();
                if ($inmuebleNit) $idInmueble = $inmuebleNit->id_inmueble;
                else {
                    return response()->json([
                        "success"=>false,
                        'data' => [],
                        "message" => 'El usuario no tiene inmuebles al cual se pueda registrar la familia'
                    ], 422);
                }
            }

            if (!$nitData && $usuarioEmpresa) {
                $nitData = Nits::find($usuarioEmpresa->id_nit);
            }

            //ACTUALIZAR
            if ($request->get('id_familia_up')) {
                Porteria::where('id', $request->get('id_familia_up'))
                    ->update([
                        'tipo_porteria' => $request->get('tipo_familia_create'),
                        'tipo_vehiculo' => $request->get('tipo_vehiculo_familia'),
                        'tipo_mascota' => $request->get('tipo_mascota_familia'),
                        'documento' => $request->get('documento_persona_familia'),
                        'nombre' => $request->get('nombre_persona_familia'),
                        'dias' => $this->getDiasString($request),
                        'placa' => $request->get('placa_persona_familia'),
                        // 'hoy' => $request->get('diaPorteria0') ? Carbon::now()->format('Y-m-d') : null,
                        'observacion' => $request->get('observacion_persona_familia'),
                        'telefono' => $request->get('telefono_familia'),
                        'updated_by' => request()->user()->id
                    ]);

                $porteria = Porteria::where('id', $request->get('id_familia_up'))
                    ->first();

                if ($file) {
                    ArchivosGenerales::where('relation_type', 1)
                        ->where('relation_id', $request->get('id_familia_up'))
                        ->delete();
                }
            } else {
                $porteria = Porteria::create([
                    'id_nit' => $nitData ? $nitData->id : null,
                    'id_inmueble' => $idInmueble,
                    'id_usuario' => $usuarioEmpresa ? $usuarioEmpresa->id_usuario : null,
                    'tipo_porteria' => $request->get('tipo_familia_create'),
                    'tipo_vehiculo' => $request->get('tipo_vehiculo_familia'),
                    'tipo_mascota' => $request->get('tipo_mascota_familia'),
                    'nombre' => $request->get('nombre_persona_familia'),
                    'documento' => $request->get('documento_persona_familia'),
                    'dias' => $this->getDiasString($request),
                    'placa' => $request->get('placa_persona_familia'),
                    // 'hoy' => $request->get('diaPorteria0') ? Carbon::now()->format('Y-m-d') : null,
                    'observacion' => $request->get('observacion_persona_familia'),
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);
            }

            if ($request->file('photos')) {

                $archivos = ArchivosGenerales::where('relation_type', 10)
                    ->where('relation_id', $porteria->id)
                    ->get();
    
                if (count($archivos)) {
                    foreach ($archivos as $archivo) {
                        Storage::disk('do_spaces')->delete($archivo->url_archivo);
                        $archivo->delete();
                    }
                }

                foreach ($request->file('photos') as $photos) {
                    $nameFile = 'maximo/empresas/'.request()->user()->id_empresa.'/imagen/porteria/'. $photos->getClientOriginalName();
                    $url = Storage::disk('do_spaces')->putFileAs($nameFile, $photos, $photos->getClientOriginalName(), 'public');

                    $archivo = new ArchivosGenerales([
                        'tipo_archivo' => 'imagen',
                        'url_archivo' => $url,
                        'estado' => 1,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);
        
                    $archivo->relation()->associate($porteria);
                    $porteria->archivos()->save($archivo);
                }
            }

            $porteria->load('archivos', 'propietario');

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $porteria,
                'message'=> 'Datos familia creados con exito!'
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
            'id' => 'required|exists:max.porterias,id',
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

            Porteria::where('id', $request->get('id'))->delete();

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Familia eliminada con exito!'
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
        $inmuebles = Porteria::with('archivos')
            ->select(
                \DB::raw('*'),
                \DB::raw("nombre as text")
            );

        if ($request->get("search")) {
            $inmuebles->where('nombre', 'like', '%' .$request->get("search"). '%')
                ->orWhere('placa', 'like', '%' .$request->get("search"). '%')
                ->orWhere('observacion', 'like', '%' .$request->get("search"). '%');
        }

        return $inmuebles->paginate(40);
    }

    private function getDiasString ($request)
    {
        $dias = "";
        for ($i = 1; $i <= 7; $i++) {

            if ($request->get('diaFamilia'.$i)) {
                if ($dias) {
                    $dias.= ",".$i;
                } else {
                    $dias.=$i;
                }
            }
        }
        return $dias;
    }

    private function usuarioSearch($search)
    {
        $data = [];
        $users = DB::connection('clientes')->table('users')->select('id')
            ->where('firstname', 'LIKE', '%'.$search.'%')
            ->orWhere('lastname', 'LIKE', '%'.$search.'%')
            ->orWhere('email', 'LIKE', '%'.$search.'%')
            ->orWhere('apartamentos', 'LIKE', '%'.$search.'%')
            ->get()->toArray();

        if (count($users)) {
            foreach ($users as $nit) {
                $data[] = $nit->id;
            }
        }

        return $data;        
    }

    private function nitsSearch($search)
    {
        $data = [];
        $nits = DB::connection('sam')->table('nits')->select('id')
            ->where('razon_social', 'LIKE', '%'.$search.'%')
            ->orWhere('numero_documento', 'LIKE', '%'.$search.'%')
            ->orWhere(DB::raw("(CASE
                WHEN razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                WHEN (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, otros_nombres, primer_apellido, segundo_apellido)
                ELSE NULL
            END)"), 'LIKE', '%'.$search.'%')
            ->orWhere('email', 'LIKE', '%'.$search.'%')
            ->orWhere('apartamentos', 'LIKE', '%'.$search.'%')
            ->get()->toArray();

        if (count($nits)) {
            foreach ($nits as $nit) {
                $data[] = $nit->id;
            }
        }

        return $data;        
    }
}