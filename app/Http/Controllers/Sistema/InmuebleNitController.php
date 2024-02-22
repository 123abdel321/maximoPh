<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Sistema\Inmueble;
use App\Models\Sistema\InmuebleNit;

class InmuebleNitController extends Controller
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
            $rowperpage = $request->get("length");

            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search');

            $columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc
            $searchValue = $search_arr['value']; // Search value

            $inmuebleNit = InmuebleNit::orderBy($columnName,$columnSortOrder)
                ->with('nit')
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                );

            if ($request->has('id_inmueble')) {
                $inmuebleNit->where('id_inmueble', $request->get('id_inmueble'));
            }

            $inmuebleNitTotals = $inmuebleNit->get();

            $inmuebleNitPaginate = $inmuebleNit->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $inmuebleNitTotals->count(),
                'iTotalDisplayRecords' => $inmuebleNitTotals->count(),
                'data' => $inmuebleNitPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Inmuebles nits generados con exito!'
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
            'id_nit' => 'required|exists:sam.nits,id',
            'id_inmueble' => 'required|exists:max.inmuebles,id',
            'porcentaje_administracion' => 'required|numeric|min:0|max:100',
            'tipo' => 'nullable',
            'paga_administracion' => 'nullable',
            'enviar_notificaciones_mail' => 'nullable',
            'enviar_notificaciones_fisica' => 'nullable'
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        $existePropietario = InmuebleNit::where('id_nit', $request->get('id_nit'))
            ->where('id_inmueble', $request->get('id_inmueble'));

        if ($existePropietario->count()) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> "El nit ya hace parte del inmueble."
            ], 422);
        }

        try {
            DB::connection('max')->beginTransaction();  

            $inmueble = Inmueble::find($request->get('id_inmueble'));
            $total = $inmueble->valor_total_administracion * ($request->get('porcentaje_administracion') / 100);

            $inmuebleNit = InmuebleNit::create([
                'id_nit' => $request->get('id_nit'),
                'id_inmueble' => $request->get('id_inmueble'),
                'porcentaje_administracion' => $request->get('porcentaje_administracion'),
                'valor_total' => round($total),
                'tipo' => $request->get('tipo'),
                'paga_administracion' => $request->get('paga_administracion'),
                'enviar_notificaciones_mail' => $request->get('enviar_notificaciones_mail'),
                'enviar_notificaciones_fisica' => $request->get('enviar_notificaciones_fisica'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $inmuebleNit,
                'message'=> 'Nit asignado al inmueble con exito!'
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
            'id' => 'required|exists:max.inmueble_nits,id',
            'id_nit' => ['required', 'exists:sam.nits,id',
                function($attribute, $value, $fail) use ($request) {
                    $nitOld = InmuebleNit::find($request->get('id'));
                    if ($nitOld->id_nit != $request->get('id_nit')) {
                        $existNitIntoInmueble = InmuebleNit::where('id_nit', $request->get('id_nit'))
                            ->where('id_inmueble', $request->get('id_inmueble'));
                        if ($existNitIntoInmueble->count()) {
                            $fail("El nit ya existe en el inmueble.");
                        }
                    } 
                }],
            'id_inmueble' => 'required|exists:max.inmuebles,id',
            'porcentaje_administracion' => 'required|numeric|min:0|max:100',
            'tipo' => 'nullable',
            'paga_administracion' => 'nullable',
            'enviar_notificaciones_mail' => 'nullable',
            'enviar_notificaciones_fisica' => 'nullable'
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

            $inmueble = Inmueble::find($request->get('id_inmueble'));
            $total = $inmueble->valor_total_administracion * ($request->get('porcentaje_administracion') / 100);

            $inmuebleNit = InmuebleNit::where('id', $request->get('id'))
                ->update ([
                    'id_nit' => $request->get('id_nit'),
                    'id_inmueble' => $request->get('id_inmueble'),
                    'porcentaje_administracion' => $request->get('porcentaje_administracion'),
                    'valor_total' => round($total),
                    'tipo' => $request->get('tipo'),
                    'paga_administracion' => $request->get('paga_administracion'),
                    'enviar_notificaciones_mail' => $request->get('enviar_notificaciones_mail'),
                    'enviar_notificaciones_fisica' => $request->get('enviar_notificaciones_fisica'),
                    'updated_by' => request()->user()->id
                ]);

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $inmuebleNit,
                'message'=> 'Nit editado dentro del inmueble con exito!'
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
            'id' => 'required|exists:max.inmueble_nits,id',
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

            InmuebleNit::where('id', $request->get('id'))->delete();

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Nit eliminado del inmueble con exito!'
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