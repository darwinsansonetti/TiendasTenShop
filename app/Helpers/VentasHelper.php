<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Sucursal;
use App\Models\Divisa;
use App\Models\DivisaValor;
use App\Models\Mensaje;
use App\Models\Producto;
use App\Models\VentaProducto;

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
use App\DTO\VentaDiariaDTO;
use App\DTO\VentasPeriodoDTO;
use Illuminate\Support\Facades\Log;

use Illuminate\Support\Facades\Auth;

class VentasHelper
{
    public static function BuscarListadoVentasDiarias(ParametrosFiltroFecha $filtro, ?int $sucursalId) 
    {
        $user = Auth::user()->load('sucursal');

        // Instanciar servicios (puedes pasarlos por constructor si usas IoC)
        $ventasService = new VentasService();

        // Si es una Tienda == 1, Si es 0 Es "OFICINA PRINCIPAL"
        if($user && $user->sucursal->Tipo == 1){

            // Si la Sucursal esta Activa
            if($user->sucursal->EsActiva == 1){
                // ================================
                // VENTAS DIARIAS
                // ================================
                $balanceSucursal['Ventas'] =
                    $ventasService->obtenerListadoVentasDiarias(
                        $filtro,
                        $sucursalId,
                        false
                    );

            }
        }else{
            // ================================
            // VENTAS DIARIAS
            // ================================
            $balanceSucursal['Ventas'] =
                $ventasService->obtenerListadoVentasDiarias(
                    $filtro,
                    $sucursalId,
                    false
                );
        }

        // dd($balanceSucursal['Ventas']);

        $balanceSucursal['FechaInicio'] = $filtro->fechaInicio->startOfDay();
        $balanceSucursal['FechaFin'] = $filtro->fechaFin->startOfDay();

        return $balanceSucursal;
    }

