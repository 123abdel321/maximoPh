<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Helpers\NotificacionGeneral;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Porteria;
use App\Models\Sistema\PorteriaEvento;
use App\Models\Sistema\ArchivosGenerales;

class PorteriaEventoController extends Controller
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

    public function read (Request $request)
    {
        try {
            
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length") > 0 ? $request->get("length") : 20;

            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');

            $columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc
            
            $porteriaEvento = PorteriaEvento::with('archivos', 'inmueble.zona', 'persona')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                )
                ->whereDate('created_at', Carbon::now()->format('Y-m-d'));

            if ($request->get("id_inmueble")) $porteriaEvento->where('id_inmueble', $request->get("id_inmueble"));
            if ($request->get("tipo") || $request->get("tipo") == '0') $porteriaEvento->where('tipo', $request->get("tipo"));
            if ($request->get("fecha")) {
                $fechaFilter = Carbon::parse($request->get("fecha"))->format('Y-m-d');
                $porteriaEvento->where('fecha_ingreso', 'LIKE', '%'.$fechaFilter.'%')
                    ->orWhere('fecha_salida', 'LIKE', '%'.$fechaFilter.'%')
                    ->orWhere('created_at', 'LIKE', '%'.$fechaFilter.'%');
            }
            if ($request->get("search")) {
                $porteriaEvento->where('observacion', 'like', '%' .$request->get("search"). '%')
                    ->orWhereHas('persona', function ($query) use ($request) {
                        $query->where('nombre', 'like', '%' .$request->get("search"). '%')
                            ->orWhere('placa', 'like', '%' .$request->get("search"). '%')
                            ->orWhere('observacion', 'like', '%' .$request->get("search"). '%');
                    })
                    ->orWhereHas('inmueble', function ($query) use ($request) {
                        $query->where('nombre', 'like', '%' .$request->get("search"). '%')
                            ->orWhere('observacion', 'like', '%' .$request->get("search"). '%');
                    });
            }
            
            $porteriaEventoTotals = $porteriaEvento->get();
            
            $porteriaEventoPaginate = $porteriaEvento->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $porteriaEventoTotals->count(),
                'iTotalDisplayRecords' => $porteriaEventoTotals->count(),
                'data' => $porteriaEventoPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Eventos de portaria generados con exito!'
            ]);


        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function create(Request $request)
    {
        $rules = [
            'inmueble_porteria_evento' => 'nullable|exists:max.inmuebles,id',
            'persona_porteria_evento' => 'nullable|exists:max.porterias,id',
            'fecha_ingreso_porteria_evento' => 'nullable',
            'fecha_salida_porteria_evento' => 'nullable',
            'observacion_porteria_evento' => 'nullable|min:1|max:200'
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

            $file = $request->file('imagen_evento');

            $evento = PorteriaEvento::Create([
                'tipo' => $request->get('tipo_evento'),
                'id_inmueble' => $request->get('inmueble_porteria_evento'),
                'id_porteria' => $request->get('persona_porteria_evento'),
                'fecha_ingreso' => $request->get('fecha_ingreso_porteria_evento'),
                'fecha_salida' => $request->get('fecha_salida_porteria_evento'),
                'observacion' => $request->get('observacion_porteria_evento'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            if ($request->get('persona_porteria_evento')) {
                $porteria = Porteria::find($request->get('persona_porteria_evento'));
                if ($porteria->tipo == 5 || $porteria->tipo == 6) {
                    $porteria->estado = false;
                }
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
    
                $archivo->relation()->associate($evento);
                $evento->archivos()->save($archivo);
            }

            $evento->load('archivos');

            $notificacion =(new NotificacionGeneral(
                null,
                null,
                $evento
            ));

            $itemPorteria = null;
            $dataMensaje = 'Se ha grabado evento en ';
            $itemPorteria = Porteria::find($request->get('persona_porteria_evento'));
            $dataMensaje.= $itemPorteria->nombre ? $itemPorteria->nombre : $itemPorteria->placa;

            $id_notificacion = $notificacion->crear((object)[
                'id_usuario' => $itemPorteria->id_usuario,
                'mensaje' => $dataMensaje,
                'function' => 'cerrarNotificacion',
                'data' => $evento->id,
                'estado' => 0,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ], true);
            
            $notificacion->notificar(
                'porteria-mensaje-'.$request->user()['has_empresa'].'_'.$itemPorteria->id_usuario,
                ['data' => [$dataMensaje], 'id_notificacion' => $id_notificacion]
            );

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $evento,
                'message'=> 'Evento creado con exito!'
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

    public function update(Request $request)
    {
        $rules = [
            'id' => 'required|exists:max.porteria_eventos,id',
            'observacion' => 'min:1|max:200'
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

            $eventoPorteria = PorteriaEvento::where('id', $request->get('id'))
                ->first();

            $eventoPorteria->observacion = $request->get('observacion');
            $eventoPorteria->updated_by = request()->user()->id;

            if ($request->get('fecha_ingreso')) {
                $eventoPorteria->fecha_ingreso = $request->get('fecha_ingreso');
            }

            if ($request->get('fecha_salida')) {
                $eventoPorteria->fecha_salida = $request->get('fecha_salida');
            }

            $eventoPorteria->save();

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $eventoPorteria,
                'message'=> 'Evento porteria actualizado con exito!'
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

    public function find (Request $request)
    {
        try {
            $eventoPorteria = PorteriaEvento::with('archivos', 'inmueble.zona', 'persona.archivos')
                ->where('id', $request->get('id'))
                ->first();

            return response()->json([
                'success'=>	true,
                'data' => $eventoPorteria,
                'message'=> 'Datos evento de porteria cargados con exito!'
            ]);
        } catch (Exception $e) {
            
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }
}