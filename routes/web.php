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
//TABLAS
use App\Http\Controllers\Sistema\ConceptoFacturacionController;
//CONFIGURACION
use App\Http\Controllers\Sistema\EntornoController;

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
		Route::get('/conceptofacturacion', [ConceptoFacturacionController::class, 'index'])->name('entorno');
		//ENTORNO
		Route::get('/entorno', [EntornoController::class, 'index'])->name('entorno');
	});
	
});
            

