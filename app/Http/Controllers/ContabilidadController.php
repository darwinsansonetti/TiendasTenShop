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
use App\Models\CierreOfp;
use App\DTO\EDCOficinaPrincipalDTO;
use App\DTO\TransferenciaDTO;
use App\DTO\TransferenciaDetalleDTO;
use App\DTO\ProductoDTO;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

use App\Enums\EnumTipoFiltroFecha;
use Illuminate\Support\Facades\Validator;

class ContabilidadController extends Controller
{
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

            // 2.3 Ultima Venta Totalizada // No genera para OFICINA PRINCIPAL
            $UltimaVentaTotalizada = $ventasService->obtenerUltimaVentaDiariaTotalizada(
                $_sucursal->ID
            );

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
                'UltimaVentaTotalizada' => [
                    'UltimaVenta' => $UltimaVentaTotalizada,
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

    public function cerrar_dia_automaticamente(Request $request)
    {
        $now = Carbon::now('America/Caracas');
        $ayer = $now->copy()->subDay(2); // Restar 1 día
        
        try {
            // Se busca si hay Cierre del dia anterior
            $cierreExistente = CierreOfp::whereDate('Fecha', $ayer)->first();

            // No se ha realizado el cierre de ayer.
            if (!$cierreExistente) {
                
                // Se obtiene lo que esta pendiente por cerrar hasta la fecha
                $ventasAgrupadasPorFecha = $this->listar_cerrar_dia();
                
                // Verificar que hay datos
                if (!$ventasAgrupadasPorFecha->isEmpty()) {

                    // Buscar la fecha específica de ayer en el array
                    $fechaAyerStr = $ayer->format('Y-m-d');
                    $fechaAyerData = null;
                    
                    foreach ($ventasAgrupadasPorFecha as $fechaKey => $data) {
                        if ($fechaKey === $fechaAyerStr) {
                            $fechaAyerData = $data;
                            break;
                        }
                    }
                    
                    // Si hay Cierres Pendientes del dia de ayer, se insertan y se actualizan los de otra
                    if ($fechaAyerData) {
                        
                        DB::beginTransaction();
                        
                        try {
                            // Crear el cierre para la fecha específica
                            $_cierreOFP = new CierreOfp();

                            // ========== ABONOS (Préstamos) ==========
                            $pagoPrestamos = collect($fechaAyerData['pago_prestamos'] ?? []);
                            $_cierreOFP->AbonosBs = $pagoPrestamos->sum('MontoAbonado'); // 0 (array vacío)
                            $_cierreOFP->AbonosDivisa = $pagoPrestamos->sum('MontoDivisaAbonado'); // 0

                            // ========== VENTAS (Cierre) ==========
                            //NOTA: Aqui en CierreBs se muestra una dieferencia debido a que se llevan los dolares a bolivares y se suman
                            $cierresData = $this->procesarCierresDiarios($fechaAyerData['ventas']);

                            if ($cierresData['tiene_cierres'] && $cierresData['total_bs_periodo'] > 0) {
                                $_cierreOFP->CierreBs = $cierresData['total_bs_periodo'];
                                $_cierreOFP->CierreDivisa = $cierresData['total_divisa'];
                            } else {
                                $_cierreOFP->CierreBs = $fechaAyerData['total_bs_dia'] ?? 0;
                                $_cierreOFP->CierreDivisa = $fechaAyerData['total_divisa_dia'] ?? 0;
                            }

                            // ========== ESTATUS ==========
                            $_cierreOFP->Estatus = 1; // Cerrado

                            // ========== FACTURAS DE MERCANCÍA ==========
                            $_cierreOFP->FacturasBs = $fechaAyerData['resumen_facturas']['mercancia']['total_bs'] ?? 0;
                            $_cierreOFP->FacturasDivisa = $fechaAyerData['resumen_facturas']['mercancia']['total_divisa'] ?? 0;

                            // ========== FECHA ==========
                            $_cierreOFP->Fecha = $fechaAyerData['fecha']; // 2026-03-05

                            // ========== GASTOS DE CAJA (Egresos) ==========
                            // Usar el total de egresos calculado en procesarCierresDiarios()
                            $_cierreOFP->GastosCajaBs = $cierresData['total_egresos_bs'] ?? 0;      // En Bs
                            $_cierreOFP->GastosCajaDivisa = $cierresData['total_egresos_divisa'] ?? 0;  // En Divisas

                            // ========== GASTOS DE SUCURSAL (solo de la fecha específica) ==========
                            $fechaCierre = $fechaAyerData['fecha_objeto'] ?? $fechaAyerData['fecha'];
                            $fechaCierreStr = is_string($fechaCierre) ? $fechaCierre : $fechaCierre->format('Y-m-d');

                            $gastosSucursal = collect($fechaAyerData['listado_gastos'] ?? [])
                                ->filter(function($gasto) use ($fechaCierreStr) {
                                    $fechaGasto = $gasto->Fecha instanceof \Carbon\Carbon 
                                        ? $gasto->Fecha->format('Y-m-d')
                                        : (is_string($gasto->Fecha) ? substr($gasto->Fecha, 0, 10) : null);
                                    
                                    return $fechaGasto === $fechaCierreStr;
                                });

                            $_cierreOFP->GastosSucursalBs = $gastosSucursal->sum('MontoAbonado');
                            $_cierreOFP->GastosSucursalDivisa = $gastosSucursal->sum('MontoDivisaAbonado');

                            // ========== PAGOS DE SERVICIOS ==========
                            $_cierreOFP->PagoServiciosBs = $fechaAyerData['listado_pagos']['pagos_servicios']['MontoBs'] ?? 0;
                            $_cierreOFP->PagoServiciosDivisa = $fechaAyerData['listado_pagos']['pagos_servicios']['MontoDivisa'] ?? 0;

                            // ========== PAGOS DE FACTURAS (Mercancía) ==========
                            $_cierreOFP->PagoFacturasBs = $fechaAyerData['listado_pagos']['pagos_mercancia']['MontoBs'] ?? 0;
                            $_cierreOFP->PagoFacturasDivisa = $fechaAyerData['listado_pagos']['pagos_mercancia']['MontoDivisa'] ?? 0;

                            // ========== PRÉSTAMOS ==========
                            $prestamos = collect($fechaAyerData['prestamos'] ?? []);
                            $_cierreOFP->PrestamosBs = $prestamos->sum('MontoBs'); // 0
                            $_cierreOFP->PrestamosDivisa = $prestamos->sum('MontoDivisa'); // 0

                            // ========== SALDO DE OPERACIÓN BS ==========
                            $totalAbonosBs = $_cierreOFP->AbonosBs ?? 0;                 // 0
                            $totalCierreBs = $_cierreOFP->CierreBs ?? 0;                 // 224,196.55

                            $totalGastosCajaBs = $_cierreOFP->GastosCajaBs ?? 0;         // 0
                            $totalGastosSucursalBs = $_cierreOFP->GastosSucursalBs ?? 0; // 17,269.98
                            $totalPagoFacturasBs = $_cierreOFP->PagoFacturasBs ?? 0;     // 0
                            $totalPagoServiciosBs = $_cierreOFP->PagoServiciosBs ?? 0;   // 0
                            $totalPagosGeneralBs = 0; // ¿De dónde sale este? 
                            $totalPrestamosBs = $_cierreOFP->PrestamosBs ?? 0;           // 0

                            // Calcular
                            $saldoOperacionBs = ($totalAbonosBs + $totalCierreBs) - 
                                                ($totalGastosCajaBs + 
                                                $totalGastosSucursalBs + 
                                                $totalPagoFacturasBs + 
                                                $totalPagoServiciosBs + 
                                                $totalPagosGeneralBs + 
                                                $totalPrestamosBs);

                            $_cierreOFP->SaldoOperacionBs = $saldoOperacionBs;
                            
                            // ========== SALDO DE OPERACIÓN DIVISAS ==========
                            // Obtener valores ya asignados
                            $totalAbonosDivisa = $_cierreOFP->AbonosDivisa ?? 0;
                            $totalVentasDivisa = $_cierreOFP->CierreDivisa ?? 0;  // Usamos CierreDivisa (equivale a TotalVentasDivisa)

                            $totalGastosCajaDivisa = $_cierreOFP->GastosCajaDivisa ?? 0;
                            $totalGastosSucursalDivisa = $_cierreOFP->GastosSucursalDivisa ?? 0;
                            $totalPagoFacturasDivisa = $_cierreOFP->PagoFacturasDivisa ?? 0;
                            $totalPagoServiciosDivisa = $_cierreOFP->PagoServiciosDivisa ?? 0;
                            $totalPrestamosDivisa = $_cierreOFP->PrestamosDivisa ?? 0;

                            // Calcular TotalPagosGeneralDivisa (suma de pagos en divisas)
                            $totalPagosGeneralDivisa = $totalPagoFacturasDivisa + $totalPagoServiciosDivisa;

                            // Calcular saldo de operación en divisas
                            $_cierreOFP->SaldoOperacionDivisas = ($totalAbonosDivisa + $totalVentasDivisa) - 
                                                                ($totalGastosCajaDivisa + 
                                                                $totalGastosSucursalDivisa + 
                                                                $totalPagosGeneralDivisa + 
                                                                $totalPrestamosDivisa);

                            // ========== SUCURSAL ==========
                            $_cierreOFP->SucursalId = 8; // Oficina principal

                            // ========== SERVICIOS (Facturas de Servicios) ==========
                            $_cierreOFP->ServiciosBs = $fechaAyerData['resumen_facturas']['servicios']['total_bs'] ?? 0;
                            $_cierreOFP->ServiciosDivisa = $fechaAyerData['resumen_facturas']['servicios']['total_divisa'] ?? 0;

                            // ========== VENTAS (Cierre) ==========
                            // Este es el que INCLUYE la conversión de divisas a bolívares
                            $cierresData = $this->procesarCierresDiarios($fechaAyerData['ventas']);

                            if ($cierresData['tiene_cierres'] && $cierresData['total_bs_periodo'] > 0) {
                                $_cierreOFP->CierreBs = $cierresData['total_bs_periodo'];      // 224,196.55
                                $_cierreOFP->CierreDivisa = $cierresData['total_divisa'];       // 5.00
                            } else {
                                $_cierreOFP->CierreBs = $fechaAyerData['total_bs_dia'] ?? 0;
                                $_cierreOFP->CierreDivisa = $fechaAyerData['total_divisa_dia'] ?? 0;
                            }

                            dd($fechaAyerData);

                            // 2. AHORA llamar a los métodos de contabilización
                            $this->contabilizarAbonosDePrestamo($fechaAyerData);
                            $this->contabilizarCierresDiarios($fechaAyerData);
                            $this->contabilizarGastosDeCaja($fechaAyerData);
                            $this->contabilizarGastosSucursal($fechaAyerData);
                            $this->contabilizarPagoServicios($fechaAyerData);
                            $this->contabilizarPagoFacturas($fechaAyerData);
                            $this->contabilizarPrestamos($fechaAyerData);
                            $this->contabilizarVentas($fechaAyerData);

                            // 3. Guardar el cierre (solo una vez)
                            $_cierreOFP->save();

                            $this->actualizarSaldoCierre($_cierreOFP);
                            
                            DB::commit();
                            
                        } catch (\Exception $e) {
                            DB::rollBack();
                            throw $e;
                        }
                        
                    }
                }                
            }
            
        } catch (\Exception $e) {
            \Log::error("❌ Error en cierre automático: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
        }
    }

    private function actualizarSaldoCierre($cierreOFP)
    {
        try {
            // #region Actualizar con el saldo anterior
            $cierreAnterior = CierreOfp::where('Fecha', '<', $cierreOFP->Fecha)
                ->orderBy('Fecha', 'desc')
                ->first();
            
            if ($cierreAnterior) {
                $cierreOFP->SaldoGeneralBs = $cierreAnterior->SaldoGeneralBs + $cierreOFP->SaldoOperacionBs;
                $cierreOFP->SaldoGeneralDivisas = $cierreAnterior->SaldoGeneralDivisas + $cierreOFP->SaldoOperacionDivisas;
            } else {
                $cierreOFP->SaldoGeneralBs = $cierreOFP->SaldoOperacionBs;
                $cierreOFP->SaldoGeneralDivisas = $cierreOFP->SaldoOperacionDivisas;
            }
            
            // Guardar o actualizar el cierre actual
            if (!$cierreOFP->CierreOfpId) {
                $cierreOFP->save();
            } else {
                $cierreOFP->update();
            }
            // #endregion
            
            // #region Actualizar los saldos futuros
            $cierresSiguientes = CierreOfp::where('Fecha', '>', $cierreOFP->Fecha)
                ->orderBy('Fecha')
                ->get();
            
            if ($cierresSiguientes->isNotEmpty()) {
                // El último cierre pasa a ser el cierre recién registrado
                $cierreAnterior = $cierreOFP;
                
                foreach ($cierresSiguientes as $cierre) {
                    $cierre->SaldoGeneralBs = $cierreAnterior->SaldoGeneralBs + $cierre->SaldoOperacionBs;
                    $cierre->SaldoGeneralDivisas = $cierreAnterior->SaldoGeneralDivisas + $cierre->SaldoOperacionDivisas;
                    
                    $cierre->save();
                    
                    // El último cierre pasa a ser el cierre recién actualizado
                    $cierreAnterior = $cierre;
                }
            }
            // #endregion
            
            \Log::info("Saldos actualizados correctamente para cierre ID: {$cierreOFP->CierreOfpId}");
            
        } catch (\Exception $e) {
            \Log::error("Error al actualizar saldos de cierre: " . $e->getMessage());
            throw $e;
        }
    }

    private function contabilizarCierresDiarios($fechaAyerData)
    {
        // Recorrer las ventas para acceder a sus cierres diarios
        foreach ($fechaAyerData['ventas'] as $venta) {
            if (isset($venta->cierreDiario) && $venta->cierreDiario->isNotEmpty()) {
                foreach ($venta->cierreDiario as $cierre) {
                    // Actualizar usando Eloquent
                    CierreDiario::where('CierreDiarioId', $cierre->CierreDiarioId)
                        ->update(['Estatus' => 4]); // 4 = Cerrada
                }
            }
        }
    }

    private function contabilizarGastosDeCaja($fechaAyerData)
    {
        $this->contabilizarTransacciones($fechaAyerData['listado_gastos'] ?? []);
    }

    private function contabilizarGastosSucursal($fechaAyerData)
    {
        $this->contabilizarTransacciones($fechaAyerData['listado_gastos'] ?? []);
    }

    private function contabilizarAbonosDePrestamo($fechaAyerData)
    {
        $this->contabilizarTransacciones($fechaAyerData['pago_prestamos'] ?? []);
    }

    private function contabilizarPagoServicios($fechaAyerData)
    {
        $pagos = $fechaAyerData['listado_pagos']['pagos_servicios']['Detalle'] ?? [];
        $this->contabilizarTransacciones($pagos);
    }

    private function contabilizarPagoFacturas($fechaAyerData)
    {
        $pagos = $fechaAyerData['listado_pagos']['pagos_mercancia']['Detalle'] ?? [];
        $this->contabilizarTransacciones($pagos);
    }

    private function contabilizarPrestamos($fechaAyerData)
    {
        // $prestamos = $fechaAyerData['prestamos'] ?? [];
        
        // foreach ($prestamos as $prestamo) {
        //     $id = $prestamo->Id ?? $prestamo['Id'] ?? null;
        //     if ($id) {
        //         // Actualizar el estatus del préstamo
        //         DB::table('Prestamos')
        //             ->where('ID', $id)
        //             ->update(['Estatus' => 3]); // Ajusta el valor según tu EnumPrestamo.Cerrada
        //     }
        // }
    }

    private function contabilizarTransacciones($transacciones)
    {
        foreach ($transacciones as $transaccion) {
            $id = null;
            if (is_object($transaccion)) {
                $id = $transaccion->ID ?? $transaccion->id ?? null;
            } elseif (is_array($transaccion)) {
                $id = $transaccion['ID'] ?? $transaccion['id'] ?? null;
            }
            
            if ($id) {
                DB::table('Transacciones')
                    ->where('ID', $id)
                    ->update(['Estatus' => 4]);
            }
        }
    }

    private function contabilizarVentas($fechaAyerData)
    {
        $ventas = $fechaAyerData['ventas'] ?? [];
        
        foreach ($ventas as $venta) {
            // 1. Cambiar estatus de la venta a Cerrada (3)
            $this->cambiarEstatusVenta($venta);
            
            // 2. Cerrar recepciones de la sucursal
            $this->cerrarRecepciones(
                $venta->sucursalId ?? $venta['sucursalId'] ?? null,
                $fechaAyerData['fecha']
            );
        }
    }

    private function cambiarEstatusVenta($venta)
    {
        $id = $venta->id ?? $venta['id'] ?? null;
        
        if ($id) {
            DB::table('Ventas') // Ajusta el nombre de tu tabla
                ->where('ID', $id)
                ->update(['Estatus' => 3]); // 3 = Cerrada
            
            \Log::info("Venta {$id} actualizada a estatus 3");
        }
    }

    private function cerrarRecepciones($sucursalId, $fecha)
    {
        if (!$sucursalId || !$fecha) {
            return;
        }
        
        \Log::info("Iniciando CerrarRecepciones para sucursal {$sucursalId} fecha {$fecha}");
        
        // Crear fechas basadas en el parámetro $fecha (como en .NET)
        $fechaInicio = Carbon::parse($fecha)->startOfDay();
        $fechaFin = Carbon::parse($fecha)->endOfDay();

        $filtroFecha = new ParametrosFiltroFecha(
            null, null, null, false,
            $fechaInicio, $fechaFin
        );
        
        // #region Transferencias de la sucursal
        $transferencias = $this->buscarTransferenciasParaCerrar($sucursalId, $filtroFecha);
        
        if (!empty($transferencias)) {
            // Ordenar por fecha como en .NET
            $transferenciasOrdenadas = collect($transferencias)->sortBy('Fecha')->values();
            
            foreach ($transferenciasOrdenadas as $transferencia) {
                $this->abonarDeudaSucursal($sucursalId, $transferencia, $filtroFecha);
            }
        }
        // #endregion
        
        // Buscar ventas diarias para cerrar
        $ventasPeriodo = $ventasService->obtenerListadoVentasDiariasParaCerrarSinTotalizar($filtroFecha, $sucursalId, true);

        
        if (!empty($ventasPeriodo['ListaVentasDiarias'])) {
            // Ordenar por fecha como en .NET
            $ventasOrdenadas = collect($ventasPeriodo['ListaVentasDiarias'])->sortBy('Fecha')->values();
            
            foreach ($ventasOrdenadas as $venta) {
                $this->abonarDeudaSucursal($sucursalId, $venta, $filtroFecha);
            }
        }
        
        \Log::info("Finalizado CerrarRecepciones para sucursal {$sucursalId}");
    }

    private function abonarDeudaSucursal($sucursalId, $transferencia, $filtroFecha)
    {
        // Generar transacción de abono
        $nuevoAbono = $this->generarTransaccionAbono($transferencia);
        
        $esCerrarOperacion = false;
        $operacionId = 0;
        $montoTransferenciaDivisa = $transferencia->Saldo;
        $nuevoAbono['Observacion'] = "TRANSFERENCIA {$transferencia->TransferenciaId} FECHA: " . 
                                    Carbon::parse($transferencia->Fecha)->format('Y-m-d');
        
        // Buscar recepciones de la sucursal para cerrar
        $listaRecepciones = $this->buscarRecepcionesSucursalParaCerrar($sucursalId, $filtroFecha);
        
        if (!empty($listaRecepciones)) {
            // Filtrar y ordenar como en .NET
            $recepcionesFiltradas = collect($listaRecepciones)
                ->filter(function($r) use ($filtroFecha) {
                    return $r->FechaCreacion <= $filtroFecha->fechaFin;
                })
                ->sortBy('FechaCreacion')
                ->values();
            
            foreach ($recepcionesFiltradas as $recepcion) {
                if ($recepcion->SaldoDivisa > 0) {
                    $nuevoAbono['Descripcion'] = "ABONO POR TRANSFERENCIA - {$recepcion->Numero}";
                    
                    // Asignar monto del abono
                    $resultado = $this->asignarMontoAbono(
                        $nuevoAbono, 
                        $montoTransferenciaDivisa, 
                        $recepcion
                    );
                    
                    $montoTransferenciaDivisa = $resultado['monto_restante'];
                    $esCerrarOperacion = $resultado['es_cerrar'];
                    $operacionId = $recepcion->RecepcionId;
                    $nuevoAbono = $resultado['nuevo_abono'];
                    
                    try {
                        // Guardar abono
                        $nuevoAbono = $this->guardarAbonoRecepcion(
                            $sucursalId, 
                            $nuevoAbono, 
                            $esCerrarOperacion, 
                            $operacionId
                        );
                        
                        // Actualizar saldo de la transferencia
                        DB::table('Transferencias')
                            ->where('TransferenciaId', $transferencia->TransferenciaId)
                            ->update(['Saldo' => $montoTransferenciaDivisa]);
                        
                        // Si se terminó el pago, salir
                        if ($montoTransferenciaDivisa <= 0) {
                            return;
                        }
                        
                    } catch (\Exception $e) {
                        throw $e;
                    }
                }
            }
        }
    }

    private function generarTransaccionAbono($transferencia)
    {
        return [
            'Cedula' => null,
            'DivisaId' => null,
            'Estatus' => 2, // self::ENUM_TRANSACCION_PAGADA
            'Fecha' => $transferencia->Fecha,
            'FormaDePago' => 0, // self::ENUM_FORMA_PAGO_EFECTIVO
            'MontoAbonado' => 0,
            'MontoDivisaAbonado' => $transferencia->Saldo,
            'Nombre' => null,
            'Descripcion' => 'ABONO DEUDA X TRANSFERENCIA',
            'NumeroOperacion' => 'ABT' . Carbon::parse($transferencia->Fecha)->format('Ymd') . '-' . $transferencia->TransferenciaId,
            'Observacion' => 'ABONO DEUDA X TRANSFERENCIA',
            'SucursalId' => $transferencia->SucursalOrigenId,
            'SucursalOrigenId' => $transferencia->SucursalOrigenId,
            'TasaDeCambio' => 0,
            'Tipo' => 7, // self::ENUM_TIPO_TRANSACCION_ABONO_DEUDA
            'UrlComprobante' => null
        ];
    }

    private function buscarRecepcionesSucursalParaCerrar($sucursalId, $filtroFecha)
    {
        try {
            $recepciones = DB::table('Recepciones as r')
                ->leftJoin('TransaccionesRecepciones as tr', 'r.RecepcionId', '=', 'tr.RecepcionId')
                ->leftJoin('Transacciones as t', 'tr.TransaccionId', '=', 't.ID')
                ->where('r.SucursalDestinoId', $sucursalId)
                ->whereNotIn('r.Estatus', [7, 8])
                ->where('r.FechaCreacion', '<=', $filtroFecha->fechaFin)
                ->select(
                    'r.*',
                    't.ID as TransaccionId',
                    't.MontoDivisaAbonado',
                    't.Fecha as TransaccionFecha'
                )
                ->orderBy('r.FechaCreacion')
                ->get();
            
            // Agrupar por recepción
            $recepcionesAgrupadas = [];
            foreach ($recepciones as $item) {
                $recepcionId = $item->RecepcionId;
                
                if (!isset($recepcionesAgrupadas[$recepcionId])) {
                    $recepcionesAgrupadas[$recepcionId] = [
                        'RecepcionId' => $item->RecepcionId,
                        'Numero' => $item->Numero,
                        'SucursalDestinoId' => $item->SucursalDestinoId,
                        'FechaCreacion' => $item->FechaCreacion,
                        'SaldoDivisa' => $item->SaldoDivisa,
                        'Estatus' => $item->Estatus,
                        'AbonoVentas' => []
                    ];
                }
                
                if ($item->TransaccionId) {
                    $recepcionesAgrupadas[$recepcionId]['AbonoVentas'][] = [
                        'TransaccionId' => $item->TransaccionId,
                        'MontoDivisaAbonado' => $item->MontoDivisaAbonado,
                        'Fecha' => $item->TransaccionFecha
                    ];
                }
            }
            
            // Filtrar solo las que tienen saldo > 0 y convertir a objetos
            $resultado = [];
            foreach ($recepcionesAgrupadas as $recepcion) {
                if ($recepcion['SaldoDivisa'] > 0) {
                    $resultado[] = (object)$recepcion;
                }
            }
            
            return $resultado;
            
        } catch (\Exception $e) {
            \Log::error("Error en buscarRecepcionesSucursalParaCerrar: " . $e->getMessage());
            return [];
        }
    }

    private function asignarMontoAbono(&$nuevoAbono, $montoTransferenciaDivisa, $recepcion)
    {
        $operacionId = $recepcion->RecepcionId;
        
        if ($recepcion->SaldoDivisa > 0 && $recepcion->SaldoDivisa <= $montoTransferenciaDivisa) {
            $montoTransferenciaDivisa -= $recepcion->SaldoDivisa;
            $nuevoAbono['MontoDivisaAbonado'] = $recepcion->SaldoDivisa;
            $esCerrarOperacion = true;
        } else {
            $nuevoAbono['MontoDivisaAbonado'] = $montoTransferenciaDivisa;
            $montoTransferenciaDivisa = 0;
            $esCerrarOperacion = false;
        }
        
        // Agregar abono a la recepción
        if (!isset($recepcion->AbonoVentas)) {
            $recepcion->AbonoVentas = [];
        }
        
        $recepcion->AbonoVentas[] = [
            'MontoDivisaAbonado' => $nuevoAbono['MontoDivisaAbonado'],
            'Descripcion' => $nuevoAbono['Descripcion'],
            'Fecha' => $nuevoAbono['Fecha']
        ];
        
        return [
            'monto_restante' => $montoTransferenciaDivisa,
            'es_cerrar' => $esCerrarOperacion,
            'operacion_id' => $operacionId,
            'nuevo_abono' => $nuevoAbono
        ];
    }

    private function guardarAbonoRecepcion($sucursalId, $nuevoAbono, $esCerrarOperacion, $operacionId)
    {
        // Insertar transacción
        $transaccionId = DB::table('Transacciones')->insertGetId([
            'Cedula' => $nuevoAbono['Cedula'],
            'DivisaId' => $nuevoAbono['DivisaId'],
            'Estatus' => $nuevoAbono['Estatus'],
            'Fecha' => $nuevoAbono['Fecha'],
            'FormaDePago' => $nuevoAbono['FormaDePago'],
            'MontoAbonado' => $nuevoAbono['MontoAbonado'],
            'MontoDivisaAbonado' => $nuevoAbono['MontoDivisaAbonado'],
            'Nombre' => $nuevoAbono['Nombre'],
            'Descripcion' => $nuevoAbono['Descripcion'],
            'NumeroOperacion' => $nuevoAbono['NumeroOperacion'],
            'Observacion' => $nuevoAbono['Observacion'],
            'SucursalId' => $nuevoAbono['SucursalId'],
            'SucursalOrigenId' => $nuevoAbono['SucursalOrigenId'],
            'TasaDeCambio' => $nuevoAbono['TasaDeCambio'],
            'Tipo' => $nuevoAbono['Tipo'],
            'UrlComprobante' => $nuevoAbono['UrlComprobante'],
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        // Insertar relación TransaccionesRecepciones
        DB::table('TransaccionesRecepciones')->insert([
            'RecepcionId' => $operacionId,
            'TransaccionId' => $transaccionId,
            'SucursalId' => $sucursalId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        if ($esCerrarOperacion) {
            // Obtener la recepción
            $recepcion = DB::table('Recepciones')
                ->where('RecepcionId', $operacionId)
                ->first();
            
            // Determinar nuevo estatus
            if ($recepcion->Estatus == 6) {
                $nuevoEstatus = 8;
            } else {
                $nuevoEstatus = 7;
            }
            
            // Actualizar recepción
            DB::table('Recepciones')
                ->where('RecepcionId', $operacionId)
                ->update(['Estatus' => $nuevoEstatus]);
        }
        
        // Actualizar el ID en el array del abono
        $nuevoAbono['Id'] = $transaccionId;
        
        return $nuevoAbono;
    }

    private function buscarTransferenciasParaCerrar($sucursalId, $filtroFecha)
    {
        try {
            // Obtener la fecha fin del filtro
            $fechaFin = $filtroFecha->fechaFin ?? $filtroFecha['fecha_fin'] ?? now();
            
            // Buscar transferencias (igual que en .NET)
            $transferencias = DB::table('Transferencias')
                ->where('SucursalOrigenId', $sucursalId)
                ->where('Fecha', '<=', $fechaFin)
                ->where('Saldo', '>', 0)
                ->orderBy('Fecha')
                ->get();
            
            // Convertir a DTOs (como el _mapper.Map en .NET)
            $transferenciasDTO = [];
            foreach ($transferencias as $item) {
                $transferenciasDTO[] = $this->mapearTransferenciaADTO($item);
            }
            
            return $transferenciasDTO;
            
        } catch (\Exception $e) {
            \Log::error("Error en buscarTransferenciasParaCerrar: " . $e->getMessage());
            return [];
        }
    }

    private function mapearTransferenciaADTO($transferencia)
    {
        // Mapear detalles si existen
        $detallesDTO = [];
        if (isset($transferencia->detalles) && $transferencia->detalles->isNotEmpty()) {
            foreach ($transferencia->detalles as $detalle) {
                // Crear ProductoDTO si existe relación
                $productoDTO = null;
                if (isset($detalle->producto)) {
                    $productoDTO = new ProductoDTO([
                        'ProductoId' => $detalle->producto->id,
                        'Descripcion' => $detalle->producto->Descripcion,
                        'CodigoBarras' => $detalle->producto->CodigoBarras,
                        'CostoDivisa' => $detalle->producto->CostoDivisa,
                        'CostoBs' => $detalle->producto->CostoBs,
                        'Referencia' => $detalle->producto->Referencia,
                        'Serial' => $detalle->producto->Serial,
                        'Modelo' => $detalle->producto->Modelo,
                        'CategoriaId' => $detalle->producto->CategoriaId,
                        'MarcaId' => $detalle->producto->MarcaId,
                        'ProveedorId' => $detalle->producto->ProveedorId,
                        'Estatus' => $detalle->producto->Estatus,
                    ]);
                }
                
                $detallesDTO[] = new TransferenciaDetalleDTO([
                    'TransferenciaDetalleId' => $detalle->id,
                    'TransferenciaId' => $transferencia->id,
                    'SucursalId' => $detalle->SucursalId ?? $transferencia->SucursalOrigenId,
                    'ProductoId' => $detalle->ProductoId,
                    'CantidadEmitida' => $detalle->CantidadEmitida,
                    'CantidadRecibida' => $detalle->CantidadRecibida ?? 0,
                    'CostoDivisa' => $detalle->CostoDivisa ?? 0,
                    'CostoBs' => $detalle->CostoBs ?? 0,
                    'CostoUnitario' => $detalle->CostoUnitario ?? 0,
                    'Producto' => $productoDTO
                ]);
            }
        }
        
        // Crear y retornar el DTO de transferencia
        return new TransferenciaDTO([
            'TransferenciaId' => $transferencia->id,
            'Numero' => $transferencia->Numero ?? '',
            'Fecha' => Carbon::parse($transferencia->Fecha),
            'Estatus' => $transferencia->Estatus,
            'SucursalOrigenId' => $transferencia->SucursalOrigenId,
            'SucursalDestinoId' => $transferencia->SucursalDestinoId,
            'Observacion' => $transferencia->Observacion ?? '',
            'Saldo' => $transferencia->Saldo ?? 0,
            'Tipo' => $transferencia->Tipo ?? 0,
            'PasoOperacion' => $transferencia->PasoOperacion ?? 0,
            'Detalles' => $detallesDTO,
            'CantidadItems' => count($detallesDTO),
            
            // Propiedades que vienen de los detalles (se calculan automáticamente en el DTO)
            'CantidadEmitida' => collect($detallesDTO)->sum('CantidadEmitida'),
            'CantidadRecibida' => collect($detallesDTO)->sum('CantidadRecibida'),
            'CantidadDisponible' => collect($detallesDTO)->sum(function($d) {
                return $d->CantidadDisponible; // Usa el getter del DTO
            }),
        ]);
    }

    public function enviar_listado_cerrar_dia(Request $request)
    {
        session([
            'menu_active' => 'Contabilidad',
            'submenu_active' => 'Cerrar Día'
        ]);

        $ventasAgrupadasPorFecha = $this->listar_cerrar_dia();

        return view('cpanel.contabilidad.cerrar_dia', [
            'ventasAgrupadasPorFecha' => $ventasAgrupadasPorFecha
        ]);
    }

    public function listar_cerrar_dia()
    {

        $oficinaPrincipalDTO = new EDCOficinaPrincipalDTO();
        $ventasService = new VentasService();

        // Buscar todas las Sucursales sin el Almacen
        $listaSucursales = GeneralHelper::buscarSucursales(0)
        ->reject(function ($sucursal) {
            return $sucursal->ID == 6;
        })
        ->values();

        // Buscar Sucursal Tipo 0 (Oficina Principal)
        $oficinaPrincipal = $listaSucursales->firstWhere('Tipo', '0');
        // $oficinaPrincipalDTO->SucursalId = $oficinaPrincipal->ID;

        // // Ventas Diarias (No por fecha sino todas las Ventas Diarias sin Cerrar)
        // $user = Auth::user()->load('sucursal');

        // // Si el Usuario es de una Tienda
        // if($user && $user->sucursal->Tipo == 1){

        // }else{
        //     // Si es Oficina Principal
        // }

        $todasLasVentasDiarias = collect();

        foreach ($listaSucursales as $_sucursal)
        {
            $ventasSinTotalizar = $ventasService->obtenerVentasDiariasParaCerrarSinTotalizar(
                $_sucursal->ID, true
            );

            if (isset($ventasSinTotalizar['ListaVentasDiarias'])) {
                // Convertir a collection y agregar información de sucursal
                $ventasConSucursal = collect($ventasSinTotalizar['ListaVentasDiarias'])
                    ->map(function ($venta) use ($_sucursal) {
                        $venta->sucursalIdOrigen = $_sucursal->ID;
                        $venta->nombreSucursalOrigen = $_sucursal->Nombre;
                        return $venta;
                    });

                $todasLasVentasDiarias = $todasLasVentasDiarias->concat($ventasConSucursal);
            }
        }

        // Agrupar por fecha
        $ventasAgrupadasPorFecha = $todasLasVentasDiarias
            ->groupBy(function ($venta) {
                return $venta->fecha->format('Y-m-d'); // Agrupar por fecha (2026-02-26)
            })
            ->map(function ($ventasDelDia, $fecha) {
                // Puedes agregar cálculos por día si los necesitas
                return [
                    'fecha' => $fecha,
                    'fecha_objeto' => \Carbon\Carbon::parse($fecha),
                    'ventas' => $ventasDelDia,
                    'total_divisa_dia' => $ventasDelDia->sum('totalDivisa'),
                    'total_bs_dia' => $ventasDelDia->sum('totalBs'),
                    'cantidad_ventas_dia' => $ventasDelDia->count(),
                    'sucursales_participantes' => $ventasDelDia->pluck('nombreSucursalOrigen')->unique()->values(),
                    'Facturas' => null,
                    'resumen_facturas' => null 
                ];
            });

        // Convertir a array ANTES de la transacción
        $ventasArray = $ventasAgrupadasPorFecha->toArray();

        // Si hay ventas diarias pendientes por cerrar
        if (!empty($ventasArray)) {

            DB::transaction(function () use (&$ventasArray) {

                $ventasServicio = new VentasService();

                foreach ($ventasArray as $fechaKey => $_ventaFecha) {

                    $fecha = $_ventaFecha['fecha_objeto'];
                    $ventasDelDia = $_ventaFecha['ventas'];

                    // // Validación ejemplo
                    // $cierreExistente = CierreOfp::whereDate('Fecha', $fecha)->first();

                    // if ($cierreExistente) {

                    //     dd($cierreExistente);
                    // }

                    $fechaInicio = $fecha;
                    $fechaFin = $fecha;

                    $filtroFecha = new ParametrosFiltroFecha(
                        null,
                        null,
                        null,
                        false,
                        $fechaInicio,
                        $fechaFin
                    );

                    foreach ($ventasDelDia as $venta) {

                        // Obtenga el Cierre Diario por Fecha y Sucursal
                        $tipoEstatus = 3; // Contabilizado
                        $tipo = 1; // Tipo Cierre
                        $CierreDiario = VentasHelper::buscarListadoAuditoriasConContabilidad($filtroFecha, $venta->sucursalId, $tipoEstatus, $tipo);

                        $venta->cierreDiario = $CierreDiario ?? null;
                    }

                    // Obtener facturas por fecha
                    $listadoFacturas = [];
                    $totalFacturas = 0;

                    $listadoFacturas = VentasHelper::buscarFacturasActivasEnProceso($filtroFecha);

                    // Separar facturas por tipo de saldo
                    $facturasMercancia = 0;
                    $totalFacturasMercanciaBs = 0;        // ← NUEVO
                    $totalFacturasMercanciaDivisa = 0;    // ← NUEVO (antes era $totalFacturasMercancia)
                    $totalFacturasMercanciaPagadoBs = 0;  // ← NUEVO
                    $totalFacturasMercanciaPagadoDivisa = 0; // ← NUEVO (antes era $totalFacturasMercanciaPagado)
                    $totalFacturasMercanciaPorPagarBs = 0;   // ← NUEVO
                    $totalFacturasMercanciaPorPagarDivisa = 0; // ← NUEVO (antes era $totalFacturasMercanciaPorPagar)

                    $facturasServicios = 0;
                    $totalFacturasServiciosBs = 0;         // ← NUEVO
                    $totalFacturasServiciosDivisa = 0;     // ← NUEVO (antes era $totalFacturasServicios)
                    $totalFacturasServiciosPagadoBs = 0;   // ← NUEVO
                    $totalFacturasServiciosPagadoDivisa = 0; // ← NUEVO (antes era $totalFacturasServiciosPagado)
                    $totalFacturasServiciosPorPagarBs = 0;    // ← NUEVO
                    $totalFacturasServiciosPorPagarDivisa = 0; // ← NUEVO (antes era $totalFacturasServiciosPorPagar)

                    foreach ($listadoFacturas as $factura) {
                        // La factura es tipo MERCANCIA
                        if($factura['Factura']['Tipo'] == 0){
                            $totalFacturasMercanciaBs += $factura['TotalBs'] ?? 0;
                            $totalFacturasMercanciaDivisa += $factura['TotalDivisa'] ?? 0;
                            $totalFacturasMercanciaPagadoBs += $factura['TotalAbonadoBs'] ?? 0;
                            $totalFacturasMercanciaPagadoDivisa += $factura['TotalAbonadoDivisa'] ?? 0;
                            $totalFacturasMercanciaPorPagarBs += $factura['TotalSaldoBs'] ?? 0;      // ← NUEVO
                            $totalFacturasMercanciaPorPagarDivisa += $factura['TotalSaldoDivisa'] ?? 0;
                            $facturasMercancia++;
                        }

                        // La factura es tipo SERVICIO
                        if($factura['Factura']['Tipo'] == 1){
                            $totalFacturasServiciosBs += $factura['TotalBs'] ?? 0;
                            $totalFacturasServiciosDivisa += $factura['TotalDivisa'] ?? 0;
                            $totalFacturasServiciosPagadoBs += $factura['TotalAbonadoBs'] ?? 0;
                            $totalFacturasServiciosPagadoDivisa += $factura['TotalAbonadoDivisa'] ?? 0;
                            $totalFacturasServiciosPorPagarBs += $factura['TotalSaldoBs'] ?? 0;      // ← NUEVO
                            $totalFacturasServiciosPorPagarDivisa += $factura['TotalSaldoDivisa'] ?? 0;
                            $facturasServicios++;
                        }
                    }

                    // Detalle de facturas (AHORA CON BS Y DIVISA)
                    $detalleFacturas = [
                        'cantidad_facturas_mercancia' => $facturasMercancia,
                        'facturas_mercancia_bs' => round($totalFacturasMercanciaBs, 2),           // ← NUEVO
                        'facturas_mercancia_divisa' => round($totalFacturasMercanciaDivisa, 2),   // ← NUEVO
                        'abonado_mercancia_bs' => round($totalFacturasMercanciaPagadoBs, 2),      // ← NUEVO
                        'abonado_mercancia_divisa' => round($totalFacturasMercanciaPagadoDivisa, 2), // ← NUEVO
                        'pendiente_mercancia_bs' => round($totalFacturasMercanciaPorPagarBs, 2),  // ← NUEVO
                        'pendiente_mercancia_divisa' => round($totalFacturasMercanciaPorPagarDivisa, 2), // ← NUEVO
                        'cantidad_facturas_servicios' => $facturasServicios,
                        'facturas_servicios_bs' => round($totalFacturasServiciosBs, 2),           // ← NUEVO
                        'facturas_servicios_divisa' => round($totalFacturasServiciosDivisa, 2),   // ← NUEVO
                        'abonado_servicios_bs' => round($totalFacturasServiciosPagadoBs, 2),      // ← NUEVO
                        'abonado_servicios_divisa' => round($totalFacturasServiciosPagadoDivisa, 2), // ← NUEVO
                        'pendiente_servicios_bs' => round($totalFacturasServiciosPorPagarBs, 2),  // ← NUEVO
                        'pendiente_servicios_divisa' => round($totalFacturasServiciosPorPagarDivisa, 2), // ← NUEVO
                    ];

                    $facturasDetalle = [
                        'MontoBs' => round($totalFacturasMercanciaBs + $totalFacturasServiciosBs, 2),        // ← NUEVO
                        'MontoDivisa' => round($totalFacturasMercanciaDivisa + $totalFacturasServiciosDivisa, 2), // ← NUEVO
                        'Detalle' => $detalleFacturas,
                        'Cantidad' => count($listadoFacturas),
                        'Listado' => $listadoFacturas
                    ];

                    // Asignar los datos reales de facturas al array
                    $ventasArray[$fechaKey]['Facturas'] = $facturasDetalle;

                    // Separar facturas por tipo
                    $facturasMercanciaArray = array_filter($listadoFacturas, function($factura) {
                        return $factura['Factura']['Tipo'] == 0;
                    });

                    $facturasServiciosArray = array_filter($listadoFacturas, function($factura) {
                        return $factura['Factura']['Tipo'] == 1;
                    });

                    $ventasArray[$fechaKey]['resumen_facturas'] = [
                        'mercancia' => [
                            'cantidad' => count($facturasMercanciaArray),
                            'total_bs' => round($totalFacturasMercanciaBs, 2),              // ← NUEVO
                            'total_divisa' => round($totalFacturasMercanciaDivisa, 2),      // ← NUEVO
                            'pagado_bs' => round($totalFacturasMercanciaPagadoBs, 2),       // ← NUEVO
                            'pagado_divisa' => round($totalFacturasMercanciaPagadoDivisa, 2), // ← NUEVO
                            'pendiente_bs' => round($totalFacturasMercanciaPorPagarBs, 2),  // ← NUEVO
                            'pendiente_divisa' => round($totalFacturasMercanciaPorPagarDivisa, 2), // ← NUEVO
                            'facturas' => array_values($facturasMercanciaArray)
                        ],
                        'servicios' => [
                            'cantidad' => count($facturasServiciosArray),
                            'total_bs' => round($totalFacturasServiciosBs, 2),              // ← NUEVO
                            'total_divisa' => round($totalFacturasServiciosDivisa, 2),      // ← NUEVO
                            'pagado_bs' => round($totalFacturasServiciosPagadoBs, 2),       // ← NUEVO
                            'pagado_divisa' => round($totalFacturasServiciosPagadoDivisa, 2), // ← NUEVO
                            'pendiente_bs' => round($totalFacturasServiciosPorPagarBs, 2),  // ← NUEVO
                            'pendiente_divisa' => round($totalFacturasServiciosPorPagarDivisa, 2), // ← NUEVO
                            'facturas' => array_values($facturasServiciosArray)
                        ]
                    ];

                    // También podemos mantener el listado original si es necesario
                    $ventasArray[$fechaKey]['listado_facturas_original'] = $listadoFacturas;


                    //-----------------------------------------------------//
                    //----------PAGOS DE MERCANCIAS Y SERVICIOS------------//
                    //-----------------------------------------------------//
                    $listadoPagoServicios = collect();
                    $listadoPagoMercancia = collect();

                    // Inicializar variables para servicios
                    $totalPagosServiciosBs = 0;       
                    $totalPagosServiciosDivisa = 0;    
                    $totalPagosServicios = 0;          

                    // Inicializar variables para mercancía
                    $totalPagosMercanciaBs = 0;        
                    $totalPagosMercanciaDivisa = 0;    
                    $totalPagosMercancia = 0;           

                    $listadoPagoServicios = $ventasServicio->buscarTransacciones(
                        1, null,
                        ['fecha_inicio' => $filtroFecha->fechaInicio, 'fecha_fin' => $filtroFecha->fechaFin]
                    );

                    foreach ($listadoPagoServicios as $pago) {
                        $totalPagosServiciosBs += (float)($pago->MontoAbonado ?? $pago['MontoAbonado'] ?? 0);
                        $totalPagosServiciosDivisa += (float)($pago->MontoDivisaAbonado ?? $pago['MontoDivisaAbonado'] ?? 0);
                    }


                    // Pagos de mercancía (tipo 0) - Estos SÍ existen
                    $listadoPagoMercancia = $ventasServicio->buscarTransacciones(
                        0, null,
                        ['fecha_inicio' => $filtroFecha->fechaInicio, 'fecha_fin' => $filtroFecha->fechaFin]
                    );

                    foreach ($listadoPagoMercancia as $pago) {
                        $totalPagosMercanciaBs += (float)($pago->MontoAbonado ?? $pago['MontoAbonado'] ?? 0);
                        $totalPagosMercanciaDivisa += (float)($pago->MontoDivisaAbonado ?? $pago['MontoDivisaAbonado'] ?? 0);
                    }

                    // Total Egresos
                    $ventasArray[$fechaKey]['total_egresos'] = round($totalPagosServicios + $totalPagosMercancia, 2);

                    $ventasArray[$fechaKey]['listado_pagos'] = [
                        'pagos_mercancia' => [
                            'MontoBs' => round($totalPagosMercanciaBs, 2),
                            'MontoDivisa' => round($totalPagosMercanciaDivisa, 2),
                            'Detalle' => $listadoPagoMercancia,
                        ],
                        'pagos_servicios' => [
                            'MontoBs' => round($totalPagosServiciosBs, 2),
                            'MontoDivisa' => round($totalPagosServiciosDivisa, 2),
                            'Detalle' => $listadoPagoServicios,
                        ]
                    ];


                    //-----------------------------------------------------//
                    //------------------------PRESTAMOS--------------------//
                    //-----------------------------------------------------//
                    $ventasArray[$fechaKey]['prestamos'] = $ventasServicio->BuscarPrestamosActivos($filtroFecha);

                    //-----------------------------------------------------//
                    //--------------------PAGO DE PRESTAMOS----------------//
                    //-----------------------------------------------------//
                    $ventasArray[$fechaKey]['pago_prestamos'] = $ventasServicio->buscarPagosPrestamoPorFecha($filtroFecha);

                    //-----------------------------------------------------//
                    //-------------------GASTOS POR SUCURSAL---------------//
                    //-----------------------------------------------------//
                    $listadoGastos = collect(); // Colección vacía por defecto
                    $totalGastos = 0;

                    $listadoGastos = $ventasServicio->buscarTransacciones(
                        2, null,
                        ['fecha_inicio' => $filtroFecha->fechaInicio, 'fecha_fin' => $filtroFecha->fechaFin]
                    );

                    foreach ($listadoGastos as $gasto) {
                        $totalGastos += (float)($gasto->MontoDivisaAbonado ?? $gasto['MontoDivisaAbonado'] ?? 0);
                    }

                    // Total Egresos
                    $ventasArray[$fechaKey]['total_gastos'] = round($totalGastos, 2);

                    $ventasArray[$fechaKey]['listado_gastos'] = $listadoGastos;
                }

            });
        }

        // Reconstruir la colección con los datos modificados
        $ventasAgrupadasPorFecha = collect($ventasArray);

        // Para ver el resultado agrupado
        // dd($ventasAgrupadasPorFecha);

        // return view('cpanel.contabilidad.cerrar_dia', [
        //     'ventasAgrupadasPorFecha' => $ventasAgrupadasPorFecha
        // ]);

        return $ventasAgrupadasPorFecha;
    }

    private function procesarCierresDiarios($ventas)
    {
        $totales = [
            'total_bs_facturados' => 0,
            'total_egresos_bs' => 0,
            'total_egresos_divisa' => 0,  // ← NUEVO CAMPO
            'total_bs_periodo' => 0,
            'total_cobrado_bs' => 0,
            'total_convertidos_bs' => 0,
            'total_divisa' => 0,
            'tiene_cierres' => false
        ];
        
        foreach ($ventas as $venta) {
            if (isset($venta->cierreDiario) && $venta->cierreDiario->isNotEmpty()) {
                $cierre = $venta->cierreDiario->first();
                $totales['tiene_cierres'] = true;
                
                // Calcular TotalCobradoBs (código existente)
                $cobradoBs = floatval($cierre->EfectivoBs ?? 0) +
                            floatval($cierre->PagoMovilBs ?? 0) +
                            floatval($cierre->TransferenciaBs ?? 0) +
                            floatval($cierre->Biopago ?? 0) +
                            floatval($cierre->CasheaBs ?? 0) +
                            floatval($cierre->PuntoDeVentaBs ?? 0);
                
                // Calcular TotalDivisa
                $totalDivisa = floatval($cierre->EfectivoDivisas ?? 0) +
                            floatval($cierre->TransferenciaDivisas ?? 0) +
                            floatval($cierre->ZelleDivisas ?? 0) +
                            floatval($cierre->PuntoDeVentaDivisas ?? 0);
                
                // Calcular TotalBsConvertidos
                $divisaValor = floatval($cierre->DivisaValor ?? 0);
                $convertidosBs = $divisaValor * $totalDivisa;
                
                // TotalBsFacturados
                $bsFacturados = $cobradoBs + $convertidosBs;
                
                // Gastos en Bs (código existente)
                $egresosBs = 0;
                if (isset($cierre->relations['gastosCierreDiario']) && $cierre->gastosCierreDiario->isNotEmpty()) {
                    foreach ($cierre->gastosCierreDiario as $gasto) {
                        $egresosBs += floatval($gasto->MontoAbonado ?? 0);
                    }
                }
                
                // Gastos en Divisas (NUEVO)
                $egresosDivisa = floatval($cierre->EgresoDivisas ?? 0);
                
                // Acumular totales
                $totales['total_bs_facturados'] += $bsFacturados;
                $totales['total_egresos_bs'] += $egresosBs;
                $totales['total_egresos_divisa'] += $egresosDivisa;  // ← NUEVO
                $totales['total_cobrado_bs'] += $cobradoBs;
                $totales['total_convertidos_bs'] += $convertidosBs;
                $totales['total_divisa'] += $totalDivisa;
            }
        }
        
        $totales['total_bs_periodo'] = $totales['total_bs_facturados'] - $totales['total_egresos_bs'];
        
        return $totales;
    }
}