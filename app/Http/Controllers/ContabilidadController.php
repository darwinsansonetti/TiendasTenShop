<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AspNetUser;
use App\Helpers\GeneralHelper;
use App\Helpers\VentasHelper;

use App\Helpers\ParametrosFiltroFecha;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Services\VentasService;

use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\DivisaValor;
use App\Models\Venta;
use App\Models\VentaProducto;
use App\Models\VentaVendedor;
use App\Models\Producto;
use App\Models\ProductoSucursal;
use App\Models\Usuario;
use App\DTOs\CierreDiarioPeriodoDTO;
use App\Models\CierreDiario;
use App\Models\PagoPuntoDeVenta;
use App\Models\ValorizacionInventario;
use App\Models\Transaccion;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Enums\EnumTipoFiltroFecha; 
use Illuminate\Support\Facades\Validator;

class ContabilidadController extends Controller
{   

    // Balance general
    // public function balance_general(Request $request)
    // {       
    //     // 🚀 Aquí: usar fechas del request si existen
    //     $fechaInicio = $request->input('fecha_inicio')
    //         ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
    //         : null;

    //     $fechaFin = $request->input('fecha_fin')
    //         ? Carbon::parse($request->input('fecha_fin'))->startOfDay()
    //         : null;

    //     // // $fechaInicio = Carbon::parse('2026-01-01')->startOfDay();
    //     // // $fechaFin = Carbon::parse('2026-01-05')->startOfDay();

    //     $filtroFecha = new ParametrosFiltroFecha(
    //         null,
    //         null,
    //         null,
    //         false,
    //         $fechaInicio,
    //         $fechaFin
    //     );

    //     // Asignacion al menu
    //     session([
    //         'menu_active' => 'Contabilidad',
    //         'submenu_active' => 'Balance General'
    //     ]);

    //     // Buscar todas las Sucursales
    //     $listaSucursales = GeneralHelper::buscarSucursales(0);

    //     $listaBalance = [];

    //     foreach ($listaSucursales as $_sucursal) 
    //     {
    //         // Valorización del Stock por sucursal, desde siempre
    //         $valorizacion = ValorizacionInventario::where('SucursalId', $_sucursal->ID)->first();

    //         // Gastos pendientes por pagar (Incluye cuentas pagas parcialmente)
    //         // Deuda por gastos pendientes (Recepciones / Cuentas a pagar)
    //         // Desde el dia actual hacia atras
    //         $gastos = VentasHelper::BuscarGastosSucursalParaCerrar($_sucursal->ID, null);

    //         $totalDeudaGastos = array_sum(
    //             array_map(fn($g) => $g->getSaldoDivisa(), $gastos)
    //         );

    //         // Ventas por cerrar / a favor, desde el dia actual hacia atras
    //         $ventasService = new VentasService();

    //         $ventas = $ventasService->ObtenerListadoVentasDiariasParaCerrar(
    //                     $filtroFecha,
    //                     $_sucursal->ID,
    //                     true
    //                 );

    //         // Pago de servicios de la sucursal
    //         $listadoPagoServicios = $ventasService->buscarTransacciones(
    //                     1, // TipoTransaccion.Servicio
    //                     $_sucursal->ID,
    //                     ['fecha_inicio' => $filtroFecha->fechaInicio, 'fecha_fin' => $filtroFecha->fechaFin]
    //                 );

    //         // Pagos de facturas / mercancia
    //         $listadoPagoMercancia = $ventasService->buscarTransacciones(
    //                     0, // TipoTransaccion.PagoMercancia
    //                     $_sucursal->ID,
    //                     ['fecha_inicio' => $filtroFecha->fechaInicio, 'fecha_fin' => $filtroFecha->fechaFin]
    //                 );

    //         // Recepciones de la sucursal
    //         $listadoRecepciones = $ventasService->buscarRecepcionesSucursalParaCerrar($_sucursal->ID, $filtroFecha->fechaFin);

    //         // Transferencias de la sucursal
    //         $listadoTransferencias = $ventasService->buscarTransferenciasParaCerrar($filtroFecha, $_sucursal->ID);

    //         // Facturas de proveedores por pagar (solo oficinas)
    //         $listadoFacturas = [];
    //         if ($_sucursal->Tipo == 0) {
    //             $listadoFacturas = $ventasService->buscarFacturasActivas();
    //         }

    //         $listaBalance[] = [
    //             'SucursalId' => $_sucursal->ID,
    //             'SucursalNombre' => $_sucursal->Nombre,
    //             'ValorizacionInventario' => $valorizacion,
    //             'ListadoGastosPorPagar' => $gastos,
    //             'TotalDeudaGastos' => $totalDeudaGastos,
    //             'ventas' => $ventas['ListaVentasDiarias'] ?? [],
    //             'ListadoPagoServicios' => $listadoPagoServicios,
    //             'ListadoPagoMercancia' => $listadoPagoMercancia,
    //             'ListadoRecepciones' => $listadoRecepciones,
    //             'ListadoTransferencias' => $listadoTransferencias,
    //             'Facturas' => $listadoFacturas, 
    //         ];
    //     }

    //     dd($listaBalance);


    //     // 5️⃣ Obtener sucursal activa
    //     // $sucursalId = session('sucursal_id');
    //     // $sucursalNombre = session('sucursal_nombre');

