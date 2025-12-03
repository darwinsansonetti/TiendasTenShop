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
            $fechaFin = Carbon::create($anio, $mes, 1)->endOfMonth()->endOfDay();

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

}