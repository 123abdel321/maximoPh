<?php

use Illuminate\Support\Facades\Route;
//SISTEMA
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\ResetPassword;
use App\Http\Controllers\ChangePassword;
//PORTAFOLIO
use App\Http\Controllers\Portafolio\NitController;
use App\Http\Controllers\Portafolio\ReciboController;
//TABLAS
use App\Http\Controllers\Sistema\ZonasController;
use App\Http\Controllers\Sistema\InmuebleController;
use App\Http\Controllers\Sistema\ConceptoFacturacionController;
//OPERACIONES
use App\Http\Controllers\Sistema\FacturacionController;
use App\Http\Controllers\Sistema\CuotasMultasController;
//ADMINISTRATIVO
use App\Http\Controllers\Sistema\PorteriaController;
use App\Http\Controllers\Empresa\InstaladorController;
use App\Http\Controllers\Sistema\EstadoCuentaController;
use App\Http\Controllers\Sistema\PorteriaEventoController;
//CONFIGURACION
use App\Http\Controllers\Sistema\EntornoController;
use App\Http\Controllers\Empresa\UsuariosController;
//IMPORTADOR
use App\Http\Controllers\Sistema\ImportadorRecibosController;

Route::get('/', function () {
	return redirect('/home');
});

Auth::routes();

Route::get('/register', [RegisterController::class, 'create'])->middleware('guest')->name('register');
Route::post('/register', [RegisterController::class, 'store'])->middleware('guest')->name('register.perform');
Route::get('/login', [LoginController::class, 'show'])->middleware('guest')->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest')->name('login.perform');
Route::get('/reset-password', [ResetPassword::class, 'show'])->middleware('guest')->name('reset-password');
Route::post('/reset-password', [ResetPassword::class, 'send'])->middleware('guest')->name('reset.perform');
Route::get('/change-password', [ChangePassword::class, 'show'])->middleware('guest')->name('change-password');
Route::post('/change-password', [ChangePassword::class, 'update'])->middleware('guest')->name('change.perform');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::group(['middleware' => ['auth:sanctum']], function () {

	Route::group(['middleware' => ['clientconnectionweb']], function () {
		//INICIO
		Route::get('/home', [HomeController::class, 'index'])->name('home');
		Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
		//TABLAS
		Route::get('/nit', [NitController::class, 'index']);
		Route::get('/zona', [ZonasController::class, 'index']);
		Route::get('/inmueble', [InmuebleController::class, 'index']);
		Route::get('/conceptofacturacion', [ConceptoFacturacionController::class, 'index']);
		//OPERACIONES
		Route::get('/recibo', [ReciboController::class, 'index']);
		Route::get('/pagotransferencia', [ReciboController::class, 'indexPagos']);
		Route::get('/facturacion', [FacturacionController::class, 'index']);
		Route::get('/cuotasmultas', [CuotasMultasController::class, 'index']);
		//ADMINISTRATIVO
		Route::get('/porteria', [PorteriaController::class, 'index']);
		Route::post('/loadrut', [InstaladorController::class, 'rut']);
		Route::get('/instalacionempresa', [InstaladorController::class, 'index']);
		Route::post('/instalacionempresa', [InstaladorController::class, 'instalacionEmpresa']);
		Route::post('/porteria', [PorteriaController::class, 'create']);
		Route::post('/porteriaevento', [PorteriaEventoController::class, 'create']);
		Route::get('/estadocuenta', [EstadoCuentaController::class, 'index']);
		//IMPORTADOR
		Route::get('/importrecibos', [ImportadorRecibosController::class, 'index']);
		Route::get('/importrecibos-exportar', [ImportadorRecibosController::class, 'exportar']);
		Route::post('/importrecibos-importar', [ImportadorRecibosController::class, 'importar']);
		//CONFIGURACION
		Route::get('/entorno', [EntornoController::class, 'index']);
		Route::get('/usuarios', [UsuariosController::class, 'index']);
	});
	
});
            

