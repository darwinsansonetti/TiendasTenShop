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
use App\Models\Sucursal;
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

    public function cerrar_dia_automaticamente(Request $request = null)
    {
        
        $ventasService = new VentasService();

        $now = Carbon::now('America/Caracas');
        // $ayer = $now->copy()->subDay(); // Restar 1 día

        $ayer = Carbon::parse('2026-03-12', 'America/Caracas');
        
        \Log::info("=== INICIO CIERRE AUTOMÁTICO ===");
        \Log::info("Fecha actual: " . $now->toDateTimeString());
        \Log::info("Procesando fecha: " . $ayer->format('Y-m-d'));
        
        try {
            // Se busca si hay Cierre del dia anterior
            $cierreExistente = CierreOfp::whereDate('Fecha', $ayer)->first();
            
            if ($cierreExistente) {
                \Log::warning("El día {$ayer->format('Y-m-d')} ya tiene un cierre registrado", [
                    'cierre_id' => $cierreExistente->CierreOfpId
                ]);
                return;
            }

            \Log::info("No hay cierre previo, continuando...");

            // Ok..

            $_oficinaPrincipalDTO = new EDCOficinaPrincipalDTO();
            $_oficinaPrincipalDTO->Fecha = $ayer;

            // Obtenemos el modelo de la Sucursal tipo Oficina
            $sucursal = Sucursal::where('Tipo', 0)->first();

            if($sucursal){
                $_oficinaPrincipalDTO->Sucursal = $sucursal;
                $_oficinaPrincipalDTO->SucursalId = $sucursal->ID;
            }

            // Ventas Diarias Totalizadas en la Fecha
            $user = Auth::user()->load('sucursal');
            $sucursalActivaId = 0;

            // Si el Usuario es de una Tienda
            if($user && $user->sucursal->Tipo == 1){
                $sucursalActivaId = $user->SucursalId;
            }else{
                $sucursalActivaId = 0;
            }

            // $fechaInicio = $ayer;
            // $fechaFin = $ayer;

            $fechaInicio = $ayer->copy()->startOfDay();  // 2026-03-09 00:00:00
            $fechaFin = $ayer->copy()->endOfDay();       // 2026-03-09 23:59:59

            $filtroFecha = new ParametrosFiltroFecha(
                null, null, null, false,
                $fechaInicio,
                $fechaFin
            );

            $filtroFecha = new ParametrosFiltroFecha(
                null,
                null,
                null,
                false,
                $fechaInicio,
                $fechaFin
            );

            $_oficinaPrincipalDTO->VentasDiariaPeriodo = $ventasService->obtenerVentasDiariasParaCerrarSinTotalizarEnPeriodo(
                $sucursalActivaId, false, $filtroFecha
            );

            if(!$_oficinaPrincipalDTO->VentasDiariaPeriodo){
                return;
            }

            //cierres diarios
            $tipoEstatus = -100;
            $tipoCierre = 1;

            $_oficinaPrincipalDTO->CierreDiario = VentasHelper::buscarListadoAuditoriasConContabilidadNuevo($sucursalActivaId, $filtroFecha, $tipoEstatus, $tipoCierre);

            if(!$_oficinaPrincipalDTO->CierreDiario){
                return;
            }

            // Facturas de Mercancia y Servicios
            $listadoFacturas = [];
            $totalFacturas = 0;

            $listadoFacturas = VentasHelper::buscarFacturasActivasEnProceso($filtroFecha);

            // Separar facturas por tipo de saldo
            $facturasMercancia = 0;
            $totalFacturasMercanciaBs = 0;        
            $totalFacturasMercanciaDivisa = 0;    
            $totalFacturasMercanciaPagadoBs = 0;  
            $totalFacturasMercanciaPagadoDivisa = 0; 
            $totalFacturasMercanciaPorPagarBs = 0;   
            $totalFacturasMercanciaPorPagarDivisa = 0; 

            $facturasServicios = 0;
            $totalFacturasServiciosBs = 0;         
            $totalFacturasServiciosDivisa = 0;     
            $totalFacturasServiciosPagadoBs = 0;   
            $totalFacturasServiciosPagadoDivisa = 0; 
            $totalFacturasServiciosPorPagarBs = 0;   
            $totalFacturasServiciosPorPagarDivisa = 0; 

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

            // Factura tipo Mercancia
            $_oficinaPrincipalDTO->FacturasMercancia = array_filter($listadoFacturas, function($factura) {
                return $factura['Factura']['Tipo'] == 0;
            });

            // Factura tipo Servicio
            $_oficinaPrincipalDTO->FacturasServicio = array_filter($listadoFacturas, function($factura) {
                return $factura['Factura']['Tipo'] == 1;
            });

            // $ventasArray[$fechaKey]['resumen_facturas'] = [
            //     'mercancia' => [
            //         'cantidad' => count($facturasMercanciaArray),
            //         'total_bs' => round($totalFacturasMercanciaBs, 2),              // ← NUEVO
            //         'total_divisa' => round($totalFacturasMercanciaDivisa, 2),      // ← NUEVO
            //         'pagado_bs' => round($totalFacturasMercanciaPagadoBs, 2),       // ← NUEVO
            //         'pagado_divisa' => round($totalFacturasMercanciaPagadoDivisa, 2), // ← NUEVO
            //         'pendiente_bs' => round($totalFacturasMercanciaPorPagarBs, 2),  // ← NUEVO
            //         'pendiente_divisa' => round($totalFacturasMercanciaPorPagarDivisa, 2), // ← NUEVO
            //         'facturas' => array_values($facturasMercanciaArray)
            //     ],
            //     'servicios' => [
            //         'cantidad' => count($facturasServiciosArray),
            //         'total_bs' => round($totalFacturasServiciosBs, 2),              // ← NUEVO
            //         'total_divisa' => round($totalFacturasServiciosDivisa, 2),      // ← NUEVO
            //         'pagado_bs' => round($totalFacturasServiciosPagadoBs, 2),       // ← NUEVO
            //         'pagado_divisa' => round($totalFacturasServiciosPagadoDivisa, 2), // ← NUEVO
            //         'pendiente_bs' => round($totalFacturasServiciosPorPagarBs, 2),  // ← NUEVO
            //         'pendiente_divisa' => round($totalFacturasServiciosPorPagarDivisa, 2), // ← NUEVO
            //         'facturas' => array_values($facturasServiciosArray)
            //     ]
            // ];

            // // También podemos mantener el listado original si es necesario
            // $ventasArray[$fechaKey]['listado_facturas_original'] = $listadoFacturas;


            //--------------------------------------------------------------//
            //----------PAGOS DE FACTURAS MERCANCIAS Y SERVICIOS------------//
            //--------------------------------------------------------------//
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

            $listadoPagoServicios = $ventasService->buscarTransacciones(
                5, null,
                ['fecha_inicio' => $filtroFecha->fechaInicio, 'fecha_fin' => $filtroFecha->fechaFin]
            );

            // foreach ($listadoPagoServicios as $pago) {
            //     $totalPagosServiciosBs += (float)($pago->MontoAbonado ?? $pago['MontoAbonado'] ?? 0);
            //     $totalPagosServiciosDivisa += (float)($pago->MontoDivisaAbonado ?? $pago['MontoDivisaAbonado'] ?? 0);
            // }


            // Pagos de mercancía (tipo 0) - Estos SÍ existen
            $listadoPagoMercancia = $ventasService->buscarTransacciones(
                0, null,
                ['fecha_inicio' => $filtroFecha->fechaInicio, 'fecha_fin' => $filtroFecha->fechaFin]
            );

            // dd($listadoPagoMercancia);

            // foreach ($listadoPagoMercancia as $pago) {
            //     $totalPagosMercanciaBs += (float)($pago->MontoAbonado ?? $pago['MontoAbonado'] ?? 0);
            //     $totalPagosMercanciaDivisa += (float)($pago->MontoDivisaAbonado ?? $pago['MontoDivisaAbonado'] ?? 0);
            // }

            // // Total Egresos
            // $ventasArray[$fechaKey]['total_egresos'] = round($totalPagosServicios + $totalPagosMercancia, 2);

            // $ventasArray[$fechaKey]['listado_pagos'] = [
            //     'pagos_mercancia' => [
            //         'MontoBs' => round($totalPagosMercanciaBs, 2),
            //         'MontoDivisa' => round($totalPagosMercanciaDivisa, 2),
            //         'Detalle' => $listadoPagoMercancia,
            //     ],
            //     'pagos_servicios' => [
            //         'MontoBs' => round($totalPagosServiciosBs, 2),
            //         'MontoDivisa' => round($totalPagosServiciosDivisa, 2),
            //         'Detalle' => $listadoPagoServicios,
            //     ]
            // ];
            
            $_oficinaPrincipalDTO->PagosFacturas = $listadoPagoMercancia;
            $_oficinaPrincipalDTO->PagosServicios = $listadoPagoServicios;
            
            //-----------------------------------------------------//
            //------------------------PRESTAMOS--------------------//
            //-----------------------------------------------------//
            $_oficinaPrincipalDTO->Prestamos = $ventasService->BuscarPrestamosActivos($filtroFecha);
            
            //-----------------------------------------------------//
            //--------------------PAGO DE PRESTAMOS----------------//
            //-----------------------------------------------------//
            $_oficinaPrincipalDTO->PagosPrestamos = $ventasService->buscarPagosPrestamoPorFecha($filtroFecha);

            //-----------------------------------------------------//
            //-------------------GASTOS POR SUCURSAL---------------//
            //-----------------------------------------------------//
            $listadoGastos = collect(); // Colección vacía por defecto
            $totalGastos = 0;

            $listadoGastos = $ventasService->buscarTransacciones(
                2, null,
                ['fecha_inicio' => $filtroFecha->fechaInicio, 'fecha_fin' => $filtroFecha->fechaFin]
            );

            // foreach ($listadoGastos as $gasto) {
            //     $totalGastos += (float)($gasto->MontoDivisaAbonado ?? $gasto['MontoDivisaAbonado'] ?? 0);
            // }

            // // Total Egresos
            // $ventasArray[$fechaKey]['total_gastos'] = round($totalGastos, 2);

            $_oficinaPrincipalDTO->GastosSucursal = $listadoGastos;

            // dd(json_encode($_oficinaPrincipalDTO, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));


            DB::beginTransaction();
            \Log::info("Transacción de base de datos iniciada");
            
            try {
                // Crear el cierre para la fecha específica
                $_cierreOFP = new CierreOfp();
                \Log::info("Objeto CierreOfp creado");

                // ========== ABONOS (Préstamos) ==========
                $pagoPrestamos = collect($_oficinaPrincipalDTO->PagosPrestamos ?? []);
                $_cierreOFP->AbonosBs = $pagoPrestamos->sum('MontoAbonado');
                $_cierreOFP->AbonosDivisa = $pagoPrestamos->sum('MontoDivisaAbonado');
                \Log::info("Abonos asignados", [
                    'AbonosBs' => $_cierreOFP->AbonosBs,
                    'AbonosDivisa' => $_cierreOFP->AbonosDivisa
                ]);

                // dd($_oficinaPrincipalDTO->VentasDiariaPeriodo['ListaVentasDiarias']);

                // ========== VENTAS (Cierre) ==========
                //$_oficinaPrincipalDTO->VentasDiariaPeriodo['ListaVentasDiarias']
                $cierresData = $this->procesarCierresDiarios($_oficinaPrincipalDTO->VentasDiariaPeriodo['ListaVentasDiarias'], $_oficinaPrincipalDTO->CierreDiario);
                \Log::info("Datos de cierres procesados", [
                    'tiene_cierres' => $cierresData['tiene_cierres'],
                    'total_bs_periodo' => $cierresData['total_bs_periodo'] ?? 0,
                    'total_divisa' => $cierresData['total_divisa'] ?? 0
                ]);

                if ($cierresData['tiene_cierres'] && ($cierresData['total_bs_periodo'] ?? 0) > 0) {
                    $_cierreOFP->CierreBs = $cierresData['total_bs_periodo'];
                    $_cierreOFP->CierreDivisa = $cierresData['total_divisa'];
                }
                
                // else {
                //     $_cierreOFP->CierreBs = $fechaAyerData['total_bs_dia'] ?? 0;
                //     $_cierreOFP->CierreDivisa = $fechaAyerData['total_divisa_dia'] ?? 0;
                // }
                \Log::info("CierreBs asignado: {$_cierreOFP->CierreBs}, CierreDivisa: {$_cierreOFP->CierreDivisa}");

                // ========== ESTATUS ==========
                $_cierreOFP->Estatus = 0; // Abierto

                // ========== FECHA ==========
                $_cierreOFP->Fecha = $_oficinaPrincipalDTO->Fecha;

                // ========== FACTURAS DE MERCANCÍA ==========
                $_cierreOFP->FacturasBs = $totalFacturasMercanciaBs ?? 0;
                $_cierreOFP->FacturasDivisa = $totalFacturasMercanciaDivisa ?? 0;

                // ========== GASTOS DE CAJA (Egresos) ==========
                $_cierreOFP->GastosCajaBs = $cierresData['total_egresos_bs'] ?? 0; 
                $_cierreOFP->GastosCajaDivisa = $cierresData['total_egresos_divisa'] ?? 0; 

                // ========== GASTOS DE SUCURSAL ==========
                $gastosSucursal = collect($_oficinaPrincipalDTO->GastosSucursal ?? []);
                $_cierreOFP->GastosSucursalBs = $gastosSucursal->sum('MontoAbonado');
                $_cierreOFP->GastosSucursalDivisa = $gastosSucursal->sum('MontoDivisaAbonado');

                \Log::info("Gastos de sucursal procesados", [
                    'cantidad' => $gastosSucursal->count(),
                    'total_bs' => $_cierreOFP->GastosSucursalBs,
                    'total_divisa' => $_cierreOFP->GastosSucursalDivisa
                ]);

                // ========== PAGOS DE SERVICIOS ==========
                $listaPagosServicios = collect($_oficinaPrincipalDTO->PagosServicios ?? []);
                $_cierreOFP->PagoServiciosBs = $listaPagosServicios->sum('MontoAbonado');
                $_cierreOFP->PagoServiciosDivisa = $listaPagosServicios->sum('MontoDivisaAbonado');  

                // ========== PAGOS DE FACTURAS ==========
                $listaPagosFacturas = collect($_oficinaPrincipalDTO->PagosFacturas ?? []);
                $_cierreOFP->PagoFacturasBs = $listaPagosFacturas->sum('MontoAbonado');
                $_cierreOFP->PagoFacturasDivisa = $listaPagosFacturas->sum('MontoDivisaAbonado');  

                // ========== PRÉSTAMOS ==========
                $prestamos = collect($_oficinaPrincipalDTO->Prestamos ?? []);
                $_cierreOFP->PrestamosBs = $prestamos->sum('montoBs');        // ← minúscula
                $_cierreOFP->PrestamosDivisa = $prestamos->sum('montoDivisa'); // ← minúscula

                \Log::info("Préstamos procesados", [
                    'cantidad' => $prestamos->count(),
                    'total_bs' => $_cierreOFP->PrestamosBs,
                    'total_divisa' => $_cierreOFP->PrestamosDivisa
                ]);

                // ========== SALDO DE OPERACIÓN BS ==========
                $totalAbonosBs = $_cierreOFP->AbonosBs ?? 0;
                $totalCierreBs = $_cierreOFP->CierreBs ?? 0;
                $totalGastosCajaBs = $_cierreOFP->GastosCajaBs ?? 0;
                $totalGastosSucursalBs = $_cierreOFP->GastosSucursalBs ?? 0;
                $totalPagoFacturasBs = $_cierreOFP->PagoFacturasBs ?? 0;
                $totalPagoServiciosBs = $_cierreOFP->PagoServiciosBs ?? 0;
                $totalPagosGeneralBs = 0;

                $totalPagosGeneralBs = ($_cierreOFP->PagoServiciosBs ?? 0) + ($_cierreOFP->PagoFacturasBs ?? 0);

                $totalPrestamosBs = $_cierreOFP->PrestamosBs ?? 0;

                $saldoOperacionBs = ($totalAbonosBs + $totalCierreBs) - 
                                    ($totalGastosCajaBs + 
                                    $totalGastosSucursalBs + 
                                    $totalPagoFacturasBs + 
                                    $totalPagoServiciosBs + 
                                    $totalPagosGeneralBs + 
                                    $totalPrestamosBs);

                $_cierreOFP->SaldoOperacionBs = $saldoOperacionBs;
                
                // ========== VENTAS (solo ventas, sin conversiones) ==========
                // Calcular totales de ventas del día
                $totalVentasBs = 0;
                $totalVentasDivisa = 0;

                if (isset($_oficinaPrincipalDTO->VentasDiariaPeriodo['ListaVentasDiarias'])) {
                    foreach ($_oficinaPrincipalDTO->VentasDiariaPeriodo['ListaVentasDiarias'] as $venta) {
                        $totalVentasBs += $venta->totalBs ?? 0;
                        $totalVentasDivisa += $venta->totalDivisa ?? 0;
                    }
                }

                // ========== VENTAS (solo ventas) ==========
                $_cierreOFP->VentaBs = $totalVentasBs;
                $_cierreOFP->VentaDivisa = $totalVentasDivisa;

                // ... más abajo ...

                // ========== SALDO DE OPERACIÓN DIVISAS ==========
                $totalAbonosDivisa = $_cierreOFP->AbonosDivisa ?? 0;
                $totalGastosCajaDivisa = $_cierreOFP->GastosCajaDivisa ?? 0;
                $totalGastosSucursalDivisa = $_cierreOFP->GastosSucursalDivisa ?? 0;
                $totalPagoFacturasDivisa = $_cierreOFP->PagoFacturasDivisa ?? 0;
                $totalPagoServiciosDivisa = $_cierreOFP->PagoServiciosDivisa ?? 0;
                $totalPrestamosDivisa = $_cierreOFP->PrestamosDivisa ?? 0;

                $totalPagosGeneralDivisa = $totalPagoFacturasDivisa + $totalPagoServiciosDivisa;

                $_cierreOFP->SaldoOperacionDivisas = ($totalAbonosDivisa + $totalVentasDivisa) - 
                                                    ($totalGastosCajaDivisa + 
                                                    $totalGastosSucursalDivisa + 
                                                    $totalPagosGeneralDivisa + 
                                                    $totalPrestamosDivisa); 

                // ========== SUCURSAL ==========
                $_cierreOFP->SucursalId = $_oficinaPrincipalDTO->SucursalId ?? 8;

                // ========== SERVICIOS (Facturas de Servicios) ==========
                $_cierreOFP->ServiciosBs = $totalFacturasServiciosBs ?? 0;
                $_cierreOFP->ServiciosDivisa = $totalFacturasServiciosDivisa ?? 0;

                // 2. AHORA llamar a los métodos de contabilización
                \Log::info("Iniciando métodos de contabilización");

                // dd($_cierreOFP);
                // dd(json_encode($_cierreOFP, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

                // dd($ventasAgrupadasPorFecha);
                
                $this->contabilizarAbonosDePrestamo($_oficinaPrincipalDTO);
                $this->contabilizarCierresDiarios($_oficinaPrincipalDTO);
                $this->contabilizarGastosDeCaja($_oficinaPrincipalDTO);
                $this->contabilizarGastosSucursal($_oficinaPrincipalDTO);
                $this->contabilizarPagoServicios($_oficinaPrincipalDTO);
                $this->contabilizarPagoFacturas($_oficinaPrincipalDTO);
                $this->contabilizarPrestamos($_oficinaPrincipalDTO);
                $this->contabilizarVentas($_oficinaPrincipalDTO);
                
                // \Log::info("Métodos de contabilización completados");

                // 3. Guardar el cierre
                $_cierreOFP->save();
                \Log::info("Cierre guardado con ID: " . $_cierreOFP->CierreOfpId);

                // 4. Actualizar saldos
                $this->actualizarSaldoCierre($_cierreOFP);
                \Log::info("Saldos actualizados");

                DB::commit();
                \Log::info("Transacción commiteada exitosamente");
                
                \Log::info("=== CIERRE AUTOMÁTICO COMPLETADO CON ÉXITO ===");
                
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error("❌ Error durante el cierre: " . $e->getMessage());
                \Log::error($e->getTraceAsString());
                throw $e;
            }
            
        } catch (\Exception $e) {
            \Log::error("❌ Error en cierre automático: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
        }
    }

    private function contabilizarTransacciones($transacciones)
    {
        $contador = 0;
        $idsActualizados = [];
        
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
                
                $contador++;
                $idsActualizados[] = $id;
            }
        }
        
        \Log::info("contabilizarTransacciones ejecutado", [
            'total_transacciones_procesadas' => count($transacciones),
            'total_actualizadas' => $contador,
            'ids' => $idsActualizados
        ]);
    }

    private function contabilizarAbonosDePrestamo($_oficinaPrincipalDTO)
    {
        $total = count($_oficinaPrincipalDTO->PagosPrestamos ?? []);
        \Log::info("contabilizarAbonosDePrestamo iniciado", [
            'total_abonos' => $total
        ]);
        
        $this->contabilizarTransacciones($_oficinaPrincipalDTO->PagosPrestamos ?? []);
        
        \Log::info("contabilizarAbonosDePrestamo completado");
    }

    private function contabilizarCierresDiarios($_oficinaPrincipalDTO)
    {
        \Log::info("contabilizarCierresDiarios iniciado");
        
        $contador = 0;
        $idsActualizados = [];
        
        // Obtener la colección de cierres diarios del DTO
        $cierresDiarios = $_oficinaPrincipalDTO->CierreDiario ?? collect();
        
        foreach ($cierresDiarios as $cierre) {
            // Actualizar usando Eloquent
            CierreDiario::where('CierreDiarioId', $cierre->CierreDiarioId)
                ->update(['Estatus' => 4]); // 4 = Cerrada
            
            $contador++;
            $idsActualizados[] = $cierre->CierreDiarioId;
            
            \Log::info("Cierre diario actualizado", [
                'CierreDiarioId' => $cierre->CierreDiarioId,
                'sucursal' => $cierre->SucursalId ?? 'N/A'
            ]);
        }
        
        \Log::info("contabilizarCierresDiarios completado", [
            'total_cierres_actualizados' => $contador,
            'ids' => $idsActualizados
        ]);
    }

    private function contabilizarGastosDeCaja($_oficinaPrincipalDTO)
    {
        $total = count($_oficinaPrincipalDTO->GastosSucursal ?? []);
        \Log::info("contabilizarGastosDeCaja iniciado", [
            'total_gastos' => $total
        ]);
        
        $this->contabilizarTransacciones($_oficinaPrincipalDTO->GastosSucursal ?? []);
        
        \Log::info("contabilizarGastosDeCaja completado");
    }

    private function contabilizarGastosSucursal($_oficinaPrincipalDTO)
    {
        $total = count($_oficinaPrincipalDTO->GastosSucursal ?? []);
        \Log::info("contabilizarGastosSucursal iniciado", [
            'total_gastos' => $total
        ]);
        
        $this->contabilizarTransacciones($_oficinaPrincipalDTO->GastosSucursal ?? []);
        
        \Log::info("contabilizarGastosSucursal completado");
    }

    private function contabilizarPagoServicios($_oficinaPrincipalDTO)
    {
        $pagos = $_oficinaPrincipalDTO->PagosServicios ?? [];
        $total = count($pagos);
        
        \Log::info("contabilizarPagoServicios iniciado", [
            'total_pagos_servicios' => $total
        ]);
        
        $this->contabilizarTransacciones($pagos);
        
        \Log::info("contabilizarPagoServicios completado");
    }

    private function contabilizarPagoFacturas($_oficinaPrincipalDTO)
    {        
        $pagos = $_oficinaPrincipalDTO->PagosFacturas ?? [];
        $total = count($pagos);
        
        \Log::info("contabilizarPagoFacturas iniciado", [
            'total_pagos_facturas' => $total
        ]);
        
        $this->contabilizarTransacciones($pagos);
        
        \Log::info("contabilizarPagoFacturas completado");
    }

    private function contabilizarPrestamos($_oficinaPrincipalDTO)
    {
        \Log::info("contabilizarPrestamos iniciado (comentado en .NET)");
        
        // $prestamos = $fechaAyerData['prestamos'] ?? [];
        // $contador = 0;
        
        // foreach ($prestamos as $prestamo) {
        //     $id = $prestamo->Id ?? $prestamo['Id'] ?? null;
        //     if ($id) {
        //         // Actualizar el estatus del préstamo
        //         DB::table('Prestamos')
        //             ->where('ID', $id)
        //             ->update(['Estatus' => 3]); // Ajusta el valor según tu EnumPrestamo.Cerrada
        //         $contador++;
                
        //         \Log::info("Préstamo actualizado", [
        //             'PrestamoId' => $id
        //         ]);
        //     }
        // }
        
        \Log::info("contabilizarPrestamos completado (sin cambios)");
    }

    // private function contabilizarVentas($_oficinaPrincipalDTO)
    // {
    //     $ventas = $fechaAyerData['ventas'] ?? [];
    //     $totalVentas = count($ventas);
        
    //     \Log::info("=== INICIO contabilizarVentas ===", [
    //         'total_ventas' => $totalVentas
    //     ]);
        
    //     $contador = 0;
    //     foreach ($ventas as $venta) {
    //         $contador++;
    //         $ventaId = $venta->id ?? $venta['id'] ?? 'N/A';
    //         $sucursalId = $venta->sucursalId ?? $venta['sucursalId'] ?? null;
            
    //         \Log::info("Procesando venta {$contador}/{$totalVentas}", [
    //             'venta_id' => $ventaId,
    //             'sucursal_id' => $sucursalId
    //         ]);
            
    //         // 1. Cambiar estatus de la venta a Cerrada (3)
    //         $this->cambiarEstatusVenta($venta);
            
    //         // 2. Cerrar recepciones de la sucursal
    //         if ($sucursalId) {
    //             $this->cerrarRecepciones($sucursalId, $fechaAyerData['fecha']);
    //         } else {
    //             \Log::warning("Venta sin sucursalId", ['venta_id' => $ventaId]);
    //         }
    //     }
        
    //     \Log::info("=== FIN contabilizarVentas ===");
    // }

    private function contabilizarVentas($_oficinaPrincipalDTO)
    {
        \Log::info("=== INICIO contabilizarVentas ===");
        
        // Validar que existan los datos (como en .NET)
        if (!$_oficinaPrincipalDTO || 
            !isset($_oficinaPrincipalDTO->VentasDiariaPeriodo['ListaVentasDiarias']) || 
            empty($_oficinaPrincipalDTO->VentasDiariaPeriodo['ListaVentasDiarias'])) {
            
            \Log::warning("No hay ventas para contabilizar");
            return;
        }
        
        $ventas = $_oficinaPrincipalDTO->VentasDiariaPeriodo['ListaVentasDiarias'];
        $totalVentas = count($ventas);
        
        \Log::info("Total ventas a procesar", ['total' => $totalVentas]);
        
        $contador = 0;
        foreach ($ventas as $venta) {
            $contador++;
            $ventaId = $venta->id ?? 'N/A';
            $sucursalId = $venta->sucursalId ?? null;
            
            \Log::info("Procesando venta {$contador}/{$totalVentas}", [
                'venta_id' => $ventaId,
                'sucursal_id' => $sucursalId
            ]);
            
            // 1. Cambiar estatus de la venta a Cerrada (3)
            $this->cambiarEstatusVenta($venta);
            
            // 2. Crear filtro de fecha (como en .NET)
            $filtroFecha = new ParametrosFiltroFecha(
                null, null, null, false,
                Carbon::parse($venta->fecha)->startOfDay(),
                Carbon::parse($venta->fecha)->endOfDay()
            );
            
            // 3. Cerrar recepciones de la sucursal
            if ($sucursalId) {
                $this->cerrarRecepciones($sucursalId, $venta->fecha);
            } else {
                \Log::warning("Venta sin sucursalId", ['venta_id' => $ventaId]);
            }
        }
        
        \Log::info("=== FIN contabilizarVentas ===", [
            'total_procesadas' => $contador
        ]);
    }

    private function cambiarEstatusVenta($venta)
    {
        $id = $venta->id ?? $venta['id'] ?? null;
        
        if ($id) {
            DB::table('Ventas')
                ->where('ID', $id)
                ->update(['Estatus' => 4]); // 4 = Cerrada
            
            \Log::info("Venta actualizada", [
                'id' => $id,
                'estatus' => 3
            ]);
        } else {
            \Log::warning("Intento de actualizar venta sin ID");
        }
    }

    private function cerrarRecepciones($sucursalId, $fecha)
    {
        \Log::info("--- INICIO cerrarRecepciones ---", [
            'sucursal_id' => $sucursalId,
            'fecha' => $fecha
        ]);
        
        if (!$sucursalId || !$fecha) {
            \Log::warning("cerrarRecepciones llamado sin datos", [
                'sucursal_id' => $sucursalId,
                'fecha' => $fecha
            ]);
            return;
        }
        
        // Crear fechas basadas en el parámetro $fecha
        $fechaInicio = Carbon::parse($fecha)->startOfDay();
        $fechaFin = Carbon::parse($fecha)->endOfDay();

        $filtroFecha = new ParametrosFiltroFecha(
            null, null, null, false,
            $fechaInicio, $fechaFin
        );
        
        // #region Transferencias de la sucursal
        \Log::info("Buscando transferencias para sucursal {$sucursalId}");
        $transferencias = $this->buscarTransferenciasParaCerrar($sucursalId, $filtroFecha);
        \Log::info("Transferencias encontradas", ['cantidad' => count($transferencias)]);
        
        if (!empty($transferencias)) {
            $transferenciasOrdenadas = collect($transferencias)->sortBy('Fecha')->values();
            $transfCount = 0;
            
            foreach ($transferenciasOrdenadas as $transferencia) {
                $transfCount++;
                \Log::info("Procesando transferencia {$transfCount}/" . count($transferencias), [
                    'transferencia_id' => $transferencia->TransferenciaId,
                    'fecha' => $transferencia->Fecha,
                    'saldo' => $transferencia->Saldo
                ]);
                
                $this->abonarDeudaSucursalTransferencia($sucursalId, $transferencia, $filtroFecha);
            }
        }
        // #endregion
        
        // Buscar ventas diarias para cerrar
        \Log::info("Buscando ventas diarias para sucursal {$sucursalId}");
        $ventasService = new VentasService();
        $ventasPeriodo = $ventasService->obtenerListadoVentasDiariasParaCerrarSinTotalizar($filtroFecha, $sucursalId, true);
        
        $cantidadVentas = isset($ventasPeriodo['ListaVentasDiarias']) ? count($ventasPeriodo['ListaVentasDiarias']) : 0;
        \Log::info("Ventas diarias encontradas", ['cantidad' => $cantidadVentas]);
        
        if (!empty($ventasPeriodo['ListaVentasDiarias'])) {
            $ventasOrdenadas = collect($ventasPeriodo['ListaVentasDiarias'])->sortBy('Fecha')->values();
            $ventaCount = 0;
            
            foreach ($ventasOrdenadas as $venta) {
                $ventaCount++;
                \Log::info("Procesando venta diaria {$ventaCount}/{$cantidadVentas}", [
                    'venta_id' => $venta->id ?? 'N/A',
                    'fecha' => $venta->fecha ?? 'N/A'
                ]);
                
                $this->abonarDeudaSucursalVenta($sucursalId, $venta, $filtroFecha);
            }
        }
        
        \Log::info("--- FIN cerrarRecepciones para sucursal {$sucursalId} ---");
    }

    private function buscarTransferenciasParaCerrar($sucursalId, $filtroFecha)
    {
        \Log::info("Buscando transferencias para cerrar", [
            'sucursal_id' => $sucursalId,
            'fecha_fin' => $filtroFecha->fechaFin ?? 'N/A'
        ]);
        
        try {
            $fechaFin = $filtroFecha->fechaFin ?? $filtroFecha['fecha_fin'] ?? now();
            
            $transferencias = DB::table('Transferencias')
                ->where('SucursalOrigenId', $sucursalId)
                ->where('Fecha', '<=', $fechaFin)
                ->where('Saldo', '>', 0)
                ->orderBy('Fecha')
                ->get();
            
            \Log::info("Transferencias encontradas", ['cantidad' => $transferencias->count()]);
            
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

    private function abonarDeudaSucursalTransferencia($sucursalId, $transferencia, $filtroFecha)
    {
        \Log::info(">>> INICIO abonarDeudaSucursalTransferencia (PRODUCCIÓN)");
        \Log::info("Sucursal: $sucursalId, Transferencia ID: " . ($transferencia->TransferenciaId ?? 'N/A') . ", Saldo: " . ($transferencia->Saldo ?? 0));

        // 1. Generar transacción de abono
        $nuevoAbono = $this->generarTransaccionAbonoTransferencia($transferencia);
        $nuevoAbono['Observacion'] = "TRANSFERENCIA {$transferencia->TransferenciaId} FECHA: " . Carbon::parse($transferencia->Fecha)->format('Y-m-d');

        $esCerrarOperacion = false;
        $operacionId = 0;
        $montoTransferenciaDivisa = $transferencia->Saldo ?? 0;

        // 2. Buscar recepciones
        $listaRecepciones = $this->buscarRecepcionesSucursalParaCerrar($sucursalId, $filtroFecha);

        if (!empty($listaRecepciones)) {
            $recepcionesFiltradas = collect($listaRecepciones)
                ->filter(function($r) use ($filtroFecha) {
                    return $r->FechaCreacion <= $filtroFecha->fechaFin;
                })
                ->sortBy('FechaCreacion')
                ->values();

            foreach ($recepcionesFiltradas as $recepcion) {
                if ($recepcion->SaldoDivisa > 0) {
                    // Asignar descripción específica
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

                        \Log::info("Saldo de transferencia actualizado", [
                            'transferencia_id' => $transferencia->TransferenciaId,
                            'nuevo_saldo' => $montoTransferenciaDivisa
                        ]);

                        if ($montoTransferenciaDivisa <= 0) {
                            \Log::info("Pago completado, saliendo del ciclo");
                            return;
                        }

                    } catch (\Exception $e) {
                        \Log::error("Error al guardar abono: " . $e->getMessage());
                        throw $e;
                    }
                }
            }
        }

        \Log::info("<<< FIN abonarDeudaSucursalTransferencia (monto restante: $montoTransferenciaDivisa)");
    }

    private function generarTransaccionAbonoTransferencia($transferencia)
    {
        return [
            'Cedula' => null,
            'DivisaId' => null,
            'Estatus' => 2, // Pagada
            'Fecha' => $transferencia->Fecha,
            'FormaDePago' => 0, // Efectivo
            'MontoAbonado' => 0,
            'MontoDivisaAbonado' => $transferencia->Saldo,
            'Nombre' => null,
            'Descripcion' => 'ABONO POR TRANSFERENCIA',
            'NumeroOperacion' => 'ABT' . Carbon::parse($transferencia->Fecha)->format('Ymd') . '-' . $transferencia->TransferenciaId,
            'Observacion' => '',
            'SucursalId' => $transferencia->SucursalOrigenId,
            'SucursalOrigenId' => $transferencia->SucursalOrigenId,
            'TasaDeCambio' => 0,
            'Tipo' => 7, // AbonoDeuda
            'UrlComprobante' => null
        ];
    }

    private function abonarDeudaSucursalVenta($sucursalId, $venta, $filtroFecha)
    {
        \Log::info(">>> INICIO abonarDeudaSucursalVenta (PRODUCCIÓN)");
        \Log::info("Sucursal: $sucursalId, Venta ID: " . ($venta->id ?? 'N/A') . ", TotalDivisa: " . ($venta->totalDivisa ?? 0));

        // 1. Generar transacción de abono
        $nuevoAbono = $this->generarTransaccionAbonoVenta($venta);

        $esCerrarOperacion = false;
        $operacionId = 0;
        $montoVentaDivisa = $venta->totalDivisa ?? 0;

        // 2. Buscar recepciones
        $listaRecepciones = $this->buscarRecepcionesSucursalParaCerrar($sucursalId, $filtroFecha);

        if (!empty($listaRecepciones)) {
            $recepcionesFiltradas = collect($listaRecepciones)
                ->filter(function($r) use ($filtroFecha) {
                    return $r->FechaCreacion <= $filtroFecha->fechaFin;
                })
                ->sortBy('FechaCreacion')
                ->values();

            foreach ($recepcionesFiltradas as $recepcion) {
                if ($recepcion->SaldoDivisa > 0) {
                    $nuevoAbono['Descripcion'] = "ABONO POR VENTA - {$recepcion->Numero}";

                    $resultado = $this->asignarMontoAbono(
                        $nuevoAbono,
                        $montoVentaDivisa,
                        $recepcion
                    );

                    $montoVentaDivisa = $resultado['monto_restante'];
                    $esCerrarOperacion = $resultado['es_cerrar'];
                    $operacionId = $recepcion->RecepcionId;
                    $nuevoAbono = $resultado['nuevo_abono'];

                    try {
                        // Guardar abono en recepción
                        $nuevoAbono = $this->guardarAbonoRecepcion(
                            $sucursalId,
                            $nuevoAbono,
                            $esCerrarOperacion,
                            $operacionId
                        );

                        if ($montoVentaDivisa <= 0) {
                            \Log::info("Venta pagada completamente, saliendo del ciclo de recepciones");
                            return;
                        }

                    } catch (\Exception $e) {
                        \Log::error("Error al guardar abono en recepción: " . $e->getMessage());
                        throw $e;
                    }
                }
            }
        }

        // 3. Buscar gastos (si quedó saldo)
        if ($montoVentaDivisa > 0) {
            \Log::info("Buscando gastos para sucursal {$sucursalId} (saldo restante: $montoVentaDivisa)");

            $listaGastos = $this->buscarGastosParaCerrar($sucursalId, $filtroFecha);

            if (!empty($listaGastos)) {
                $gastosFiltrados = collect($listaGastos)
                    ->filter(function($g) use ($filtroFecha) {
                        return $g->Fecha <= $filtroFecha->fechaFin;
                    })
                    ->sortBy('Fecha')
                    ->values();

                foreach ($gastosFiltrados as $gasto) {
                    if ($gasto->SaldoDivisa > 0 && $montoVentaDivisa > 0) {
                        $nuevoAbono['Descripcion'] = "ABONO POR VENTA - G{$gasto->NumeroOperacion}";

                        // Asignar monto (misma lógica que en recepciones)
                        if ($gasto->SaldoDivisa <= $montoVentaDivisa) {
                            $montoVentaDivisa -= $gasto->SaldoDivisa;
                            $nuevoAbono['MontoDivisaAbonado'] = $gasto->SaldoDivisa;
                            $esCerrarOperacion = true;
                        } else {
                            $nuevoAbono['MontoDivisaAbonado'] = $montoVentaDivisa;
                            $montoVentaDivisa = 0;
                            $esCerrarOperacion = false;
                        }

                        try {
                            // Guardar abono en gasto (necesitamos guardarAbonoGasto)
                            $nuevoAbono = $this->guardarAbonoGasto(
                                $sucursalId,
                                $nuevoAbono,
                                $esCerrarOperacion,
                                $gasto->Id
                            );

                            if ($montoVentaDivisa <= 0) {
                                \Log::info("Venta pagada completamente, saliendo del ciclo de gastos");
                                return;
                            }

                        } catch (\Exception $e) {
                            \Log::error("Error al guardar abono en gasto: " . $e->getMessage());
                            throw $e;
                        }
                    }
                }
            } else {
                \Log::info("No hay gastos para procesar.");
            }
        }

        \Log::info("<<< FIN abonarDeudaSucursalVenta (monto restante: $montoVentaDivisa)");
    }

    private function guardarAbonoGasto($sucursalId, $nuevoAbono, $esCerrarOperacion, $gastoId)
    {
        \Log::info("Guardando abono de gasto", [
            'sucursal_id' => $sucursalId,
            'gasto_id' => $gastoId,
            'monto' => $nuevoAbono['MontoDivisaAbonado'],
            'cerrar_operacion' => $esCerrarOperacion
        ]);

        // 1. Insertar transacción (igual que en guardarAbonoRecepcion)
        $transaccionId = DB::table('Transacciones')->insertGetId([
            'Cedula' => $nuevoAbono['Cedula'] ?? null,
            'DivisaId' => $nuevoAbono['DivisaId'] ?? null,
            'Estatus' => $nuevoAbono['Estatus'] ?? 2,
            'Fecha' => $nuevoAbono['Fecha'] ?? now(),
            'FormaDePago' => $nuevoAbono['FormaDePago'] ?? 0,
            'MontoAbonado' => $nuevoAbono['MontoAbonado'] ?? 0,
            'MontoDivisaAbonado' => $nuevoAbono['MontoDivisaAbonado'],
            'Nombre' => $nuevoAbono['Nombre'] ?? null,
            'Descripcion' => $nuevoAbono['Descripcion'] ?? '',
            'NumeroOperacion' => $nuevoAbono['NumeroOperacion'] ?? '',
            'Observacion' => $nuevoAbono['Observacion'] ?? '',
            'SucursalId' => $nuevoAbono['SucursalId'] ?? $sucursalId,
            'SucursalOrigenId' => $nuevoAbono['SucursalOrigenId'] ?? $sucursalId,
            'TasaDeCambio' => $nuevoAbono['TasaDeCambio'] ?? 0,
            'Tipo' => $nuevoAbono['Tipo'] ?? 7,
            'UrlComprobante' => $nuevoAbono['UrlComprobante'] ?? null
        ]);

        \Log::info("Transacción insertada", ['transaccion_id' => $transaccionId]);

        // 2. Insertar relación TransaccionesGastos
        DB::table('TransaccionesGastos')->insert([
            'GastoId' => $gastoId,
            'TransaccionId' => $transaccionId
        ]);

        \Log::info("Relación TransaccionesGastos insertada");

        // 3. Si corresponde, actualizar estatus del gasto
        if ($esCerrarOperacion) {
            DB::table('Transacciones')
                ->where('ID', $gastoId)
                ->update(['Estatus' => 5]); // PagadaAbono (según .NET)

            \Log::info("Gasto actualizado a PagadaAbono", ['gasto_id' => $gastoId]);
        }

        $nuevoAbono['Id'] = $transaccionId;

        \Log::info("Abono guardado exitosamente");

        return $nuevoAbono;
    }

    private function generarTransaccionAbonoVenta($venta)
    {
        return [
            'Cedula' => null,
            'DivisaId' => null,
            'Estatus' => 2, // Pagada
            'Fecha' => $venta->fecha,
            'FormaDePago' => 0, // Efectivo
            'MontoAbonado' => 0,
            'MontoDivisaAbonado' => $venta->totalDivisa ?? 0,
            'Nombre' => null,
            'Descripcion' => 'ABONO POR VENTA',
            'NumeroOperacion' => 'ABV' . Carbon::parse($venta->fecha)->format('Ymd') . '-' . $venta->id,
            'Observacion' => "VENTA {$venta->id} FECHA: " . Carbon::parse($venta->fecha)->format('Y-m-d'),
            'SucursalId' => $venta->sucursalId,
            'SucursalOrigenId' => $venta->sucursalId,
            'TasaDeCambio' => $venta->tasaDeCambio ?? 0,
            'Tipo' => 7, // AbonoDeuda
            'UrlComprobante' => null
        ];
    }

    private function abonarDeudaSucursal($sucursalId, $item, $filtroFecha)
    {
        $tipo = property_exists($item, 'TransferenciaId') ? 'transferencia' : 'venta';
        
        \Log::info(">>> INICIO abonarDeudaSucursal", [
            'sucursal_id' => $sucursalId,
            'tipo' => $tipo,
            'id' => $item->TransferenciaId ?? $item->id ?? 'N/A',
            'saldo_inicial' => $item->Saldo ?? $item->totalDivisa ?? 0
        ]);
        
        // Generar transacción de abono
        $nuevoAbono = $this->generarTransaccionAbono($item);
        
        $esCerrarOperacion = false;
        $operacionId = 0;
        $montoTransferenciaDivisa = $item->Saldo ?? $item->totalDivisa ?? 0;
        
        // 🔴 CORREGIDO: Usar $item en lugar de $transferencia
        if ($tipo === 'transferencia') {
            $nuevoAbono['Observacion'] = "TRANSFERENCIA {$item->TransferenciaId} FECHA: " . 
                                        Carbon::parse($item->Fecha)->format('Y-m-d');
        } else {
            $nuevoAbono['Observacion'] = "VENTA {$item->id} FECHA: " . 
                                        Carbon::parse($item->fecha)->format('Y-m-d');
        }
        
        \Log::info("Abono generado", [
            'monto' => $nuevoAbono['MontoDivisaAbonado'],
            'tipo' => $nuevoAbono['Tipo']
        ]);
        
        // Buscar recepciones de la sucursal para cerrar
        \Log::info("Buscando recepciones para cerrar");
        $listaRecepciones = $this->buscarRecepcionesSucursalParaCerrar($sucursalId, $filtroFecha);
        \Log::info("Recepciones encontradas", ['cantidad' => count($listaRecepciones)]);
        
        if (!empty($listaRecepciones)) {
            $recepcionesFiltradas = collect($listaRecepciones)
                ->filter(function($r) use ($filtroFecha) {
                    return $r->FechaCreacion <= $filtroFecha->fechaFin;
                })
                ->sortBy('FechaCreacion')
                ->values();
            
            \Log::info("Recepciones filtradas", ['cantidad' => count($recepcionesFiltradas)]);
            
            $recepcionCount = 0;
            foreach ($recepcionesFiltradas as $recepcion) {
                $recepcionCount++;
                \Log::info("Procesando recepción {$recepcionCount}/" . count($recepcionesFiltradas), [
                    'recepcion_id' => $recepcion->RecepcionId,
                    'saldo_divisa' => $recepcion->SaldoDivisa,
                    'monto_disponible' => $montoTransferenciaDivisa
                ]);
                
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
                    
                    \Log::info("Monto asignado", [
                        'abono_realizado' => $nuevoAbono['MontoDivisaAbonado'],
                        'monto_restante' => $montoTransferenciaDivisa,
                        'cerrar_operacion' => $esCerrarOperacion
                    ]);
                    
                    try {
                        // Guardar abono
                        $nuevoAbono = $this->guardarAbonoRecepcion(
                            $sucursalId, 
                            $nuevoAbono, 
                            $esCerrarOperacion, 
                            $operacionId
                        );
                        
                        // 🔴 CORREGIDO: Verificar si es transferencia antes de actualizar
                        if ($tipo === 'transferencia') {
                            DB::table('Transferencias')
                                ->where('TransferenciaId', $item->TransferenciaId)
                                ->update(['Saldo' => $montoTransferenciaDivisa]);
                            
                            \Log::info("Saldo de transferencia actualizado", [
                                'transferencia_id' => $item->TransferenciaId,
                                'nuevo_saldo' => $montoTransferenciaDivisa
                            ]);
                        }
                        
                        \Log::info("Abono guardado", [
                            'transaccion_id' => $nuevoAbono['Id'] ?? 'N/A'
                        ]);
                        
                        // Si se terminó el pago, salir
                        if ($montoTransferenciaDivisa <= 0) {
                            \Log::info("Pago completado, saliendo del ciclo");
                            return;
                        }
                        
                    } catch (\Exception $e) {
                        \Log::error("Error al guardar abono: " . $e->getMessage());
                        throw $e;
                    }
                }
            }
        }
        
        \Log::info("<<< FIN abonarDeudaSucursal", [
            'monto_final_restante' => $montoTransferenciaDivisa
        ]);
    }

    private function mapearTransferenciaADTO($transferencia)
    {
        \Log::info("Mapeando transferencia a DTO", [
            'transferencia_id' => $transferencia->id ?? 'N/A'
        ]);
        
        $detallesDTO = [];
        if (isset($transferencia->detalles) && $transferencia->detalles->isNotEmpty()) {
            foreach ($transferencia->detalles as $detalle) {
                $productoDTO = null;
                if (isset($detalle->producto)) {
                    $productoDTO = new ProductoDTO([
                        'ProductoId' => $detalle->producto->id,
                        'Descripcion' => $detalle->producto->Descripcion,
                        'CodigoBarras' => $detalle->producto->CodigoBarras,
                        'CostoDivisa' => $detalle->producto->CostoDivisa,
                        'CostoBs' => $detalle->producto->CostoBs,
                        'Referencia' => $detalle->producto->Referencia,
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
                    'Producto' => $productoDTO
                ]);
            }
        }
        
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
            'Detalles' => $detallesDTO,
            'CantidadItems' => count($detallesDTO)
        ]);
    }

    private function actualizarSaldoCierre($cierreOFP)
    {
        \Log::info("=== INICIO actualizarSaldoCierre ===", [
            'cierre_id' => $cierreOFP->CierreOfpId ?? 'nuevo',
            'fecha' => $cierreOFP->Fecha,
            'saldo_operacion_bs' => $cierreOFP->SaldoOperacionBs,
            'saldo_operacion_divisas' => $cierreOFP->SaldoOperacionDivisas
        ]);
        
        try {
            // #region Actualizar con el saldo anterior
            $cierreAnterior = CierreOfp::where('Fecha', '<', $cierreOFP->Fecha)
                ->orderBy('Fecha', 'desc')
                ->first();
            
            if ($cierreAnterior) {
                \Log::info("Cierre anterior encontrado", [
                    'cierre_anterior_id' => $cierreAnterior->CierreOfpId,
                    'saldo_anterior_bs' => $cierreAnterior->SaldoGeneralBs,
                    'saldo_anterior_divisas' => $cierreAnterior->SaldoGeneralDivisas
                ]);
                
                $cierreOFP->SaldoGeneralBs = $cierreAnterior->SaldoGeneralBs + $cierreOFP->SaldoOperacionBs;
                $cierreOFP->SaldoGeneralDivisas = $cierreAnterior->SaldoGeneralDivisas + $cierreOFP->SaldoOperacionDivisas;
            } else {
                \Log::info("No hay cierre anterior, es el primero");
                $cierreOFP->SaldoGeneralBs = $cierreOFP->SaldoOperacionBs;
                $cierreOFP->SaldoGeneralDivisas = $cierreOFP->SaldoOperacionDivisas;
            }
            
            \Log::info("Saldos calculados", [
                'nuevo_saldo_bs' => $cierreOFP->SaldoGeneralBs,
                'nuevo_saldo_divisas' => $cierreOFP->SaldoGeneralDivisas
            ]);
            
            // Guardar o actualizar el cierre actual
            if (!$cierreOFP->CierreOfpId) {
                $cierreOFP->save();
                \Log::info("Cierre guardado", ['nuevo_id' => $cierreOFP->CierreOfpId]);
            } else {
                $cierreOFP->update();
                \Log::info("Cierre actualizado");
            }
            // #endregion
            
            // #region Actualizar los saldos futuros
            $cierresSiguientes = CierreOfp::where('Fecha', '>', $cierreOFP->Fecha)
                ->orderBy('Fecha')
                ->get();
            
            if ($cierresSiguientes->isNotEmpty()) {
                \Log::info("Actualizando cierres futuros", [
                    'cantidad' => $cierresSiguientes->count()
                ]);
                
                $cierreAnterior = $cierreOFP;
                $contador = 0;
                
                foreach ($cierresSiguientes as $cierre) {
                    $contador++;
                    $cierre->SaldoGeneralBs = $cierreAnterior->SaldoGeneralBs + $cierre->SaldoOperacionBs;
                    $cierre->SaldoGeneralDivisas = $cierreAnterior->SaldoGeneralDivisas + $cierre->SaldoOperacionDivisas;
                    
                    $cierre->save();
                    
                    \Log::info("Cierre futuro {$contador} actualizado", [
                        'cierre_id' => $cierre->CierreOfpId,
                        'nuevo_saldo_bs' => $cierre->SaldoGeneralBs,
                        'nuevo_saldo_divisas' => $cierre->SaldoGeneralDivisas
                    ]);
                    
                    $cierreAnterior = $cierre;
                }
            } else {
                \Log::info("No hay cierres futuros para actualizar");
            }
            // #endregion
            
            \Log::info("=== FIN actualizarSaldoCierre OK ===");
            
        } catch (\Exception $e) {
            \Log::error("Error en actualizarSaldoCierre: " . $e->getMessage());
            throw $e;
        }
    }

    // private function generarTransaccionAbono($transferencia)
    // {
    //     return [
    //         'Cedula' => null,
    //         'DivisaId' => null,
    //         'Estatus' => 2,
    //         'Fecha' => $transferencia->Fecha,
    //         'FormaDePago' => 0,
    //         'MontoAbonado' => 0,
    //         'MontoDivisaAbonado' => $transferencia->Saldo,
    //         'Nombre' => null,
    //         'Descripcion' => 'ABONO DEUDA X TRANSFERENCIA',
    //         'NumeroOperacion' => 'ABT' . Carbon::parse($transferencia->Fecha)->format('Ymd') . '-' . $transferencia->TransferenciaId,
    //         'Observacion' => 'ABONO DEUDA X TRANSFERENCIA',
    //         'SucursalId' => $transferencia->SucursalOrigenId,
    //         'SucursalOrigenId' => $transferencia->SucursalOrigenId,
    //         'TasaDeCambio' => 0,
    //         'Tipo' => 7,
    //         'UrlComprobante' => null
    //     ];
    // }

    private function buscarGastosParaCerrar($sucursalId, $filtroFecha)
    {
        // Similar a buscarRecepciones, pero para gastos (tipo 2)
        return Transaccion::where('SucursalId', $sucursalId)
            ->where('Tipo', 2) // Tipo Gasto
            ->where('Fecha', '<=', $filtroFecha->fechaFin)
            ->where('Estatus', '!=', 4) // No contabilizados
            ->get();
    }

    private function generarTransaccionAbono($item)
    {
        // Determinar si es transferencia o venta
        $fecha = null;
        $transferenciaId = null;
        $sucursalOrigenId = null;
        $saldo = 0;
        
        if (property_exists($item, 'TransferenciaId')) {
            // Es una transferencia
            $fecha = $item->Fecha;
            $transferenciaId = $item->TransferenciaId;
            $sucursalOrigenId = $item->SucursalOrigenId;
            $saldo = $item->Saldo;
            $tipoOperacion = 'TRANSFERENCIA';
            $idParaNumero = $item->TransferenciaId;
        } else {
            // Es una venta
            $fecha = $item->fecha; // Nota: minúscula
            $transferenciaId = $item->id ?? 'VENTA';
            $sucursalOrigenId = $item->sucursalId ?? $item->sucursalIdOrigen ?? null;
            $saldo = $item->totalDivisa ?? $item->saldo ?? 0;
            $tipoOperacion = 'VENTA';
            $idParaNumero = $item->id ?? '0';
        }
        
        \Log::info("Generando transacción abono", [
            'tipo' => $tipoOperacion,
            'id' => $transferenciaId,
            'fecha' => $fecha,
            'monto' => $saldo
        ]);
        
        return [
            'Cedula' => null,
            'DivisaId' => null,
            'Estatus' => 2,
            'Fecha' => $fecha,
            'FormaDePago' => 0,
            'MontoAbonado' => 0,
            'MontoDivisaAbonado' => $saldo,
            'Nombre' => null,
            'Descripcion' => "ABONO DEUDA X {$tipoOperacion}",
            'NumeroOperacion' => "ABO" . Carbon::parse($fecha)->format('Ymd') . "-{$idParaNumero}",
            'Observacion' => "{$tipoOperacion} {$transferenciaId} FECHA: " . Carbon::parse($fecha)->format('Y-m-d'),
            'SucursalId' => $sucursalOrigenId,
            'SucursalOrigenId' => $sucursalOrigenId,
            'TasaDeCambio' => 0,
            'Tipo' => 7,
            'UrlComprobante' => null
        ];
    }

    private function buscarRecepcionesSucursalParaCerrar($sucursalId, $filtroFecha)
    {
        try {
            // DEBUG: Ver filtro de fecha
            \Log::info("DEBUG - Buscando recepciones para sucursal {$sucursalId}", [
                'fecha_fin' => $filtroFecha->fechaFin->toDateTimeString()
            ]);

            $recepciones = DB::table('Recepciones as r')
                ->leftJoin('RecepcionesDetalles as rd', 'r.RecepcionId', '=', 'rd.RecepcionId')
                ->leftJoin('TransaccionesRecepciones as tr', 'r.RecepcionId', '=', 'tr.RecepcionId')
                ->leftJoin('Transacciones as t', 'tr.TransaccionId', '=', 't.ID')
                ->where('r.SucursalDestinoId', $sucursalId)
                ->whereNotIn('r.Estatus', [7, 8])
                ->where('r.FechaCreacion', '<=', $filtroFecha->fechaFin)
                ->select(
                    'r.RecepcionId',
                    'r.Numero',
                    'r.SucursalDestinoId',
                    'r.FechaCreacion',
                    'r.Estatus',
                    'rd.CantidadRecibida',
                    'rd.CostoDivisa',
                    't.ID as TransaccionId',
                    't.MontoDivisaAbonado',
                    't.Fecha as TransaccionFecha'
                )
                ->orderBy('r.FechaCreacion')
                ->get();

            // DEBUG: Ver cuántas filas trajo la consulta
            \Log::info("DEBUG - Filas obtenidas: " . $recepciones->count());

            // Agrupar por recepción y calcular TotalRecepcionDivisas y abonos
            $recepcionesAgrupadas = [];
            foreach ($recepciones as $item) {
                $recepcionId = $item->RecepcionId;

                if (!isset($recepcionesAgrupadas[$recepcionId])) {
                    $recepcionesAgrupadas[$recepcionId] = [
                        'RecepcionId' => $item->RecepcionId,
                        'Numero' => $item->Numero,
                        'SucursalDestinoId' => $item->SucursalDestinoId,
                        'FechaCreacion' => $item->FechaCreacion,
                        'Estatus' => $item->Estatus,
                        'TotalRecepcionDivisas' => 0,
                        'AbonoVentas' => []
                    ];
                }

                // Sumar CostoDivisa * CantidadRecibida para TotalRecepcionDivisas
                if ($item->CantidadRecibida && $item->CostoDivisa) {
                    $recepcionesAgrupadas[$recepcionId]['TotalRecepcionDivisas'] += 
                        $item->CantidadRecibida * $item->CostoDivisa;
                }

                // Agregar abonos si existen
                if ($item->TransaccionId) {
                    $recepcionesAgrupadas[$recepcionId]['AbonoVentas'][] = [
                        'TransaccionId' => $item->TransaccionId,
                        'MontoDivisaAbonado' => $item->MontoDivisaAbonado,
                        'Fecha' => $item->TransaccionFecha
                    ];
                }
            }

            // DEBUG: Ver recepciones agrupadas
            \Log::info("DEBUG - Recepciones agrupadas: " . count($recepcionesAgrupadas));

            $resultado = [];
            foreach ($recepcionesAgrupadas as $id => $rec) {
                // Calcular TotalAbonadoDivisa
                $totalAbonado = collect($rec['AbonoVentas'])->sum('MontoDivisaAbonado');

                // Calcular SaldoDivisa (como en .NET)
                $saldoDivisa = $rec['TotalRecepcionDivisas'] - $totalAbonado;

                \Log::info("   → Recepción ID: {$id}, TotalRecepcionDivisas: {$rec['TotalRecepcionDivisas']}, TotalAbonado: {$totalAbonado}, SaldoDivisa: {$saldoDivisa}");

                if ($saldoDivisa > 0) {
                    $rec['SaldoDivisa'] = $saldoDivisa;
                    $resultado[] = (object)$rec;
                }
            }

            \Log::info("DEBUG - Recepciones con saldo > 0: " . count($resultado));

            return $resultado;

        } catch (\Exception $e) {
            \Log::error("Error en buscarRecepcionesSucursalParaCerrar: " . $e->getMessage());
            return [];
        }
    }

    private function asignarMontoAbono(&$nuevoAbono, $montoTransferenciaDivisa, $recepcion)
    {
        $operacionId = $recepcion->RecepcionId;
        
        \Log::info("Asignando monto de abono", [
            'recepcion_id' => $operacionId,
            'saldo_recepcion' => $recepcion->SaldoDivisa,
            'monto_disponible' => $montoTransferenciaDivisa
        ]);
        
        if ($recepcion->SaldoDivisa > 0 && $recepcion->SaldoDivisa <= $montoTransferenciaDivisa) {
            $montoTransferenciaDivisa -= $recepcion->SaldoDivisa;
            $nuevoAbono['MontoDivisaAbonado'] = $recepcion->SaldoDivisa;
            $esCerrarOperacion = true;
            \Log::info("Caso 1: Abono completo", [
                'abono' => $recepcion->SaldoDivisa,
                'restante' => $montoTransferenciaDivisa
            ]);
        } else {
            $nuevoAbono['MontoDivisaAbonado'] = $montoTransferenciaDivisa;
            $montoTransferenciaDivisa = 0;
            $esCerrarOperacion = false;
            \Log::info("Caso 2: Abono parcial", [
                'abono' => $nuevoAbono['MontoDivisaAbonado'],
                'restante' => 0
            ]);
        }
        
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
        \Log::info("Guardando abono de recepción", [
            'sucursal_id' => $sucursalId,
            'operacion_id' => $operacionId,
            'monto' => $nuevoAbono['MontoDivisaAbonado'],
            'cerrar_operacion' => $esCerrarOperacion
        ]);
        
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
            'UrlComprobante' => $nuevoAbono['UrlComprobante']
        ]);
        
        \Log::info("Transacción insertada", ['transaccion_id' => $transaccionId]);
        
        DB::table('TransaccionesRecepciones')->insert([
            'RecepcionId' => $operacionId,
            'TransaccionId' => $transaccionId,
            'SucursalId' => $sucursalId
        ]);
        
        \Log::info("Relación TransaccionesRecepciones insertada");
        
        if ($esCerrarOperacion) {
            $recepcion = DB::table('Recepciones')
                ->where('RecepcionId', $operacionId)
                ->first();
            
            $estatusAnterior = $recepcion->Estatus;
            if ($recepcion->Estatus == 6) {
                $nuevoEstatus = 8;
            } else {
                $nuevoEstatus = 7;
            }
            
            DB::table('Recepciones')
                ->where('RecepcionId', $operacionId)
                ->update(['Estatus' => $nuevoEstatus]);
            
            \Log::info("Recepción actualizada", [
                'estatus_anterior' => $estatusAnterior,
                'nuevo_estatus' => $nuevoEstatus
            ]);
        }
        
        $nuevoAbono['Id'] = $transaccionId;
        
        \Log::info("Abono guardado exitosamente");
        
        return $nuevoAbono;
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

    private function procesarCierresDiarios($ventas, $cierresDiarios)
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

        // PASO 1: Crear un mapa de cierres por sucursal para búsqueda rápida
        $cierresPorSucursal = [];
        foreach ($cierresDiarios as $cierre) {
            $cierresPorSucursal[$cierre->SucursalId] = $cierre;
        }
        
        foreach ($ventas as $venta) {
            $sucursalId = $venta->sucursalId;            

            if (isset($cierresPorSucursal[$sucursalId])) {
                $cierre = $cierresPorSucursal[$sucursalId];
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
                $egresosBs = floatval($cierre->EgresoBs ?? 0);
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