    //     // $cierreDiario = collect();

    //     // if ($sucursalId != 0) {
    //     //     // Llamamos al helper que construye los cierres diarios
    //     //     // $cierreDiario = VentasHelper::buscarListadoAuditorias($cierreDiario, $filtroFecha, $sucursalId);
    //     //     $cierreDiario = VentasHelper::buscarListadoAuditoriasNew($filtroFecha, $sucursalId, 999);
    //     // }

    //     // $totalDivisa = $cierreDiario->sum('EfectivoDivisas');
    //     // $totalEfectivoBs = $cierreDiario->sum('EfectivoBs');
    //     // $totalPagoMovil = $cierreDiario->sum('PagoMovilBs');
    //     // $totalPuntoVenta = $cierreDiario->sum('PuntoDeVentaBs');
    //     // $totalTransferencias = $cierreDiario->sum('TransferenciaBs');
    //     // $totalSistemaBs = $cierreDiario->sum('VentaSistema');
    //     // $totalEgresosBs = $cierreDiario->sum('EgresoBs');
    //     // $totalBiopago = $cierreDiario->sum('Biopago');
    //     // $totalEgresosDivisa = $cierreDiario->sum('EgresoDivisas');

    //     // $totalIngresoBs = $totalEfectivoBs
    //     //             + $totalPagoMovil
    //     //             + $totalPuntoVenta
    //     //             + $totalTransferencias
    //     //             + $totalBiopago;

    //     // $totalBs = $totalIngresoBs - $totalEgresosBs;
    //     // $totalGeneralDivisa = $totalDivisa - $totalEgresosDivisa;
    //     // $diferencia = $totalBs - $totalSistemaBs;

    //     // // dd($cierreDiario);

    //     // // Pasar todo a la vista
    //     // return view('cpanel.cuadre.resumen_diario', [
    //     //     'cierreDiario' => $cierreDiario,
    //     //     'fecha_inicio' => $fechaInicio,
    //     //     'fecha_fin' => $fechaFin,
    //     //     'sucursalId' => $sucursalId,
    //     //     'totalDivisa' => $totalDivisa,
    //     //     'totalEfectivoBs' => $totalEfectivoBs,
    //     //     'totalPagoMovil' => $totalPagoMovil,
    //     //     'totalPuntoVenta' => $totalPuntoVenta,
    //     //     'totalTransferencias' => $totalTransferencias,
    //     //     'totalBiopago' => $totalBiopago,
    //     //     'totalSistemaBs' => $totalSistemaBs,
    //     //     'totalBs' => $totalBs,
    //     //     'totalGeneralDivisa' => $totalGeneralDivisa,
    //     //     'diferencia' => $diferencia,
    //     // ]);
    // }

    // public function balance_general(Request $request)
    // {
    //     // Fechas del request
    //     $fechaInicio = $request->input('fecha_inicio')
    //         ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
    //         : Carbon::now()->startOfMonth(); // Por defecto inicio del mes

    //     $fechaFin = $request->input('fecha_fin')
    //         ? Carbon::parse($request->input('fecha_fin'))->startOfDay()
    //         : Carbon::now()->endOfDay(); // Por defecto hoy

    //     $filtroFecha = new ParametrosFiltroFecha(
    //         null,
    //         null,
    //         null,
    //         false,
    //         $fechaInicio,
    //         $fechaFin
    //     );

    //     // Asignacion al menu
    //     session([
    //         'menu_active' => 'Contabilidad',
    //         'submenu_active' => 'Balance General'
    //     ]);

    //     // Buscar todas las Sucursales
    //     $listaSucursales = GeneralHelper::buscarSucursales(0);
    //     $ventasService = new VentasService();
        
    //     $listaBalance = [];

    //     foreach ($listaSucursales as $_sucursal) 
    //     {
    //         // ========== ACTIVOS ==========
            
    //         // 1. Inventario (Valorización del Stock) - CORREGIDO
    //         $valorizacion = ValorizacionInventario::where('SucursalId', $_sucursal->ID)->first();
            
    //         $totalInventario = 0;
    //         $unidades = 0;
    //         $referencias = 0;
            
    //         if ($valorizacion) {
    //             // El monto del inventario está en 'CostoDivisa'
    //             $totalInventario = (float)($valorizacion->CostoDivisa ?? 0);
    //             $unidades = (int)($valorizacion->Existencia ?? 0);
    //             $referencias = (int)($valorizacion->Referencias ?? 0);
    //         }
            
    //         // 2. Ventas por cobrar / Ventas a favor - CORREGIDO
    //         $ventas = $ventasService->ObtenerListadoVentasDiariasParaCerrar(
    //             $filtroFecha,
    //             $_sucursal->ID,
    //             true
    //         );
            
    //         $totalVentasPorCobrar = 0;
    //         if (isset($ventas['ListaVentasDiarias']) && is_array($ventas['ListaVentasDiarias'])) {
    //             foreach ($ventas['ListaVentasDiarias'] as $venta) {
    //                 // Las ventas son objetos, necesitas ver sus propiedades
    //                 if (is_object($venta)) {
    //                     // Prueba estas propiedades (usa la que funcione)
    //                     $totalVentasPorCobrar += (float)(
    //                         $venta->Saldo ?? 
    //                         $venta->saldo ?? 
    //                         $venta->Total ?? 
    //                         $venta->total ?? 
    //                         0
    //                     );
    //                 }
    //             }
    //         }

