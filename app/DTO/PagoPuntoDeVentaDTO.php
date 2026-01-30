<?php

namespace App\DTO;

class PagoPuntoDeVentaDTO extends PuntoDeVentaDTO
{
    public int $PagoPuntoDeVentaId;
    public ?float $Monto = 0;
    public int $CierreDiarioId;
    public ?PuntoDeVentaDTO $PuntoDeVenta = null;
}
