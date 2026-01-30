<?php

namespace App\DTO;

use Carbon\Carbon;

class DivisaValorDTO
{
    public int $Id;
    public int $DivisaId;
    public float $Valor = 0;
    public Carbon $Fecha;
}
