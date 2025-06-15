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

            $messageId = $event['sg_message_id'] ?? null;
            $eventType = $event['event'] ?? null;

            Log::warning("eventType: {$eventType}");
            Log::warning("messageId: {$messageId}");

        }

        // foreach ($events as $event) {

        //     $customId = $event['headers'];
        //     Log::warning("Custom ID: {$customId}");
        //     // Puedes manejar eventos como:
        //     // $event['event'] === 'delivered', 'bounce', 'open', etc.
        //     Log::info('SendGrid Event:', $event);
        // }

        return response()->json(['status' => 'ok']);
    }
}
