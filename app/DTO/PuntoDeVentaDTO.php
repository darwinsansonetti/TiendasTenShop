<?php

namespace App\DTO;

class PuntoDeVentaDTO
{
    public int $PuntoDeVentaId;
    public string $Codigo = '';
    public string $Descripcion = '';
    public ?SucursalDTO $Sucursal = null;
    public ?int $SucursalId = null;

    public string $Banco = '';
    public ?int $BancoId = null;
    public string $Serial = '';
    public bool $EsActivo = true;
}
