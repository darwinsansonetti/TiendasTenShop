<?php
// app/Models/VentaDiariaProveedorTotalizada.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class VentaDiariaProveedorTotalizada extends BaseModel
{
    use HasFactory;

    protected $table = 'VentaDiariaProveedorTotalizada';
    public $timestamps = false;
    
    protected $casts = [
        'Fecha' => 'datetime',
        'ID' => 'integer',
        'SucursalId' => 'integer',
        'tasadecambio' => 'decimal:2',
        'cantidad' => 'integer',
        'costodivisa' => 'decimal:2',
        'totalbs' => 'decimal:2',
        'totaldivisa' => 'decimal:2',
        'vendedorid' => 'integer',
        'estatus' => 'integer',
        'ProveedorId' => 'integer',
    ];
}