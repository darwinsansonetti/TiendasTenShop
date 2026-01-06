<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Sucursal;
use App\Models\Divisa;
use App\Models\DivisaValor;
use App\Models\Mensaje;
use App\Models\Producto;

use App\Helpers\ParametrosFiltroFecha;

use App\Models\VentaDiariaTotalizada;
use App\Models\VentaVendedoresTotalizada;
use App\Models\Usuario;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use App\Services\VentasService;

use App\DTO\ComparacionSucursalesDTO;
use App\DTO\ComparacionSucursalesDetalleDTO;

use App\DTO\IndiceDeRotacionDTO;
use App\DTO\IndiceDeRotacionDetallesDTO;

use App\DTO\ComparacionSinVentaDTO;
use App\DTO\ComparacionSinVentaDetallesDTO;
use App\DTO\ProductoDTO;


class GeneralHelper
{
    // Obtener Tasa del Dia
    public static function obtenerTasaCambioDiaria($fecha)
    {
        $fecha = Carbon::parse($fecha)->format('Y-m-d');

        $IDDolar = 1; // Ajusta si usas otra ID

        $divisa = Divisa::find($IDDolar);

        if (!$divisa) {
            return null;
        }

        $divisaDTO = [
            'EsActiva' => true,
            'EsPrincipal' => true,
            'Nombre' => 'Dolar',
            'Simbolo' => '$',
            'DivisaValor' => null
        ];

        $valor = DivisaValor::whereDate('Fecha', $fecha)->first();

        if ($valor) {
            $divisaDTO['DivisaValor'] = [
                'Id' => $valor->Id,
                'Fecha' => $valor->Fecha,
                'Valor' => $valor->Valor
            ];
        }

        return $divisaDTO;
    }

    // Obtener último Mensaje
    public static function ultimoMensaje()
    {
        $mensaje = Mensaje::orderByDesc('Fecha')
                        ->orderByDesc('MensajeId') // Por si hay varios en la misma fecha
                        ->first();

        if ($mensaje) {
            return $mensaje->Mensaje;
        }

        // Mensaje por defecto
        return '¡Bienvenido a Tiendas TenShop! | Descubre nuestras ofertas y promociones especiales hoy mismo.';
    }

    // Obtener todas las sucursales
    public static  function buscarSucursales($tipoSucursal)
    {
        $query = Sucursal::orderBy('Nombre');

        // 0 = Todas (igual que EnumTipoSucursal.Todas)
        if ($tipoSucursal !== 0) {
            $query->where('Tipo', $tipoSucursal);
        }

        return $query->get();
    }

    // Obtener productos por Estatus
    public static  function obtenerProductos($estatus)
    {
        $query = Producto::orderByDesc('ID');

        // Si se envia el estatus
        if ($estatus == 0 || $estatus == 1) {
            $query->where('Estatus', $estatus);
        }

        return $query->get();
    }

    // Obtener Ranking de Sucursales segun la tabla VentasDiariasTotalizadas
    public static function ObtenerRankingSucursales(ParametrosFiltroFecha $filtroFecha): Collection
    {
        $ventas = VentaDiariaTotalizada::with('sucursal')
            ->whereBetween('Fecha', [$filtroFecha->fechaInicio, $filtroFecha->fechaFin])
            ->get();

        // Agrupar por sucursal y calcular totales
        $ranking = $ventas->groupBy('SucursalId')
            ->map(function ($ventasSucursal) {
                $sucursal = $ventasSucursal->first()->sucursal;

                return (object)[
                    'Sucursal' => $sucursal->Nombre,
                    'Unidades' => $ventasSucursal->sum('Cantidad'),
                    'Volumen' => $ventasSucursal->sum('TotalBs'),
                ];
            })
            ->sortByDesc('Volumen')
            ->values();

        // Calcular el total de volumen para el porcentaje
        $totalVolumen = $ranking->sum('Volumen');

        // Agregar porcentaje a cada sucursal
        $ranking->transform(function ($item) use ($totalVolumen) {
            $item->PorcentajeVolumen = $totalVolumen > 0 
                ? round(($item->Volumen / $totalVolumen) * 100, 2) 
                : 0;
            return $item;
        });

        return $ranking;
    }

