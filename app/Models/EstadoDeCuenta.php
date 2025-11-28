<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstadoDeCuenta extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'EstadoDeCuenta';
    protected $primaryKey = 'EdcId';

    protected $fillable = [
        'EdcId',
        'SucursalId',
        'Fecha',
        'Estatus',
        'Ventas',
        'AbonoPrestamos',
        'GastosCaja',
        'GastosSucursal',
        'PagoServicios',
        'PagoProveedores',
        'Prestamos',
        'SaldoOperacion',
        'SaldoGeneral'
    ];

    protected $casts = [
        'Fecha' => 'datetime',
        'Estatus' => 'integer',
        'Ventas' => 'decimal:2',
        'AbonoPrestamos' => 'decimal:2',
        'GastosCaja' => 'decimal:2',
        'GastosSucursal' => 'decimal:2',
        'PagoServicios' => 'decimal:2',
        'PagoProveedores' => 'decimal:2',
        'Prestamos' => 'decimal:2',
        'SaldoOperacion' => 'decimal:2',
        'SaldoGeneral' => 'decimal:2'
    ];

    public $timestamps = false;

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }
}