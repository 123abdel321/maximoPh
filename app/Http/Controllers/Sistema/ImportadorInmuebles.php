<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Carbon\Carbon;
use App\Helpers\Extracto;
use App\Helpers\Documento;
use App\Jobs\ProcessNotify;
use Illuminate\Http\Request;
use App\Jobs\ImportInmueblesJob;
use App\Events\PrivateMessageEvent;
use Illuminate\Support\Facades\Bus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Imports\InmueblesGeneralesImport;

//MODELS
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Entorno;
use App\Models\Sistema\Inmueble;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\InmueblesImport;

class ImportadorInmuebles extends Controller
{
    protected $messages = null;

    public function __construct()
	{
		$this->messages = [
            'required' => 'El campo :attribute es requerido.',
        ];
	}

	public function index ()
    {
        return view('pages.importador.inmuebles.inmuebles-view');
    }

    public function importar (Request $request)
    {
        $rules = [
            'file_import_inmuebles' => 'required|mimes:xlsx'
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
            $file = $request->file('file_import_inmuebles');
            $has_empresa = $request->user()['has_empresa'];
            $empresa = Empresa::where('token_db_maximo', $has_empresa)->first();
            $user_id = $request->user()->id;
            $filePath = $file->store('inmuebles');

            $actualizarValores = $request->has('actualizar_valores') ? true : false;

            InmueblesImport::truncate();

            Bus::chain([
                new ImportInmueblesJob($empresa, $actualizarValores, $filePath),
                new ProcessNotify('importador-inmuebles-'.$has_empresa.'_'.$user_id, [
                    'success'=>	true,
                    'accion' => 1,
                    'tipo' => 'exito',
                    'mensaje' => 'Archivo importado con exito!',
                    'titulo' => 'Inmuebles importados',
                    'autoclose' => true
                ])
            ])->catch(function (\Throwable $e) use ($user_id, $has_empresa) {
                event(new PrivateMessageEvent('importador-inmuebles-'.$has_empresa.'_'.$user_id, [
                    'success'=>	false,
                    'accion' => 0,
                    'tipo' => 'error',
                    'mensaje' => 'Error al importar el archivo: ' . $e->getMessage(),
                    'titulo' => 'Fallo en la importación',
                    'autoclose' => false
                ]));
            })->dispatch();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Inmuebles generales creados con exito!'
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

            return response()->json([
                'success'=>	false,
                'data' => $e->failures(),
                'message'=> 'Error al cargar inmuebles generales'
            ]);
        }
    }

    public function generate (Request $request)
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

        $recibos = InmueblesImport::orderBy('estado', 'DESC')
            ->orderBy('id', 'ASC');

        $recibosTotals = $recibos->get();

        $recibosPaginate = $recibos->skip($start)
            ->take($rowperpage);

        return response()->json([
            'success'=>	true,
            'draw' => $draw,
            'iTotalRecords' => $recibosTotals->count(),
            'iTotalDisplayRecords' => $recibosTotals->count(),
            'data' => $recibosPaginate->get(),
            'perPage' => $rowperpage,
            'message'=> 'Recibos generado con exito!'
        ]);
    }
    
    public function exportar (Request $request)
    {
        return response()->json([
            'success'=>	true,
            'url' => 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/import/importador_inmuebles.xlsx',
            'message'=> 'Url generada con exito'
        ]);
    }

    public function cargar (Request $request)
    {
        $inmueblesImport = InmueblesImport::with('inmueble.personas')
            ->where('estado', 0)
            ->get();

        try {
            //RECORREMOS CUOTAS EXTRAS & MULTAS
            foreach ($inmueblesImport as $inmuebleIm) {
                $inmueble = (object)['id' => null];
                //CREATE OR UPDATE INMUEBLE
                if ($inmuebleIm->id_inmueble) {
                    Inmueble::where('id', $inmuebleIm->id_inmueble)
                        ->update([
                            'area' => $inmuebleIm->area,
                            'coeficiente' => $inmuebleIm->coheficiente,
                            'id_zona' => $inmuebleIm->id_zona,
                            'id_concepto_facturacion' => $inmuebleIm->id_concepto_facturacion,
                            'valor_total_administracion' => $inmuebleIm->valor_administracion,
                        ]);
                    $inmueble = Inmueble::find($inmuebleIm->id_inmueble);
                } else {
                    
                    $inmueble = Inmueble::create([
                        'nombre' => $inmuebleIm->nombre_inmueble,
                        'area' => $inmuebleIm->area,
                        'coeficiente' => $inmuebleIm->coheficiente,
                        'id_zona' => $inmuebleIm->id_zona,
                        'id_concepto_facturacion' => $inmuebleIm->id_concepto_facturacion,
                        'valor_total_administracion' => $inmuebleIm->valor_administracion,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);
                }
                $inmueblesNitsExistentes = InmuebleNit::where('id_inmueble', $inmuebleIm->id_inmueble)->get();
                //CREATE OR UPDATE PROPIETARIO
                $porcentajeAdmin = $inmuebleIm->porcentaje_administracion ? $inmuebleIm->porcentaje_administracion : 100;
                $valorAdmin = $inmuebleIm->valor_administracion;
                if ($inmuebleIm->id_nit) {

                    if ($inmueble && is_object($inmueble) && $inmueble->id) {

                        InmuebleNit::where('id_inmueble', $inmueble->id)
                            ->where('id_nit', $inmuebleIm->id_nit)
                            ->updateOrCreate([
                                'id_nit' => $inmuebleIm->id_nit,
                                'id_inmueble' => $inmueble->id,
                                'porcentaje_administracion' => $porcentajeAdmin,
                                'valor_total' => $valorAdmin * ($porcentajeAdmin / 100),
                                'tipo' => $inmuebleIm->tipo,
                                'created_by' => request()->user()->id,
                                'updated_by' => request()->user()->id
                            ]);
                    } else {
                        return response()->json([
                            "success"=>false,
                            'data' => [],
                            "message"=>["Inmueble" => ["El inmueble no existe, registro No. ".$inmuebleIm->id]]
                        ], 422);
                    }
                } else if (count($inmueblesNitsExistentes)) {

                    foreach ($inmueblesNitsExistentes as $key => $inmuebleNit) {
                        InmuebleNit::where('id', $inmuebleNit->id)
                            ->update([
                                'porcentaje_administracion' => $porcentajeAdmin,
                                'valor_total' => $valorAdmin * ($porcentajeAdmin / 100),
                                'updated_by' => request()->user()->id
                            ]);
                    }
                }
                $inmuebleIm->estado = 5;
                $inmuebleIm->save();
            }

            InmueblesImport::truncate();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Inmuebles creados con exito!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function totales (Request $request)
    {
        $InmueblesErrores = InmueblesImport::where('estado', 1)->count();
        $InmueblesBuenos = InmueblesImport::where('estado', 0)->count();
        $InmueblesAdmin = InmueblesImport::where('estado', 0)->sum('valor_administracion');

        $data = [
            'errores' => $InmueblesErrores,
            'buenos' => $InmueblesBuenos,
            'valores' => $InmueblesAdmin,
        ];

        return response()->json([
            'success'=>	true,
            'data' => $data
        ]);
    }

}