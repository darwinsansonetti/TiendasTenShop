<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductoSucursal extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'ProductoSucursal';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'SucursalId',
        'ProductoId',
        'PvpBs',
        'PvpDivisa',
        'Estatus',
        'Existencia',
        'FechaIngreso',
        'FechaUltimaVenta',
        'Sobreventa',
        'NuevoPvp',
        'FechaNuevoPrecio',
        'Tipo',
        'PvpAnterior'
    ];

    protected $casts = [
        'PvpBs' => 'decimal:2',
        'PvpDivisa' => 'decimal:2',
        'Estatus' => 'integer',
        'Existencia' => 'integer',
        'FechaIngreso' => 'datetime',
        'FechaUltimaVenta' => 'datetime',
        'Sobreventa' => 'integer',
        'NuevoPvp' => 'decimal:2',
        'FechaNuevoPrecio' => 'datetime',
        'Tipo' => 'integer',
        'PvpAnterior' => 'decimal:2'
    ];

    public $timestamps = false;

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'ProductoId', 'ID');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }
}