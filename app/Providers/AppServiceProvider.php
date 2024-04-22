<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Relations\Relation;
//MODELS SISTEMA
use App\Models\Sistema\Porteria;
use App\Models\Sistema\PorteriaEvento;
use App\Models\Portafolio\ConRecibos;

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
