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

class PorteriaController extends Controller
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

        return view('pages.administrativo.porteria.porteria-view', $data);
    }

    public function readPorteria (Request $request)
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

            $porteria = Porteria::orderBy($columnName,$columnSortOrder)
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                );

            $porteriasTotals = $porterias->get();

            $porteriasPaginate = $porterias->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $porteriasTotals->count(),
                'iTotalDisplayRecords' => $porteriasTotals->count(),
                'data' => $porteriasPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Zonas generados con exito!'
            ]);


        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function read (Request $request)
    {
        try {
            $usuarioEmpresa = UsuarioEmpresa::where('id_usuario', $request->user()->id)
                ->where('id_empresa', $request->user()->id_empresa)
                ->first();
            
            $start = $request->get("start");
            $rowperpage = 24;

            $porteriaTotal = Porteria::count();

            $porteria = Porteria::with('archivos', 'propietario')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                );

            if ($usuarioEmpresa->id_rol != 1 && $usuarioEmpresa->id_rol != 2) {
                $porteria->where('id_usuario', $request->user()->id);
            }
            
            if ($usuarioEmpresa->id_rol != 1 && $usuarioEmpresa->id_rol != 2 && $request->get("search")) {
                $nitSearch = $this->nitsSearch($request->get("search"));
                $porteria->where('id_nit', $usuarioEmpresa->id_nit)
                    ->orWhere('nombre', 'like', '%' .$request->get("search"). '%')
                    ->orWhere('placa', 'like', '%' .$request->get("search"). '%')
                    ->orWhere('observacion', 'like', '%' .$request->get("search"). '%')
                    ->when(count($nitSearch), function ($query) use($nitSearch) {
                        $query->orWhereIn('id_nit', $nitSearch);
                    });
            } else if ($request->get("search")){
                $nitSearch = $this->nitsSearch($request->get("search"));
                $porteria->where('nombre', 'like', '%' .$request->get("search"). '%')
                    ->orWhere('placa', 'like', '%' .$request->get("search"). '%')
                    ->orWhere('observacion', 'like', '%' .$request->get("search"). '%')
                    ->when(count($nitSearch), function ($query) use($nitSearch) {
                        $query->orWhereIn('id_nit', $nitSearch);
                    });
            }
            
            if ($request->get("id_nit")) $porteria->where('id_nit', $request->get("id_nit"));
            if ($request->get("tipo") || $request->get("tipo") == '0') $porteria->where('tipo_porteria', $request->get("tipo"));
            if ($request->get("fecha")) {
                $fechaFilter = Carbon::parse($request->get("fecha"));
                $diaFilter = $fechaFilter->dayOfWeek;
                $porteria->where('dias', 'LIKE', '%'.$diaFilter);
            }

            $porteria->skip($start)->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'iTotalRecords' => $porteriaTotal,
                'iTotalDisplayRecords' => $porteriaTotal,
                'data' => $porteria->get(),
                'perPage' => $rowperpage,
                'message'=> 'Porteria generada con exito!'
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
            'tipo_porteria_create' => 'nullable',
            'tipo_vehiculo_porteria' => 'nullable',
            'tipo_mascota_porteria' => 'nullable',
            'nombre_persona_porteria' => 'nullable|min:1|max:200',
            'placa_persona_porteria' => 'nullable',
            'observacion_persona_porteria' => 'nullable',
            'imagen_porteria' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
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

            $file = $request->file('imagen_porteria');

            $usuarioEmpresa = null;
            if ($request->get('id_nit_porteria')) {
                $usuarioEmpresa = UsuarioEmpresa::where('id_nit', $request->get('id_nit_porteria'))
                    ->where('id_empresa', $request->user()->id_empresa)
                    ->first();
            } else {
                $usuarioEmpresa = UsuarioEmpresa::where('id_usuario', $request->user()->id)
                    ->where('id_empresa', $request->user()->id_empresa)
                    ->first();
            }

            //ACTUALIZAR
            if ($request->get('id_porteria_up')) {
                Porteria::where('id', $request->get('id_porteria_up'))
                    ->update([
                        'tipo_porteria' => $request->get('tipo_porteria_create'),
                        'tipo_vehiculo' => $request->get('tipo_vehiculo_porteria'),
                        'tipo_mascota' => $request->get('tipo_mascota_porteria'),
                        'nombre' => $request->get('nombre_persona_porteria'),
                        'dias' => $this->getDiasString($request),
                        'placa' => $request->get('placa_persona_porteria'),
                        'hoy' => $request->get('diaPorteria0') ? Carbon::now()->format('Y-m-d') : null,
                        'observacion' => $request->get('observacion_persona_porteria'),
                        'updated_by' => request()->user()->id
                    ]);

                $porteria = Porteria::where('id', $request->get('id_porteria_up'))
                    ->first();

                if ($file) {
                    ArchivosGenerales::where('relation_type', 1)
                        ->where('relation_id', $request->get('id_porteria_up'))
                        ->delete();
                }
            } else {
                $porteria = Porteria::create([
                    'id_nit' => $usuarioEmpresa->id_nit,
                    'id_usuario' => $usuarioEmpresa->id_usuario,
                    'tipo_porteria' => $request->get('tipo_porteria_create'),
                    'tipo_vehiculo' => $request->get('tipo_vehiculo_porteria'),
                    'tipo_mascota' => $request->get('tipo_mascota_porteria'),
                    'nombre' => $request->get('nombre_persona_porteria'),
                    'dias' => $this->getDiasString($request),
                    'placa' => $request->get('placa_persona_porteria'),
                    'hoy' => $request->get('diaPorteria0') ? Carbon::now()->format('Y-m-d') : null,
                    'observacion' => $request->get('observacion_persona_porteria'),
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);
            }

            if ($file) {
                $nameFile = 'maximo/empresas/'.request()->user()->id_empresa.'/imagen/porteria';
                $url = Storage::disk('do_spaces')->put($nameFile, $file, 'public');

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

            $porteria->load('archivos', 'propietario');

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $porteria,
                'message'=> 'Datos porteria creados con exito!'
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

            $existeNit = false;
            $eventoPorteria = PorteriaEvento::where('id_porteria', $request->get('id'));
            $porteria = Porteria::with('archivos')
                ->where('id', $request->get('id'))
                ->first();

            if (count($porteria->archivos)) {
                $existeNit = Nits::where('logo_nit', $porteria->archivos[0]->url_archivo)
                    ->first();
            }

            if ($eventoPorteria->count() || $existeNit) {
                $porteria->estado = true;
                $porteria->save();

                DB::connection('max')->commit();

                return response()->json([
                    'success'=>	true,
                    'data' => [],
                    'message'=> 'Evento porteria eliminado con exito!'
                ]);
            }

            $archivos = ArchivosGenerales::where('relation_type', 1)
                ->where('relation_id', $request->get('id'))
                ->get();

            if (count($archivos)) {
                foreach ($archivos as $archivo) {
                    Storage::disk('do_spaces')->delete($archivo->url_archivo);
                    $archivo->delete();
                }
            }

            Porteria::where('id', $request->get('id'))->delete();

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Evento porteria eliminado con exito!'
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
            if ($request->get('diaPorteria'.$i)) {
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