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

        // 1. Validación de empresa
        if (!$user || !$user->has_empresa) {
            return response()->json([
                "success" => false,
                "message" => "Para acceder a esta opción debes seleccionar una empresa",
            ], 401);
        }

        // 2. Obtener la empresa y los nombres de BD deseados
        $empresa = Empresa::where('token_db_maximo', $user->has_empresa)->first();
        
        // Manejo de error si no se encuentra la empresa (opcional, pero buena práctica)
        if (!$empresa) {
             return response()->json([
                "success" => false,
                "message" => "Empresa no encontrada o datos de conexión inválidos.",
            ], 401);
        }
        
        $desiredDbMax = $empresa->token_db_maximo;
        $desiredDbSam = $empresa->token_db_portafolio;
        
        // --- Conexión 'max' (Contabilidad) ---
        $currentConfigDbMax = Config::get('database.connections.max.database');

        if ($currentConfigDbMax !== $desiredDbMax) {
            // Cierra la conexión actual si está activa
            if (DB::getConnections() && array_key_exists('max', DB::getConnections())) {
                DB::purge('max');
            }
            // Setea la nueva configuración
            Config::set('database.connections.max.database', $desiredDbMax);
        }

        // --- Conexión 'sam' (Portafolio/Principal) ---
        $currentConfigDbSam = Config::get('database.connections.sam.database');

        if ($currentConfigDbSam !== $desiredDbSam) {
            // Cierra la conexión actual si está activa
            if (DB::getConnections() && array_key_exists('sam', DB::getConnections())) {
                DB::purge('sam');
            }
            // Setea la nueva configuración
            Config::set('database.connections.sam.database', $desiredDbSam);
        }
        
        // 3. Continuar con la solicitud
        return $next($request);
    }
}