    // Obtener valores a mostrar en la Grafica
    public static function ObtenerGraficaSucursales()
    {
        try {
            // Últimos 7 meses
            $fechaInicio = now()->startOfMonth()->subMonths(6);
            $fechaFin = now()->endOfMonth();

            // Query corregida para SQL Server
            $ventas = VentaDiariaTotalizada::query()
                ->with('sucursal')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->selectRaw("
                    SucursalId,
                    FORMAT(Fecha, 'yyyy-MM') as Mes,
                    SUM(Cantidad) as TotalCantidad
                ")
                ->groupBy('SucursalId', \DB::raw("FORMAT(Fecha, 'yyyy-MM')"))
                ->orderBy(\DB::raw("FORMAT(Fecha, 'yyyy-MM')"))
                ->orderBy('SucursalId')
                ->get();

            $meses = $ventas->pluck('Mes')->unique()->values();

            $series = [];
            $agrupado = $ventas->groupBy('SucursalId');

            foreach ($agrupado as $sucursalId => $items) {
                $nombreSucursal = $items->first()->sucursal->Nombre ?? "Sucursal $sucursalId";

                $data = $meses->map(function ($mes) use ($items) {
                    return $items->firstWhere('Mes', $mes)->TotalCantidad ?? 0;
                });

                $series[] = [
                    'name' => $nombreSucursal,
                    'data' => $data,
                ];
            }

            return [
                'categories' => $meses,
                'series' => $series,
            ];

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    // Obtener el ranking de tiendas segun su venta en dolares en un mes
    public static function ObtenerProduccionSucursales($filtroMes = null)
    {
        try {

            // Si NO viene mes desde la vista → usar mes actual
            if (!$filtroMes) {
                $mes = now()->month;
                $anio = now()->year;
            } else {
                $mes = $filtroMes['mes'];
                $anio = $filtroMes['anio'];
            }

            // Obtener rango del mes seleccionado
            $fechaInicio = Carbon::create($anio, $mes, 1)->startOfDay();
            $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth()->startOfDay();

            // Consultar producción del mes agrupada por sucursal
            $produccion = VentaDiariaTotalizada::query()
                ->with('sucursal')
                ->whereBetween('Fecha', [$fechaInicio, $fechaFin])
                ->selectRaw("
                    SucursalId,
                    SUM(TotalDivisa) as TotalProduccion
                ")
                ->groupBy('SucursalId')
                ->orderByDesc('TotalProduccion')
                ->get();

            // Formato para retornar a la vista
            $ranking = $produccion->map(function ($item) {
                return [
                    'sucursal' => $item->sucursal->Nombre ?? "Sucursal {$item->SucursalId}",
                    'produccion' => round($item->TotalProduccion, 2)
                ];
            });

            return $ranking;

        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Obtener ranking de vendedores en un rango de fechas
     *
     * @param ParametrosFiltroFecha $filtroFecha
     * @param string|null $usuarioId
     * @param int|null $sucursalId
     * @return Collection
     */
    public static function ObtenerRankingVendedores(ParametrosFiltroFecha $filtroFecha, $usuarioId = null, $sucursalId = null): Collection
    {
        // Obtener totales de ventas por usuario y sucursal
        $ranking = VentaVendedoresTotalizada::query()
            ->selectRaw('UsuarioId, SucursalId, SUM(Cantidad) as total_unidades, SUM(TotalDivisa) as total_ventas')
            ->whereBetween('Fecha', [$filtroFecha->fechaInicio, $filtroFecha->fechaFin])
            ->when($usuarioId, fn($q) => $q->where('UsuarioId', $usuarioId))
            ->when($sucursalId, fn($q) => $q->where('SucursalId', $sucursalId))
            ->groupBy('UsuarioId', 'SucursalId')
            ->orderByDesc('total_unidades')
            ->get();

        // Cargar información de Usuario y Sucursal
        $ranking->transform(function ($item, $index) {
            // Datos del usuario activo
            $usuario = Usuario::where('UsuarioId', $item->UsuarioId)
                ->where('EsActivo', 1)
                ->first();

            $item->Vendedor = $usuario ? [
                'UsuarioId' => $usuario->UsuarioId,
                'NombreCompleto' => $usuario->NombreCompleto,
                'VendedorId' => $usuario->VendedorId,
                'SucursalId' => $usuario->SucursalId,
                'FotoPerfil' => $usuario->FotoPerfil
            ] : null;

            // Nombre de la sucursal
            if ($item->SucursalId) {
                $sucursal = Sucursal::find($item->SucursalId);
                $item->SucursalNombre = $sucursal ? $sucursal->Nombre : null;
            }

            // Ranking numérico
            $item->ranking = $index + 1;

            return $item;
        });

        return $ranking;
    }

    // Obtener informacion a mostrar en Resumen de ventas
    public static function generarDatosVentas(ParametrosFiltroFecha $filtro)
    {
        // Rango de fechas manual para pruebas
        // $fechaInicio = Carbon::parse('2025-11-01')->startOfDay();
        // $fechaFin    = Carbon::parse('2025-12-08')->startOfDay();

        $fechaInicio = $filtro->fechaInicio->startOfDay(); 
        $fechaFin = $filtro->fechaFin->startOfDay();

        // 1️⃣ IDs de ventas dentro del rango
        $queryVentas = DB::table('Ventas')
            ->whereBetween('FECHA', [$fechaInicio, $fechaFin]);

        // Filtrar por sucursal si no es "Todas"
        if (session('sucursal_id') != 0) {
            $queryVentas->where('SucursalId', session('sucursal_id'));
        }

        $ventasIds = $queryVentas->pluck('ID');

        $ventasDiariasQuery = DB::table('VentaDiariaTotalizada')
                                ->select(
                                    'FECHA',
                                    DB::raw('SUM(Cantidad) as CantidadItems'),
                                    DB::raw('SUM(CostoDivisa) as CostoDivisa'),
                                    DB::raw('SUM(TotalBs) as TotalBs'),
                                    DB::raw('SUM(TotalDivisa) as TotalDivisa'),
                                    'TasaDeCambio'
                                )
                                ->whereBetween('FECHA', [$fechaInicio, $fechaFin]);

        if (session('sucursal_id') != 0) {
            $ventasDiariasQuery->where('SucursalId', session('sucursal_id'));
        }

        $ventasDiarias = $ventasDiariasQuery->groupBy('FECHA', 'TasaDeCambio')
                        ->get()
                        ->map(function ($venta) {
                            $venta->fecha_formateada = Carbon::parse($venta->FECHA)->format('Y-m-d');
                            return $venta;
                        });

        $detallesQuery = DB::table('VentaProductosView as vpv')
            ->join('Productos as p', 'vpv.ProductoId', '=', 'p.ID')
            ->whereIn('vpv.VentaId', $ventasIds)
            ->select(
                'vpv.VentaId',
                'vpv.Cantidad',
                'vpv.MontoDivisa',
                'vpv.PrecioVenta',
                'vpv.ProductoId',
                'p.Codigo',
                'p.Descripcion',
                'p.CostoDivisa',
                'p.UrlFoto'
            );

        if (session('sucursal_id') != 0) {
            $detallesQuery->where('vpv.SucursalId', session('sucursal_id')); // si tienes SucursalId en la vista
        }

        $detalles = $detallesQuery->get();


        // 4️⃣ Mapear ventas con fechas
        $ventasMap = DB::table('Ventas')
            ->whereIn('ID', $ventasIds)
            ->select('ID', 'FECHA')
            ->get()
            ->keyBy('ID');

        $detallesPorFecha = [];
        foreach ($detalles as $d) {
            if (!isset($ventasMap[$d->VentaId])) continue;

            $fecha = Carbon::parse($ventasMap[$d->VentaId]->FECHA)->format('Y-m-d');

            if (!isset($detallesPorFecha[$fecha])) {
                $detallesPorFecha[$fecha] = [];
            }

            $detallesPorFecha[$fecha][] = $d;
        }

        // Totales para margen
        $totalDivisaPeriodo = 0;
        $costoTotalDivisaPeriodo = 0;

        // RESULTADO INICIAL
        $resultado = [
            "FechaInicio" => $fechaInicio->format('Y-m-d H:i:s'),
            "FechaFin" => $fechaFin->format('Y-m-d H:i:s'),
            "ListaVentasDiarias" => [],
            "ListaItemsPeriodo" => [],       // 🔥 INICIALIZADO AQUÍ
            "MargenDivisasPeriodo" => 0,
        ];

        // 5️⃣ Recorrer ventas diarias
        foreach ($ventasDiarias as $venta) {
            $fecha = $venta->fecha_formateada;

            $ventaDiaria = [
                "Fecha" => $fecha,
                "TasaDeCambio" => $venta->TasaDeCambio,
                "TotalBS" => $venta->TotalBs,
                "TotalDivisa" => $venta->TotalDivisa,
                "ListadoProductosVentaDiaria" => [],
                "UtilidadDivisaDiario" => 0,
                "PromedioMontoPorFactura" => 0,
                "PromedioMontoDivisaPorFactura" => 0,
                "PromedioProductosPorFactura" => 0,
            ];

            if (isset($detallesPorFecha[$fecha])) {
                $totalUnidades = 0;
                $facturasDelDia = collect($detallesPorFecha[$fecha])->pluck('VentaId')->unique()->count();

                foreach ($detallesPorFecha[$fecha] as $row) {
                    $utilidadProducto = round(
                        $row->MontoDivisa - ($row->CostoDivisa * $row->Cantidad),
                        2
                    );

                    $totalUnidades += $row->Cantidad;

                    $ventaDiaria["ListadoProductosVentaDiaria"][] = [
                        "Cantidad"      => $row->Cantidad,
                        "MontoDivisa"   => $row->MontoDivisa,
                        "PrecioVenta"   => $row->PrecioVenta,
                        "ProductoId"    => $row->ProductoId,
                        "UtilidadDivisa" => $utilidadProducto,
                        "Producto" => [
                            "Id"          => $row->ProductoId,
                            "Codigo"      => $row->Codigo,
                            "Descripcion" => $row->Descripcion,
                            "CostoDivisa" => $row->CostoDivisa,
                            "UrlFoto"     => $row->UrlFoto ? strtolower($row->UrlFoto) : "",
                        ],
                    ];

                    // 🔥 ESTE ARRAY ES EL QUE USAREMOS PARA EL TOP RENTABLES
                    $resultado["ListaItemsPeriodo"][] = [
                        "Cantidad"        => $row->Cantidad,
                        "MontoDivisa"     => $row->MontoDivisa,
                        "PrecioVenta"     => $row->PrecioVenta,
                        "ProductoId"      => $row->ProductoId,
                        "UtilidadDivisa"  => $utilidadProducto,
                        "Codigo"          => $row->Codigo,
                        "Descripcion"     => $row->Descripcion,
                        "CostoDivisa"     => $row->CostoDivisa,
                        "UrlFoto"         => $row->UrlFoto ? strtolower($row->UrlFoto) : "",
                    ];

                    $ventaDiaria["UtilidadDivisaDiario"] += $utilidadProducto;

                    $totalDivisaPeriodo += $row->MontoDivisa;
                    $costoTotalDivisaPeriodo += ($row->CostoDivisa * $row->Cantidad);
                }

                if ($facturasDelDia > 0) {
                    $ventaDiaria["PromedioMontoPorFactura"] = round($ventaDiaria["TotalBS"] / $facturasDelDia, 2);
                    $ventaDiaria["PromedioMontoDivisaPorFactura"] = round($ventaDiaria["TotalDivisa"] / $facturasDelDia, 2);
                    $ventaDiaria["PromedioProductosPorFactura"] = round($totalUnidades / $facturasDelDia, 2);
                }
            }

            $resultado["ListaVentasDiarias"][] = $ventaDiaria;
        }

        // ▶️ Margen del período
        if ($costoTotalDivisaPeriodo != 0) {
            $resultado["MargenDivisasPeriodo"] = round((($totalDivisaPeriodo * 100) / $costoTotalDivisaPeriodo) - 100, 2);
        }

        // ================================
        //    🔥 CALCULAR TOP RENTABLES
        // ================================

        $acumulado = [];

        foreach ($resultado["ListaItemsPeriodo"] as $item) {

            $id = $item["ProductoId"];

            if (!isset($acumulado[$id])) {
                $acumulado[$id] = [
                    "ProductoId" => $id,
                    "Codigo" => $item["Codigo"],
                    "Descripcion" => $item["Descripcion"],
                    "UrlFoto" => $item["UrlFoto"],
                    "Cantidad" => 0,
                    "UtilidadDivisa" => 0,
                ];
            }

            $acumulado[$id]["Cantidad"] += $item["Cantidad"];
            $acumulado[$id]["UtilidadDivisa"] += $item["UtilidadDivisa"];
        }

        // Ordenar por utilidad DESC y tomar TOP 4
        $resultado["TopProductosRentables"] = collect($acumulado)
            ->sortByDesc("UtilidadDivisa")
            ->take(4)
            ->values()
            ->all();

        return $resultado;
    }

    // Obtener valores en 
    /**
     * Equivalente a BuscarValoresEstadoDeCuentaSucursal de .NET
     */
    public static function buscarValoresEstadoDeCuentaSucursal(ParametrosFiltroFecha $filtroFecha, int $sucursalId) 
    {

        // Instanciar servicios (puedes pasarlos por constructor si usas IoC)
        $ventasService = new VentasService();

        // Objeto final que devolveremos (array estilo DTO)
        $balanceSucursal = [
            'SucursalId' => $sucursalId,
        ];

        // ================================
        // VENTAS
        // ================================
        $balanceSucursal['Ventas'] =
            $ventasService->obtenerListadoVentasDiarias(
                $filtroFecha,
                $sucursalId,
                false
            );

        // dd($balanceSucursal['Ventas']);

        // ================================
        // GASTOS DEL PERÍODO
        // ================================
        $balanceSucursal['ListadoGastosPeriodo'] = self::buscarGastosSucursal(
                                                            $sucursalId,
                                                            $filtroFecha,
                                                            true
                                                        );

        // ================================
        // GASTOS POR PAGAR (para cerrar)
        // ================================
        $balanceSucursal['ListadoGastosPorPagar'] =
            self::buscarGastosSucursalParaCerrar(
                $sucursalId,
                $filtroFecha
            );

        // ================================
        // RECEPCIONES
        // ================================
        $balanceSucursal['ListadoRecepciones'] =
            self::buscarRecepcionesSucursalParaCerrar(
                $sucursalId,
                null
            );

        // ================================
        // VALORIZACIÓN DE INVENTARIO
        // ================================
        $balanceSucursal['ValorizacionInventario'] =
            self::buscarValorizacionInventario(
                $sucursalId
            );

        // ================================
        // PAGO DE SERVICIOS
        // ================================
        $balanceSucursal['ListadoPagoServicios'] =
            self::buscarTransacciones(
                'Servicio',
                $sucursalId,
                $filtroFecha
            );

        // ================================
        // PAGO DE FACTURAS – MERCANCIA
        // ================================
        $balanceSucursal['ListadoPagoMercancia'] =
            self::buscarTransacciones(
                'PagoMercancia',
                $sucursalId,
                $filtroFecha
            );

        // ================================
        // OPERACIONES (Balance final)
        // ================================
        $balanceSucursal['EDCOperaciones'] =
            self::generarBalanceSucursalVentas(
                $balanceSucursal['Ventas'],
                null
            );

        $balanceSucursal['EDCOperaciones'] =
            self::generarBalanceSucursalOperaciones(
                $balanceSucursal['ListadoGastosPeriodo'],
                $balanceSucursal['EDCOperaciones']
            );

        $balanceSucursal['EDCOperaciones'] =
            self::generarBalanceSucursalOperaciones(
                $balanceSucursal['ListadoPagoServicios'],
                $balanceSucursal['EDCOperaciones']
            );

        // Calculo de gastos por mes
        $balanceSucursal['GastosDivisaPeriodo'] = 0;
        $balanceSucursal['MontoGastosBsPeriodo'] = 0;

        if (!empty($balanceSucursal['ListadoGastosPeriodo'])) {
            $balanceSucursal['GastosDivisaPeriodo'] = collect($balanceSucursal['ListadoGastosPeriodo'])
                ->sum(fn ($g) => (float) ($g->MontoDivisaAbonado ?? 0));

            $balanceSucursal['MontoGastosBsPeriodo'] = collect($balanceSucursal['ListadoGastosPeriodo'])
                ->sum(fn ($g) => (float) ($g->MontoBsAbonado ?? 0));
        }
        
        return $balanceSucursal;
    }

    // ===================================================================
    // Métodos equivalentes a GenerarBalanceDeSucursalVentas / Operaciones
    // ===================================================================
    public static function generarBalanceSucursalVentas($ventas, $edc)
    {
        if (!$ventas || !isset($ventas['listaVentasDiarias']) || empty($ventas['listaVentasDiarias'])) 
        {
            return $edc;
        }

        if (!$edc || !is_object($edc)) {
            $edc = new \stdClass();
        }

        if (!isset($edc->Detalles) || !$edc->Detalles) {
            $edc->Detalles = [];
        }

        foreach ($ventas['listaVentasDiarias'] as $item) {
            
            // 👉 propiedades reales del DTO
            $montoDivisa = (float) ($item->totalDivisa ?? 0);
            $tasa        = (float) ($item->tasaDeCambio ?? 0);

            $detalle = (object) [
                'Descripcion'     => 'Ingreso por venta',
                'Fecha'           => $item->fecha ?? null,
                'MontoDivisa'     => $montoDivisa,
                'MontoBs'         => round($montoDivisa * $tasa, 2),
                'MontoPagoDivisa' => 0,
                'MontoPagoBs'     => 0,
                'Referencia'      => $item->id ? 'ID: ' . $item->Id : null,
                'SaldoDivisa'     => 0,
                'SaldoBs'         => 0,
            ];

            $edc->Detalles[] = $detalle;
        }

        self::ordenarOperacionesEDC($edc);

        return $edc;
    }


    public static function generarBalanceSucursalOperaciones($operaciones, $edc)
    {
        if (!$operaciones || count($operaciones) === 0) {
            return $edc;
        }

        if ($edc === null) {
            $edc = new \stdClass();
        }

        if (!isset($edc->Detalles) || !$edc->Detalles) {
            $edc->Detalles = [];
        }

        foreach ($operaciones as $item) {
            $detalle = (object) [
                'Descripcion'     => trim(($item->Descripcion ?? '') . (isset($item->Observacion) && $item->Observacion !== '' ? '-' . $item->Observacion : '')),
                'Fecha'           => $item->Fecha,
                'MontoPagoDivisa' => (float) ($item->MontoDivisaAbonado ?? 0),
                'MontoPagoBs'     => (float) ($item->MontoAbonado ?? 0),
                'Referencia'      => $item->NumeroOperacion ?? null,
                'MontoDivisa'     => 0,
                'MontoBs'         => 0,
                'SaldoDivisa'     => 0,
                'SaldoBs'         => 0,
                'UrlComprobante'  => $item->UrlComprobante ?? null,
            ];

            $edc->Detalles[] = $detalle;
        }

        // Reordenar
        self::ordenarOperacionesEDC($edc);

        return $edc;
    }


    public static function ordenarOperacionesEDC($balanceGeneral)
    {
        if (!$balanceGeneral || empty($balanceGeneral->Detalles)) {
            return;
        }

        // Ordenar: Fecha ASC, Referencia DESC
        usort($balanceGeneral->Detalles, function ($a, $b) {

            if ($a->Fecha == $b->Fecha) {
                return strcmp($b->Referencia, $a->Referencia);
            }

            return strtotime($a->Fecha) <=> strtotime($b->Fecha);
        });

        $saldoDivisa = 0;
        $saldoBs = 0;

        foreach ($balanceGeneral->Detalles as $item) {

            $saldoDivisa +=
                (float) $item->MontoDivisa - (float) $item->MontoPagoDivisa;

            $item->SaldoDivisa = $saldoDivisa;

            $saldoBs +=
                (float) $item->MontoBs - (float) $item->MontoPagoBs;

            $item->SaldoBs = $saldoBs;
        }

        // Reverse como en .NET
        $balanceGeneral->Detalles = array_reverse($balanceGeneral->Detalles);
    }

    public static function buscarGastosSucursal($sucursalId, ParametrosFiltroFecha $filtroFecha, bool $incluirGastosSucursal = true)
    {
        // Definir las fechas de inicio y fin basadas en el filtro
        // $fechaInicio = $filtroFecha->fechaInicio->startOfDay();
        // $fechaFin = $filtroFecha->fechaFin->startOfDay();

        // ============================
        // Buscar los gastos tipo "Gasto"
        // ============================
        // $listadoGastos = DB::table('Transacciones')
        //     ->where('Tipo', 2)
        //     ->whereBetween('fecha', [$filtroFecha->fechaInicio, $filtroFecha->fechaFin]);
        //     //->whereBetween('Fecha', [$fechaInicio, $fechaFin]);
                

        // // Filtrar por sucursal si se pasa un ID de sucursal
        // if ($sucursalId != null) {
        //     $listadoGastos->where('SucursalId', $sucursalId);
        // }

        // // Obtener los resultados como un array de objetos
        // $listadoGastos = $listadoGastos->get()->toArray();

        $listadoGastos = DB::table('Transacciones as t')
            ->leftJoin('Sucursales as s', 't.SucursalId', '=', 's.ID')
            ->where('t.Tipo', 2)
            ->whereBetween('t.Fecha', [$filtroFecha->fechaInicio, $filtroFecha->fechaFin]);

        // Filtrar por sucursal si se pasa un ID de sucursal
        if (!is_null($sucursalId) && $sucursalId != 0) {
            $listadoGastos->where('t.SucursalId', $sucursalId);
        }

        // Seleccionar columnas (incluye el nombre de la sucursal)
        $listadoGastos = $listadoGastos
            ->select(
                't.*',
                's.Nombre as SucursalNombre'
            )
            ->orderBy('t.Fecha')
            ->get()
            ->toArray();    

        // return $listadoGastos;

        // ============================
        // Si se incluyen los gastos de caja
        // ============================
        if ($incluirGastosSucursal) {
            // $gastosCaja = DB::table('Transacciones')
            //     ->where('Tipo', 3)
            //     ->whereBetween('fecha', [$filtroFecha->fechaInicio, $filtroFecha->fechaFin]);

            // // Filtrar por sucursal si es necesario
            // if ($sucursalId != null) {
            //     $gastosCaja->where('SucursalId', $sucursalId);
            // }

            // // Obtener los resultados como un array de objetos
            // $gastosCaja = $gastosCaja->get()->toArray();

            $gastosCaja = DB::table('Transacciones as t')
                ->leftJoin('Sucursales as s', 't.SucursalId', '=', 's.ID')
                ->where('t.Tipo', 3)
                ->whereBetween('t.Fecha', [$filtroFecha->fechaInicio, $filtroFecha->fechaFin]);

            // Filtrar por sucursal si aplica
            if (!is_null($sucursalId) && $sucursalId != 0) {
                $gastosCaja->where('t.SucursalId', $sucursalId);
            }

            // Seleccionar columnas con nombre de sucursal
            $gastosCaja = $gastosCaja
                ->select(
                    't.*',
                    's.Nombre as SucursalNombre'
                )
                ->orderBy('t.Fecha')
                ->get()
                ->toArray();

            // Combinar los resultados de ambos tipos de gastos
            $listadoGastos = array_merge($listadoGastos, $gastosCaja);
        }

        return $listadoGastos;
    }

    public static function buscarGastosSucursalParaCerrar(int $sucursalId, ParametrosFiltroFecha $filtroFecha) 
    {
        // Tipos permitidos (equivalentes al enum .NET)
        $tiposTransacciones = [
            2, // Gasto
            3, // GastoCaja
            0, // PagoMercancia
            5, // PagoServicio
        ];

        // Consulta principal de gastos
        $query = DB::table('Transacciones')
            ->whereIn('Tipo', $tiposTransacciones)
            ->where('Tipo', '!=', 7) // AbonoDeuda
            ->where('SucursalId', $sucursalId)
            ->where('Estatus', '!=', 5);

        // Filtro de fecha solo si viene
        if ($filtroFecha) {
            $query->where('Fecha', '<=', $filtroFecha->fechaFin);
        }

        $transacciones = $query->get();

        if ($transacciones->isEmpty()) {
            return [];
        }

        $resultado = [];

        // Obtener todos los IDs de gastos para JOIN
        $gastoIds = $transacciones->pluck('ID')->toArray();

        // Obtener todos los abonos de esos gastos usando JOIN (mejor rendimiento)
        $abonosTodos = DB::table('Transacciones as t')
            ->join('TransaccionesGastos as tg', 'tg.TransaccionId', '=', 't.ID')
            ->where('t.Tipo', 7) // AbonoDeuda
            ->whereIn('tg.GastoId', $gastoIds)
            ->select('t.*', 'tg.GastoId')
            ->get()
            ->groupBy('GastoId'); // Agrupamos por gasto para fácil asignación

        // Recorremos los gastos y calculamos saldo
        foreach ($transacciones as $gasto) {
            $abonos = $abonosTodos->get($gasto->ID, collect());

            $totalAbonosDivisa = $abonos->sum('MontoDivisaAbonado');
            $saldoDivisa = ($gasto->MontoDivisaAbonado ?? 0) - $totalAbonosDivisa;

            if ($saldoDivisa > 0) {
                $gasto->AbonoVentas = $abonos;
                $gasto->SaldoDivisa = $saldoDivisa;
                $resultado[] = $gasto;
            }
        }

        return $resultado;
    }

    public static function buscarRecepcionesSucursalParaCerrar(int $sucursalId, ?ParametrosFiltroFecha $filtroFecha = null) 
    {
        // ================================
        // 1️⃣ Buscar recepciones pendientes
        // ================================
        $query = DB::table('Recepciones')
            ->where('SucursalDestinoId', $sucursalId)
            ->whereNotIn('Estatus', [
                7, // Pagada
                8  // FinalizadaPagada
            ]);

        if ($filtroFecha) {
            $query->where('FechaCreacion', '<=', $filtroFecha->fechaFin);
        }

        $recepciones = $query->get();

        if ($recepciones->isEmpty()) {
            return [];
        }

        $resultado = [];

        // ================================
        // 2️⃣ Procesar cada recepción
        // ================================
        foreach ($recepciones as $recepcion) {

            // ================================
            // Buscar abonos de la recepción
            // ================================
            $abonos = DB::table('Transacciones')
                ->where('Tipo', 7) // AbonoDeuda
                ->whereExists(function ($q) use ($recepcion) {
                    $q->select(DB::raw(1))
                    ->from('TransaccionesRecepciones')
                    ->whereColumn(
                        'TransaccionesRecepciones.TransaccionId',
                        'Transacciones.ID'
                    )
                    ->where(
                        'TransaccionesRecepciones.RecepcionId',
                        $recepcion->RecepcionId
                    );
                })
                ->get();

            // ================================
            // Calcular saldo
            // ================================
            $totalAbonosDivisa = $abonos->sum('MontoDivisaAbonado');
            $montoRecepcionDivisa = $recepcion->TotalDivisa ?? 0;

            $saldoDivisa = $montoRecepcionDivisa - $totalAbonosDivisa;

            if ($saldoDivisa > 0) {
                $recepcion->AbonoVentas = $abonos;
                $recepcion->SaldoDivisa = $saldoDivisa;
                $resultado[] = $recepcion;
            }
        }

        return $resultado;
    }

    public static function buscarValorizacionInventario(int $sucursalId)
    {
        $valorizacion = DB::table('ValorizacionInventario')
            ->where('SucursalId', $sucursalId)
            ->first();

        if (!$valorizacion) {
            return null;
        }

        return (object) [
            'Existencia'   => (int) $valorizacion->Existencia,
            'Referencias'  => (int) $valorizacion->Referencias,
            'CostoBs'      => (float) $valorizacion->CostoBs,
            'CostoDivisa'  => (float) $valorizacion->CostoDivisa,
            'PvpBs'        => (float) $valorizacion->PvpBs,
            'PvpDivisa'    => (float) $valorizacion->PvpDivisa,
            'SucursalId'   => (int) $valorizacion->SucursalId,
        ];
    }

    public static function buscarTransacciones(string $tipoTransaccion, ?int $sucursalId, ?ParametrosFiltroFecha $filtroFecha) 
    {
        // Mapeo EnumTipoTransaccion .NET → int BD
        $mapaTipos = [
            'PagoMercancia' => 0,
            'Servicio'      => 1,
            'Gasto'         => 2,
            'GastoCaja'     => 3,
            'AbonoPrestamo' => 4,
            'PagoServicio'  => 5,
            'PagoFraccionado' => 6,
            'AbonoDeuda'    => 7,
            'Liberalidad'   => 8,
        ];

        $query = DB::table('Transacciones')
            ->where('Tipo', $mapaTipos[$tipoTransaccion]);

        if ($sucursalId !== null) {
            $query->where('SucursalId', $sucursalId);
        }

        if ($filtroFecha) {
            $fechaInicio = $filtroFecha->fechaInicio->startOfDay();
            $fechaFin = $filtroFecha->fechaFin->startOfDay();

            $query->whereBetween('Fecha', [$fechaInicio, $fechaFin]);
        }

        $transacciones = $query->get();

        if ($transacciones->isEmpty()) {
            return null;
        }

        $resultado = [];

        foreach ($transacciones as $item) {
            $transaccion = (object) [
                'Id'           => $item->ID,
                'Descripcion'  => $item->Descripcion,
                'MontoAbonado' => (float) $item->MontoAbonado,
                'MontoDivisaAbonado' => (float) $item->MontoDivisaAbonado,
                'Tipo'         => (int) $item->Tipo,
                'Estatus'      => (int) $item->Estatus,
                'SucursalId'   => (int) $item->SucursalId,
                'CategoriaId'  => $item->CategoriaId,
                'Fecha'        => $item->Fecha,
                'Categoria'    => null,
            ];

            // ✅ Equivalente a BuscarCategoriaGasto
            if ($item->CategoriaId) {
                $transaccion->Categoria =
                    self::buscarCategoriaGasto((int) $item->CategoriaId);
            }

            $resultado[] = $transaccion;
        }

        return $resultado;
    }

    public static function buscarCategoriaGasto(int $categoriaId)
    {
        if ($categoriaId <= 0) {
            return null;
        }

        $categoria = DB::table('CategoriaGastos')
            ->where('CategoriaId', $categoriaId)
            ->first();

        if (!$categoria) {
            return null;
        }

        return (object) [
            'CategoriaId' => $categoria->CategoriaId,
            'Nombre'      => $categoria->Nombre,
            'Descripcion' => $categoria->Descripcion ?? null,
        ];
    }

    public static function ObtenerComparacionSucursales(ParametrosFiltroFecha $filtro)
    {
        $fechaInicio = $filtro->fechaInicio->startOfDay(); 
        $fechaFin = $filtro->fechaFin->startOfDay();

        // Primero obtener ventas SIN el join problemático
        $rows = DB::table('VentaProductosView as v')
            ->join('Productos as p', 'p.ID', '=', 'v.ProductoId')
            ->whereBetween('v.Fecha', [$fechaInicio, $fechaFin])
            ->where('v.Estatus', 1)
            ->groupBy(
                'v.ProductoId',
                'p.Codigo',
                'p.Descripcion',
                'p.CostoDivisa',
                'p.UrlFoto'
            )
            ->selectRaw('
                p.UrlFoto,
                v.ProductoId,
                p.Codigo,
                p.CostoDivisa,
                p.Descripcion,

                SUM(CASE WHEN v.SucursalId = 3 THEN v.Cantidad ELSE 0 END) AS CantidadCalzatodo,
                SUM(CASE WHEN v.SucursalId = 4 THEN v.Cantidad ELSE 0 END) AS CantidadTenShop,
                SUM(CASE WHEN v.SucursalId = 5 THEN v.Cantidad ELSE 0 END) AS Cantidad10y10,
                SUM(CASE WHEN v.SucursalId = 7 THEN v.Cantidad ELSE 0 END) AS CantidadG1091,

                SUM(CASE WHEN v.SucursalId = 3 THEN v.MontoDivisa ELSE 0 END) AS TotalDivisasCalzatodo,
                SUM(CASE WHEN v.SucursalId = 4 THEN v.MontoDivisa ELSE 0 END) AS TotalDivisasTenShop,
                SUM(CASE WHEN v.SucursalId = 5 THEN v.MontoDivisa ELSE 0 END) AS TotalDivisas10y10,
                SUM(CASE WHEN v.SucursalId = 7 THEN v.MontoDivisa ELSE 0 END) AS TotalDivisasG1091
            ')
            ->havingRaw('SUM(v.Cantidad) > 0')
            ->get();

        // Luego obtener existencias por separado
        $existencias = DB::table('ProductoSucursal as ps')
            ->select([
                'ps.ProductoId',
                'ps.SucursalId',
                DB::raw('MAX(ps.Existencia) as Existencia'),
                DB::raw('MAX(ps.PvpDivisa) as PvpDivisa')
            ])
            ->where('ps.Estatus', 1)
            ->groupBy('ps.ProductoId', 'ps.SucursalId')
            ->get()
            ->groupBy('ProductoId');

        $dto = new ComparacionSucursalesDTO();
        $dto->fechaInicio = $fechaInicio;
        $dto->fechaFin = $fechaFin;

        foreach ($rows as $row) {
            $detalle = new ComparacionSucursalesDetalleDTO();

            $detalle->producto = [
                'Id' => $row->ProductoId,
                'Codigo' => $row->Codigo,
                'Descripcion' => $row->Descripcion,
                'CostoDivisa' => (float) $row->CostoDivisa,
                'UrlFoto' => $row->UrlFoto ? strtolower($row->UrlFoto) : ''
            ];

            // Asignar ventas (estas ya vienen correctas)
            $detalle->CantidadCalzatodo = (float) $row->CantidadCalzatodo;
            $detalle->CantidadTenShop = (float) $row->CantidadTenShop;
            $detalle->Cantidad10y10 = (float) $row->Cantidad10y10;
            $detalle->CantidadG1091 = (float) $row->CantidadG1091;
            
            $detalle->TotalDivisasCalzatodo = (float) $row->TotalDivisasCalzatodo;
            $detalle->TotalDivisasTenShop = (float) $row->TotalDivisasTenShop;
            $detalle->TotalDivisas10y10 = (float) $row->TotalDivisas10y10;
            $detalle->TotalDivisasG1091 = (float) $row->TotalDivisasG1091;

            // Asignar existencias si existen
            if (isset($existencias[$row->ProductoId])) {
                foreach ($existencias[$row->ProductoId] as $exist) {
                    switch ($exist->SucursalId) {
                        case 3:
                            $detalle->ExistenciaCalzatodo = (float) $exist->Existencia;
                            $detalle->PvpDivisaCalzatodo = (float) $exist->PvpDivisa;
                            break;
                        case 4:
                            $detalle->ExistenciaTenShop = (float) $exist->Existencia;
                            $detalle->PvpDivisaTenShop = (float) $exist->PvpDivisa;
                            break;
                        case 5:
                            $detalle->Existencia10y10 = (float) $exist->Existencia;
                            $detalle->PvpDivisa10y10 = (float) $exist->PvpDivisa;
                            break;
                        case 7:
                            $detalle->ExistenciaG1091 = (float) $exist->Existencia;
                            $detalle->PvpDivisaG1091 = (float) $exist->PvpDivisa;
                            break;
                    }
                }
            }

            $dto->detalles[] = $detalle;
        }

        // dd($dto->detalles[0] ?? 'No hay datos');
        return $dto;
    }

    public static function ObtenerIndiceRotacion(ParametrosFiltroFecha $filtro, ?int $sucursalId)
    {
        $fechaInicio = $filtro->fechaInicio->startOfDay(); 
        $fechaFin = $filtro->fechaFin->startOfDay();

        // Query base: productos vendidos
        $ventasQuery = DB::table('VentaProductosView as v')
            ->join('Productos as p', 'v.ProductoId', '=', 'p.ID')
            ->select(
                'v.ProductoId',
                DB::raw('SUM(v.Cantidad) as TotalUnidades'),
                'v.Existencia',
                'p.Codigo',
                'p.Descripcion',
                'p.CostoDivisa',
                'v.PvpDivisa',
                'v.PvpAnterior',
                'v.NuevoPvp',
                'v.FechaNuevoPrecio',
                'v.FechaUltimaVenta',
                'p.UrlFoto',
                'v.SucursalId'
            )
            ->where('v.Existencia', '>', 0)
            ->where('v.Estatus', 1);

        if ($fechaInicio && $fechaFin) {
            $ventasQuery->whereBetween('v.Fecha', [$fechaInicio, $fechaFin]);
        }

        if ($sucursalId != 0) {
            $ventasQuery->where('v.SucursalId', $sucursalId);
        }

        $ventas = $ventasQuery
            ->groupBy(
                'v.ProductoId', 'v.Existencia', 'p.Codigo', 'p.Descripcion', 'p.CostoDivisa',
                'v.PvpDivisa', 'v.PvpAnterior', 'v.NuevoPvp', 'v.FechaNuevoPrecio', 
                'v.FechaUltimaVenta', 'p.UrlFoto', 'v.SucursalId'
            )
            ->havingRaw('SUM(v.Cantidad) > 0')
            ->get();

        $ventaTotal = $ventas->sum('TotalUnidades');

        if ($ventaTotal == 0) return null;

        // **CORRECCIÓN: Calcular factor con MÁXIMA precisión**
        $factor = $ventas->reduce(function ($max, $item) use ($ventaTotal) {
            $calculo = ($item->TotalUnidades / $ventaTotal) * 365;
            return $calculo > $max ? $calculo : $max;
        }, 0);

        // Mapear detalles con CÁLCULO EXACTO
        $detalles = $ventas->map(function ($item) use ($ventaTotal, $factor) {
            $totalUnidades = $item->TotalUnidades;
            
            // 1. Calcular base con alta precisión
            $base = ($totalUnidades / $ventaTotal) * 365;
            
            if ($factor > 10000) {
                $indice = $base / 100;
            } elseif ($factor > 1000) {
                $indice = $base / 10;
            } elseif ($factor > 100) {
                $indice = $base; // /1
            } elseif ($factor > 10) {
                $indice = $base / 0.1; // *10
            } else {
                $indice = $base * 0.9576;
            }
            
            // Redondear EXACTAMENTE como SQL Server: 2 decimales
            $indice = round($indice, 2);

            $producto = [
                'id' => $item->ProductoId,
                'codigo' => $item->Codigo,
                'descripcion' => $item->Descripcion,
                'existencia' => $item->Existencia ?? 0,
                'costo_divisa' => $item->CostoDivisa ?? 0,
                'pvp_divisa' => $item->PvpDivisa ?? 0,
                'pvp_anterior' => $item->PvpAnterior ?? 0,
                'nuevo_pvp' => $item->NuevoPvp ?? 0,
                'fecha_nuevo_precio' => $item->FechaNuevoPrecio ? Carbon::parse($item->FechaNuevoPrecio) : null,
                'fecha_ultima_venta' => $item->FechaUltimaVenta ? Carbon::parse($item->FechaUltimaVenta) : null,
                'url_foto' => strtolower($item->UrlFoto ?? ''),
                'sucursal_id' => $item->SucursalId
            ];

            return new IndiceDeRotacionDetallesDTO(
                $item->ProductoId,
                $totalUnidades,
                $indice,
                $producto
            );
        })->sortByDesc('indice_rotacion')->values();

        return new IndiceDeRotacionDTO($detalles, $fechaInicio, $fechaFin);
    }

    public static function ObtenerSinVentaSucursales(ParametrosFiltroFecha $filtro, ?int $sucursalId) 
    {
        $fechaInicio = $filtro->fechaInicio->startOfDay();
        $fechaFin    = $filtro->fechaFin->startOfDay();

        /*
        |--------------------------------------------------------------------------
        | 1️⃣ PRODUCTOS CON BAJA DEMANDA (TIENEN VENTAS)
        |--------------------------------------------------------------------------
        */
        $ventas = DB::table('VentaProductosView as v')
            ->join('Productos as p', 'p.ID', '=', 'v.ProductoId')
            ->where('v.Estatus', 1)
            ->where('v.Existencia', '>', 0)
            ->where('p.Codigo', '<>', 'SALDO')
            ->where('p.Descripcion', '<>', 'SALDO')
            ->whereBetween('v.Fecha', [$fechaInicio, $fechaFin])
            ->when($sucursalId != 0, fn ($q) =>
                $q->where('v.SucursalId', $sucursalId)
            )
            ->where('p.FechaCreacion', '<', now()->subMonths(2))
            ->groupBy(
                'v.ProductoId',
                'v.SucursalId',
                'v.Existencia',
                'p.Codigo',
                'p.Descripcion',
                'p.CostoDivisa',
                'v.PvpDivisa',
                'v.PvpAnterior',
                'v.NuevoPvp',
                'v.FechaNuevoPrecio',
                'v.FechaUltimaVenta',
                'p.UrlFoto',
                'p.FechaCreacion'
            )
            ->havingRaw('SUM(v.Cantidad) > 0')
            ->havingRaw('SUM(v.Cantidad) <= (v.Existencia * 0.2)')
            ->select(
                'v.ProductoId',
                'v.SucursalId',
                DB::raw('SUM(v.Cantidad) as TotalUnidades'),
                'v.Existencia',
                'p.Codigo',
                'p.Descripcion',
                'p.CostoDivisa',
                'v.PvpDivisa',
                'v.PvpAnterior',
                'v.NuevoPvp',
                'v.FechaNuevoPrecio',
                'v.FechaUltimaVenta',
                'p.UrlFoto',
                'p.FechaCreacion'
            )
            ->get();

        $ventaTotal = $ventas->sum('TotalUnidades');

        /*
        |--------------------------------------------------------------------------
        | 2️⃣ MAPEAR BAJA DEMANDA
        |--------------------------------------------------------------------------
        */
        $bajaDemanda = collect();

        if ($ventaTotal > 0) {
            $factor = $ventas->reduce(function ($max, $item) use ($ventaTotal) {
                $valor = ($item->TotalUnidades / $ventaTotal) * 365;
                return $valor > $max ? $valor : $max;
            }, 0);

            $bajaDemanda = $ventas->map(function ($item) use ($ventaTotal, $factor) {

                $base = ($item->TotalUnidades / $ventaTotal) * 365;

                if ($factor > 10000)       $indice = $base / 100;
                elseif ($factor > 1000)    $indice = $base / 10;
                elseif ($factor > 100)     $indice = $base;
                elseif ($factor > 10)      $indice = $base / 0.1;
                else                       $indice = $base * 0.9576;

                $indice = round($indice, 2);

                return new IndiceDeRotacionDetallesDTO(
                    $item->ProductoId,
                    $item->TotalUnidades,
                    $indice,
                    [
                        'id' => $item->ProductoId,
                        'codigo' => $item->Codigo,
                        'descripcion' => $item->Descripcion,
                        'existencia' => $item->Existencia,
                        'costo_divisa' => $item->CostoDivisa,
                        'pvp_divisa' => $item->PvpDivisa,
                        'pvp_anterior' => $item->PvpAnterior,
                        'nuevo_pvp' => $item->NuevoPvp,
                        'fecha_nuevo_precio' => $item->FechaNuevoPrecio ? Carbon::parse($item->FechaNuevoPrecio) : null,
                        'fecha_ultima_venta' => $item->FechaUltimaVenta ? Carbon::parse($item->FechaUltimaVenta) : null,
                        'fecha_creacion' => Carbon::parse($item->FechaCreacion),
                        'url_foto' => strtolower($item->UrlFoto ?? ''),
                        'sucursal_id' => $item->SucursalId
                    ]
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 3️⃣ PRODUCTOS SIN VENTA (NUNCA VENDIDOS)
        |--------------------------------------------------------------------------
        */
        $sinVenta = DB::table('ProductosSucursalView as ps')
            ->join('Productos as p', 'p.ID', '=', 'ps.ID')
            ->where('ps.Existencia', '>', 0)
            ->where('ps.Estatus', 1)
            ->where('p.Codigo', '<>', 'SALDO')
            ->where('p.Descripcion', '<>', 'SALDO')
            ->where('p.FechaCreacion', '<', now()->subMonths(2))
            ->when($sucursalId != 0, fn ($q) =>
                $q->where('ps.SucursalId', $sucursalId)
            )
            ->whereNotExists(function ($q) use ($fechaInicio, $fechaFin) {
                $q->select(DB::raw(1))
                ->from('VentaProductos as vp')
                ->join('Ventas as v', 'v.Id', '=', 'vp.VentaId')
                ->whereRaw('vp.ProductoId = ps.ID')
                ->whereBetween('v.Fecha', [$fechaInicio, $fechaFin]);
            })
            ->select(
                'ps.ID as ProductoId',
                'ps.SucursalId',
                'ps.Existencia',
                'p.Codigo',
                'p.Descripcion',
                'p.CostoDivisa',
                'ps.PvpDivisa',
                'ps.PvpAnterior',
                'ps.NuevoPvp',
                'ps.FechaNuevoPrecio',
                'ps.FechaUltimaVenta',
                'p.UrlFoto',
                'p.FechaCreacion'
            )
            ->get()
            ->map(function ($item) {
                return new IndiceDeRotacionDetallesDTO(
                    $item->ProductoId,
                    0,
                    0,
                    [
                        'id' => $item->ProductoId,
                        'codigo' => $item->Codigo,
                        'descripcion' => $item->Descripcion,
                        'existencia' => $item->Existencia,
                        'costo_divisa' => $item->CostoDivisa,
                        'pvp_divisa' => $item->PvpDivisa,
                        'pvp_anterior' => $item->PvpAnterior,
                        'nuevo_pvp' => $item->NuevoPvp,
                        'fecha_nuevo_precio' => $item->FechaNuevoPrecio ? Carbon::parse($item->FechaNuevoPrecio) : null,
                        'fecha_ultima_venta' => null,
                        'fecha_creacion' => Carbon::parse($item->FechaCreacion),
                        'url_foto' => strtolower($item->UrlFoto ?? ''),
                        'sucursal_id' => $item->SucursalId
                    ]
                );
            });

        /*
        |--------------------------------------------------------------------------
        | 4️⃣ UNIR Y ELIMINAR DUPLICADOS
        |--------------------------------------------------------------------------
        */
        $final = collect();

        foreach ($bajaDemanda as $d) {
            $key = $d->producto['id'].'_'.$d->producto['sucursal_id'];
            $final[$key] = $d;
        }

        foreach ($sinVenta as $d) {
            $key = $d->producto['id'].'_'.$d->producto['sucursal_id'];
            if (!isset($final[$key])) {
                $final[$key] = $d;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 5️⃣ ORDEN FINAL (MÁS ANTIGUOS PRIMERO)
        |--------------------------------------------------------------------------
        */
        $detalles = $final
            ->sortBy(fn ($d) => optional($d->producto['fecha_creacion'])->timestamp)
            ->values();

        return new IndiceDeRotacionDTO($detalles, $fechaInicio, $fechaFin);
    }

}