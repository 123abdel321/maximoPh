<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SendGridWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $events = json_decode($request->getContent(), true);

        foreach ($events as $event) {

            $sgMessageId = $event['sg_message_id'] ?? null;
            $eventType = $event['event'] ?? null;

            Log::info('SendGrid Event', [
                'trackingId'   => $trackingId,
                'sgMessageId'  => $sgMessageId,
                'eventType'    => $eventType,
            ]);
        }

        return response()->json(['status' => 'ok']);
    }
}
