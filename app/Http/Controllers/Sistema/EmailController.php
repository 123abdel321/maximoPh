<?php

namespace App\Http\Controllers\Sistema;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
//MODELS
use App\Models\Empresa\EnvioEmail;
use App\Models\Empresa\EnvioEmailDetalle;

class EmailController extends Controller
{

    public function index (Request $request)
    {
        return view('pages.administrativo.email.email-view');
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
            $search_arr = $request->get('search');

            $searchValue = $search_arr['value']; // Search value

            $emails = EnvioEmail::with('nit')
                ->orderBy('id', 'DESC')
                ->where('id_empresa', request()->user()->id_empresa)
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion")
                );

            if ($request->get('id_nit')) {
                $emails->where('id_nit', $request->get('id_nit'));
            }

            if ($request->get('estado')) {
                $emails->where('status', $request->get('estado'));
            }

            if ($request->get('fecha_desde') && $request->get('fecha_hasta')) {
                $emails->whereBetween('created_at', [
                    $request->get('fecha_desde') . ' 00:00:00',
                    $request->get('fecha_hasta') . ' 23:59:59',
                ]);
            }

            $emailsTotals = $emails->get();

            $emailsPaginate = $emails->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $emailsTotals->count(),
                'iTotalDisplayRecords' => $emailsTotals->count(),
                'data' => $emailsPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Emails generados con exito!'
            ]);


        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }

    public function readDetalle (Request $request)
    {
        try {
            $draw = $request->get('draw');
            $start = $request->get("start");
            $rowperpage = $request->get("length");

            $columnIndex_arr = $request->get('order');
            $columnName_arr = $request->get('columns');
            $order_arr = $request->get('order');
            $search_arr = $request->get('search');

            $searchValue = $search_arr['value']; // Search value

            $emails = EnvioEmailDetalle::orderBy('id', 'DESC')
                ->whereHas('email', function ($query) {
                    $query->where('id_empresa', request()->user()->id_empresa);
                })
                ->where('id_email', $request->get('id'))
                ->select(
                    '*',
                    DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %T') AS fecha_creacion"),
                    DB::raw("DATE_FORMAT(updated_at, '%Y-%m-%d %T') AS fecha_edicion")
                );

            $emailsTotals = $emails->get();

            $emailsPaginate = $emails->skip($start)
                ->take($rowperpage);

            return response()->json([
                'success'=>	true,
                'draw' => $draw,
                'iTotalRecords' => $emailsTotals->count(),
                'iTotalDisplayRecords' => $emailsTotals->count(),
                'data' => $emailsPaginate->get(),
                'perPage' => $rowperpage,
                'message'=> 'Emails detalle generado con exito!'
            ]);


        } catch (Exception $e) {
            return response()->json([
                "success"=>false,
                'data' => [],
                "message"=>$e->getMessage()
            ], 422);
        }
    }
}