<?php

namespace App\DTO;

use Carbon\Carbon;

class ProductoDTO
{
    public int $Id;
    public string $Codigo;
    public string $Descripcion;
    public float $CostoDivisa;
    public float $PvpDivisa;
    public float $NuevoPvp;
    public float $PvpAnterior;
    public int $Existencia;
    public ?Carbon $FechaUltimaVenta;
    public ?Carbon $FechaNuevoPrecio;
    public string $UrlFoto;
    public int $Tipo; // Equivalente a tdcmp en .NET

    // Campos calculados
    public function getMargen(): float
    {
        if ($this->CostoDivisa == 0 || $this->PvpDivisa == 0) return 0;
        return round((($this->PvpDivisa * 100) / $this->CostoDivisa) - 100, 2);
    }

    public function getMargenNuevoPrecio(): float
    {
        if ($this->CostoDivisa == 0 || $this->NuevoPvp == 0) return 0;
        return round((($this->NuevoPvp * 100) / $this->CostoDivisa) - 100, 2);
    }

    public function getUtilidadDivisaUnitario(): float
    {
        return round($this->PvpDivisa - $this->CostoDivisa, 2);
    }

    public function getUtilidadDivisaNuevoPvp(): float
    {
        return $this->NuevoPvp > 0 ? round($this->NuevoPvp - $this->CostoDivisa, 2) : 0;
    }
}