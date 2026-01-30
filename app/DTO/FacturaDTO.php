<?php

namespace App\DTO;

use Carbon\Carbon;

class FacturaDTO
{
    public int $Id;
    public string $NumeroFactura = '';
    public ?Carbon $Fecha = null;
    public float $Monto = 0;
    public float $MontoDivisa = 0;
}
