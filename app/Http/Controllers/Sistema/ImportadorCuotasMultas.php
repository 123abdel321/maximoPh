<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Carbon\Carbon;
use App\Helpers\Extracto;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use App\Imports\CutasExtrasImport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Sistema\CuotasMultas;
use App\Models\Sistema\CuotasMultasImport;

class ImportadorCuotasMultas extends Controller
{

    protected $id_recibo = 0;
    protected $messages = null;
    protected $fechaManual = null;
    protected $consecutivo = null;
    protected $id_comprobante = null;

    public function __construct()
	{
		$this->messages = [
            'required' => 'El campo :attribute es requerido.',
        ];
	}

	public function index ()
    {
        return view('pages.importador.cuotas_multas.cuotas_multas-view');
    }

    public function importar (Request $request)
    {
        $rules = [
            'file_import_cuotas_multas' => 'required|mimes:xlsx'
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
            $file = $request->file('file_import_cuotas_multas');

            CuotasMultasImport::truncate();

            $import = new CutasExtrasImport();
            $import->import($file);

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Cuotas extras & multas creados con exito!'
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

            return response()->json([
                'success'=>	false,
                'data' => $e->failures(),
                'message'=> 'Error al cargar cuotas extras & multas'
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

        $recibos = CuotasMultasImport::orderBy($columnName,$columnSortOrder)
            ->with('concepto');

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
            'url' => 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/import/importador_cuotas_multas.xlsx',
            'message'=> 'Url generada con exito'
        ]);
    }

    public function cargar (Request $request)
    {
        $cuotasMultas = CuotasMultasImport::where('estado', 0)
            ->get();

        try {
            //RECORREMOS CUOTAS EXTRAS & MULTAS
            foreach ($cuotasMultas as $cuota) {
                CuotasMultas::create([
                    'id_nit' => $cuota->id_nit,
                    'id_inmueble' => $cuota->id_inmueble,
                    'tipo_concepto' => 1,
                    'id_concepto_facturacion' => $cuota->id_concepto_facturacion,
                    'fecha_inicio' => $cuota->fecha_inicio,
                    'fecha_fin' => $cuota->fecha_fin,
                    'valor_total' => $cuota->valor_total,
                    'observacion' => '',
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id,
                ]);
            }

            CuotasMultasImport::truncate();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Recibos creados con exito!'
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

    public function totales (Request $request)
    {
        $recibosErrores = CuotasMultasImport::where('estado', 1)->count();
        $recibosBuenos = CuotasMultasImport::where('estado', 0)->count();
        $recibosPagos = CuotasMultasImport::where('estado', 0)->sum('pago');
        $recibosAnticipos = CuotasMultasImport::where('estado', 0)->sum('anticipos');

        $data = [
            'errores' => $recibosErrores,
            'buenos' => $recibosBuenos,
            'pagos' => $recibosPagos,
            'anticipos' => $recibosAnticipos
        ];

        return response()->json([
            'success'=>	true,
            'data' => $data
        ]);
    }

    private function createFacturaRecibo($reciboImport)
    {
        $recibo = ConRecibos::create([
            'id_nit' => $reciboImport->id_nit,
            'id_comprobante' => $this->id_comprobante,
            'fecha_manual' => $this->fechaManual,
            'consecutivo' => $this->consecutivo,
            'total_abono' => $reciboImport->pago,
            'total_anticipo' => $reciboImport->total_anticipo ? $reciboImport->total_anticipo : 0,
            'observacion' => 'CARGADO DESDE IMPORTADOR',
            'created_by' => request()->user()->id,
            'updated_by' => request()->user()->id
        ]);
        return $recibo;
    }

}