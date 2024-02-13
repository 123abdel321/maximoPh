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

	public $empresa;
	public $connectionName = '';

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
			createDatabase($this->empresa->token_db_maximo);

			if (!config('database.connections.' . $this->connectionName)) {
				copyDBConnection('max', $this->empresa->token_db_maximo);
			}

			setDBInConnection('max', $this->empresa->token_db_maximo);

			// Artisan::call('migrate', [
			// 	'--force' => true,
			// 	'--path' => 'database/migrations/sistema',
			// 	'--database' => 'max'
			// ]);

			info('Base de datos generada: ' . $this->empresa->token_db_maximo);
			
			return $this->empresa->token_db_maximo;
		} catch (Exception $exception) {
			Log::error('Error al generar base de datos provisionada', ['message' => $exception->getMessage()]);

			$this->dropDb($this->empresa->token_db_maximo);
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

		$this->dropDb($this->empresa->token_db_maximo);
	}
}
