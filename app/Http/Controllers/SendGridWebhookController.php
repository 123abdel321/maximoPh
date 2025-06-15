<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SendGridWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $events = $request->all(); // esto puede ser un array de eventos

        foreach ($events as $event) {

            $customId = $event['headers']['X-Custom-Message-ID'] ?? null;
            Log::warning("Custom ID: {$customId}");
            // Puedes manejar eventos como:
            // $event['event'] === 'delivered', 'bounce', 'open', etc.
            Log::info('SendGrid Event:', $event);
        }

        return response()->json(['status' => 'ok']);
    }
}
