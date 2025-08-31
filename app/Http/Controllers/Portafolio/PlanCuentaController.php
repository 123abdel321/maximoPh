<?php

namespace App\Http\Controllers\Portafolio;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\Portafolio\PlanCuentas;

class PlanCuentaController extends Controller
{
    public function comboCuenta(Request $request)
    {
        $totalRows = $request->has("totalRows") ? $request->get("totalRows") : 40;
        $comprobante = NULL;
        $naturaleza = "naturaleza_cuenta";
        $select = [
            'id',
            'cuenta',
            'exige_nit',
            'exige_documento_referencia',
            'exige_concepto',
            'exige_centro_costos',
            'nombre',
            'auxiliar',
            DB::raw($naturaleza. ' AS naturaleza_cuenta'),
            DB::raw("CONCAT(cuenta, ' - ', nombre) as text")
        ];

        $planCuenta = PlanCuentas::select($select)->with('tipos_cuenta');
        
        if ($request->has("id_tipo_cuenta")) {
            if (!$request->has("total_cuentas")) {
                $planCuenta->where('auxiliar', 1);
            }
            $planCuenta->whereHas('tipos_cuenta', function ($query) use($request) {
                $query->whereIn('id_tipo_cuenta', $request->get('id_tipo_cuenta'));
            });
        }

        if ($request->has("auxiliar") && $request->get("auxiliar")) {
            $planCuenta->where('auxiliar', 1);
        }

        if ($request->get("search")) {
            $planCuenta->where('cuenta', 'LIKE', $request->get("search") . '%')
                ->orWhere('nombre', 'LIKE', '%' . $request->get("search") . '%');
        }

        if ($request->get("q")) {
            $planCuenta->where('cuenta', 'LIKE', $request->get("q") . '%')
                ->orWhere('nombre', 'LIKE', '%' . $request->get("q") . '%');
        }

        return $planCuenta->orderBy('cuenta')->paginate($totalRows);
    }
}
