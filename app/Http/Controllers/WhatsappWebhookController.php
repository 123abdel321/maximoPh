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
        Log::info($request->all());

        $envio = EnvioEmail::where('sg_message_id', $request->MessageSid)->first();

        if (!$envio) {
            Log::error("No se encontro whatsaap relaciado $request->MessageSid");
            return;
        }

        $status = 'rechazado';

        if ($request->SmsStatus == 'sent') {
            $status = 'enviado';
        }

        if ($request->SmsStatus == 'delivered') {
            $status = 'entregado';
        }

        if ($request->SmsStatus == 'read') {
            $status = 'abierto';
        }
        
        $envio->status = $status;
        $envio->save();

        EnvioEmailDetalle::create([
            'id_email' => $envio->id,
            'email' => $request->To,
            'event' => $status,
            'sg_message_id' => $request->MessageSid,
            'timestamp' => $timestamp
        ]);

        return response()->json(['status' => 'ok']);
    }
}
