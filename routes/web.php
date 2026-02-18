<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\LandingpageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CpanelController;
use App\Http\Controllers\DivisasController;
use App\Http\Controllers\MensajesController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CuadreController;

use App\Http\Controllers\VentasController;
use App\Http\Controllers\ContabilidadController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Llamada al Index del LandinPage
Route::get('/', [LandingpageController::class, 'index'])->name('landingpage.index');

// Login
Route::post('/login', [AuthController::class, 'login'])->name('login');

// Ruta para redireccionar al index.blade.php en landingpage
Route::get('/login', function () {
    return redirect()->route('landingpage.index');
});

// Cerrar sesion
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Redireccionar al cPanel al hacer Login
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');



// Consultar tasa del dia
Route::get('/divisa/tasa/{fecha}', [DivisasController::class, 'obtenerTasaCambioDiaria']);

// Recuperar password
Route::post('/auth/recover-password', [AuthController::class, 'recoverPassword']);

// Rutas protegidas (Acceso solo con login)
Route::middleware('auth')->group(function() {

    // Acceso al cPanel 
    Route::get('/cpanel/dashboard', [CpanelController::class, 'dashboard'])->name('cpanel.dashboard');

    // Guardar o Actualizar Tasa del Dia
    Route::post('/divisas/guardar-tasa', [DivisasController::class, 'guardarTasa'])->name('divisas.guardarTasa');

    // Agregar un nuevo mensaje o publicidad
    Route::post('/mensajes/enviar', [MensajesController::class, 'enviarMensaje'])->name('admin.publicidad.store');

    // Seleccionar un Sucursal
    Route::get('/seleccionar-sucursal/{id}', [SucursalController::class, 'seleccionar'])->name('seleccionar.sucursal');

    // Obtener ranking de sucursales por rango de fechas
    Route::get('/cpanel/dashboard/ranking', [CpanelController::class, 'obtenerRankingSucursales'])->name('cpanel.dashboard.ranking');

    // Obtener datos de gráfica Producción Mensual vía AJAX
    Route::get('/cpanel/dashboard/produccion', [CpanelController::class, 'obtenerProduccionMensual'])
     ->name('cpanel.dashboard.produccion');

    // Obtener ranking de vendedores
    Route::get('cpanel/ranking-vendedores', [CpanelController::class, 'obtenerRankingVendedores'])
    ->name('cpanel.ranking-vendedores');

    // Resumen Ventas
    Route::get('/cpanel/resumen', [CpanelController::class, 'resumen_ventas'])->name('cpanel.resumen.ventas');

    // Estados de Cuentas
    Route::get('/cpanel/estados/cuentas', [CpanelController::class, 'estado_cuentas'])->name('cpanel.estado.cuentas');

    // Comparativa entre sucursales
    Route::get('/cpanel/comparativa/sucursales', [CpanelController::class, 'comparativa_sucursales'])->name('cpanel.comparativa.sucursales');

    // Indice de Rotacion
    Route::get('/cpanel/indice/rotacion', [CpanelController::class, 'indice_rotacion'])->name('cpanel.indice.rotacion');

    // Comparativa de precios
    Route::get('/cpanel/baja/demanda', [CpanelController::class, 'baja_demanda'])->name('cpanel.baja.ventas');

    // Actualizar PVP de un producto
    Route::post('/ruta/actualizar-pvp', [ProductoController::class, 'actualizarPVP']);

    // Ventas Diarias
    Route::get('/cpanel/ventas/diarias', [VentasController::class, 'ventas_diarias'])->name('cpanel.ventas.diarias');

    // Eliminar Venta Diaria
    Route::post('/ventas-diarias/eliminar', [VentasController::class, 'eliminar_venta'])->name('ventas-diarias.eliminar');

    // Ver Lista de Productos en una Venta Diaria
    Route::get('/ventas-diarias/detalle/{ventaId}/{sucursalId}', [VentasController::class, 'detalleVenta'])->name('ventas.detalle');

    // Cargar Ventas Diarias
    Route::get('/cpanel/cargar/ventas/diarias', [VentasController::class, 'cargar_ventas_diarias'])->name('cpanel.cargar.ventas.diarias');

    // Guardar registros de las ventas diarias y ventas de los vendedores
    Route::post('/ventas/store', [VentasController::class, 'store'])->name('ventas.store');

    // Ventas por producto
    Route::get('/cpanel/ventas/producto', [VentasController::class, 'ventas_producto'])->name('cpanel.ventas.producto');

    // Producto detalle
    Route::get('/productos/{id}', [ProductoController::class, 'show'])->name('productos.show');

    // Resumen Diario (Cuadre de caja)
    Route::get('/cpanel/cuadre/resumen', [CuadreController::class, 'resumen_diario'])->name('cpanel.cuadre.resumen_diario');

    // Detalles del Cierre Diario
    Route::get('/cierre/detalle/{cierreDiario}', [CuadreController::class, 'detalle'])->name('cierre.detalle');

    // Registrar Cierre Diario
    Route::get('/cpanel/registrar/cierre', [CuadreController::class, 'listar_registro_cierre'])->name('cpanel.cuadre.registrar_cierre');

    // Verificar estatus Cierre Diario
    Route::post('/cuadre/verificar', [CuadreController::class, 'verificarCierre'])->name('cpanel.cuadre.verificar');

    // Crear Cierre Diario
    Route::get('/cpanel/cierre/diario', [CuadreController::class, 'crear'])->name('cpanel.cuadre.crear');

    // Ruta para actualizar cierre diario
    Route::post('/cierres-diarios/{cierre}/actualizar', [CuadreController::class, 'actualizar'])->name('cierres-diarios.actualizar');

    // Editar Cierre Diario Pendiente
    Route::get('/cierre/editar/{cierreDiario}', [CuadreController::class, 'editar'])->name('cierre.editar');

    // Gastos Diarios    
    Route::prefix('gastos-diarios')->group(function () {
        Route::post('/guardar', [CuadreController::class, 'guardar_gasto'])->name('gastos-diarios.guardar');
        Route::get('/listar/{cierreId}', [CuadreController::class, 'listar_gastos'])->name('gastos-diarios.listar');
        Route::delete('/eliminar/{gasto}', [CuadreController::class, 'eliminar_gasto'])->name('gastos-diarios.eliminar');
    });

    // Auditar Cierre Diario
    Route::get('/cpanel/auditar/cierre', [CuadreController::class, 'listar_auditar_cierre'])->name('cpanel.cuadre.auditar_cierre');

    // Editar y Auditar Cierre Diario
    Route::get('/cierre/editar/auditar/{cierreDiario}', [CuadreController::class, 'editar_auditar'])->name('cierre.editar_auditoria');

    // Consolidado Financiero
    Route::get('/cpanel/consolidado', [CuadreController::class, 'listar_consolidado'])->name('cpanel.cuadre.consolidado');

    // Resumen Consolidacion Financiera
    Route::get('/cuadre/resumen/consolidacion', [CuadreController::class, 'resumen_consolidacion'])->name('cpanel.cuadre.resumen_consolidado');

    // Balance General
    Route::get('/cpanel/contabilidad/general', [ContabilidadController::class, 'balance_general'])->name('cpanel.contabilidad.balance_general');
});



