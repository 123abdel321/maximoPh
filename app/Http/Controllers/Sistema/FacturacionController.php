<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\PortafolioERP\Extracto;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Validator;
use App\Helpers\PortafolioERP\FacturacionERP;
use App\Helpers\PortafolioERP\EliminarFacturas;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Empresa\Empresa;
use App\Models\Portafolio\Nits;
use App\Models\Sistema\Inmueble;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\Facturacion;
use App\Models\Sistema\CuotasMultas;
use App\Models\Sistema\FacturacionDetalle;

class FacturacionController extends Controller
{
    public function index ()
    {
        $totalInmuebles = Inmueble::count();
        $areaM2Total = Inmueble::sum('area');
        $coeficienteTotal = Inmueble::sum('coeficiente');
        $valorRegistroPresupuesto = InmuebleNit::sum('valor_total');

        $data = [
            'numero_total_unidades' => Entorno::where('nombre', 'numero_total_unidades')->first()->valor,
            'numero_registro_unidades' => $totalInmuebles,
            'area_total_m2' => Entorno::where('nombre', 'area_total_m2')->first()->valor,
            'area_registro_m2' => $areaM2Total,
            'valor_total_presupuesto' => Entorno::where('nombre', 'valor_total_presupuesto_year_actual')->first()->valor,
            'valor_registro_presupuesto' => $valorRegistroPresupuesto,
            'valor_registro_coeficiente' => intval($coeficienteTotal * 100),
        ];

        return view('pages.operaciones.facturacion.facturacion-view', $data);
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
            $search = $request->get('search');

            $columnIndex = $columnIndex_arr[0]['column']; // Column index
            $columnName = $columnName_arr[$columnIndex]['data']; // Column name
            $columnSortOrder = $order_arr[0]['dir']; // asc or desc

            $empresa = Empresa::where('token_db_maximo', $request->user()['has_empresa'])->first();
            
            $nitSsearch = $search ? $this->nitsSearch($search) : [];
            $query = $this->inmueblesNitsQuery($empresa, $search, $nitSsearch);
            $query->unionAll($this->cuotasMultasQuery($empresa, $search, $nitSsearch));

            $facturacion = DB::connection('max')
                ->table(DB::raw("({$query->toSql()}) AS facturaciondata"))
                ->mergeBindings($query)
                ->select(
                    'nombre_inmueble',
                    'area_inmueble',
                    'id_nit',
                    'tipo',
                    'porcentaje_administracion',
                    'valor_total',
                    'nombre_concepto',
                    'tipo_factura'
                );

            $facturacionTotals = $facturacion->get();

            $facturacionPaginate = $facturacion->skip($start)
                ->take($rowperpage);

            $dataTotals = (object)[
                'nombre_inmueble' => 'TOTAL FACTURA',
                'area_inmueble' => '',
                'id_nit' => '',
                'tipo' => '',
                'porcentaje_administracion' => '',
                'valor_total' => $facturacionTotals->sum('valor_total'),
                'nombre_concepto' => '',
                'tipo_factura' => 2,
            ];

            $dataFactura = $facturacionPaginate->get()->toArray();
            $dataFactura = $this->asignarNombreNit($dataFactura);
            
            array_push($dataFactura, $dataTotals);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $facturacionTotals->count(),
                'iTotalDisplayRecords' => $facturacionTotals->count(),
                'data' => $dataFactura,
                'perPage' => $rowperpage,
                'message'=> 'Facturas generadas con exito!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function generar (Request $request)
    {
        try {
            DB::connection('max')->beginTransaction();

            $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
            $id_comprobante_ventas = Entorno::where('nombre', 'id_comprobante_ventas')->first()->valor;
            $nitsFacturacion = InmuebleNit::select('id_nit')->groupBy('id_nit')->get();

            (new EliminarFacturas(
                $periodo_facturacion
            ))->send(request()->user()->id_empresa);

            //ELIMINAMOS LAS FACTURACIONES EN LA MISMA FECHA
            Facturacion::where('fecha_manual', $periodo_facturacion)->delete();
            FacturacionDetalle::where('fecha_manual', $periodo_facturacion)->delete();
            
            //RECORREMOS NITS CON INMUEBLES
            foreach ($nitsFacturacion as $nit) {

                $factura = Facturacion::create([//CABEZA DE FACTURA
                    'id_comprobante' => $id_comprobante_ventas,
                    'id_nit' => $nit->id_nit,
                    'fecha_manual' => $periodo_facturacion,
                    'token_factura' => $this->generateTokenDocumento(),
                    'valor' => 0,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id,
                ]);

                $valor = 0;
                $cobrarInteses = [];
                $inicioMes = date('Y-m', strtotime($periodo_facturacion));
                $finmes = date('Y-m-t', strtotime($periodo_facturacion));

                $inmueblesFacturar = InmuebleNit::with('inmueble.concepto', 'inmueble.zona')//INMUEBLES DEL NIT
                    ->where('id_nit', $nit->id_nit)
                    ->get();

                $cuotasMultasFacturar = CuotasMultas::with('inmueble.zona', 'concepto')//CUOTAS Y MULTAS DEL NIT
                    ->where('id_nit', $nit->id_nit)
                    ->whereDate("fecha_inicio", '>=', $inicioMes.'-01')
                    ->whereDate("fecha_fin", '<=', $finmes)
                    ->get();

                $totalAnticipos = $this->totalAnticipos($factura->id_nit, request()->user()->id_empresa);
                $totalInmuebles = 0;

                //RECORREMOS INMUEBLES DEL NIT
                foreach ($inmueblesFacturar as $inmuebleFactura) {
                    $cxcIntereses = $inmuebleFactura->inmueble->concepto->id_cuenta_cobrar;
                    if (count($inmueblesFacturar) > 1) $totalInmuebles++;
                    if ($inmuebleFactura->inmueble->concepto->intereses && !in_array($cxcIntereses, $cobrarInteses)) {
                        array_push($cobrarInteses, $cxcIntereses);
                    }
                    
                    $inicioMes = date('Y-m', strtotime($periodo_facturacion));
                    $valor+= $inmuebleFactura->valor_total;
                    
                    $this->generarFacturaInmueble($factura, $inmuebleFactura, $totalInmuebles);
                    if ($totalAnticipos > 0) {
                        $totalAnticipos = $this->generarFacturaAnticipos($factura, $inmuebleFactura, $totalInmuebles, $totalAnticipos);
                    }
                }
                //RECORREMOS CUOTAS Y MULTAS
                foreach ($cuotasMultasFacturar as $cuotaMultaFactura) {
                    $this->generarFacturaCuotaMulta($factura, $cuotaMultaFactura);
                }

                if (count($cobrarInteses)) {//COBRAR INTERESES
                    $valor+= $this->generarFacturaInmuebleIntereses($factura, $inmueblesFacturar[0], request()->user()->id_empresa, $cobrarInteses);
                    $factura->valor = $valor;
                    $factura->save();
                }
            }

            Entorno::where('nombre', 'periodo_facturacion')
                ->update([
                    'valor' => date("Y-m-d", strtotime("+1 month", strtotime($periodo_facturacion)))
                ]);

            (new FacturacionERP(
                $periodo_facturacion
            ))->send(request()->user()->id_empresa);

            DB::connection('max')->commit();

            return response()->json([
                "success"=>true,
                'data' => [],
                "message"=>'Facturación creada con exito'
            ], 200);

        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    private function generarFacturaCuotaMulta(Facturacion $factura, CuotasMultas $cuotaMultaFactura)
    {
        $id_comprobante_ventas = Entorno::where('nombre', 'id_comprobante_ventas')->first()->valor;
        $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $inicioMes = date('Y-m', strtotime($periodo_facturacion));
        
        $facturaDetalle = FacturacionDetalle::create([
            'id_factura' => $factura->id,
            'id_nit' => $cuotaMultaFactura->id_nit,
            'id_cuenta_por_cobrar' => $cuotaMultaFactura->concepto->id_cuenta_cobrar,
            'id_cuenta_ingreso' => $cuotaMultaFactura->concepto->id_cuenta_ingreso,
            'id_comprobante' => $id_comprobante_ventas,
            'id_centro_costos' => $cuotaMultaFactura->inmueble->zona->id_centro_costos,
            'fecha_manual' => $periodo_facturacion,
            'documento_referencia' => $inicioMes,
            'valor' => $cuotaMultaFactura->valor_total,
            'concepto' => $cuotaMultaFactura->concepto->nombre_concepto.' '.$cuotaMultaFactura->observacion,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id,
        ]);
    }

    private function generarFacturaInmueble(Facturacion $factura, InmuebleNit $inmuebleFactura, $totalInmuebles)
    {
        $id_comprobante_ventas = Entorno::where('nombre', 'id_comprobante_ventas')->first()->valor;
        $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $inicioMes = date('Y-m', strtotime($periodo_facturacion));
        $documentoReferenciaNumeroInmuebles = $totalInmuebles ? '_'.$totalInmuebles : '';

        $facturaDetalle = FacturacionDetalle::create([
            'id_factura' => $factura->id,
            'id_nit' => $inmuebleFactura->id_nit,
            'id_cuenta_por_cobrar' => $inmuebleFactura->inmueble->concepto->id_cuenta_cobrar,
            'id_cuenta_ingreso' => $inmuebleFactura->inmueble->concepto->id_cuenta_ingreso,
            'id_comprobante' => $id_comprobante_ventas,
            'id_centro_costos' => $inmuebleFactura->inmueble->zona->id_centro_costos,
            'fecha_manual' => $periodo_facturacion,
            'documento_referencia' => $inicioMes.$documentoReferenciaNumeroInmuebles,
            'valor' => $inmuebleFactura->valor_total,
            'concepto' => $inmuebleFactura->inmueble->concepto->nombre_concepto.' '.$inmuebleFactura->inmueble->nombre,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id,
        ]);
    }

    private function generarFacturaAnticipos(Facturacion $factura, InmuebleNit $inmuebleFactura, $totalInmuebles, $totalAnticipos)
    {
        $totalAnticipar = 0;
        if ($totalAnticipos >= $inmuebleFactura->valor_total) {
            $totalAnticipar = $inmuebleFactura->valor_total;
            $totalAnticipos-= $inmuebleFactura->valor_total;
        } else {
            $totalAnticipar = $totalAnticipos;
            $totalAnticipos = 0;
        }

        $id_comprobante_notas = Entorno::where('nombre', 'id_comprobante_notas')->first()->valor;
        $id_cuenta_anticipos = Entorno::where('nombre', 'id_cuenta_anticipos')->first()->valor;
        $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $inicioMes = date('Y-m', strtotime($periodo_facturacion));
        $documentoReferenciaNumeroInmuebles = $totalInmuebles ? '_'.$totalInmuebles : '';

        $facturaDetalle = FacturacionDetalle::create([
            'id_factura' => $factura->id,
            'id_nit' => $inmuebleFactura->id_nit,
            'id_cuenta_por_cobrar' => $id_cuenta_anticipos,
            'id_cuenta_ingreso' => $inmuebleFactura->inmueble->concepto->id_cuenta_cobrar,
            'id_comprobante' => $id_comprobante_notas,
            'id_centro_costos' => $inmuebleFactura->inmueble->zona->id_centro_costos,
            'fecha_manual' => $periodo_facturacion,
            'documento_referencia' => $inicioMes.$documentoReferenciaNumeroInmuebles,
            'valor' => $totalAnticipar,
            'concepto' => 'CRUCE ANTICIPOS '.$inmuebleFactura->inmueble->concepto->nombre_concepto.' '.$inmuebleFactura->inmueble->nombre,
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id,
        ]);

        return $totalAnticipos;
    }
    
    private function generarFacturaInmuebleIntereses(Facturacion $factura, InmuebleNit $inmuebleFactura, $id_empresa, $cobrarInteses)
    {
        $id_cuenta_intereses = Entorno::where('nombre', 'id_cuenta_intereses')->first()->valor;
        
        if (!$id_cuenta_intereses) return;
        
        $response = (new Extracto(//TRAER CUENTAS POR COBRAR
            $factura->id_nit,
            [3,8],
        ))->send($id_empresa);

        if ($response['status'] > 299) {//VALIDAR ERRORES PORTAFOLIO
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> $response['message']
            ], 422);
        }

        $extractos = $response['response']->data;

        //VALIDAMOS QUE TENGA CUENTAS POR COBRAR
        if (!count($extractos)) return;
        
        foreach ($extractos as $extracto) {
            $extracto = (object)$extracto;
            
            if (!in_array($extracto->id_cuenta, $cobrarInteses)) continue;

            $saldo = floatval($extracto->saldo);

            $porcentaje_intereses_mora = Entorno::where('nombre', 'porcentaje_intereses_mora')->first()->valor;
            $id_comprobante_ventas = Entorno::where('nombre', 'id_comprobante_ventas')->first()->valor;
            $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
            $id_cuenta_ingreso = Entorno::where('nombre', 'id_cuenta_ingreso')->first()->valor;
            
            $inicioMes = date('Y-m', strtotime($periodo_facturacion));
            $valorTotal = $saldo * ($porcentaje_intereses_mora / 100);

            //DEFINIR CONCEPTO DE INTERESES
            $concepto = $extracto->concepto;
            $validateConcepto = explode('INTERESES ', $concepto );
            if (count($validateConcepto) > 1) $concepto = explode(' -', $validateConcepto[1])[0];

            $facturaDetalle = FacturacionDetalle::create([
                'id_factura' => $factura->id,
                'id_nit' => $factura->id_nit,
                'id_cuenta_por_cobrar' => $id_cuenta_intereses,
                'id_cuenta_ingreso' => $id_cuenta_ingreso,
                'id_comprobante' => $id_comprobante_ventas,
                'id_centro_costos' => $inmuebleFactura->inmueble->zona->id_centro_costos,
                'fecha_manual' => $periodo_facturacion,
                'documento_referencia' => $inicioMes,
                'valor' => $valorTotal,
                'concepto' => 'INTERESES '.$concepto.' - '.$extracto->fecha_manual.' - %'.$porcentaje_intereses_mora.' - BASE: '.$saldo,
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id,
            ]);

            FacturacionDetalle::where('concepto', $extracto->concepto)
                ->where('id_nit', $extracto->id_nit)
                ->where('fecha_manual', $extracto->fecha_manual)
                ->update([
                    'saldo' => $saldo
                ]);
        }

        return $valorTotal;
    }

    private function inmueblesNitsQuery($empresa, $search, $nitSsearch)
    {
        return DB::connection('max')->table('inmueble_nits AS INMN')
            ->select(
                DB::raw("CONCAT(INM.nombre, ' - ', Z.nombre) AS nombre_inmueble"),
                "INM.area AS area_inmueble",
                "INMN.id_nit",
                "INMN.tipo",
                "INMN.porcentaje_administracion",
                "INMN.valor_total",
                "CF.nombre_concepto",
                DB::raw("0 AS tipo_factura")
            )
            ->leftJoin('inmuebles AS INM', 'INMN.id_inmueble', 'INM.id')
            ->leftJoin('zonas AS Z', 'INM.id_zona', 'Z.id')
            ->leftJoin('concepto_facturacions AS CF', 'INM.id_concepto_facturacion', 'CF.id')
            ->when(isset($search), function ($query) use($search, $nitSsearch) {
                $query->where('INM.nombre', 'LIKE', '%'.$search.'%')
                    ->orWhere('Z.nombre', 'LIKE', '%'.$search.'%')
                    ->orWhere('CF.nombre_concepto', 'LIKE', '%'.$search.'%');
            })
            ->when(count($nitSsearch), function ($query) use($nitSsearch) {
                $query->orWhereIn('INMN.id_nit', $nitSsearch);
            });
    }

    private function cuotasMultasQuery($empresa, $search, $nitSsearch)
    {
        $periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $inicioMes = date('Y-m', strtotime($periodo_facturacion));
        $finmes = date('Y-m-t', strtotime($periodo_facturacion));

        return DB::connection('max')->table('cuotas_multas AS CM')
            ->select(
                DB::raw("CONCAT(INM.nombre, ' - ', Z.nombre) AS nombre_inmueble"),
                "INM.area AS area_inmueble",
                "CM.id_nit",
                "inmueble_nits.tipo",
                "inmueble_nits.porcentaje_administracion",
                "CM.valor_total",
                "CF.nombre_concepto",
                DB::raw("1 AS tipo_factura")
            )
            ->leftJoin('inmuebles AS INM', 'CM.id_inmueble', 'INM.id')
            ->leftJoin('zonas AS Z', 'INM.id_zona', 'Z.id')
            ->leftJoin('inmueble_nits',function ($join) {
                $join->on('CM.id_inmueble', '=', 'inmueble_nits.id_inmueble')
                    ->on('inmueble_nits.id_nit', '=', 'CM.id_nit');
            })
            ->leftJoin('concepto_facturacions AS CF', 'CM.id_concepto_facturacion', 'CF.id')
            ->when(isset($search), function ($query) use($search) {
                $query->where('INM.nombre', 'LIKE', '%'.$search.'%')
                    ->orWhere('Z.nombre', 'LIKE', '%'.$search.'%')
                    ->orWhere('CF.nombre_concepto', 'LIKE', '%'.$search.'%');
            })
            ->when(count($nitSsearch), function ($query) use($nitSsearch) {
                $query->orWhereIn('CM.id_nit', $nitSsearch);
            })
            ->whereDate("CM.fecha_inicio", '>=', $inicioMes.'-01')
            ->whereDate("CM.fecha_fin", '<=', $finmes);
    }

    private function asignarNombreNit($dataFacturas)
    {
        foreach ($dataFacturas as $dataFactura) {
            $nit = Nits::find($dataFactura->id_nit);
            $dataFactura->id_nit = $nit->numero_documento.' - '.$nit->nombre_completo;
        }
        return $dataFacturas;
    }

    private function nitsSearch($search)
    {
        $data = [];
        $nits = DB::connection('sam')->table('nits')->select('id')
            ->where('razon_social', 'LIKE', '%'.$search.'%')
            ->orWhere('numero_documento', 'LIKE', '%'.$search.'%')
            ->orWhere('primer_nombre', 'LIKE', '%'.$search.'%')
            ->orWhere('primer_apellido', 'LIKE', '%'.$search.'%')
            ->get()->toArray();

        if (count($nits)) {
            foreach ($nits as $nit) {
                $data[] = $nit->id;
            }
        }

        return $data;        
    }

    private function totalAnticipos($id_nit, $id_empresa)
    {
        $extractos = (new Extracto(//TRAER CUENTAS POR PAGAR
            $id_nit,
            4,
        ))->send($id_empresa);

        if ($extractos['status'] > 299) {//VALIDAR ERRORES PORTAFOLIO
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=> $extractos['message']
            ], 422);
        }

        $extractos = $extractos['response']->data;

        //VALIDAMOS QUE TENGA CUENTAS POR COBRAR
        if (!count($extractos)) return 0;

        $totalAnticipos = 0;
        
        foreach ($extractos as $extracto) {
            $extracto = (object)$extracto;
            $totalAnticipos+= floatval($extracto->saldo);
        }

        return $totalAnticipos;
    }

    private function generateTokenDocumento()
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 64; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}