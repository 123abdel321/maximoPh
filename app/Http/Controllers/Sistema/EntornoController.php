<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Sistema\ConceptoFacturacion;

class EntornoController extends Controller
{

    public function index(Request $request)
    {
        $data = [
            'variables_entorno' => Entorno::with(
                'concepto_facturacion', 'nit', 'formas_pago'
            )->get()
        ];
        
        return view('pages.configuracion.entorno.entorno-view', $data);
    }

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
                'id_concepto_pago_none',
                'id_nit_por_defecto',
                'area_total_m2',
                'numero_total_unidades',
                'valor_total_presupuesto_year_actual',
                'validacion_estricta',
                'causacion_mensual_rapida',
                'dia_limite_pago_sin_interes',
                'dia_limite_descuento_pronto_pago',
                'porcentaje_descuento_pronto_pago',
                'porcentaje_intereses_mora',
                'editar_valor_admon_inmueble',
                'editar_coheficiente_admon_inmueble',
                'periodo_facturacion',
                'redondeo_intereses',
                'factura_texto1',
                'factura_texto2',
                'dias_pronto_pago',
                'descuento_pago_parcial',
                'recausar_meses',
                'documento_referencia_agrupado',
                'placetopay_url',
                'placetopay_login',
                'placetopay_trankey',
                'placetopay_forma_pago',
            ];

            foreach ($variablesEntorno as $variable) {
                if ($request->get($variable) || $request->get($variable) == '0') {
                    Entorno::updateOrCreate(
                        [ 'nombre' => $variable ],
                        [ 'valor' => $request->get($variable) ]
                    );
                }
                if ($variable == 'dias_pronto_pago') {
                    ConceptoFacturacion::whereNotNull('id')
                        ->update(['dias_pronto_pago' => $request->get($variable)]);
                }
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