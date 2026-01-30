<?php

namespace App\DTO;

use Carbon\Carbon;

class CierreDiarioDTO
{
    public ?int $CierreDiarioId = null;
    public ?Carbon $Fecha = null;
    public ?object $GastoActivo = null; // equivalente a TransaccionDTO
    public array $GastosCierreDiario = [];
    public ?string $SerialImpresora = null;
    public ?string $NumeroZeta = null;
    public ?float $VentaSistema = null;
    public ?int $SucursalId = null;
    public ?object $Sucursal = null; // SucursalDTO
    public float $MontoBaseGeneral = 0;
    public float $MontoBaseExento = 0;
    public float $ImpuestoGeneral = 0;
    public float $MontoBaseGeneralDevoluciones = 0;
    public float $MontoImpuestoGeneralDevoluciones = 0;
    public float $MontoBaseExentoDevoluciones = 0;
    public float $MontoBaseGeneralAuditado = 0;
    public float $MontoBaseExentoAuditado = 0;
    public float $ImpuestoGeneralAuditado = 0;
    public float $MontoBaseGeneralDevolucionesAuditado = 0;
    public float $MontoImpuestoGeneralDevolucionesAuditado = 0;
    public float $MontoBaseExentoDevolucionesAuditado = 0;
    public ?int $Estatus = null; // EnumCierreDiario
    public bool $EsEditable = false;
    public ?float $EfectivoBs = null;
    public array $PagosPuntoDeVenta = [];
    public ?float $TransferenciaBs = null;
    public ?string $Observacion = null;
    public ?float $PagoMovilBs = null;
    public ?float $EgresoBs = null;
    public ?float $EfectivoDivisas = null;
    public ?float $PuntoDeVentaDivisas = null;
    public ?float $TransferenciaDivisas = null;
    public ?float $ZelleDivisas = null;
    public ?float $EgresoDivisas = null;
    public ?int $DivisaValorId = null;
    public ?object $DivisaValor = null; // DivisaValorDTO
    public ?float $Tipo = null; // EnumTipoCierre

    // ------------------ MÉTODOS CALCULADOS ------------------
    public function getDiferencia(): float
    {
        return ($this->TotalBsFacturados() - ($this->VentaSistema ?? 0) + ($this->TotalEgresoBs()));
    }

    public function TotalCobradoBs(): float
    {
        $total = ($this->EfectivoBs ?? 0) + ($this->PagoMovilBs ?? 0) + ($this->TransferenciaBs ?? 0);
        foreach ($this->PagosPuntoDeVenta as $pago) {
            $total += $pago->Monto ?? 0;
        }
        return $total;
    }

    public function TotalDisponibleBs(): float
    {
        $total = ($this->EfectivoBs ?? 0) + ($this->PagoMovilBs ?? 0) + ($this->TransferenciaBs ?? 0) - ($this->EgresoBs ?? 0);
        foreach ($this->PagosPuntoDeVenta as $pago) {
            $total += $pago->Monto ?? 0;
        }
        return $total;
    }

    public function TotalPuntoDeVentaBs(): float
    {
        $total = 0;
        foreach ($this->PagosPuntoDeVenta as $pago) {
            $total += $pago->Monto ?? 0;
        }
        return $total;
    }

    public function TotalBsConvertidos(): float
    {
        $totalDivisa = $this->TotalDivisa();
        return $this->DivisaValor ? ($this->DivisaValor->Valor * $totalDivisa) : 0;
    }

    public function TotalEgresoBs(): float
    {
        $total = 0;
        foreach ($this->GastosCierreDiario as $gasto) {
            $total += $gasto->MontoAbonado ?? 0;
        }
        return $total;
    }

    public function TotalBsFacturados(): float
    {
        return $this->TotalCobradoBs() + $this->TotalBsConvertidos();
    }

    public function TotalDivisa(): float
    {
        return ($this->EfectivoDivisas ?? 0) +
               ($this->TransferenciaDivisas ?? 0) +
               ($this->ZelleDivisas ?? 0) +
               ($this->PuntoDeVentaDivisas ?? 0);
    }
}
