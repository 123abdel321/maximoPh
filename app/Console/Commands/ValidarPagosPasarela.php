<?php

namespace App\Console\Commands;

use DB;
use Exception;
use App\Helpers\helpers;
use App\Helpers\Extracto;
use App\Helpers\Documento;
use Illuminate\Console\Command;
use App\Events\PrivateMessageEvent;
use App\Helpers\PlacetoPay\PaymentStatus;
use App\Http\Controllers\Traits\BegConsecutiveTrait;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Portafolio\Nits;
use App\Models\Portafolio\ConRecibos;
use App\Models\Portafolio\PlanCuentas;
use App\Models\Portafolio\CentroCostos;
use App\Models\Portafolio\FacFormasPago;
use App\Models\Portafolio\FacDocumentos;
use App\Models\Portafolio\DocumentosGeneral;
use App\Models\Portafolio\ConReciboDetalles;

class ValidarPagosPasarela extends Command
{
    use BegConsecutiveTrait;

    protected $signature = 'app:validar-pagos-pasarela';
    protected $description = 'Validar pagos de place to pay';

    protected $timeout = 10;
    protected $chunkSize = 34;

    public function handle()
    {
        $startTime = microtime(true);
        $this->info('Iniciando validación de pagos...');

        try {
            
            $empresas = DB::connection('clientes')
                ->table('empresas')
                ->select('razon_social', 'token_db_maximo', 'token_db_portafolio')
                ->cursor(); // Usar cursor para mejor manejo de memoria

            foreach ($empresas as $empresa) {
                $this->info("\nValidando pagos de: {$empresa->razon_social}");

                // Configurar conexiones
                $this->setUpDatabaseConnections($empresa);

                ConRecibos::where('estado', 2)
                    ->chunk($this->chunkSize, function ($recibos) {
                        foreach ($recibos as $recibo) {
                            $this->validarPlaceToPay($recibo);
                        }
                    });

                // Liberar memoria
                gc_collect_cycles();
            }

            $executionTime = round((microtime(true) - $startTime) / 60, 2);
            $this->info("\nProceso completado. Tiempo total: {$executionTime} minutos");
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }

        $empresas = DB::connection('clientes')
            ->table('empresas')
            ->select('razon_social', 'token_db_maximo', 'token_db_portafolio')
            ->get();

        foreach ($empresas as $empresa) {

            $this->info("Validando pagos de: {$empresa->razon_social}");

            copyDBConnection('max', 'max');
            setDBInConnection('max', $empresa->token_db_maximo);

            copyDBConnection('sam', 'sam');
            setDBInConnection('sam', $empresa->token_db_portafolio);

            $recibos = ConRecibos::where('estado', 2)->get();

            if (!count($recibos)) {
                continue;
            }

            foreach ($recibos as $recibo) {
                $this->validarPlaceToPay($recibo);
            }

        }
    }

    protected function setUpDatabaseConnections($empresa): void
    {
        try {
            copyDBConnection('max', 'max');
            setDBInConnection('max', $empresa->token_db_maximo);

            copyDBConnection('sam', 'sam');
            setDBInConnection('sam', $empresa->token_db_portafolio);
            
            $this->info("Conexiones configuradas correctamente");
        } catch (\Exception $e) {
            $this->error("Error configurando conexiones para {$empresa->razon_social}: " . $e->getMessage());
            throw $e;
        }
    }

    protected function validarPlaceToPay($recibo): void
    {
        try {
            // Aquí iría la lógica de validación
            $this->info("Validando recibo ID: {$recibo->id}");
            
        } catch (\Exception $e) {
            $this->error("Error validando recibo {$recibo->id}: " . $e->getMessage());
        }
    }
}
