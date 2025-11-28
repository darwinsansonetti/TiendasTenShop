<?php
// app/Models/VentaVendedoresView.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class VentaVendedoresView extends BaseModel
{
    use HasFactory;

    protected $table = 'VentaVendedoresView';
    public $timestamps = false;
    
    protected $casts = [
        'Fecha' => 'datetime',
        'ID' => 'integer',
        'VentaId' => 'integer',
        'SucursalId' => 'integer',
        'ProductoId' => 'integer',
        'VentaVendedorId' => 'integer',
        'Cantidad' => 'integer',
        'PrecioVenta' => 'decimal:2',
        'MontoDivisa' => 'decimal:2',
        'CostoDivisa' => 'decimal:2',
        'Costo' => 'decimal:2',
        'Existencia' => 'integer',
    ];
}