<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Sistema\Turno;
use App\Models\Sistema\TurnoEvento;
use App\Models\Empresa\UsuarioEmpresa;
use App\Models\Sistema\ArchivosGenerales;

class TurnosController extends Controller
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

    public function index ()
    {
        return view('pages.tareas.turnos.turnos-view');
    }

    public function read (Request $request)
    {
        $start =  Carbon::parse($request->start);
        $end = Carbon::parse($request->end);
        
        $data = array();
        $idResponsable = $request->id_empleado == "null" ? null : $request->id_empleado;
        $tipo = $request->tipo;
        $estado = $request->estado;

        $turnos = Turno::where(function($query) use ($start, $end) {
            $query->whereBetween('fecha_inicio', [$start, $end])
                ->orWhereBetween('fecha_fin', [$start, $end])
                ->orWhere(function($query) use ($start, $end) {
                    $query->where('fecha_inicio', '<=', $start)
                        ->where('fecha_fin', '>=', $end);
                });
            })
            ->when($idResponsable, function ($query) use($idResponsable) {
				$query->where('id_usuario', $idResponsable);
			})
            ->when($tipo, function ($query) use($tipo) {
				$query->where('tipo', $tipo);
			})
            ->when($estado, function ($query) use($estado) {
				$query->where('estado', $estado);
			})
        ->get();

        foreach ($turnos as $turno) {
            $fechaInicio = Carbon::parse($turno->fecha_inicio)->format('Y-m-d');
            $fechaFin = Carbon::parse($turno->fecha_fin)->format('Y-m-d');

            $horaInicio = Carbon::parse($turno->fecha_inicio)->format('H:i:s');
            $horaFin = Carbon::parse($turno->fecha_fin)->format('H:i:s');

            $color = "#055ebe";

            // if ($turno->estado == 1) {

            // }

            array_push($data, array(
                // 'backgroundColor' => $color,
                // 'borderColor' => $color,
                'id' => $turno->id,
                'title' => $turno->asunto,
                'start' => $horaInicio == "00:00:00" ? $fechaInicio : $fechaInicio.' '.$horaInicio,
                'end' => $horaFin == "00:00:00" ? $fechaFin : $fechaFin.' '.$horaFin,
            ));
        }

        return response()->json($data);
    }

    public function create (Request $request)
    {
        $rules = [
            'id_usuario_turno' => 'required|exists:clientes.users,id',
            'id_proyecto_turno' => 'nullable|exists:max.proyectos,id',
            'tipo_turno' => 'nullable',
            'fecha_inicio_turno' => 'required',
            'fecha_fin_turno' => 'required',
            'hora_inicio_turno' => 'required',
            'hora_fin_turno' => 'required',
            'asunto_turno' => 'required',
            'mensaje_turno' => 'required'
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

            $usuarioEmpresa = UsuarioEmpresa::with('usuario', 'nit')
                ->where('id_usuario', $request->get('id_usuario_turno'))
                ->where('id_empresa', request()->user()->id_empresa)
                ->first();

            if (!$usuarioEmpresa->id_nit) {
                return response()->json([
                    "success"=>false,
                    'data' => [],
                    "message"=>'El usuario no tiene nit asociado en la empresa'
                ], 422);
            }
            
            $urlArchivos = [];
            if ($request->file('photos')) {
                foreach ($request->file('photos') as $photos) {
                    $nameFile = 'maximo/empresas/'.request()->user()->id_empresa.'/imagen/turnos';
                    $url = Storage::disk('do_spaces')->put($nameFile, $photos, 'public');

                    array_push($urlArchivos, $url);
                }
            }

            if ($request->get('multiple_tarea_turno') == 'on') {
                $dias = $this->getDiasString($request);

                $inicio = Carbon::parse($request->get('fecha_inicio_turno'));
                $fin = Carbon::parse($request->get('fecha_fin_turno'));

                while ($inicio->lte($fin)) {
                    $numero_dia = $inicio->isoWeekday();

                    if (in_array($numero_dia, $dias)) {

                        $fechaInicio = $inicio->format('Y-m-d').' '.$request->get('hora_inicio_turno');
                        $fechaFin = $inicio->format('Y-m-d').' '.$request->get('hora_fin_turno');

                        $turno = Turno::create([
                            'id_usuario' => $request->get('id_usuario_turno'),
                            'id_nit' => $usuarioEmpresa->id_nit,
                            'id_proyecto' => $request->get('id_proyecto_turno'),
                            'tipo' => $request->get("tipo_turno"),
                            'fecha_inicio' => $fechaInicio,
                            'fecha_fin' => $fechaFin,
                            'asunto' => $request->get("asunto_turno"),
                            'descripcion' => $request->get("mensaje_turno"),
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);

                        if (count($urlArchivos)) {
                            foreach ($urlArchivos as $url) {
                                $archivo = new ArchivosGenerales([
                                    'tipo_archivo' => 'imagen',
                                    'url_archivo' => $url,
                                    'estado' => 1,
                                    'created_by' => request()->user()->id,
                                    'updated_by' => request()->user()->id
                                ]);
                    
                                $archivo->relation()->associate($turno);
                                $turno->archivos()->save($archivo);
                            }
                        }
                    }

                    $inicio->addDay();
                }
            } else {
                
                $fechaInicio = $request->get("fecha_inicio_turno").' '.$request->get("hora_inicio_turno");
                $fechaFin = $request->get("fecha_fin_turno").' '.$request->get("hora_fin_turno");

                $turno = Turno::create([
                    'id_usuario' => $request->get('id_usuario_turno'),
                    'id_nit' => $usuarioEmpresa->id_nit,
                    'id_proyecto' => $request->get('id_proyecto_turno'),
                    'tipo' => $request->get("tipo_turno"),
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'asunto' => $request->get("asunto_turno"),
                    'descripcion' => $request->get("mensaje_turno"),
                    'created_by' => request()->user()->id,
                    'updated_by' => request()->user()->id
                ]);

                if (count($urlArchivos)) {
                    foreach ($urlArchivos as $url) {
                        $archivo = new ArchivosGenerales([
                            'tipo_archivo' => 'imagen',
                            'url_archivo' => $url,
                            'estado' => 1,
                            'created_by' => request()->user()->id,
                            'updated_by' => request()->user()->id
                        ]);
            
                        $archivo->relation()->associate($turno);
                        $turno->archivos()->save($archivo);
                    }
                }
            }

            DB::connection('max')->commit();

            $fechaInicio = Carbon::parse($turno->fecha_inicio)->format('Y-m-d');
            $fechaFin = Carbon::parse($turno->fecha_fin)->format('Y-m-d');
            
            $horaInicio = "00:00:00";
            $horaFin = "00:00:00";
            
            if ($turno->fecha_inicio) $horaInicio = Carbon::parse($turno->fecha_inicio)->format('H:i:s');
            if ($turno->fecha_fin) $horaFin = Carbon::parse($turno->fecha_fin)->format('H:i:s');

            $turnoData = (object)[
                'id' => $turno->id,
                'title' => $turno->asunto,
                'start' => $horaInicio == "00:00:00" ? $fechaInicio : $fechaInicio.' '.$horaInicio,
                'end' => $horaFin == "00:00:00" ? $fechaFin : $fechaFin.' '.$horaFin,
            ];

            return response()->json([
                'success'=>	true,
                'data' => $turnoData,
                'message'=> 'Turno creado con exito!'
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
            'id' => 'required|exists:max.turnos,id',
            'fecha_inicio' => 'required',
            'fecha_fin' => 'required',
            'hora_inicio' => 'required',
            'hora_fin' => 'required'
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

            $fechaInicio = $request->get("fecha_inicio").' '.$request->get("hora_inicio");
            $fechaFin = $request->get("fecha_fin").' '.$request->get("hora_fin");

            Turno::where('id', $request->get('id'))
                ->update([
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'updated_by' => request()->user()->id
                ]);

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Turno actualizado con exito!'
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

    public function find (Request $request)
    {
        $rules = [
            'id' => 'required|exists:max.turnos,id'
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

            $turno = Turno::where('id', $request->get('id'))
                ->with('responsable', 'archivos', 'eventos.creador', 'eventos.archivos', 'creador')
                ->first();

            return response()->json([
                'success'=>	true,
                'data' => $turno,
                'message'=> 'Turno encontrado con exito!'
            ]);

        } catch (Exception $e) {

            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function createEvento (Request $request)
    {
        $rules = [
            'id_turno_evento' => 'required|exists:max.turnos,id',
            'mensaje_turno_evento' => 'nullable',
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

            $turnoEvento = TurnoEvento::create([
                'id_turno' => $request->get('id_turno_evento'),
                'id_usuario' => request()->user()->id,
                'descripcion' => $request->get('mensaje_turno_evento'),
                'created_by' => request()->user()->id,
                'updated_by' => request()->user()->id
            ]);

            if ($request->file('photos')) {
                foreach ($request->file('photos') as $photos) {
                    $nameFile = 'maximo/empresas/'.request()->user()->id_empresa.'/imagen/turnos';
                    $url = Storage::disk('do_spaces')->put($nameFile, $photos, 'public');
    
                    $archivo = new ArchivosGenerales([
                        'tipo_archivo' => 'imagen',
                        'url_archivo' => $url,
                        'estado' => 1,
                        'created_by' => request()->user()->id,
                        'updated_by' => request()->user()->id
                    ]);
        
                    $archivo->relation()->associate($turnoEvento);
                    $turnoEvento->archivos()->save($archivo);
                }
            }

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => $turnoEvento,
                'message'=> 'Turno evento creado con exito!'
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

    public function delete (Request $request)
    {
        $rules = [
            'id' => 'required|exists:max.turnos,id',
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

            Turno::where('id', $request->get('id'))->delete();
            TurnoEvento::where('id_turno', $request->get('id'))->delete();

            DB::connection('max')->commit();

            return response()->json([
                'success'=>	true,
                'data' => [],
                'message'=> 'Zona eliminada con exito!'
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

    private function getDiasString ($request)
    {
        $dias = [];
        for ($i = 1; $i <= 7; $i++) {
            if ($request->get('diaTurno'.$i)) {
                array_push($dias, $i);
            }
        }
        return $dias;
    }

}