<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
//MODELS
use App\Models\Sistema\Entorno;

class PlacetoPayNotificationController extends Controller
{
    public function handle(Request $request)
    {
        $data = $request->all();

        Log::info('Notificación PlacetoPay recibida', $data);

        // Validación básica de campos necesarios
        if (!isset($data['status'], $data['status']['status'], $data['status']['date'], $data['requestId'], $data['signature'])) {
            return response('Bad Request', 400);
        }

        $requestId = $data['requestId'];
        $status = $data['status']['status'];
        $statusDate = $data['status']['date'];
        $signatureFromRequest = $data['signature'];

        $secretKey = config('services.placetopay.secret'); // Define tu clave secreta en config/services.php

        $generatedSignature = sha1($requestId . $status . $statusDate . $secretKey);

        // Validar la firma
        if ($generatedSignature !== $signatureFromRequest) {
            Log::warning("Firma inválida en notificación de PlacetoPay", ['requestId' => $requestId]);
            return response('Unauthorized', 401);
        }

        // Aquí deberías buscar el pago en tu sistema por reference o requestId y actualizar su estado
        // Ejemplo:
        /*
        $pago = Pago::where('request_id', $requestId)->first();
        if ($pago) {
            $pago->estado = $status;
            $pago->save();
        }
        */

        // Opcional: envía a un job si es mucha lógica

        return response('OK', 200);
    }
}
