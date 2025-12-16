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
