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
use App\Helpers\WhatsApp\SendTwilioWhatsApp;
//MODEL
use App\Models\Empresa\Empresa;
use App\Models\Empresa\EnvioEmail;

class SendSingleWhatsapp implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;
    public $maxExceptions = 1;

    public function __construct(
        public Empresa $empresa,
        public string $to,
        public string $contentSid,
        public array $parameters,
        public string $envioEmailId
    ) {}

    public function handle()
    {
        $whatsapp = new SendTwilioWhatsApp(
            $this->contentSid,
            $this->to,
            $this->parameters
        );

	    $result = $whatsapp->send();

        $envioEmail = EnvioEmail::where('id', $this->envioEmailId)->first();
        $envioEmail->sg_message_id = $result->response->sid;
        $envioEmail->save();
    }

    public function failed(\Throwable $exception)
    {
        Log::error('SendSingleWhatsapp falló', [
            'whatsapp' => $this->to,
            'error' => $exception->getMessage(),
            'line' => $exception->getLine()
        ]);
    }
}