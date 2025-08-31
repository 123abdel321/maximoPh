<?php

namespace App\Helpers\WhatsApp;

use Twilio\Rest\Client;
// MODELS
use App\Models\Empresa\Empresa;

abstract class AbstractTwilioWhatsAppSender
{
    public abstract function getContentSid(): string;
    public abstract function getParameters(): array;
    public abstract function getTo(): string;
    public abstract function getFrom(): string;
    public abstract function getMessagingServiceSid(): string;

    public function send($id_empresa = null)
    {
        // Configuración de Twilio
        $sid = config('services.twilio.account_sid');
        $token = config('services.twilio.auth_token');

        Log::info('sid: ', $sid);
        Log::info('token: ', $token);

        $twilio = new Client($sid, $token);

        try {
            
            $message = $twilio->messages->create(
                $this->getTo(),
                [
                    "from" => $this->getFrom(),
                    "contentSid" => $this->getContentSid(),
                    "contentVariables" => json_encode($this->getParameters())
                ]
            );

            return (object)[
                "status" => 200,
                "response" => (object)[
                    'sid' => $message->sid,
                    'status' => $message->status,
                    'body' => $message->body,
                    'to' => $message->to,
                    'from' => $message->from,
                    'date_created' => $message->dateCreated->format('Y-m-d H:i:s')
                ],
            ];
        } catch (\Exception $e) {
            return [
                "status" => 500,
                "response" => [
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ],
            ];
        }
    }
}