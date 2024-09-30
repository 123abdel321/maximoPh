<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Events\PrivateMessageEvent;
//MODELS
use App\Models\Empresa\Versiones;

class ReloadPageVersion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:reload-page-version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recargar pagina despues de actualizar versión';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $nombreVersionActual = config('app.version');
        $versionGuardada = Versiones::where('estado', 1)->first();
        if ($versionGuardada && $versionGuardada->nombre == $nombreVersionActual) {
            event(new PrivateMessageEvent('canal-general-abdel-cartagena', [
                'tipo' => 'reloadPage',
            ]));
        } else {
            Versiones::where('estado', 1)
                ->update([
                    'estado' => 0
                ]);
                
            Versiones::create([
                'nombre' => $nombreVersionActual,
                'estado' => 1
            ]);

            event(new PrivateMessageEvent('canal-general-abdel-cartagena', [
                'tipo' => 'reloadPage',
            ]));
        }

    }
}
