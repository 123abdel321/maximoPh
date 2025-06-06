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
use App\Models\Sistema\envioEmail;
use App\Models\Empresa\Empresa;

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
        public int $id_nit
    ) {}

    public function handle()
    {
        Mail::to($this->email)
            ->cc('noreply@maximoph.co')
            ->bcc('bcc@maximoph.co')
            ->send(
                new GeneralEmail(
                    $this->empresa->razon_social,
                    'emails.factura',
                    [
                        'nombre' => $this->nombre,
                        'factura' => $this->consecutivo,
                        'valor' => $this->saldo_final,
                    ],
                    $this->pdfPath
                )
            );

        envioEmail::create([
            'id_nit' => $this->id_nit,
            'email' => $this->email,
            'contexto' => 'emails.factura'
        ]);
    }

    public function failed(\Throwable $exception)
    {
        Log::error('SendSingleEmail falló', [
            'email' => $this->email,
            'error' => $exception->getMessage()
        ]);
    }
}