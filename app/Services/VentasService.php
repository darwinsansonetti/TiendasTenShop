<?php

namespace App\Services;

use App\Models\VentaDiariaTotalizada;
use App\Models\Transaccion;
use App\DTO\VentaDiariaDTO;
use Carbon\Carbon;
use App\Models\Recepciones;
use App\Models\Transferencia;
use App\Models\Factura;
use App\Models\Contenedor;
use App\Models\Sucursal;

use App\DTO\TransaccionDTO;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VentasService
{
    public function obtenerListadoVentasDiarias($fechas, $sucursalId, $incluirGastos)
    {
        // 1️⃣ Traer ventas del período y sucursal
        $ventas = VentaDiariaTotalizada::with('sucursal')
            ->when($sucursalId, fn($q) => $q->where('SucursalId', $sucursalId))
            ->whereBetween('fecha', [$fechas->fechaInicio, $fechas->fechaFin])
            ->orderBy('fecha')
            ->get();


        // 2️⃣ Mapear a DTO
        $detalle = $this->obtenerDetallesPorVentas($sucursalId, $incluirGastos, $ventas);

        // 3️⃣ Totales globales
        $montoDivisaGlobal = array_sum(array_map(fn($v) => $v->getMontoDivisaDiario(), $detalle));
        $unidadesGlobalVendidas = array_sum(array_map(fn($v) => $v->getUnidadesVendidas(), $detalle));
        $costoDivisaGlobal = collect($detalle)->sum(fn($v) => $v->costoDivisa);

        // 4️⃣ Utilidades igual .NET
        $utilidadDivisaPeriodo = collect($detalle)->sum(fn($v) => $v->getUtilidadDivisaDiario());
        $utilidadBsPeriodo     = collect($detalle)->sum(fn($v) => $v->getUtilidadBsDiario());

        // 4️⃣ Asignar totales a cada DTO
        foreach ($detalle as $ventaDiariaDTO) {
            $ventaDiariaDTO->montoDivisaGlobal = $montoDivisaGlobal;
            $ventaDiariaDTO->unidadesGlobalVendidas = $unidadesGlobalVendidas;
        }

        // 5️⃣ Gastos y servicios (solo para sucursal específica)
        $gastosDivisaPeriodo = 0;
        $pagosServiciosDivisa = 0;        

        // UTILIDAD NETA
        $utilidadNetaPeriodo = $utilidadDivisaPeriodo; // default: todas las sucursales

        if ($sucursalId) { // solo cuando es una sucursal específica
            $tiposGastos = [2, 3, 0];
            $tiposServicios = [5];

            $gastosDivisaPeriodo = Transaccion::whereIn('Tipo', $tiposGastos)
                ->where('SucursalId', $sucursalId)
                ->whereBetween('Fecha', [$fechas->fechaInicio, $fechas->fechaFin])
                ->sum('MontoDivisaAbonado');

            $pagosServiciosDivisa = Transaccion::whereIn('Tipo', $tiposServicios)
                ->where('SucursalId', $sucursalId)
                ->whereBetween('Fecha', [$fechas->fechaInicio, $fechas->fechaFin])
                ->sum('MontoDivisaAbonado');

            $utilidadNetaPeriodo = $utilidadDivisaPeriodo - $gastosDivisaPeriodo - $pagosServiciosDivisa;
        }

        // 6️⃣ Calcular Margen Bruto
        $margenBrutoPeriodo = 0;
        if ($costoDivisaGlobal > 0) {
            $margenBrutoPeriodo = (($montoDivisaGlobal * 100) / $costoDivisaGlobal) - 100;
            $margenBrutoPeriodo = round($margenBrutoPeriodo, 2);
        }

        // 7️⃣ Calcular Margen Neto
        $margenNetoPeriodo = $margenBrutoPeriodo; // por defecto igual al bruto
        if ($sucursalId && $costoDivisaGlobal > 0) {
            $ingresoNeto = $montoDivisaGlobal - $gastosDivisaPeriodo - $pagosServiciosDivisa;
            $margenNetoPeriodo = (($ingresoNeto * 100) / $costoDivisaGlobal) - 100;
            $margenNetoPeriodo = round($margenNetoPeriodo, 2);
        }

        return [
            'listaVentasDiarias'        => $detalle,

            'UtilidadDivisaPeriodo'     => $utilidadDivisaPeriodo,
            'UtilidadBsPeriodo'         => $utilidadBsPeriodo,
            'UtilidadDivisaPeriodoDsp'  => number_format($utilidadDivisaPeriodo, 2, ',', '.'),
            'UtilidadBsPeriodoDsp'      => number_format($utilidadBsPeriodo, 2, ',', '.'),

            'UtilidadNetaPeriodo'        => $utilidadNetaPeriodo,
            'UtilidadNetaPeriodoDsp'     => number_format($utilidadNetaPeriodo, 2, ',', '.'),

            'MontoDivisaTotalPeriodo'   => $montoDivisaGlobal,
            'CostoDivisaPeriodo'        => $costoDivisaGlobal,
            'GastosDivisaPeriodo'       => $gastosDivisaPeriodo,
            'MontoPagosServiciosDivisa' => $pagosServiciosDivisa,

            'MargenBrutoPeriodo'        => $margenBrutoPeriodo,
            'MargenBrutoPeriodoDsp'     => number_format($margenBrutoPeriodo, 2, ',', '.'),

            'MargenNetoPeriodo'         => $margenNetoPeriodo,
            'MargenNetoPeriodoDsp'      => number_format($margenNetoPeriodo, 2, ',', '.'),
        ];
    }

    public function obtenerDetallesPorVentas(?int $sucursalId, bool $incluirGastos, Collection $listaVentas): array
    {
        $listaDetalleDTO = [];

        foreach ($listaVentas as $ventaDiaria) {
            $ventaDiariaDTO = new VentaDiariaDTO();
            $ventaDiariaDTO->id = $ventaDiaria->ID;
            $ventaDiariaDTO->fecha = $ventaDiaria->Fecha;
            $ventaDiariaDTO->sucursalId = $ventaDiaria->SucursalId;
            $ventaDiariaDTO->cantidad = $ventaDiaria->Cantidad;
            $ventaDiariaDTO->costoDivisa = $ventaDiaria->CostoDivisa;
            $ventaDiariaDTO->totalDivisa = $ventaDiaria->TotalDivisa;
            $ventaDiariaDTO->totalBs = $ventaDiaria->TotalBs;
            $ventaDiariaDTO->saldo = $ventaDiaria->Saldo;
            $ventaDiariaDTO->usuarioId = $ventaDiaria->UsuarioId;
            $ventaDiariaDTO->proveedorId = $ventaDiaria->ProveedorId;
            $ventaDiariaDTO->tasaDeCambio = $ventaDiaria->TasaDeCambio ?? 1;

            $ventaDiariaDTO->nombreSucursal = optional($ventaDiaria->sucursal)->Nombre ?? 'Sin sucursal';

            // 5️⃣ Incluir gastos si aplica
            if ($incluirGastos) {
                $filtroFecha = [
                    'fecha_inicio' => Carbon::parse($ventaDiaria->Fecha)->startOfDay(),
                    'fecha_fin'    => Carbon::parse($ventaDiaria->Fecha)->endOfDay(),
                ];

                $tipos = [2, 3, 0, 5]; // TipoTransaccion equivalente a .NET
                $ventaDiariaDTO->listadoGastos = [];

                foreach ($tipos as $tipo) {
                    $gastos = $this->buscarTransacciones($tipo, $sucursalId, $filtroFecha);
                    if ($gastos && $gastos->isNotEmpty()) {
                        $ventaDiariaDTO->listadoGastos = array_merge($ventaDiariaDTO->listadoGastos, $gastos->toArray());
                    }
                }
            }

            // 🔹 **Asignar Utilidad y Margen diario**
            $ventaDiariaDTO->utilidadDivisaDiario = $ventaDiariaDTO->getUtilidadDivisaDiario();
            $ventaDiariaDTO->utilidadBsDiario    = $ventaDiariaDTO->getUtilidadBsDiario();
            $ventaDiariaDTO->margenDivisaDiario  = $ventaDiariaDTO->getMargenDivisaDiario();
            //$ventaDiariaDTO->margenBsDiario      = $ventaDiariaDTO->getMargenBsDiario(); // opcional

            $listaDetalleDTO[] = $ventaDiariaDTO;
        }


        return $listaDetalleDTO;
    }

    // public function buscarTransacciones(int $tipoTransaccion, ?int $sucursalId, array $filtroFecha, ?int $proveedorId = null, ?string $estatusTransaccion = null) 
    // {
    //     $query = Transaccion::query()
    //         ->where('Tipo', $tipoTransaccion)
    //         ->whereBetween('Fecha', [$filtroFecha['fecha_inicio'], $filtroFecha['fecha_fin']]);

    //     if ($sucursalId) {
    //         $query->where('SucursalId', $sucursalId);
    //     }

    //     if ($proveedorId) {
    //         $query->whereHas('transaccionesProveedor', function($q) use ($proveedorId) {
    //             $q->where('ProveedorId', $proveedorId);
    //         });
    //     }

    //     if ($estatusTransaccion) {
    //         $query->where('Estatus', $estatusTransaccion);
    //     }

    //     // Log para depurar
    //     \Log::info('=== Buscar Transacciones ===', [
    //         'tipo' => $tipoTransaccion,
    //         'sucursal' => $sucursalId,
    //         'proveedor' => $proveedorId,
    //         'fecha_inicio' => isset($fechaInicio) ? $fechaInicio->format('Y-m-d H:i:s') : null,
    //         'fecha_fin' => isset($fechaFin) ? $fechaFin->format('Y-m-d H:i:s') : null,
    //         'sql' => $query->toSql(),
    //         'bindings' => $query->getBindings(),
    //     ]);

    //     return $query->get();
    // }

    public function buscarTransacciones(int $tipoTransaccion, ?int $sucursalId, array $filtroFecha, ?int $proveedorId = null, ?string $estatusTransaccion = null) 
    {
        // Extraer y parsear las fechas del array
        $fechaInicio = isset($filtroFecha['fecha_inicio']) 
            ? Carbon::parse($filtroFecha['fecha_inicio'])->startOfDay() 
            : null;
            
        $fechaFin = isset($filtroFecha['fecha_fin']) 
            ? Carbon::parse($filtroFecha['fecha_fin'])->endOfDay() 
            : null;

        $query = Transaccion::query()
            ->where('Tipo', $tipoTransaccion);

        // Aplicar filtro de fechas si ambas existen
        if ($fechaInicio && $fechaFin) {
            $query->whereBetween('Fecha', [$fechaInicio, $fechaFin]);
        } elseif ($fechaInicio) {
            $query->where('Fecha', '>=', $fechaInicio);
        } elseif ($fechaFin) {
            $query->where('Fecha', '<=', $fechaFin);
        }

        if ($sucursalId) {
            $query->where('SucursalId', $sucursalId);
        }

        if ($proveedorId) {
            $query->whereHas('transaccionesProveedor', function($q) use ($proveedorId) {
                $q->where('ProveedorId', $proveedorId);
            });
        }

        if ($estatusTransaccion) {
            $query->where('Estatus', $estatusTransaccion);
        }

        // Log para depurar
        \Log::info('=== Buscar Transacciones ===', [
            'tipo' => $tipoTransaccion,
            'sucursal' => $sucursalId,
            'proveedor' => $proveedorId,
            'fecha_inicio' => $fechaInicio ? $fechaInicio->format('Y-m-d H:i:s') : null,
            'fecha_fin' => $fechaFin ? $fechaFin->format('Y-m-d H:i:s') : null,
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
        ]);

        return $query->get();
    }

    public function borrarVentaDiaria(int $ventaId): bool
    {
        return DB::transaction(function () use ($ventaId) {

            // 1️⃣ Obtener la sucursal de la venta
            $venta = DB::table('Ventas')
                ->select('SucursalId')
                ->where('ID', $ventaId)
                ->first();

            if (!$venta) {
                return false; // No existe la venta
            }

            $sucursalId = $venta->SucursalId;

            // 2️⃣ Reponer existencias en ProductoSucursal
            DB::table('ProductoSucursal as Prod')
                ->join('VentaProductos as Det', function ($join) use ($ventaId, $sucursalId) {
                    $join->on('Prod.ProductoId', '=', 'Det.ProductoId')
                        ->where('Det.VentaId', '=', $ventaId)
                        ->where('Prod.SucursalId', '=', $sucursalId);
                })
                ->update([
                    'Prod.Existencia' => DB::raw('Prod.Existencia + Det.Cantidad')
                ]);

            // 3️⃣ Eliminar hijos
            DB::table('VentaProductos')->where('VentaId', $ventaId)->delete();
            DB::table('VentasVendedor')->where('VentaId', $ventaId)->delete();

            // 4️⃣ Eliminar venta principal
            DB::table('Ventas')->where('ID', $ventaId)->delete();

            return true;
        });
    }

    public function obtenerListadoVentasDiariasParaCerrar($filtroFecha, $sucursalId, $incluirGastos)
    {
        $ventas = VentaDiariaTotalizada::with('sucursal')
            ->when($sucursalId, fn($q) => $q->where('SucursalId', $sucursalId))
            ->where('Saldo', '>', 0)
            ->where('Fecha', '<=', $filtroFecha->fechaFin)
            ->where(function ($q) {
                $q->whereNull('Estatus')
                ->orWhere('Estatus', '!=', 4);
            })
            ->orderBy('Fecha')
            ->get();

        $detalle = $this->obtenerDetallesPorVentasBalance($sucursalId, $ventas);

        return [
            'ListaVentasDiarias' => $detalle
        ];
    }

    private function obtenerDetallesPorVentasBalance(?int $sucursalId, Collection $listaVentas, array $tiposGastos = [0,2,3,5]): array
    {
        $listaDetalleDTO = [];

        if ($listaVentas->isEmpty()) {
            return $listaDetalleDTO;
        }

        // 1️⃣ Obtener rango de fechas de todas las ventas
        $fechaInicio = $listaVentas->min('Fecha');
        $fechaFin    = $listaVentas->max('Fecha');

        // 2️⃣ Traer todos los gastos del período de una sola vez
        $gastosQuery = Transaccion::whereIn('Tipo', $tiposGastos)
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin]);

        if ($sucursalId) {
            $gastosQuery->where('SucursalId', $sucursalId);
        }

        $gastos = $gastosQuery->get();

        // 3️⃣ Agrupar los gastos por fecha para asignarlos fácilmente
        $gastosPorFecha = $gastos->groupBy(function($item) {
            return Carbon::parse($item->Fecha)->format('Y-m-d');
        });

        // 4️⃣ Mapear ventas a DTO
        foreach ($listaVentas as $venta) {
            $ventaDTO = new VentaDiariaDTO();
            $ventaDTO->id           = $venta->ID;
            $ventaDTO->fecha        = $venta->Fecha;
            $ventaDTO->sucursalId   = $venta->SucursalId;
            $ventaDTO->cantidad     = $venta->Cantidad;
            $ventaDTO->costoDivisa  = $venta->CostoDivisa;
            $ventaDTO->totalDivisa  = $venta->TotalDivisa;
            $ventaDTO->totalBs      = $venta->TotalBs;
            $ventaDTO->saldo        = $venta->Saldo;
            $ventaDTO->usuarioId    = $venta->UsuarioId;
            $ventaDTO->proveedorId  = $venta->ProveedorId;
            $ventaDTO->tasaDeCambio = $venta->TasaDeCambio ?? 1;
            $ventaDTO->nombreSucursal = optional($venta->sucursal)->Nombre ?? 'Sin sucursal';

            // 5️⃣ Asignar gastos del mismo día
            $fechaVenta = Carbon::parse($venta->Fecha)->format('Y-m-d');
            $ventaDTO->listadoGastos = $gastosPorFecha->get($fechaVenta, collect())->map(function($gasto) {
                $dto = new \App\DTO\TransaccionDTO();
                $dto->Id                = $gasto->ID;
                $dto->Descripcion       = $gasto->Descripcion ?? '';
                $dto->MontoAbonado      = (float) ($gasto->MontoAbonado ?? 0);
                $dto->MontoDivisaAbonado= (float) ($gasto->MontoDivisaAbonado ?? 0);
                $dto->SucursalId        = $gasto->SucursalId;
                $dto->Fecha             = $gasto->Fecha;
                return $dto;
            })->toArray();

            // 6️⃣ Calcular utilidad y margen diario
            $ventaDTO->utilidadDivisaDiario = $ventaDTO->getUtilidadDivisaDiario();
            $ventaDTO->utilidadBsDiario     = $ventaDTO->getUtilidadBsDiario();
            $ventaDTO->margenDivisaDiario   = $ventaDTO->getMargenDivisaDiario();

            $listaDetalleDTO[] = $ventaDTO;
        }

        return $listaDetalleDTO;
    }

    public function buscarRecepcionesSucursalParaCerrar(int $sucursalId, $fechaFin = null): array
    {
        try {
            // Cargar recepciones con sus relaciones
            $query = Recepciones::with([
                'detalles',
                'transacciones' => function($q) {
                    $q->where('Tipo', 7)
                    ->select('transacciones.ID', 'Descripcion', 'MontoDivisaAbonado', 'Fecha');
                }
            ])
            ->where('SucursalDestinoId', $sucursalId)
            ->whereNotIn('Estatus', [7, 8]); // Excluir Pagada y FinalizadaPagada

            // Aplicar filtro de fecha si existe
            if ($fechaFin !== null) {
                if (!$fechaFin instanceof Carbon) {
                    $fechaFin = Carbon::parse($fechaFin);
                }
                $query->where('FechaCreacion', '<=', $fechaFin);
            }

            $recepciones = $query->get();
            
            if ($recepciones->isEmpty()) {
                return [];
            }

            $recepcionesDTO = [];

            foreach ($recepciones as $recepcion) {
                // Calcular total: CantidadRecibida * CostoDivisa
                $totalDivisa = $recepcion->detalles->sum(function($detalle) {
                    return (float)$detalle->CantidadRecibida * (float)$detalle->CostoDivisa;
                });
                
                // Obtener abonos y calcular total abonado
                $abonos = $recepcion->transacciones;
                $totalAbonado = $abonos->sum('MontoDivisaAbonado');
                
                // Mapear abonos a DTO
                $abonosDTO = $abonos->map(function($abono) {
                    return [
                        'Id' => $abono->ID,
                        'Descripcion' => $abono->Descripcion,
                        'MontoDivisaAbonado' => (float)$abono->MontoDivisaAbonado,
                        'Fecha' => $abono->Fecha instanceof Carbon 
                            ? $abono->Fecha->format('Y-m-d H:i:s') 
                            : $abono->Fecha,
                    ];
                })->toArray();

                $saldoDivisa = round($totalDivisa - $totalAbonado, 2);

                // Solo incluir si hay saldo pendiente (considerando margen de redondeo)
                if ($saldoDivisa > 0.01) {
                    $recepcionesDTO[] = [
                        'RecepcionId' => $recepcion->RecepcionId,
                        'SucursalDestinoId' => $recepcion->SucursalDestinoId,
                        'FechaCreacion' => $recepcion->FechaCreacion instanceof Carbon 
                            ? $recepcion->FechaCreacion->format('Y-m-d H:i:s') 
                            : $recepcion->FechaCreacion,
                        'TotalDivisa' => round($totalDivisa, 2),
                        'SaldoDivisa' => $saldoDivisa,
                        'Abonos' => $abonosDTO,
                        'Estatus' => $recepcion->Estatus,
                    ];
                }
            }

            return $recepcionesDTO;

        } catch (\Exception $ex) {
            \Log::error('Error en buscarRecepcionesSucursalParaCerrar: ' . $ex->getMessage());
            \Log::error($ex->getTraceAsString());
            return [];
        }
    }

    public function buscarTransferenciasParaCerrar($filtroFecha, int $sucursalId): array
    {
        try {
            // Validar que $filtroFecha tenga fechaFin
            $fechaFin = null;
            if ($filtroFecha && property_exists($filtroFecha, 'fechaFin')) {
                $fechaFin = $filtroFecha->fechaFin;
            }

            // Construir consulta
            $query = Transferencia::where('SucursalOrigenId', $sucursalId)
                ->where('Saldo', '>', 0); // Solo transferencias con saldo pendiente

            // Aplicar filtro de fecha si existe
            if ($fechaFin) {
                if (!$fechaFin instanceof Carbon) {
                    $fechaFin = Carbon::parse($fechaFin);
                }
                $query->where('Fecha', '<=', $fechaFin);
            }

            // Ejecutar consulta
            $transferencias = $query->get();

            // Si no hay resultados, retornar array vacío
            if ($transferencias->isEmpty()) {
                return [];
            }

            // Convertir a DTO (array asociativo)
            return $this->generarListadoTransferencias($transferencias);

        } catch (\Exception $ex) {
            Log::error('Error en TransferenciaService@buscarTransferenciasParaCerrar: ' . $ex->getMessage());
            Log::error($ex->getTraceAsString());
            return [];
        }
    }

    private function generarListadoTransferencias($transferencias): array
    {
        $transferenciasDTO = [];

        foreach ($transferencias as $transferencia) {
            $transferenciasDTO[] = [
                'TransferenciaId' => $transferencia->TransferenciaId,
                'SucursalOrigenId' => $transferencia->SucursalOrigenId,
                'SucursalDestinoId' => $transferencia->SucursalDestinoId,
                'Fecha' => $transferencia->Fecha instanceof Carbon 
                    ? $transferencia->Fecha->format('Y-m-d H:i:s') 
                    : $transferencia->Fecha,
                'Monto' => (float)$transferencia->Monto,
                'Saldo' => (float)$transferencia->Saldo,
                'Estatus' => $transferencia->Estatus,
                // Agrega más campos según necesites
            ];
        }

        return $transferenciasDTO;
    }

    public function buscarFacturasActivas(): array
    {
        try {
            Log::info('=== VentasService@buscarFacturasActivas (Optimizado) ===');

            // 1. Obtener IDs de facturas con estatus 1, 2 y 4 (una sola consulta)
            $facturas = Factura::with(['proveedor', 'detalles'])
                ->whereIn('Estatus', [1, 2, 4]) // Activas, Adicionales y Otro estatus
                ->orderBy('FechaCreacion')
                ->get();

            if ($facturas->isEmpty()) {
                return [];
            }

            // 2. Obtener TODOS los abonos de todas las facturas en UNA SOLA CONSULTA
            $facturaIds = $facturas->pluck('ID')->toArray();
            
            $abonos = Transaccion::select(
                    'transacciones.ID',
                    'transacciones.Descripcion',
                    'transacciones.MontoAbonado',
                    'transacciones.MontoDivisaAbonado',
                    'transacciones.Fecha',
                    'transacciones.Tipo',
                    'tp.FacturaId'  // Obtener el FacturaId de la tabla pivote
                )
                ->join('TransaccionesProveedor as tp', 'transacciones.ID', '=', 'tp.TransaccionId')
                ->whereIn('tp.FacturaId', $facturaIds)
                ->get()
                ->groupBy('FacturaId'); // Agrupar por FacturaId

            Log::info('Facturas encontradas: ' . $facturas->count());
            Log::info('Abonos encontrados: ' . $abonos->count());

            // 3. Obtener IDs de contenedores y sucursales únicos
            $contenedorIds = $facturas->whereNotNull('ContenedorId')
                                      ->pluck('ContenedorId')
                                      ->unique()
                                      ->toArray();
            
            $sucursalIds = $facturas->pluck('SucursalId')
                                    ->unique()
                                    ->toArray();

            // 4. Cargar todos los contenedores en UNA SOLA CONSULTA
            $contenedores = [];
            if (!empty($contenedorIds)) {
                $contenedores = Contenedor::whereIn('Id', $contenedorIds)
                    ->get()
                    ->keyBy('Id');
            }

            // 5. Cargar todas las sucursales en UNA SOLA CONSULTA
            $sucursales = [];
            if (!empty($sucursalIds)) {
                $sucursales = Sucursal::whereIn('ID', $sucursalIds)
                    ->get()
                    ->keyBy('ID');
            }

            // 6. Construir el resultado final
            $facturasDTO = [];

            foreach ($facturas as $factura) {
                // Calcular totales desde detalles (similar a .NET)
                $totalDivisa = 0;
                $totalBs = 0;
                
                $detallesDTO = [];
                foreach ($factura->detalles as $detalle) {
                    $totalDivisa += (float)$detalle->CostoDivisa * $detalle->CantidadRecibida;
                    $totalBs += (float)$detalle->CostoBs * $detalle->CantidadRecibida;
                    
                    $detallesDTO[] = [
                        'ID' => $detalle->ID,
                        'ProductoId' => $detalle->ProductoId,
                        'CantidadEmitida' => $detalle->CantidadEmitida,
                        'CantidadRecibida' => $detalle->CantidadRecibida,
                        'CostoDivisa' => (float)$detalle->CostoDivisa,
                        'CostoBs' => (float)$detalle->CostoBs,
                        'SubtotalDivisa' => (float)$detalle->CostoDivisa * $detalle->CantidadRecibida,
                        'SubtotalBs' => (float)$detalle->CostoBs * $detalle->CantidadRecibida,
                    ];
                }

                // Procesar abonos de esta factura
                $abonosDTO = [];
                $totalAbonado = 0;
                
                if (isset($abonos[$factura->ID])) {
                    foreach ($abonos[$factura->ID] as $abono) {
                        $totalAbonado += (float)$abono->MontoDivisaAbonado;
                        $abonosDTO[] = [
                            'ID' => $abono->ID,
                            'Descripcion' => $abono->Descripcion,
                            'MontoAbonado' => (float)$abono->MontoAbonado,
                            'MontoDivisaAbonado' => (float)$abono->MontoDivisaAbonado,
                            'Fecha' => $abono->Fecha,
                            'Tipo' => $abono->Tipo,
                        ];
                    }
                }

                // Construir DTO de factura
                $facturaDTO = [
                    'ID' => $factura->ID,
                    'ProveedorId' => $factura->ProveedorId,
                    'SucursalId' => $factura->SucursalId,
                    'ContenedorId' => $factura->ContenedorId,
                    'Numero' => $factura->Numero,
                    'Serie' => $factura->Serie,
                    'FechaCreacion' => $factura->FechaCreacion,
                    'FechaDespacho' => $factura->FechaDespacho,
                    'FechaCierre' => $factura->FechaCierre,
                    'Estatus' => $factura->Estatus,
                    'Tipo' => $factura->Tipo,
                    'MontoDivisa' => (float)$factura->MontoDivisa ?: $totalDivisa,
                    'MontoBs' => (float)$factura->MontoBs ?: $totalBs,
                    'SaldoDivisa' => round(((float)$factura->MontoDivisa ?: $totalDivisa) - $totalAbonado, 2),
                    'Descripcion' => $factura->Descripcion,
                    'TasaDeCambio' => (float)$factura->TasaDeCambio,
                    'MonedaPrincipal' => $factura->MonedaPrincipal,
                    'Detalles' => $detallesDTO,
                    'Pagos' => $abonosDTO,
                    'Proveedor' => null,
                    'Sucursal' => null,
                    'Contenedor' => null,
                ];

                // Agregar proveedor si existe
                if ($factura->proveedor) {
                    $facturaDTO['Proveedor'] = [
                        'ID' => $factura->proveedor->ID,
                        'Nombre' => $factura->proveedor->Nombre,
                        'Rif' => $factura->proveedor->Rif,
                        'Tipo' => $factura->proveedor->Tipo,
                    ];
                }

                // Agregar sucursal si existe (de nuestra colección precargada)
                if (isset($sucursales[$factura->SucursalId])) {
                    $suc = $sucursales[$factura->SucursalId];
                    $facturaDTO['Sucursal'] = [
                        'ID' => $suc->ID,
                        'Nombre' => $suc->Nombre,
                        'Tipo' => $suc->Tipo,
                    ];
                }

                // Si el proveedor es tipo 0 y tiene contenedor, agregar contenedor
                if ($factura->proveedor && ($factura->proveedor->Tipo ?? 0) == 0 && $factura->ContenedorId) {
                    if (isset($contenedores[$factura->ContenedorId])) {
                        $cont = $contenedores[$factura->ContenedorId];
                        $facturaDTO['Contenedor'] = [
                            'Id' => $cont->Id,
                            'Nombre' => $cont->Nombre,
                            'FechaCreacion' => $cont->FechaCreacion,
                            'FechaDespacho' => $cont->FechaDespacho,
                            'FechaRecepcion' => $cont->FechaRecepcion,
                            'Flete' => (float)$cont->Flete,
                            'Aduana' => (float)$cont->Aduana,
                            'NumeroOperacion' => $cont->NumeroOperacion,
                            'Origen' => $cont->Origen,
                            'Estatus' => $cont->Estatus,
                        ];
                    }
                }

                $facturasDTO[] = $facturaDTO;
            }

            Log::info('Facturas DTO generadas: ' . count($facturasDTO));
            return $facturasDTO;

        } catch (\Exception $ex) {
            Log::error('Error en buscarFacturasActivas: ' . $ex->getMessage());
            Log::error($ex->getTraceAsString());
            return [];
        }
    }

    /**
     * Versión con filtros adicionales (si los necesitas)
     */
    public function buscarFacturasActivasConFiltros(?int $tipo = null, ?int $proveedorId = null): array
    {
        try {
            $query = Factura::with(['proveedor', 'detalles'])
                ->whereIn('Estatus', [1, 2, 4]);

            if ($tipo !== null) {
                $query->where('Tipo', $tipo);
            }

            if ($proveedorId !== null) {
                $query->where('ProveedorId', $proveedorId);
            }

            $facturas = $query->orderBy('FechaCreacion')->get();

            if ($facturas->isEmpty()) {
                return [];
            }

            // El resto del proceso es igual que arriba...
            // (Reutilizar la misma lógica de procesamiento)
            return $this->procesarFacturasConAbonos($facturas);

        } catch (\Exception $ex) {
            Log::error('Error en buscarFacturasActivasConFiltros: ' . $ex->getMessage());
            return [];
        }
    }

    /**
     * Método auxiliar para procesar facturas con sus abonos
     */
    private function procesarFacturasConAbonos($facturas): array
    {
        $facturaIds = $facturas->pluck('ID')->toArray();
        
        // Obtener todos los abonos en una sola consulta
        $abonos = Transaccion::select(
                'transacciones.ID',
                'transacciones.Descripcion',
                'transacciones.MontoAbonado',
                'transacciones.MontoDivisaAbonado',
                'transacciones.Fecha',
                'transacciones.Tipo',
                'tp.FacturaId'
            )
            ->join('TransaccionesProveedor as tp', 'transacciones.ID', '=', 'tp.TransaccionId')
            ->whereIn('tp.FacturaId', $facturaIds)
            ->get()
            ->groupBy('FacturaId');

        // Obtener contenedores y sucursales únicos
        $contenedorIds = $facturas->whereNotNull('ContenedorId')
                                  ->pluck('ContenedorId')
                                  ->unique()
                                  ->toArray();
        
        $sucursalIds = $facturas->pluck('SucursalId')
                                ->unique()
                                ->toArray();

        // Cargar contenedores y sucursales
        $contenedores = !empty($contenedorIds) 
            ? Contenedor::whereIn('Id', $contenedorIds)->get()->keyBy('Id') 
            : [];
        
        $sucursales = !empty($sucursalIds) 
            ? Sucursal::whereIn('ID', $sucursalIds)->get()->keyBy('ID') 
            : [];

        // Construir resultado
        $facturasDTO = [];
        foreach ($facturas as $factura) {
            $facturasDTO[] = $this->construirFacturaDTO(
                $factura, 
                $abonos[$factura->ID] ?? [], 
                $contenedores, 
                $sucursales
            );
        }

        return $facturasDTO;
    }

    /**
     * Construir DTO de una factura individual
     */
    private function construirFacturaDTO($factura, $abonos, $contenedores, $sucursales): array
    {
        // Calcular totales
        $totalDivisa = 0;
        $detallesDTO = [];
        
        foreach ($factura->detalles as $detalle) {
            $subtotalDivisa = (float)$detalle->CostoDivisa * $detalle->CantidadRecibida;
            $totalDivisa += $subtotalDivisa;
            
            $detallesDTO[] = [
                'ID' => $detalle->ID,
                'ProductoId' => $detalle->ProductoId,
                'CantidadRecibida' => $detalle->CantidadRecibida,
                'CostoDivisa' => (float)$detalle->CostoDivisa,
                'SubtotalDivisa' => $subtotalDivisa,
            ];
        }

        // Procesar abonos
        $abonosDTO = [];
        $totalAbonado = 0;
        
        foreach ($abonos as $abono) {
            $totalAbonado += (float)$abono->MontoDivisaAbonado;
            $abonosDTO[] = [
                'ID' => $abono->ID,
                'Descripcion' => $abono->Descripcion,
                'MontoDivisaAbonado' => (float)$abono->MontoDivisaAbonado,
                'Fecha' => $abono->Fecha,
            ];
        }

        // Construir DTO base
        $facturaDTO = [
            'ID' => $factura->ID,
            'ProveedorId' => $factura->ProveedorId,
            'SucursalId' => $factura->SucursalId,
            'ContenedorId' => $factura->ContenedorId,
            'Numero' => $factura->Numero,
            'FechaCreacion' => $factura->FechaCreacion,
            'Estatus' => $factura->Estatus,
            'Tipo' => $factura->Tipo,
            'MontoDivisa' => (float)$factura->MontoDivisa ?: $totalDivisa,
            'SaldoDivisa' => round(((float)$factura->MontoDivisa ?: $totalDivisa) - $totalAbonado, 2),
            'Detalles' => $detallesDTO,
            'Pagos' => $abonosDTO,
            'Proveedor' => $factura->proveedor ? [
                'ID' => $factura->proveedor->ID,
                'Nombre' => $factura->proveedor->Nombre,
                'Tipo' => $factura->proveedor->Tipo,
            ] : null,
            'Sucursal' => isset($sucursales[$factura->SucursalId]) ? [
                'ID' => $sucursales[$factura->SucursalId]->ID,
                'Nombre' => $sucursales[$factura->SucursalId]->Nombre,
            ] : null,
            'Contenedor' => null,
        ];

        // Agregar contenedor si aplica
        if ($factura->proveedor && ($factura->proveedor->Tipo ?? 0) == 0 && $factura->ContenedorId) {
            if (isset($contenedores[$factura->ContenedorId])) {
                $cont = $contenedores[$factura->ContenedorId];
                $facturaDTO['Contenedor'] = [
                    'Id' => $cont->Id,
                    'Nombre' => $cont->Nombre,
                    'NumeroOperacion' => $cont->NumeroOperacion,
                ];
            }
        }

        return $facturaDTO;
    }
}
