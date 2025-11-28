<?php
// app/Models/ProductosSucursalView.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductosSucursalView extends BaseModel
{
    use HasFactory;

    protected $table = 'ProductosSucursalView';
    public $timestamps = false;
    
    protected $casts = [
        'ID' => 'integer',
        'SucursalId' => 'integer',
        'CostoBs' => 'decimal:2',
        'CostoDivisa' => 'decimal:2',
        'FechaActualizacion' => 'datetime',
        'FechaCreacion' => 'datetime',
        'DepartamentoId' => 'integer',
        'EsProveedorAsignado' => 'boolean',
        'PvpBs' => 'decimal:2',
        'PvpDivisa' => 'decimal:2',
        'Estatus' => 'integer',
        'Existencia' => 'integer',
        'FechaUltimaVenta' => 'datetime',
        'NuevoPvp' => 'decimal:2',
        'FechaNuevoPrecio' => 'datetime',
        'Tipo' => 'integer',
        'PvpAnterior' => 'decimal:2',
    ];
}