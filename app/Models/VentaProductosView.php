<?php
// app/Models/VentaProductosView.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class VentaProductosView extends BaseModel
{
    use HasFactory;

    protected $table = 'VentaProductosView';
    public $timestamps = false;
    
    protected $casts = [
        'Fecha' => 'datetime',
        'ID' => 'integer',
        'SucursalId' => 'integer',
        'VentaId' => 'integer',
        'ProductoId' => 'integer',
        'Cantidad' => 'integer',
        'PrecioVenta' => 'decimal:2',
        'MontoDivisa' => 'decimal:2',
        'PvpDivisa' => 'decimal:2',
        'Existencia' => 'integer',
        'Estatus' => 'integer',
        'FechaNuevoPrecio' => 'datetime',
        'NuevoPvp' => 'decimal:2',
        'PvpAnterior' => 'decimal:2',
        'FechaUltimaVenta' => 'datetime',
    ];
}