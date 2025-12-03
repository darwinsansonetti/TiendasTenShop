<?php
// app/DTO/EDCSucursalDTO.php

namespace App\DTO;

class EDCSucursalDTO
{
    public int $SucursalId;
    public mixed $Sucursal;
    public ?array $Ventas = null; // Array de VentasDTO o similar
    public ?int $Ranking = null;
    
    // Propiedades calculadas
    public float $VentaTotal = 0;
    public int $TotalTransacciones = 0;
    public float $PromedioVenta = 0;
    public float $PorcentajeParticipacion = 0;
    
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * Calcular métricas basadas en las ventas
     */
    public function calcularMetricas(): void
    {
        $this->VentaTotal = 0;
        $this->TotalTransacciones = 0;
        
        if (is_array($this->Ventas) && !empty($this->Ventas)) {
            foreach ($this->Ventas as $venta) {
                $this->VentaTotal += $venta->MontoTotal ?? $venta->monto_total ?? 0;
                $this->TotalTransacciones++;
            }
        }
        
        $this->PromedioVenta = $this->TotalTransacciones > 0 
            ? $this->VentaTotal / $this->TotalTransacciones 
            : 0;
    }
}