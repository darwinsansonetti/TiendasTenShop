<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CierreOfp extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'CierreOfp';
    protected $primaryKey = 'CierreOfpId';

    protected $fillable = [
        'CierreOfpId',
        'SucursalId',
        'Fecha',
        'Estatus',
        'VentaDivisa',
        'VentaBs',
        'CierreDivisa',
        'CierreBs',
        'GastosCajaDivisa',
        'GastosCajaBs',
        'GastosSucursalDivisa',
        'GastosSucursalBs',
        'PagoServiciosDivisa',
        'PagoServiciosBs',
        'PagoFacturasBs',
        'PagoFacturasDivisa',
        'PrestamosDivisa',
        'PrestamosBs',
        'AbonosDivisa',
        'AbonosBs',
        'FacturasDivisa',
        'FacturasBs',
        'ServiciosDivisa',
        'ServiciosBs',
        'SaldoOperacionBs',
        'SaldoOperacionDivisas',
        'SaldoGeneralBs',
        'SaldoGeneralDivisas'
    ];

    protected $casts = [
        'Fecha' => 'datetime',
        'Estatus' => 'integer',
        'VentaDivisa' => 'decimal:2',
        'VentaBs' => 'decimal:2',
        'CierreDivisa' => 'decimal:2',
        'CierreBs' => 'decimal:2',
        'GastosCajaDivisa' => 'decimal:2',
        'GastosCajaBs' => 'decimal:2',
        'GastosSucursalDivisa' => 'decimal:2',
        'GastosSucursalBs' => 'decimal:2',
        'PagoServiciosDivisa' => 'decimal:2',
        'PagoServiciosBs' => 'decimal:2',
        'PagoFacturasBs' => 'decimal:2',
        'PagoFacturasDivisa' => 'decimal:2',
        'PrestamosDivisa' => 'decimal:2',
        'PrestamosBs' => 'decimal:2',
        'AbonosDivisa' => 'decimal:2',
        'AbonosBs' => 'decimal:2',
        'FacturasDivisa' => 'decimal:2',
        'FacturasBs' => 'decimal:2',
        'ServiciosDivisa' => 'decimal:2',
        'ServiciosBs' => 'decimal:2',
        'SaldoOperacionBs' => 'decimal:2',
        'SaldoOperacionDivisas' => 'decimal:2',
        'SaldoGeneralBs' => 'decimal:2',
        'SaldoGeneralDivisas' => 'decimal:2'
    ];

    public $timestamps = false;

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }
}