<?php

namespace App\Jobs;

use DB;
use Exception;
use App\Helpers\helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
//MODELS
use App\Models\User;
use App\Models\Empresas\Empresa;

class ProcessProvisionedDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
	public $dbName = '';
	public $connectionName = '';
	public $empresa;

    /**
     * Create a new job instance.
	 * 
	 * @return void
     */
    public function __construct($empresa)
    {
        $this->empresa = $empresa;
		$this->connectionName = 'sam';
    }

    /**
     * Execute the job.
	 * 
	 * @return string
     */
    public function handle()
    {
        try {
			createDatabase($this->empresa->token_db);

			if (!config('database.connections.' . $this->connectionName)) {
				copyDBConnection('sam', $this->empresa->token_db);
			}

			setDBInConnection('sam', $this->empresa->token_db);

			// // Artisan::call('migrate', [
			// // 	'--force' => true,
			// // 	'--path' => 'database/migrations/sistema',
			// // 	'--database' => 'sam'
			// // ]);

			// // Artisan::call('db:seed', [
			// // 	'--force' => true,
			// // 	'--class' => PropiedadesHorizontalesSeeder::class,
			// // 	'--database' => 'sam'
			// // ]);

			info('Base de datos generada: ' . $this->empresa->token_db);
			
			return $this->empresa->token_db;
		} catch (Exception $exception) {
			Log::error('Error al generar base de datos provisionada', ['message' => $exception->getMessage()]);

			$this->dropDb($this->empresa->token_db);
		}
    }

    private function dropDb($schemaName)
	{
		if (config('database.connections.' . $this->connectionName)) {
			DB::connection($this->connectionName)->statement("DROP DATABASE IF EXISTS $schemaName");
		}
	}

	public function failed($exception)
	{
		Log::error('Error al generar base de datos provisionada desde failed', ['message' => $exception->getMessage()]);

		$this->dropDb($this->empresa->token_db);
	}
}
