<?php

namespace App\DTO;

use Carbon\Carbon;

class VentaDiariaDTO
{
    public $id;
    public $fecha;
    public $sucursalId;
    public $cantidad;
    public $costoDivisa;
    public $totalDivisa;
    public $totalBs;
    public $saldo;
    public $usuarioId;
    public $proveedorId;
    public $tasaDeCambio;
    public $listadoProductosVentaDiaria = []; // array de VentaDetalleDTO
    public $listadoGastos = []; // array de TransaccionDTO

    public $unidadesGlobalVendidas = 0;
    public $montoDivisaGlobal = 0;

    public $nombreSucursal;

    public $listaItemsTopTenDiario = [];
    public $montoDivisaTopTenDiario = [];
    public $montoTopTenDiario = [];
    public $ranking = 0;
    public $sucursal;


    public function getPromedioProductosPorFactura(): float {
        $count = count($this->listadoProductosVentaDiaria);
        if($count > 0){
            $totalProductos = array_sum(array_column($this->listadoProductosVentaDiaria,'cantidad'));
            return round($totalProductos / $count, 2);
        }
        return 0;
    }

    public function getPromedioMontoPorFactura(): float {
        $count = count($this->listadoProductosVentaDiaria);
        if($count > 0){
            $totalMonto = array_sum(array_column($this->listadoProductosVentaDiaria,'precioVenta'));
            return round($totalMonto / $count,2);
        }
        return 0;
    }


    public function getMargenDiarioDivisaPromedio(): float {
        if (!empty($this->listadoProductosVentaDiaria)) {
            $productosConCosto = array_filter($this->listadoProductosVentaDiaria, fn($p)=> isset($p['costoDivisa']) && $p['costoDivisa']>0);
            if(count($productosConCosto) > 0){
                $sumaMargen = array_sum(array_map(fn($p)=> $p['margen'] ?? 0, $productosConCosto));
                return round($sumaMargen / count($productosConCosto), 2);
            }
        }
        if($this->getCostoTotalDivisaDiario() > 0){
            return round((($this->getMontoDivisaDiario()*100)/$this->getCostoTotalDivisaDiario())-100, 2);
        }
        return 0;
    }

    public function calcularTopTen() {
        if (!empty($this->listadoProductosVentaDiaria)) {
            $productos = $this->listadoProductosVentaDiaria;

            // Top 10 por cantidad
            usort($productos, fn($a,$b)=> $b['cantidad'] <=> $a['cantidad']);
            $this->listaItemsTopTenDiario = array_slice($productos, 0, 10);

            // Top 10 por monto divisa
            usort($productos, fn($a,$b)=> $b['montoDivisa'] <=> $a['montoDivisa']);
            $this->montoDivisaTopTenDiario = array_slice($productos, 0, 10);

            // Top 10 por precioVenta
            usort($productos, fn($a,$b)=> $b['precioVenta'] <=> $a['precioVenta']);
            $this->montoTopTenDiario = array_slice($productos, 0, 10);
        }
    }

    // Calculados
    public function getUnidadesVendidas(): int
    {
        if (!empty($this->listadoProductosVentaDiaria)) {
            return array_sum(array_column($this->listadoProductosVentaDiaria, 'cantidad'));
        }
        return $this->cantidad ?? 0;
    }

    public function getMontoBsDiario(): float
    {
        if (!empty($this->listadoProductosVentaDiaria)) {
            return round(array_sum(array_column($this->listadoProductosVentaDiaria, 'precioVenta')), 2);
        }
        return $this->totalBs ?? 0;
    }

    public function getMontoDivisaDiario(): float
    {
        if (!empty($this->listadoProductosVentaDiaria)) {
            return round(array_sum(array_column($this->listadoProductosVentaDiaria, 'montoDivisa')), 2);
        }
        return $this->totalDivisa ?? 0;
    }

    public function getUtilidadBsDiario(): float
    {
        if (!empty($this->listadoProductosVentaDiaria)) {
            return round(array_sum(array_column($this->listadoProductosVentaDiaria, 'utilidadBs')), 2);
        }
        return ($this->totalBs ?? 0) - ($this->getCostoBsDiario() ?? 0);
    }

    public function getUtilidadDivisaDiario(): float
    {
        if (!empty($this->listadoProductosVentaDiaria)) {
            return round(array_sum(array_column($this->listadoProductosVentaDiaria, 'utilidadDivisa')), 2);
        }
        return ($this->totalDivisa ?? 0) - ($this->getCostoTotalDivisaDiario() ?? 0);
    }

    public function getMargenDivisaDiario(): float
    {
        $costo = $this->getCostoTotalDivisaDiario(); // usa tu método existente para obtener costo en divisa
        $venta = $this->getMontoDivisaDiario();      // usa tu método existente para obtener venta en divisa

        if ($costo <= 0) {
            return 0;
        }

        return round((($venta * 100) / $costo) - 100, 2);
    }

    public function getCostoBsDiario(): float
    {
        if (!empty($this->listadoProductosVentaDiaria)) {
            return round(array_sum(array_column($this->listadoProductosVentaDiaria, 'costoTotalItemBs')), 2);
        }
        return ($this->getCostoTotalDivisaDiario() * ($this->tasaDeCambio ?? 1));
    }

    public function getCostoTotalDivisaDiario(): float
    {
        if (!empty($this->listadoProductosVentaDiaria)) {
            return round(array_sum(array_column($this->listadoProductosVentaDiaria, 'costoTotalItemDivisa')), 2);
        }
        return $this->costoDivisa ?? 0;
    }

    public function getPorcentajeUnidades(): float
    {
        return $this->unidadesGlobalVendidas != 0
            ? round($this->getUnidadesVendidas() * 100 / $this->unidadesGlobalVendidas, 2)
            : 0;
    }

    public function getPorcentajeVenta(): float
    {
        return $this->montoDivisaGlobal != 0
            ? round($this->getMontoDivisaDiario() * 100 / $this->montoDivisaGlobal, 2)
            : 0;
    }
}
