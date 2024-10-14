<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Carbon\Carbon;
use App\Helpers\Extracto;
use App\Helpers\Documento;
use App\Jobs\ProcessNotify;
use Illuminate\Http\Request;
use App\Jobs\ImportRecibosJob;
use App\Imports\RecibosCajaImport;
use App\Events\PrivateMessageEvent;
use Illuminate\Support\Facades\Bus;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessImportarRecibos;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Entorno;
use App\Models\Sistema\Facturacion;
use App\Models\Portafolio\ConRecibos;
use App\Models\Portafolio\PlanCuentas;
use App\Models\Portafolio\Comprobantes;
use App\Models\Portafolio\CentroCostos;
use App\Models\Portafolio\FacFormasPago;
use App\Models\Sistema\ConRecibosImport;
use App\Models\Portafolio\ConReciboPagos;
use App\Models\Sistema\ConceptoFacturacion;
use App\Models\Portafolio\DocumentosGeneral;
use App\Models\Portafolio\ConReciboDetalles;


class ImportadorRecibosController extends Controller
{
    use BegConsecutiveTrait;

    protected $id_recibo = 0;
    protected $redondeo = null;
    protected $messages = null;
    protected $fechaManual = null;
    protected $consecutivo = null;
    protected $prontoPago = false;
    protected $id_comprobante = null;
    protected $facturasAnticipos = [];
    protected $extractosAgrupados = [];
    protected $descuentoParcial = false;

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
            $has_empresa = $request->user()['has_empresa'];
            $empresa = Empresa::where('token_db_maximo', $has_empresa)->first();

            $user_id = $request->user()->id;
            $id_informe = $request->get('id');

            $filePath = $file->store('recibos');
            ConRecibosImport::truncate();

