<?php

namespace App\Http\Controllers\Portafolio;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CarteraController extends Controller
{
    public function index ()
    {
        $data = [
            'ubicacion_maximoph' => 1,
        ];

        return view('pages.informes.cartera.cartera-view', $data);
    }

}
