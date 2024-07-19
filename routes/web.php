<?php

// use Illuminate\Http\Request;
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
use App\Http\Controllers\Portafolio\CarteraController;
//TABLAS
use App\Http\Controllers\Sistema\ZonasController;
use App\Http\Controllers\Sistema\InmuebleController;
use App\Http\Controllers\Sistema\InmuebleNitController;
use App\Http\Controllers\Sistema\ConceptoFacturacionController;
//OPERACIONES
use App\Http\Controllers\Sistema\FacturacionController;
use App\Http\Controllers\Sistema\CuotasMultasController;
//ADMINISTRATIVO
use App\Http\Controllers\Sistema\PqrsfController;
use App\Http\Controllers\Sistema\PorteriaController;
use App\Http\Controllers\Empresa\InstaladorController;
use App\Http\Controllers\Sistema\EstadoCuentaController;
use App\Http\Controllers\Sistema\PorteriaEventoController;
//CONFIGURACION
use App\Http\Controllers\Empresa\PerfilController;
use App\Http\Controllers\Sistema\EntornoController;
use App\Http\Controllers\Empresa\UsuariosController;
//IMPORTADOR
use App\Http\Controllers\Sistema\ImportadorInmuebles;
use App\Http\Controllers\Sistema\ImportadorCuotasMultas;
use App\Http\Controllers\Sistema\ImportadorRecibosController;

// //MODELOS
// use App\Models\Empresa\Empresa;
// use App\Models\Sistema\Porteria;
// use App\Models\Sistema\InmuebleNit;
// use App\Models\Empresa\UsuarioEmpresa;

Route::get('/', function () {
	return view('pages.landing-page');
});

Auth::routes();

Route::get('/login', [LoginController::class, 'show'])->middleware('guest')->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest')->name('login.perform');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

Route::group(['middleware' => ['auth:sanctum']], function () {

	Route::group(['middleware' => ['clientconnectionweb']], function () {

		// Route::get('/actualizar-nits-apartamentos', [InmuebleNitController::class, 'fast']);
		// Route::get('/crear-porteria-items', function (Request $request) {
		// 	$inmuebleNit = InmuebleNit::with('nit')->groupBy('id_nit')->get();
			
		// 	if (count($inmuebleNit)) {
		// 		foreach ($inmuebleNit as $nit) {
		// 			$usuarioNit = UsuarioEmpresa::where('id_empresa', request()->user()->id_empresa)
		// 				->where('id_nit', $nit->id)
		// 				->first();

		// 			if ($usuarioNit) {
		// 				Porteria::create([
		// 					'id_nit' => $nit->id,
		// 					'id_usuario' => $usuarioNit->id_usuario,
		// 					'tipo_porteria' => $nit->tipo,
		// 					'tipo_vehiculo' => null,
		// 					'tipo_mascota' => null,
		// 					'nombre' => $nit->nit->nombre_completo,
		// 					'dias' => null,
		// 					'placa' => null,
		// 					'hoy' => null,
		// 					'observacion' => null,
		// 					'created_by' => request()->user()->id,
		// 					'updated_by' => request()->user()->id
		// 				]);
		// 			}
		// 		}
		// 	}
		// 	return json_encode('items de porteria creados con exito');
		// });

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
		Route::get('/facturacion-pdf', [FacturacionController::class, 'index']);
		Route::get('/facturacion-show-pdf', [FacturacionController::class, 'showPdf']);
		Route::get('/facturacion-multiple-show-pdf', [FacturacionController::class, 'showMultiplePdf']);
		Route::get('/cuotasmultas', [CuotasMultasController::class, 'index']);
		//ADMINISTRATIVO
		Route::get('/porteria', [PorteriaController::class, 'index']);
		Route::post('/loadrut', [InstaladorController::class, 'rut']);
		Route::get('/instalacionempresa', [InstaladorController::class, 'index']);
		Route::post('/instalacionempresa', [InstaladorController::class, 'instalacionEmpresa']);
		Route::get('/estadocuenta', [EstadoCuentaController::class, 'index']);
		//PERFIL
		Route::get('/perfil', [PerfilController::class, 'index']);
		Route::post('/perfil-fondo', [PerfilController::class, 'fondo']);
		Route::post('/perfil-avatar', [PerfilController::class, 'avatar']);
		//PORTERIA
		Route::post('/porteria', [PorteriaController::class, 'create']);
		Route::post('/porteriaevento', [PorteriaEventoController::class, 'create']);
		//PQRSF
		Route::get('/pqrsf', [PqrsfController::class, 'index']);
		Route::post('/pqrsf', [PqrsfController::class, 'create']);
		Route::post('/pqrsf-mensaje/{id}', [PqrsfController::class, 'createMensaje']);
		//IMPORTADOR PAGOS
		Route::get('/importrecibos', [ImportadorRecibosController::class, 'index']);
		Route::get('/importrecibos-exportar', [ImportadorRecibosController::class, 'exportar']);
		Route::post('/importrecibos-importar', [ImportadorRecibosController::class, 'importar']);
		//IMPORTADOR CUOTAS EXTRAS
		Route::get('/importcuotas', [ImportadorCuotasMultas::class, 'index']);
		Route::get('/importcuotas-exportar', [ImportadorCuotasMultas::class, 'exportar']);
		Route::post('/importcuotas-importar', [ImportadorCuotasMultas::class, 'importar']);
		//IMPORTAR INMUEBLES
		Route::get('/importinmuebles', [ImportadorInmuebles::class, 'index']);
		Route::get('/importinmuebles-exportar', [ImportadorInmuebles::class, 'exportar']);
		Route::post('/importinmuebles-importar', [ImportadorInmuebles::class, 'importar']);
		//CONFIGURACION
		Route::get('/entorno', [EntornoController::class, 'index']);
		Route::get('/usuarios', [UsuariosController::class, 'index']);
		//INFORMES
		Route::get('/cartera', [CarteraController::class, 'index']);
		Route::get('/facturaciones', [FacturacionController::class, 'indexPdf']);
	});
	
});
            

