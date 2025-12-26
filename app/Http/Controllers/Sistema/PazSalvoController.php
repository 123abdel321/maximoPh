<?php

namespace App\Http\Controllers\Sistema;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//HELPERS
use App\Helpers\Printers\PazSalvoPdf;
//MODELOS
use App\Models\Empresa\Empresa;
use App\Models\Empresa\UsuarioEmpresa;

class PazSalvoController extends Controller
{

    protected $messages = null;

    public function __construct()
	{
		$this->messages = [
            'required' => 'El campo :attribute es requerido.',
            'exists' => 'El :attribute es inválido.',
            'numeric' => 'El campo :attribute debe ser un valor numérico.',
            'string' => 'El campo :attribute debe ser texto',
            'array' => 'El campo :attribute debe ser un arreglo.',
            'date' => 'El campo :attribute debe ser una fecha válida.',
        ];
	}

    public function showPdfPersonal(Request $request)
    {
        $empresa = Empresa::where('token_db_maximo', $request->user()['has_empresa'])->first();
        
        if (!$empresa) {
            return response()->json([
                'message'=> 'La empresa no existe!'
            ]);
        }

        $usuarioEmpresa = UsuarioEmpresa::where('id_empresa', $empresa->id)
            ->where('id_usuario', $request->user()->id)
            ->first();

        if (!$usuarioEmpresa) {
            return response()->json([
                'message'=> 'El usuario no esta asociado a una empresa!'
            ]);
        }

        if (!$usuarioEmpresa->id_nit) {
            return response()->json([
                'message'=> 'El usuario no tiene un nit asociado para consultar!'
            ]);
        }

        

        return (new PazSalvoPdf($empresa, $usuarioEmpresa->id_nit))
            ->buildPdf()
            ->showPdf();
        
        // return view('pdf.facturacion.facturaciones', $data);
    }

    public function showPdfNit(Request $request)
    {
        
         $rules = [
            'id_nit' => 'required',
            'periodo' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        $empresa = Empresa::where('token_db_maximo', $request->user()['has_empresa'])->first();
        
        if (!$empresa) {
            return response()->json([
                'message'=> 'La empresa no existe!'
            ]);
        }

        // $data = (new PazSalvoPdf($empresa, $request->get('id_nit')))
        //     ->buildPdf()
        //     ->data();

        // dd($data);

        return (new PazSalvoPdf($empresa, $request->get('id_nit')))
            ->buildPdf()
            ->showPdf();
        
        // return view('pdf.facturacion.facturaciones', $data);
    }

    public function showPdfPublico(Request $request)
    {
        $idEmpresa = $request->code1;
        $idNit = $request->code2;

        if (!$idEmpresa || !$idNit) {
            return response()->json([
                'message'=> 'Dactos incorrectos!'
            ]);
        }

        $idEmpresa = base64_decode($request->code1);
        $idNit = base64_decode($request->code2);

        $empresa = Empresa::where('id', $idEmpresa)->first();
        
        if (!$empresa) {
            return response()->json([
                'message'=> 'La empresa no existe!'
            ]);
        }

        if (!$idNit) {
            return response()->json([
                'message'=> 'El nit no esta asociado a la compañia!'
            ]);
        }

        return (new PazSalvoPdf($empresa, $idNit))
            ->buildPdf()
            ->showPdf();
    }
}
