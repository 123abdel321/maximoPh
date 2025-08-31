<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//MODELS
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Entorno;
use App\Models\Empresa\UsuarioEmpresa;
use App\Models\Empresa\ComponentesMenu;
use App\Models\Sistema\TerminosCondiciones;
use App\Models\Sistema\TerminosCondicionesUser;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        ini_set('upload_max_filesize', '100M');
        ini_set('post_max_size', '110M');
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '300');
        
        $hasEmpresa = $request->user()->has_empresa;

        $empresa = Empresa::where('token_db_maximo', $hasEmpresa)
            ->first();

        $menus = ComponentesMenu::whereNotNull('id_componente')
            ->where('estado', 1)
            ->with('padre')
            ->orderBy('orden_menu', 'ASC')
            ->get();

        $usuarioEmpresa = UsuarioEmpresa::where('id_usuario', $request->user()->id)
            ->where('id_empresa', $request->user()->id_empresa)
            ->first();

        $terminosCondiciones = TerminosCondiciones::orderBy('id', 'DESC')->first();

        $obligarAceptarTerminos = Entorno::where('nombre', 'aceptar_terminos')->first();
        $obligarAceptarTerminos = $obligarAceptarTerminos ? intval($obligarAceptarTerminos->valor) : 0;
        // user_id
        // terminos_condiciones_id
        $mostrarModalTerminosCondicion = null;
        if ($terminosCondiciones) {
            $aceptoTerminos = TerminosCondicionesUser::where('user_id', $request->user()->id)
                ->where('terminos_condiciones_id', $terminosCondiciones->id)
                ->count();
            if (!$aceptoTerminos) {
                $mostrarModalTerminosCondicion = true;
            } 

            $terminosCondiciones = $terminosCondiciones->content;
        }

        

        foreach ($menus as $key => $menu) {
            if ($menu->code_name && !$request->user()->hasPermissionTo($menu->code_name.' read')) {
                unset($menus[$key]);
            }
        }

        $data = [
            'menus' => $menus->groupBy('id_padre'),
            'rol_usuario' => $usuarioEmpresa->id_rol,
            'is_owner' => Empresa::where('id_usuario_owner', $request->user()->id)->count(),
            'terminos_condiciones' => $terminosCondiciones,
            'obligar_aceptar_terminos' => $obligarAceptarTerminos,
            'mostrar_modal_terminos_condicion' => $mostrarModalTerminosCondicion,
            'pqrsf_responder' => $request->user()->can('pqrsf responder'),
            'turno_responder' => $request->user()->can('turnos responder'),
        ];

        return view('layouts.app', $data);
    }

    public function dashboard()
    {
        return view('pages.dashboard');
    }
}
