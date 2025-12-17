<?php

namespace App\DTO;

class ComparacionSucursalesDetalleDTO
{
    public array $producto;

    public float $CantidadCalzatodo = 0;
    public float $CantidadTenShop = 0;
    public float $Cantidad10y10 = 0;
    public float $CantidadG1091 = 0;

    public float $TotalDivisasCalzatodo = 0;
    public float $TotalDivisasTenShop = 0;
    public float $TotalDivisas10y10 = 0;
    public float $TotalDivisasG1091 = 0;

    public float $ExistenciaCalzatodo = 0;
    public float $ExistenciaTenShop = 0;
    public float $Existencia10y10 = 0;
    public float $ExistenciaG1091 = 0;

    public float $PvpDivisaCalzatodo = 0;
    public float $PvpDivisaTenShop = 0;
    public float $PvpDivisa10y10 = 0;
    public float $PvpDivisaG1091 = 0;

    // 🔥 Propiedad calculada (igual que .NET)
    public function getEsResaltarDiferenciasAttribute(): bool
    {
        $pares = [
            [$this->Existencia10y10, $this->ExistenciaCalzatodo],
            [$this->Existencia10y10, $this->ExistenciaTenShop],
            [$this->Existencia10y10, $this->ExistenciaG1091],
            [$this->ExistenciaCalzatodo, $this->ExistenciaTenShop],
            [$this->ExistenciaCalzatodo, $this->ExistenciaG1091],
            [$this->ExistenciaTenShop, $this->ExistenciaG1091],
        ];

        foreach ($pares as [$a, $b]) {
            if ($a > 0 && $b > 0 && abs($a - $b) >= 10) {
                return true;
            }
        }

        return false;
    }
}
