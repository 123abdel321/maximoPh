<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\GeneralEmail;
use App\Helpers\Eco\SendEcoEmail;
//MODEL
use App\Models\Empresa\Empresa;
use App\Models\Empresa\EnvioEmail;

class SendSingleEmail implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;
    public $maxExceptions = 1;

    public function __construct(
        public Empresa $empresa,
        public string $email,
        public array $emailData,
        public array $filterData,
        public string $pdfPath,
        public string $ecoToken,
        public string $view
    ) {}

    public function handle()
    {
        try {
            copyDBConnection('max', 'max');
            setDBInConnection('max', $this->empresa->token_db_maximo);

            $htmlContent = View::make($this->view, $this->emailData)->render();

            $subject = $this->emailData['asunto'] ?? 'Notificación de ' . $this->empresa->razon_social;
            
            $attachments = [];
            
            if (!empty($this->pdfPath)) {
                // Descargar el contenido del PDF desde la URL
                $pdfResponse = Http::timeout(30)->get($this->pdfPath);
                
                if ($pdfResponse->successful()) {
                    $pdfContentBase64 = base64_encode($pdfResponse->body());
                    
                    // Intentar obtener el nombre del archivo de la URL
                    $pdfFileName = basename(parse_url($this->pdfPath, PHP_URL_PATH));
                    if (empty($pdfFileName) || $pdfFileName === '/') {
                        $pdfFileName = 'documento_adjunto.pdf';
                    }
                    
                    $attachments[] = [
                        "contenido" => $pdfContentBase64,
                        "nombre" => $pdfFileName,
                        "mime" => "application/pdf"
                    ];

                } else {
                    Log::warning('SendSingleEmail: No se pudo descargar el PDF para el envío.', [
                        'pdfPath' => $this->pdfPath,
                        'status' => $pdfResponse->status(),
                    ]);
                    // Se permite continuar sin adjunto si la descarga falla, pero se registra.
                }
            }

            $metadata = array_merge($this->emailData, [
                'contexto' => $this->view,
                'envio_id' => '',
                'empresa_token' => $this->empresa->token_db_maximo,
            ]);

            $sendEcoEmail = new SendEcoEmail(
                $this->email,
                $subject,
                $htmlContent,
                $metadata,
                $this->filterData,
                $attachments
            );

            $sendEcoEmail->setToken($this->ecoToken)->send();
    
        } catch (\Throwable $exception) {
            Log::error('SendSingleEmail falló', [
                'email' => $this->email,
                'error' => $exception->getMessage(),
                'line' => $exception->getLine(),
                'pdf_path' => $this->pdfPath,
            ]);
            throw $exception; 
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('SendSingleEmail falló', [
            'email' => $this->email,
            'error' => $exception->getMessage(),
            'line' => $exception->getLine(),
            'pdf_path' => $this->pdfPath,
        ]);
    }
}