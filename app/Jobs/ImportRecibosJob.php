<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use App\Imports\RecibosCajaImport;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportRecibosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $empresa;
    protected $filePath;

    public function __construct($empresa, $filePath)
    {
        $this->empresa = $empresa;
        $this->filePath = $filePath;
    }

    public function handle()
    {
        try {
            (new RecibosCajaImport($this->empresa))->import($this->filePath);
        } catch (Exception $e) {
            throw $e;
        }
    }
}