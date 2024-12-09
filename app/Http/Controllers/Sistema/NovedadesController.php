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
use App\Models\Portafolio\Nits;
use App\Models\Sistema\Porteria;
use App\Models\Sistema\Novedades;
use App\Models\Sistema\InmuebleNit;
use App\Models\Sistema\ArchivosCache;
use App\Models\Sistema\ArchivosGenerales;

class NovedadesController extends Controller
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

    public function index (Request $request)
    {
        return view('pages.administrativo.novedades.novedades-view');
    }

    public function read (Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $searchValue = $request->get('search');

            $novedades = Novedades::orderBy('id', 'DESC')
                ->with([
                    'archivos',
                    'responsable.nit',
                    'responsable.archivos',
                    'responsable.inmueble.zona',
                ])
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion"),
                    'created_by',
                    'updated_by'
                );

            $totalNovedades = $novedades->count();
            $novedadesPaginate = $novedades->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $totalNovedades,
                'iTotalDisplayRecords' => $totalNovedades,
                'data' => $novedadesPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Novedades generados con exito!'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function create (Request $request)
    {
        $rules = [
            'id_porteria' => 'required',
            'area' => 'required',
            'tipo' => 'required',
            'fecha' => 'required',
            'asunto' => 'required',
            'mensaje' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        try {
            DB::connection('max')->beginTransaction();

            $novedad = Novedades::create([
                'id_porteria' => $request->get('id_porteria'),
                'area' => $request->get('area'),
                'tipo' => $request->get('tipo'),
                'fecha' => $request->get('fecha'),
                'asunto' => $request->get('asunto'),
                'mensaje' => $request->get('mensaje'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            $archivos = $request->get('archivos');

            if (count($archivos)) {
                foreach ($archivos as $archivo) {
                    $archivoCache = ArchivosCache::where('id', $archivo['id'])->first();
                    $finalPath = 'maximo/empresas/'.request()->user()->id_empresa.'/imagen/novedades/'.$archivoCache->name_file;
                    if (Storage::exists($archivoCache->relative_path)) {
                        Storage::move($archivoCache->relative_path, $finalPath);
                        
                        $archivo = new ArchivosGenerales([
                            'tipo_archivo' => $archivoCache->tipo_archivo,
                            'url_archivo' => $finalPath,
                            'estado' => 1,
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);
                        $archivo->relation()->associate($novedad);
                        $novedad->archivos()->save($archivo);
                    }
                    $archivoCache->delete();
                }
            }

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $novedad,
                'message'=> 'Novedad creada con exito!'
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

    public function update (Request $request)
    {
        $rules = [
            'id' => 'required|exists:max.novedades,id',
            'id_porteria' => 'required',
            'area' => 'required',
            'tipo' => 'required',
            'fecha' => 'required',
            'asunto' => 'required',
            'mensaje' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        try {
            DB::connection('max')->beginTransaction();

            Novedades::where('id', $request->get('id'))
                ->update([
                    'id_porteria' => $request->get('id_porteria'),
                    'area' => $request->get('area'),
                    'tipo' => $request->get('tipo'),
                    'fecha' => $request->get('fecha'),
                    'asunto' => $request->get('asunto'),
                    'mensaje' => $request->get('mensaje'),
                    'updated_by' => request()->user()->id
                ]);

            $novedad = Novedades::where('id', $request->get('id'))->first();

            $archivos = $request->get('archivos');

            if (count($archivos)) {
                foreach ($archivos as $archivo) {
                    $archivoCache = ArchivosCache::where('id', $archivo['id'])->first();
                    if (!$archivoCache) continue;
                    $finalPath = 'maximo/empresas/'.request()->user()->id_empresa.'/imagen/novedades/'.$archivoCache->name_file;
                    if (Storage::exists($archivoCache->relative_path)) {
                        Storage::move($archivoCache->relative_path, $finalPath);
                        
                        $archivo = new ArchivosGenerales([
                            'tipo_archivo' => $archivoCache->tipo_archivo,
                            'url_archivo' => $finalPath,
                            'estado' => 1,
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);
                        $archivo->relation()->associate($novedad);
                        $novedad->archivos()->save($archivo);
                    }
                    $archivoCache->delete();
                }
            }

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $novedad,
                'message'=> 'Novedad actualizada con exito!'
            ], 200);                
        } catch (Exception $e) {
            DB::connection('max')->rollback();
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function delete (Request $request)
    {
        $rules = [
            'id' => 'required|exists:max.novedades,id',
        ];

        $validator = Validator::make($request->all(), $rules, $this->messages);

		if ($validator->fails()){
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$validator->errors()
            ], 422);
        }

        try {
            DB::connection('max')->beginTransaction();

            Novedades::where('id', $request->get('id'))->delete();

            $files = ArchivosGenerales::where('relation_type', 16)
                ->where('relation_id', $request->get('id'))
                ->get();
                
            if (count($files)) {
                foreach ($files as $file) {
                    Storage::disk('do_spaces')->delete($file->url_archivo);
                    $file->delete();
                }
            }

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Novedad eliminada con exito!'
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

}