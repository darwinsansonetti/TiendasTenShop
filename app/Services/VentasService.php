<?php

namespace App\Services;

use App\Models\VentaDiariaTotalizada;
use App\Models\Transaccion;
use App\DTO\VentaDiariaDTO;
use Carbon\Carbon;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

            // ✅ AQUÍ ES DONDE VA
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

            $listaDetalleDTO[] = $ventaDiariaDTO;
        }

        return $listaDetalleDTO;
    }

    public function buscarTransacciones(int $tipoTransaccion, ?int $sucursalId, array $filtroFecha, ?int $proveedorId = null, ?string $estatusTransaccion = null) 
    {
        $query = Transaccion::query()
            ->where('Tipo', $tipoTransaccion)
            ->whereBetween('Fecha', [$filtroFecha['fecha_inicio'], $filtroFecha['fecha_fin']]);

        if ($sucursalId) {
            $query->where('SucursalId', $sucursalId);
        }

        if ($proveedorId) {
            $query->where('ProveedorId', $proveedorId);
        }

        if ($estatusTransaccion) {
            $query->where('Estatus', $estatusTransaccion);
        }

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
}