    public static function GenerarDatosdeVentasParaEscritorio(ParametrosFiltroFecha $filtro, ?int $sucursalId): array
    {
        $service = new VentasService();
        $resultadoVentas = $service->obtenerListadoVentasDiarias($filtro, $sucursalId, false);
        
        // Extraer las ventas diarias del resultado
        $listaVentasDiarias = $resultadoVentas['listaVentasDiarias'] ?? [];
        
        // Crear array inicial que pasaremos al DTO
        $ventasPeriodoArray = [
            'FechaInicio' => $filtro->fechaInicio,
            'FechaFin' => $filtro->fechaFin,
            'ListaVentasDiarias' => [],
            'UtilidadDivisaPeriodo' => $resultadoVentas['UtilidadDivisaPeriodo'] ?? 0,
            'UtilidadBsPeriodo' => $resultadoVentas['UtilidadBsPeriodo'] ?? 0,
            'UtilidadNetaPeriodo' => $resultadoVentas['UtilidadNetaPeriodo'] ?? 0,
            'MontoDivisaTotalPeriodo' => $resultadoVentas['MontoDivisaTotalPeriodo'] ?? 0,
            'CostoDivisaPeriodo' => $resultadoVentas['CostoDivisaPeriodo'] ?? 0,
            'GastosDivisaPeriodo' => $resultadoVentas['GastosDivisaPeriodo'] ?? 0,
            'MargenBrutoPeriodo' => $resultadoVentas['MargenBrutoPeriodo'] ?? 0,
            'MargenNetoPeriodo' => $resultadoVentas['MargenNetoPeriodo'] ?? 0,
            'UnidadesGlobalVendidas' => 0,
            'MontoDivisasGlobal' => $resultadoVentas['MontoDivisaTotalPeriodo'] ?? 0,
            'MontoCostoGlobal' => $resultadoVentas['CostoDivisaPeriodo'] ?? 0,
        ];
        
        $productosAgrupados = []; // Para estadísticas por producto
        $unidadesGlobalVendidas = 0;
        
        foreach ($listaVentasDiarias as $ventaDTO) {
            // Obtener ID de la venta
            $ventaId = is_object($ventaDTO) ? $ventaDTO->id : ($ventaDTO['id'] ?? null);
            
            if (!$ventaId) {
                continue;
            }
            
            // Usar Eloquent para obtener detalles
            $detalles = \App\Models\VentaProducto::where('VentaId', $ventaId)->get();
            
            $productoIds = $detalles->pluck('ProductoId')->toArray();
            $productos = collect();
            
            if (!empty($productoIds)) {
                // Primero buscar en ProductosSucursalView (si existe como modelo)
                if (class_exists('App\Models\ProductosSucursalView')) {
                    $productos = \App\Models\ProductosSucursalView::whereIn('ID', $productoIds)
                        ->where('SucursalId', $sucursalId)
                        ->get()
                        ->keyBy('ID');
                } else {
                    // Si no existe el modelo, usar DB
                    $productos = DB::table('ProductosSucursalView')
                        ->whereIn('ID', $productoIds)
                        ->where('SucursalId', $sucursalId)
                        ->get()
                        ->keyBy('ID');
                }
                
                // Fallback a tabla Productos
                if ($productos->isEmpty()) {
                    $productos = \App\Models\ProductoModel::whereIn('ID', $productoIds)
                        ->select(
                            'ID',
                            'Codigo',
                            'Descripcion',
                            'CostoBs',
                            'CostoDivisa',
                            DB::raw('0 as Existencia'),
                            DB::raw($sucursalId . ' as SucursalId'),
                            DB::raw('0 as PvpBs'),
                            DB::raw('0 as PvpDivisa')
                        )
                        ->get()
                        ->keyBy('ID');
                }
            }
            
            // Construir listadoProductosVentaDiaria
            $listadoProductos = [];
            
            foreach ($detalles as $detalle) {
                $producto = $productos[$detalle->ProductoId] ?? null;
                
                if (!$producto) {
                    continue;
                }
                
                $cantidad = $detalle->Cantidad ?? 0;
                $precio = $detalle->Precio ?? 0;
                $montoDivisa = $detalle->MontoDivisa ?? $precio;
                $costoBs = $producto->CostoBs ?? 0;
                $costoDivisa = $producto->CostoDivisa ?? 0;
                $tasa = is_object($ventaDTO) ? ($ventaDTO->tasaDeCambio ?? 1) : ($ventaDTO['tasaDeCambio'] ?? 1);
                
                $productoVenta = [
                    'ProductoId' => $detalle->ProductoId,
                    'Cantidad' => $cantidad,
                    'PrecioVenta' => $precio,
                    'MontoDivisa' => $montoDivisa,
                    'Producto' => $producto,
                    'margen' => $costoDivisa > 0 ? round(($montoDivisa / $costoDivisa * 100) - 100, 2) : 0,
                    'costoTotalItemDivisa' => $cantidad * $costoDivisa,
                    'costoTotalItemBs' => $cantidad * $costoBs,
                    'utilidadDivisa' => $montoDivisa - ($cantidad * $costoDivisa),
                    'utilidadBs' => ($montoDivisa * $tasa) - ($cantidad * $costoBs),
                ];
                
                $listadoProductos[] = $productoVenta;
                
                // Agrupar para estadísticas
                $unidadesGlobalVendidas += $cantidad;
                
                if (!isset($productosAgrupados[$detalle->ProductoId])) {
                    $productosAgrupados[$detalle->ProductoId] = [
                        'Producto' => $producto,
                        'CantidadTotal' => 0,
                        'MontoTotal' => 0,
                        'CostoTotalDivisa' => 0,
                        'VecesVendido' => 0,
                    ];
                }
                
                $productosAgrupados[$detalle->ProductoId]['CantidadTotal'] += $cantidad;
                $productosAgrupados[$detalle->ProductoId]['MontoTotal'] += $montoDivisa;
                $productosAgrupados[$detalle->ProductoId]['CostoTotalDivisa'] += ($cantidad * $costoDivisa);
                $productosAgrupados[$detalle->ProductoId]['VecesVendido'] += 1;
            }
            
            // Agregar listado de productos a la venta
            if (is_object($ventaDTO)) {
                $ventaDTO->listadoProductosVentaDiaria = collect($listadoProductos);
            } else {
                $ventaDTO['listadoProductosVentaDiaria'] = collect($listadoProductos);
            }
            
            // Agregar al array final
            $ventasPeriodoArray['ListaVentasDiarias'][] = $ventaDTO;
        }
        
        // Actualizar unidades globales
        $ventasPeriodoArray['UnidadesGlobalVendidas'] = $unidadesGlobalVendidas;
        
        // Agregar productos agrupados al array
        $ventasPeriodoArray['ProductosAgrupados'] = collect($productosAgrupados);
        
        // Calcular estadísticas de productos agrupados
        $ventasPeriodoArray = self::calcularEstadisticasProductos($ventasPeriodoArray);
        
        // DEBUG: Ver el array antes de crear el DTO
        // dd($ventasPeriodoArray);
        
        return $ventasPeriodoArray;
    }

