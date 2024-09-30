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
            $notificaciones = Notificaciones::orderByRaw("id DESC, estado ASC")
                ->with('creador')
                ->where('estado', '!=', 2)
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                );

            $usuario_empresa = UsuarioEmpresa::where('id_empresa', $request->user()['id_empresa'])
                ->where('id_usuario', $request->user()['id'])
                ->first();

            if ($request->user()->can('pqrsf responder')) {
                $notificaciones->where('notificacion_type', '!=', 11)
                    ->orWhere('id_rol', 1)
                    ->orWhereNull('id_usuario');

            } else {
                $notificaciones->where('id_usuario', request()->user()->id);
            }

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
                $notificacionesCount->where('notificacion_type', '!=', 11)
                    ->orWhere('id_rol', 1)
                    ->orWhereNull('id_usuario');
            } else {
                $notificacionesCount->where('id_usuario', request()->user()->id);
            }

            return response()->json([
                'success'=>	true,
                'data' => $notificaciones->get(),
                'total' => $notificacionesCount->count(),
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