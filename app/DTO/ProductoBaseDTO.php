<?php
// app/DTO/ProductoBaseDTO.php
namespace App\DTO;

class ProductoBaseDTO
{
    public int $ProductoId;
    
    // La cantidad ya recibida de la factura o transferencia
    public int $CantidadRecibida;
    
    // Cantidad disponible (calculada)
    public function getCantidadDisponibleAttribute(): int
    {
        $disponible = $this->CantidadEmitida - $this->CantidadRecibida;
        return $disponible > 0 ? $disponible : 0;
    }
    
    /// <summary>
    /// Representa la cantidad Facturada o la cantidad Emitida desde una 
    /// transferencia o distribucion para sucursal
    /// </summary>
    public int $CantidadEmitida;
    
    public float $CostoDivisa;
    public float $CostoBs;
    public float $CostoUnitario;
    
    public ?ProductoDTO $Producto;
    
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}