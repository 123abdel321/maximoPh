<?php

namespace App\Http\Controllers\Portafolio;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Portafolio\Nits;
use App\Models\Portafolio\Comprobantes;


class ReciboController extends Controller
{
    public function index ()
    {
        $data = [
            'comprobantes' => Comprobantes::where('tipo_comprobante', Comprobantes::TIPO_INGRESOS)->get()
        ];

        return view('pages.operaciones.recibo.recibo-view', $data);
    }

}