    //         // ========== PASIVOS ==========
            
    //         // 3. Deudas de recepciones
    //         $listadoRecepciones = $ventasService->buscarRecepcionesSucursalParaCerrar($_sucursal->ID, $filtroFecha->fechaFin);
    //         $totalDeudaRecepciones = array_sum(
    //             array_map(fn($r) => (float)($r['SaldoDivisa'] ?? 0), $listadoRecepciones)
    //         );

    //         // 4. Deudas de gastos (YA FUNCIONA)
    //         $gastos = VentasHelper::BuscarGastosSucursalParaCerrar($_sucursal->ID, null);
    //         $totalDeudaGastos = array_sum(
    //             array_map(fn($g) => (float)$g->getSaldoDivisa(), $gastos)
    //         );

    //         // 5. Transferencias pendientes
    //         $listadoTransferencias = $ventasService->buscarTransferenciasParaCerrar($filtroFecha, $_sucursal->ID);
    //         $totalTransferencias = array_sum(
    //             array_map(fn($t) => (float)($t['Saldo'] ?? 0), $listadoTransferencias)
    //         );

    //         // 6. Facturas por pagar
    //         $listadoFacturas = [];
    //         $totalFacturas = 0;
            
    //         if ($_sucursal->Tipo == 0) { // Oficina
    //             $listadoFacturas = $ventasService->buscarFacturasActivas();
    //             $totalFacturas = array_sum(
    //                 array_map(fn($f) => (float)($f['SaldoDivisa'] ?? $f['MontoDivisa'] ?? 0), $listadoFacturas)
    //             );
    //         }

    //         // ========== PAGOS DEL PERÍODO ==========
            
    //         $listadoPagoServicios = $ventasService->buscarTransacciones(
    //             1, $_sucursal->ID,
    //             ['fecha_inicio' => $filtroFecha->fechaInicio, 'fecha_fin' => $filtroFecha->fechaFin]
    //         );

    //         $listadoPagoMercancia = $ventasService->buscarTransacciones(
    //             0, $_sucursal->ID,
    //             ['fecha_inicio' => $filtroFecha->fechaInicio, 'fecha_fin' => $filtroFecha->fechaFin]
    //         );

    //         $totalPagosServicios = 0;
    //         foreach ($listadoPagoServicios as $pago) {
    //             $totalPagosServicios += (float)($pago->MontoDivisaAbonado ?? $pago['MontoDivisaAbonado'] ?? 0);
    //         }
            
    //         $totalPagosMercancia = 0;
    //         foreach ($listadoPagoMercancia as $pago) {
    //             $totalPagosMercancia += (float)($pago->MontoDivisaAbonado ?? $pago['MontoDivisaAbonado'] ?? 0);
    //         }

    //         // ========== CÁLCULOS FINALES ==========
            
    //         $totalActivos = $totalInventario + $totalVentasPorCobrar;
    //         $totalPasivos = $totalDeudaRecepciones + $totalDeudaGastos + $totalTransferencias + $totalFacturas;
    //         $patrimonio = $totalActivos - $totalPasivos;

    //         $listaBalance[] = [
    //             'SucursalId' => $_sucursal->ID,
    //             'SucursalNombre' => $_sucursal->Nombre,
    //             'SucursalTipo' => $_sucursal->Tipo,
                
    //             // ACTIVOS
    //             'Inventario' => [
    //                 'Monto' => round($totalInventario, 2),
    //                 'Detalle' => $valorizacion,
    //                 'Unidades' => $unidades,
    //                 'Referencias' => $referencias,
    //             ],
    //             'VentasPorCobrar' => [
    //                 'Monto' => round($totalVentasPorCobrar, 2),
    //                 'Detalle' => $ventas['ListaVentasDiarias'] ?? [],
    //                 'Cantidad' => count($ventas['ListaVentasDiarias'] ?? []),
    //             ],
    //             'TotalActivos' => round($totalActivos, 2),
                
    //             // PASIVOS
    //             'DeudaRecepciones' => [
    //                 'Monto' => round($totalDeudaRecepciones, 2),
    //                 'Detalle' => $listadoRecepciones,
    //                 'Cantidad' => count($listadoRecepciones),
    //             ],
    //             'DeudaGastos' => [
    //                 'Monto' => round($totalDeudaGastos, 2),
    //                 'Detalle' => $gastos,
    //                 'Cantidad' => count($gastos),
    //             ],
    //             'TransferenciasPendientes' => [
    //                 'Monto' => round($totalTransferencias, 2),
    //                 'Detalle' => $listadoTransferencias,
    //                 'Cantidad' => count($listadoTransferencias),
    //             ],
    //             'FacturasPorPagar' => [
    //                 'Monto' => round($totalFacturas, 2),
    //                 'Detalle' => $listadoFacturas,
    //                 'Cantidad' => count($listadoFacturas),
    //             ],
    //             'TotalPasivos' => round($totalPasivos, 2),
                
    //             // PATRIMONIO
    //             'Patrimonio' => round($patrimonio, 2),
                
