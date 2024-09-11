<?php

namespace App\Http\Controllers\Informes;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeEstadisticas;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Empresa\Empresa;
use App\Models\Informes\InfEstadisticas;
use App\Models\Sistema\ConceptoFacturacion;
use App\Models\Informes\InfEstadisticaDetalle;


class EstadisticasController extends Controller
{
    public function index ()
    {
        return view('pages.informes.estadisticas.estadisticas-view');
    }

    public function generate(Request $request)
    {
        if (!$request->has('fecha_desde') && $request->get('fecha_desde')|| !$request->has('fecha_hasta') && $request->get('fecha_hasta')) {
			return response()->json([
                'success'=>	false,
                'data' => [],
                'message'=> 'Por favor ingresar un rango de fechas válido.'
            ]);
		}
        
        $empresa = Empresa::where('token_db_maximo', $request->user()['has_empresa'])->first();

        if($request->get('id_concepto_facturacion')) {
            $concepto = ConceptoFacturacion::find($request->get('id_concepto_facturacion'));
            $cuenta = PlanCuentas::find($request->get('id_cuenta_cobrar'));
            $request->merge(['id_cuenta' => $cuenta->id]);
        }

        $estadisticas = InfEstadisticas::where('id_empresa', $empresa->id)
            ->where('fecha_desde', $request->get('fecha_desde'))
            ->where('fecha_hasta', $request->get('fecha_hasta'))
            ->where('id_nit', $request->get('id_nit', null))
            ->where('id_zona', $request->get('id_zona', null))
            ->where('id_concepto_facturacion', $request->get('id_concepto_facturacion', null))
            ->where('agrupar', $request->get('agrupar', null))
            ->where('detalle', $request->get('detalle', null))
			->first();
        
        if($estadisticas) {
            InfEstadisticaDetalle::where('id_estadisticas', $estadisticas->id)->delete();
            $estadisticas->delete();
        }
        
        $data = $request->except(['columns']);

        ProcessInformeEstadisticas::dispatch($data, $request->user()->id, $empresa->id);

        return response()->json([
    		'success'=>	true,
    		'data' => '',
    		'message'=> 'Generando informe de estadisticas'
    	]);
    }

    public function show(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $estadistica = InfEstadisticas::where('id', $request->get('id'))->first();
		$informe = InfEstadisticaDetalle::where('id_estadisticas', $estadistica->id)
            ->with('nit', 'cuenta');
		$total = InfEstadisticaDetalle::where('id_estadisticas', $estadistica->id)->orderBy('id', 'desc')->first();

        $informeTotals = $informe->get();

        $informePaginate = $informe->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $informeTotals->count(),
            'iTotalDisplayRecords' => $informeTotals->count(),
            'data' => $informePaginate->get(),
            'perPage' => $rowperpage,
            'totales' => $total,
            'message'=> 'Estadisticas generadas con exito!'
        ]);
    }
}