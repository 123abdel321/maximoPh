<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Pqrsf;

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

    public function create (Request $request)
    {
        $rules = [
            'id_nit' => 'required|exists:sam.nits,id',
            'id_inmueble' => 'required|min:1|max:200|exists:max.inmuebles,id',
            'tipo' => 'required',
            'asunto' => 'nullable',
            'descripcion' => 'nullable',
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
                'id_nit' => $request->get('id_nit'),
                'id_inmueble' => $request->get('id_inmueble'),
                'tipo' => $request->get('tipo'),
                'asunto' => $request->get('asunto'),
                'descripcion' => $request->get('descripcion'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

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
}