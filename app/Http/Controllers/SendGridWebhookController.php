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
            $trackingId   = $this->extractTrackingId($event);
            $sgMessageId  = $event['sg_message_id'] ?? null;
            $eventType    = $event['event'] ?? null;

            Log::info('SendGrid Event', [
                'event'        => $event,
                'trackingId'   => $trackingId,
                'sgMessageId'  => $sgMessageId,
                'eventType'    => $eventType,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }

    protected function extractTrackingId(array $event): ?string
    {
        // SendGrid a veces coloca los headers en diferentes lugares
        if (isset($event['headers'])) {
            if (is_string($event['headers'])) {
                // Parsear headers como string
                preg_match('/X-Maximoph-Tracking-ID: (.+)/', $event['headers'], $matches);
                return $matches[1] ?? null;
            } elseif (is_array($event['headers'])) {
                return $event['headers']['X-Maximoph-Tracking-ID'] ?? null;
            }
        }
        
        return null;
    }
}
