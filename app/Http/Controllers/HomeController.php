<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//MODELS
use App\Models\Empresa\Empresa;
use App\Models\Empresa\UsuarioEmpresa;
use App\Models\Empresa\ComponentesMenu;

class HomeController extends Controller
{
    public function index(Request $request)
    {
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

        foreach ($menus as $key => $menu) {
            if ($menu->code_name && !$request->user()->hasPermissionTo($menu->code_name.' read')) {
                unset($menus[$key]);
            }
        }

        $data = [
            'menus' => $menus->groupBy('id_padre'),
            'rol_usuario' => $usuarioEmpresa->id_rol,
            'is_owner' => Empresa::where('id_usuario_owner', $request->user()->id)->count(),
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
