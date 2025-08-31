<?php

namespace App\Http\Controllers\Sistema;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//HELPERS
use App\Helpers\Printers\PazSalvoPdf;
//MODELOS
use App\Models\Empresa\Empresa;
use App\Models\Empresa\UsuarioEmpresa;

class PazSalvoController extends Controller
{
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
