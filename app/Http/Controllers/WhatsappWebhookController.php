<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
//MODELOS
use App\Models\Empresa\EnvioEmail;
use App\Models\Empresa\EnvioEmailDetalle;

class WhatsappWebhookController extends Controller
{
    public function handle(Request $request)
    {
        $events = json_decode($request->getContent(), true);
        Log::info($events);

        // foreach ($events as $event) {

        //     $sgMessageId = $event['sg_message_id'] ?? null;
        //     $sgEventId = $event['sg_event_id'] ?? null;
        //     $smtpId = $event['smtp-id'] ?? null;
        //     $smtpId = $smtpId ? trim($smtpId, '<>') : null;
        //     $eventType = $event['event'] ?? null;
        //     $email = $event['email'] ?? null;
        //     $timestamp = $event['timestamp'] ?? null;

        //     $trackingId = null;

        //     // Opción 1: extraer de sg_message_id
        //     if ($sgMessageId && str_contains($sgMessageId, '.')) {
        //         $trackingId = explode('.', $sgMessageId)[0];
        //         $sgMessageId = $trackingId;
        //     } elseif ($sgMessageId) {
        //         $trackingId = $sgMessageId;
        //     }
        //     // Opción 2: usar smtp-id como fallback si no hay sg_message_id válido
        //     elseif ($smtpId && str_contains($smtpId, '@') === false) {
        //         $trackingId = $smtpId;
        //     }

        //     // Buscar por message_id en la base de datos
        //     $envio = EnvioEmail::where('sg_message_id', $trackingId)->first();

        //     if (!$envio) {
        //         $envio = EnvioEmail::where('sg_message_id', $smtpId)->first();
        //     }

        //     if ($envio) {

        //         if ($eventType == "delivered") {
        //             $envio->status = "enviado";
        //         }

        //         if ($eventType == "open") {
        //             $envio->status = "abierto";
        //         }

        //         if ($eventType == "bounce") {
        //             $envio->status = "rechazado";
        //         }

        //         $envio->sg_message_id = $sgMessageId;
        //         $envio->save();

        //         EnvioEmailDetalle::create([
        //             'id_email' => $envio->id,
        //             'email' => $email,
        //             'event' => $eventType,
        //             'sg_event_id' => $sgEventId,
        //             'sg_message_id' => $sgMessageId,
        //             'smtp_id' => $smtpId,
        //             'timestamp' => $timestamp
        //         ]);
        //     } else {
        //         Log::error('No se encontro correo relacionado', [
        //             'event' => $event,
        //         ]);
        //     }
        // }

        return response()->json(['status' => 'ok']);
    }
}
