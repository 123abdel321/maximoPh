<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Mail\GeneralEmail;
//MODEL
use App\Models\Empresa\Empresa;
use App\Models\Empresa\EnvioEmail;

class SendSingleEmail implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $maxExceptions = 1;

    public function __construct(
        public Empresa $empresa,
        public string $email,
        public string $nombre,
        public string $consecutivo,
        public float $saldo_final,
        public string $pdfPath,
        public string $view,
        public string $envioEmailId
    ) {}

    public function handle()
    {
        copyDBConnection('max', 'max');
        setDBInConnection('max', $this->empresa->token_db_maximo);

        $response = Mail::to($this->email)
            ->send(
                new GeneralEmail(
                    $this->empresa->razon_social,
                    $this->view,
                    [
                        'nombre' => $this->nombre,
                        'factura' => $this->consecutivo,
                        'valor' => $this->saldo_final,
                    ],
                    $this->pdfPath
                )
            );

        $sgMessageId = $response->getSymfonySentMessage()->getMessageId();

        $envioEmail = EnvioEmail::where('id', $this->envioEmailId)->first();
        $envioEmail->sg_message_id = $sgMessageId;
        $envioEmail->save();
    }

    public function failed(\Throwable $exception)
    {
        Log::error('SendSingleEmail falló', [
            'email' => $this->email,
            'error' => $exception->getMessage()
        ]);
    }
}