<?php

namespace App\Helpers;

use Carbon\Carbon;
use App\Models\Sucursal;
use App\Models\Divisa;
use App\Models\DivisaValor;
use App\Models\Mensaje;
use App\Models\Producto;
use App\Models\VentaProducto;
use App\Models\CierreDiario;
use App\Models\AspNetUser;
use App\Models\Usuario;
use App\Models\VentaDiariaTotalizada;
use App\Models\VentaVendedoresTotalizada;
use App\Models\PuntoDeVenta;
use App\Models\PagoPuntoDeVenta;
use App\Models\Proveedor;
use App\Models\TransaccionCierreDiario;
use App\Models\Transaccion;

use App\Helpers\ParametrosFiltroFecha;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use App\Services\VentasService;

use App\Helpers\GeneralHelper;

use App\DTO\ComparacionSucursalesDTO;
use App\DTO\ComparacionSucursalesDetalleDTO;
use App\DTO\IndiceDeRotacionDTO;
use App\DTO\IndiceDeRotacionDetallesDTO;
use App\DTO\ComparacionSinVentaDTO;
use App\DTO\ComparacionSinVentaDetallesDTO;
use App\DTO\ProductoDTO;
use App\DTO\VentaDiariaDTO;
use App\DTO\CierreDiarioDTO;
use App\DTO\CierreDiarioPeriodoDTO;
use App\DTO\DivisaValorDTO;
use App\DTO\PagoPuntoDeVentaDTO;
use App\DTO\PuntoDeVentaDTO;
use App\DTO\SucursalDTO;
use App\DTO\VentasPeriodoDTO;

