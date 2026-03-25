<?php

namespace App\Jobs;

use DB;
use Exception;
use App\Mail\GeneralEmail;
use Illuminate\Bus\Queueable;
use App\Jobs\SendSingleEmail;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
//HELPER
use App\Helpers\Eco\SendEcoWhatsApp;
//MODELS
use App\Models\Sistema\Zonas;
use App\Models\Portafolio\Nits;
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Entorno;
use App\Models\Empresa\EnvioEmail;
use App\Models\Sistema\InmuebleNit;

class ProcessEnvioGeneralWhatsapp implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $plantilla;
    public $archivos = null;
    public $timeout = 300;
    public $empresa = null;
    public $request = null;
    public $ecoToken = null;
    public $id_empresa = null;
    public $id_usuario = null;
    public $maxExceptions = 3;
    public $backoff = [60, 120];
    public $emailsPerMinute = 20;

    private $templateVariableMap = [
        'general_multimedia' => [
            '1' => 'empresa',
            '2' => 'mensaje',
            '3' => 'archivo_url_completa'
        ],

        'general_media' => [
            '1' => 'nombre',
            '2' => 'empresa',
            '3' => 'mensaje',
            '4' => 'archivo_url_completa'
        ],
        
        'general_text' => [
            '1' => 'nombre',
            '2' => 'empresa',
            '3' => 'mensaje'
        ],
    ];

    public function __construct($request, $id_empresa, $id_usuario, $archivos = [])
    {
        $this->request = $request;
        $this->id_empresa = $id_empresa;
        $this->id_usuario = $id_usuario;
        $this->empresa = Empresa::find($id_empresa);

        if (count($archivos)) {
            $this->archivos = $archivos[0]['url'];
        }

        $this->plantilla = $this->request['plantilla'];        
    }

    public function handle()
    {
        try {
            copyDBConnection('max', 'max');
            setDBInConnection('max', $this->empresa->token_db_maximo);

            copyDBConnection('sam', 'sam');
            setDBInConnection('sam', $this->empresa->token_db_portafolio);

            $this->ecoToken = Entorno::where('nombre', 'eco_login')->first();
            $this->ecoToken = $this->ecoToken?->valor ?? null;

            // Obtener los Nits basados en los filtros
            $query = Nits::on('sam')
                ->whereNotNull('telefono_1')
                ->where('telefono_1', '!=', '');

            // Filtrar por NIT específico
            if (!empty($this->request['id_nit'])) {
                $query->where('id', $this->request['id_nit']);
            }

            // Filtrar por zona
            if (!empty($this->request['id_zona'])) {
                $zona = Zonas::find($this->request['id_zona']);
                if ($zona) {
                    $query->where('apartamentos', 'LIKE', '%' . $zona->nombre . '%');
                }
            }

            // Obtener los Nits
            $nits = $query->get();

            // Procesar números adicionales separados por coma
            $numerosAdicionales = [];
            if (!empty($this->request['numeros'])) {
                // Solo separar por coma y limpiar espacios
                $numerosAdicionales = array_map('trim', explode(',', $this->request['numeros']));
                
                // Si quieres eliminar números vacíos (por si hay comas extras)
                $numerosAdicionales = array_filter($numerosAdicionales);
                
                // Re-indexar el array
                $numerosAdicionales = array_values($numerosAdicionales);
            }

            $whatsappIndex = 0;

            // Primero enviar a los Nits
            foreach ($nits as $index => $nit) {

                $inmuebleNit = DB::connection('max')
                    ->table('inmueble_nits AS IN')
                    ->where('IN.id_nit', $nit->id_nit)
                    ->first();
                
                if ($inmuebleNit && !$inmuebleNit->enviar_notificaciones_mail) continue;

                $whatsappToSend = array_filter([
                    $nit->telefono_1 ?: null,
                    ($nit->telefono_2 && $nit->telefono_2 != $nit->telefono_1) ? $nit->telefono_2 : null
                ]);

                foreach ($whatsappToSend as $whatsapp) {
                    $whatsappIndex++;
                    $this->enviarWhatsappIndividual($whatsapp, $nit);
                }
            }

            // Enviar a whatsapp adicionales
            foreach ($numerosAdicionales as $index => $whatsapp) {
                $whatsappIndex++;
                $this->enviarWhatsappIndividual($whatsapp, (object)[
                    'primer_nombre' => 'Usuario Maximo PH',
                    'id_nit' => null
                ]);
            }

            $urlEventoNotificacion = $this->empresa->token_db_maximo.'_'.$this->id_usuario;
            event(new PrivateMessageEvent('facturacion-email-'.$urlEventoNotificacion, [
                'tipo' => 'exito',
                'success' =>  true,
                'total_envios' => $whatsappIndex,
                'action' => 2
            ]));

        } catch (Exception $exception) {
            Log::error('ProcessEnvioGeneralEmail Error', [
                'message' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'request' => $this->request
            ]);

            throw $exception;
        }
    }

    private function buildTemplateParameters($nit)
    {
        $map = $this->templateVariableMap[$this->plantilla] ?? [];
        $archivo = $this->archivos ?? null;

        if ($archivo) {
            $archivo = str_replace(
                'https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/',
                '',
                $archivo
            );
        }

        $availableData = [
            'nombre' => $nit->primer_nombre ?? null,
            'empresa' => $this->empresa->razon_social ?? null,
            'mensaje' => $this->request['mensaje'] ?? null,
            'archivo_url_completa' => $this->archivos,
            'archivo_url_recortada' => $archivo,
        ];

        $parameters = [];

        foreach ($map as $index => $field) {
            if (isset($availableData[$field])) {
                $parameters[$index] = $availableData[$field];
            }
        }

        return $parameters;
    }


    /**
     * Envía un WhatsApp individual con delay
     */
    private function enviarWhatsappIndividual($whatsapp, $nit)
    {
        $whatsappData = $this->buildTemplateParameters($nit);

        $nombreCompleto = '';

        $filterData = [
            'id_nit' => $nit->id,
            'nombre_completo' => $nit->nombre_completo,
            'apartamentos' => $nit->apartamentos,
            'telefono' => $whatsapp
        ];

        $sender = new SendEcoWhatsApp(
            // "573145876923",
            "57$whatsapp",
            $whatsappData,
            $filterData,
            $this->plantilla,
        );

        $sender->setToken($this->ecoToken)->send();
    }

    public function failed($exception)
    {
        Log::error('ProcessEnvioGeneralWhatsapp Failed', [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine(),
            'request' => $this->request
        ]);
    }
}