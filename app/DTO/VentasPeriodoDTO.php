<?php
// app/DTO/VentasPeriodoDTO.php

namespace App\DTO;

class VentasPeriodoDTO
{
    public array $ListaVentasDiarias = [];
    public float $TotalVentas = 0;
    public float $TotalGastos = 0;
    public float $UtilidadBruta = 0;
    public int $TotalTransacciones = 0;
    public ?string $Periodo = null;
    
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
    
    /**
     * Calcular totales basados en la lista de ventas diarias
     */
    public function calcularTotales(): void
    {
        $this->TotalVentas = 0;
        $this->TotalGastos = 0;
        $this->TotalTransacciones = 0;
        
        foreach ($this->ListaVentasDiarias as $ventaDiaria) {
            if ($ventaDiaria instanceof VentaDiariaDTO) {
                $this->TotalVentas += $ventaDiaria->TotalVentas ?? 0;
                $this->TotalGastos += $ventaDiaria->TotalGastos ?? 0;
                $this->TotalTransacciones += $ventaDiaria->CantidadTransacciones ?? 0;
            }
        }
        
        $this->UtilidadBruta = $this->TotalVentas - $this->TotalGastos;
    }
}