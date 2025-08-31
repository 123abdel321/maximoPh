<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Events\PrivateMessageEvent;
use App\Http\Controllers\Controller;
use App\Helpers\NotificacionGeneral;
use App\Helpers\WhatsApp\SendWhatApp;
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
            
            $porteriaEvento = PorteriaEvento::with('archivos', 'persona.inmueble.zona', 'persona.archivos')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                );

            // Filtro por inmueble (ahora a través de la relación persona.inmueble)
            if ($request->get("id_inmueble")) {
                $porteriaEvento->whereHas('persona.inmueble', function($query) use ($request) {
                    $query->where('id', $request->get("id_inmueble"));
                });
            }

            if ($request->get("tipo") || $request->get("tipo") == '0') {
                $porteriaEvento->where('tipo', $request->get("tipo"));
            }

            if ($request->get("fecha_desde") && $request->get("fecha_hasta")) {
                $fechaDesde = Carbon::parse($request->get("fecha_desde"))->startOfDay();
                $fechaHasta = Carbon::parse($request->get("fecha_hasta"))->endOfDay();
                $porteriaEvento->where('created_at', '>=', $fechaDesde)
                    ->where('created_at', '<=', $fechaHasta);
            } else if ($request->get("fecha_desde")) {
                $fechaDesde = Carbon::parse($request->get("fecha_desde"))->startOfDay();
                $porteriaEvento->where('created_at', '>=', $fechaDesde);
            } else if ($request->get("fecha_hasta")) {
                $fechaHasta = Carbon::parse($request->get("fecha_hasta"))->endOfDay();
                $porteriaEvento->where('created_at', '<=', $fechaHasta);
            }

            if ($request->get("search")) {
                $porteriaEvento->where('observacion', 'like', '%' .$request->get("search"). '%')
                    ->orWhereHas('persona', function ($query) use ($request) {
                        $query->where('nombre', 'like', '%' .$request->get("search"). '%')
                            ->orWhere('placa', 'like', '%' .$request->get("search"). '%')
                            ->orWhere('observacion', 'like', '%' .$request->get("search"). '%');
                    })
                    ->orWhereHas('persona.inmueble', function ($query) use ($request) {
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
            if (! $itemPorteria->id_usuario) {
                $empresa = Empresa::where('id', request()->user()->id_empresa)->first();
    
                DB::connection('max')->commit();
    
                return response()->json([
                    'success'=>	true,
                    'data' => $evento,
                    'message'=> 'Evento creado con exito!'
                ]);
            }
            //CREAR CHAT NOVEDADES SI EXISTE
            $chat = Chat::where('relation_type', '10')
                ->whereHas('personas', function ($query) use($itemPorteria) {
                    $query->where('user_id', $itemPorteria->id_usuario);
                })
                ->first();

            if (!$chat) {
                $chat = new Chat([
                    'name' => 'PORTERIA #'.$evento->id,
                    'is_group' => true,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                $chat->relation()->associate($itemPorteria);
                $itemPorteria->chats()->save($chat);

                ChatUser::create([
                    'chat_id' => $chat->id,
                    'user_id' => $itemPorteria->id_usuario,
                ]);
            }

            $tipoPorteria = 'Propietario';
            if ($itemPorteria->tipo_porteria == 1) $tipoPorteria = 'Residente';
            if ($itemPorteria->tipo_porteria == 2) $tipoPorteria = 'Mascota';
            if ($itemPorteria->tipo_porteria == 3) {
                $tipoPorteria = "Carro";
                if ($itemPorteria->tipo_vehiculo == 1) $tipoPorteria = 'Moto';
                if ($itemPorteria->tipo_vehiculo == 2) $tipoPorteria = 'Moto electrica';
                if ($itemPorteria->tipo_vehiculo == 2) $tipoPorteria = 'Bicicleta electrica';
                if ($itemPorteria->tipo_vehiculo == 4) $tipoPorteria = 'Otros';
            }
            if ($itemPorteria->tipo_porteria == 4) $tipoPorteria = 'Visitante';
            if ($itemPorteria->tipo_porteria == 5) $tipoPorteria = 'Paquete';
            if ($itemPorteria->tipo_porteria == 6) $tipoPorteria = 'Domicilio';

            $nombrePorteria = $itemPorteria->nombre ? $itemPorteria->nombre : $itemPorteria->placa;

            $fechaIngreso = $evento->fecha_ingreso ? Carbon::parse($evento->fecha_ingreso)->format('Y-m-d H:m') : 'Sin registrar';
            $fechaSalida = $evento->fecha_salida ? Carbon::parse($evento->fecha_salida)->format('Y-m-d H:m') : 'Sin registrar';
            
            $contentMensaje = "
                <b style='color: aqua;'>Tipo: </b>{$tipoPorteria}<br/>
                <b style='color: aqua;'>Nombre: </b>{$nombrePorteria}<br/>
                <b style='color: aqua;'>Fecha ingreso: </b>{$fechaIngreso}<br/>
                <b style='color: aqua;'>Fecha salida: </b>{$fechaSalida}<br/>
                <b style='color: aqua;'>Observación: </b>{$evento->observacion}<br/>
            ";

            $mensaje = Message::create([
                'chat_id' => $chat->id,
                'user_id' => request()->user()->id,
                'content' => $contentMensaje,
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

            // $telefono = "3145876923";

            // if ($telefono) {
            //     (new SendWhatApp(
            //         "evento_porteria",
            //         "57{$telefono}",
            //         [
            //             $tipoPorteria,
            //             $nombrePorteria,
            //             $fechaIngreso,
            //             $fechaSalida,
            //             $evento->observacion
            //         ]
            //     ))->send(request()->user()->id_empresa);
            // }

            $empresa = Empresa::where('id', request()->user()->id_empresa)->first();

            event(new PrivateMessageEvent('mensajeria-'.$empresa->token_db_maximo.'_'.$itemPorteria->id_usuario, [
                'chat_id' => $chat->id,
                'permisos' => 'mensajes porteria',
                'action' => 'creacion_porteria'
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