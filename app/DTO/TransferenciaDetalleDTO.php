<?php
// app/DTO/TransferenciaDetalleDTO.php
namespace App\DTO;

class TransferenciaDetalleDTO extends ProductoBaseDTO
{
    public int $TransferenciaDetalleId;
    public int $TransferenciaId;
    public int $SucursalId;
    
    // Constructor que puede recibir un ProductoDTO
    public function __construct($data = null)
    {
        parent::__construct([]);
        
        if ($data instanceof ProductoDTO) {
            $this->Producto = $data;
        } elseif (is_array($data)) {
            foreach ($data as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }
    
    // Propiedad calculada TotalCostoDetalle
    public function getTotalCostoDetalleAttribute(): float
    {
        if ($this->Producto) {
            return round($this->CantidadEmitida * $this->Producto->CostoDivisa, 2);
        }
        return 0;
    }
}