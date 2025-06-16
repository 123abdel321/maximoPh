<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SendGridWebhookController extends Controller
{

    public function handle(Request $request)
    {
        $events = $request->all();

        if (!is_array($events)) {
            Log::warning('SendGrid Webhook: payload no es un array válido');
            return response()->json(['error' => 'Invalid payload'], 400);
        }

        foreach ($events as $event) {
            
            // Nivel 1: Buscar en unique_args (SMTPAPI)
            $trackingData = $this->extractTrackingData($eventData);

            // Nivel 2: Buscar en headers personalizados
            if (empty($trackingData)) {
                $trackingData = $this->extractFromCustomHeaders($eventData);
            }

            // Nivel 3: Buscar en el cuerpo del mensaje (para eventos opens/clicks)
            if (empty($trackingData)) {
                // $trackingData = $this->extractFromBody($eventData);
            }

            Log::info('SendGrid Event', [
                'event'          => $event,
                'trackingData'   => $trackingData
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    private function extractFromSmtpApi($event): ?string
    {
        if (isset($event['smtp-api'])) {
            $smtpApi = json_decode($event['smtp-api'], true);
            return $smtpApi['custom_args']['maximoph_tracking_id'] ?? null;
        }
        return null;
    }
}
