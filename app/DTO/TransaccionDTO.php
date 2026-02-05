<?php

namespace App\DTO;

use Carbon\Carbon;

class TransaccionDTO
{
    public int $Id;
    public string $Descripcion = '';
    public float $MontoAbonado = 0;
    public float $MontoDivisaAbonado = 0;
    public string $NumeroOperacion = '';
    public string $Nombre = '';
    public string $Cedula = '';

    public ?int $CategoriaId = null;
    public ?CategoriaGastosDTO $Categoria = null;

    /** @var TransaccionDTO[] */
    public array $AbonoVentas = [];

    public int $Tipo = 0; // EnumTipoTransaccion
    public int $FormaDePago = 0; // EnumFormaPago
    public int $Estatus = 0; // EnumTransaccion

    public ?SucursalDTO $Sucursal = null;
    public ?SucursalDTO $SucursalOrigen = null;
    public ?int $SucursalId = null;
    public ?int $SucursalOrigenId = null;

    public int $DivisaId = 0;
    public float $TasaDeCambio = 0;

    public string $UrlComprobante = '';
    public string $Observacion = '';
    public int $PrestamoId = 0;
    public ?PrestamoDTO $Prestamo = null;

    public int $FacturaId = 0;
    public ?FacturaDTO $Factura = null;

    public ?ProveedorDTO $proveedor = null;

    public Carbon $Fecha;

    // ⚡ Métodos calculados
    public function getTotalAbonadoDivisa(): float
    {
        $total = 0;
        foreach ($this->AbonoVentas as $abono) {
            $total += $abono->MontoDivisaAbonado ?? 0;
        }
        return round($total, 2);
    }

    public function getSaldoDivisa(): float
    {
        return round($this->MontoDivisaAbonado - $this->getTotalAbonadoDivisa(), 2);
    }
}
