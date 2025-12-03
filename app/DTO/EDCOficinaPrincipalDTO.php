<?php
// app/DTO/EDCOficinaPrincipalDTO.php

namespace App\DTO;

class EDCOficinaPrincipalDTO
{
    public ?int $SucursalId = null;
    public mixed $Sucursal = null;
    public mixed $Fecha = null;
    public array $EDCSucursales = [];
    
    // Propiedades adicionales que puedas necesitar
    public float $VentaTotal = 0;
    public int $TotalTransacciones = 0;
    public float $PromedioVenta = 0;
    
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * Calcular totales basados en las sucursales
     */
    public function calcularTotales(): void
    {
        $this->VentaTotal = 0;
        $this->TotalTransacciones = 0;
        
        foreach ($this->EDCSucursales as $sucursal) {
            $this->VentaTotal += $sucursal->VentaTotal ?? 0;
            $this->TotalTransacciones += $sucursal->TotalTransacciones ?? 0;
        }
        
        $this->PromedioVenta = $this->TotalTransacciones > 0 
            ? $this->VentaTotal / $this->TotalTransacciones 
            : 0;
    }
}