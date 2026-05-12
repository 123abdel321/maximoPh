<?php

namespace App\Jobs;

use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Sistema\Entorno;
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Facturacion;

class ProcessFacturacionGeneralDelete implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1800;
    public $tries = 1;
    // public $queue = 'facturacion';

    public $empresa = null;
    public $inicioMes = null;
    public $id_usuario = null;
    public $id_empresa = null;
    public $periodo_facturacion = null;

    public function __construct($id_usuario, $id_empresa)
    {
        $this->id_usuario = $id_usuario;
        $this->id_empresa = $id_empresa;
        $this->empresa = Empresa::find($id_empresa);
        $this->periodo_facturacion = Entorno::where('nombre', 'periodo_facturacion')->first()->valor;
        $this->inicioMes = date('Y-m', strtotime($this->periodo_facturacion));
    }

    public function handle()
    {
        try {
            copyDBConnection('max', 'max');
            setDBInConnection('max', $this->empresa->token_db_maximo);

            copyDBConnection('sam', 'sam');
            setDBInConnection('sam', $this->empresa->token_db_portafolio);

            $fechaObjetivo = $this->inicioMes . '-01';

            // 1. Obtener todos los token_factura del período
            $tokens = Facturacion::where('fecha_manual', $fechaObjetivo)
                ->whereNotNull('token_factura')
                ->pluck('token_factura')
                ->toArray();

            if (!empty($tokens)) {
                // Procesar tokens en lotes de 500 para evitar consultas gigantes
                foreach (array_chunk($tokens, 500) as $tokenChunk) {
                    // Obtener los IDs de fac_documentos que serán eliminados
                    $facDocumentosIds = DB::connection('sam')->table('fac_documentos')
                        ->whereIn('token_factura', $tokenChunk)
                        ->pluck('id')
                        ->toArray();

                    if (!empty($facDocumentosIds)) {
                        // Eliminar documentos_generals relacionados (relación polimórfica)
                        // relation_type = 2 significa que apunta a la tabla fac_documentos
                        DB::connection('sam')->table('documentos_generals')
                            ->whereIn('relation_id', $facDocumentosIds)
                            ->where('relation_type', 2)
                            ->delete();

                        // Eliminar los fac_documentos
                        DB::connection('sam')->table('fac_documentos')
                            ->whereIn('id', $facDocumentosIds)
                            ->delete();
                    }
                }
            }

            // 2. Eliminar detalles de facturación (en 'max')
            DB::connection('max')->table('facturacion_detalles')
                ->whereIn('id_factura', function ($query) use ($fechaObjetivo) {
                    $query->select('id')
                        ->from('facturacions')
                        ->where('fecha_manual', $fechaObjetivo);
                })->delete();

            // 3. Eliminar las facturas principales
            $deleted = Facturacion::where('fecha_manual', $fechaObjetivo)->delete();

            // 4. Limpiar posibles huérfanos (detalles sin factura)
            $this->limpiarDetallesHuerfanos();

            // Notificar éxito
            event(new PrivateMessageEvent("facturacion-rapida-{$this->empresa->token_db_maximo}_{$this->id_usuario}", [
                'tipo' => 'exito',
                'success' => true,
                'action' => 2
            ]));

        } catch (Exception $exception) {
            Log::error('ProcessFacturacionGeneralDelete falló', [
                'message' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString()
            ]);

            event(new PrivateMessageEvent("facturacion-rapida-{$this->empresa->token_db_maximo}_{$this->id_usuario}", [
                'tipo' => 'error',
                'success' => false,
                'message' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'action' => 5
            ]));

            throw $exception;
        }
    }

    /**
     * Limpia registros huérfanos en facturacion_detalles (sin factura padre)
     */
    private function limpiarDetallesHuerfanos()
    {
        DB::connection('max')->table('facturacion_detalles')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('facturacions')
                    ->whereRaw('facturacion_detalles.id_factura = facturacions.id');
            })
            ->delete();
    }

    public function failed($exception)
    {
        Log::error('ProcessFacturacionGeneralDelete falló definitivamente', [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine()
        ]);
    }
}