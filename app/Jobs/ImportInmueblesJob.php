<?php

namespace App\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\InteractsWithQueue;
use App\Imports\InmueblesGeneralesImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ImportInmueblesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    protected $empresa;
    protected $actualizarValores;
    protected $filePath;

    public function __construct($empresa, $actualizarValores, $filePath)
    {
        $this->empresa = $empresa;
        $this->actualizarValores = $actualizarValores;
        $this->filePath = $filePath;
    }

    public function handle()
    {
        try {
            (new InmueblesGeneralesImport($this->empresa, $this->actualizarValores))->import($this->filePath);
        } catch (Exception $e) {
            throw $e;
        }
    }
}