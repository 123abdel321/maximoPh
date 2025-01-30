<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\Empresa\UsuarioEmpresa;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\ArchivosCache;
use App\Models\Sistema\ArchivosGenerales;

class ArchivosCacheController extends Controller
{

    public function store (Request $request)
    {
        try {
            if ($request->hasFile('images')) {

                $file = $request->file('images')[0];

                $mimeType = $file->getMimeType();
                $extensionType = $file->getClientOriginalExtension();

                $path = Storage::disk('do_spaces')->putFileAs(
                    "archivos-cache",
                    $file,
                    $file->getClientOriginalName(),
                    'public'
                );

                $url = Storage::disk('do_spaces')->url($path);

                $archivo = ArchivosCache::create([
                    'tipo_archivo' => $mimeType,
                    'name_file' => $file->getClientOriginalName(),
                    'relative_path' => 'archivos-cache/'.$file->getClientOriginalName(),
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
            $idUsuario = request()->user()->id;
            Log::info("Usuario id: {$idUsuario}, cargo archivo muy pesado");
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

    public function delete (Request $request)
    {
        $content = $request->getContent();
        
        try {
            $file = ArchivosCache::where('url_archivo', $content)
                ->first();
                
            if ($file) {
                Storage::disk('do_spaces')->delete($file->url_archivo);
                $file->delete();
            }

            return response()->json([
                'success'=>	true,
                'message'=> 'Archivo eliminado con exito'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function deleteFile (Request $request)
    {
        try {
            $file = ArchivosGenerales::where('relation_type', $request->get('relationType'))
                ->where('id', $request->get('id'))
                ->first();
                
            if ($file) {
                $urlDelete = $file->url_archivo;
                $file->delete();
                Storage::disk('do_spaces')->delete($urlDelete);
            } else {
                return response()->json([
                    'success'=>	true,
                    'message'=> 'El archivo no existes'
                ], 200);
            }

            return response()->json([
                'success'=>	true,
                'message'=> 'Archivo eliminado con exito'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

}