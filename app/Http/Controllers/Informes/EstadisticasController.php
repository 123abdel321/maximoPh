<?php

namespace App\Http\Controllers\Informes;

use DB;
use Illuminate\Http\Request;
use App\Exports\EstadisticasExport;
use App\Events\PrivateMessageEvent;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessInformeEstadisticas;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Empresa\Empresa;
use App\Models\Portafolio\PlanCuentas;
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

    public function excel(Request $request)
    {
        try {
            $informeEstadisticas = InfEstadisticas::find($request->get('id'));

            if($informeEstadisticas && $informeEstadisticas->exporta_excel == 1) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => '',
                    'message'=> 'Actualmente se esta generando el excel de estadisticas'
                ]);
            }

            if($informeEstadisticas && $informeEstadisticas->exporta_excel == 2) {
                return response()->json([
                    'success'=>	true,
                    'url_file' => $informeEstadisticas->archivo_excel,
                    'message'=> ''
                ]);
            }

            $fileName = 'export/estadisticas_'.uniqid().'.xlsx';
            $url = $fileName;

            $informeEstadisticas->exporta_excel = 1;
            $informeEstadisticas->archivo_excel = 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url;
            $informeEstadisticas->save();

            (new EstadisticasExport($request->get('id')))->store($fileName, 'do_spaces', null, [
                'visibility' => 'public'
            ])->chain([
                event(new PrivateMessageEvent('informe-estadisticas-'.$request->user()['has_empresa'].'_'.$request->user()->id, [
                    'tipo' => 'exito',
                    'mensaje' => 'Excel de Estadisticas generado con exito!',
                    'titulo' => 'Excel generado',
                    'url_file' => 'porfaolioerpbucket.nyc3.digitaloceanspaces.com/'.$url,
                    'autoclose' => false
                ])),
                $informeEstadisticas->exporta_excel = 2,
                $informeEstadisticas->save(),
            ]);

            return response()->json([
                'success'=>	true,
                'url_file' => '',
                'message'=> 'Se le notificará cuando el informe haya finalizado'
            ]);
        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }
}