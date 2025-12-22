<?php
namespace App\DTO;

use Illuminate\Support\Collection;
use Carbon\Carbon;

class IndiceDeRotacionDTO
{
    public Collection $detalles;
    public float $mayor_indice;
    public float $indice_promedio;
    public ?Carbon $fecha_inicio;
    public ?Carbon $fecha_fin;

    public function __construct(Collection $detalles, ?Carbon $fecha_inicio, ?Carbon $fecha_fin)
    {
        $this->detalles = $detalles;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;

        $this->mayor_indice = $detalles->max('indice_rotacion') ?? 0;
        $this->indice_promedio = $detalles->avg('indice_rotacion') ?? 0;
    }
}
