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
//MODELS
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Entorno;
use App\Models\Sistema\Zonas;
use App\Models\Portafolio\Nits;
use App\Models\Sistema\InmuebleNit;

class ProcessEnvioGeneralEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
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

    public function __construct($request, $id_empresa, $id_usuario, $archivos = [])
    {
        $this->request = $request;
        $this->id_empresa = $id_empresa;
        $this->id_usuario = $id_usuario;
        $this->empresa = Empresa::find($id_empresa);
        if (count($archivos)) {
            $this->archivos = $archivos[0]['url'];
        }
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
                ->whereNotNull('email')
                ->where('email', '!=', '');

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

            // Procesar correos adicionales separados por coma
            $emailsAdicionales = [];
            if (!empty($this->request['correos'])) {
                $emailsAdicionales = array_map('trim', explode(',', $this->request['correos']));
                // Filtrar emails válidos
                $emailsAdicionales = array_filter($emailsAdicionales, function($email) {
                    return filter_var($email, FILTER_VALIDATE_EMAIL);
                });
            }

            $totalEnviados = 0;
            // Primero enviar a los Nits
            foreach ($nits as $index => $nit) {
                // Verificar si debe recibir notificaciones por email (de la tabla inmueble_nits)
                $recibirNotificaciones = $this->debeRecibirNotificaciones($nit->id);
                
                if (!$recibirNotificaciones) {
                    continue;
                }
                
                // Preparar los emails a enviar para este NIT
                $emailsNit = $this->obtenerEmailsNit($nit);
                
                foreach ($emailsNit as $email) {
                    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        continue;
                    }

                    $this->enviarEmailIndividual(
                        $email,
                        $nit->nombre_completo ?? $nit->razon_social,
                        $nit->id,
                        $this->archivos
                    );
                    
                    $totalEnviados++;
                }
            }

            // Enviar a emails adicionales
            foreach ($emailsAdicionales as $index => $email) {
                
                $this->enviarEmailIndividual(
                    $email,
                    'Cliente',
                    null,
                    $this->archivos
                );
                
                $totalEnviados++;
            }

            // Notificar éxito
            $urlEventoNotificacion = $this->empresa->token_db_maximo . '_' . $this->id_usuario;
            event(new PrivateMessageEvent('general-email-' . $urlEventoNotificacion, [
                'tipo' => 'exito',
                'success' => true,
                'total_envios' => $totalEnviados,
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

    /**
     * Verifica si un NIT debe recibir notificaciones por email
     */
    private function debeRecibirNotificaciones($idNit)
    {
        $inmuebleNit = InmuebleNit::on('max')
            ->where('id_nit', $idNit)
            ->first();
        
        // Si no existe registro en inmueble_nits, asumimos que sí recibe notificaciones
        if (!$inmuebleNit) {
            return true;
        }
        
        return $inmuebleNit->enviar_notificaciones_mail == 1;
    }

    /**
     * Obtiene todos los emails válidos de un NIT
     */
    private function obtenerEmailsNit($nit)
    {
        $emails = [];
        
        // Email principal
        if (!empty($nit->email)) {
            $emails[] = $nit->email;
        }
        
        // Email 1 (si es diferente al principal)
        if (!empty($nit->email_1) && $nit->email_1 != $nit->email) {
            $emails[] = $nit->email_1;
        }
        
        // Email 2 (si es diferente a los anteriores)
        if (!empty($nit->email_2) && 
            $nit->email_2 != $nit->email && 
            $nit->email_2 != $nit->email_1) {
            $emails[] = $nit->email_2;
        }
        
        return array_unique($emails);
    }

    /**
     * Envía un email individual con delay
     */
    private function enviarEmailIndividual($email, $nombre, $id_nit = null, $url_file = null)
    {
        $emailData = [
            'nombre' => $nombre,
            'mensaje' => $this->request['mensaje'] ?? '',
            'asunto' => $this->request['asunto'] ?? 'Comunicación Importante',
        ];
        
        $filterData = [
            'nombre_completo' => $nombre,
            'email' => $email
        ];

        SendSingleEmail::dispatch(
            $this->empresa,
            $email,
            // 'abdel_123@hotmail.es',
            $emailData,
            $filterData,
            $url_file,
            $this->ecoToken,
            'emails.general'
        );
    }

    public function failed($exception)
    {
        Log::error('ProcessEnvioGeneralEmail Failed', [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine(),
            'request' => $this->request
        ]);
    }
}