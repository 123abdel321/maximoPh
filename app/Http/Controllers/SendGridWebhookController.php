<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
//MODELOS
use App\Models\Empresa\EnvioEmail;
use App\Models\Empresa\EnvioEmailDetalle;

class SendGridWebhookController extends Controller
{
    // public function handle(Request $request)
    // {
    //     $events = json_decode($request->getContent(), true);
    //     Log::info('No se encontro correo relacionado', [
    //         'events' => $events,
    //     ]);
    //     foreach ($events as $event) {

    //         $sgMessageId = $event['sg_message_id'] ?? null;
    //         $sgEventId = $event['sg_event_id'] ?? null;
    //         $smtpId = $event['smtp-id'] ?? null;
    //         $smtpId = $smtpId ? trim($smtpId, '<>') : null;
    //         $eventType = $event['event'] ?? null;
    //         $email = $event['email'] ?? null;
    //         $timestamp = $event['timestamp'] ?? null;

    //         $trackingId = null;

    //         // Opción 1: extraer de sg_message_id
    //         if ($sgMessageId && str_contains($sgMessageId, '.')) {
    //             $trackingId = explode('.', $sgMessageId)[0];
    //             $sgMessageId = $trackingId;
    //         } elseif ($sgMessageId) {
    //             $trackingId = $sgMessageId;
    //         }
    //         // Opción 2: usar smtp-id como fallback si no hay sg_message_id válido
    //         elseif ($smtpId && str_contains($smtpId, '@') === false) {
    //             $trackingId = $smtpId;
    //         }

    //         // Buscar por message_id en la base de datos
    //         $envio = EnvioEmail::where('sg_message_id', 'LIKE', $trackingId."%")->first();

    //         if (!$envio) {
    //             $envio = EnvioEmail::where('sg_message_id', $smtpId)->first();
    //         }

    //         if (!$envio) {
    //             $envio = EnvioEmail::where('sg_message_id', $event['sg_message_id'])->first();
    //         }

    //         if ($envio) {

    //             if ($eventType == "delivered") {
    //                 $envio->status = "enviado";
    //             }

    //             if ($eventType == "open") {
    //                 $envio->status = "abierto";
    //             }

    //             if ($eventType == "bounce") {
    //                 $envio->status = "rechazado";
    //             }

    //             $envio->sg_message_id = $sgMessageId;
    //             $envio->save();

    //             EnvioEmailDetalle::create([
    //                 'id_email' => $envio->id,
    //                 'email' => $email,
    //                 'event' => $eventType,
    //                 'sg_event_id' => $sgEventId,
    //                 'sg_message_id' => $sgMessageId,
    //                 'smtp_id' => $smtpId,
    //                 'timestamp' => $timestamp
    //             ]);
    //         } else {
    //             Log::error('No se encontro correo relacionado', [
    //                 'event' => $event,
    //             ]);
    //         }
    //     }

    //     return response()->json(['status' => 'ok']);
    // }

    public function handle(Request $request)
    {
        $payload = json_decode($request->getContent(), true);

        Log::info('Webhook recibido', [
            'payload' => $payload
        ]);

        if (!isset($payload['event-data'])) {
            Log::warning('Payload inválido');
            return response()->json(['status' => 'invalid'], 400);
        }

        $event = $payload['event-data'];

        // Datos reales del payload
        $eventType  = $event['event'] ?? null;
        $email      = $event['recipient'] ?? null;
        $timestamp  = $event['timestamp'] ?? null;

        $messageId = $event['message']['headers']['message-id'] ?? null;
        $messageId = $messageId ? trim($messageId, '<>') : null;

        if (!$messageId) {
            Log::warning('Evento sin message-id', ['event' => $event]);
            return response()->json(['status' => 'ok']);
        }

        // Buscar el correo enviado
        $envio = EnvioEmail::where('sg_message_id', 'LIKE', "%{$messageId}%")->first();

        if (!$envio) {
            Log::error('No se encontró correo relacionado', [
                'message_id' => $messageId,
                'email' => $email
            ]);
            return response()->json(['status' => 'ok']);
        }

        // Mapear estados
        if ($eventType === 'accepted') {
            $envio->status = 'aceptado';
        }

        if ($eventType === 'delivered') {
            $envio->status = 'enviado';
        }

        if ($eventType === 'opened') {
            $envio->status = 'abierto';
        }

        if (in_array($eventType, ['failed', 'rejected', 'bounced'])) {
            $envio->status = 'rechazado';
        }

        $envio->save();

        // Guardar detalle
        EnvioEmailDetalle::create([
            'id_email'      => $envio->id,
            'email'         => $email,
            'event'         => $eventType,
            'sg_event_id'   => null,
            'sg_message_id' => $messageId,
            'smtp_id'       => null,
            'timestamp'     => $timestamp,
        ]);

        return response()->json(['status' => 'ok'], 200);
    }

}
