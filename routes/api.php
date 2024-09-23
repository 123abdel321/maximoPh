<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
//EMPRESA
use App\Http\Controllers\Empresa\ApiController;
use App\Http\Controllers\Empresa\PerfilController;
use App\Http\Controllers\Empresa\UsuariosController;
use App\Http\Controllers\Empresa\InstaladorController;
//PORTAFOLIO
use App\Http\Controllers\Portafolio\NitController;
use App\Http\Controllers\Portafolio\RecioController;
use App\Http\Controllers\Portafolio\PlanCuentaController;
//SISTEMA
use App\Http\Controllers\LoginController;
use App\Http\Controllers\Sistema\PqrsfController;
use App\Http\Controllers\Sistema\ZonasController;
use App\Http\Controllers\Sistema\EntornoController;
use App\Http\Controllers\Sistema\InmuebleController;
use App\Http\Controllers\Sistema\PorteriaController;
use App\Http\Controllers\Sistema\VisitantesController;
use App\Http\Controllers\Sistema\InmuebleNitController;
use App\Http\Controllers\Sistema\FacturacionController;
use App\Http\Controllers\Sistema\ImpuestosIvaController;
use App\Http\Controllers\Sistema\CuotasMultasController;
use App\Http\Controllers\Sistema\EstadoCuentaController;
use App\Http\Controllers\Informes\EstadisticasController;
use App\Http\Controllers\Sistema\PorteriaEventoController;
use App\Http\Controllers\Sistema\NotificacionesController;
use App\Http\Controllers\Sistema\ConceptoFacturacionController;
//IMPORTADOR
use App\Http\Controllers\Sistema\ImportadorInmuebles;
use App\Http\Controllers\Sistema\ImportadorCuotasMultas;
use App\Http\Controllers\Sistema\ImportadorRecibosController;
//TAREAS
use App\Http\Controllers\Sistema\TurnosController;
use App\Http\Controllers\Sistema\ProyectosController;


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


Route::controller(VisitantesController::class)->group(function () {
    Route::post('visitante', 'create');
});

Route::controller(ApiController::class)->group(function () {
    Route::get('login', 'login');
    Route::get('register', 'register');
    Route::get('usuario-accion', 'getUsuario');
    Route::post('confirm-pass', 'confirmPass');
    Route::post('validate-code', 'validateCode');
    Route::post('create-empresa', 'createEmpresa');
    Route::post('validate-email', 'validateEmail');
    Route::post('change-password', 'changePassword');
    
    Route::get('/', function (Request $request) {
        return response()->json('MaximoPH API');
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['middleware' => ['auth:sanctum']], function() {

    Route::group(['middleware' => ['clientconnection']], function() {
        
        Route::controller(LoginController::class)->group(function () {
            Route::post('select-empresa', 'selectEmpresa');
            Route::post('login-portafolioerp', 'loginPortafolioERP');
        });
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
            Route::get('facturaciones', 'readPdf');
            Route::post('facturacion', 'generar');
            Route::get('facturacion-preview', 'totales');
            Route::get('facturacion-preview-lite', 'totalesLite');
            Route::get('facturacion-proceso', 'readDetalle');
            Route::post('facturacion-confirmar', 'confirmar');
            Route::post('facturacion-general', 'generarGeneral');
            Route::post('facturacion-general-delete', 'generarGeneralDelete');
            Route::post('facturacion-general-causar', 'generarGeneralCausar');
            Route::post('facturacion-individual', 'generarIndividual');
            Route::get('periodo-facturacion-combo', 'comboPeriodos');
            Route::get('facturacion-email', 'email');
        });
        //IMPUESTOS IVA
        Route::controller(ImpuestosIvaController::class)->group(function () {
            Route::get('impuestosiva', 'read');
        });
        
        //CUOTAS EXTRA & MULTAS
        Route::controller(CuotasMultasController::class)->group(function () {
            Route::get('cuotasmultas', 'read');
            Route::post('cuotasmultas', 'create');
            Route::put('cuotasmultas', 'update');
            Route::delete('cuotasmultas', 'delete');
            Route::get('cuotasmultas-total', 'totales');
            Route::delete('cuotasmultas-delete', 'deleteMasivo');
            Route::get('cuotasmultas-concepto', 'comboConcepto');
        });
        //ESTADISTICAS
        Route::controller(EstadisticasController::class)->group(function () {
            Route::get('estadisticas', 'generate');
            Route::get('estadisticas-show', 'show');
        });
        Route::controller(ReciboController::class)->group(function () {
            Route::get('recibo', 'read');
        });
        //PQRSF
        Route::controller(PqrsfController::class)->group(function () {
            Route::get('pqrsf', 'read');
            Route::post('pqrsf', 'create');
            Route::get('pqrsf-find', 'find');
            Route::post('pqrsf-tiempo', 'tiempo');
            Route::post('pqrsf-estado', 'updateEstado');
            Route::put('pqrsf-destinatario', 'updateDestinatario');
            Route::post('pqrsf-email', 'sendEmail');
        });
        //ENTORNO
        Route::controller(EntornoController::class)->group(function () {
            Route::put('entorno', 'update');
        });
        //PERFILES
        Route::controller(PerfilController::class)->group(function () {
            Route::get('perfil', 'nit');
            Route::put('perfil', 'update');
            Route::post('perfil', 'fondo');
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
            Route::delete('usuarios', 'delete');
            Route::post('usuarios-sync', 'sync');
            Route::post('usuarios-welcome', 'welcome');
            Route::get('usuarios/combo', 'combo');
            
        });
        //IMPORTADOR PAGOS
        Route::controller(ImportadorRecibosController::class)->group(function () {
            Route::get('recibos-cache-import', 'generate');
            Route::post('recibos-cargar-import', 'cargar');
            Route::get('recibos-totales-import', 'totales');
        });
        //IMPORTADOR CUOTAS & MULTAS
        Route::controller(ImportadorCuotasMultas::class)->group(function () {
            Route::get('cuotas-cache-import', 'generate');
            Route::post('cuotas-cargar-import', 'cargar');
            Route::get('cuotas-totales-import', 'totales');
        });
        //IMPORTADOR DE INMUEBLES
        Route::controller(ImportadorInmuebles::class)->group(function () {
            Route::get('inmuebles-cache-import', 'generate');
            Route::post('inmuebles-cargar-import', 'cargar');
            Route::get('inmuebles-totales-import', 'totales');
        });
        //EMPRESA
        Route::controller(InstaladorController::class)->group(function () {
            Route::get('empresas', 'generate');
        });
        //NOTIFICACIONES
        Route::controller(NotificacionesController::class)->group(function () {
            Route::get('notificacion', 'find');
            Route::get('notificaciones', 'read');
            Route::put('notificaciones', 'update');
        });
        //TAREAS
        Route::controller(ProyectosController::class)->group(function () {
            Route::get('proyectos', 'read');
            Route::post('proyectos', 'create');
            Route::put('proyectos', 'update');
            Route::delete('proyectos', 'delete');
            Route::get('proyectos-combo', 'combo');
        });
        Route::controller(TurnosController::class)->group(function () {
            Route::get('turnos', 'find');
            Route::put('turnos', 'update');
        });
        
    });
});