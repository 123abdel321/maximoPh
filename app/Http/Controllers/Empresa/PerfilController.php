<?php

namespace App\Http\Controllers\Empresa;

use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
//MODELS
use App\Models\User;
use App\Models\Sistema\Zonas;
use App\Models\Empresa\Empresa;
use App\Models\Portafolio\Nits;
use App\Models\Sistema\InmuebleNit;

use App\Models\Empresa\UsuarioEmpresa;

class PerfilController extends Controller
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
        $usuarioEmpresa = UsuarioEmpresa::with('nit', 'rol')
            ->where('id_empresa', $request->user()['id_empresa'])
            ->where('id_usuario', $request->user()['id'])
            ->first();
        
        $data = [
            'usuario_nit' => $usuarioEmpresa->nit,
            'nombre_rol' => $usuarioEmpresa->rol->nombre
        ];

        return view('pages.configuracion.perfil.perfil-view', $data);
    }

    public function update (Request $request)
    {
        $nitActual = null;
        $usuarioEmpresa = UsuarioEmpresa::with('nit')
            ->where('id_empresa', $request->user()['id_empresa'])
            ->where('id_usuario', $request->user()['id'])
            ->first();

        if ($usuarioEmpresa->nit) {
            $nitActual = Nits::find($usuarioEmpresa->id_nit);
        }

        if($nitActual && $nitActual->numero_documento != $request->get('numero_documento')){
            $nitsExist = Nits::where('numero_documento', $request->get('numero_documento'));
            if($nitsExist->count() > 0){
                return response()->json([
                    'success'=>	false,
                    'data' => '',
                    'message'=> 'El numero de documento ya esta siendo usado!'
                ]);
            }
        }

        Nits::where('id', $usuarioEmpresa->id_nit)
            ->update([
                'id_tipo_documento' => $request->get('id_tipo_documento'),
                'numero_documento' => $request->get('numero_documento'),
                'primer_apellido' => $request->get('primer_apellido'),
                'segundo_apellido' => $request->get('segundo_apellido'),
                'primer_nombre' => $request->get('primer_nombre'),
                'otros_nombres' => $request->get('otros_nombres'),
                'email' => $request->get('email'),
                'email_1' => $request->get('email_1'),
                'email_2' => $request->get('email_2'),
                'telefono_1' => $request->get('telefono_1'),
            ]);

        InmuebleNit::where('id_nit', $usuarioEmpresa->id_nit)
            ->update([
                'enviar_notificaciones_mail' => $request->get('notificaciones_mail'),
                'enviar_notificaciones_fisica' => $request->get('notificaciones_fisica')
            ]);

        if ($request->get('password')) {
            User::where('id', $request->user()['id'])
                ->update([
                    'password' => Hash::make($request->get('password')),
                ]);
        }

        return response()->json([
            'success'=>	true,
            'data' => [],
            'message'=> 'Perfil actualizado con exito!'
        ]);
    }

    public function fondo (Request $request)
    {
        $url = null;
        $file = $request->file('newFondoSistema');
        if ($file) {
            $usuario = User::find(request()->user()->id);

            $nameFile = 'maximo/empresas/'.request()->user()->id_empresa.'/imagen/usuario';
            $url = Storage::disk('do_spaces')->put($nameFile, $file, 'public');

            if ($usuario->fondo_sistema) {
                Storage::disk('do_spaces')->delete($usuario->fondo_sistema);
            }

            $usuario->fondo_sistema = $url;
            $usuario->save();
        }

        return response()->json([
            'success'=>	true,
            'url' => $url,
            'message'=> 'Fondo usuario actualizado con exito!'
        ]);
    }

    public function avatar (Request $request)
    {
        $url = null;
        $file = $request->file('imagen_perfil');
        if ($file) {
            $usuario = User::find(request()->user()->id);

            $nameFile = 'maximo/empresas/'.request()->user()->id_empresa.'/imagen/usuario';
            $url = Storage::disk('do_spaces')->put($nameFile, $file, 'public');

            if ($usuario->avatar) {
                Storage::disk('do_spaces')->delete($usuario->avatar);
            }

            $usuario->avatar = $url;
            $usuario->save();

            $usuarioEmpresa = UsuarioEmpresa::with('nit.tipo_documento', 'nit.ciudad')
                ->where('id_empresa', $request->user()['id_empresa'])
                ->where('id_usuario', $request->user()['id'])
                ->first();

            $nitActual = Nits::find($usuarioEmpresa->id_nit);
            $nitActual->logo_nit = $url;
            $nitActual->save();
        }

        return response()->json([
            'success'=>	true,
            'url' => $url,
            'message'=> 'Avatar usuario actualizado con exito!'
        ]);
    }

    public function nit (Request $request)
    {
        $usuarioEmpresa = UsuarioEmpresa::with('nit.tipo_documento', 'nit.ciudad')
            ->where('id_empresa', $request->user()['id_empresa'])
            ->where('id_usuario', $request->user()['id'])
            ->first();

        $notificaciones = null;
        if ($usuarioEmpresa->nit) {
            $notificaciones = DB::connection('max')->select("SELECT
                    enviar_notificaciones_mail,
                    enviar_notificaciones_fisica
                FROM
                    inmueble_nits
                    
                WHERE id_nit = {$usuarioEmpresa->nit->id}
                
                GROUP BY id_nit
            ");
    
            $notificaciones = collect($notificaciones);
            $notificaciones = count($notificaciones) ? $notificaciones[0] :  null;
        }

        return response()->json([
            'success'=>	true,
            'data' => $usuarioEmpresa->nit,
            'fondo_sistema' => $request->user()->fondo_sistema,
            'notificaciones_mail' => $notificaciones ? $notificaciones->enviar_notificaciones_mail : false,
            'notificaciones_fisica' => $notificaciones ? $notificaciones->enviar_notificaciones_fisica : false,
            'message'=> 'Datos nit consultados con exito!'
        ]);
    }
}