<?php

namespace App\DTO;

use Carbon\Carbon;

class CierreDiarioPeriodoDTO
{
    public array $ListadoCierresDiarios = [];
    public ?Carbon $FechaInicio = null;
    public ?Carbon $FechaFin = null;
    public bool $VerEnDivisa = false;

    // ------------------ MÉTODOS CALCULADOS ------------------
    public function TotalBsEfectivo(): float
    {
        return array_sum(array_map(fn($c) => $c->EfectivoBs ?? 0, $this->ListadoCierresDiarios));
    }

    public function TotalDisponible(): float
    {
        return array_sum(array_map(fn($c) => $c->TotalDisponibleBs(), $this->ListadoCierresDiarios));
    }

    public function TotalBsConvertidos(): float
    {
        return array_sum(array_map(fn($c) => $c->TotalBsConvertidos(), $this->ListadoCierresDiarios));
    }

    public function TotalBsFacturados(): float
    {
        return array_sum(array_map(fn($c) => $c->TotalBsFacturados(), $this->ListadoCierresDiarios));
    }

    public function TotalBsCobrados(): float
    {
        return array_sum(array_map(fn($c) => $c->TotalCobradoBs(), $this->ListadoCierresDiarios));
    }

    public function TotalPuntoDeVentaBs(): float
    {
        return array_sum(array_map(fn($c) => $c->TotalPuntoDeVentaBs(), $this->ListadoCierresDiarios));
    }

    public function TotalEgresoBs(): float
    {
        return array_sum(array_map(fn($c) => $c->TotalEgresoBs(), $this->ListadoCierresDiarios));
    }

    public function TotalBsPeriodo(): float
    {
        return $this->TotalBsFacturados() - $this->TotalEgresoBs();
    }

    public function TotalEfectivoDivisa(): float
    {
        return array_sum(array_map(fn($c) => $c->EfectivoDivisas ?? 0, $this->ListadoCierresDiarios));
    }

    public function TotalPuntoDeVentaDivisa(): float
    {
        return array_sum(array_map(fn($c) => $c->PuntoDeVentaDivisas ?? 0, $this->ListadoCierresDiarios));
    }

    public function TotalTransferenciaDivisa(): float
    {
        return array_sum(array_map(fn($c) => $c->TransferenciaDivisas ?? 0, $this->ListadoCierresDiarios));
    }

    public function TotalZelleDivisa(): float
    {
        return array_sum(array_map(fn($c) => $c->ZelleDivisas ?? 0, $this->ListadoCierresDiarios));
    }

    public function TotalEgresoDivisa(): float
    {
        return array_sum(array_map(fn($c) => $c->EgresoDivisas ?? 0, $this->ListadoCierresDiarios));
    }

    public function TotalDivisaPeriodo(): float
    {
        return array_sum(array_map(fn($c) => $c->TotalDivisa(), $this->ListadoCierresDiarios));
    }

    public function TotalSobrantePeriodo(): float
    {
        return array_sum(array_map(fn($c) => $c->Diferencia > 0 ? $c->Diferencia : 0, $this->ListadoCierresDiarios));
    }

    public function TotalFaltantePeriodo(): float
    {
        return array_sum(array_map(fn($c) => $c->Diferencia < 0 ? $c->Diferencia : 0, $this->ListadoCierresDiarios));
    }

    public function TotalDiferenciaPeriodo(): float
    {
        return array_sum(array_map(fn($c) => $c->Diferencia, $this->ListadoCierresDiarios));
    }
}
