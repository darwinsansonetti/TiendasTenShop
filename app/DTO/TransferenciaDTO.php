<?php
// app/DTO/TransferenciaDTO.php
namespace App\DTO;

use Carbon\Carbon;

class TransferenciaDTO
{
    // Propiedades públicas (como en .NET)
    public int $TransferenciaId;
    public string $Numero;
    public Carbon $Fecha;
    public int $Estatus; // EnumTransferencia como entero
    public int $SucursalOrigenId;
    public int $SucursalDestinoId;
    public ?string $Observacion;
    public float $Saldo;
    public int $Tipo; // EnumTipoTransferencia
    public int $PasoOperacion; // EnumPasoOperacion
    
    // Propiedades con setter privado en .NET, aquí públicas
    public float $CantidadDisponible;
    public float $CantidadEmitida;
    public float $CantidadRecibida;
    public int $CantidadItems;
    
    // Relaciones
    public array $Detalles = []; // Array de TransferenciaDetalleDTO
    public ?SucursalDTO $SucursalDestino;
    public ?SucursalDTO $SucursalOrigen;
    public array $ListaSucursalDestino = [];
    
    // Propiedades calculadas (getters)
    public function getEsDisponibleRecibirAttribute(): bool
    {
        // EnumTransferencia.Registrada = 1, Disponible = 2
        return in_array($this->Estatus, [1, 2]);
    }
    
    public function getEsEnCreacionAttribute(): bool
    {
        // EnumTransferencia.EnEdicion = 0
        return $this->Estatus <= 0;
    }
    
    public function getPorcentajeRecibidoAttribute(): float
    {
        if ($this->TotalCantidadEmitida > 0) {
            return round(($this->TotalCantidadRecibida * 100) / $this->TotalCantidadEmitida, 2);
        }
        return 0;
    }
    
    public function getTotalItemsAttribute(): int
    {
        return !empty($this->Detalles) ? count($this->Detalles) : $this->CantidadItems;
    }
    
    public function getTotalCantidadDisponibleAttribute(): float
    {
        if (!empty($this->Detalles)) {
            return collect($this->Detalles)->sum('CantidadDisponible');
        }
        return $this->CantidadDisponible;
    }
    
    public function getTotalCantidadRecibidaAttribute(): float
    {
        if (!empty($this->Detalles)) {
            return collect($this->Detalles)->sum('CantidadRecibida');
        }
        return $this->CantidadRecibida;
    }
    
    public function getTotalCantidadEmitidaAttribute(): float
    {
        if (!empty($this->Detalles)) {
            return collect($this->Detalles)->sum('CantidadEmitida');
        }
        return $this->CantidadEmitida;
    }
    
    public function getTotalCostoAttribute(): float
    {
        if (!empty($this->Detalles)) {
            return collect($this->Detalles)->sum('TotalCostoDetalle');
        }
        return 0;
    }
    
    // Constructor para facilitar la creación
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}