<?php

namespace App\Http\Controllers\Portafolio;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Entorno;
use App\Models\Portafolio\Nits;
use App\Models\Portafolio\Comprobantes;
use App\Models\Sistema\ConceptoFacturacion;


class ReciboController extends Controller
{
    public function index ()
    {
        $id_cuenta_intereses = Entorno::where('nombre', 'id_cuenta_intereses')->first()->valor;
        $ordenCuentas = ConceptoFacturacion::select('id_cuenta_cobrar')
            ->orderBy('orden', 'ASC')
            ->pluck('id_cuenta_cobrar')
            ->toArray();

        array_unshift($ordenCuentas, $id_cuenta_intereses);
        $ordenCuentas = array_flip($ordenCuentas);
        
        $data = [
            'comprobantes' => Comprobantes::where('tipo_comprobante', Comprobantes::TIPO_INGRESOS)->get(),
            'ordenCuentas' => $ordenCuentas
        ];

        return view('pages.operaciones.recibo.recibo-view', $data);
    }

    public function indexPagos ()
    {
        return view('pages.operaciones.pago_transferencia.pago_transferencia-view'); 
    }

}
