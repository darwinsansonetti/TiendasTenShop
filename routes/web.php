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
use App\Http\Controllers\EmpleadosController;
use App\Http\Controllers\ProveedoresController;
use App\Http\Controllers\RecepcionesController;
use App\Http\Controllers\DistribucionController;
use App\Http\Controllers\InventarioController;

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

    // Vista Cerrar Dia
    Route::get('/cpanel/contabilidad/show/cerrar', [ContabilidadController::class, 'enviar_listado_cerrar_dia'])->name('cpanel.contabilidad.show_cerrar_dia');

    // Probar Cerrar Dia
    Route::get('/cpanel/contabilidad/probar/cerrar', [ContabilidadController::class, 'cerrar_dia_automaticamente'])->name('cpanel.contabilidad.probar_cerrar_dia');

    // Ventas Diarias - Empleados
    Route::get('/cpanel/empleados/ventas', [EmpleadosController::class, 'listado_empleados'])->name('cpanel.empleados.ventas_diarias');

    // Detalles de venta
    Route::get('/detalles-venta/{id}/{vti}', [EmpleadosController::class, 'detallesVenta'])->name('detalles-venta');

    // Ventas Diarias - Empleados
    Route::get('/cpanel/empleados/ranking', [EmpleadosController::class, 'listado_ranking'])->name('cpanel.empleados.ranking');

    // Ventas de un vendedor en un periodo
    Route::get('/cpanel/empleados/ventas-vendedor/{id}', [EmpleadosController::class, 'ventasVendedor'])->name('cpanel.empleados.ventas.vendedor');

    // Listado de Vendedores
    Route::get('/cpanel/empleados/vendedores', [EmpleadosController::class, 'listado_vendedores'])->name('cpanel.empleados.vendedores');

    // Editar Informacion del Vendedor
    Route::get('/cpanel/empleados/vendedor/editar/{id}', [EmpleadosController::class, 'editarVendedor'])->name('cpanel.empleados.vendedor.editar');

    // Ruta para ACTUALIZAR vendedor (PUT/PATCH)
    Route::put('/cpanel/empleados/vendedor/actualizar/{id}', [EmpleadosController::class, 'actualizarVendedor'])->name('cpanel.empleados.vendedor.actualizar');

    // Listado de Personal Interno
    Route::get('/cpanel/empleados', [EmpleadosController::class, 'listado_personal'])->name('cpanel.empleados.personal');

    // Editar Empleado Interno
    Route::get('/cpanel/empleados/internos/editar/{id}', [EmpleadosController::class, 'editarEmpleadoInterno'])->name('cpanel.empleados.internos.editar');

    // Actualizar Empleado Interno
    Route::put('/cpanel/empleados/internos/actualizar/{id}', [EmpleadosController::class, 'actualizarEmpleadoInterno'])->name('cpanel.empleados.internos.actualizar');

    // Vista para cambiar contraseña
    Route::get('/cpanel/empleados/internos/password/{id}', [EmpleadosController::class, 'cambiarPassword'])->name('cpanel.empleados.internos.password');
    
    // Procesar cambio de contraseña
    Route::put('/cpanel/empleados/internospassword/{id}', [EmpleadosController::class, 'actualizarPassword'])->name('cpanel.empleados.internos.password.update');

    // Vista para agregar empleado
    Route::get('/cpanel/empleados/agregar', [EmpleadosController::class, 'agregarEmpleado'])->name('cpanel.empleados.agregar');

    // Guardar nuevo empleado
    Route::post('/cpanel/empleados/internos/store', [EmpleadosController::class, 'guardarEmpleado'])->name('cpanel.empleados.internos.guardar');

    // Guardar Usuario como empleado interno
    Route::post('/cpanel/vendedores/crear-identity', [EmpleadosController::class, 'crearUsuarioIdentity'])->name('cpanel.vendedores.crear-identity');

    // Obtener siguiente codigo de vendedor a asignar
    Route::get('/cpanel/empleados/obtener-proximo-vendedor-id/{sucursalId}', [EmpleadosController::class, 'obtenerProximoVendedorId'])->name('cpanel.empleados.obtener.proximo.vendedor.id');

    // Listado de liberalidad
    Route::get('/cpanel/empleados/liberalidad', [EmpleadosController::class, 'listado_liberalidad'])->name('cpanel.empleados.lista_liberalidad');

    // Detalles de LiberalidadDTO
    Route::get('/empleado/liberalidad-detalle/{id}', [EmpleadosController::class, 'verDetalleLiberalidad'])->name('liberalidad.detalle');

    // Listado de empleados con bonos
    Route::get('/cpanel/empleados/listado/bonos', [EmpleadosController::class, 'listado_empleados_bonos'])->name('cpanel.empleados.lista_empleados_bonos');

    // Asignar Bonos
    Route::get('/cpanel/empleados/bonos/asignar/{tipo}/{id}', [EmpleadosController::class, 'asignarBono'])->name('cpanel.empleados.bonos.asignar');

    // Guardar Bonos
    Route::post('/cpanel/empleados/bonos/guardar', [EmpleadosController::class, 'guardarBono'])->name('cpanel.empleados.bonos.guardar');

    // Eliminar Bono
    Route::delete('/cpanel/empleados/bonos/eliminar', [EmpleadosController::class, 'eliminarBono'])->name('cpanel.empleados.bonos.eliminar');

    // Listado de empleados con deducciones
    Route::get('/cpanel/empleados/listado/deducciones', [EmpleadosController::class, 'listado_empleados_deducciones'])->name('cpanel.empleados.lista_empleados_deducciones');

    // Asignar Deducciones
    Route::get('/cpanel/empleados/deducciones/asignar/{tipo}/{id}', [EmpleadosController::class, 'asignarDeduccion'])->name('cpanel.empleados.deduccion.asignar');

    // Guardar Deduccion
    Route::post('/cpanel/empleados/deduccion/guardar', [EmpleadosController::class, 'guardarDeduccion'])->name('cpanel.empleados.deducciones.guardar');

    // Eliminar Deduccion
    Route::delete('/cpanel/empleados/deduccion/eliminar', [EmpleadosController::class, 'eliminarDeduccion'])->name('cpanel.empleados.deducciones.eliminar');

    // Listado de empleados con prestamos
    Route::get('/cpanel/empleados/listado/prestamos', [EmpleadosController::class, 'listado_empleados_prestamos'])->name('cpanel.empleados.lista_empleados_prestamos');

    // Obtener bonos disponibles de un empleado para pagar préstamos
    Route::get('/cpanel/empleados/prestamos/bonos-disponibles/{usuarioId}', [EmpleadosController::class, 'obtenerBonosDisponiblesPrestamo'])
        ->name('cpanel.empleados.prestamos.bonos_disponibles');

    // Registrar pago normal de préstamo (efectivo, transferencia, etc.)
    Route::post('/cpanel/empleados/prestamos/registrar-pago', [EmpleadosController::class, 'registrarPagoNormal'])
        ->name('cpanel.empleados.prestamos.registrar_pago');

    // Registrar pago de préstamo usando bono
    Route::post('/cpanel/empleados/prestamos/registrar-pago-bono', [EmpleadosController::class, 'registrarPagoConBono'])
        ->name('cpanel.empleados.prestamos.registrar_pago_bono');

    // Registrar pago de préstamo usando liberalidad
    Route::post('/cpanel/empleados/prestamos/registrar-pago-liberalidad', [EmpleadosController::class, 'registrarPagoConLiberalidad'])
        ->name('cpanel.empleados.prestamos.registrar_pago_liberalidad');

    // Detalle de préstamos de un empleado
    Route::get('/cpanel/empleados/prestamos/detalle/{usuarioId}', [EmpleadosController::class, 'detallePrestamosEmpleado'])
        ->name('cpanel.empleados.prestamos.detalle');

    // Formulario para solicitar préstamo
    Route::get('/cpanel/empleados/prestamos/solicitar/{usuarioId}', [EmpleadosController::class, 'formularioSolicitarPrestamo'])
        ->name('cpanel.empleados.prestamos.solicitar.form');

    // Guardar solicitud de préstamo
    Route::post('/cpanel/empleados/prestamos/solicitar', [EmpleadosController::class, 'guardarSolicitarPrestamo'])
        ->name('cpanel.empleados.prestamos.solicitar.guardar');

    // Buscar producto por código
    Route::get('/cpanel/empleados/prestamos/buscar-producto', [EmpleadosController::class, 'buscarProductoPorCodigo'])
        ->name('cpanel.empleados.prestamos.buscar_producto');

    // Cerra Liberalidad
    Route::post('/cpanel/empleados/liberalidad/cerrar', [EmpleadosController::class, 'cerrarLiberalidad'])
    ->name('cpanel.empleados.liberalidad.cerrar');

    // Listado de proveedores de mercancia
    Route::get('/cpanel/proveedores/mercancias/listado', [ProveedoresController::class, 'listado_proveedores_mercancia'])->name('cpanel.proveedor.mercancia.listado');

    // Proveedores - Crear nuevo
    Route::get('/cpanel/proveedores/crear', [ProveedoresController::class, 'crearProveedor'])->name('cpanel.proveedores.crear');

    // Proveedores - Guardar nuevo
    Route::post('/cpanel/proveedores/guardar', [ProveedoresController::class, 'guardarProveedor'])->name('cpanel.proveedores.guardar');

    // Proveedores - Editar
    Route::get('/cpanel/proveedores/editar/{id}', [ProveedoresController::class, 'editarProveedor'])
        ->name('cpanel.proveedores.editar');

    Route::put('/cpanel/proveedores/actualizar/{id}', [ProveedoresController::class, 'actualizarProveedor'])
        ->name('cpanel.proveedores.actualizar');

    // Eliminar proveedor
    Route::delete('/cpanel/proveedores/eliminar', [ProveedoresController::class, 'eliminarProveedor'])
        ->name('cpanel.proveedores.eliminar');

    // Proveedores - Detalle
    Route::get('/cpanel/proveedores/detalle/{id}', [ProveedoresController::class, 'detalleProveedor'])
        ->name('cpanel.proveedores.detalle');

    // Registrar Pagos - Listado de proveedores
    Route::get('/proveedor/registrar-pagos', [ProveedoresController::class, 'registrarPagosIndex'])
        ->name('cpanel.proveedor.mercancia.registrar_pagos');

    // Obtener facturas pendientes de un proveedor (para AJAX)
    Route::get('/proveedor/{id}/facturas/pendientes', [ProveedoresController::class, 'getFacturasPendientes'])
        ->name('cpanel.proveedor.facturas.pendientes');

    // // Registrar pago a proveedor
    // Route::get('/proveedor/listar-proveedores', [ProveedoresController::class, 'registrarPagosIndex'])
    //     ->defaults('modo', 'pagos')
    //     ->name('cpanel.proveedor.mercancia.registrar_pagos');

    // Proveedores - Registrar Pago
    Route::get('/cpanel/proveedores/registrar/pago/{id}', [ProveedoresController::class, 'pagarProveedor'])
        ->name('cpanel.proveedores.pagar');

    // Proveedores - Registrar Factura
    Route::get('/cpanel/proveedores/registrar/factura/{id}', [ProveedoresController::class, 'facturaRegistroProveedor'])
        ->name('cpanel.proveedores.nueva.factura');

    // Guardar Factura
    Route::post('/cpanel/facturas/guardar', [ProveedoresController::class, 'generarFactura'])->name('cpanel.facturas.guardar');

    // Registrar Facturas
    Route::get('/proveedor/registrar-facturas', [ProveedoresController::class, 'registrarPagosIndex'])
        ->defaults('modo', 'facturas')
        ->name('cpanel.proveedor.mercancia.registrar_facturas');

    // Ruta para la automatización
    Route::post('/cpanel/automatizacion/ejecutar', [CpanelController::class, 'ejecutarAutomatizacion'])
        ->name('cpanel.automatizacion.ejecutar')
        ->middleware('auth');    

    // Alta Demanda
    Route::get('/cpanel/alta/demanda', [CpanelController::class, 'alta_demanda'])->name('cpanel.alta.ventas');

    Route::post('/cpanel/automatizacion/subir', [CpanelController::class, 'ejecutarSubidaPrecios'])->name('cpanel.automatizacion.subir');

    Route::get('/cpanel/alta-demanda', [CpanelController::class, 'alta_demanda'])->name('cpanel.alta.demanda');

    Route::post('/cpanel/automatizacion/subir', [CpanelController::class, 'ejecutarSubidaPrecios'])->name('cpanel.automatizacion.subir');

    // Facturas en Proveedor de Mercancia
    Route::get('/cpanel/facturas/{id}/detalle', [ProveedoresController::class, 'detalle'])->name('cpanel.facturas.detalle');
    Route::get('/cpanel/facturas/{id}/editar', [ProveedoresController::class, 'editar'])->name('cpanel.facturas.editar');
    Route::delete('/cpanel/facturas/{id}', [ProveedoresController::class, 'eliminar'])->name('cpanel.facturas.eliminar');
    Route::put('/cpanel/facturas/{id}', [ProveedoresController::class, 'actualizar'])->name('cpanel.facturas.actualizar');

    Route::get('/cpanel/buscar-producto', [ProveedoresController::class, 'buscarProductoProveedor'])->name('cpanel.productos.buscar.proveedor');
    Route::post('/facturas/{id}/guardar-productos', [ProveedoresController::class, 'guardarProductosFactura'])->name('cpanel.facturas.guardar.productos');
    Route::post('/facturas/{id}/agregar-producto', [ProveedoresController::class, 'agregarProductoFactura'])->name('cpanel.facturas.agregar.producto');
    Route::post('/facturas/{id}/upload-excel', [ProveedoresController::class, 'uploadProductosFactura'])->name('cpanel.facturas.upload.excel');

    // Ruta para guardar productos desde Excel (guardar todos los productos de la tabla)
    Route::post('/facturas/{id}/guardar-excel', [ProveedoresController::class, 'guardarExcelFactura'])->name('cpanel.facturas.guardar.excel');

    // Pagar Factura a Proveedor
    Route::post('/pagos', [ProveedoresController::class, 'store'])->name('cpanel.pagos.store');

    // Acciones en los pagos
    Route::get('/pagos/{id}/detalle', [ProveedoresController::class, 'detallePago'])->name('cpanel.pagos.detalle');
    Route::get('/pagos/{id}/editar', [ProveedoresController::class, 'editarPago'])->name('cpanel.pagos.editar');
    Route::put('/pagos/{id}', [ProveedoresController::class, 'actualizarPago'])->name('cpanel.pagos.actualizar');
    Route::delete('/pagos/{id}', [ProveedoresController::class, 'eliminarPago'])->name('cpanel.pagos.eliminar');
    Route::get('/pagos/{id}/imprimir', [ProveedoresController::class, 'imprimirRecibo'])->name('cpanel.pagos.imprimir');
    Route::get('/pagos/{id}/ver-comprobante', [ProveedoresController::class, 'verComprobante'])->name('cpanel.pagos.ver-comprobante');

    // Recibos
    Route::get('/facturas/{id}/recibo-pagos', [ProveedoresController::class, 'reciboPagosJson'])->name('cpanel.facturas.recibo-pagos');
    Route::get('/facturas/{id}/recibo-productos', [ProveedoresController::class, 'reciboProductos'])->name('cpanel.facturas.recibo-productos');
    Route::get('/proveedores/{id}/recibo-facturas', [ProveedoresController::class, 'reciboListaFacturas'])->name('cpanel.proveedores.recibo-facturas');

    // Contenedores
    Route::get('/proveedor/contenedores', [ProveedoresController::class, 'listaContenedores'])->name('cpanel.proveedor.mercancia.contenedores');

    Route::get('/contenedores/crear', [ProveedoresController::class, 'crearContenedor'])->name('cpanel.contenedores.crear');
    Route::post('/contenedores', [ProveedoresController::class, 'guardarContenedor'])->name('cpanel.contenedores.guardar');
    Route::get('/contenedores/{id}/detalle', [ProveedoresController::class, 'detalleContenedor'])->name('cpanel.contenedores.detalle');
    Route::get('/contenedores/{id}/editar', [ProveedoresController::class, 'editarContenedor'])->name('cpanel.contenedores.editar');
    Route::put('/contenedores/{id}', [ProveedoresController::class, 'actualizarContenedor'])->name('cpanel.contenedores.actualizar');
    Route::delete('/contenedores/{id}', [ProveedoresController::class, 'eliminarContenedor'])->name('cpanel.contenedores.eliminar');

    // Listado de Recepciones Proveedor
    Route::get('/cpanel/recepciones/proveedor/listado', [RecepcionesController::class, 'listado_recepciones_proveedores'])->name('cpanel.recepciones.proveedor');
    // Listado de Recepciones de Sucursal
    Route::get('/cpanel/recepciones/sucursal/listado', [RecepcionesController::class, 'listado_recepciones_sucursal'])->name('cpanel.recepciones.sucursal');
    Route::get('/{id}/recibir', [RecepcionesController::class, 'recibirTransferencia'])->name('recibir');
    
    // Recepciones Proveedor
    Route::get('/recepciones/nuevo', [RecepcionesController::class, 'nuevaRecepcion'])->name('cpanel.recepciones.nuevo');
    Route::get('/recepciones/crear/{proveedorId}', [RecepcionesController::class, 'crearRecepcion'])->name('cpanel.recepciones.crear');
    Route::post('/recepciones/guardar', [RecepcionesController::class, 'guardarRecepcion'])->name('cpanel.recepciones.guardar');

    //Route::get('/recepciones/{id}/detalle', [RecepcionesController::class, 'detalleRecepcion'])->name('cpanel.recepciones.detalle');
    Route::get('/recepciones/{id}/editar', [RecepcionesController::class, 'editarRecepcion'])->name('cpanel.recepciones.editar');
    Route::put('/recepciones/{id}', [RecepcionesController::class, 'actualizarRecepcion'])->name('cpanel.recepciones.actualizar');
    Route::delete('/cpanel/recepciones/{id}', [RecepcionesController::class, 'eliminarRecepcion'])
    ->name('cpanel.recepciones.eliminar');

    Route::post('/recepciones/{id}/recuperar-factura', [RecepcionesController::class, 'recuperarFactura'])->name('cpanel.recepciones.recuperar.factura');

    // ✅ NUEVA RUTA: Devuelve datos en JSON para armar el Excel
    Route::get('/{recepcionId}/datos-exportacion', [RecepcionesController::class, 'getDatosExportacion'])->name('datos-exportacion');

    // Subir productos en recepcion
    Route::post('/{id}/upload-excel', [RecepcionesController::class, 'uploadExcel'])->name('cpanel.recepciones.upload-excel');

    // Guardar el Recibir Recepcion del Proveedor
    Route::post('/cpanel/recepciones/{id}/finalizar', [RecepcionesController::class, 'finalizarRecepcion'])
    ->name('cpanel.recepciones.finalizar');

    Route::post('/cpanel/recepciones/{id}/actualizar-detalles', [RecepcionesController::class, 'actualizarDetallesRecepcion'])
    ->name('cpanel.recepciones.actualizar-detalles');

    Route::post('/cpanel/recepciones/{id}/asociar-factura', [RecepcionesController::class, 'asociarFactura'])->name('cpanel.recepciones.asociar-factura');

    // routes/web.php
    Route::post('/cpanel/recepciones/guardar-productos', [RecepcionesController::class, 'guardarProductos'])->name('cpanel.recepciones.guardar-productos');

    // Listado de Auditorias de Recepciones de Proveedor
    Route::get('/cpanel/recepciones/auditorias/listado', [RecepcionesController::class, 'listado_recepciones_auditoria'])->name('cpanel.recepciones.auditorias');

    // Obtener la Auditoria
    Route::get('/cpanel/auditorias/{id}/procesar', [RecepcionesController::class, 'procesarAuditoria'])->name('cpanel.auditorias.procesar');

    // Rutas generales
    Route::post('/cpanel/auditorias/{id}/aprobar', [RecepcionesController::class, 'aprobarAuditoria'])->name('aprobar');
    Route::post('/cpanel/auditorias/{id}/rechazar', [RecepcionesController::class, 'rechazarAuditoria'])->name('rechazar');
    
    // Rutas por producto
    Route::post('/cpanel/auditorias/producto/{id}/aprobar', [RecepcionesController::class, 'aprobarProducto'])->name('aprobar.producto');
    Route::post('/cpanel/auditorias/producto/{id}/rechazar', [RecepcionesController::class, 'rechazarProducto'])->name('rechazar.producto');

    // routes/web.php
    Route::post('/cpanel/auditorias/producto/{id}/editar-recibido', [RecepcionesController::class, 'editarRecibido'])
        ->name('auditorias.producto.editar-recibido');

    // Listado de Recepciones Finalizadas
    Route::get('/cpanel/recepciones/finalizadas/listado', [RecepcionesController::class, 'listado_recepciones_finalizadas'])->name('cpanel.recepciones.finalizadas');

    // Exportar excel de la recepcion
    Route::get('/cpanel/recepciones/{id}/exportar-excel', [RecepcionesController::class, 'exportarRecepcionExcel'])->name('cpanel.recepciones.exportar.excel');

    // Detalles de la recepcion
    Route::get('/cpanel/recepciones/{id}/detalle', [RecepcionesController::class, 'detalleRecepcion'])->name('cpanel.recepciones.detalle');

    // Cambiar precio de la recepcion finalizada
    Route::get('/cpanel/recepciones/{id}/precios', [RecepcionesController::class, 'pvpRecepcion'])->name('cpanel.recepciones.precios');

    // Crear Recepciones de sucursal
    Route::get('/transferencias/create', [RecepcionesController::class, 'createTransferencia'])->name('cpanel.transferencias.create');

    // Nueva Distribucion
    Route::get('/cpanel/distribucion/listado', [DistribucionController::class, 'distribuciones_listado'])
        ->name('cpanel.distribucion.distribuciones');

    Route::post('/cpanel/distribuciones/{id}/finalizar', [DistribucionController::class, 'finalizarDistribucion'])
        ->name('cpanel.distribuciones.finalizar');

    // Cancelar distribucion en la vista Nueva Distribucion (Listado Distribuciones)
    Route::post('/cpanel/distribuciones/{id}/cancelar', [DistribucionController::class, 'cancelarDistribucion'])
        ->name('cpanel.distribuciones.cancelar');

    Route::get('/cpanel/distribuciones/create', [DistribucionController::class, 'createDistribucion'])
        ->name('cpanel.distribuciones.create');

    Route::post('/cpanel/distribuciones', [DistribucionController::class, 'storeDistribucion'])
        ->name('cpanel.distribuciones.store');

    Route::get('/cpanel/distribuciones/{id}/edit', [DistribucionController::class, 'editDistribucion'])
        ->name('cpanel.distribuciones.edit');

    // ✅ Agregar rutas faltantes
    Route::get('/cpanel/distribuciones', [DistribucionController::class, 'indexDistribuciones'])
        ->name('cpanel.distribuciones.index');

    Route::put('/cpanel/distribuciones/{id}', [DistribucionController::class, 'updateDistribucion'])
        ->name('cpanel.distribuciones.update');

    Route::post('/cpanel/distribuciones/asociar-sucursal', [DistribucionController::class, 'asociarSucursal'])
        ->name('cpanel.distribuciones.asociar-sucursal');

    Route::post('/cpanel/distribuciones/remover-sucursal', [DistribucionController::class, 'removerSucursal'])
        ->name('cpanel.distribuciones.remover-sucursal');

    Route::post('/cpanel/distribuciones/upload-excel', [DistribucionController::class, 'uploadExcelDistribucion'])
        ->name('cpanel.distribuciones.upload-excel');

    Route::get('/cpanel/distribuciones/download-details', [DistribucionController::class, 'downloadDetailsTransferencia'])
    ->name('cpanel.distribuciones.download-details');

    Route::post('/cpanel/distribuciones/upload-excel', [DistribucionController::class, 'uploadExcelDistribucion'])
    ->name('cpanel.distribuciones.upload-excel');    

    // Listado Distribuciones / Transferencias
    Route::get('/cpanel/distribuciones/listado-transferencias', [DistribucionController::class, 'distribuciones_listado_aceptar'])
        ->name('cpanel.distribucion.listado');

    // Recibir Recepcion en la Sucursal
    Route::post('/cpanel/transferencias/{id}/finalizar-recibir', [DistribucionController::class, 'finalizarRecibirTransferencia'])
    ->name('cpanel.transferencias.finalizar-recibir');

    // Recibir transferencia (muestra la vista para recibir productos)
    Route::get('/cpanel/transferencias/{id}/recibir', [DistribucionController::class, 'recibirTransferenciaProducto'])
        ->name('cpanel.transferencias.recibir-productos');

    // Ver detalle de transferencia
    Route::get('/cpanel/transferencias/{id}/detalle', [DistribucionController::class, 'detalleTransferencia'])
        ->name('cpanel.transferencias.detalle');

    // Distribucion - Inventario de almacen
    Route::get('/cpanel/distribucion/inventario', [DistribucionController::class, 'distribuciones_inventario'])
        ->name('cpanel.distribucion.inventario');

    // Ver detalle de recepcion en la sucursal
    Route::get('/cpanel/recepcion/{id}/detallesucursal', [RecepcionesController::class, 'detalleTransferenciaSucursal'])
        ->name('cpanel.transferencias.detallesucursal');

    // Nueva Recepcion en la Sucursal
    Route::post('/cpanel/recepcion/crear-recepcion/{id}', [RecepcionesController::class, 'crearRecepcionTransferencia'])
        ->name('cpanel.recepcion.crear-recepcion');

    // ✅ Descargar plantilla Excel para recepción
    Route::get('/cpanel/download-template/{id}', [RecepcionesController::class, 'downloadTemplateRecepcion'])
        ->name('cpanel.recibir-sucursal.download-template');

    // ✅ Cargar Excel con cantidades recibidas
    Route::post('/cpanel/upload-excel/{id}', [RecepcionesController::class, 'uploadExcelRecepcion'])
        ->name('cpanel.recibir-sucursal.upload-excel');

    // ✅ Confirmar/Finalizar recepción (POST)
    Route::post('/cpanel/confirmar/{id}', [RecepcionesController::class, 'confirmarRecepcion'])
        ->name('cpanel.recibir-sucursal.confirmar');

    // Ruta para actualizar precios de una recepción finalizada
    Route::post('/cpanel/recepciones/{id}/actualizar-precios', [RecepcionesController::class, 'actualizarPrecios'])
        ->name('cpanel.recepciones.actualizar-precios');

    // Llamado a la vista para cargar cambios de precios masivos
    Route::get('/cpanel/productos/cambios/masivos', [RecepcionesController::class, 'mostrarCargaProductos'])
        ->name('cpanel.productos.cambiar.pvp');

    // Guardar precios masivos de productos
    Route::post('/cpanel/precios/guardar-manual', [RecepcionesController::class, 'guardarPreciosManual'])
    ->name('cpanel.precios.guardar-manual');

    // Llamado a la vista para cargar excel de inventario
    Route::get('/cpanel/inventtario/cargar/excel', [InventarioController::class, 'mostrarCargaInventarios'])
        ->name('cpanel.inventario.cargar.excel');

    // Cargar Excel del Inventario
    Route::post('/cpanel/inventario/cargar-excel', [InventarioController::class, 'cargarExcel'])
    ->name('cpanel.inventario.cargar-excel');
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