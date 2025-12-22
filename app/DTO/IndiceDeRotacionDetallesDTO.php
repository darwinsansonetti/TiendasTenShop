<?php
namespace App\DTO;

class IndiceDeRotacionDetallesDTO
{
    public int $producto_id;
    public float $total_unidades;
    public float $indice_rotacion;
    public array $producto;

    public function __construct($producto_id, $total_unidades, $indice_rotacion, $producto)
    {
        $this->producto_id = $producto_id;
        $this->total_unidades = $total_unidades;
        $this->indice_rotacion = $indice_rotacion;
        $this->producto = $producto;
    }
}
