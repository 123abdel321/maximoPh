<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Empresa\EnvioEmail;
use App\Models\Empresa\EnvioEmailDetalle;

class NotificacionController extends Controller
{

    public function index (Request $request)
    {

        $ecoToken = Entorno::where('nombre', 'eco_login')->first();
        $ecoToken = $ecoToken->valor ?? null;

        $data = [
            'tokenEco' => $ecoToken
        ];

        dd($data);

        return view('pages.administrativo.notificaciones.notificaciones-view', $data);
    }
}