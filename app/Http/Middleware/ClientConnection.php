<?php

namespace App\Http\Middleware;

use Closure;
use Config;
use DB;

//MODELS
use App\Models\Empresa\Empresa;

class ClientConnection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
		$user = $request->user();
        if(!$user->has_empresa){
            return response()->json([
                "success" => false,
				"message" => "Para acceder a esta opción debes seleccionar una empresa",
            ], 401);
        }

        $empresa = Empresa::where('token_db_maximo', $user->has_empresa)->first();

        // Cerrar la conexión max actual si existe
        if (DB::connection('max')->getDatabaseName() !== $empresa->token_db_maximo) {
            DB::purge('max'); // Cierra la conexión actual
        }

        // Cerrar la conexión sam actual si existe
        if (DB::connection('sam')->getDatabaseName() !== $empresa->token_db_portafolio) {
            DB::purge('sam'); // Cierra la conexión actual
        }        

		Config::set('database.connections.max.database', $empresa->token_db_maximo);
		Config::set('database.connections.sam.database', $empresa->token_db_portafolio);
		
        return $next($request);
    }
}
