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
            $totalUsuariosSincronizados = 0;
            foreach ($dataInmuebles as $dataInmueble) {
                $usuario = UsuarioEmpresa::where('id_nit', $dataInmueble->id_nit)
                    ->count();
                if (!$usuario) {
                    
                    $totalUsuariosSincronizados++;
                    $nit = Nits::where('id', $dataInmueble->id_nit)
                        ->first();
                    
                    $usuarioPropietario = User::create([
                        'id_empresa' => $this->id_empresa,
                        'has_empresa' => $this->empresa->token_db_maximo,
                        'firstname' => $nit->primer_nombre,
                        'lastname' => $nit->primer_apellido,
                        'username' => '123'.$nit->primer_nombre.'321',
                        'email' => $nit->email,
                        'telefono' => $nit->telefono_1,
                        'password' => $nit->numero_documento,
                        'address' => $nit->direccion,
                        'created_by' => $this->id_usuario,
                        'updated_by' => $this->id_usuario
                    ]);
                    
                    $idRol = $dataInmueble->tipo ? 3 : 5;
                    $rolPropietario = RolesGenerales::find($idRol);
                    
                    UsuarioEmpresa::updateOrCreate([
                        'id_usuario' => $usuarioPropietario->id,
                        'id_empresa' => $this->id_empresa
                    ],[
                        'id_rol' => $idRol, // 3: PROPIETARIO; 4:RESIDENTE
                        'id_nit' => $nit->id,
                        'estado' => 1, // default: 1 activo
                    ]);

                    UsuarioPermisos::updateOrCreate([
                        'id_user' => $usuarioPropietario->id,
                        'id_empresa' => $this->id_empresa
                    ],[
                        'id_rol' => $idRol, // ROL PROPIETARIO
                        'ids_permission' => $rolPropietario->ids_permission
                    ]);

                    $portero = Porteria::where('id_usuario', $usuarioPropietario->id)
                        ->whereIn('tipo_porteria', [0,1])
                        ->first();

                    if ($portero) {
                        $portero->tipo_porteria = $dataInmueble->tipo == 1 ? 1 : 0;
                        $portero->nombre = $nit->primer_nombre.' '.$nit->primer_apellido;
                        $portero->dias = $dataInmueble->tipo != 0 ? '1,2,3,4,5,6,7' : null;
                        $portero->updated_by = $this->id_usuario;
                        $portero->save();
                    } else {
                        $portero = Porteria::create([
                            'id_usuario' => $usuarioPropietario->id,
                            'id_nit' => $nit->id,
                            'tipo_porteria' => $dataInmueble->tipo == 1 ? 1 : 0,
                            'nombre' => $nit->primer_nombre.' '.$nit->primer_apellido,
                            'dias' => !$dataInmueble->tipo ? '1,2,3,4,5,6,7' : null,
                            'created_by' => $this->id_usuario,
                            'updated_by' => $this->id_usuario,
                        ]);
                    }

                    $tieneImagen = ArchivosGenerales::where('relation_type', 1)
                        ->where('relation_id', $portero->id);
                    
                    if ($nit->logo_nit && !$tieneImagen->count()) {
                        $archivo = new ArchivosGenerales([
                            'tipo_archivo' => 'imagen',
                            'url_archivo' => $nit->logo_nit,
                            'estado' => 1,
                            'created_by' => $this->id_usuario,
                            'updated_by' => $this->id_usuario
                        ]);
            
                        $archivo->relation()->associate($portero);
                        $portero->archivos()->save($archivo);
                    }
                }
            }
            
            $urlEventoNotificacion = $this->empresa->token_db_maximo.'_'.$this->id_usuario;
            event(new PrivateMessageEvent('sincronizar-usuarios-'.$urlEventoNotificacion, [
                'tipo' => 'exito',
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
