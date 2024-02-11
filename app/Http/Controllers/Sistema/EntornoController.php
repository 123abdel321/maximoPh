<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Entorno;

class EntornoController extends Controller
{
    public function update (Request $request)
    {
        try {
            DB::connection('max')->beginTransaction();

            $variablesEntorno = [
                'id_comprobante_ventas',
                'id_comprobante_gastos',
                'id_comprobante_recibos_caja',
                'id_comprobante_pagos',
                'id_cuenta_intereses',
                'id_cuenta_descuento',
                'id_cuenta_ingreso_recibos_caja',
                'id_cuenta_egreso_pagos',
                'id_cuenta_ingreso_pasarela',
                'area_total_m2',
                'numero_total_unidades',
                'valor_total_presupuesto_year_actual',
                'validacion_estricta_area',
                'dia_limite_pago_sin_interes',
                'dia_limite_descuento_pronto_pago',
                'porcentaje_descuento_pronto_pago',
                'porcentaje_intereses_mora',
                'editar_valor_admon_inmueble',
                'periodo_facturacion'
            ];

            foreach ($variablesEntorno as $variable) {
                Entorno::where('nombre', $variable)->update([
                    'valor' => $request->get($variable)
                ]);
            }

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Entorno actualizado con exito!'
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