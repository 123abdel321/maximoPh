<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Notificaciones;
use App\Models\Empresa\UsuarioEmpresa;

class NotificacionesController extends Controller
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
            $canalesAdmin = [];

            if (auth()->user()->can('mensajes pqrsf')) $canalesAdmin[] = 12;
            if (auth()->user()->can('mensajes turnos')) $canalesAdmin[] = 14;
            if (auth()->user()->can('mensajes novedades')) $canalesAdmin[] = 16;

            $totalMensajes = DB::connection('max')
                ->table('chats AS CH')
                ->where(function ($query) use($canalesAdmin) {
                    $query->whereExists(function ($subquery) use($canalesAdmin) {
                        $subquery->select(DB::raw(1))
                            ->from('chat_users AS CU')
                            ->whereColumn('CU.chat_id', 'CH.id')
                            ->where('CU.user_id', auth()->user()->id);
                    })
                    ->orWhereIn('CH.relation_type', $canalesAdmin);
                })
                ->join('messages AS ME', 'CH.id', '=', 'ME.chat_id')
                ->whereNotIn('ME.id', function ($query) {
                    $query->select('message_id')
                        ->from('message_users')
                        ->where('user_id', auth()->user()->id); // Excluye mensajes ya leídos
                })
                ->count();

            return response()->json([
                'success'=>	true,
                'total' => $totalMensajes,
                'message'=> 'Notificaciones generados con exito!'
            ]);

        } catch (Exception $e) {
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
            'id' => 'required|exists:max.notificaciones,id',
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

            $notificacion = Notificaciones::find($request->get('id'));
            $notificacion->updated_by = request()->user()->id;
            $notificacion->estado = $request->get('estado');
            $notificacion->id_rol = 0;
            $idUsuario = $notificacion->id_usuario;
            if (!$notificacion->id_usuario) {
                $idUsuario = request()->user()->id;
                $notificacion->id_usuario = request()->user()->id;
            }

            $notificacion->save();
            
            Notificaciones::where('notificacion_type', 12)
                ->where('id_usuario', $notificacion->id_usuario)
                ->where('notificacion_id', $notificacion->notificacion_id)
                ->where('estado', 0)
                ->update([
                    'estado' => $request->get('estado'),
                    'id_rol' => 0,
                    'updated_by' => request()->user()->id,
                    'id_usuario' => $idUsuario
                ]);

            $notificacionesCount = Notificaciones::with('creador')
                ->where('estado', '=', 0)
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                );
                

            if ($request->user()->can('pqrsf responder')) {
                $notificacionesCount->orWhere('id_rol', 1)
                    ->orWhereNull('id_usuario');
            } else {
                $notificacionesCount->where('id_usuario', request()->user()->id);
            }

            $notificacionesCountTotal = Notificaciones::with('creador')
                ->where('estado', '!=', 2)
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                );
            if ($request->user()->can('pqrsf responder')) {
                $notificacionesCountTotal->orWhere('id_rol', 1)
                    ->orWhereNull('id_usuario');
            } else {
                $notificacionesCountTotal->where('id_usuario', request()->user()->id);
            }

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $notificacion,
                'count' => $notificacionesCount->count(),
                'count_total' => $notificacionesCountTotal->count(),
                'message'=> 'Notificacion actualizada con exito!'
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
            'id' => 'required|exists:max.notificaciones,id',
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

            $notificacion = Notificaciones::where('id', $request->get('id'))
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                )
                ->first();

            return response()->json([
                'success'=>	true,
                'data' => $notificacion,
                'message'=> 'Notificacion encontrada con exito!'
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