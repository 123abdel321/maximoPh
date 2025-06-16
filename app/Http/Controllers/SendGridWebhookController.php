<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
//MODELOS
use App\Models\Empresa\EnvioEmail;

class SendGridWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $events = json_decode($request->getContent(), true);

        foreach ($events as $event) {

            $sgMessageId = $event['sg_message_id'] ?? null;
            $smtpId = $event['smtp-id'] ?? null;
            $eventType = $event['event'] ?? null;

            Log::info('SendGrid Event', [
                'event'        => $event,
                'sgMessageId'  => $sgMessageId,
                'smtpId'  => $smtpId,
                'eventType'    => $eventType,
            ]);

            $trackingId = null;

            // Opción 1: extraer de sg_message_id
            if ($sgMessageId && str_contains($sgMessageId, '.')) {
                $trackingId = explode('.', $sgMessageId)[0];
            } elseif ($sgMessageId) {
                $trackingId = $sgMessageId;
            }
            // Opción 2: usar smtp-id como fallback si no hay sg_message_id válido
            elseif ($smtpId && str_contains($smtpId, '@') === false) {
                $trackingId = $smtpId;
            }

            // Buscar por message_id en la base de datos
            $envio = EnvioEmail::where('sg_message_id', $trackingId)->first();

            Log::info($envio);

        }

        return response()->json(['status' => 'ok']);
    }
}
