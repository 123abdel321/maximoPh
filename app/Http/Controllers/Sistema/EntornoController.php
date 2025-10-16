<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Sistema\TerminosCondiciones;
use App\Models\Sistema\ConceptoFacturacion;

class EntornoController extends Controller
{

    public function index(Request $request)
    {
        $data = [
            'variables_entorno' => Entorno::with(
                'concepto_facturacion', 'nit', 'formas_pago', 'cuenta'
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
                'id_cuenta_ingreso_intereses',
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
                'validar_fecha_entrega_causacion',
                'detallar_facturas',
                'documento_referencia_agrupado',
                'placetopay_url',
                'placetopay_login',
                'placetopay_trankey',
                'placetopay_forma_pago',
                'terminos_condiciones',
                'aceptar_terminos',
                'firma_digital',
                'nombre_administrador',
                'id_cuenta_ingreso',
                'id_cuenta_anticipos'
            ];

            foreach ($variablesEntorno as $variable) {
                if ($variable == 'firma_digital') {
                    if ($request->get('firma_digital')) {
                        $base64Image = $request->get('firma_digital');
                        // 1. Extraer el tipo de imagen y decodificar
                        preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type);
                        $data = substr($base64Image, strpos($base64Image, ',') + 1);
    
                        $imageType = $type[1];
                        $data = base64_decode($data);
                        // 2. Generar un nombre único para la imagen
                        $finalPath = 'maximo/empresas/'.request()->user()->id_empresa.'/imagenes/'.uniqid().'.'.$imageType;
                        // 3. Guardar la imagen en DigitalOcean Spaces
                        Storage::disk('do_spaces')->put($finalPath, $data, 'public');
                        // 4. obtener la URL
                        $url = Storage::disk('do_spaces')->url($finalPath);
                        Entorno::updateOrCreate(
                            [ 'nombre' => $variable ],
                            [ 'valor' =>  $url ]
                        );
                    }
                    continue;
                }
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
                if ($variable == 'terminos_condiciones') {
                    $existe = TerminosCondiciones::where('content', $request->get($variable))->count();
                    if (!$existe) {
                        TerminosCondiciones::create([
                            'content' => $request->get($variable),
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);
                    }
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