    //             // FLUJO DEL PERÍODO
    //             'PagosServicios' => [
    //                 'Monto' => round($totalPagosServicios, 2),
    //                 'Detalle' => $listadoPagoServicios,
    //             ],
    //             'PagosMercancia' => [
    //                 'Monto' => round($totalPagosMercancia, 2),
    //                 'Detalle' => $listadoPagoMercancia,
    //             ],
    //             'TotalEgresosPeriodo' => round($totalPagosServicios + $totalPagosMercancia, 2),
    //         ];
    //     }

    //     // Después del foreach, antes del resumen, FILTRAR las sucursales
    //     $sucursalesBalance = array_filter($listaBalance, function($item) {
    //         return $item['SucursalTipo'] != 0; // Excluir Tipo 0 (Oficina Principal)
    //     });

    //     // Resumen SOLO de sucursales (excluyendo oficina)
    //     $resumen = [
    //         'TotalActivos' => round(array_sum(array_column($sucursalesBalance, 'TotalActivos')), 2),
    //         'TotalPasivos' => round(array_sum(array_column($sucursalesBalance, 'TotalPasivos')), 2),
    //         'TotalPatrimonio' => round(array_sum(array_column($sucursalesBalance, 'Patrimonio')), 2),
    //         'TotalEgresosPeriodo' => round(array_sum(array_column($sucursalesBalance, 'TotalEgresosPeriodo')), 2),
    //         'CantidadSucursales' => count($sucursalesBalance),
    //     ];

    //     // También puedes agregar un resumen de la oficina por separado
    //     $oficinaBalance = array_filter($listaBalance, function($item) {
    //         return $item['SucursalTipo'] == 0;
    //     });

    //     $oficinaResumen = !empty($oficinaBalance) ? [
    //         'TotalActivos' => round(array_sum(array_column($oficinaBalance, 'TotalActivos')), 2),
    //         'TotalPasivos' => round(array_sum(array_column($oficinaBalance, 'TotalPasivos')), 2),
    //         'TotalPatrimonio' => round(array_sum(array_column($oficinaBalance, 'Patrimonio')), 2),
    //         'TotalEgresosPeriodo' => round(array_sum(array_column($oficinaBalance, 'TotalEgresosPeriodo')), 2),
    //         'FacturasPorPagar' => !empty($oficinaBalance) ? $oficinaBalance[array_key_first($oficinaBalance)]['FacturasPorPagar']['Monto'] ?? 0 : 0,
    //     ] : null;

    //     dd([
    //         'sucursales' => $sucursalesBalance,
    //         'oficina' => $oficinaBalance,
    //         'resumen_sucursales' => $resumen,
    //         'resumen_oficina' => $oficinaResumen,
    //     ]);

    //     return response()->json([
    //         'success' => true,
    //         'data' => $listaBalance,
    //         'resumen' => $resumen,
    //         'periodo' => [
    //             'fecha_inicio' => $fechaInicio->format('Y-m-d'),
    //             'fecha_fin' => $fechaFin->format('Y-m-d'),
    //         ]
    //     ]);
    // }

