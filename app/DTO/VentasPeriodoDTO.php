<?php

namespace App\DTO;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class VentasPeriodoDTO
{
    // Entrada
    public array $raw; // todo el array devuelto por generarDatosVentas()
    public Collection $listaVentasDiarias; // colección de ventas diarias (cada item es array)

    // Datos externos / configurables
    public int $SucursalId = 0;
    public string $culture = 'es-VE';

    // Campos que en .NET eran asignados externamente
    public int $UnidadesGlobalVendidas = 0;
    public float $MontoDivisasGlobal = 0.0;
    public float $MontoCostoGlobal = 0.0;

    public function __construct(array $ventasPeriodo)
    {
        $this->raw = $ventasPeriodo;

        $lista = $ventasPeriodo['ListaVentasDiarias'] ?? [];
        // convertimos cada venta diaria a colección/array estándar
        $this->listaVentasDiarias = collect($lista)->map(function ($v) {
            // asegurar campos numéricos
            $v['TasaDeCambio'] = isset($v['TasaDeCambio']) ? (float)$v['TasaDeCambio'] : 0.0;
            $v['TotalBS'] = isset($v['TotalBS']) ? (float)$v['TotalBS'] : 0.0;
            $v['TotalDivisa'] = isset($v['TotalDivisa']) ? (float)$v['TotalDivisa'] : 0.0;

            // listado de productos (si viene como array) — convertir a collection
            $v['ListadoProductosVentaDiaria'] = isset($v['ListadoProductosVentaDiaria'])
                ? collect($v['ListadoProductosVentaDiaria'])->map(function ($p) {
                    // normalizar campos del producto
                    $p['Cantidad'] = isset($p['Cantidad']) ? (float)$p['Cantidad'] : 0.0;
                    $p['PrecioVenta'] = isset($p['PrecioVenta']) ? (float)$p['PrecioVenta'] : 0.0;
                    $p['MontoDivisa'] = isset($p['MontoDivisa']) ? (float)$p['MontoDivisa'] : 0.0;
                    return $p;
                })->toArray()
                : [];

            // algunos nombres diferentes entre .NET y lo que generamos:
            // aseguramos propiedades usadas por cálculos (nombres compatibles)
            $v['MontoBsDiario'] = $v['TotalBS'] ?? 0.0;
            $v['MontoDivisaDiario'] = $v['TotalDivisa'] ?? 0.0;

            // campos que .NET usaba (si no existen, 0)
            $v['CostoBsDiario'] = $v['CostoBsDiario'] ?? 0.0;
            $v['CostoTotalDivisa'] = $v['CostoTotalDivisa'] ?? 0.0;
            $v['UtilidadDivisaDiario'] = $v['UtilidadDivisaDiario'] ?? 0.0;
            $v['UtilidadBsDiario'] = $v['UtilidadBsDiario'] ?? 0.0;
            $v['ComisionesBs'] = $v['ComisionesBs'] ?? 0.0;
            $v['ComisionesDivisas'] = $v['ComisionesDivisas'] ?? 0.0;
            $v['UnidadesVendidas'] = isset($v['ListadoProductosVentaDiaria']) ? array_sum(array_column($v['ListadoProductosVentaDiaria'], 'Cantidad')) : 0;

            // Promedios/ratios que .NET usaba (si hace falta)
            $v['PromedioMontoPorFactura'] = $v['PromedioMontoPorFactura'] ?? ($v['MontoBsDiario'] ?? 0.0);
            $v['PromedioMontoDivisaPorFactura'] = $v['PromedioMontoDivisaPorFactura'] ?? ($v['MontoDivisaDiario'] ?? 0.0);

            return $v;
        });
    }

    /* -------------------------
       Métodos calculados (equivalentes a .NET)
       ------------------------- */

    public function getFechaInicio(): ?string
    {
        return $this->raw['FechaInicio'] ?? null;
    }

    public function getFechaFin(): ?string
    {
        return $this->raw['FechaFin'] ?? null;
    }

    // Costo en divisa del periodo (suma de costo por dia)
    public function getCostoDivisaPeriodo(): float
    {
        return (float) $this->listaVentasDiarias->sum(fn($v) => (float)($v['CostoTotalDivisa'] ?? $v['CostoTotalDivisa'] ?? 0));
    }