    /**
     * Calcular estadísticas para productos agrupados
     */
    private static function calcularEstadisticasProductos(array $ventasPeriodoArray): array
    {
        $productosAgrupados = $ventasPeriodoArray['ProductosAgrupados'] ?? collect();
        
        if ($productosAgrupados->isNotEmpty()) {
            // Calcular totales
            $totalVentasProductos = $productosAgrupados->sum('MontoTotal');
            $totalCostoProductos = $productosAgrupados->sum('CostoTotalDivisa');
            $totalUnidades = $productosAgrupados->sum('CantidadTotal');
            $totalProductosUnicos = $productosAgrupados->count();
            
            // Agregar al array
            $ventasPeriodoArray['TotalVentasProductosDivisa'] = $totalVentasProductos;
            $ventasPeriodoArray['TotalCostoProductosDivisa'] = $totalCostoProductos;
            $ventasPeriodoArray['TotalProductosVendidos'] = $totalUnidades;
            $ventasPeriodoArray['TotalProductosUnicos'] = $totalProductosUnicos;
            
            // Calcular utilidad y margen
            $ventasPeriodoArray['UtilidadProductosDivisa'] = $totalVentasProductos - $totalCostoProductos;
            
            if ($totalCostoProductos > 0) {
                $ventasPeriodoArray['MargenProductos'] = round((($totalVentasProductos * 100) / $totalCostoProductos) - 100, 2);
            } else {
                $ventasPeriodoArray['MargenProductos'] = 0;
            }
            
            // Ordenar por monto total (descendente)
            $productosAgrupados = $productosAgrupados->sortByDesc('MontoTotal');
            
            // Calcular porcentajes de participación
            if ($totalVentasProductos > 0) {
                $productosAgrupados = $productosAgrupados->map(function($producto) use ($totalVentasProductos) {
                    $producto['PorcentajeParticipacion'] = round(($producto['MontoTotal'] / $totalVentasProductos) * 100, 2);
                    
                    // Calcular margen por producto
                    if ($producto['CostoTotalDivisa'] > 0) {
                        $producto['MargenProducto'] = round((($producto['MontoTotal'] * 100) / $producto['CostoTotalDivisa']) - 100, 2);
                    } else {
                        $producto['MargenProducto'] = 0;
                    }
                    
                    // Calcular utilidad por producto
                    $producto['UtilidadProducto'] = $producto['MontoTotal'] - $producto['CostoTotalDivisa'];
                    
                    return $producto;
                });
            }
            
            // Obtener top 10 productos
            $ventasPeriodoArray['Top10Productos'] = $productosAgrupados->take(10);
            
            // Actualizar la colección ordenada
            $ventasPeriodoArray['ProductosAgrupados'] = $productosAgrupados;
        } else {
            // Valores por defecto
            $ventasPeriodoArray['TotalVentasProductosDivisa'] = 0;
            $ventasPeriodoArray['TotalCostoProductosDivisa'] = 0;
            $ventasPeriodoArray['TotalProductosVendidos'] = 0;
            $ventasPeriodoArray['TotalProductosUnicos'] = 0;
            $ventasPeriodoArray['UtilidadProductosDivisa'] = 0;
            $ventasPeriodoArray['MargenProductos'] = 0;
            $ventasPeriodoArray['Top10Productos'] = collect();
        }
        
        return $ventasPeriodoArray;
    }
}