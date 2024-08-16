<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Carbon\Carbon;
use App\Helpers\Extracto;
use App\Helpers\Documento;
use Illuminate\Http\Request;
use App\Imports\RecibosCajaImport;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Portafolio\ConRecibos;
use App\Models\Portafolio\PlanCuentas;
use App\Models\Portafolio\Comprobantes;
use App\Models\Portafolio\CentroCostos;
use App\Models\Portafolio\FacFormasPago;
use App\Models\Sistema\ConRecibosImport;
use App\Models\Portafolio\ConReciboPagos;
use App\Models\Portafolio\DocumentosGeneral;
use App\Models\Portafolio\ConReciboDetalles;


class ImportadorRecibosController extends Controller
{
    use BegConsecutiveTrait;

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
        return view('pages.importador.recibos_caja.recibos_caja-view');
    }

    public function importar (Request $request)
    {
        $rules = [
            'file_import_recibos' => 'required|mimes:xlsx'
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
            $file = $request->file('file_import_recibos');

            ConRecibosImport::truncate();

            $import = new RecibosCajaImport();
            $import->import($file);

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Recibos creados con exito!'
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

            return response()->json([
                'success'=>	false,
                'data' => $e->failures(),
                'message'=> 'Error al actualizar precio de productos'
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

        $recibos = ConRecibosImport::orderBy($columnName,$columnSortOrder);

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
            'url' => 'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/import/importador_recibos.xlsx',
            'message'=> 'Url generada con exito'
        ]);
    }

    public function cargar (Request $request)
    {
        $recibosImport = ConRecibosImport::where('estado', 0)
            ->get();

        try {
            DB::connection('max')->beginTransaction();

            $this->id_comprobante = Entorno::where('nombre', 'id_comprobante_recibos_caja')->first()->valor;
            $id_cuenta_ingreso = Entorno::where('nombre', 'id_cuenta_ingreso_recibos_caja')->first()->valor;
            $id_cuenta_anticipos = Entorno::where('nombre', 'id_cuenta_anticipos')->first()->valor;
            $comprobante = Comprobantes::where('id', $this->id_comprobante)->first();

            if ($recibosImport->count()) {
                foreach ($recibosImport as $reciboImport) {
                    
                    $valorDisponible = $reciboImport->pago;
                    $this->fechaManual = $reciboImport->fecha_manual;
                    $this->consecutivo = $this->getNextConsecutive($comprobante->id, $this->fechaManual);

                    $recibo = $this->createFacturaRecibo($reciboImport);
                    $cecos = CentroCostos::first();
                    //GUARDAR DETALLE & MOVIMIENTO CONTABLE RECIBOS

                    $extractos = (new Extracto(
                        $reciboImport->id_nit,
                        [3,7],
                        null,
                        $this->fechaManual
                    ))->actual()->get();

                    $documentoGeneral = new Documento(
                        $comprobante->id,
                        $recibo,
                        $this->fechaManual,
                        $this->consecutivo
                    );
                    //AGREGAR DEUDA
                    foreach ($extractos as $extracto) {
                        if ($valorDisponible <= 0) continue;
                                                
                        $cuentaPago = PlanCuentas::find($extracto->id_cuenta);
                        $saldoNuevo = $extracto->saldo - $valorDisponible;
                        $documentoReferencia = $extracto->documento_referencia ? $extracto->documento_referencia : $this->consecutivo;
                        
                        ConReciboDetalles::create([
                            'id_recibo' => $recibo->id,
                            'id_cuenta' => $cuentaPago->id,
                            'id_nit' => $recibo->id_nit,
                            'fecha_manual' => $recibo->fecha_manual,
                            'documento_referencia' => $extracto->documento_referencia,
                            'consecutivo' => $recibo->consecutivo,
                            'concepto' => 'VALOR IMPORTADO DESDE RECIBOS',
                            'total_factura' => 0,
                            'total_abono' => $saldoNuevo < 0 ? $extracto->saldo : $valorDisponible,
                            'total_saldo' => $extracto->saldo,
                            'nuevo_saldo' => $saldoNuevo < 0 ? 0 : $saldoNuevo,
                            'total_anticipo' => 0,
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);

                        //AGREGAR MOVIMIENTO CONTABLE
                        $doc = new DocumentosGeneral([
                            "id_cuenta" => $cuentaPago->id,
                            "id_nit" => $cuentaPago->exige_nit ? $recibo->id_nit : null,
                            "id_centro_costos" => $cuentaPago->exige_centro_costos ?  $cecos->id : null,
                            "concepto" => $cuentaPago->exige_concepto ? 'IMPORTADO DESDE RECIBOS' : null,
                            "documento_referencia" => $cuentaPago->exige_documento_referencia ? $documentoReferencia : null,
                            "debito" => $saldoNuevo < 0 ? $extracto->saldo : $valorDisponible,
                            "credito" => $saldoNuevo < 0 ? $extracto->saldo : $valorDisponible,
                            "created_by" => request()->user()->id,
                            "updated_by" => request()->user()->id
                        ]);
                        $documentoGeneral->addRow($doc, $cuentaPago->naturaleza_ingresos);

                        if ($valorDisponible - $extracto->saldo > 0) {
                            $valorDisponible-= $extracto->saldo;
                        } else { 
                            $valorDisponible = 0;
                        }
                    }
                    //AGREGAR ANTICIPO
                    if ($valorDisponible > 0) {
                        $cuentaAnticipo = PlanCuentas::find($id_cuenta_anticipos);

                        ConReciboDetalles::create([
                            'id_recibo' => $recibo->id,
                            'id_cuenta' => $cuentaAnticipo->id,
                            'id_nit' => $recibo->id_nit,
                            'fecha_manual' => $recibo->fecha_manual,
                            'documento_referencia' => $recibo->consecutivo,
                            'consecutivo' => $recibo->consecutivo,
                            'concepto' => 'ANTICIPO IMPORTADO DESDE RECIBOS',
                            'total_factura' => 0,
                            'total_abono' => 0,
                            'total_saldo' => 0,
                            'nuevo_saldo' => 0,
                            'total_anticipo' => $valorDisponible,
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);

                        //AGREGAR MOVIMIENTO CONTABLE
                        $doc = new DocumentosGeneral([
                            "id_cuenta" => $cuentaAnticipo->id,
                            "id_nit" => $cuentaAnticipo->exige_nit ? $recibo->id_nit : null,
                            "id_centro_costos" => $cuentaAnticipo->exige_centro_costos ? $cecos->id : null,
                            "concepto" => $cuentaAnticipo->exige_concepto ? 'ANTICIPO IMPORTADO DESDE RECIBOS' : null,
                            "documento_referencia" => $cuentaAnticipo->exige_documento_referencia ? $documentoReferencia : null,
                            "debito" => $valorDisponible,
                            "credito" => $valorDisponible,
                            "created_by" => request()->user()->id,
                            "updated_by" => request()->user()->id
                        ]);
                        $documentoGeneral->addRow($doc, $cuentaAnticipo->naturaleza_ingresos);
                    }
                    //GREGAR PAGO
                    $formaPago = FacFormasPago::where('id_cuenta', $id_cuenta_ingreso)
                        ->with('cuenta.tipos_cuenta')
                        ->first();

                    $pagoRecibo = ConReciboPagos::create([
                        'id_recibo' => $recibo->id,
                        'id_forma_pago' => $formaPago->id,
                        'valor' => $reciboImport->pago,
                        'saldo' => 0,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);

                    $doc = new DocumentosGeneral([
                        'id_cuenta' => $formaPago->cuenta->id,
                        'id_nit' => $formaPago->cuenta->exige_nit ? $recibo->id_nit : null,
                        'id_centro_costos' => null,
                        'concepto' => $formaPago->cuenta->exige_concepto ? 'PAGO IMPORTADO DESDE RECIBOS' : null,
                        'documento_referencia' => $documentoReferencia,
                        'debito' => $reciboImport->pago,
                        'credito' => $reciboImport->pago,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);
        
                    $documentoGeneral->addRow($doc, $formaPago->cuenta->naturaleza_ventas);

                    $this->updateConsecutivo($this->id_comprobante, $this->consecutivo);

                    if (!$documentoGeneral->save()) {

                        DB::connection('max')->rollback();
                        return response()->json([
                            'success'=>	false,
                            'data' => [],
                            'message'=> $documentoGeneral->getErrors()
                        ], 422);
                    }
                }
            }

            ConRecibosImport::whereIn('estado', [0])->delete();

            DB::connection('max')->commit();

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
        $recibosErrores = ConRecibosImport::where('estado', 1)->count();
        $recibosBuenos = ConRecibosImport::where('estado', 0)->count();
        $recibosPagos = ConRecibosImport::where('estado', 0)->sum('pago');
        $recibosAnticipos = ConRecibosImport::where('estado', 0)->sum('anticipos');

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