    public function getCostoDivisaPeriodoDsp(): string
    {
        return number_format($this->getCostoDivisaPeriodo(), 2, ',', '.');
    }

    // Lista items periodo (suma por producto)
    public function getListaItemsPeriodo(): ?array
    {
        $acum = [];

        foreach ($this->listaVentasDiarias as $venta) {
            foreach ($venta['ListadoProductosVentaDiaria'] as $prod) {
                if ($prod === null) continue;

                $pid = $prod['ProductoId'] ?? $prod['Producto']['Id'] ?? null;
                if ($pid === null) continue;

                if (!isset($acum[$pid])) {
                    $acum[$pid] = [
                        'ProductoId' => $pid,
                        'Id' => $prod['Producto']['Id'] ?? null,
                        'Codigo' => $prod['Producto']['Codigo'] ?? null,
                        'Descripcion' => $prod['Producto']['Descripcion'] ?? null,
                        'Cantidad' => 0.0,
                        'PrecioVenta' => 0.0,
                        'MontoDivisa' => 0.0,
                        'Producto' => $prod['Producto'] ?? null,
                    ];
                }

                $acum[$pid]['Cantidad'] += (float)($prod['Cantidad'] ?? 0);
                $acum[$pid]['PrecioVenta'] += (float)($prod['PrecioVenta'] ?? 0);
                $acum[$pid]['MontoDivisa'] += (float)($prod['MontoDivisa'] ?? 0);
            }
        }

        // devolver como array indexado
        return array_values($acum);
    }

    public function getUnidadesVendidas(): int
    {
        return (int) $this->listaVentasDiarias->sum(fn($v) => (int)($v['UnidadesVendidas'] ?? 0));
    }

    public function getMontoDivisaTotalPeriodo(): float
    {
        return (float) $this->listaVentasDiarias->sum(fn($v) => (float)($v['MontoDivisaDiario'] ?? $v['TotalDivisa'] ?? 0));
    }

    public function getMontoBsPeriodo(): float
    {
        return (float) $this->listaVentasDiarias->sum(fn($v) => (float)($v['MontoBsDiario'] ?? $v['TotalBS'] ?? 0));
    }

    public function getCostoBsPeriodoDsp(): string
    {
        $total = (float) $this->listaVentasDiarias->sum(fn($v) => (float)($v['CostoBsDiario'] ?? 0));
        return number_format($total, 2, ',', '.');
    }

    public function getUtilidadDivisaPeriodo(): float
    {
        return (float) $this->listaVentasDiarias->sum(fn($v) => (float)($v['UtilidadDivisaDiario'] ?? 0));
    }

    public function getUtilidadBsPeriodo(): float
    {
        return (float) $this->listaVentasDiarias->sum(fn($v) => (float)($v['UtilidadBsDiario'] ?? 0));
    }

    public function getUtilidadDivisaPeriodoDsp(): string
    {
        return number_format($this->getUtilidadDivisaPeriodo(), 2, ',', '.');
    }

    public function getUtilidadBsPeriodoDsp(): string
    {
        return number_format($this->getUtilidadBsPeriodo(), 2, ',', '.');
    }

    public function getListaItemsTopTenPeriodo(): ?array
    {
        $items = $this->getListaItemsPeriodo();
        if (empty($items)) return null;

        usort($items, fn($a, $b) => $b['Cantidad'] <=> $a['Cantidad']);
        return array_slice($items, 0, 10);
    }

    public function getMontoTopTenPeriodo(): ?array
    {
        $items = $this->getListaItemsPeriodo();
        if (empty($items)) return null;

        usort($items, fn($a, $b) => $b['PrecioVenta'] <=> $a['PrecioVenta']);
        return array_slice($items, 0, 10);
    }

    public function getTasaDeCambioPromedio(): float
    {
        $count = $this->listaVentasDiarias->count();
        if ($count === 0) return 0.0;
        return round($this->listaVentasDiarias->sum(fn($v) => (float)$v['TasaDeCambio']) / $count, 2);
    }

    public function getPorcentajeUnidadesVendidas(): float
    {
        if ($this->UnidadesGlobalVendidas == 0) return 0.0;
        return round(((float)$this->getUnidadesVendidas() * 100) / $this->UnidadesGlobalVendidas, 2);
    }

