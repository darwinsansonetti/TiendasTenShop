<?php
// app/DTO/LiberalidadDTO.php

namespace App\DTO;

use App\Models\Liberalidad;
use Illuminate\Support\Collection;

class LiberalidadDTO
{
    public int $LiberalidadId;
    public int $Mes;
    public int $Anno;
    public $FechaInicio;
    public $FechaFinal;
    public int $Estatus;
    
    /** @var Collection<LiberalidadDetalleDTO> */
    public Collection $detalles;
    
    /**
     * Constructor desde el modelo
     */
    public function __construct(Liberalidad $liberalidad)
    {
        $this->LiberalidadId = $liberalidad->LiberalidadId;
        $this->Mes = $liberalidad->Mes;
        $this->Anno = $liberalidad->Anno;
        $this->FechaInicio = $liberalidad->FechaInicio;
        $this->FechaFinal = $liberalidad->FechaFinal;
        $this->Estatus = $liberalidad->Estatus;
        $this->detalles = collect();
        
        // Cargar detalles
        $liberalidad->load('detalles');
        foreach ($liberalidad->detalles as $detalle) {
            $this->detalles->push(new LiberalidadDetalleDTO($detalle));
        }
    }
    
    /**
     * Crear un DTO vacío (para cuando no existe liberalidad)
     */
    public static function empty(): self
    {
        // Crear un modelo vacío con valores por defecto
        $liberalidad = new Liberalidad();
        $liberalidad->LiberalidadId = 0;
        $liberalidad->Mes = 0;
        $liberalidad->Anno = 0;
        $liberalidad->FechaInicio = null;
        $liberalidad->FechaFinal = null;
        $liberalidad->Estatus = 0;
        
        $dto = new self($liberalidad);
        $dto->detalles = collect();
        
        return $dto;
    }
    
    /**
     * Propiedad calculada: VentasTotales
     */
    public function getVentasTotales(): float
    {
        return round($this->detalles->sum('Venta'), 2);
    }
    
    /**
     * Propiedad calculada: LiberalidadTotal
     */
    public function getLiberalidadTotal(): float
    {
        return round($this->detalles->sum('MontoLiberalidad'), 2);
    }
    
    /**
     * Propiedad calculada: UnidadesTotales
     */
    public function getUnidadesTotales(): int
    {
        return $this->detalles->sum('Unidades');
    }
    
    /**
     * Propiedad calculada: PagoTotal
     */
    public function getPagoTotal(): float
    {
        return round($this->detalles->sum('Pago'), 2);
    }
    
    /**
     * Propiedad calculada: SaldoFavorTotal
     */
    public function getSaldoFavorTotal(): float
    {
        return round($this->detalles->sum('SaldoFavor'), 2);
    }
    
    /**
     * Propiedad calculada: DisponibleTotal
     */
    public function getDisponibleTotal(): float
    {
        return round($this->detalles->sum(function($detalle) {
            return $detalle->getDisponible();
        }), 2);
    }
    
    /**
     * Convertir a array
     */
    public function toArray(): array
    {
        return [
            'LiberalidadId' => $this->LiberalidadId,
            'Mes' => $this->Mes,
            'Anno' => $this->Anno,
            'FechaInicio' => $this->FechaInicio,
            'FechaFinal' => $this->FechaFinal,
            'Estatus' => $this->Estatus,
            'VentasTotales' => $this->getVentasTotales(),
            'LiberalidadTotal' => $this->getLiberalidadTotal(),
            'UnidadesTotales' => $this->getUnidadesTotales(),
            'PagoTotal' => $this->getPagoTotal(),
            'SaldoFavorTotal' => $this->getSaldoFavorTotal(),
            'DisponibleTotal' => $this->getDisponibleTotal(),
            'Detalles' => $this->detalles->map(function($detalle) {
                return $detalle->toArray();
            })->values()->toArray()
        ];
    }
}