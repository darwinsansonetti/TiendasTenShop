<?php

namespace App\Services;

use App\Models\VentaDiariaTotalizada;
use App\Models\Transaccion;
use App\DTO\VentaDiariaDTO;
use Carbon\Carbon;

use Illuminate\Support\Collection;

class VentasService
{
    public function obtenerListadoVentasDiarias($fechas, $sucursalId, $incluirGastos)
    {
        $ventas = VentaDiariaTotalizada::with('sucursal')
            ->when($sucursalId, function ($q) use ($sucursalId) {
                $q->where('sucursal_id', $sucursalId);
            })
            ->whereBetween('fecha', [$fechas->fechaInicio, $fechas->fechaFin])
            ->orderBy('fecha')
            ->get();

        $detalle = $this->obtenerDetallesPorVentas($sucursalId, $incluirGastos, $ventas);

        return [
            'listaVentasDiarias' => $detalle
        ];
    }


    public function obtenerDetallesPorVentas(?int $sucursalId, bool $incluirGastos, Collection $listaVentas): array
    {
        $listaDetalleDTO = [];

        foreach ($listaVentas as $ventaDiaria) {

            // Map manual desde VentaDiariaTotalizada -> VentaDiariaDTO
            $ventaDiariaDTO = new VentaDiariaDTO();
            $ventaDiariaDTO->id = $ventaDiaria->Id;
            $ventaDiariaDTO->fecha = $ventaDiaria->Fecha;
            $ventaDiariaDTO->sucursalId = $ventaDiaria->SucursalId;
            $ventaDiariaDTO->cantidad = $ventaDiaria->Cantidad;
            $ventaDiariaDTO->costoDivisa = $ventaDiaria->CostoDivisa;
            $ventaDiariaDTO->totalDivisa = $ventaDiaria->TotalDivisa;
            $ventaDiariaDTO->totalBs = $ventaDiaria->TotalBs;
            $ventaDiariaDTO->saldo = $ventaDiaria->Saldo;
            $ventaDiariaDTO->usuarioId = $ventaDiaria->UsuarioId;
            $ventaDiariaDTO->proveedorId = $ventaDiaria->ProveedorId;

            if ($incluirGastos) {
                $filtroFecha = [
                    'fecha_inicio' => Carbon::parse($ventaDiaria->Fecha)->startOfDay(),
                    'fecha_fin'    => Carbon::parse($ventaDiaria->Fecha)->endOfDay(),
                ];

                $ventaDiariaDTO->listadoGastos = collect();

                //$tipos = ['Gasto', 'GastoCaja', 'PagoMercancia', 'PagoServicio'];
                $tipos = [2, 3, 0, 5];

                foreach ($tipos as $tipo) {
                    $gastos = $this->buscarTransacciones($tipo, $sucursalId, $filtroFecha);
                    if ($gastos && $gastos->isNotEmpty()) {
                        $ventaDiariaDTO->listadoGastos = $ventaDiariaDTO->listadoGastos->merge($gastos);
                    }
                }

                // Convertimos a array si tu DTO espera array
                $ventaDiariaDTO->listadoGastos = $ventaDiariaDTO->listadoGastos->toArray();
            }

            $listaDetalleDTO[] = $ventaDiariaDTO;
        }

        return $listaDetalleDTO;
    }

    public function buscarTransacciones(
    int $tipoTransaccion,   // <-- cambiar a int
    ?int $sucursalId,
    array $filtroFecha,
    ?int $proveedorId = null,
    ?string $estatusTransaccion = null
    ) {
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
}
