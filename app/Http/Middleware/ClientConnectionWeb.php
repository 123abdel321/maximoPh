<?php

namespace App\Http\Middleware;

use DB;
use Config;
use Closure;
use App\Providers\RouteServiceProvider;
//MODELS
use App\Models\Empresa\Empresa;

class ClientConnectionWeb
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
            return redirect(RouteServiceProvider::SELECT_EMPRESA);
        }

		$empresa = Empresa::where('token_db_maximo', $user->has_empresa)->first();

		Config::set('database.connections.max.database', $empresa->token_db_maximo);
		Config::set('database.connections.sam.database', $empresa->token_db_portafolio);
        
        return $next($request);
    }
}
