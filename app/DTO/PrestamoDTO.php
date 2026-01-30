<?php

namespace App\DTO;

use Carbon\Carbon;

class PrestamoDTO
{
    public int $Id;
    public string $Descripcion = '';
    public float $Monto = 0;
    public float $MontoDivisa = 0;
    public ?Carbon $FechaInicio = null;
    public ?Carbon $FechaFin = null;
}
