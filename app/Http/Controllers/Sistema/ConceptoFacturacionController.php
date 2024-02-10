<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\ConceptoFacturacion;


class ConceptoFacturacionController extends Controller
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
            'id' => 'required|min:1|max:200|exists:max.concepto_facturacions,id',
            'nombre_concepto' => 'required|min:1|max:200|unique:max.concepto_facturacions,nombre_concepto',
            'id_cuenta_ingreso' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_interes' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_cobrar' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_iva' => 'nullable|exists:sam.plan_cuentas,id',
            'intereses' => 'nullable',
            'valor' => 'nullable',
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

            $conceptoFacturacion = ConceptoFacturacion::create([
                'nombre_concepto' => $request->get('nombre_concepto'),
                'id_cuenta_ingreso' => $request->get('id_cuenta_ingreso'),
                'id_cuenta_interes' => $request->get('id_cuenta_interes'),
                'id_cuenta_cobrar' => $request->get('id_cuenta_cobrar'),
                'id_cuenta_iva' => $request->get('id_cuenta_iva'),
                'intereses' => $request->get('intereses'),
                'valor' => $request->get('valor'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $conceptoFacturacion,
                'message'=> 'Concepto facturación creado con exito!'
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
            'nombre_concepto' => ['required','min:1','max:200',
                function($attribute, $value, $fail) use ($request) {
                    $conceptoOld = ConceptoFacturacion::find($request->get('id'));
                    if ($conceptoOld->nombre_concepto != $request->get('nombre_concepto')) {
                        $conceptoNew = ConceptoFacturacion::where('nombre_concepto', $request->get('nombre_concepto'));
                        if ($conceptoNew->count()) {
                            $fail("La nombre de concepto ".$value." ya existe.");
                        }
                    }
                }],
            'id_cuenta_ingreso' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_interes' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_cobrar' => 'nullable|exists:sam.plan_cuentas,id',
            'id_cuenta_iva' => 'nullable|exists:sam.plan_cuentas,id',
            'intereses' => 'nullable',
            'valor' => 'nullable',
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

            $conceptoFacturacion = ConceptoFacturacion::where('id', $request->get('id'))
                ->update([
                    'nombre_concepto' => $request->get('nombre_concepto'),
                    'id_cuenta_ingreso' => $request->get('id_cuenta_ingreso'),
                    'id_cuenta_interes' => $request->get('id_cuenta_interes'),
                    'id_cuenta_cobrar' => $request->get('id_cuenta_cobrar'),
                    'id_cuenta_iva' => $request->get('id_cuenta_iva'),
                    'intereses' => $request->get('intereses'),
                    'valor' => $request->get('valor'),
                    'updated_by' => request()->user()->id
                ]);

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $conceptoFacturacion,
                'message'=> 'Concepto facturación actualizado con exito!'
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
            'id' => 'required|min:1|max:200|exists:max.concepto_facturacions,id',
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

            ConceptoFacturacion::where('id', $request->get('id'))->delete();

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Concepto facturación eliminado con exito!'
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