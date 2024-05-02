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
use App\Models\Sistema\Pqrsf;
use App\Models\Empresa\Empresa;
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

            $columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc

            $pqrsf = Pqrsf::orderBy($columnName,$columnSortOrder)
                ->with('archivos', 'usuario')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                );

            if ($request->get('search')) {
            }

            $usuario_empresa = UsuarioEmpresa::where('id_empresa', $request->user()['id_empresa'])
                ->where('id_usuario', $request->user()['id'])
                ->first();

            if ($usuario_empresa->id_rol == 1 || $usuario_empresa->id_rol == 2) {
                
            } else {
                $pqrsf->where('id_usuario', $request->user()['id'])
                    ->orWhere('created_by', $request->user()['id']);
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
            $pqrsf = Pqrsf::with('archivos', 'usuario', 'creador', 'mensajes.archivos')
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
            'id_usuario_pqrsf' => 'required|exists:clientes.users,id',
            'tipo_pqrsf' => 'required',
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

            $pqrsf = Pqrsf::create([
                'id_usuario' => $request->get('id_usuario_pqrsf'),
                'id_nit' => null,
                'tipo' => $request->get("tipo_pqrsf"),
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
            $id_notificacion = $notificacion->crear((object)[
                'id_usuario' => $pqrsf->id_usuario,
                'mensaje' => $mensaje,
                'function' => 'abrirPqrsfNotificacion',
                'data' => $pqrsf->id,
                'estado' => 0,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ], true);
            $notificacion->notificar(
                'pqrsf-mensaje-'.$request->user()['has_empresa'].'_'.$pqrsf->id_usuario ,
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
                'pqrsf-mensaje-'.$request->user()['has_empresa'].'_'.$usuarioNotificacion,
                ['id_pqrsf' => $id, 'data' => $mensaje->toArray(), 'id_notificacion' => $id_notificacion]
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