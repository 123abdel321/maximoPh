<?php

namespace App\Jobs;

use DB;
use Exception;
use App\Helpers\helpers;
use App\Mail\GeneralEmail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Queue\InteractsWithQueue;
use App\Helpers\Printers\FacturacionPdf;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Helpers\DocumentoGeneralController;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Empresa\Empresa;
use App\Models\Sistema\envioEmail;
use App\Models\Sistema\Facturacion;
use App\Models\Sistema\InmuebleNit;
use App\Models\Portafolio\FacDocumentos;
use App\Models\Sistema\FacturacionDetalle;
use App\Models\Portafolio\DocumentosGeneral;

class ProcessEnvioFacturaEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $empresa = null;
    public $request = null;
    public $id_empresa = null;
    public $id_usuario = null;

    public function __construct($request, $id_empresa, $id_usuario)
    {
        $this->request = $request;
        $this->id_empresa = $id_empresa;
        $this->id_usuario = $id_usuario;
        $this->empresa = Empresa::find($id_empresa);
    }

    public function handle()
    {
        try {
            copyDBConnection('max', 'max');
            setDBInConnection('max', $this->empresa->token_db_maximo);

            copyDBConnection('sam', 'sam');
            setDBInConnection('sam', $this->empresa->token_db_portafolio);

            $countFacturasEnviadas = 0;

            $query = $this->carteraDocumentosQuery();
            $query->unionAll($this->carteraAnteriorQuery());

            $nits = DB::connection('sam')
                ->table(DB::raw("({$query->toSql()}) AS cartera"))
                ->select(
                    'id_nit',
                    'email',
                    'email_1',
                    'email_2',
                    'nombre_nit',
                    'consecutivo',
                    DB::raw('SUM(saldo_anterior) + SUM(debito) - SUM(credito) AS saldo_final'),
                )
                ->mergeBindings($query)
                ->groupByRaw('id_nit')
                ->orderByRaw('cuenta, id_nit, documento_referencia, created_at')
                ->get();

            foreach ($nits as $nit) {

                $facturaPdf = (new FacturacionPdf($this->empresa, $nit->id_nit, $this->request['periodo']))
                    ->buildPdf()
                    ->saveStorage();

                if ($nit->email && filter_var($nit->email, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($nit->email)
                    ->cc('noreply@maximoph.co')
                    ->bcc('bcc@maximoph.co')
                    ->queue(new GeneralEmail($this->empresa->razon_social, 'emails.factura', [
                        'nombre' => $nit->nombre_nit,
                        'factura' => $nit->consecutivo,
                        'valor' => $nit->saldo_final,
                    ], $facturaPdf));
                    envioEmail::create([
                        'id_nit' => $nit->id_nit,
                        'email' => $nit->email,
                        'contexto' => 'emails.factura'
                    ]);
                }

                if ($nit->email_1 && $nit->email != $nit->email_1 && filter_var($nit->email_1, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($nit->email_1)
                    ->cc('noreply@maximoph.co')
                    ->bcc('bcc@maximoph.co')
                    ->queue(new GeneralEmail($this->empresa->razon_social, 'emails.factura', [
                        'nombre' => $nit->nombre_nit,
                        'factura' => $nit->consecutivo,
                        'valor' => $nit->saldo_final,
                    ], $facturaPdf));
                    envioEmail::create([
                        'id_nit' => $nit->id_nit,
                        'email' => $nit->email_1,
                        'contexto' => 'emails.factura'
                    ]);
                }

                if ($nit->email_2 && $nit->email != $nit->email_2 && $nit->email_1 != $nit->email_2 && filter_var($nit->email_2, FILTER_VALIDATE_EMAIL)) {
                    Mail::to($nit->email_2)
                    ->cc('noreply@maximoph.co')
                    ->bcc('bcc@maximoph.co')
                    ->queue(new GeneralEmail($this->empresa->razon_social, 'emails.factura', [
                        'nombre' => $nit->nombre_nit,
                        'factura' => $nit->consecutivo,
                        'valor' => $nit->saldo_final,
                    ], $facturaPdf));
                    envioEmail::create([
                        'id_nit' => $nit->id_nit,
                        'email' => $nit->email_2,
                        'contexto' => 'emails.factura'
                    ]);
                }
                $countFacturasEnviadas++;
                Storage::disk('do_spaces')->delete($facturaPdf);
            }

            $urlEventoNotificacion = $this->empresa->token_db_maximo.'_'.$this->id_usuario;
            event(new PrivateMessageEvent('facturacion-email-'.$urlEventoNotificacion, [
                'tipo' => 'exito',
                'success' =>  true,
                'total_envios' => $countFacturasEnviadas,
                'action' => 2
            ]));

		} catch (Exception $exception) {
			Log::error('ProcessFacturacionGeneralDelete al enviar facturación a PortafolioERP', [
                'message' => $exception->getMessage(),
                'line' => $exception->getLine()
            ]);

            throw $exception;
		}
    }

    private function carteraDocumentosQuery()
    {
        $documentosQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'N.id AS id',
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "N.plazo",
                "N.email",
                "N.email_1",
                "N.email_2",
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
            ->when($this->request['periodo'] ? true : false, function ($query) {
				$query->where('DG.fecha_manual', '>=', $this->request['periodo']);
			})
            ->when($this->request['id_nit'] ? true : false, function ($query) {
				$query->where('DG.id_nit', '=', $this->request['id_nit']);
			})
            ->when($this->request['factura_fisica'] ? true : false, function ($query) {
                $nits = $this->nitFacturaFisica(true);
				$query->whereIn('DG.id_nit', $nits);
			});

        return $documentosQuery;
    }

    private function carteraAnteriorQuery()
    {
        $anterioresQuery = DB::connection('sam')->table('documentos_generals AS DG')
            ->select(
                'N.id AS id',
                'N.id AS id_nit',
                'N.numero_documento',
                DB::raw("(CASE
                    WHEN id_nit IS NOT NULL AND razon_social IS NOT NULL AND razon_social != '' THEN razon_social
                    WHEN id_nit IS NOT NULL AND (razon_social IS NULL OR razon_social = '') THEN CONCAT_WS(' ', primer_nombre, primer_apellido)
                    ELSE NULL
                END) AS nombre_nit"),
                "N.razon_social",
                "N.plazo",
                "N.email",
                "N.email_1",
                "N.email_2",
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
            ->when($this->request['periodo'] ? true : false, function ($query) {
				$query->where('DG.fecha_manual', '<', $this->request['periodo']);
			})
            ->when($this->request['id_nit'] ? true : false, function ($query) {
				$query->where('DG.id_nit', '=', $this->request['id_nit']);
			})
            ->when($this->request['factura_fisica'] ? true : false, function ($query) {
                $nits = $this->nitFacturaFisica(true);
				$query->whereIn('DG.id_nit', $nits);
			});

        return $anterioresQuery;
    }

    private function nitFacturaFisica($fisica = false)
    {
        $nits = [];
        $inmuebleNit = InmuebleNit::select('id_nit')
            ->when($fisica, function ($query) {
                $query->where('enviar_notificaciones_fisica', 1);
            })
            ->groupBy('id_nit')
            ->get();

        foreach ($inmuebleNit as $key => $nit) {
            array_push($nits, $nit->id_nit);
        }

        return $nits;
    }

	public function failed($exception)
	{
		Log::error('ProcessEnvioFacturaEmail al enviar facturación a PortafolioERP', [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine()
        ]);
	}
}
