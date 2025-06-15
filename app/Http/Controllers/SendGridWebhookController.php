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

            Log::info($event);

            $trackingId = $event['headers']['X-Maximoph-Tracking-ID'] ?? null;
            $sgMessageId = $event['sg_message_id'] ?? null;
            $eventType = $event['event'] ?? null;

            Log::warning("trackingId: {$trackingId}");
            Log::warning("sgMessageId: {$sgMessageId}");
            Log::warning("eventType: {$eventType}");
        }

        return response()->json(['status' => 'ok']);
    }
}
