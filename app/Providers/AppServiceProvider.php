<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
//MODELS SISTEMA
use App\Models\Sistema\Pqrsf;
use App\Models\Sistema\Porteria;
use App\Models\Portafolio\ConRecibos;
use App\Models\Sistema\PqrsfMensajes;
use App\Models\Sistema\PorteriaEvento;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Relation::morphMap([
            '6' => ConRecibos::class,
            '10' => Porteria::class,
            '11' => PorteriaEvento::class,
            '12' => Pqrsf::class,
            '13' => PqrsfMensajes::class
		]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
