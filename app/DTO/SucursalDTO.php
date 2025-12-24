<?php

namespace App\DTO;

use Carbon\Carbon;

class SucursalDTO
{
    public int $Id;
    public string $Nombre;
    public ?string $Direccion = null;
    public ?string $SerialImpresora = null;
    public bool $EsActiva = true;
    public int $Tipo = 0; // Puedes mapear a un enum o constante si lo deseas
    public ?Carbon $FechaCarga = null;

    /**
     * Constructor opcional para inicializar con un array de datos.
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