    public function getPorcentajeCostoVentas(): float
    {
        if ($this->MontoCostoGlobal == 0) return 0.0;
        return round(($this->getCostoDivisaPeriodo() * 100) / $this->MontoCostoGlobal, 2);
    }

    public function getPorcentajeMontoVentas(): float
    {
        if ($this->MontoDivisasGlobal == 0) return 0.0;
        return round(($this->getMontoDivisaTotalPeriodo() * 100) / $this->MontoDivisasGlobal, 2);
    }

    // Comisiones acumuladas
    public function getComisionesBs(): float
    {
        return (float) $this->listaVentasDiarias->sum(fn($v) => (float)($v['ComisionesBs'] ?? 0));
    }

    public function getComisionesDivisas(): float
    {
        return (float) $this->listaVentasDiarias->sum(fn($v) => (float)($v['ComisionesDivisas'] ?? 0));
    }

    // Promedios
    public function getTotalPromedioProductos(): float
    {
        $count = $this->listaVentasDiarias->count();
        if ($count === 0) return 0.0;
        $cantidad = $this->listaVentasDiarias->sum(fn($v) => (float)($v['UnidadesVendidas'] ?? 0));
        return $cantidad / $count;
    }

    public function getTotalPromedioMonto(): float
    {
        $count = $this->listaVentasDiarias->count();
        if ($count === 0) return 0.0;
        $promedios = $this->listaVentasDiarias->sum(fn($v) => (float)($v['PromedioMontoPorFactura'] ?? 0));
        return $promedios / $count;
    }

    public function getTotalPromedioMontoDivisa(): float
    {
        $count = $this->listaVentasDiarias->count();
        if ($count === 0) return 0.0;
        $promedios = $this->listaVentasDiarias->sum(fn($v) => (float)($v['PromedioMontoDivisaPorFactura'] ?? $v['MontoDivisaDiario'] ?? 0));
        return $promedios / $count;
    }

    public function getUtilidadDivisaPromedio(): float
    {
        $items = $this->getListaItemsPeriodo();
        if (empty($items)) return 0.0;

        $count = count(array_filter($items, fn($i) => isset($i['Producto']['CostoDivisa']) && $i['Producto']['CostoDivisa'] != 0));
        if ($count === 0) return 0.0;

        return $this->getUtilidadDivisaPeriodo() / $count;
    }

    /**
     * Top 10 productos más rentables (por UtilidadDivisa)
     * Igual que ListaMontoDivisaTopTenPeriodo en .NET
     */
    public function getListaMontoDivisaTopTenPeriodo(): array
    {
        $items = $this->getListaItemsPeriodo();

        if (!$items) {
            return [];
        }

        // Ordenar por utilidad descendente
        usort($items, function ($a, $b) {
            $uA = $a['UtilidadDivisa'] ?? 0;
            $uB = $b['UtilidadDivisa'] ?? 0;
            return $uB <=> $uA; // descendente
        });

        return array_slice($items, 0, 10);
    }

    // En VentasPeriodoDTO.php
    public function getEDCOperaciones(): ?array
    {
        return $this->raw['EDCOperaciones'] ?? null;
    }

    public function getDetallesEDC(): array
    {
        $edc = $this->getEDCOperaciones();
        return $edc['Detalles'] ?? [];
    }

    public function getResumenEDC(): array
    {
        $detalles = $this->getDetallesEDC();
        
        $resumen = [
            'total_ingresos_divisa' => 0,
            'total_egresos_divisa' => 0,
            'total_ingresos_bs' => 0,
            'total_egresos_bs' => 0,
            'saldo_final_divisa' => 0,
            'saldo_final_bs' => 0,
        ];
        
        if (!empty($detalles)) {
            // Tomar el último saldo como saldo final
            $ultimo = end($detalles);
            $resumen['saldo_final_divisa'] = $ultimo['SaldoDivisa'] ?? 0;
            $resumen['saldo_final_bs'] = $ultimo['SaldoBs'] ?? 0;
            
            // Calcular totales
            foreach ($detalles as $detalle) {
                if ($detalle['MontoDivisa'] > 0) {
                    $resumen['total_ingresos_divisa'] += $detalle['MontoDivisa'];
                } elseif ($detalle['MontoDivisa'] < 0) {
                    $resumen['total_egresos_divisa'] += abs($detalle['MontoDivisa']);
                }
                
                if ($detalle['MontoBs'] > 0) {
                    $resumen['total_ingresos_bs'] += $detalle['MontoBs'];
                } elseif ($detalle['MontoBs'] < 0) {
                    $resumen['total_egresos_bs'] += abs($detalle['MontoBs']);
                }
            }
        }
        
        return $resumen;
    }