// Prueba de conexion con la BD
Route::get('/test-db', function () {
    try {
        // Intentamos hacer una consulta simple para verificar la conexión
        $result = DB::select('SELECT 1 AS prueba'); 

        return response()->json([
            'status' => 'success',
            'message' => 'Conexión exitosa a la base de datos SQL Server',
            'data' => $result
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error de conexión a la base de datos',
            'error' => $e->getMessage()
        ], 500);
    }
});

// Conocer estructura de la BD
Route::get('/db-tables', function () {
    $tables = DB::select("
        SELECT TABLE_NAME 
        FROM INFORMATION_SCHEMA.TABLES 
        WHERE TABLE_TYPE = 'BASE TABLE'
    ");

    return $tables;
});

// Conocer los campos de una tabla en la BD
Route::get('/db-columns/{table}', function ($table) {
    $columns = DB::select("
        SELECT *
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = '$table'
    ");

    return $columns;
});

// Ruta temporal para obtener toda la estructura de la BD
Route::get('/db-structure', function () {
    $tables = DB::select("
        SELECT TABLE_NAME 
        FROM INFORMATION_SCHEMA.TABLES 
        WHERE TABLE_TYPE = 'BASE TABLE'
    ");

    $output = [];

    foreach ($tables as $t) {
        $table = $t->TABLE_NAME;

        $columns = DB::select("
            SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = '$table'
        ");

        $pk = DB::select("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_NAME = '$table'
            AND OBJECTPROPERTY(OBJECT_ID(CONSTRAINT_SCHEMA + '.' + QUOTENAME(CONSTRAINT_NAME)), 'IsPrimaryKey') = 1
        ");

        $output[$table] = [
            'columns' => $columns,
            'primary_key' => $pk
        ];
    }

    return response()->json($output);
});

// Obtener Views en BD Sql Server
Route::get('/db-views', function () {
    // Obtener todas las views
    $views = DB::select("
        SELECT TABLE_NAME 
        FROM INFORMATION_SCHEMA.VIEWS
    ");

    $output = [];

    foreach ($views as $v) {
        $view = $v->TABLE_NAME;

        // Obtener columnas de cada view
        $columns = DB::select("
            SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_NAME = '$view'
        ");

        $output[$view] = [
            'columns' => $columns
        ];
    }

    return response()->json($output);
});