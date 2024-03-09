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
use App\Http\Controllers\Sistema\EstadoCuentaController;
//CONFIGURACION
use App\Http\Controllers\Sistema\EntornoController;
use App\Http\Controllers\Empresa\UsuariosController;

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
		Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('dashboard');
		Route::get('/home', [HomeController::class, 'index'])->name('home');
		//TABLAS
		Route::get('/nit', [NitController::class, 'index']);
		Route::get('/zona', [ZonasController::class, 'index']);
		Route::get('/inmueble', [InmuebleController::class, 'index']);
		Route::get('/conceptofacturacion', [ConceptoFacturacionController::class, 'index']);
		//OPERACIONES
		Route::get('/recibo', [ReciboController::class, 'index']);
		Route::get('/facturacion', [FacturacionController::class, 'index']);
		Route::get('/cuotasmultas', [CuotasMultasController::class, 'index']);
		//ADMINISTRATIVO
		Route::get('/estadocuenta', [EstadoCuentaController::class, 'index']);
		//CONFIGURACION
		Route::get('/entorno', [EntornoController::class, 'index']);
		Route::get('/usuarios', [UsuariosController::class, 'index']);
	});
	
});
            