    /* -------------------------
       Exportar a array (para dd/json)
       ------------------------- */
    public function toArray(): array
    {
        return [
            'FechaInicio' => $this->getFechaInicio(),
            'FechaFin' => $this->getFechaFin(),
            'CostoDivisaPeriodo' => $this->getCostoDivisaPeriodo(),
            'CostoDivisaPeriodoDsp' => $this->getCostoDivisaPeriodoDsp(),
            'ListaVentasDiarias' => $this->listaVentasDiarias->map(function ($v) {
                // convertir listado productos a array tal como lo devuelve .NET
                $listado = collect($v['ListadoProductosVentaDiaria'])->map(function ($p) {
                    return [
                        'Cantidad' => $p['Cantidad'] ?? 0,
                        'MontoDivisa' => $p['MontoDivisa'] ?? 0,
                        'PrecioVenta' => $p['PrecioVenta'] ?? 0,
                        'ProductoId' => $p['ProductoId'] ?? null,
                        'Producto' => $p['Producto'] ?? null,
                        'VentaId' => $p['VentaId'] ?? null,
                        'Id' => $p['Id'] ?? null,
                    ];
                })->toArray();

                return [
                    'Fecha' => $v['fecha_formateada'] ?? ($v['FECHA'] ?? null),
                    'TasaDeCambio' => $v['TasaDeCambio'] ?? 0,
                    'TotalBS' => $v['TotalBS'] ?? 0,
                    'TotalDivisa' => $v['TotalDivisa'] ?? 0,
                    'ListadoProductosVentaDiaria' => $listado,
                    // incluir campos auxiliares si los hay
                    'UnidadesVendidas' => $v['UnidadesVendidas'] ?? 0,
                    'MontoBsDiario' => $v['MontoBsDiario'] ?? 0,
                    'MontoDivisaDiario' => $v['MontoDivisaDiario'] ?? 0,
                ];
            })->toArray(),

            // Totales y calculados
            'UnidadesVendidas' => $this->getUnidadesVendidas(),
            'MontoDivisaTotalPeriodo' => $this->getMontoDivisaTotalPeriodo(),
            'MontoBsPeriodo' => $this->getMontoBsPeriodo(),
            'UtilidadDivisaPeriodo' => $this->getUtilidadDivisaPeriodo(),
            'UtilidadDivisaPeriodoDsp' => $this->getUtilidadDivisaPeriodoDsp(),
            'UtilidadBsPeriodo' => $this->getUtilidadBsPeriodo(),
            'UtilidadBsPeriodoDsp' => $this->getUtilidadBsPeriodoDsp(),
            'ListaItemsPeriodo' => $this->getListaItemsPeriodo(),
            'ListaItemsTopTenPeriodo' => $this->getListaItemsTopTenPeriodo(),
            'MontoTopTenPeriodo' => $this->getMontoTopTenPeriodo(),
            'TasaDeCambioPromedio' => $this->getTasaDeCambioPromedio(),
            'ComisionesBs' => $this->getComisionesBs(),
            'ComisionesDivisas' => $this->getComisionesDivisas(),
            'TotalPromedioProductos' => $this->getTotalPromedioProductos(),
            'TotalPromedioMonto' => $this->getTotalPromedioMonto(),
            'TotalPromedioMontoDivisa' => $this->getTotalPromedioMontoDivisa(),
            'PorcentajeUnidadesVendidas' => $this->getPorcentajeUnidadesVendidas(),
            'PorcentajeCostoVentas' => $this->getPorcentajeCostoVentas(),
            'PorcentajeMontoVentas' => $this->getPorcentajeMontoVentas(),
        ];
    }
}
