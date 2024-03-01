<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
//MODELS
use App\Models\User;
use App\Models\Empresa\Empresa;
use App\Models\Empresa\UsuarioEmpresa;
use App\Models\Empresa\UsuarioPermisos;
use Spatie\Permission\Models\Permission;

class LoginController extends Controller
{
    /**
     * Display login page.
     *
     * @return Renderable
     */
    public function show()
    {
        return view('auth.login');
    }

    public function validateSession (Request $request)
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credenciales1 = ['email' => $request->email, 'password' => $request->password];
        $credenciales2 = ['username' => $request->email, 'password' => $request->password];

        if (Auth::attempt($credenciales1) || Auth::attempt($credenciales2)) {
            $request->session()->regenerate();
            $user =  User::find(Auth::user()->id);

            if($user->tokens()->where('tokenable_id', $user->id)
                ->where('name', 'web_token')
                ->exists()) {
                // $user->tokens()->delete();
            }

            $plainTextToken = '';
            if ($user->remember_token) {
                $token = $user->createToken("web_token");
                $plainTextToken = $token->plainTextToken;
                $user->remember_token = $plainTextToken;
            } else {
                $token = $user->createToken("web_token");
                $plainTextToken = $token->plainTextToken;
                $user->remember_token = $plainTextToken;
            }

            $idEmpresa = $user->id_empresa;

            if (!$idEmpresa) {
                $empresa = UsuarioEmpresa::where('id_usuario', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (!$empresa){
                    return response()->json([
                        'success'=>	true,
                        'access_token' => $plainTextToken,
                        'token_type' => 'Bearer',
                        'empresa' => '',
                        'notificacion_code' => '',
                        'message'=> 'Usuario logeado con exito!'
                    ], 200);
                }

                $idEmpresa = $empresa->id;
            }
                
            $notificacionCode =  null;
            $empresaSelect = Empresa::where('id', $idEmpresa)->first();

            $notificacionCode = $empresaSelect->token_db_maximo.'_'.$user->id;
            $user->id_empresa = $empresaSelect->id;
            $user->has_empresa = $empresaSelect->token_db_maximo;
            $user->save();

            $usuarioPermisosEmpresa = UsuarioPermisos::where('id_user', $user->id)
                ->where('id_empresa', $empresaSelect->id)
                ->first();

            $permisosNombre = Permission::whereIn('id', explode(',', $usuarioPermisosEmpresa->ids_permission))->get();
            $nombrePermisos = [];
            foreach ($permisosNombre as $permisoNombre) {
                $nombrePermisos[] = $permisoNombre->name;
            }

            $user->syncPermissions($nombrePermisos);

            return response()->json([
                'success'=>	true,
                'access_token' => $plainTextToken,
                'token_api_portafolio' => $empresaSelect->token_api_portafolio,
                'token_type' => 'Bearer',
                'empresa' => $empresaSelect,
                'token_db_portafolio' => base64_encode($empresaSelect->token_db_portafolio),
                'notificacion_code' => $notificacionCode,
                'fondo_sistema' => $user->fondo_sistema,
                'message'=> 'Usuario logeado con exito!'
            ], 200);
        }

        return response()->json([
    		'success'=>	false,
    		'data' => '',
    		'message'=> 'The provided credentials do not match our records.'
    	], 422);
    }

    private function encryptData($data)
    {
        $method = "AES-256-CBC";
        $key = "encryptionKey123";
        $options = 0;
        $iv = '1234567891011121';

        return openssl_encrypt($data, $method, $key, $options, $iv);
    }

    private function decryptData($data)
    {
        $method = "AES-256-CBC";
        $key = "encryptionKey123";
        $options = 0;
        $iv = '1234567891011121';

        return openssl_decrypt($data, $method, $key, $options, $iv);
    }

    public function logout(Request $request)
    {
        $user =  User::find(Auth::user()->id);
        $user->tokens()->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
    		'success'=>	false,
    		'data' => '',
    		'message'=> 'logout true'
    	], 200);
    }
}
