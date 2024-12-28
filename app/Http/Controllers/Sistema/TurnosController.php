<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Events\PrivateMessageEvent;
use App\Helpers\NotificacionGeneral;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Chat;
use App\Models\Sistema\Turno;
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Message;
use App\Models\Sistema\ChatUser;
use App\Models\Sistema\MessageUser;
use App\Models\Sistema\TurnoEvento;
use App\Models\Sistema\ArchivosCache;
use App\Models\Empresa\UsuarioEmpresa;
use App\Models\Sistema\Notificaciones;
use App\Models\Sistema\ArchivosGenerales;

class TurnosController extends Controller
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
        return view('pages.tareas.turnos.turnos-view');
    }

    public function read (Request $request)
    {
        $start =  Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        $data = array();
        $tipo = $request->tipo == 'null' ? null : $request->tipo;
        $estado = $request->estado == 'null' ? null : $request->estado;
        $id_empleado = $request->id_empleado == 'null' ? null : $request->id_empleado;
        $id_proyecto = $request->id_proyecto == 'null' ? null : $request->id_proyecto;

        $turnos = Turno::where(function($query) use ($start, $end) {
            $query->whereBetween('fecha_inicio', [$start, $end])
                ->orWhereBetween('fecha_fin', [$start, $end])
                ->orWhere(function($query) use ($start, $end) {
                    $query->where('fecha_inicio', '<=', $start)
                        ->where('fecha_fin', '>=', $end);
                });
            })
            ->when($tipo || $tipo == '0' ? true : false, function ($query) use($tipo) {
				$query->where('tipo', $tipo);
			})
            ->when($estado, function ($query) use($estado) {
				$query->where('estado', $estado);
			})
            ->when($id_empleado, function ($query) use($id_empleado) {
				$query->where('id_usuario', $id_empleado);
			})
            ->when($id_proyecto, function ($query) use($id_proyecto) {
				$query->where('id_proyecto', $id_proyecto);
			});
        
        if (!$request->user()->can('turnos create')) {
            $turnos->where('id_usuario', $request->user()['id']);
        }

        $dataTurno = $turnos->get();

        foreach ($dataTurno as $turno) {
            $fechaInicio = Carbon::parse($turno->fecha_inicio)->format('Y-m-d');
            $fechaFin = Carbon::parse($turno->fecha_fin)->format('Y-m-d');

            $horaInicio = Carbon::parse($turno->fecha_inicio)->format('H:i:s');
            $horaFin = Carbon::parse($turno->fecha_fin)->format('H:i:s');

            $color = "#055ebe";
            if ($turno->tipo == 1) $color = "#28b463";
            if ($turno->estado == 2) {
                if ($turno->tipo == 1) $color = "#76a98b";
                else $color = "#6689af";
            }

            array_push($data, array(
                'backgroundColor' => $color,
                'borderColor' => $color,
                'id' => $turno->id,
                'title' => $turno->asunto,
                'start' => $horaInicio == "00:00:00" ? $fechaInicio : $fechaInicio.' '.$horaInicio,
                'end' => $horaFin == "00:00:00" ? $fechaFin : $fechaFin.' '.$horaFin,
            ));
        }

        return response()->json($data);
    }

    public function table (Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');

            $turnos = Turno::with('responsable', 'creador', 'nit', 'archivos', 'eventos')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                )
                ->orderBy('id', 'DESC');

            if ($request->get('fecha_desde')) $turnos->where('fecha_inicio', '>=', $request->get('fecha_desde'));
            if ($request->get('fecha_hasta')) $turnos->where('fecha_fin', '<=', $request->get('fecha_hasta').' 23:59:59');
            if ($request->get('id_usuario')) $turnos->where('id_usuario', $request->get('id_usuario'));
            if ($request->get('id_proyecto')) $turnos->where('id_proyecto', $request->get('id_proyecto'));
            if ($request->get('tipo') || $request->get('tipo') == '0') $turnos->where('tipo', $request->get('tipo'));
            if ($request->get('estado') || $request->get('estado') == '0') $turnos->where('estado', $request->get('estado'));

            if (!$request->user()->can('turnos create')) {
                $turnos->where('id_usuario', $request->user()['id']);
            }

            $turnosTotals = $turnos->get();

            $turnosPaginate = $turnos->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $turnosTotals->count(),
                'iTotalDisplayRecords' => $turnosTotals->count(),
                'data' => $turnosPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Turno generados con exito!'
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
            'id_usuario_turno' => 'required|exists:clientes.users,id',
            'id_proyecto_turno' => 'nullable|exists:max.proyectos,id',
            'fecha_inicio_turno' => 'required',
            'fecha_fin_turno' => 'required',
            'hora_inicio_turno' => 'required',
            'hora_fin_turno' => 'required',
            'asunto_turno' => 'required',
            'mensaje_turno' => 'required'
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
            
            $usuarioEmpresa = UsuarioEmpresa::with('usuario', 'nit')
                ->where('id_usuario', $request->get('id_usuario_turno'))
                ->where('id_empresa', request()->user()->id_empresa)
                ->first();

            if (!$usuarioEmpresa->id_nit) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'El usuario no tiene nit asociado en la empresa'
                ], 422);
            }

            if ($request->get('multiple_tarea_turno')) {
                $dias = $this->getDiasString($request);

                $inicio = Carbon::parse($request->get('fecha_inicio_turno'));
                $fin = Carbon::parse($request->get('fecha_fin_turno'));

                while ($inicio->lte($fin)) {
                    $numero_dia = $inicio->isoWeekday();

                    if (in_array($numero_dia, $dias)) {

                        $fechaInicio = $inicio->format('Y-m-d').' '.$request->get('hora_inicio_turno');
                        $fechaFin = $inicio->format('Y-m-d').' '.$request->get('hora_fin_turno');

                        $turno = Turno::create([
                            'id_usuario' => $request->get('id_usuario_turno'),
                            'id_nit' => $usuarioEmpresa->id_nit,
                            'id_proyecto' => $request->get('id_proyecto_turno'),
                            'tipo' => $request->get("tipo_turno"),
                            'fecha_inicio' => $fechaInicio,
                            'fecha_fin' => $fechaFin,
                            'asunto' => $request->get("asunto_turno"),
                            'descripcion' => $request->get("mensaje_turno"),
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);
                        
                        $nombreTurno = $request->get("tipo_turno") ? 'TAREA' : 'TURNO';

                        $chat = new Chat([
                            'name' => "{$nombreTurno} #{$turno->id}",
                            'is_group' => true,
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);
            
                        $chat->relation()->associate($turno);
                        $turno->chats()->save($chat);
            
                        ChatUser::create([
                            'chat_id' => $chat->id,
                            'user_id' => $request->get('id_usuario_turno'),
                        ]);

                        ChatUser::create([
                            'chat_id' => $chat->id,
                            'user_id' => request()->user()->id,
                        ]);

                        $fechaInicio = Carbon::parse($turno->fecha_inicio)->format('Y-m-d');
                        $fechaFin = Carbon::parse($turno->fecha_fin)->format('Y-m-d');
                        
                        $horaInicio = "00:00:00";
                        $horaFin = "00:00:00";
                        
                        if ($turno->fecha_inicio) $horaInicio = Carbon::parse($turno->fecha_inicio)->format('H:i:s');
                        if ($turno->fecha_fin) $horaFin = Carbon::parse($turno->fecha_fin)->format('H:i:s');

                        $start = $horaInicio == "00:00:00" ? $fechaInicio : $fechaInicio.' '.$horaInicio;
                        $end = $horaFin == "00:00:00" ? $fechaFin : $fechaFin.' '.$horaFin;

                        $contentMensaje = "
                            <b style='color: aqua;'>Asunto: </b>{$request->get("asunto_turno")}<br/>
                            <b style='color: aqua;'>Descripción: </b>{$request->get("mensaje_turno")}<br/>
                            <b style='color: aqua;'>Fecha inicio: </b>{$start}<br/>
                            <b style='color: aqua;'>Fecha fin: </b>{$end}<br/>
                        ";
            
                        $mensaje = Message::create([
                            'chat_id' => $chat->id,
                            'user_id' => request()->user()->id,
                            'content' => $contentMensaje,
                            'status' => 1
                        ]);
            
                        MessageUser::firstOrCreate([
                            'message_id' => $mensaje->id,
                            'user_id' => request()->user()->id,
                        ]);
                        
                        $archivos = $request->get('archivos');
                        
                        if (count($archivos)) {
                            foreach ($archivos as $archivo) {
                                $archivoCache = ArchivosCache::where('id', $archivo['id'])->first();
                                $finalPath = 'maximo/empresas/'.request()->user()->id_empresa.'/imagen/turnos/'.$archivoCache->name_file;
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
                    }

                    $inicio->addDay();
                }

                $empresa = Empresa::where('id', request()->user()->id_empresa)->first();
    
                event(new PrivateMessageEvent('mensajeria-'.$empresa->token_db_maximo.'_'.$request->get('id_usuario_turno'), [
                    'chat_id' => null,
                    'permisos' => 'mensajes turnos',
                    'action' => 'creacion_turnos'
                ]));

            } else {

                $fechaInicio = $request->get("fecha_inicio_turno").' '.$request->get("hora_inicio_turno");
                $fechaFin = $request->get("fecha_fin_turno").' '.$request->get("hora_fin_turno");

                $turno = Turno::create([
                    'id_usuario' => $request->get('id_usuario_turno'),
                    'id_nit' => $usuarioEmpresa->id_nit,
                    'id_proyecto' => $request->get('id_proyecto_turno'),
                    'tipo' => $request->get("tipo_turno"),
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'asunto' => $request->get("asunto_turno"),
                    'descripcion' => $request->get("mensaje_turno"),
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                $nombreTurno = $request->get("tipo_turno") ? 'TAREA' : 'TURNO';

                $chat = new Chat([
                    'name' => "{$nombreTurno} #{$turno->id}",
                    'is_group' => true,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);
    
                $chat->relation()->associate($turno);
                $turno->chats()->save($chat);

                ChatUser::create([
                    'chat_id' => $chat->id,
                    'user_id' => $request->get('id_usuario_turno'),
                ]);
    
                ChatUser::create([
                    'chat_id' => $chat->id,
                    'user_id' => request()->user()->id,
                ]);

                $fechaInicio = Carbon::parse($turno->fecha_inicio)->format('Y-m-d');
                $fechaFin = Carbon::parse($turno->fecha_fin)->format('Y-m-d');
                
                $horaInicio = "00:00:00";
                $horaFin = "00:00:00";
                
                if ($turno->fecha_inicio) $horaInicio = Carbon::parse($turno->fecha_inicio)->format('H:i:s');
                if ($turno->fecha_fin) $horaFin = Carbon::parse($turno->fecha_fin)->format('H:i:s');

                $start = $horaInicio == "00:00:00" ? $fechaInicio : $fechaInicio.' '.$horaInicio;
                $end = $horaFin == "00:00:00" ? $fechaFin : $fechaFin.' '.$horaFin;

                $contentMensaje = "
                    <b style='color: crimson;'>Asunto: </b>{$request->get("asunto_turno")}<br/>
                    <b style='color: crimson;'>Descripción: </b>{$request->get("mensaje_turno")}<br/>
                    <b style='color: crimson;'>Fecha inicio: </b>{$start}<br/>
                    <b style='color: crimson;'>Fecha fin: </b>{$end}<br/>
                ";
    
                $mensaje = Message::create([
                    'chat_id' => $chat->id,
                    'user_id' => request()->user()->id,
                    'content' => $contentMensaje,
                    'status' => 1
                ]);
    
                MessageUser::firstOrCreate([
                    'message_id' => $mensaje->id,
                    'user_id' => request()->user()->id,
                ]);
                
                $archivos = $request->get('archivos');
                
                if (count($archivos)) {
                    foreach ($archivos as $archivo) {
                        $archivoCache = ArchivosCache::where('id', $archivo['id'])->first();
                        $finalPath = 'maximo/empresas/'.request()->user()->id_empresa.'/imagen/turnos/'.$archivoCache->name_file;
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
    
                event(new PrivateMessageEvent('mensajeria-'.$empresa->token_db_maximo.'_'.$request->get('id_usuario_turno'), [
                    'chat_id' => $chat->id,
                    'permisos' => 'mensajes turnos',
                    'action' => 'creacion_turnos'
                ]));
            }

            DB::connection('max')->commit();

            $turnoData = (object)[
                'id' => $turno->id,
                'title' => $turno->asunto,
                'start' => $horaInicio == "00:00:00" ? $fechaInicio : $fechaInicio.' '.$horaInicio,
                'end' => $horaFin == "00:00:00" ? $fechaFin : $fechaFin.' '.$horaFin,
            ];

            return response()->json([
                'success'=>	true,
                'data' => $turnoData,
                'message'=> 'Turno creado con exito!'
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

    public function update (Request $request)
    {
        $rules = [
            'id' => 'required|exists:max.turnos,id',
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'hora_inicio' => 'required',
            'hora_fin' => 'required'
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

            $fechaInicio = $request->get("fecha_inicio").' '.$request->get("hora_inicio");
            $fechaFin = $request->get("fecha_fin").' '.$request->get("hora_fin");

            Turno::where('id', $request->get('id'))
                ->update([
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'updated_by' => request()->user()->id
                ]);

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Turno actualizado con exito!'
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
        $rules = [
            'id' => 'required|exists:max.turnos,id'
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

            $turno = Turno::where('id', $request->get('id'))
                ->with('responsable', 'archivos', 'eventos.creador', 'eventos.archivos', 'creador')
                ->first();

            if ($turno->id_usuario == request()->user()->id && $turno->estado == 0) {
                $turno->estado = 3;
                $turno->save();
            }

            return response()->json([
                'success'=>	true,
                'data' => $turno,
                'message'=> 'Turno encontrado con exito!'
            ]);

        } catch (Exception $e) {

            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function updateEstado (Request $request)
    {
        $rules = [
            'id' => 'required|exists:max.turnos,id',
            'estado' => 'required',
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
            
            $nombreEstado = '<b class="turnos-chat-mensaje-activo">Activo</b>';
            if ($request->get('estado') == 1) {
                $nombreEstado = '<b class="turnos-chat-mensaje-proceso">En proceso</b>';
            }
            if ($request->get('estado') == 2) {
                $nombreEstado = '<b class="turnos-chat-mensaje-cerrado">Cerrado</b>';
            }
            
            $turnos = Turno::find($request->get('id'));
            $turnos->estado = $request->get('estado');
            $turnos->save();

            $usuarioNotificacion = $turnos->id_usuario;

            if ($turnos->id_usuario == $request->user()['id']) {
                $usuarioNotificacion = $turnos->created_by;
            }

            $mensajes = TurnoEvento::create([
                'id_turnos' => $turnos->id,
                'id_usuario' => $turnos->id_usuario,
                'descripcion' => 'Se ha cambiado el estado del turnos a '.$nombreEstado,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);
            
            $mensaje = TurnoEvento::where('id', $mensajes->id)
                ->with('archivos')
                ->get();

            $usuarioNotificacion = $turnos->id_usuario;
            if ($turnos->id_usuario == request()->user()->id) {
                $usuarioNotificacion = $turnos->created_by;
            }

            // CANALES DE NOTIFICACION
            $canalesNotificacion = [
                'turno-mensaje-responder-'.$request->user()['has_empresa'], //PERMISO: turno responder
                'turno-mensaje-'.$request->user()['has_empresa'].'_'.$turnos->id_usuario
            ];

            $notificacionesEnEspera = Notificaciones::where('notificacion_id', $turnos->id)
                ->where('id_usuario', $usuarioNotificacion)
                ->where('notificacion_type', 14)
                ->where('estado', 0)
                ->count();

            $nombreUsuario = request()->user()->lastname ? request()->user()->firstname.' '.request()->user()->lastname : request()->user()->firstname;

            $notificacion = (new NotificacionGeneral(
                request()->user()->id,
                $usuarioNotificacion,
                $turnos
            ));

            $mensajeText = '<b style="color: gold;">Tarea</b>: Ha recibido un nuevo <b>MENSAJE</b> de '.$nombreUsuario;

            $id_notificacion = $notificacion->crear((object)[
                'id_usuario' => $usuarioNotificacion,
                'tipo' => 1,
                'mensaje' => $mensajeText,
                'function' => 'abrirTurnosNotificacion',
                'data' => $turnos->id,
                'id_rol' => 1,
                'estado' => $notificacionesEnEspera ? 2 : 0,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ], true);

            $notificacion->notificar(
                $canalesNotificacion,
                [
                    'id_turno' => $turnos->id,
                    'data' => $mensaje->toArray(),
                    'estado' => 1,
                    'id_notificacion' => $id_notificacion,
                    'id_usuario' => $usuarioNotificacion
                ]
            );

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $mensajes,
                'notificar' => [],
                'message'=> 'Mensaje creado con exito!'
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

    public function createMensaje (Request $request, string $id)
    {
        $rules = [
            'mensaje_turnos_nuevo' => 'required',
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

            $turno = Turno::find($id);

            $turnoEvento = TurnoEvento::create([
                'id_turno' => $turno->id,
                'id_usuario' => $turno->id_usuario,
                'descripcion' => $request->get('mensaje_turnos_nuevo'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            if ($request->file('photos')) {
                foreach ($request->file('photos') as $photos) {
                    $nameFile = 'maximo/empresas/'.request()->user()->id_empresa.'/imagen/turnos';
                    $url = Storage::disk('do_spaces')->put($nameFile, $photos, 'public');
    
                    $archivo = new ArchivosGenerales([
                        'tipo_archivo' => 'imagen',
                        'url_archivo' => $url,
                        'estado' => 1,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);
        
                    $archivo->relation()->associate($turnoEvento);
                    $turnoEvento->archivos()->save($archivo);
                }
            }
            
            $mensaje = TurnoEvento::where('id', $turnoEvento->id)
                ->with('archivos')
                ->get();

            $usuarioNotificacion = $turno->id_usuario;
            if ($turno->id_usuario == request()->user()->id) {
                $usuarioNotificacion = $turno->created_by;
                $turno->estado = 1;
                $turno->save();
            }

            // CANALES DE NOTIFICACION
            $canalesNotificacion = [
                'turno-mensaje-responder-'.$request->user()['has_empresa'], //PERMISO: turno responder
                'turno-mensaje-'.$request->user()['has_empresa'].'_'.$turno->id_usuario
            ];

            $notificacionesEnEspera = Notificaciones::where('notificacion_id', $id)
                ->where('id_usuario', $usuarioNotificacion)
                ->where('notificacion_type', 14)
                ->where('estado', 0)
                ->count();

            $nombreUsuario = request()->user()->lastname ? request()->user()->firstname.' '.request()->user()->lastname : request()->user()->firstname;

            $notificacion = (new NotificacionGeneral(
                request()->user()->id,
                $usuarioNotificacion,
                $turno
            ));

            $mensajeText = '<b style="color: gold;">Tarea</b>: Ha recibido un nuevo <b>MENSAJE</b> de '.$nombreUsuario;

            $id_notificacion = $notificacion->crear((object)[
                'id_usuario' => $usuarioNotificacion,
                'tipo' => 1,
                'mensaje' => $mensajeText,
                'function' => 'abrirTurnosNotificacion',
                'data' => $id,
                'id_rol' => 1,
                'estado' => $notificacionesEnEspera ? 2 : 0,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ], true);

            $notificacion->notificar(
                $canalesNotificacion,
                [
                    'id_turno' => $id,
                    'data' => $mensaje->toArray(),
                    'estado' => 1,
                    'id_notificacion' => $id_notificacion,
                    'id_usuario' => $usuarioNotificacion
                ]
            );

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $turnoEvento,
                'notificar' => [],
                'message'=> 'Mensaje creado con exito!'
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

    public function createEvento (Request $request)
    {
        $rules = [
            'id_turno_evento' => 'required|exists:max.turnos,id',
            'mensaje_turno_evento' => 'nullable',
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

            $turnoEvento = TurnoEvento::create([
                'id_turno' => $request->get('id_turno_evento'),
                'id_usuario' => request()->user()->id,
                'descripcion' => $request->get('mensaje_turno_evento'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            if ($request->file('photos')) {
                foreach ($request->file('photos') as $photos) {
                    $nameFile = 'maximo/empresas/'.request()->user()->id_empresa.'/imagen/turnos';
                    $url = Storage::disk('do_spaces')->put($nameFile, $photos, 'public');
    
                    $archivo = new ArchivosGenerales([
                        'tipo_archivo' => 'imagen',
                        'url_archivo' => $url,
                        'estado' => 1,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);
        
                    $archivo->relation()->associate($turnoEvento);
                    $turnoEvento->archivos()->save($archivo);
                }
            }

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $turnoEvento,
                'message'=> 'Turno evento creado con exito!'
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
            'id' => 'required|exists:max.turnos,id',
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

            $turno = Turno::with('archivos')
                ->where('id', $request->get('id'))
                ->first();

            $turnosEvento = TurnoEvento::with('archivos')
                ->where('id_turno', $request->get('id'))
                ->get();

            if (count($turno->archivos)) {
                foreach ($turno->archivos as $archivo) {
                    Storage::disk('do_spaces')->delete($archivo->url_archivo);
                    $archivo->delete();
                }
            }

            if (count($turnosEvento)) {
                foreach ($turnosEvento as $turnoEvento) {
                    if (count($turnoEvento->archivos)) {
                        foreach ($turnoEvento->archivos as $archivo) {
                            Storage::disk('do_spaces')->delete($archivo->url_archivo);
                            $archivo->delete();
                        }
                    }
                }
            }

            Turno::where('id', $request->get('id'))->delete();
            TurnoEvento::where('id_turno', $request->get('id'))->delete();

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Turno/Tarea eliminada con exito!'
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

    private function getDiasString ($request)
    {
        $dias = [];
        for ($i = 1; $i <= 7; $i++) {
            if ($request->get('diaTurno'.$i)) {
                array_push($dias, $i);
            }
        }
        return $dias;
    }

}