<?php
// app/Models/VentaDiariaTotalizada.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class VentaDiariaTotalizada extends BaseModel
{
    use HasFactory;

    protected $table = 'VentaDiariaTotalizada';
    public $timestamps = false;
    
    protected $casts = [
        'Fecha' => 'datetime',
        'ID' => 'integer',
        'SucursalId' => 'integer',
        'TasaDeCambio' => 'decimal:2',
        'Cantidad' => 'integer',
        'CostoDivisa' => 'decimal:2',
        'TotalBs' => 'decimal:2',
        'TotalDivisa' => 'decimal:2',
        'ProveedorId' => 'integer',
        'Estatus' => 'integer',
        'Saldo' => 'decimal:2',
    ];
}