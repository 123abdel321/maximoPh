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
            $trackingId = $event['custom_args']['maximoph_tracking_id'] ?? 
                $this->extractFromSmtpApi($event) ?? 
                null;
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

    private function extractFromSmtpApi($event): ?string
    {
        if (isset($event['smtp-api'])) {
            $smtpApi = json_decode($event['smtp-api'], true);
            return $smtpApi['custom_args']['maximoph_tracking_id'] ?? null;
        }
        return null;
    }
}
