<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Empresa\UsuarioEmpresa;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\ArchivosCache;

class ArchivosCacheController extends Controller
{

    public function store (Request $request)
    {
        try {
            if ($request->hasFile('images')) {

                $uniqueName = uniqid();
                $file = $request->file('images')[0];
                $mimeType = $file->getMimeType(); 
                $extensionType = $file->getClientOriginalExtension();
                $nameFile = "{$uniqueName}.{$extensionType}";

                $path = Storage::disk('do_spaces')->putFileAs(
                    "archivos-cache",
                    $file,
                    $nameFile,
                    'public'
                );

                $url = Storage::disk('do_spaces')->url($path);

                $archivo = ArchivosCache::create([
                    'tipo_archivo' => $mimeType,
                    'name_file' => $nameFile,
                    'relative_path' => 'archivos-cache/'.$nameFile,
                    'url_archivo' => $url,
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                return response()->json([
                    'success'=>	true,
                    'path' => $url,
                    'id' => $archivo->id,
                    'message'=> 'Archivo cargado con exito'
                ], 200);
            }
            return response()->json([
                'success'=>	false,
                'url' => '',
                'message'=> 'Sin archivos para cargar'
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

}