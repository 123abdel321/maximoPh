<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Events\PrivateMessageEvent;
use App\Http\Controllers\Controller;
use App\Helpers\NotificacionGeneral;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Chat;
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Message;
use App\Models\Sistema\ChatUser;
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
            
            $porteriaEvento = PorteriaEvento::with('archivos', 'inmueble.zona', 'persona.archivos')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                );

            if ($request->get("id_inmueble")) $porteriaEvento->where('id_inmueble', $request->get("id_inmueble"));
            if ($request->get("tipo") || $request->get("tipo") == '0') $porteriaEvento->where('tipo', $request->get("tipo"));
            if ($request->get("fecha_desde")) {
                $fechaFilter = Carbon::parse($request->get("fecha_desde"))->format('Y-m-d');
                $porteriaEvento->where('created_at', '>=', $fechaFilter);
            }
            if ($request->get("fecha_hasta")) {
                $fechaFilter = Carbon::parse($request->get("fecha_hasta"))->format('Y-m-d');
                $porteriaEvento->where(function ($query) use ($fechaFilter) {
                    $query->where('created_at', '<=', $fechaFilter)
                          ->orWhereNull('fecha_salida');
                });
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

            $itemPorteria = Porteria::find($request->get('id_porteria_evento'));

            $evento = PorteriaEvento::Create([
                'id_porteria' => $itemPorteria->id,
                'tipo' => $itemPorteria->tipo_porteria,
                'fecha_ingreso' => $request->get('fecha_ingreso_porteria_evento'),
                'fecha_salida' => $request->get('fecha_salida_porteria_evento'),
                'observacion' => $request->get('observacion_porteria_evento'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            $itemPorteria->estado = false;
            $itemPorteria->save();

            //NOTIFICAR MEDIANTE EL CHAT
            $chat = Chat::where('relation_type', '11')
                ->whereHas('personas', function ($query) use($itemPorteria) {
                    $query->where('user_id', $itemPorteria->id_usuario);
                })
                ->first();

            if (!$chat) {
                $chat = new Chat([
                    'name' => 'PORTERIA',
                    'is_group' => true,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                $chat->relation()->associate($evento);
                $evento->chats()->save($chat);

                ChatUser::create([
                    'chat_id' => $chat->id,
                    'user_id' => $itemPorteria->id_usuario,
                ]);
            }

            $dataMensaje = 'Se ha grabado en ';
            $dataMensaje.= $itemPorteria->nombre ? $itemPorteria->nombre : $itemPorteria->placa;

            $mensaje = Message::create([
                'chat_id' => $chat->id,
                'user_id' => request()->user()->id,
                'content' => $dataMensaje,
                'status' => 1
            ]);

            $archivos = $request->get('archivos');

            if (count($archivos)) {
                foreach ($archivos as $archivo) {
                    $archivoCache = ArchivosCache::where('id', $archivo['id'])->first();
                    $finalPath = 'maximo/empresas/'.request()->user()->id_empresa.'/imagen/porteria/'.$archivoCache->name_file;
                    if (Storage::exists($archivoCache->relative_path)) {
                        Storage::move($archivoCache->relative_path, $finalPath);
                        
                        $archivo = new ArchivosGenerales([
                            'tipo_archivo' => $archivoCache->tipo_archivo,
                            'url_archivo' => $finalPath,
                            'estado' => 1,
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);
                        $archivo->relation()->associate($mensaje);
                        $mensaje->archivos()->save($archivo);
                    }
                    $archivoCache->delete();
                }
            }

            $empresa = Empresa::where('id', request()->user()->id_empresa)->first();

            event(new PrivateMessageEvent('mensajeria-'.$empresa->token_db_maximo.'_'.$itemPorteria->id_usuario, [
                'chat_id' => $chat->id,
                'permisos' => 'mensajes pqrsf',
                'action' => 'creacion_pqrsf'
            ]));

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