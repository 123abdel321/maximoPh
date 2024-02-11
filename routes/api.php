<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//EMPRESA
use App\Http\Controllers\Empresa\ApiController;
//SISTEMA
use App\Http\Controllers\Sistema\ZonasController;
use App\Http\Controllers\Sistema\EntornoController;
use App\Http\Controllers\Sistema\InmuebleController;
use App\Http\Controllers\Sistema\ConceptoFacturacionController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::controller(ApiController::class)->group(function () {
    Route::get('login', 'login');
    Route::get('register', 'register');
    Route::get('create-empresa', 'createEmpresa');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:sanctum']], function() {

    Route::group(['middleware' => ['clientconnection']], function() {
        //CONCEPTO FACTURACION
        Route::controller(ConceptoFacturacionController::class)->group(function () {
            Route::post('concepto-facturacion', 'create');
            Route::put('concepto-facturacion', 'update');
            Route::delete('concepto-facturacion', 'delete');
        });
        //ZONAS
        Route::controller(ZonasController::class)->group(function () {
            Route::post('zona', 'create');
            Route::put('zona', 'update');
            Route::delete('zona', 'delete');
        });
        //INMUEBLES
        Route::controller(InmuebleController::class)->group(function () {
            Route::post('inmueble', 'create');
            Route::put('inmueble', 'update');
            Route::delete('inmueble', 'delete');
        });
        //ENTORNO
        Route::controller(EntornoController::class)->group(function () {
            Route::put('entorno', 'update');
        });
    });
});