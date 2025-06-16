<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SendGridWebhookController extends Controller
{

    public function handle(Request $request)
    {
        $event = $request->all();
        $eventData = $event['event'] ?? $event;

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
            'trackingData'   => $trackingData,
            'eventData'      => $eventData,
            'event'          => $event,
        ]);

        return response()->json(['status' => 'ok']);
    }

    private function extractTrackingData($event)
    {
        // Método 1: SMTPAPI directo
        if (isset($event['smtp-api'])) {
            $smtpApi = json_decode($event['smtp-api'], true);
            return $smtpApi['unique_args'] ?? null;
        }
        
        // Método 2: Unique_args directo
        return $event['unique_args'] ?? null;
    }

    private function extractFromCustomHeaders($event)
    {
        if (isset($event['headers'])) {
            $headers = is_string($event['headers']) ? 
                    $this->parseHeaders($event['headers']) : 
                    $event['headers'];
                    
            if (isset($headers['x-maximoph-tracking'])) {
                return json_decode(base64_decode($headers['x-maximoph-tracking']), true);
            }
        }
        return null;
    }
}
