<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use App\Imports\CutasExtrasImport;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportCuotasJob implements ShouldQueue
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
            (new CutasExtrasImport($this->empresa))->import($this->filePath);
        } catch (Exception $e) {
            throw $e;
        }
    }
}