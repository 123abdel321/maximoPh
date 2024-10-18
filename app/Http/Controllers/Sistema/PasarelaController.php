<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Events\PrivateMessageEvent;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Helpers\PlacetoPay\PaymentStatus;
use App\Helpers\PlacetoPay\PaymentRequest;
//MODELS
use App\Models\Portafolio\ConRecibos;

class PasarelaController extends Controller
{
    public function close (Request $request, string $code)
    {
        $code = explode('_', base64_decode($code));

        if (count($code) == 3) {
            $recibo = ConRecibos::where('id', $code[0])->first();

            $response = (new PaymentStatus(
                $recibo->request_id
            ))->send(request()->user()->id_empresa);

            if ($response->status < 300) {
                $status = (object)$response->response->status;
                $tipo_mesaje = 'info';

                switch ($status->status) {
                    case 'APPROVED':
                        $tipo_mesaje = 'exito';
                        $recibo->estado = 1;
                        $recibo->observacion = $status->message;
                        $recibo->save();
                        break;
                    case 'PENDING':
                        //AGREGAR JOB PARA VALIDAR PAGO
                        $recibo->observacion = $status->message;
                        $recibo->save();
                        break;
                    case 'REJECTED':
                        $tipo_mesaje = 'warning';
                        $recibo->estado = 0;
                        $recibo->observacion = $status->message;
                        $recibo->save();
                        break;
                    case 'PARTIAL_EXPIRED':
                        //AGREGAR JOB PARA VALIDAR PAGO ??
                        $recibo->observacion = $status->message;
                        $recibo->save();
                        break;
                    case 'APPROVED_PARTIAL':
                        //AGREGAR JOB PARA VALIDAR PAGO ??
                        $recibo->observacion = $status->message;
                        $recibo->save();
                        break;
                    default:
                        break;
                }

                $user_id = $request->user()->id;
                $has_empresa = $request->user()['has_empresa'];

                event(new PrivateMessageEvent('estado-cuenta-'.$has_empresa.'_'.$user_id, [
                    'success'=>	true,
                    'accion' => 2,
                    'tipo' => $tipo_mesaje,
                    'mensaje' => $status->message,
                    'titulo' => 'Actualización de pago',
                    'autoclose' => false
                ]));
            }
        }

        return view('pages.close');
    }

    public function create (Request $request)
    {
        try {
            $recibo = ConRecibos::first();

            $response = (new PaymentRequest(
                $recibo->id
            ))->send(request()->user()->id_empresa);

            if ($response['status'] > 299) {//VALIDAR ERRORES PORTAFOLIO
                DB::connection('max')->rollback();
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=> $response['response']->message
                ], 422);
            }

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Pago creado con exito!'
            ]);
                
        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function status (Request $request)
    {
        $recibo = ConRecibos::where('id', 2175)->first();
        // dd($recibo);
        $response = (new PaymentStatus(
            $recibo->request_id
        ))->send(request()->user()->id_empresa);
    }
}