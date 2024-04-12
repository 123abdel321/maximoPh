<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Porteria;
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

    public function index ()
    {
        return view('pages.administrativo.porteria.porteria-view');
    }

    public function read (Request $request)
    {
        try {
            $start = $request->get("start");
            $rowperpage = 24;

            $porteriaTotal = Porteria::count();

            $porteria = Porteria::with('archivos');

            if ($request->get("search")) {
                $porteria->where('nombre', 'like', '%' .$request->get("search"). '%')
                    ->orWhere('placa', 'like', '%' .$request->get("search"). '%')
                    ->orWhere('observacion', 'like', '%' .$request->get("search"). '%');
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
            $nombrePorteria = $request->get('nombre_persona_porteria');

            if (!$nombrePorteria) {
                $nombrePorteria = request()->user()->firstname.' '.request()->user()->lastname;
            }
            //ACTUALIZAR 
            if ($request->get('id_porteria_up')) {
                Porteria::where('id', $request->get('id_porteria_up'))
                    ->update([
                        'tipo_porteria' => $request->get('tipo_porteria_create'),
                        'tipo_vehiculo' => $request->get('tipo_vehiculo_porteria'),
                        'tipo_mascota' => $request->get('tipo_mascota_porteria'),
                        'nombre' => $nombrePorteria,
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
                    'id_usuario' => request()->user()->id,
                    'tipo_porteria' => $request->get('tipo_porteria_create'),
                    'tipo_vehiculo' => $request->get('tipo_vehiculo_porteria'),
                    'tipo_mascota' => $request->get('tipo_mascota_porteria'),
                    'nombre' => $nombrePorteria,
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

            $porteria->load('archivos');

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

}