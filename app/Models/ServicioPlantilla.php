<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicioPlantilla extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'ServiciosPlantilla';
    protected $primaryKey = 'ServiciosPlantillaId';

    protected $fillable = [
        'ServiciosPlantillaId',
        'ProveedorId',
        'Numero',
        'Descripcion',
        'MontoDivisa',
        'Monto',
        'MonedaPrincipal',
        'FechaCreacion',
        'Estatus',
        'TipoRecurrencia',
        'FechaRecurrencia',
        'SucursalId'
    ];

    protected $casts = [
        'MontoDivisa' => 'decimal:2',
        'Monto' => 'decimal:2',
        'MonedaPrincipal' => 'integer',
        'FechaCreacion' => 'datetime',
        'Estatus' => 'integer',
        'TipoRecurrencia' => 'integer',
        'FechaRecurrencia' => 'datetime'
    ];

    public $timestamps = false;

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'ProveedorId');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'SucursalId', 'ID');
    }
}