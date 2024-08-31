<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Carbon\Carbon;
use App\Mail\GeneralEmail;
use Illuminate\Http\Request;
use App\Events\PrivateMessageEvent;
use App\Helpers\NotificacionGeneral;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Pqrsf;
use App\Models\Empresa\Empresa;
use App\Models\Portafolio\Nits;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\PqrsfTiempos;
use App\Models\Sistema\PqrsfMensajes;
use App\Models\Empresa\UsuarioEmpresa;
use App\Models\Sistema\Notificaciones;
use App\Models\Sistema\ArchivosGenerales;

class PqrsfController extends Controller
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
        $data = [
            'usuario_empresa' => UsuarioEmpresa::where('id_empresa', $request->user()['id_empresa'])
                ->where('id_usuario', $request->user()['id'])
                ->first()
        ];
        
        return view('pages.administrativo.pqrsf.pqrsf-view', $data);
    }

    public function read (Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');

            $pqrsf = Pqrsf::with('archivos', 'usuario', 'nit')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                )
                ->orderBy('id', 'DESC');

            if ($request->get('fecha_desde')) $pqrsf->where('created_at', '>=', $request->get('fecha_desde'));
            if ($request->get('fecha_hasta')) $pqrsf->where('created_at', '<=', $request->get('fecha_hasta').' 23:59:59');
            if ($request->get('id_nit')) $pqrsf->where('id_nit', $request->get('id_nit'));
            if ($request->get('tipo')) $pqrsf->where('tipo', $request->get('tipo'));
            if ($request->get('area')) $pqrsf->where('area', $request->get('area'));
            if ($request->get('estado') || $request->get('estado') == '0') $pqrsf->where('estado', $request->get('estado'));

            $usuario_empresa = UsuarioEmpresa::where('id_empresa', $request->user()['id_empresa'])
                ->where('id_usuario', $request->user()['id'])
                ->first();

            if ($usuario_empresa->id_rol == 3 || $usuario_empresa->id_rol == 4) {
                $pqrsf->where('id_usuario', $request->user()['id'])
                    ->orWhere('created_by', $request->user()['id'])
                    ->orWhere('id_nit', $usuario_empresa->id_nit);
            }

            $pqrsfTotals = $pqrsf->get();

            $pqrsfPaginate = $pqrsf->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $pqrsfTotals->count(),
                'iTotalDisplayRecords' => $pqrsfTotals->count(),
                'data' => $pqrsfPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Pqrsf generados con exito!'
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
            $pqrsf = Pqrsf::with('usuario', 'creador', 'nit', 'archivos', 'tiempos', 'mensajes.archivos')
                ->where('id', $request->get('id'))
                ->first();

            return response()->json([
                'success'=>	true,
                'data' => $pqrsf,
                'message'=> 'Datos Pqrsf cargados con exito!'
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
            'id_usuario_pqrsf' => 'nullable|exists:clientes.users,id',
            'tipo_pqrsf' => 'required',
            'area_pqrsf' => 'required',
            'hora_inicio_pqrsf' => 'nullable',
            'hora_fin_pqrsf' => 'nullable',
            'asunto_pqrsf' => 'nullable',
            'mensaje_pqrsf' => 'nullable',
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

            $id_usuario_pqrsf = $request->get('id_usuario_pqrsf');
            $id_nit = null;

            if (!$id_usuario_pqrsf || $request->get('tipo_pqrsf') == 5) {
                $empresa = Empresa::find(request()->user()->id_empresa);
                $id_usuario_pqrsf = $empresa->id_usuario_owner;
            }

            $usuarioEmpresa = UsuarioEmpresa::where('id_usuario', request()->user()->id)
                ->where('id_empresa', request()->user()->id_empresa)
                ->first();

            if ($usuarioEmpresa->id_rol != 1 && $usuarioEmpresa->id_rol != 2) {
                $id_nit = $usuarioEmpresa->id_nit;
            } else if ($request->get('tipo_pqrsf') == 5) {
                $usuarioEmpresa = UsuarioEmpresa::where('id_usuario', $request->get('id_usuario_pqrsf'))
                    ->where('id_empresa', request()->user()->id_empresa)
                    ->first();
                $id_nit = $usuarioEmpresa ? $usuarioEmpresa->id_nit : null;
            }

            $pqrsf = Pqrsf::create([
                'id_usuario' => $id_usuario_pqrsf,
                'id_nit' => $id_nit,
                'tipo' => $request->get("tipo_pqrsf"),
                'area' => $request->get("area_pqrsf"),
                'dias' => $this->getDiasString($request),
                'hoy' => $request->get('diaPorteria0') ? Carbon::now()->format('Y-m-d') : null,
                'asunto' => $request->get("asunto_pqrsf"),
                'descripcion' => $request->get("mensaje_pqrsf"),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            if ($request->file('photos')) {
                foreach ($request->file('photos') as $photos) {
                    $nameFile = 'maximo/empresas/'.request()->user()->id_empresa.'/imagen/pqrsf';
                    $url = Storage::disk('do_spaces')->put($nameFile, $photos, 'public');
    
                    $archivo = new ArchivosGenerales([
                        'tipo_archivo' => 'imagen',
                        'url_archivo' => $url,
                        'estado' => 1,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);
        
                    $archivo->relation()->associate($pqrsf);
                    $pqrsf->archivos()->save($archivo);
                }
            }

            $nombreUsuario = request()->user()->lastname ? request()->user()->firstname.' '.request()->user()->lastname : request()->user()->firstname;
            $mensaje = 'Ha recibido una nueva '.$this->tipoPqrsf($pqrsf->tipo).' de '.$nombreUsuario;
            $notificacion = (new NotificacionGeneral(
                request()->user()->id,
                $request->get('id_usuario_pqrsf'),
                $pqrsf
            ));
            $idUsuarioNotificacion = $pqrsf->id_usuario;

            if ($request->get('tipo_pqrsf') == 5) {
                $usuarioEmpresa = UsuarioEmpresa::where('id_usuario', $request->get('id_usuario_pqrsf'))
                    ->where('id_empresa', request()->user()->id_empresa)
                    ->first();

                $idUsuarioNotificacion = $usuarioEmpresa->id_usuario;
            }
            
            $id_notificacion = $notificacion->crear((object)[
                'id_usuario' =>  $idUsuarioNotificacion,
                'mensaje' => $mensaje,
                'function' => 'abrirPqrsfNotificacion',
                'data' => $pqrsf->id,
                'estado' => 0,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ], true);
            $notificacion->notificar(
                [
                    'pqrsf-mensaje-'.$request->user()['has_empresa'].'_'.$pqrsf->id_usuario,
                    'pqrsf-mensaje-'.$request->user()['has_empresa'].'_rol_admin'
                ],
                ['id_pqrsf' => $pqrsf->id, 'data' => [], 'id_notificacion' => $id_notificacion]
            );

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $pqrsf,
                'message'=> 'Pqrsf creado con exito!'
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
            'mensaje_pqrsf_nuevo' => 'required',
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

            $pqrsf = Pqrsf::find($id);

            $mensajes = PqrsfMensajes::create([
                'id_pqrsf' => $id,
                'id_usuario' => $pqrsf->id_usuario,
                'descripcion' => $request->get("mensaje_pqrsf_nuevo"),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            if ($request->file('photos')) {
                foreach ($request->file('photos') as $photos) {
                    $nameFile = 'maximo/empresas/'.request()->user()->id_empresa.'/imagen/pqrsf';
                    $url = Storage::disk('do_spaces')->put($nameFile, $photos, 'public');
    
                    $archivo = new ArchivosGenerales([
                        'tipo_archivo' => 'imagen',
                        'url_archivo' => $url,
                        'estado' => 1,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);
        
                    $archivo->relation()->associate($mensajes);
                    $mensajes->archivos()->save($archivo);
                }
            }

            $mensaje = PqrsfMensajes::where('id', $mensajes->id)
                ->with('archivos')
                ->get();
            
            $nombreUsuario = request()->user()->lastname ? request()->user()->firstname.' '.request()->user()->lastname : request()->user()->firstname;
            $usuarioNotificacion = $pqrsf->id_usuario;

            if ($pqrsf->id_usuario == $request->user()['id']) {
                $usuarioNotificacion = $pqrsf->created_by;
            }

            $notificacion =(new NotificacionGeneral(
                request()->user()->id,
                $usuarioNotificacion,
                $mensajes
            ));
            
            $id_notificacion = $notificacion->crear((object)[
                'id_usuario' => $usuarioNotificacion,
                'mensaje' => 'Ha recibido un nuevo mensaje de '.$nombreUsuario,
                'function' => 'abrirPqrsfNotificacion',
                'data' => $id,
                'estado' => 0,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ], true);
            $notificacion->notificar(
                [
                    'pqrsf-mensaje-'.$request->user()['has_empresa'].'_'.$usuarioNotificacion,
                    'pqrsf-mensaje-'.$request->user()['has_empresa'].'_rol_admin'
                ],
                ['id_pqrsf' => $id, 'data' => $mensaje->toArray(), 'estado' => 1, 'id_notificacion' => $id_notificacion]
            );

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $mensaje,
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

    public function updateEstado (Request $request)
    {
        $rules = [
            'id' => 'required|exists:max.pqrsf,id',
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
            $nombreEstado = '<b class="pqrsf-chat-mensaje-activo">Activo</b>';
            if ($request->get('estado') == 1) {
                $nombreEstado = '<b class="pqrsf-chat-mensaje-proceso">En proceso</b>';
            }
            if ($request->get('estado') == 2) {
                $nombreEstado = '<b class="pqrsf-chat-mensaje-cerrado">Cerrado</b>';
            }
            
            $pqrsf = Pqrsf::find($request->get('id'));
            $pqrsf->estado = $request->get('estado');
            $pqrsf->save();

            $usuarioNotificacion = $pqrsf->id_usuario;

            if ($pqrsf->id_usuario == $request->user()['id']) {
                $usuarioNotificacion = $pqrsf->created_by;
            }

            $mensajes = PqrsfMensajes::create([
                'id_pqrsf' => $request->get('id'),
                'id_usuario' => $usuarioNotificacion,
                'descripcion' => 'Se ha cambiado el estado del pqrsf a '.$nombreEstado,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            $notificacion =(new NotificacionGeneral(
                request()->user()->id,
                $usuarioNotificacion,
                $mensajes
            ));

            $dataMensaje = [
                'id_pqrsf' => $mensajes->id_pqrsf,
                'id_usuario' => $mensajes->id_usuario,
                'descripcion' => $mensajes->descripcion,
                'estado' => $request->get('estado'),
                'created_by' => $mensajes->created_by,
                'updated_by' => $mensajes->updated_by,
                'created_at' => $mensajes->created_at,
                'updated_at' => $mensajes->updated_at,
            ];
            
            $id_notificacion = $notificacion->crear((object)[
                'id_usuario' => $usuarioNotificacion,
                'mensaje' => 'Se ha cambiado el estado del pqrsf a '.$nombreEstado,
                'function' => 'abrirPqrsfNotificacion',
                'data' => $request->get('id'),
                'estado' => 0,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ], true);
            
            $notificacion->notificar(
                'pqrsf-mensaje-'.$request->user()['has_empresa'].'_'.$usuarioNotificacion,
                ['id_pqrsf' => $request->get('id'), 'data' => [$dataMensaje], 'id_notificacion' => $id_notificacion]
            );

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $pqrsf,
                'mensaje' => [$dataMensaje],
                'message'=> 'Estado actualizado con exito!'
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

    public function tiempo (Request $request)
    {
        $rules = [
            'id' => 'nullable|exists:max.pqrsf,id',
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

            $pqrsf = Pqrsf::with('tiempo')
                ->where('id', $request->get('id'))
                ->first();

            $diff = null;
            $function = 'inicioTimePqrsf';
            $agregoFechaFinal = false;
            $mensajeNotificacion = "Se ha iniciado el registro de tiempo";

            if ($pqrsf->tiempo && $pqrsf->tiempo->fecha_fin && $pqrsf->tiempo->fecha_fin != '0000-00-00 00:00:00') {
                PqrsfTiempos::create([
                    'id_pqrsf' => $pqrsf->id,
                    'id_usuario' => request()->user()->id,
                    'fecha_inicio' => Carbon::now(),
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);
                
            } else if ($pqrsf->tiempo){
                $function = 'pararPqrsf';
                $agregoFechaFinal = true;
                $fechaInicio = Carbon::parse($pqrsf->tiempo->fecha_inicio);
                $fechaFin = Carbon::now();
                $diff = $fechaInicio->diff($fechaFin);
                $mensajeNotificacion = "Se ha finalizado el registro de tiempo con un total de ".$diff->format('%h')." Horas ".$diff->format('%i')." Minutos y ".$diff->format('%s'." Segundos");
                PqrsfTiempos::where('id', $pqrsf->tiempo->id)
                    ->update([
                        'fecha_fin' => $fechaFin,
                        'tiempo_total' => $diff->format('%y, %m, %d, %h, %i, %s'),
                        'updated_by' => request()->user()->id
                    ]);
            } else {
                PqrsfTiempos::create([
                    'id_pqrsf' => $pqrsf->id,
                    'id_usuario' => request()->user()->id,
                    'fecha_inicio' => Carbon::now(),
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);
            }

            $usuarioNotificacion = $pqrsf->id_usuario;
            $nombreUsuario = request()->user()->lastname ? request()->user()->firstname.' '.request()->user()->lastname : request()->user()->firstname;

            if ($pqrsf->id_usuario == $request->user()['id']) {
                $usuarioNotificacion = $pqrsf->created_by;
            }

            $mensajes = PqrsfMensajes::create([
                'id_pqrsf' => $pqrsf->id,
                'id_usuario' => $usuarioNotificacion,
                'descripcion' => $mensajeNotificacion,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);
            $mensaje = PqrsfMensajes::where('id', $mensajes->id)
                ->with('archivos')
                ->get();

            $notificacion =(new NotificacionGeneral(
                request()->user()->id,
                $usuarioNotificacion,
                $mensajes
            ));
            
            $id_notificacion = $notificacion->crear((object)[
                'id_usuario' => $usuarioNotificacion,
                'mensaje' => $nombreUsuario. ' ah agregado tiempo a la tarea',
                'function' => 'abrirPqrsfNotificacion',
                'data' => $pqrsf->id,
                'estado' => 0,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ], true);

            $notificacion->notificar(
                [
                    'pqrsf-mensaje-'.$request->user()['has_empresa'].'_'.$usuarioNotificacion,
                    'pqrsf-mensaje-'.$request->user()['has_empresa'].'_rol_admin'
                ],
                [
                    'id_pqrsf' => $pqrsf->id,
                    'data' => $mensaje->toArray(),
                    'id_notificacion' => $id_notificacion,
                    'function' => $function,
                ]
            );

            $pqrsf = Pqrsf::with('tiempos')
                ->where('id', $request->get('id'))
                ->first();

            $pqrsf->estado = 1;
            $pqrsf->save();

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $pqrsf,
                'mensaje' => $mensaje->toArray(),
                'message'=> 'Tiempo agregado con exito!'
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

    public function sendEmail(Request $request)
    {
        $empresa = Empresa::where('token_db_maximo', $request->user()['has_empresa'])->first();
        // dd($empresa);
        $nits = InmuebleNit::with('nit');

        if ($request->get('id_zona')) {
            $nits->whereHas('inmueble', function ($query) use ($request) {
				$query->where('id_zona', $request->get('id_zona'));
			});
        }

        if ($request->get('id_nit')) {
            $nits = Nits::where('id', $request->get('id_nit'));
        }

        $nits->chunk(233, function($datos) use($empresa, $request) {
            foreach ($datos as $nit) {
                $nit = $nit;
                if ($nit->nit) $nit = $nit->nit;

                if ($nit->email_1) {
                    Mail::to($nit->email_1)
                    ->cc('noreply@maximoph.com')
                    ->bcc('bcc@maximoph.com')
                    ->queue(new GeneralEmail($empresa->razon_social, 'emails.mensaje', [
                        'nombre' => $nit->nombre_completo,
                        'mensaje' => $request->get('texto'),
                        'logo' => $empresa->logo,
                    ]));
                }
            }
        });


        return response()->json([
            'success'=>	true,
            'message'=> 'Emails enviados con exito!'
        ]);
    }

    private function getDiasString ($request)
    {
        $dias = "";
        for ($i = 1; $i <= 7; $i++) {
            if ($request->get('diaPqrsf'.$i)) {
                if ($dias) $dias.= ",".$i;
                else $dias.=$i;
            }
        }
        return $dias;
    }

    private function tipoPqrsf ($tipo)
    {
        if ($tipo == '5') return '<b>TAREA</b>';
        if ($tipo == '1') return '<b>QUEJA</b>';
        if ($tipo == '2') return '<b>RECLAMO</b>';
        if ($tipo == '3') return '<b>SOLICITUD</b>';
        if ($tipo == '4') return '<b>FELICITACION</b>';

        return 'Peticion';
    }
}