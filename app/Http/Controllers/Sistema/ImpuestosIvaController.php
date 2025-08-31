<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS

class ImpuestosIvaController extends Controller
{
    protected $gastoData = [];

    public function index ()
    {
        return view('pages.informes.impuestos_iva.impuestos_iva-view');
    }

    public function read (Request $request)
    {
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

        $gasto = DB::connection('sam')->table('con_gasto_detalles AS GD')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN G.id_proveedor IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN G.id_proveedor IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                'N.razon_social',
                'G.fecha_manual',
                DB::raw("SUM(GD.total) AS total"),
                DB::raw("SUM(GD.iva_valor) AS iva_valor"),
                'GD.observacion',
                'GD.id_cuenta_gasto',
                'PC.cuenta',
                'PC.nombre AS nombre_cuenta',
                'CC.id AS id_cecos',
                'CC.codigo AS codigo_cecos',
                'CC.nombre AS nombre_cecos',
                'CO.codigo AS codigo_comprobante',
                'CO.nombre AS nombre_comprobante',
            )
            ->leftJoin('con_gastos AS G', 'GD.id_gasto', 'G.id')
            ->leftJoin('nits AS N', 'G.id_proveedor', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'GD.id_cuenta_gasto', 'PC.id')
            ->leftJoin('centro_costos AS CC', 'G.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'G.id_comprobante', 'CO.id')
            ->groupByRaw($request->get('agrupar') == 'id_cuenta' ? 'id_cuenta_gasto' : 'id_nit')
            ->havingRaw('iva_valor != 0')
            ->take($rowperpage);

        if ($request->get('id_nit')) {
            $gasto->where('N.id', $request->get('id_nit'));
        }

        if ($request->get('id_cecos')) {
            $gasto->where('CC.id', $request->get('id_cecos'));
        }

        if ($request->get('id_cuenta')) {
            $gasto->where('GD.id_cuenta_gasto', $request->get('id_cuenta'));
        }

        if ($request->get('fecha_desde')) {
            $gasto->where('G.fecha_manual', '>=', $request->get('fecha_desde'));
        }

        if ($request->get('fecha_hasta')) {
            $gasto->where('G.fecha_manual', '<=', $request->get('fecha_hasta'));
        }

        $dataGastos = $gasto->get();
        if ($request->get('detallar') == 'si') {
            $this->generarGastoDetalles($dataGastos, $request);
        } else {
            $this->generarGastoDetalles($dataGastos, $request, false);
        }

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $gasto->count(),
            'iTotalDisplayRecords' => $gasto->count(),
            'data' => $this->gastoData,
            'perPage' => $rowperpage,
            'message'=> 'Impuestos iva cargados con exito!'
        ]);
    }

    private function generarGastoDetalles($dataGastos, $request, $detallar = true)
    {
        foreach ($dataGastos as $value) {
            
            $this->gastoData[] = [
                'id_nit' => $value->id_nit,
                'numero_documento' => $value->numero_documento,
                'nombre_nit' => $value->nombre_nit,
                'razon_social' => $value->razon_social,
                'fecha_manual' => $value->fecha_manual,
                'iva_valor' => $value->iva_valor,
                'gasto_valor' => $value->total,
                'observacion' => $value->observacion,
                'id_cuenta_gasto' => $value->id_cuenta_gasto,
                'cuenta' => $value->cuenta,
                'nombre_cuenta' => $value->nombre_cuenta,
                'id_cecos' => $value->id_cecos,
                'codigo_cecos' => $value->codigo_cecos,
                'nombre_cecos' => $value->nombre_cecos,
                'codigo_comprobante' => $value->codigo_comprobante,
                'nombre_comprobante' => $value->nombre_comprobante,
                "detalle" => $detallar ? false : true
            ];
            
            if ($detallar) {

                $gastoDetalle = $this->getDetalleGastos($request->get('agrupar'), $value);
                $gastoDetalle = (object)$gastoDetalle;
                // dd($gastoDetalle);
                foreach ($gastoDetalle as $data) {
                    $this->gastoData[] = [
                        'id_nit' => $data->id_nit,
                        'numero_documento' => $data->numero_documento,
                        'nombre_nit' => $data->nombre_nit,
                        'razon_social' => $data->razon_social,
                        'fecha_manual' => $data->fecha_manual,
                        'iva_valor' => $data->iva_valor,
                        'gasto_valor' => $data->total,
                        'observacion' => $data->observacion,
                        'id_cuenta_gasto' => $data->id_cuenta_gasto,
                        'cuenta' => $data->cuenta,
                        'nombre_cuenta' => $data->nombre_cuenta,
                        'id_cecos' => $data->id_cecos,
                        'codigo_cecos' => $data->codigo_cecos,
                        'nombre_cecos' => $data->nombre_cecos,
                        'codigo_comprobante' => $data->codigo_comprobante,
                        'nombre_comprobante' => $data->nombre_comprobante,
                        "detalle" => true
                    ];
                }
            }
        }
    }

    private function getDetalleGastos($agrupar, $data)
    {
        $detalle = DB::connection('sam')->table('con_gasto_detalles AS GD')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN G.id_proveedor IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN G.id_proveedor IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                'N.razon_social',
                'G.fecha_manual',
                'GD.total',
                'GD.iva_valor',
                'GD.observacion',
                'GD.id_cuenta_gasto',
                'PC.cuenta',
                'PC.nombre AS nombre_cuenta',
                'CC.id AS id_cecos',
                'CC.codigo AS codigo_cecos',
                'CC.nombre AS nombre_cecos',
                'CO.codigo AS codigo_comprobante',
                'CO.nombre AS nombre_comprobante',
            )
            ->leftJoin('con_gastos AS G', 'GD.id_gasto', 'G.id')
            ->leftJoin('nits AS N', 'G.id_proveedor', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'GD.id_cuenta_gasto', 'PC.id')
            ->leftJoin('centro_costos AS CC', 'G.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'G.id_comprobante', 'CO.id')
            ->havingRaw('iva_valor != 0');

        if ($agrupar == 'id_cuenta') {
            $detalle->where('GD.id_cuenta_gasto', $data->id_cuenta_gasto);
        } else {
            $detalle->where('N.id', $data->id_nit);
        }

        return $detalle->get();
}

}