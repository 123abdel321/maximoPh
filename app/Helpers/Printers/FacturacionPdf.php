<?php

namespace App\Helpers\Printers;

use DB;
use Illuminate\Support\Carbon;
//MODELS
use App\Models\Portafolio\Nits;
use App\Models\Empresa\Empresa;

class FacturacionPdf extends AbstractPrinterPdf
{
    public $id_nit;
	public $empresa;
	public $periodo;

    public function __construct(Empresa $empresa, $id_nit = null, $periodo)
	{
		parent::__construct($empresa);

		copyDBConnection('max', 'max');
        setDBInConnection('max', $empresa->token_db);

		$this->id_nit = $id_nit;
		$this->empresa = $empresa;
		$this->periodo = $periodo;
	}

    public function view()
	{
		return 'pdf.facturacion.facturaciones';
	}

    public function name()
	{
		return 'factura_'.uniqid();
	}

    public function paper()
	{
		// if ($this->tipoEmpresion == 1) return 'landscape';
		// if ($this->tipoEmpresion == 2) return 'portrait';

		return '';
	}

    public function data()
    {
		
		$getNit = Nits::whereId($this->id_nit)->with('ciudad')->first();
		$nit = null;
		
		if($getNit){ 
			$nit = (object)[
				'nombre_nit' => $getNit->nombre_completo,
				'telefono' =>  $getNit->telefono_1,
				'email' => $getNit->email,
				'direccion' => $getNit->direccion,
				'tipo_documento' => $getNit->tipo_documento->nombre,
				'numero_documento' => $getNit->numero_documento,
				"ciudad" => $getNit->ciudad ? $getNit->ciudad->nombre_completo : '',
			];
		}

		$query = $this->carteraDocumentosQuery();
		$query->unionAll($this->carteraAnteriorQuery());

		$totales = DB::connection('sam')
			->table(DB::raw("({$query->toSql()}) AS cartera"))
			->mergeBindings($query)
			->select(DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'));

		$facturaciones = DB::connection('sam')
			->table(DB::raw("({$query->toSql()}) AS cartera"))
			->mergeBindings($query)
			->select(
				'id_nit',
				'numero_documento',
				'nombre_nit',
				'razon_social',
				'id_cuenta',
				'cuenta',
				'naturaleza_cuenta',
				'auxiliar',
				'nombre_cuenta',
				'documento_referencia',
				'id_centro_costos',
				'codigo_cecos',
				'nombre_cecos',
				'id_comprobante',
				'codigo_comprobante',
				'nombre_comprobante',
				'consecutivo',
				'concepto',
				'fecha_manual',
				'created_at',
				'fecha_creacion',
				'fecha_edicion',
				'created_by',
				'updated_by',
				'anulado',
				'plazo',
				DB::raw('SUM(saldo_anterior) AS saldo_anterior'),
				DB::raw('SUM(debito) AS debito'),
				DB::raw('SUM(credito) AS credito'),
				DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
				DB::raw("IF(naturaleza_cuenta = 0, SUM(credito), SUM(debito)) AS total_abono"),
				DB::raw("IF(naturaleza_cuenta = 0, SUM(debito), SUM(credito)) AS total_facturas"),
				DB::raw('DATEDIFF(now(), fecha_manual) AS dias_cumplidos'),
				DB::raw('SUM(total_columnas) AS total_columnas')
			)
			->orderByRaw('cuenta, id_nit, documento_referencia, created_at');

        return [
			'empresa' => $this->empresa,
			'nit' => $nit,
			'cuentas' => $facturaciones->groupByRaw('id_nit, id_cuenta')->get(),
			'totales' => $totales->groupByRaw('id_nit')->first(),
			'fecha_pdf' => Carbon::now()->format('Y-m-d H:i:s'),
			'usuario' => request()->user() ? request()->user()->username : 'MaximoPH'
		];
    }

	private function carteraDocumentosQuery()
    {
        $documentosQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "N.plazo",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.naturaleza_cuenta",
                "PC.auxiliar",
                "PC.nombre AS nombre_cuenta",
                "DG.documento_referencia",
                "DG.id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "CO.id AS id_comprobante",
                "CO.codigo AS codigo_comprobante",
                "CO.nombre AS nombre_comprobante",
                "DG.consecutivo",
                "DG.concepto",
                "DG.fecha_manual",
                "DG.created_at",
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                "DG.anulado",
                DB::raw("0 AS saldo_anterior"),
                DB::raw("DG.debito AS debito"),
                DB::raw("DG.credito AS credito"),
                DB::raw("DG.debito - DG.credito AS saldo_final"),
                DB::raw("1 AS total_columnas")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'PC.id', 'PCT.id_cuenta')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->whereIn('PCT.id_tipo_cuenta', [3,7])
            ->when($this->periodo, function ($query) {
				$query->where('DG.fecha_manual', '>=', $this->periodo);
			})
            ->when($this->id_nit, function ($query) {
				$query->where('DG.id_nit', '=', $this->id_nit);
			});

        return $documentosQuery;
    }

    private function carteraAnteriorQuery()
    {
        $anterioresQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "N.plazo",
                "PC.id AS id_cuenta",
                "PC.cuenta",
                "PC.naturaleza_cuenta",
                "PC.auxiliar",
                "PC.nombre AS nombre_cuenta",
                "DG.documento_referencia",
                "DG.id_centro_costos",
                "CC.codigo AS codigo_cecos",
                "CC.nombre AS nombre_cecos",
                "CO.id AS id_comprobante",
                "CO.codigo AS codigo_comprobante",
                "CO.nombre AS nombre_comprobante",
                "DG.consecutivo",
                "DG.concepto",
                "DG.fecha_manual",
                "DG.created_at",
                DB::raw("DATE_FORMAT(DG.created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                DB::raw("DATE_FORMAT(DG.updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                "DG.created_by",
                "DG.updated_by",
                "DG.anulado",
                DB::raw("debito - credito AS saldo_anterior"),
                DB::raw("0 AS debito"),
                DB::raw("0 AS credito"),
                DB::raw("0 AS saldo_final"),
                DB::raw("1 AS total_columnas")
            )
            ->leftJoin('nits AS N', 'DG.id_nit', 'N.id')
            ->leftJoin('plan_cuentas AS PC', 'DG.id_cuenta', 'PC.id')
            ->leftJoin('plan_cuentas_tipos AS PCT', 'PC.id', 'PCT.id_cuenta')
            ->leftJoin('centro_costos AS CC', 'DG.id_centro_costos', 'CC.id')
            ->leftJoin('comprobantes AS CO', 'DG.id_comprobante', 'CO.id')
            ->where('anulado', 0)
            ->whereIn('PCT.id_tipo_cuenta', [3,7])
            ->when($this->periodo, function ($query) {
				$query->where('DG.fecha_manual', '<', $this->periodo);
			})
            ->when($this->id_nit, function ($query) {
				$query->where('DG.id_nit', '=', $this->id_nit);
			});

        return $anterioresQuery;
    }
}