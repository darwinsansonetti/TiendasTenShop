<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventario extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'Inventario';
    protected $primaryKey = 'InventarioId';

    protected $fillable = [
        'InventarioId',
        'FechaInicio',
        'FechaFin',
        'FechaConteo',
        'FechaCierre',
        'Codigo',
        'Descripcion',
        'SucursalId',
        'CantidadParaContar',
        'CantidadContada',
        'CantidadDiferencias',
        'ItemsParaContar',
        'ItemsContados',
        'ItemsDiferencias',
        'Estatus',
        'Tipo',
        'ArchivoConteo',
        'ProductoInicialId',
        'ProductoFinalId'
    ];

    protected $casts = [
        'FechaInicio' => 'datetime',
        'FechaFin' => 'datetime',
        'FechaConteo' => 'datetime',
        'FechaCierre' => 'datetime',
        'Estatus' => 'integer',
        'Tipo' => 'integer'
    ];

    public $timestamps = false;

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(InventarioDetalle::class, 'InventarioId');
    }
}