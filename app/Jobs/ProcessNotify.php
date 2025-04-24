<?php

namespace App\Jobs;

use DB;
use Exception;
use App\Helpers\helpers;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use App\Events\PrivateMessageEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldQueue;
//MODELS
use App\Models\User;
use App\Models\Empresas\Empresa;

class ProcessNotify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

	protected $url;
	protected $data;
    protected $has_empresa;

    public function __construct($url, $data)
    {
        $this->url = $url;
        $this->data = $data;
    }

    public function handle()
    {
        event(new PrivateMessageEvent($this->url, $this->data));
    }
}
