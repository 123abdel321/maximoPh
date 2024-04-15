<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//EMPRESA
use App\Http\Controllers\Empresa\ApiController;
use App\Http\Controllers\Empresa\UsuariosController;
//PORTAFOLIO
use App\Http\Controllers\Portafolio\NitController;
use App\Http\Controllers\Portafolio\RecioController;
use App\Http\Controllers\Portafolio\PlanCuentaController;
//SISTEMA
use App\Http\Controllers\Sistema\PqrsfController;
use App\Http\Controllers\Sistema\ZonasController;
use App\Http\Controllers\Sistema\EntornoController;
use App\Http\Controllers\Sistema\InmuebleController;
use App\Http\Controllers\Sistema\PorteriaController;
use App\Http\Controllers\Sistema\InmuebleNitController;
use App\Http\Controllers\Sistema\FacturacionController;
use App\Http\Controllers\Sistema\CuotasMultasController;
use App\Http\Controllers\Sistema\EstadoCuentaController;
use App\Http\Controllers\Sistema\PorteriaEventoController;
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
    Route::get('usuario-accion', 'getUsuario');
    Route::post('create-empresa', 'createEmpresa');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:sanctum']], function() {

    Route::group(['middleware' => ['clientconnection']], function() {
        //CONCEPTO FACTURACION
        Route::controller(ConceptoFacturacionController::class)->group(function () {
            Route::get('concepto-facturacion', 'read');
            Route::post('concepto-facturacion', 'create');
            Route::put('concepto-facturacion', 'update');
            Route::delete('concepto-facturacion', 'delete');
            Route::get('concepto-facturacion-combo', 'combo');
        });
        //ZONAS
        Route::controller(ZonasController::class)->group(function () {
            Route::get('zona', 'read');
            Route::post('zona', 'create');
            Route::put('zona', 'update');
            Route::delete('zona', 'delete');
            Route::get('zona-combo', 'combo');
        });
        //INMUEBLES
        Route::controller(InmuebleController::class)->group(function () {
            Route::get('inmueble', 'read');
            Route::post('inmueble', 'create');
            Route::put('inmueble', 'update');
            Route::delete('inmueble', 'delete');
            Route::get('inmueble-combo', 'combo');
            Route::get('inmueble-total', 'totales');
        });
        //INMUEBLES NITS
        Route::controller(InmuebleNitController::class)->group(function () {
            Route::get('inmueble-nit', 'read');
            Route::post('inmueble-nit', 'create');
            Route::put('inmueble-nit', 'update');
            Route::delete('inmueble-nit', 'delete');
        });
        //FACTURACION
        Route::controller(FacturacionController::class)->group(function () {
            Route::get('facturacion', 'read');
            Route::post('facturacion', 'generar');
            Route::get('facturacion-preview', 'totales');
            Route::get('facturacion-proceso', 'readDetalle');
            Route::post('facturacion-confirmar', 'confirmar');
            Route::post('facturacion-individual', 'generarIndividual');
        });
        //CUOTAS EXTRA & MULTAS
        Route::controller(CuotasMultasController::class)->group(function () {
            Route::get('cuotasmultas', 'read');
            Route::post('cuotasmultas', 'create');
            Route::put('cuotasmultas', 'update');
            Route::delete('cuotasmultas', 'delete');
            Route::get('cuotasmultas-total', 'totales');
        });
        Route::controller(ReciboController::class)->group(function () {
            Route::get('recibo', 'read');
        });
        //PQRSF
        Route::controller(PqrsfController::class)->group(function () {
            Route::post('pqrsf', 'create');
        });
        //ENTORNO
        Route::controller(EntornoController::class)->group(function () {
            Route::put('entorno', 'update');
        });
        //PLAN CUENTAS
        Route::controller(PlanCuentaController::class)->group(function () {
            Route::get('plan-cuenta/combo-cuenta', 'comboCuenta');
        });
        //PLAN CUENTAS
        Route::controller(PlanCuentaController::class)->group(function () {
            Route::get('plan-cuenta/combo-cuenta', 'comboCuenta');
        });
        //PORTERIA
        Route::controller(PorteriaController::class)->group(function () {
            Route::get('porteria', 'read');
            Route::delete('porteria', 'delete');
            Route::get('porteria-find', 'find');
            Route::get('porteria-combo', 'combo');
        });
        //PORTERIA EVENTOS
        Route::controller(PorteriaEventoController::class)->group(function () {
            Route::get('porteriaevento', 'read');
            Route::put('porteriaevento', 'update');
            Route::get('porteriaevento-find', 'find');
        });
        
        //ESTADO CUENTA
        Route::controller(EstadoCuentaController::class)->group(function () {
            Route::get('estadocuenta', 'generate');
            Route::get('estadocuenta-total', 'totales');
            Route::get('estadocuenta-pagos', 'pagos');
            Route::get('estadocuenta-facturas', 'facturas');
        });
        //USUARIOS
        Route::controller(UsuariosController::class)->group(function () {
            Route::get('usuarios', 'generate');
            Route::post('usuarios', 'create');
            Route::put('usuarios', 'update');
            Route::get('usuarios/combo', 'comboUsuario');
        });
        
        
    });
});