    public function balance_general(Request $request)
    {
        // Fechas del request
        $fechaInicio = $request->input('fecha_inicio')
            ? Carbon::parse($request->input('fecha_inicio'))->startOfDay()
            : Carbon::now()->startOfMonth();

        $fechaFin = $request->input('fecha_fin')
            ? Carbon::parse($request->input('fecha_fin'))->endOfDay()
            : Carbon::now()->endOfDay();

        $filtroFecha = new ParametrosFiltroFecha(
            null, null, null, false,
            $fechaInicio, $fechaFin
        );

        session([
            'menu_active' => 'Contabilidad',
            'submenu_active' => 'Balance General'
        ]);

        $listaSucursales = GeneralHelper::buscarSucursales(0);
        $ventasService = new VentasService();
        $listaBalance = [];

        // $listadoFacturas = $ventasService->buscarFacturasActivas();

        // // Antes del foreach, agrega esto:
        // $totalTipo1 = Transaccion::where('Tipo', 1)->count();
        // $totalTipo0 = Transaccion::where('Tipo', 0)->count();

        $listaSucursales = GeneralHelper::buscarSucursales(0)
        ->reject(function ($sucursal) {
            return $sucursal->ID == 6;
        })
        ->values();

        // dd($listaSucursales);

        foreach ($listaSucursales as $_sucursal) 
        {
            // $transaccionesEnRango = Transaccion::where('SucursalId', $_sucursal->ID)
            //     ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
            //     ->count();
            
            // \Log::info("Sucursal {$_sucursal->ID} ({$_sucursal->Nombre}) tiene {$transaccionesEnRango} transacciones en el rango");

            // ========== ACTIVOS ==========
            
            // 1. Inventario // No genera para OFICINA PRINCIPAL
            $valorizacion = ValorizacionInventario::where('SucursalId', $_sucursal->ID)->first();
            
            $totalInventario = 0;
            $unidades = 0;
            $referencias = 0;
            
            if ($valorizacion) {
                $totalInventario = (float)($valorizacion->CostoDivisa ?? 0);
                $unidades = (int)($valorizacion->Existencia ?? 0);
                $referencias = (int)($valorizacion->Referencias ?? 0);
            }

            // 2.0 Ventas por TOTALIZAR // No genera para OFICINA PRINCIPAL
            $ventasTotalizar = $ventasService->obtenerListadoVentasDiariasParaCerrarSinTotalizar(
                $filtroFecha, $_sucursal->ID, true
            );

            $totalVentasSinTotalizar = 0;
            if (isset($ventasTotalizar['ListaVentasDiarias']) && is_array($ventasTotalizar['ListaVentasDiarias'])) {
                foreach ($ventasTotalizar['ListaVentasDiarias'] as $venta) {
                    // Las ventas son objetos, necesitas ver sus propiedades
                    if (is_object($venta)) {
                        // Prueba estas propiedades (usa la que funcione)
                        $totalVentasSinTotalizar += (float)(
                            $venta->Saldo ?? 
                            $venta->saldo ?? 
                            $venta->Total ?? 
                            $venta->total ?? 
                            0
                        );
                    }
                }
            }

            // dd($totalVentasSinTotalizar);
            
            // 2.1 Ventas por cobrar // No genera para OFICINA PRINCIPAL
            $ventas = $ventasService->ObtenerListadoVentasDiariasParaCerrar(
                $filtroFecha, $_sucursal->ID, true
            );
            
            $totalVentasPorCobrar = 0;
            $totalVentasPorCobrar += (float)($ventas['totales']['ventas_acumuladas'] ?? 0);

            // ========== PASIVOS ==========
            
            // 3. Deudas de recepciones // No genera para OFICINA PRINCIPAL
            $recepcionesData = $ventasService->buscarRecepcionesSucursalParaCerrar($_sucursal->ID, $filtroFecha->fechaFin);

            // $listadoRecepciones = $recepcionesData['total_historico'];
            $totalDeudaRecepciones = $recepcionesData['total_historico'];          // ✅ Total Historico
            $cantidadRecepciones = $recepcionesData['cantidad_total'];             // ✅ Recepciones totales
            $totalDeudaRecepcionesPendientes = $recepcionesData['saldo_pendiente'];          // ✅ Solo lo que deben
            $cantidadPendiente = $recepcionesData['cantidad_pendiente'];           // ✅ Recepciones con deuda

            // 4. Deudas de gastos
            $gastos = VentasHelper::BuscarGastosSucursalParaCerrar($_sucursal->ID, null);
            $totalDeudaGastos = array_sum(array_map(fn($g) => (float)$g->getSaldoDivisa(), $gastos));

            // 5. Transferencias pendientes
            $listadoTransferencias = $ventasService->buscarTransferenciasParaCerrar($filtroFecha, $_sucursal->ID);
            // $totalTransferencias = array_sum(array_map(fn($t) => (float)($t['Saldo'] ?? 0), $listadoTransferencias));
            $totalTransferencias = array_sum(array_map(fn($t) => $t['MontoCalculado'], $listadoTransferencias));
            $totalTransferenciasSaldo = array_sum(array_map(fn($t) => $t['Saldo'], $listadoTransferencias));


            // 6. Facturas por pagar (solo para oficina)
            $listadoFacturas = [];
            $totalFacturas = 0;
            
            if ($_sucursal->Tipo == 0) {
                $listadoFacturas = $ventasService->buscarFacturasActivas();
                
                // Separar facturas por tipo de saldo
                $facturasMercancia = 0;
                $totalFacturasMercancia = 0;
                $totalFacturasMercanciaPagado = 0;
                $totalFacturasMercanciaPorPagar = 0;
                $facturasServicios = 0;
                $totalFacturasServicios = 0;
                $totalFacturasServiciosPagado = 0;
                $totalFacturasServiciosPorPagar = 0;
                
                foreach ($listadoFacturas as $factura) {
                    // La factura es tipo MERCANCIA
                    if($factura['Factura']['Tipo'] == 0){
                        $totalFacturasMercancia += $factura['TotalDivisa'];
                        $totalFacturasMercanciaPagado += $factura['TotalAbonadoDivisa'];
                        $totalFacturasMercanciaPorPagar += $factura['TotalSaldoDivisa'];
                        $facturasMercancia++;
                    }

                    // La factura es tipo SERVICIO
                    if($factura['Factura']['Tipo'] == 1){
                        $totalFacturasServicios += $factura['Factura']['MontoDivisa'];
                        $totalFacturasServiciosPagado += $factura['TotalAbonadoDivisa'];
                        $totalFacturasServiciosPorPagar += $factura['TotalSaldoDivisa'];
                        $facturasServicios++;
                    }
                }
                
                // Guardamos el detalle completo para referencia
                $listadoFacturas = [
                    'cantidad_facturas_mercancia' => $facturasMercancia,
                    'facturas_mercancia' => $totalFacturasMercancia,
                    'abonado_mercancia' => $totalFacturasMercanciaPagado,
                    'pendiente_mercancia' => $totalFacturasMercanciaPorPagar,
                    'cantidad_facturas_servicios' => $facturasServicios,
                    'facturas_servicios' => $totalFacturasServicios,
                    'abonado_servicios' => $totalFacturasServiciosPagado,
                    'pendiente_servicios' => $totalFacturasServiciosPorPagar,
                ];

                // dd([
                //     'cantidad_facturas_mercancia' => $facturasMercancia,
                //     'facturas_mercancia' => $totalFacturasMercancia,
                //     'abonado_mercancia' => $totalFacturasMercanciaPagado,
                //     'pendiente_mercancia' => $totalFacturasMercanciaPorPagar,
                //     'cantidad_facturas_servicios' => $facturasServicios,
                //     'facturas_servicios' => $totalFacturasServicios,
                //     'abonado_servicios' => $totalFacturasServiciosPagado,
                //     'pendiente_servicios' => $totalFacturasServiciosPorPagar,
                // ]);
            }

            // ========== PAGOS DEL PERÍODO ==========
            
            // Verificar si hay transacciones de tipo 1 (servicios)
            $listadoPagoServicios = collect(); // Colección vacía por defecto
            $totalPagosServicios = 0;
            
            $listadoPagoServicios = $ventasService->buscarTransacciones(
                1, $_sucursal->ID,
                ['fecha_inicio' => $filtroFecha->fechaInicio, 'fecha_fin' => $filtroFecha->fechaFin]
            );
            
            foreach ($listadoPagoServicios as $pago) {
                $totalPagosServicios += (float)($pago->MontoDivisaAbonado ?? $pago['MontoDivisaAbonado'] ?? 0);
            }
            
            // Pagos de mercancía (tipo 0) - Estos SÍ existen
            $listadoPagoMercancia = $ventasService->buscarTransacciones(
                0, $_sucursal->ID,
                ['fecha_inicio' => $filtroFecha->fechaInicio, 'fecha_fin' => $filtroFecha->fechaFin]
            );
            
            $totalPagosMercancia = 0;
            foreach ($listadoPagoMercancia as $pago) {
                $totalPagosMercancia += (float)($pago->MontoDivisaAbonado ?? $pago['MontoDivisaAbonado'] ?? 0);
            }
            
            // // Log para verificar
            // \Log::info("Pagos sucursal {$_sucursal->ID}:", [
            //     'servicios_cantidad' => $listadoPagoServicios->count(),
            //     'servicios_monto' => $totalPagosServicios,
            //     'mercancia_cantidad' => $listadoPagoMercancia->count(),
            //     'mercancia_monto' => $totalPagosMercancia,
            // ]);

            // ========== CÁLCULOS FINALES ==========
            
            $totalActivos = $totalInventario + $totalVentasPorCobrar;
            $totalPasivos = $totalDeudaRecepciones + $totalDeudaGastos + $totalTransferencias + $totalFacturas;
            $patrimonio = $totalActivos - $totalPasivos;

            $listaBalance[] = [
                'SucursalId' => $_sucursal->ID,
                'SucursalNombre' => $_sucursal->Nombre,
                'SucursalTipo' => $_sucursal->Tipo,
                
                // ACTIVOS
                'Inventario' => [
                    'Monto' => round($totalInventario, 2),
                    'Detalle' => $valorizacion,
                    'Unidades' => $unidades,
                    'Referencias' => $referencias,
                ],
                'VentasSinTotalizar' => [
                    'Monto' => round($totalVentasSinTotalizar, 2),
                    //'Detalle' => $ventas['ListaVentasDiarias'] ?? [],
                    'Cantidad' => count($ventasTotalizar['ListaVentasDiarias'] ?? []),
                ],
                'VentasPorCobrar' => [
                    'Monto' => round($totalVentasPorCobrar, 2),
                    //'Detalle' => $ventas['ListaVentasDiarias'] ?? [],
                    'Cantidad' => $ventas['totales']['cantidad_ventas'] ?? 0,
                ],
                'TotalActivos' => round($totalActivos, 2),
                
                // PASIVOS
                'DeudaRecepciones' => [
                    'Monto' => round($totalDeudaRecepciones, 2),
                    // 'Detalle' => $listadoRecepciones,
                    'Cantidad' => $cantidadRecepciones,
                    'MontoPendiene' => $totalDeudaRecepcionesPendientes,
                    'CantidadPendiente' => $cantidadPendiente,
                ],
                'DeudaGastos' => [
                    'Monto' => round($totalDeudaGastos, 2),
                    'Detalle' => $gastos,
                    'Cantidad' => count($gastos),
                ],
                // 'TransferenciasPendientes' => [
                //     'Monto' => round($totalTransferencias, 2),
                //     'Detalle' => $listadoTransferencias,
                //     'Cantidad' => count($listadoTransferencias),
                // ],
                'Transferencias' => [
                    'MontoAcumulado' => round($totalTransferencias, 2),  // ✅ Total histórico
                    'SaldoPendiente' => round($totalTransferenciasSaldo, 2),  // Lo que aún se debe
                    'Detalle' => $listadoTransferencias,
                    'Cantidad' => count($listadoTransferencias),
                ],
                'FacturasPorPagar' => [
                    'Monto' => round($totalFacturas, 2),
                    'Detalle' => $listadoFacturas,
                    'Cantidad' => count($listadoFacturas),
                ],
                'TotalPasivos' => round($totalPasivos, 2),
                
                // PATRIMONIO
                'Patrimonio' => round($patrimonio, 2),
                
                // FLUJO DEL PERÍODO
                'PagosServicios' => [
                    'Monto' => round($totalPagosServicios, 2),
                    'Detalle' => $listadoPagoServicios,
                ],
                'PagosMercancia' => [
                    'Monto' => round($totalPagosMercancia, 2),
                    'Detalle' => $listadoPagoMercancia,
                ],
                'TotalEgresosPeriodo' => round($totalPagosServicios + $totalPagosMercancia, 2),
            ];
        }

        // // ========== CÁLCULOS DE OFICINA (DESPUÉS DEL FOREACH) ==========
        
        // // Separar sucursales y oficina
        // $sucursalesBalance = array_filter($listaBalance, fn($item) => $item['SucursalTipo'] != 0);
        // $oficinaBalance = array_filter($listaBalance, fn($item) => $item['SucursalTipo'] == 0);
        
        // if (!empty($oficinaBalance)) {
        //     $oficinaKey = array_key_first($oficinaBalance);
            
        //     // ===== ACTIVOS DE OFICINA (lo que le deben las sucursales) =====
        //     $totalDeudaRecepciones = 0;
        //     $totalTransferencias = 0;
            
        //     foreach ($sucursalesBalance as $sucursal) {
        //         $totalDeudaRecepciones += $sucursal['DeudaRecepciones']['Monto'] ?? 0;
        //         $totalTransferencias += $sucursal['TransferenciasPendientes']['Monto'] ?? 0;
        //     }
            
        //     $totalCuentasPorCobrar = $totalDeudaRecepciones + $totalTransferencias;
            
        //     // Actualizar la oficina con sus activos reales
        //     $listaBalance[$oficinaKey]['CuentasPorCobrar'] = [
        //         'Monto' => round($totalCuentasPorCobrar, 2),
        //         'Detalle' => [
        //             'Recepciones' => $totalDeudaRecepciones,
        //             'Transferencias' => $totalTransferencias,
        //         ],
        //     ];
            
        //     // Recalcular TotalActivos de la oficina
        //     $listaBalance[$oficinaKey]['TotalActivos'] = $totalCuentasPorCobrar;
            
        //     // Recalcular Patrimonio de la oficina
        //     $pasivosOficina = $listaBalance[$oficinaKey]['TotalPasivos'];
        //     $listaBalance[$oficinaKey]['Patrimonio'] = round($totalCuentasPorCobrar - $pasivosOficina, 2);
        // }        

        // // ========== RESUMEN DE OFICINA ==========
        // $oficinaBalance = array_filter($listaBalance, fn($item) => $item['SucursalTipo'] == 0);
        // $oficinaResumen = null;
        
        // if (!empty($oficinaBalance)) {
        //     $oficina = $oficinaBalance[array_key_first($oficinaBalance)];
            
        //     // Procesar facturas (nuevo formato con positivas/negativas separadas)
        //     $facturasPositivas = 0;
        //     $montoFacturasPositivas = 0;
        //     $facturasNegativas = 0;
        //     $montoFacturasNegativas = 0;
            
        //     $detalleFacturas = $oficina['FacturasPorPagar']['Detalle'] ?? [];
            
        //     // Verificar si es el nuevo formato
        //     if (is_array($detalleFacturas) && isset($detalleFacturas['positivas'])) {
        //         $facturasPositivas = count($detalleFacturas['positivas']);
        //         $montoFacturasPositivas = $detalleFacturas['total_positivo'] ?? 0;
        //         $facturasNegativas = count($detalleFacturas['negativas']);
        //         $montoFacturasNegativas = $detalleFacturas['total_negativo'] ?? 0;
        //     }
            
        //     $oficinaResumen = [
        //         'TotalActivos' => $oficina['TotalActivos'],
        //         'TotalPasivos' => $oficina['TotalPasivos'],
        //         'TotalPatrimonio' => $oficina['Patrimonio'],
        //         'CuentasPorCobrar' => $oficina['CuentasPorCobrar']['Monto'] ?? 0,
        //         'DeudaGastos' => $oficina['DeudaGastos']['Monto'],
        //         'FacturasPorPagar' => [
        //             'Total' => $oficina['FacturasPorPagar']['Monto'], // Solo positivas
        //             'CantidadTotal' => $oficina['FacturasPorPagar']['Cantidad'],
        //             'Positivas' => [
        //                 'Cantidad' => $facturasPositivas,
        //                 'Monto' => round($montoFacturasPositivas, 2)
        //             ],
        //             'Negativas' => [
        //                 'Cantidad' => $facturasNegativas,
        //                 'Monto' => round($montoFacturasNegativas, 2)
        //             ]
        //         ],
        //     ];
        // }

        // ========== CÁLCULOS DE OFICINA (DESPUÉS DEL FOREACH) ==========

        // Separar sucursales y oficina
        $sucursalesBalance = array_filter($listaBalance, fn($item) => $item['SucursalTipo'] != 0);
        $oficinaBalance = array_filter($listaBalance, fn($item) => $item['SucursalTipo'] == 0);

        if (!empty($oficinaBalance)) {
            $oficinaKey = array_key_first($oficinaBalance);
            
            // ===== ACTIVOS DE OFICINA (lo que le deben las sucursales) =====
            $totalDeudaRecepciones = 0;
            $totalTransferencias = 0;
            
            foreach ($sucursalesBalance as $sucursal) {
                $totalDeudaRecepciones += $sucursal['DeudaRecepciones']['Monto'] ?? 0;
                $totalTransferencias += $sucursal['TransferenciasPendientes']['Monto'] ?? 0;
            }
            
            $totalCuentasPorCobrar = $totalDeudaRecepciones + $totalTransferencias;
            
            // Actualizar la oficina con sus activos reales
            $listaBalance[$oficinaKey]['CuentasPorCobrar'] = [
                'Monto' => round($totalCuentasPorCobrar, 2),
                'Detalle' => [
                    'Recepciones' => $totalDeudaRecepciones,
                    'Transferencias' => $totalTransferencias,
                ],
            ];
            
            // Recalcular TotalActivos de la oficina
            $listaBalance[$oficinaKey]['TotalActivos'] = $totalCuentasPorCobrar;
            
            // Recalcular Patrimonio de la oficina
            $pasivosOficina = $listaBalance[$oficinaKey]['TotalPasivos'];
            $listaBalance[$oficinaKey]['Patrimonio'] = round($totalCuentasPorCobrar - $pasivosOficina, 2);
        }        

        // ========== RESUMEN DE OFICINA ==========
        $oficinaBalance = array_filter($listaBalance, fn($item) => $item['SucursalTipo'] == 0);
        $oficinaResumen = null;

        if (!empty($oficinaBalance)) {
            $oficina = $oficinaBalance[array_key_first($oficinaBalance)];
            
            // Obtener detalles de facturas
            $detalleFacturas = $oficina['FacturasPorPagar']['Detalle'] ?? [];
            
            $oficinaResumen = [
                'TotalActivos' => $oficina['TotalActivos'],
                'TotalPasivos' => $oficina['TotalPasivos'],
                'TotalPatrimonio' => $oficina['Patrimonio'],
                'CuentasPorCobrar' => $oficina['CuentasPorCobrar']['Monto'] ?? 0,
                'DeudaGastos' => $oficina['DeudaGastos']['Monto'],
                
                // 👇 NUEVO: Facturas de Mercancía (Tipo 0)
                'FacturasMercancia' => [
                    'Cantidad' => $detalleFacturas['cantidad_facturas_mercancia'] ?? 0,
                    'Total' => round($detalleFacturas['facturas_mercancia'] ?? 0, 2),
                    'Pagado' => round($detalleFacturas['abonado_mercancia'] ?? 0, 2),
                    'Pendiente' => round($detalleFacturas['pendiente_mercancia'] ?? 0, 2),
                ],
                
                // 👇 NUEVO: Facturas de Servicios (Tipo 1)
                'FacturasServicios' => [
                    'Cantidad' => $detalleFacturas['cantidad_facturas_servicios'] ?? 0,
                    'Total' => round($detalleFacturas['facturas_servicios'] ?? 0, 2),
                    'Pagado' => round($detalleFacturas['abonado_servicios'] ?? 0, 2),
                    'Pendiente' => round($detalleFacturas['pendiente_servicios'] ?? 0, 2),
                ],
                
                // Mantener compatibilidad con código existente
                'FacturasPorPagar' => [
                    'Total' => $oficina['FacturasPorPagar']['Monto'] ?? 0,
                    'CantidadTotal' => ($detalleFacturas['cantidad_facturas_mercancia'] ?? 0) + 
                                    ($detalleFacturas['cantidad_facturas_servicios'] ?? 0),
                    'Positivas' => [
                        'Cantidad' => ($detalleFacturas['cantidad_facturas_mercancia'] ?? 0) + 
                                    ($detalleFacturas['cantidad_facturas_servicios'] ?? 0),
                        'Monto' => round(($detalleFacturas['pendiente_mercancia'] ?? 0) + 
                                        ($detalleFacturas['pendiente_servicios'] ?? 0), 2)
                    ],
                ],
            ];
        }

        // ========== RESUMEN DE SUCURSALES ==========
        $sucursalesBalance = array_filter($listaBalance, fn($item) => $item['SucursalTipo'] != 0);
        $resumen = [
            'TotalActivos' => round(array_sum(array_column($sucursalesBalance, 'TotalActivos')), 2),
            'TotalPasivos' => round(array_sum(array_column($sucursalesBalance, 'TotalPasivos')), 2),
            'TotalPatrimonio' => round(array_sum(array_column($sucursalesBalance, 'Patrimonio')), 2),
            'TotalEgresosPeriodo' => round(array_sum(array_column($sucursalesBalance, 'TotalEgresosPeriodo')), 2),
            'CantidadSucursales' => count($sucursalesBalance),
        ];

        // dd([
        //     'sucursales' => $sucursalesBalance,
        //     'oficina' => $oficinaBalance,
        //     'resumen_sucursales' => $resumen,
        //     'resumen_oficina' => $oficinaResumen,
        // ]);

        return view('cpanel.contabilidad.balance_general', [
            'sucursales' => array_values($sucursalesBalance),
            'oficina' => $oficinaResumen,
            'resumen' => $resumen,
            'fecha_inicio' => $fechaInicio->format('Y-m-d'),
            'fecha_fin' => $fechaFin->format('Y-m-d'),
            'titulo' => 'Balance General',
        ]);
    }
}