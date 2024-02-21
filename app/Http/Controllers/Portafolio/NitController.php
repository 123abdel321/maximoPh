<?php

namespace App\Http\Controllers\Portafolio;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class NitController extends Controller
{
    public function index ()
    {
        return view('pages.tablas.nits.nits-view');
    }

}
