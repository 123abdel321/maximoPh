<?php

namespace App\Jobs;

use DB;
use Exception;
use App\Helpers\helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Helpers\DocumentoGeneralController;
//MODELS
use App\Models\User;
use App\Models\Portafolio\Nits;
use App\Models\Empresa\Empresa;
use App\Models\Sistema\Porteria;
use App\Models\Sistema\InmuebleNit;
use App\Models\Empresa\UsuarioEmpresa;
use App\Models\Empresa\RolesGenerales;
use App\Models\Empresa\UsuarioPermisos;
use App\Models\Sistema\ArchivosGenerales;

class ProcessSyncronizarUsuarios implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $empresa = null;
    public $request = null;
	public $id_usuario = null;
    public $id_empresa = null;

    /**
     * Create a new job instance.
	 * 
	 * @return void
     */
    public function __construct($id_usuario, $id_empresa, $data)
    {
        $this->id_usuario = $id_usuario;
        $this->id_empresa = $id_empresa;
        $this->empresa = Empresa::find($id_empresa);
        $this->request = $data;
    }

    /**
     * Execute the job.
	 * 
	 * @return string
     */
    public function handle()
    {
        try {         
            copyDBConnection('max', 'max');
            setDBInConnection('max', $this->empresa->token_db_maximo);

            copyDBConnection('sam', 'sam');
            setDBInConnection('sam', $this->empresa->token_db_portafolio);

            $inmueblesNits = InmuebleNit::whereNotNull('id_nit')
                ->groupBy('id_nit');
            
            if ($this->request['id_zona']) {
                $inmueblesNits->whereHas('inmueble',  function ($query) {
                    $query->where('id_zona', $this->request['id_zona']);
                });
            }
            
            if ($this->request['id_inmueble']) {
                $inmueblesNits->where('id_inmueble', $this->request['id_inmueble']);
            }

            if ($this->request['id_nit']) {
                $inmueblesNits->where('id_nit', $this->request['id_nit']);
            }
            
            $dataInmuebles = $inmueblesNits->get();

            $totalUsuariosCreados = 0;
            $totalUsuariosRelaciados = 0;
            
            foreach ($dataInmuebles as $dataInmueble) {
                $nit = Nits::where('id', $dataInmueble->id_nit)->first();
                $usuario = User::where('email', $nit->email)->first();
                $usuarioEmpresa = null;

                if ($usuario) {
                    $usuarioEmpresa = UsuarioEmpresa::where('id_usuario', $usuario->id)
                        ->where('id_empresa', $this->id_empresa)
                        ->count();
                }
                
                //CREAR USUARIO
                if (!$usuario && $nit->email) {
                    $username = str_replace(' ', '', $nit->primer_nombre).rand(1, 10000);
                    $usuario = User::create([
                        'id_empresa' => $this->id_empresa,
                        'has_empresa' => $this->empresa->token_db_maximo,
                        'firstname' => $nit->primer_nombre,
                        'lastname' => $nit->primer_apellido,
                        'username' => $username,
                        'email' => $nit->email,
                        'telefono' => $nit->telefono_1,
                        'password' => $nit->numero_documento,
                        'address' => $nit->direccion,
                        'created_by' => $this->id_usuario,
                        'updated_by' => $this->id_usuario
                    ]);
                    $totalUsuariosCreados++;
                } else if ($usuario){
                    $totalUsuariosRelaciados++;
                }
                
                //ASOCIAR USUARIO A LA EMPRESA
                $idRol = 5;
                if ($dataInmueble->tipo == 0) $idRol = 3;
                if ($dataInmueble->tipo == 2) $idRol = 9;
                if ($dataInmueble->tipo == 3) $idRol = 11;

                $rolPropietario = RolesGenerales::find($idRol);
                
                UsuarioEmpresa::updateOrCreate([
                    'id_usuario' => $usuario->id,
                    'id_empresa' => $this->id_empresa
                ],[
                    'id_rol' => $idRol, // 3: PROPIETARIO; 4:RESIDENTE
                    'id_nit' => $nit->id,
                    'estado' => 1, // default: 1 activo
                ]);
                //AGREGAR PERMISOS DE USUARIO
                UsuarioPermisos::updateOrCreate([
                    'id_user' => $usuario->id,
                    'id_empresa' => $this->id_empresa
                ],[
                    'id_rol' => $idRol, // ROL PROPIETARIO
                    'ids_permission' => $rolPropietario->ids_permission
                ]);
                //AGREGAR ITEM DE PORTERIA 
                Porteria::where('id_usuario', $usuario->id)->delete();
                $portero = Porteria::create([
                    'id_usuario' => $usuario->id,
                    'id_nit' => $nit->id,
                    'tipo_porteria' => $dataInmueble->tipo == 1 ? 1 : 0,
                    'nombre' => $nit->primer_nombre.' '.$nit->primer_apellido,
                    'dias' => null,
                    'created_by' => $this->id_usuario,
                    'updated_by' => $this->id_usuario,
                ]);
            }
            
            $urlEventoNotificacion = $this->empresa->token_db_maximo.'_'.$this->id_usuario;
            event(new PrivateMessageEvent('sincronizar-usuarios-'.$urlEventoNotificacion, [
                'tipo' => 'exito',
                'usuarios_creados' => $totalUsuariosCreados,
                'usuarios_relaciados' => $totalUsuariosRelaciados,
                'success' =>  true,
            ]));

		} catch (Exception $exception) {
            
			Log::error('ProcessSyncronizarUsuarios al sincronizar usuarios', [
                'message' => $exception->getMessage(),
                'line' => $exception->getLine()
            ]);
		}
    }

	public function failed($exception)
	{
		Log::error('ProcessFacturacionGeneralDelete al enviar facturación a PortafolioERP', [
            'message' => $exception->getMessage(),
            'line' => $exception->getLine()
        ]);
	}
}
