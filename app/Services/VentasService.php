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
use App\Models\Prestamo;
use App\Models\Sucursal;
use App\Models\AspNetUser;
use App\Helpers\ParametrosFiltroFecha;
use App\Helpers\GeneralHelper;

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

        \Log::info("FILTRO EN buscarTransacciones", [
            'tipo' => $tipoTransaccion,
            'fecha_inicio' => $fechaInicio ? $fechaInicio->toDateTimeString() : null,
            'fecha_fin' => $fechaFin ? $fechaFin->toDateTimeString() : null
        ]);

        $query = Transaccion::query()
            ->where('Tipo', $tipoTransaccion);

        // Aplicar filtro de fechas si ambas existen
        // if ($fechaInicio && $fechaFin) {
        //     $query->whereBetween('Fecha', [$fechaInicio, $fechaFin]);
        // } elseif ($fechaInicio) {
        //     $query->where('Fecha', '>=', $fechaInicio);
        // } elseif ($fechaFin) {
        //     $query->where('Fecha', '<=', $fechaFin);
        // }

        if ($fechaInicio && $fechaFin) {
            $query->where(function($q) use ($fechaInicio, $fechaFin) {
                $q->whereDate('Fecha', '=', $fechaInicio)
                ->orWhereDate('Fecha', '=', $fechaFin);
            });
        } elseif ($fechaInicio) {
            $query->whereDate('Fecha', '=', $fechaInicio);
        } elseif ($fechaFin) {
            $query->whereDate('Fecha', '=', $fechaFin);
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

        \Log::info("SQL ejecutado", [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        $resultados = $query->get();

        \Log::info("RESULTADOS buscarTransacciones tipo {$tipoTransaccion}", [
            'total' => $resultados->count(),
            'ids' => $resultados->pluck('ID')->toArray(),
            'fechas' => $resultados->map(function($item) {
                return [
                    'id' => $item->ID,
                    'fecha' => $item->Fecha ? Carbon::parse($item->Fecha)->toDateTimeString() : null
                ];
            })->toArray()
        ]);

        return $resultados;
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

    public function obtenerListadoVentasDiariasParaCerrarSinTotalizar($filtroFecha, $sucursalId, $incluirGastos)
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

    public function obtenerUltimaVentaDiariaTotalizada($sucursalId)
    {
        $venta = VentaDiariaTotalizada::with('sucursal')
            ->where('SucursalId', $sucursalId)
            ->where('Estatus', 4)
            ->where('Saldo', 0)
            ->where('TotalDivisa', '>', 0)
            ->orderByDesc('ID')
            ->first();

        return $venta;
    }

    public function ObtenerListadoVentasDiariasParaCerrar($filtroFecha, $sucursalId, $incluirGastos)
    {
        // $query = VentaDiariaTotalizada::query()
        //     ->when($sucursalId, fn($q) => $q->where('SucursalId', $sucursalId))
        //     ->where('TotalDivisa', '>', 0)
        //     ->where('Fecha', '<=', $filtroFecha->fechaFin);

        $fechaLimite = Carbon::parse('2021-12-01')->startOfDay();

        $query = VentaDiariaTotalizada::query()
            ->when($sucursalId, fn($q) => $q->where('SucursalId', $sucursalId))
            ->where('TotalDivisa', '>', 0)
            ->where('Fecha', '>=', $fechaLimite)
            ->where('Fecha', '<=', $filtroFecha->fechaFin);
        
        $totalVentas = $query->sum('TotalDivisa');
        $cantidadVentas = $query->count(); 

        return [
            'totales' => [
                'ventas_acumuladas' => round($totalVentas, 2),
                'cantidad_ventas' => $cantidadVentas,
            ]
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
            $fechaLimite = Carbon::parse('2021-12-01')->startOfDay();

            // ===== 1. Obtener TODAS las recepciones en el rango =====
            $queryRecepciones = Recepciones::where('SucursalDestinoId', $sucursalId);
            
            if ($fechaFin !== null) {
                $queryRecepciones->where('FechaCreacion', '>=', $fechaLimite);
                $queryRecepciones->where('FechaCreacion', '<=', $fechaFin);
            }
            
            $recepcionIds = $queryRecepciones->pluck('RecepcionId');
            
            if ($recepcionIds->isEmpty()) {
                return [
                    'total_historico' => 0,
                    'saldo_pendiente' => 0,
                    'cantidad_total' => 0,
                    'cantidad_pendiente' => 0,
                    'detalle' => []
                ];
            }

            // ===== 2. Calcular TOTAL DIVISA por recepción =====
            $totalesPorRecepcion = DB::table('RecepcionesDetalles')
                ->select('RecepcionId')
                ->selectRaw('SUM(CantidadRecibida * CostoDivisa) as total_divisa')
                ->whereIn('RecepcionId', $recepcionIds)
                ->groupBy('RecepcionId')
                ->get()
                ->keyBy('RecepcionId');

            // ===== 3. Calcular TOTAL ABONADO por recepción =====
            $abonosPorRecepcion = DB::table('TransaccionesRecepciones as tr')
                ->join('Transacciones as t', 'tr.TransaccionId', '=', 't.ID')
                ->select('tr.RecepcionId')
                ->selectRaw('SUM(t.MontoDivisaAbonado) as total_abonado')
                ->whereIn('tr.RecepcionId', $recepcionIds)
                ->where('t.Tipo', 7)
                ->groupBy('tr.RecepcionId')
                ->get()
                ->keyBy('RecepcionId');

            // ===== 4. Calcular totales =====
            $totalHistorico = 0;
            $saldoPendiente = 0;
            $cantidadPendiente = 0;
            $detalle = [];

            foreach ($recepcionIds as $id) {
                $totalDivisa = (float)($totalesPorRecepcion[$id]->total_divisa ?? 0);
                $totalAbonado = (float)($abonosPorRecepcion[$id]->total_abonado ?? 0);
                $saldo = round($totalDivisa - $totalAbonado, 2);
                
                // Sumar al total histórico
                $totalHistorico += $totalDivisa;
                
                // Si tiene saldo pendiente
                if ($saldo > 0.01) {
                    $saldoPendiente += $saldo;
                    $cantidadPendiente++;
                    
                    $detalle[] = [
                        'RecepcionId' => $id,
                        'TotalDivisa' => round($totalDivisa, 2),
                        'SaldoDivisa' => $saldo,
                    ];
                }
            }

            // dd([
            //     'total_historico' => round($totalHistorico, 2),    // 💰 Suma de TODAS las recepciones
            //     'saldo_pendiente' => round($saldoPendiente, 2),    // 💰 Solo lo que aún deben
            //     'cantidad_total' => $recepcionIds->count(),        // 📦 Total de recepciones
            //     'cantidad_pendiente' => $cantidadPendiente,        // 📦 Solo las pendientes
            //     'detalle' => $detalle,                              // 📋 Detalle de las pendientes
            // ]);

            return [
                'total_historico' => round($totalHistorico, 2),    // 💰 Suma de TODAS las recepciones
                'saldo_pendiente' => round($saldoPendiente, 2),    // 💰 Solo lo que aún deben
                'cantidad_total' => $recepcionIds->count(),        // 📦 Total de recepciones
                'cantidad_pendiente' => $cantidadPendiente,        // 📦 Solo las pendientes
                'detalle' => $detalle,                              // 📋 Detalle de las pendientes
            ];

        } catch (\Exception $ex) {
            \Log::error('Error: ' . $ex->getMessage());
            return [
                'total_historico' => 0,
                'saldo_pendiente' => 0,
                'cantidad_total' => 0,
                'cantidad_pendiente' => 0,
                'detalle' => []
            ];
        }
    }

    public function buscarTransferenciasParaCerrar($filtroFecha, int $sucursalId): array
    {
        try {
            $fechaFin = null;
            $fechaLimite = Carbon::parse('2021-12-01')->startOfDay();

            if ($filtroFecha && property_exists($filtroFecha, 'fechaFin')) {
                $fechaFin = $filtroFecha->fechaFin;
            }

            // ===== 1. Obtener TODAS las transferencias =====
            $query = Transferencia::with('detalles.producto')  // Cargar detalles y productos
                ->where('SucursalOrigenId', $sucursalId);

            if ($fechaFin) {
                $query->where('Fecha', '>=', $fechaLimite);
                $query->where('Fecha', '<=', $fechaFin);
            }

            $transferencias = $query->get();

            if ($transferencias->isEmpty()) {
                return [];
            }

            return $this->generarListadoTransferencias($transferencias);

        } catch (\Exception $ex) {
            Log::error('Error en buscarTransferenciasParaCerrar: ' . $ex->getMessage());
            return [];
        }
    }

    private function generarListadoTransferencias($transferencias): array
    {
        $transferenciasDTO = [];

        foreach ($transferencias as $transferencia) {
            // ===== Calcular MONTO total de la transferencia =====
            $montoCalculado = 0;
            
            foreach ($transferencia->detalles as $detalle) {
                // Usar CantidadRecibida (o CantidadEmitida si prefieres)
                $cantidad = (float)($detalle->CantidadRecibida ?? $detalle->CantidadEmitida ?? 0);
                
                // Obtener costo del producto
                $costoDivisa = 0;
                if ($detalle->producto) {
                    $costoDivisa = (float)($detalle->producto->CostoDivisa ?? 0);
                }
                
                $montoCalculado += $cantidad * $costoDivisa;
            }

            $transferenciasDTO[] = [
                'TransferenciaId' => $transferencia->TransferenciaId,
                'SucursalOrigenId' => $transferencia->SucursalOrigenId,
                'SucursalDestinoId' => $transferencia->SucursalDestinoId,
                'Fecha' => $transferencia->Fecha instanceof Carbon 
                    ? $transferencia->Fecha->format('Y-m-d H:i:s') 
                    : $transferencia->Fecha,
                'MontoCalculado' => round($montoCalculado, 2),  // ✅ Monto histórico real
                'Saldo' => (float)$transferencia->Saldo,        // Saldo actual pendiente
                'Estatus' => $transferencia->Estatus,
                'CantidadProductos' => $transferencia->detalles->count(),
            ];
        }

        return $transferenciasDTO;
    }

    public function buscarFacturasActivas(): array
    {
        try {

            // 1️⃣ Obtener todas las facturas activas (1,2,4)
            $facturas = Factura::with(['proveedor', 'detalles'])
                ->whereIn('Estatus', [1, 2, 4])
                ->orderBy('FechaCreacion')
                ->get();

            if ($facturas->isEmpty()) {
                return [];
            }

            // dd($facturas);

            // 2️⃣ Obtener TODOS los abonos en una sola consulta
            $facturaIds = $facturas->pluck('ID')->toArray();

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

            $resultado = [];

            // 3️⃣ Recorrer cada factura (igual que foreach en .NET)
            foreach ($facturas as $factura) {

                $totalDivisa = 0;

                // Mercancia
                if($factura->Tipo == 0){
                    // 🔹 Calcular TotalDivisa
                    $totalDivisa = $factura->detalles->sum(function ($detalle) {
                        return $detalle->CantidadEmitida * $detalle->CostoDivisa;
                    });
                    
                    $totalDivisa += $factura->Traspaso ?? 0;
                }

                // Servicio
                if($factura->Tipo == 1){
                    // 🔹 Calcular TotalDivisa
                    $totalDivisa = $factura->MontoDivisa;
                }

                // 🔹 Obtener abonos de esta factura
                $totalAbonadoDivisa = 0;
                $abonosDTO = [];

                if (isset($abonos[$factura->ID])) {
                    foreach ($abonos[$factura->ID] as $abono) {
                        $totalAbonadoDivisa += (float) $abono->MontoDivisaAbonado;

                        $abonosDTO[] = [
                            'ID' => $abono->ID,
                            'Descripcion' => $abono->Descripcion,
                            'MontoDivisaAbonado' => (float) $abono->MontoDivisaAbonado,
                            'Fecha' => $abono->Fecha,
                            'Tipo' => $abono->Tipo,
                        ];
                    }
                }

                // 🔹 Saldo
                $totalSaldoDivisa = $totalDivisa - $totalAbonadoDivisa;

                // 🔹 Agregar al resultado
                $resultado[] = [
                    'FacturaId' => $factura->ID,
                    'Factura' => $factura,
                    'ProveedorId' => $factura->ProveedorId,
                    'TotalDivisa' => round($totalDivisa, 2),
                    'TotalAbonadoDivisa' => round($totalAbonadoDivisa, 2),
                    'TotalSaldoDivisa' => round($totalSaldoDivisa, 2),
                    'Abonos' => $abonosDTO
                ];
            }

            // dd($resultado);

            return $resultado;

        } catch (\Exception $ex) {
            Log::error('Error en buscarFacturasActivas: ' . $ex->getMessage());
            Log::error($ex->getTraceAsString());
            return [];
        }
    }
    
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

    public function obtenerVentasDiariasParaCerrarSinTotalizarEnPeriodo($sucursalId, $incluirGastos, $filtro)
    {
        
        $fechaInicio = $filtro instanceof ParametrosFiltroFecha 
            ? $filtro->fechaInicio 
            : Carbon::parse($filtro['fecha_inicio'] ?? $filtro);
        
        $fechaFin = $filtro instanceof ParametrosFiltroFecha 
            ? $filtro->fechaFin 
            : Carbon::parse($filtro['fecha_fin'] ?? $filtro);
        
        $ventas = VentaDiariaTotalizada::with('sucursal')
            ->when($sucursalId, fn($q) => $q->where('SucursalId', $sucursalId))
            ->whereBetween('Fecha', [$fechaInicio, $fechaFin]) 
            ->where('Saldo', '>', 0)
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
    
    public function obtenerVentasDiariasParaCerrarSinTotalizar($sucursalId, $incluirGastos)
    {
        $ventas = VentaDiariaTotalizada::with('sucursal')
            ->when($sucursalId, fn($q) => $q->where('SucursalId', $sucursalId))
            ->where('Saldo', '>', 0)
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
    

    public function BuscarPrestamosActivos($filtroFecha) 
    {        
        // Buscar préstamos nuevos (estatus 1)
        $prestamosNuevos = $this->buscarListadoPrestamos(null, $filtroFecha, 1);
        
        // Buscar préstamos en proceso (estatus 2)
        $prestamosEnProceso = $this->buscarListadoPrestamos(null, $filtroFecha, 2);
        
        // Combinar listas
        $todosPrestamos = collect();
        
        if ($prestamosNuevos->isNotEmpty()) {
            $todosPrestamos = $todosPrestamos->concat($prestamosNuevos);
        }
        
        if ($prestamosEnProceso->isNotEmpty()) {
            $todosPrestamos = $todosPrestamos->concat($prestamosEnProceso);
        }
        
        // Para cada préstamo, cargar sus pagos (cuando implementes esa función)
        if ($todosPrestamos->isNotEmpty()) {
            foreach ($todosPrestamos as $prestamo) {
                $prestamo->listaPagos = $this->buscarPagosPrestamo($prestamo->prestamoId);
            }
        }
        
        return $todosPrestamos;
    }

    public function buscarListadoPrestamos($usuarioId = null, $filtroFecha = null, $estatus = null)
    {
        $query = Prestamo::with(['detalles']);
        
        // Filtro por estatus
        if (!is_null($estatus)) {
            $query->where('Estatus', $estatus);
        }
        
        // Filtro por usuario
        if (!is_null($usuarioId) && !empty($usuarioId)) {
            $query->where('UsuarioId', $usuarioId);
        }
        
        // Filtro por fechas (como en .NET: Fecha >= inicio AND Fecha <= fin)
        if ($filtroFecha && isset($filtroFecha->fechaInicio) && isset($filtroFecha->fechaFin)) {
            $fechaInicio = $filtroFecha->fechaInicio->startOfDay();
            $fechaFin = $filtroFecha->fechaFin->endOfDay();
            // $fechaFin = $filtroFecha->fechaFin->startOfDay();            
            
            $query->where('Fecha', '>=', $fechaInicio)
                  ->where('Fecha', '<=', $fechaFin);
        }
        
        // Ejecutar consulta (AsNoTracking equivalente)
        $prestamosModel = $query->get();
        
        // Construir DTOs
        return $this->construirListaPrestamos($prestamosModel);
    }

    protected function construirListaPrestamos($listaPrestamoModel)
    {
        $listaPrestamoDTO = collect();
        
        if ($listaPrestamoModel->isEmpty()) {
            return $listaPrestamoDTO;
        }

        // Obtener tasa del día (una sola vez para todos)
        $tasa = GeneralHelper::obtenerTasaCambioDiaria(now());
        $tasaDia = $tasa['DivisaValor']['Valor'];
        
        foreach ($listaPrestamoModel as $item) {
            // Mapear modelo a DTO
            $prestamoDTO = $this->mapearPrestamoADTO($item);
            
            // Buscar usuario
            if ($item->UsuarioId) {

                $usuario = AspNetUser::find($item->UsuarioId);
                $prestamoDTO->usuario = $usuario ? $usuario->toArray() : null;
            }
            
            // Asignar tasa de cambio del día
            if ($tasaDia) {
                $prestamoDTO->divisaValor = $tasaDia ?? 0;
                // Calcular monto en Bs si es necesario
                $prestamoDTO->montoBs = $prestamoDTO->montoDivisa * $prestamoDTO->divisaValor;
            }
            
            $listaPrestamoDTO->push($prestamoDTO);
        }
        
        return $listaPrestamoDTO;
    }

    protected function mapearPrestamoADTO($model)
    {
        $dto = new \stdClass();
        
        // Propiedades básicas
        $dto->prestamoId = $model->PrestamoId ?? $model->Id; // Ajusta según tu campo real
        $dto->usuarioId = $model->UsuarioId;
        $dto->fecha = $model->Fecha;
        $dto->fechaCierre = $model->FechaCierre ?? null;
        $dto->observacion = $model->Observacion ?? $model->Descripcion ?? '';
        
        // Montos
        $dto->montoDivisa = (float)($model->MontoDivisa ?? 0);
        $dto->montoBs = (float)($model->MontoBs ?? 0);
        
        // Enums y IDs
        $dto->estatus = (int)($model->Estatus ?? 0);
        $dto->tipo = (int)($model->Tipo ?? 0);
        $dto->sucursalId = (int)($model->SucursalId ?? 0);
        $dto->tasaCambioId = (int)($model->TasaCambioId ?? 0);
        
        // Relaciones
        $dto->prestamosDetalles = $model->prestamosDetalles ?? collect(); // Relación
        $dto->listaPagos = collect(); // Se llenará después con buscarPagosPrestamo
        $dto->pago = null; // TransaccionDTO individual
        $dto->ultimoAbono = null; // Último abono
        
        // Datos adicionales que se llenarán después
        $dto->usuario = null; // UsuarioRegistradoIdentity
        $dto->divisaValor = null; // DivisaValorDTO
        $dto->productoSeleccionado = null; // ProductoDTO
        
        return $dto;
    }

    public function buscarPagosPrestamo($prestamoId)
    {
        // En .NET: from abonos in _context.Transacciones
        //         where abonos.TransaccionesUsuario.Any(pro => pro.PrestamoId == _prestamoId)
        //         orderby abonos.Fecha descending
        //         select abonos
        
        $pagos = DB::table('Transacciones as t')
            ->join('TransaccionesUsuario as tu', 't.ID', '=', 'tu.TransaccionId')
            ->where('tu.PrestamoId', $prestamoId)
            ->orderBy('t.Fecha', 'desc')
            ->select('t.*')
            ->get();
        
        // Convertir a DTOs (similar al _mapper.Map<TransaccionDTO>(item))
        $pagosDTO = collect();
        
        foreach ($pagos as $pago) {
            $pagoDTO = $this->mapearTransaccionADTO($pago);
            $pagosDTO->push($pagoDTO);
        }
        
        return $pagosDTO;
    }

    protected function mapearTransaccionADTO($transaccion)
    {
        $dto = new TransaccionDTO();
        
        // Propiedades básicas
        $dto->Id = $transaccion->TransaccionId ?? $transaccion->Id ?? 0;
        $dto->Descripcion = $transaccion->Descripcion ?? '';
        $dto->MontoAbonado = (float)($transaccion->MontoAbonado ?? 0);
        $dto->MontoDivisaAbonado = (float)($transaccion->MontoDivisaAbonado ?? $transaccion->MontoDivisa ?? 0);
        $dto->NumeroOperacion = $transaccion->NumeroOperacion ?? '';
        $dto->Nombre = $transaccion->Nombre ?? '';
        $dto->Cedula = $transaccion->Cedula ?? '';
        
        // IDs y relaciones
        $dto->CategoriaId = $transaccion->CategoriaId ?? null;
        $dto->SucursalId = $transaccion->SucursalId ?? null;
        $dto->SucursalOrigenId = $transaccion->SucursalOrigenId ?? null;
        $dto->DivisaId = $transaccion->DivisaId ?? 0;
        $dto->TasaDeCambio = (float)($transaccion->TasaDeCambio ?? 0);
        $dto->PrestamoId = $transaccion->PrestamoId ?? 0;
        $dto->FacturaId = $transaccion->FacturaId ?? 0;
        
        // Enums (valores numéricos)
        $dto->Tipo = (int)($transaccion->Tipo ?? 0);
        $dto->FormaDePago = (int)($transaccion->FormaDePago ?? 0);
        $dto->Estatus = (int)($transaccion->Estatus ?? 0);
        
        // Texto
        $dto->UrlComprobante = $transaccion->UrlComprobante ?? '';
        $dto->Observacion = $transaccion->Observacion ?? '';
        
        // Fecha
        $dto->Fecha = Carbon::parse($transaccion->Fecha ?? now());
        
        // Arrays vacíos
        $dto->AbonoVentas = [];
        
        // Relaciones (se pueden cargar después si es necesario)
        $dto->Categoria = null;
        $dto->Sucursal = null;
        $dto->SucursalOrigen = null;
        $dto->Prestamo = null;
        $dto->Factura = null;
        $dto->proveedor = null;
        
        return $dto;
    }

    public function buscarPagosPrestamoPorFecha($filtroFecha) 
    {        
        // Query base
        $query = DB::table('Transacciones as t')
            ->where('t.Tipo', 4); // EnumTipoTransaccion.AbonoPrestamo = 4
        
        // Aplicar filtro de fecha si existe
        if ($filtroFecha) {
            // En .NET: abonos.Fecha == _filtroFecha.FechaInicio OR abonos.Fecha == _filtroFecha.FechaFin
            $fechaInicio = $filtroFecha->fechaInicio ?? null;
            $fechaFin = $filtroFecha->fechaFin ?? null;
            
            if ($fechaInicio && $fechaFin) {
                // Si fechaInicio y fechaFin son iguales (mismo día)
                if ($fechaInicio->format('Y-m-d') == $fechaFin->format('Y-m-d')) {
                    // Buscar transacciones de ese día específico
                    $query->whereDate('t.Fecha', '=', $fechaInicio->format('Y-m-d'));
                } else {
                    // Si son diferentes, buscar las que coincidan con cualquiera de las dos fechas
                    $query->where(function($q) use ($fechaInicio, $fechaFin) {
                        $q->whereDate('t.Fecha', '=', $fechaInicio->format('Y-m-d'))
                        ->orWhereDate('t.Fecha', '=', $fechaFin->format('Y-m-d'));
                    });
                }
            }
        }
        
        // Order by fecha descending (como en .NET)
        $query->orderBy('t.Fecha', 'desc');
        
        // Ejecutar consulta
        $pagos = $query->get();
        
        // Convertir a DTOs
        $pagosDTO = [];
        
        foreach ($pagos as $pago) {
            $pagosDTO[] = $this->mapearTransaccionADTO($pago);
        }
        
        return $pagosDTO;
    }
}
