<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Notificaciones;

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
            $notificaciones = Notificaciones::where('estado', 0)
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                )
                ->where('id_usuario', request()->user()->id)
                ->get();

            return response()->json([
                'success'=>	true,
                'data' => $notificaciones,
                'total' => count($notificaciones),
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

            $notificacion = Notificaciones::where('id', $request->get('id'))
                ->where('estado', 0)
                ->update([
                    'estado' => $request->get('estado'),
                    'updated_by' => request()->user()->id
                ]);

            $notificacion = Notificaciones::find($request->get('id'));
            
            Notificaciones::where('notificacion_type', $notificacion->notificacion_type) 
                ->where('mensaje', $notificacion->mensaje)
                ->where('estado', 0)
                ->update([
                    'estado' => $request->get('estado'),
                    'updated_by' => request()->user()->id
                ]);

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $notificacion,
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
}