            Bus::chain([
                new ImportRecibosJob($empresa, $filePath),
                new ProcessNotify('importador-recibos-'.$has_empresa.'_'.$user_id, [
                    'success'=>	true,
                    'accion' => 1,
                    'tipo' => 'exito',
                    'mensaje' => 'Archivo importado con exito!',
                    'titulo' => 'Recibos importados',
                    'autoclose' => false
                ])
            ])->catch(function (\Throwable $e) use ($user_id, $has_empresa) {
                event(new PrivateMessageEvent('importador-recibos-'.$has_empresa.'_'.$user_id, [
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
                'message'=> 'Importando recibos...'
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

            return response()->json([
                'success'=>	false,
                'data' => $e->failures(),
                'message'=> 'Error al importar recibos'
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

        $recibos = ConRecibosImport::orderBy('estado', 'DESC')
            ->orderBy('numero_documento', 'ASC');

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
        try {

            $has_empresa = $request->user()['has_empresa'];
            $empresa = Empresa::where('token_db_maximo', $has_empresa)->first();

            $user_id = $request->user()->id;
            $id_informe = $request->get('id');

            Bus::chain([
                new ProcessImportarRecibos($empresa, $user_id),
                function () use ($user_id, $has_empresa) {
                    event(new PrivateMessageEvent('importador-recibos-'.$has_empresa.'_'.$user_id, [
                        'success'=>	true,
                        'accion' => 2,
                        'tipo' => 'exito',
                        'mensaje' => 'Recibos importados con exito!',
                        'titulo' => 'Recibos importados',
                        'autoclose' => false
                    ]));
                }
            ])->catch(function (\Throwable $e) use ($user_id, $has_empresa) {
                
                event(new PrivateMessageEvent('importador-recibos-'.$has_empresa.'_'.$user_id, [
                    'success'=>	false,
                    'accion' => 0,
                    'tipo' => 'error',
                    'mensaje' => 'Error al importar recibos: ' . $e->getMessage(),
                    'titulo' => 'Fallo en la importación',
                    'autoclose' => false
                ]));
            })->dispatch();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Importando recibos...'
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {

            return response()->json([
                'success'=>	false,
                'data' => $e->failures(),
                'message'=> 'Error al actualizar pagos'
            ]);
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

    private function calcularTotalDescuento($facturaDescuento, $extracto, $totalPago)
    {
        if ($facturaDescuento && !$facturaDescuento->has_pronto_pago) {
            if ($totalPago + $facturaDescuento->descuento >= $extracto->saldo) {
                return $facturaDescuento->descuento;
            }
        }
        return 0;
    }

    private function getFacturaMes($id_nit, $inicioMes, $fechaManual)
    {
        $fechaManual = Carbon::parse($fechaManual)->format("Y-m-d");

        $facturas = DB::connection('max')->select("SELECT
                FA.id AS id_factura,
                FD.id AS id_factura_detalle,
                FA.pronto_pago AS has_pronto_pago,
                FD.id_concepto_facturacion,
                FD.id_cuenta_por_cobrar,
                CF.id_cuenta_gasto,
                CF.nombre_concepto,
                CF.porcentaje_pronto_pago,
                FD.documento_referencia,
                SUM(FD.valor) AS subtotal,
                CASE
                    WHEN CF.dias_pronto_pago > DATEDIFF('{$fechaManual}', '{$inicioMes}')
                        THEN SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100)
                        ELSE 0
                END AS descuento,
                CASE
                    WHEN CF.dias_pronto_pago > DATEDIFF('{$fechaManual}', '{$inicioMes}')
                        THEN SUM(FD.valor) - (SUM(FD.valor) * (CF.porcentaje_pronto_pago / 100))
                        ELSE SUM(FD.valor)
                END AS valor_total
                
            FROM
                facturacion_detalles FD
                
            LEFT JOIN facturacions FA ON FD.id_factura = FA.id
            LEFT JOIN concepto_facturacions CF ON FD.id_concepto_facturacion = CF.id

            WHERE FD.id_nit = $id_nit
                AND FA.id IS NOT NULL
                AND FD.fecha_manual = '{$inicioMes}'
                AND CF.porcentaje_pronto_pago > 0
                AND FA.pronto_pago IS NULL
                AND CF.dias_pronto_pago > DATEDIFF('{$fechaManual}', '{$inicioMes}')
                
            GROUP BY FD.id_cuenta_por_cobrar
        ");

        $facturas = collect($facturas);

        if (!count($facturas)) return false;

        $data = (object)[
            'id_factura' => $facturas[0]->id_factura,
            'has_pronto_pago' => $facturas[0]->has_pronto_pago,
            'subtotal' => 0,
            'descuento' => 0,
            'valor_total' => 0,
            'detalle' => []
        ];

        foreach ($facturas as $factura) {
            $data->subtotal+= $factura->subtotal;
            $data->descuento+= $factura->descuento;
            $data->valor_total+= $factura->valor_total;
            $data->detalle[$factura->id_cuenta_por_cobrar] = $factura;
        }

        $data->descuento = $this->roundNumber($data->descuento);

        return $data;
    }

    private function cruzarAnticipos($extracto, $anticiposDisponibles, $documentoGeneral, $cecos, $valorPendiente)
    {
        foreach ($this->facturasAnticipos as $key => $anticipo) {
            if ($anticiposDisponibles <= 0) continue;

            $totalAnticipar = 0;
            if ($anticiposDisponibles >= $valorPendiente) {
                $totalAnticipar = $valorPendiente;
                $anticiposDisponibles-= $valorPendiente;
            } else {
                $totalAnticipar = $anticiposDisponibles;
                $anticiposDisponibles = 0;
            }

            $doc = new DocumentosGeneral([
                "id_cuenta" => $anticipo->id_cuenta,
                "id_nit" => $anticipo->exige_nit ? $extracto->id_nit : null,
                "id_centro_costos" => $anticipo->exige_centro_costos ? $cecos->id : null,
                "concepto" => 'CRUCE ANTICIPOS '.$extracto->concepto,
                "documento_referencia" => $anticipo->exige_documento_referencia ? $extracto->documento_referencia : null,
                "debito" => $totalAnticipar,
                "credito" => $totalAnticipar,
                "created_by" => request()->user()->id,
                "updated_by" => request()->user()->id
            ]);
            $documentoGeneral->addRow($doc, PlanCuentas::DEBITO);

            if ($anticipo->saldo <= 0) unset($this->facturasAnticipos[$key]);
        }
        return [$anticiposDisponibles, $valorPendiente, $totalAnticipar]; 
    }

    private function totalAnticipos($id_nit)
    {
        $extractos = (new Extracto(//TRAER CUENTAS POR COBRAR
            $id_nit,
            [4,8],
            null,
            $this->fechaManual
        ))->actual()->get();

        //VALIDAMOS QUE TENGA CUENTAS POR COBRAR
        if (!count($extractos)) return 0;

        $this->facturasAnticipos = [];
        $totalAnticipos = 0;
        
        foreach ($extractos as $extracto) {
            $extracto = (object)$extracto;
            $this->facturasAnticipos[] = (object)[
                'documento_referencia' => $extracto->documento_referencia,
                'id_cuenta' => $extracto->id_cuenta,
                'naturaleza_ingresos' => $extracto->naturaleza_ingresos,
                'naturaleza_egresos' => $extracto->naturaleza_egresos,
                'naturaleza_compras' => $extracto->naturaleza_compras,
                'naturaleza_ventas' => $extracto->naturaleza_ventas,
                'naturaleza_cuenta' => $extracto->naturaleza_cuenta,
                'exige_nit' => $extracto->exige_nit,
                'exige_documento_referencia' => $extracto->exige_documento_referencia,
                'exige_concepto' => $extracto->exige_concepto,
                'exige_centro_costos' => $extracto->exige_centro_costos,
                'saldo' => floatval($extracto->saldo)
            ];

            $totalAnticipos+= floatval($extracto->saldo);
        }

        return $totalAnticipos;
    }

    private function sumarDeudaTotal($extractos)
    {
        $totalDeuda = 0;
        foreach ($extractos as $extracto) {
            $totalDeuda+= $extracto->saldo;
        }
        return $totalDeuda;
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

    private function roundNumber($number)
    {
        if ($this->redondeo) {
            return round($number / $this->redondeo) * $this->redondeo;
        }
        return $number;
    }

}