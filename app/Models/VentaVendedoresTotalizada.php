<?php
// app/Models/VentaVendedoresTotalizada.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class VentaVendedoresTotalizada extends BaseModel
{
    use HasFactory;

    protected $table = 'VentaVendedoresTotalizada';
    public $timestamps = false;
    
    protected $casts = [
        'Fecha' => 'datetime',
        'ID' => 'integer',
        'SucursalId' => 'integer',
        'TasaDeCambio' => 'decimal:2',
        'Cantidad' => 'integer',
        'TotalBs' => 'decimal:2',
        'TotalDivisa' => 'decimal:2',
        'CostoDivisa' => 'decimal:2',
        'Estatus' => 'integer',
    ];
}