use App\Enums\EnumCierreDiario;
use App\Enums\EnumTipoCierre;

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

    public static function buscarListadoAuditoriasNew(ParametrosFiltroFecha $filtroFecha, int $sucursalId, int $tipoEstatus)
    {
        if($tipoEstatus == 1){
            $query = CierreDiario::with([
                    'pagosPuntoDeVenta',
                    'sucursal',
                    'divisaValor'
                ])
                ->when($sucursalId > 0, fn($q) => $q->where('SucursalId', $sucursalId))
                ->whereBetween('Fecha', [$filtroFecha->fechaInicio, $filtroFecha->fechaFin])
                ->whereBetween('Estatus', [0, 1])
                // ->where('Estatus', $tipoEstatus)
                ->where('Tipo', 1);
        }else{
            $query = CierreDiario::with([
                    'pagosPuntoDeVenta',
                    'sucursal',
                    'divisaValor'
                ])
                ->when($sucursalId > 0, fn($q) => $q->where('SucursalId', $sucursalId))
                ->whereBetween('Fecha', [$filtroFecha->fechaInicio, $filtroFecha->fechaFin])
                ->where('Estatus', ">=" , 0)
                ->where('Tipo', 1);
        }

        $cierres = $query->get();

        foreach ($cierres as $cierre) {

            // Total punto de venta
            $totalPDV = $cierre->pagosPuntoDeVenta->sum('Monto');
            $cierre->PuntoDeVentaBs = number_format($totalPDV, 2, '.', '');

            // Valor de la divisa
            $divisaValor = $cierre->divisaValor->Valor ?? 1;
            $cierre->DivisaValor = number_format($divisaValor, 2, '.', '');

            // Conversión a divisa y formateo como string
            $cierre->EfectivoBsaDivisa      = $divisaValor > 0 ? number_format($cierre->EfectivoBs / $divisaValor, 2, '.', '') : '0.00';
            $cierre->PagoMovilBsaDivisa     = $divisaValor > 0 ? number_format($cierre->PagoMovilBs / $divisaValor, 2, '.', '') : '0.00';
            $cierre->TransferenciaBsaDivisa = $divisaValor > 0 ? number_format($cierre->TransferenciaBs / $divisaValor, 2, '.', '') : '0.00';
            $cierre->PuntoDeVentaBsaDivisa  = $divisaValor > 0 ? number_format($totalPDV / $divisaValor, 2, '.', '') : '0.00';

            $cierre->SucursalNombre = $cierre->sucursal->Nombre ?? 'Sin Sucursal';
        }

        return $cierres;
    }

    public static function buscarListadoAuditorias(?CierreDiarioPeriodoDTO $cierreDiario, ParametrosFiltroFecha $filtroFecha, int $sucursalId)
    {

        $cierreDiario = self::buscarCierresAuditorias($filtroFecha, $sucursalId);

        if (!$cierreDiario) {
            $cierreDiario = new CierreDiarioPeriodoDTO();
            $cierreDiario->ListadoCierresDiarios = [];
        } elseif (!$cierreDiario->ListadoCierresDiarios) {
            $cierreDiario->ListadoCierresDiarios = [];
        }

        return $cierreDiario;
    }

    private static function buscarCierresAuditorias(ParametrosFiltroFecha $filtroFecha, int $sucursalId)
    {

        return self::buscarPeriodoCierreDiario($sucursalId, $filtroFecha, EnumTipoCierre::Auditoria);
    }

    private static function buscarPeriodoCierreDiario(int $sucursalId, ParametrosFiltroFecha $filtroFecha, EnumTipoCierre $tipoCierre)
    {
        $cierreDiarioPeriodoDTO = new CierreDiarioPeriodoDTO();

        $listaCierre = self::buscarListaCierreDiario($sucursalId, $filtroFecha, $tipoCierre);

        if ($listaCierre) {
            $cierreDiarioPeriodoDTO->ListadoCierresDiarios = $listaCierre;
        }

        $cierreDiarioPeriodoDTO->FechaInicio = $filtroFecha->fechaInicio;
        $cierreDiarioPeriodoDTO->FechaFin = $filtroFecha->fechaFin;

        return $cierreDiarioPeriodoDTO;
    }

    private static function buscarListaCierreDiario(int $sucursalId, ParametrosFiltroFecha $filtroFecha, EnumTipoCierre $tipoCierre, EnumCierreDiario $estatus = EnumCierreDiario::Todos)
    {
        $query = CierreDiario::with(['pagosPuntoDeVenta', 'sucursal'])
            ->when($sucursalId > 0, fn($q) => $q->where('SucursalId', $sucursalId))
            ->whereBetween('Fecha', [$filtroFecha->fechaInicio, $filtroFecha->fechaFin])
            ->where('Tipo', $tipoCierre->value);

        if ($estatus !== EnumCierreDiario::Todos) {
            $query->where('Estatus', $estatus->value);
        }

        $listaCierreModel = $query->get();

        return self::construirListaCierreDiario($listaCierreModel);
    }

    private static function construirListaCierreDiario($listaCierreModel)
    {
        $listaCierreDTO = [];

        foreach ($listaCierreModel as $item) {
            $dto = new CierreDiarioDTO();

            // Campos base
            $dto->CierreDiarioId = $item->CierreDiarioId;
            $dto->Fecha = $item->Fecha;
            $dto->SucursalId = $item->SucursalId;

            // Sucursal (DTO)
            $dto->Sucursal = $item->sucursal 
                            ? new SucursalDTO($item->sucursal->toArray()) 
                            : null;

            // Pagos PDV
            $dto->PagosPuntoDeVenta = $item->pagosPuntoDeVenta
                ->map(fn($p) => new PagoPuntoDeVentaDTO($p))
                ->toArray();

            // Montos Bs
            $dto->EfectivoBs = $item->EfectivoBs;
            $dto->TransferenciaBs = $item->TransferenciaBs;
            $dto->PagoMovilBs = $item->PagoMovilBs;
            $dto->EgresoBs = $item->EgresoBs;

            // Montos divisas
            $dto->EfectivoDivisas = $item->EfectivoDivisas;
            $dto->PuntoDeVentaDivisas = $item->PuntoDeVentaDivisas;
            $dto->TransferenciaDivisas = $item->TransferenciaDivisas;
            $dto->ZelleDivisas = $item->ZelleDivisas;
            $dto->EgresoDivisas = $item->EgresoDivisas;

            // Divisa
            $dto->DivisaValor = $item->divisaValor
                ? new DivisaValorDTO($item->divisaValor)
                : null;

            // Venta
            $dto->VentaSistema = $item->VentaSistema;

            // Transacciones (gastos)
            $dto->GastosCierreDiario = $item->transacciones
                ->map(fn($t) => new TransaccionDTO($t))
                ->toArray();

            $listaCierreDTO[] = $dto;
        }

        return $listaCierreDTO;
    }

    public static function generarCierreDiario($sucursalId)
    {
        try {
            $cierreDiarioDTO = new CierreDiarioDTO();
            $cierreDiarioDTO->Fecha = Carbon::now();
            $cierreDiarioDTO->SucursalId = (int) $sucursalId;
            $cierreDiarioDTO->Tipo = 1; // Tipo: Cierre Diario
            $cierreDiarioDTO->Estatus = 0; // Estatus: Nuevo
            $cierreDiarioDTO->EsEditable = true;
            $cierreDiarioDTO->Fecha = now();

            // Obtener tasa de cambio
            $tasa = GeneralHelper::obtenerTasaCambioDiaria(now());

            if (!isset($tasa['DivisaValor'])) {
                throw new \Exception('No se encontró tasa de cambio para hoy');
            }

            $divisaValorDTO = new DivisaValorDTO();
            $divisaValorDTO->Id = (int) $tasa['DivisaValor']['ID'];
            $divisaValorDTO->DivisaId = (int) ($tasa['DivisaValor']['ID'] ?? 1); // Asume DivisaId si existe
            $divisaValorDTO->Valor = (float) $tasa['DivisaValor']['Valor'];
            $divisaValorDTO->Fecha = Carbon::parse($tasa['DivisaValor']['Fecha'] ?? now());

            // $cierreDiarioDTO->DivisaValor = (float) $tasa['DivisaValor']['Valor']; // Convertir a float
            $cierreDiarioDTO->DivisaValor = $divisaValorDTO;
            $cierreDiarioDTO->DivisaValorId = $divisaValorDTO->DivisaId;

            // Guardar el Cierre Diario
            $cierreDiarioDTO = self::guardarCierreDiario($cierreDiarioDTO);

            // Generar la lista de pagos de puntos de venta
            // $cierreDiarioDTO->PagosPuntoDeVenta = self::generarListaPagosPDV(
            //     $cierreDiarioDTO->SucursalId, 
            //     $cierreDiarioDTO->CierreDiarioId
            // );

            $cierreDiarioDTO->PagosPuntoDeVenta = self::generarListaPagosPDV($cierreDiarioDTO->SucursalId, $cierreDiarioDTO->CierreDiarioId);

            // Ahora asignar el CierreDiarioId a cada pago PDV
            if ($cierreDiarioDTO->PagosPuntoDeVenta) {
                foreach ($cierreDiarioDTO->PagosPuntoDeVenta as $pagoPDV) {
                    $pagoPDV->CierreDiarioId = $cierreDiarioDTO->CierreDiarioId;
                }
            }

            return $cierreDiarioDTO;

        } catch (\Exception $e) {
            \Log::error('Error en generarCierreDiario: ' . $e->getMessage());
            throw $e;
        }
    }

    public static function guardarCierreDiario(CierreDiarioDTO $cierreDiarioDTO)
    {

        // Buscar si ya existe el CierreDiario
        $cierreCajaModel = CierreDiario::find($cierreDiarioDTO->CierreDiarioId);

        if (!$cierreCajaModel) {
            // Si no se encuentra, creamos un nuevo modelo
            $cierreCajaModel = new CierreDiario();
        }

        // Mapear todos los campos del CierreDiarioDTO al modelo CierreDiario
        $cierreCajaModel->Fecha = $cierreDiarioDTO->Fecha;
        $cierreCajaModel->SucursalId = $cierreDiarioDTO->SucursalId;
        // $cierreCajaModel->divisa_valor = $cierreDiarioDTO->DivisaValor;  // DivisaValor se puede mapear directamente
        $cierreCajaModel->DivisaValorId = $cierreDiarioDTO->DivisaValorId;

        // Mapeo de campos adicionales
        $cierreCajaModel->MontoBaseGeneral = $cierreDiarioDTO->MontoBaseGeneral;
        $cierreCajaModel->MontoBaseExento = $cierreDiarioDTO->MontoBaseExento;
        $cierreCajaModel->ImpuestoGeneral = $cierreDiarioDTO->ImpuestoGeneral;
        $cierreCajaModel->MontoBaseGeneralDevoluciones = $cierreDiarioDTO->MontoBaseGeneralDevoluciones;
        $cierreCajaModel->MontoImpuestoGeneralDevoluciones = $cierreDiarioDTO->MontoImpuestoGeneralDevoluciones;
        $cierreCajaModel->MontoBaseExentoDevoluciones = $cierreDiarioDTO->MontoBaseExentoDevoluciones;
        $cierreCajaModel->MontoBaseGeneralAuditado = $cierreDiarioDTO->MontoBaseGeneralAuditado;
        $cierreCajaModel->MontoBaseExentoAuditado = $cierreDiarioDTO->MontoBaseExentoAuditado;
        $cierreCajaModel->ImpuestoGeneralAuditado = $cierreDiarioDTO->ImpuestoGeneralAuditado;
        $cierreCajaModel->MontoBaseGeneralDevolucionesAuditado = $cierreDiarioDTO->MontoBaseGeneralDevolucionesAuditado;
        $cierreCajaModel->MontoImpuestoGeneralDevolucionesAuditado = $cierreDiarioDTO->MontoImpuestoGeneralDevolucionesAuditado;
        $cierreCajaModel->MontoBaseExentoDevolucionesAuditado = $cierreDiarioDTO->MontoBaseExentoDevolucionesAuditado;
        $cierreCajaModel->Estatus = $cierreDiarioDTO->Estatus;
        $cierreCajaModel->EfectivoBs = $cierreDiarioDTO->EfectivoBs ?? 0.00;
        $cierreCajaModel->TransferenciaBs = $cierreDiarioDTO->TransferenciaBs ?? 0.00;
        $cierreCajaModel->Observacion = $cierreDiarioDTO->Observacion;
        $cierreCajaModel->PagoMovilBs = $cierreDiarioDTO->PagoMovilBs ?? 0.00;
        $cierreCajaModel->EgresoBs = $cierreDiarioDTO->EgresoBs ?? 0.00;
        $cierreCajaModel->EfectivoDivisas = $cierreDiarioDTO->EfectivoDivisas ?? 0;
        $cierreCajaModel->PuntoDeVentaDivisas = $cierreDiarioDTO->PuntoDeVentaDivisas ?? 0;
        $cierreCajaModel->TransferenciaDivisas = $cierreDiarioDTO->TransferenciaDivisas ?? 0;
        $cierreCajaModel->ZelleDivisas = $cierreDiarioDTO->ZelleDivisas ?? 0;
        $cierreCajaModel->EgresoDivisas = $cierreDiarioDTO->EgresoDivisas ?? 0;
        $cierreCajaModel->Tipo = $cierreDiarioDTO->Tipo;
        $cierreCajaModel->VentaSistema = $cierreDiarioDTO->VentaSistema ?? 0.00;

        // Guardar el modelo en la base de datos
        $cierreCajaModel->save();

        $cierreDiarioDTO->CierreDiarioId = $cierreCajaModel->CierreDiarioId;

        return $cierreDiarioDTO;
    }

    public static function generarListaPagosPDV($sucursalId, $CierreDiarioId)
    {
        try {
            \Log::info('Generando lista pagos PDV para sucursal:', ['sucursal_id' => $sucursalId]);

            // Obtener los puntos de venta activos para la sucursal
            $puntosDeVenta = PuntoDeVenta::with(['banco', 'sucursal'])
                ->where('EsActivo', true)
                ->where('SucursalId', $sucursalId)
                ->get();

            \Log::info('Puntos de venta encontrados:', [
                'cantidad' => $puntosDeVenta->count()
            ]);

            $pagosPDV = [];

            foreach ($puntosDeVenta as $puntoDeVenta) {
                // Mapear el punto de venta a DTO (similar a _mapper.Map en .NET)
                $puntoDeVentaDTO = self::mapearPuntoDeVentaADTO($puntoDeVenta);
                
                // Crear un PagoPuntoDeVentaDTO vacío con el punto de venta
                // En .NET: new PagoPuntoDeVentaDTO(item)
                $pagoDTO = new PagoPuntoDeVentaDTO();
                $pagoDTO->PuntoDeVenta = $puntoDeVentaDTO;
                $pagoDTO->PagoPuntoDeVentaId = 0; // Nuevo, no existe aún
                $pagoDTO->Monto = 0; // Inicialmente cero
                $pagoDTO->CierreDiarioId = $sucursalId; // Se asignará después
                
                $pagosPDV[] = $pagoDTO;

                $pagoPunto = new PagoPuntoDeVenta();
                $pagoPunto->Monto = 0;
                $pagoPunto->CierreDiarioId = $CierreDiarioId;
                $pagoPunto->PuntoDeVentaId = $puntoDeVenta->PuntoDeVentaId;

                // Guardar el modelo en la base de datos
                $pagoPunto->save();
            }

            \Log::info('Pagos PDV generados:', ['cantidad' => count($pagosPDV)]);
            return $pagosPDV;

        } catch (\Exception $e) {
            \Log::error('Error en generarListaPagosPDV: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Método equivalente a _mapper.Map<PuntoDeVentaDTO>(item) en .NET
     */
    private static function mapearPuntoDeVentaADTO($puntoDeVenta): PuntoDeVentaDTO
    {
        $dto = new PuntoDeVentaDTO();
        
        // Mapear propiedades básicas
        $dto->PuntoDeVentaId = $puntoDeVenta->PuntoDeVentaId;
        $dto->Codigo = (string) ($puntoDeVenta->Codigo ?? '');
        $dto->Descripcion = (string) ($puntoDeVenta->Descripcion ?? '');
        $dto->SucursalId = $puntoDeVenta->SucursalId;
        $dto->Serial = (string) ($puntoDeVenta->Serial ?? '');
        $dto->EsActivo = (bool) ($puntoDeVenta->EsActivo ?? false);
        
        // Mapear relaciones (si existen)
        if ($puntoDeVenta->banco) {
            $dto->Banco = (string) ($puntoDeVenta->banco->Nombre ?? '');
            $dto->BancoId = $puntoDeVenta->BancoId;
        }
        
        if ($puntoDeVenta->sucursal) {
            $dto->Sucursal = self::mapearSucursalADTO($puntoDeVenta->sucursal);
        }
        
        return $dto;
    }

    /**
     * Método para mapear Sucursal a DTO
     */
    private static function mapearSucursalADTO($sucursal): SucursalDTO
    {
        // Si no hay sucursal, retornar DTO vacío
        if (!$sucursal) {
            return new SucursalDTO([]); // Pasar array vacío
        }
        
        // Crear array con los datos de la sucursal
        $data = [
            'ID' => $sucursal->ID ?? $sucursal->Id ?? null,
            'Nombre' => $sucursal->Nombre ?? '',
            'Direccion' => $sucursal->Direccion ?? null,
            'SerialImpresora' => $sucursal->SerialImpresora ?? null,
            'EsActiva' => (bool) ($sucursal->EsActiva ?? $sucursal->EsActivo ?? true),
            'Tipo' => $sucursal->Tipo ?? 0,
            'FechaCarga' => $sucursal->FechaCarga ?? null
        ];
        
        return new SucursalDTO($data);
    }

    // Buscar Proveedot
    public static function BuscarProveedor($proveedorId, $_enumDetalles)
    {
        $_proveedor = Proveedor::find($proveedorId);

        if (!$_proveedor)
        {
            switch ($_enumDetalles)
            {
                case 0:
                    break;
                // case EnumDetalleBusquedaProveedores.IncluirCabeceraFacturas:

                //     _proveedor.Facturas = await _context.Facturas.Where(d => d.ProveedorId == id).ToListAsync();

                //     break;
                // case EnumDetalleBusquedaProveedores.IncluirDetalleFacturas:

                //     _proveedor.Facturas = await _context.Facturas.Where(d => d.ProveedorId == id).ToListAsync();

                //     foreach (var item in _proveedor.Facturas)
                //     {
                //         item.FacturaDetalles = await _context.FacturaDetalles.Where(d => d.FacturaId == item.Id).ToListAsync();
                //     }
                //     break;
                default:
                    break;
            }
        }

        if (!$_proveedor)
        {
            // ProveedorDTO proveedorDTO = _mapper.Map<ProveedorDTO>(_proveedor);
            // return proveedorDTO;
        }

        return null;
    }

    public static function guardarGastosDiariosSucursal(CierreDiario $cierre, array $data, bool $esEdicion = false) 
    {
        $numeroOperacion = $esEdicion
            ? $data['numero_operacion'] ?? null
            : now()->format('YmdHi') . '-' . $cierre->SucursalId;

        // 1️⃣ Guardar la transacción
        $transaccion = Transaccion::create([
            'Descripcion' => $data['descripcion'],
            'Fecha' => $cierre->Fecha,
            'FormaDePago' => $data['forma_pago'],
            'MontoAbonado' => $data['monto_bsf'] ?? 0,
            'MontoDivisaAbonado' => $data['monto_usd'] ?? 0,
            'Observacion' => $data['observacion_gasto'] ?? null,
            'Tipo' => 3,  // Gasto de Caja
            'Estatus' => 2, // Pagado
            'NumeroOperacion' => $numeroOperacion,
            'SucursalId' => $cierre->SucursalId
        ]);

        // 2️⃣ Asociar la transacción al cierre diario (equivalente a GuardarGastosCierreDiario)
        TransaccionCierreDiario::create([
            'CierreDiarioId' => $cierre->CierreDiarioId,
            'TransaccionId' => $transaccion->ID
        ]);

        return $transaccion;
    }

    // Gastos de un Cierre
    public static function obtenerGastosPorCierre(int $cierreDiarioId): array
    {
        $gastos = TransaccionCierreDiario::where('CierreDiarioId', $cierreDiarioId)
            ->with('transaccion')
            ->get()
            ->map(function ($item) {
                $t = $item->transaccion;
                return [
                    'id' => $t->ID,
                    'descripcion' => $t->Descripcion,
                    'monto_usd' => (float) $t->MontoDivisaAbonado,
                    'monto_bsf' => (float) $t->MontoAbonado,
                    'forma_pago' => $t->FormaDePago,
                    'observacion' => $t->Observacion,
                ];
            })
            ->toArray();

        return $gastos;
    }

    public static function totalesPorBanco($cierres)
    {
        return $cierres
            ->pluck('pagosPuntoDeVenta') // colección de colecciones
            ->flatten() // todos los pagos en un solo array
            ->groupBy(fn($pago) => $pago->puntoDeVenta->banco->ID)
            ->map(fn($pagos, $bancoId) => [
                'BancoID' => $pagos->first()->puntoDeVenta->banco->ID,
                'Logo' => $pagos->first()->puntoDeVenta->banco->Logo,
                'Nombre' => $pagos->first()->puntoDeVenta->banco->Nombre,
                'TotalPagado' => $pagos->sum('Monto')
            ])->values();
    }

}